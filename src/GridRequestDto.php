<?php declare(strict_types=1);

namespace Hanaboso\MongoDataGrid;

use Throwable;

/**
 * Class GridRequestDto
 *
 * @package Hanaboso\MongoDataGrid
 */
class GridRequestDto implements GridRequestDtoInterface
{

    public const  LIMIT           = 'limit';
    private const FILTER          = 'filter';
    private const ADVANCED_FILTER = 'advanced_filter';
    private const PAGE            = 'page';
    private const TOTAL           = 'total';
    private const ORDER_BY        = 'orderby';
    private const SEARCH          = 'search';
    private const DEFAULT_LIMIT   = 10;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var int
     */
    private $total = 0;

    /**
     * @var array
     */
    private $filter = [];

    /**
     * @var int
     */
    private $limit = 0;

    /**
     * GridRequestDto constructor.
     *
     * @param array $headers
     */
    public function __construct(array $headers)
    {
        $this->headers = array_change_key_case($headers, CASE_LOWER);
    }

    /**
     * @return array
     */
    public function getFilter(): array
    {
        if (array_key_exists(self::FILTER, $this->headers)) {
            $filter = json_decode($this->getHeader(self::FILTER), TRUE);
            if (isset($filter[self::SEARCH])) {
                $filter[GridFilterAbstract::FILTER_SEARCH_KEY] = $filter[self::SEARCH];
                unset($filter[self::SEARCH]);
            }

            return array_merge($filter, $this->filter);
        }

        return $this->filter;
    }

    /**
     * @return array
     */
    public function getAdvancedFilter(): array
    {
        if (array_key_exists(self::ADVANCED_FILTER, $this->headers)) {
            $andConditions = json_decode($this->getHeader(self::ADVANCED_FILTER), TRUE);

            foreach ($andConditions as &$andCondition) {
                foreach ($andCondition as &$orCondition) {
                    if (array_key_exists('column', $orCondition) && $orCondition['column'] === self::SEARCH) {
                        $orCondition['column'] = GridFilterAbstract::FILTER_SEARCH_KEY;
                    }
                }
            }

            return $andConditions;
        }

        return [];
    }

    /**
     * @param array $filter
     *
     * @return GridRequestDto
     */
    public function setAdditionalFilters(array $filter): self
    {
        $this->filter = array_merge($this->getFilter(), $filter);

        return $this;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        if (array_key_exists(self::PAGE, $this->headers)) {
            return intval($this->getHeader(self::PAGE));
        }

        return 1;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        if ($this->limit !== 0) {
            return $this->limit;
        }

        if (array_key_exists(self::LIMIT, $this->headers)) {
            return (int) $this->getHeader(self::LIMIT);
        }

        return self::DEFAULT_LIMIT;
    }

    /**
     * @param int $limit
     *
     * @return GridRequestDto
     */
    public function setLimit(int $limit): GridRequestDto
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return string|NULL
     */
    private function getOrderByForHeader(): ?string
    {
        if (array_key_exists(self::ORDER_BY, $this->headers)) {
            return $this->getHeader(self::ORDER_BY);
        }

        return NULL;
    }

    /**
     * @return array
     */
    public function getOrderBy(): array
    {
        if (array_key_exists(self::ORDER_BY, $this->headers) && $this->getHeader(self::ORDER_BY)) {

            preg_match('/[+-]/', $this->getHeader(self::ORDER_BY), $orderArray);

            if (reset($orderArray) == '+') {
                $order = 'ASC';
            } else {
                $order = 'DESC';
            }

            $columnName = preg_replace('/[+-]/', '', $this->getHeader(self::ORDER_BY));

            return [$columnName, $order];
        }

        return [];
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @param int $total
     *
     * @return GridRequestDtoInterface
     */
    public function setTotal(int $total): GridRequestDtoInterface
    {
        $this->total = $total;

        return $this;
    }

    /**
     * @return array
     */
    public function getParamsForHeader(): array
    {
        return [
            self::FILTER   => $this->formatFilterForHeader($this->getFilter()),
            self::PAGE     => $this->getPage(),
            self::LIMIT    => $this->getLimit(),
            self::TOTAL    => $this->total,
            self::ORDER_BY => $this->getOrderByForHeader(),
        ];
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function formatFilterForHeader(array $data): array
    {
        foreach ($data as $key => &$item) {
            if (is_array($item)) {
                try {
                    $item = implode(',', $item);
                } catch (Throwable $t) {
                    $item = '';
                }
            }

            if ($key === GridFilterAbstract::FILTER_SEARCH_KEY) {
                $data[self::SEARCH] = $item;
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * @param string $key
     *
     * @return string
     */
    private function getHeader(string $key): string
    {
        if (is_array($this->headers[$key])) {
            return (string) $this->headers[$key][0] ?? '';
        } else {
            return (string) $this->headers[$key];
        }
    }

}