<?php declare(strict_types=1);

namespace Hanaboso\MongoDataGrid;

use Hanaboso\MongoDataGrid\Exception\GridException;
use Hanaboso\Utils\String\Json;

/**
 * Class GridRequestDto
 *
 * @package Hanaboso\MongoDataGrid
 */
class GridRequestDto implements GridRequestDtoInterface
{

    public const  string ITEMS          = 'items';
    public const  string ITEMS_PER_PAGE = 'itemsPerPage';
    public const  string FILTER         = 'filter';
    public const  string NATIVE         = 'native';
    public const  string PAGE           = 'page';
    public const  string PAGING         = 'paging';
    public const  string TOTAL          = 'total';
    public const  string SORTER         = 'sorter';
    public const  string SEARCH         = 'search';
    private const int DEFAULT_LIMIT     = 10;
    private const array DIRECTION       = [GridFilterAbstract::ASCENDING, GridFilterAbstract::DESCENDING];

    /**
     * @var mixed[]
     */
    private array $headers;

    /**
     * @var int
     */
    private int $total = 0;

    /**
     * @var mixed[]
     */
    private array $filter = [];

    /**
     * @var int
     */
    private int $itemsPerPage = 0;

    /**
     * GridRequestDto constructor.
     *
     * @param mixed[] $headers
     */
    public function __construct(array $headers)
    {
        $this->headers = array_change_key_case($headers);
    }

    /**
     * @param bool $withAdditional
     *
     * @return mixed[]
     * @throws GridException
     */
    public function getFilter(bool $withAdditional = TRUE): array
    {
        $filter = [];

        if (array_key_exists(self::FILTER, $this->headers)) {
            $filter = $this->headers[self::FILTER] ?: [];
        }

        if ($withAdditional) {
            return array_merge($filter, $this->filter);
        }

        foreach ($filter as $row) {
            if (!is_array($row)) {
                throw new GridException('Incorrect filter format - must be two nested arrays');
            }

            foreach ($row as $item) {
                if (!array_key_exists(GridFilterAbstract::COLUMN, $item)
                    || !array_key_exists(GridFilterAbstract::OPERATOR, $item)) {
                    throw new GridException(
                        sprintf(
                            '[%s, %s] filter fields are mandatory',
                            GridFilterAbstract::OPERATOR,
                            GridFilterAbstract::COLUMN,
                        ),
                    );
                }
            }
        }

        return $filter;
    }

    /**
     * @param mixed[] $filter
     *
     * @return GridRequestDto
     * @throws GridException
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
        if (array_key_exists(self::PAGING, $this->headers)) {
            return max((int) ($this->headers[self::PAGING][self::PAGE] ?? 1), 1);
        }

        return 1;
    }

    /**
     * @return int
     */
    public function getItemsPerPage(): int
    {
        if ($this->itemsPerPage !== 0) {
            return $this->itemsPerPage;
        }

        if (array_key_exists(self::PAGING, $this->headers)) {
            $limit = (int) ($this->headers[self::PAGING][self::ITEMS_PER_PAGE] ?? self::DEFAULT_LIMIT);

            return $limit > 0 ? $limit : self::DEFAULT_LIMIT;
        }

        return self::DEFAULT_LIMIT;
    }

    /**
     * @param int $itemsPerPage
     *
     * @return GridRequestDto
     */
    public function setItemsPerPage(int $itemsPerPage): self
    {
        $this->itemsPerPage = $itemsPerPage;

        return $this;
    }

    /**
     * @return mixed[]
     * @throws GridException
     */
    public function getOrderBy(): array
    {
        $sort = [];
        if (array_key_exists(self::SORTER, $this->headers)) {
            $sort = $this->headers[self::SORTER] ?: [];
        }

        foreach ($sort as $item) {
            if (!is_array($item)) {
                throw new GridException('Incorrect sorter format - must be two nested arrays');
            }

            if (!array_key_exists(GridFilterAbstract::COLUMN, $item)
                || !array_key_exists(GridFilterAbstract::DIRECTION, $item)) {
                throw new GridException(
                    sprintf(
                        'Each sorter must contain [%s, %s] keys',
                        GridFilterAbstract::COLUMN,
                        GridFilterAbstract::DIRECTION,
                    ),
                );
            }

            if (!in_array($item[GridFilterAbstract::DIRECTION], self::DIRECTION, TRUE)) {
                throw new GridException(
                    sprintf(
                        'Invalid direction of sorter [%s], valid options: [%s, %s]',
                        $item[GridFilterAbstract::DIRECTION],
                        GridFilterAbstract::ASCENDING,
                        GridFilterAbstract::DESCENDING,
                    ),
                );
            }
        }

        return $sort;
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
     * @return mixed[]
     * @throws GridException
     */
    public function getParamsForHeader(): array
    {
        return [
            self::FILTER         => $this->formatFilterForHeader($this->getFilter()),
            self::ITEMS_PER_PAGE => $this->getItemsPerPage(),
            self::PAGE           => $this->getPage(),
            self::SEARCH         => $this->getSearch(),
            self::SORTER         => $this->getOrderByForHeader(),
            self::TOTAL          => $this->total,
        ];
    }

    /**
     * @return string|NULL
     */
    public function getSearch(): ?string
    {
        return $this->headers[self::SEARCH] ?? NULL;
    }

    /**
     * @return mixed[]
     */
    public function getNativeQuery(): array
    {
        return $this->headers[self::NATIVE] ?? [];
    }

    /**
     * @param mixed[] $data
     *
     * @return string
     */
    protected function formatFilterForHeader(array $data): string
    {
        return Json::encode($data);
    }

    /**
     * @return string|NULL
     */
    private function getOrderByForHeader(): ?string
    {
        if (array_key_exists(self::SORTER, $this->headers)) {
            return Json::encode($this->headers[self::SORTER]);
        }

        return NULL;
    }

}
