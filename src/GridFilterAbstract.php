<?php declare(strict_types=1);

namespace Hanaboso\MongoDataGrid;

use DateTime;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Iterator\Iterator;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Expr;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Doctrine\Persistence\ObjectRepository;
use Exception;
use Hanaboso\MongoDataGrid\Exception\GridException;
use Hanaboso\MongoDataGrid\Result\ResultData;
use Hanaboso\Utils\Date\DateTimeUtils;
use LogicException;
use MongoDB\BSON\Regex;
use MongoDB\Driver\Exception\CommandException;

/**
 * Class GridFilterAbstract
 *
 * @package Hanaboso\MongoDataGrid
 */
abstract class GridFilterAbstract
{

    public const string EQ       = 'EQ';
    public const string NEQ      = 'NEQ';
    public const string IN       = 'IN';
    public const string NIN      = 'NIN';
    public const string GT       = 'GT';
    public const string LT       = 'LT';
    public const string GTE      = 'GTE';
    public const string LTE      = 'LTE';
    public const string LIKE     = 'LIKE';
    public const string STARTS   = 'STARTS';
    public const string ENDS     = 'ENDS';
    public const string NEMPTY   = 'NEMPTY';
    public const string EMPTY    = 'EMPTY';
    public const string BETWEEN  = 'BETWEEN';
    public const string NBETWEEN = 'NBETWEEN';
    public const string EXIST    = 'EXIST';
    public const string NEXIST   = 'NEXIST';

    public const string ASCENDING  = 'ASC';
    public const string DESCENDING = 'DESC';

    public const string COLUMN    = 'column';
    public const string OPERATOR  = 'operator';
    public const string VALUE     = 'value';
    public const string DIRECTION = 'direction';
    public const string SEARCH    = 'search';

    protected const string DATE_FORMAT = DateTimeUtils::DATE_TIME_UTC;

    /**
     * @var bool
     */
    protected bool $allowNative = FALSE;

    /**
     * @var mixed[]
     */
    protected array $projection = [];

    /**
     * @var string
     * @phpstan-var class-string
     */
    protected string $document;

    /**
     * @var Builder|NULL
     */
    private ?Builder $countQuery;

    /**
     * @var bool
     */
    private bool $useTextSearch;

    /**
     * @var mixed[]
     */
    private array $orderCols;

    /**
     * @var Builder
     */
    private Builder $searchQuery;

    /**
     * @var mixed[]
     */
    private array $searchableCols;

    /**
     * @var mixed[]
     */
    private array $filterCols;

    /**
     * @var mixed[]
     */
    private array $filterColsCallbacks;

    /**
     *
     */
    abstract protected function prepareSearchQuery(): Builder;

    /**
     *
     */
    abstract protected function setDocument(): void;

    /**
     * @return mixed[]
     */
    abstract protected function filterCols(): array;

    /**
     * @return mixed[]
     */
    abstract protected function orderCols(): array;

    /**
     * @return mixed[]
     */
    abstract protected function searchableCols(): array;

    /**
     * @return bool
     */
    abstract protected function useTextSearch(): bool;

    /**
     * GridFilterAbstract constructor.
     *
     * @param DocumentManager $dm
     */
    public function __construct(protected DocumentManager $dm)
    {
        $this->setDocument();

        $this->filterCols          = $this->filterCols();
        $this->orderCols           = $this->orderCols();
        $this->searchableCols      = $this->searchableCols();
        $this->filterColsCallbacks = $this->configFilterColsCallbacks();
        $this->useTextSearch       = $this->useTextSearch();
    }

    /**
     * @param GridRequestDtoInterface $gridRequestDto
     *
     * @return ResultData
     * @throws Exception
     */
    public function getData(GridRequestDtoInterface $gridRequestDto): ResultData
    {
        $nat = $gridRequestDto->getNativeQuery();
        if ($this->allowNative && $nat) {
            return $this->nativeQuery($gridRequestDto);
        }

        $this->searchQuery = $this->prepareSearchQuery();
        $this->countQuery  = $this->configCustomCountQuery();
        $this->searchQuery->hydrate(FALSE);

        $this->processSortations($gridRequestDto);
        $this->processConditions($gridRequestDto, $this->searchQuery);

        if ($this->countQuery) {
            $this->processConditions($gridRequestDto, $this->countQuery);
        } else {
            $this->countQuery = clone $this->searchQuery;
        }

        $this->processPagination($gridRequestDto);

        try {
            /** @var Iterator<mixed> $data */
            $data = $this->searchQuery->getQuery()->execute();
            $data = $data->toArray();

            $data = new ResultData($data, static::DATE_FORMAT);
            /** @var Builder $countQuery */
            $countQuery = $this->countQuery;
            /** @var int $total */
            $total = $countQuery->count()->getQuery()->execute();
            $gridRequestDto->setTotal($total);
        } catch (CommandException $e) {
            if ($e->getCode() === 27) {
                throw new LogicException(
                    sprintf(
                        "Column cannot be used for searching! Missing TEXT index on '%s::searchableCols' fields!",
                        static::class,
                    ),
                );
            }

            throw $e;
        }

        return $data;
    }

    /**
     * @return DocumentRepository<mixed>&ObjectRepository
     */
    public function getRepository(): ObjectRepository
    {
        return $this->dm->getRepository($this->document);
    }

    /**
     * @param Builder     $builder
     * @param string      $name
     * @param mixed       $value
     * @param string|NULL $operator
     *
     * @return Expr
     */
    public static function getCondition(Builder $builder, string $name, mixed $value, ?string $operator = NULL): Expr
    {
        switch ($operator) {
            case self::EQ:
                return is_array($value)
                    ? $builder->expr()->field($name)->in($value)
                    : $builder->expr()->field($name)->equals($value);
            case self::NEQ:
                return is_array($value)
                    ? $builder->expr()->field($name)->notIn($value)
                    : $builder->expr()->field($name)->notEqual($value);
            case self::IN:
                return $builder->expr()->field($name)->in($value);
            case self::NIN:
                return $builder->expr()->field($name)->notIn($value);
            case self::GTE:
                return $builder->expr()->field($name)->gte(self::getValue($value));
            case self::GT:
                return $builder->expr()->field($name)->gt(self::getValue($value));
            case self::LTE:
                return $builder->expr()->field($name)->lte(self::getValue($value));
            case self::LT:
                return $builder->expr()->field($name)->lt(self::getValue($value));
            case self::NEMPTY:
                return $builder->expr()
                    ->addOr($builder->expr()->field($name)->notEqual(NULL))
                    ->addOr($builder->expr()->field($name)->notEqual(self::getValue($value)));
            case self::EMPTY:
                return $builder->expr()
                    ->addOr($builder->expr()->field($name)->equals(NULL))
                    ->addOr($builder->expr()->field($name)->equals(self::getValue($value)));
            case self::LIKE:
                return $builder->expr()->field($name)->equals(
                    new Regex(sprintf('%s', preg_quote(self::getValue($value))), 'i'),
                );
            case self::STARTS:
                return $builder->expr()->field($name)->equals(
                    new Regex(sprintf('^%s', preg_quote(self::getValue($value))), 'i'),
                );
            case self::ENDS:
                return $builder->expr()->field($name)->equals(
                    new Regex(sprintf('%s$', preg_quote(self::getValue($value))), 'i'),
                );
            case self::BETWEEN:
                if (is_array($value) && count($value) >= 2) {
                    return $builder->expr()
                        ->addAnd($builder->expr()->field($name)->gte($value[0]))
                        ->addAnd($builder->expr()->field($name)->lte($value[1]));
                }

                return $builder->expr()->field($name)->equals(self::getValue($value));
            case self::NBETWEEN:
                if (is_array($value) && count($value) >= 2) {
                    return $builder->expr()
                        ->addOr($builder->expr()->field($name)->lte($value[0]))
                        ->addOr($builder->expr()->field($name)->gte($value[1]));
                }

                return $builder->expr()->field($name)->notEqual(self::getValue($value));
            case self::EXIST:
                return  $builder->expr()->field($name)->exists(TRUE);
            case self::NEXIST:
                return  $builder->expr()->field($name)->exists(FALSE);
            default:
                return $builder->expr()->field($name)->equals(self::getValue($value));
        }
    }

    /**
     * @param GridRequestDtoInterface $dto
     * @param mixed[]                 $items
     *
     * @return mixed[]
     */
    public static function getGridResponse(GridRequestDtoInterface $dto, array $items): array
    {
        $total    = $dto->getTotal();
        $page     = $dto->getPage();
        $lastPage = (int) max(1, ceil($dto->getTotal() / $dto->getItemsPerPage()));

        return [
            'filter' => $dto->getFilter(FALSE),
            'items'  => $items,
            'paging' => [
                'itemsPerPage' => $dto->getItemsPerPage(),
                'lastPage'     => $lastPage,
                'nextPage'     => min($lastPage, $page + 1),
                'page'         => $page,
                'previousPage' => max(1, $page - 1),
                'total'        => $total,
            ],
            'search' => $dto->getSearch(),
            'sorter' => $dto->getOrderBy(),
        ];
    }

    /**
     * -------------------------------------------- HELPERS -----------------------------------------------
     */

    /**
     * In child can configure GridFilterAbstract::filterColsCallbacks
     * example child content
     *
     * return [ESomeEnumCols::CREATED_AT_FROM => function (Builder $builder,string $value,string $name,Expr $expr,?string $operator){}]
     *
     * @return mixed[]
     */
    protected function configFilterColsCallbacks(): array
    {
        return [];
    }

    /**
     * In child can configure GridFilterAbstract::configCustomCountQuery
     * example child content
     * return $this->getRepository()->createQueryBuilder('c')->select('count(c.id)')
     */
    protected function configCustomCountQuery(): ?Builder
    {
        return NULL;
    }

    /**
     * @param GridRequestDtoInterface $dto
     *
     * @return ResultData
     * @throws MongoDBException
     * @throws GridException
     * @throws Exception
     */
    private function nativeQuery(GridRequestDtoInterface $dto): ResultData
    {
        $native = $dto->getNativeQuery();
        $ors    = [];

        foreach ($dto->getFilter() as $and) {
            $parsed = [];
            foreach ($and as $or) {
                $this->checkFilterColumn($or[self::COLUMN]);
                $parsed[] = $this->getNativeCondition(
                    $or[self::COLUMN],
                    $this->processDateTime($or[self::VALUE]),
                    $or[self::OPERATOR],
                );
            }
            $ors[] = ['$or' => $parsed];
        }
        if ($ors) {
            $native['$and'] = $ors;
        }

        $search = $dto->getSearch();
        if ($search) {
            if ($this->useTextSearch) {
                $native['$text'] = ['$search' => $search];
            }
            $searches = [];
            foreach ($this->searchableCols as $column) {
                $searches[] = [$column => new Regex($search, 'i')];
            }
            if ($searches) {
                $native['$and'][] = ['$or' => $searches];
            }
        }

        $options = [
            'limit'      => $dto->getItemsPerPage(),
            'projection' => $this->projection,
            'skip'       => ($dto->getPage() - 1) * $dto->getItemsPerPage(),
            'sort'       => $this->nativeSortations($dto),
        ];

        $items = $this->dm->getDocumentCollection($this->document)->find($native, $options)->toArray();
        $dto->setTotal($this->dm->getDocumentCollection($this->document)->countDocuments($native));

        return new ResultData($items);
    }

    /**
     * @param GridRequestDtoInterface $dto
     *
     * @throws GridException
     */
    private function processSortations(GridRequestDtoInterface $dto): void
    {
        $sortations = $this->parseSortations($dto);
        foreach ($sortations as $column => $direction) {
            $this->searchQuery->sort($this->orderCols[$column], $direction);
        }
    }

    /**
     * @param GridRequestDtoInterface $dto
     *
     * @return mixed[]
     * @throws GridException
     */
    private function nativeSortations(GridRequestDtoInterface $dto): array
    {
        $sorts = [];
        foreach ($this->parseSortations($dto) as $column => $direction) {
            $sorts[$column] = strtolower($direction) === 'asc' ? 1 : -1;
        }

        return $sorts;
    }

    /**
     * @param GridRequestDtoInterface $dto
     *
     * @return mixed[]
     * @throws GridException
     */
    private function parseSortations(GridRequestDtoInterface $dto): array
    {
        $sortations = $dto->getOrderBy();
        $toSort     = [];

        if ($sortations) {
            foreach ($sortations as $sortation) {
                $column    = $sortation[self::COLUMN];
                $direction = $sortation[self::DIRECTION];
                if (!isset($this->orderCols[$column])) {
                    throw new GridException(
                        sprintf(
                            "Column '%s' cannot be used for sorting! Have you forgotten add it to '%s::orderCols'?",
                            $column,
                            static::class,
                        ),
                        GridException::MISSING_SORTATION_COLUMN,
                    );
                }

                $toSort[$column] = $direction;
            }
        }

        return $toSort;
    }

    /**
     * @param GridRequestDtoInterface $dto
     * @param Builder                 $builder
     *
     * @throws Exception
     */
    private function processConditions(GridRequestDtoInterface $dto, Builder $builder): void
    {
        $conditions          = $dto->getFilter();
        $conditionExpression = $builder->expr();

        $exp = FALSE;
        foreach ($conditions as $andCondition) {
            $hasExpression = FALSE;
            $expression    = $builder->expr();

            foreach ($andCondition as $orCondition) {
                if (!array_key_exists(self::COLUMN, $orCondition) ||
                    !array_key_exists(self::OPERATOR, $orCondition) ||
                    !array_key_exists(self::VALUE, $orCondition) &&
                    !in_array(
                        $orCondition[self::OPERATOR],
                        [self::EMPTY, self::NEMPTY, self::EXIST, self::NEXIST],
                        TRUE,
                    )) {
                    throw new LogicException(
                        sprintf(
                            "Advanced filter must have '%s', '%s' and '%s' field!",
                            self::COLUMN,
                            self::OPERATOR,
                            self::VALUE,
                        ),
                    );
                }

                if (!array_key_exists(self::VALUE, $orCondition)) {
                    $orCondition[self::VALUE] = '';
                }

                $column = $orCondition[self::COLUMN];

                $this->checkFilterColumn($column);
                $hasExpression            = TRUE;
                $orCondition[self::VALUE] = $this->processDateTime($orCondition[self::VALUE]);

                if (isset($this->filterColsCallbacks[$column])) {
                    $expr = $builder->expr();

                    $this->filterColsCallbacks[$column](
                        $this->searchQuery,
                        $orCondition[self::VALUE],
                        $this->filterCols[$column],
                        $expr,
                        $orCondition[self::OPERATOR]
                    );
                    $expression->addOr($expr);

                    continue;
                }

                $expression->addOr(
                    self::getCondition(
                        $builder,
                        $this->filterCols[$column],
                        $orCondition[self::VALUE],
                        $orCondition[self::OPERATOR],
                    ),
                );
            }

            if ($hasExpression) {
                $conditionExpression->addAnd($expression);
                $exp = TRUE;
            }
        }

        if ($exp) {
            $builder->addAnd($conditionExpression);
        }

        $search = $dto->getSearch();

        if ($search) {
            if ($this->useTextSearch) {
                $builder->text($search);
            }

            $searchExpression = $builder->expr();

            if (!$this->searchableCols) {
                throw new GridException(
                    sprintf(
                        "Column cannot be used for searching! Have you forgotten add it to '%s::searchableCols'?",
                        static::class,
                    ),
                    GridException::MISSING_SEARCH_COLUMN,
                );
            }

            foreach ($this->searchableCols as $column) {
                if (!array_key_exists($column, $this->filterCols)) {
                    throw new GridException(
                        sprintf(
                            "Column '%s' cannot be used for searching! Have you forgotten add it to '%s::filterCols'?",
                            $column,
                            static::class,
                        ),
                        GridException::MISSING_SEARCH_COLUMN,
                    );
                }

                if (isset($this->filterColsCallbacks[$column])) {
                    $expression = $builder->expr();

                    $this->filterColsCallbacks[$column](
                        $this->searchQuery,
                        $search,
                        $this->filterCols[$column],
                        $expression,
                        NULL
                    );

                    $searchExpression->addOr($expression);

                    continue;
                }

                $searchExpression->addOr(self::getCondition($builder, $column, $search, self::LIKE));
            }

            $builder->addAnd($searchExpression);
        }
    }

    /**
     * @param GridRequestDtoInterface $dto
     */
    private function processPagination(GridRequestDtoInterface $dto): void
    {
        $page  = $dto->getPage();
        $limit = $dto->getItemsPerPage();

        $this->searchQuery->skip(--$page * $limit)->limit($limit);
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     * @throws Exception
     */
    private function processDateTime(mixed $value): mixed
    {
        $values = $value;
        if (!is_array($value)) {
            $values = [$value];
        }

        foreach ($values as $index => $val) {
            if (is_string($val) && preg_match('/\d{4}-\d{2}-\d{2}.\d{2}:\d{2}:\d{2}/', $val)) {
                $values[$index] = new DateTime($val);
            }
        }

        return $values;
    }

    /**
     * @param string $column
     *
     * @throws GridException
     */
    private function checkFilterColumn(string $column): void
    {
        if (!isset($this->filterCols[$column])) {
            throw new GridException(
                sprintf(
                    "Column '%s' cannot be used for filtering! Have you forgotten add it to '%s::filterCols'?",
                    $column,
                    static::class,
                ),
                GridException::MISSING_CONDITION_COLUMN,
            );
        }
    }

    /**
     * @param string      $name
     * @param mixed       $value
     * @param string|null $operator
     *
     * @return mixed[]
     * @throws GridException
     */
    private function getNativeCondition(string $name, mixed $value, ?string $operator = NULL): array
    {
        switch ($operator) {
            case self::EQ:
                return is_array($value)
                    ? [$name => ['$in' => $value]]
                    : [$name => ['$eq' => $value]];
            case self::NEQ:
                return is_array($value)
                    ? [$name => ['$nin' => $value]]
                    : [$name => ['$not' => ['$eq' => $value]]];
            case self::IN:
                return [$name => ['$in' => $value]];
            case self::NIN:
                return [$name => ['$nin' => $value]];
            case self::GTE:
                return [$name => ['$gte' => self::getValue($value)]];
            case self::GT:
                return [$name => ['$gt' => self::getValue($value)]];
            case self::LTE:
                return [$name => ['$lte' => self::getValue($value)]];
            case self::LT:
                return [$name => ['$lt' => self::getValue($value)]];
            case self::NEMPTY:
                return [$name => ['$not' => ['$eq' => NULL]]];
            case self::EMPTY:
                return [$name => ['$eq' => NULL]];
            case self::LIKE:
                return [$name => ['$regex' => new Regex(sprintf('%s', preg_quote(self::getValue($value))), 'i')]];
            case self::STARTS:
                return [$name => ['$regex' => new Regex(sprintf('^%s', preg_quote(self::getValue($value))), 'i')]];
            case self::ENDS:
                return [$name => ['$regex' => new Regex(sprintf('%s$', preg_quote(self::getValue($value))), 'i')]];
            case self::BETWEEN:
                if (is_array($value) && count($value) >= 2) {
                    return [
                        $name => [
                            '$gte' => $value[0],
                            '$lte' => $value[1],
                        ],
                    ];
                }

                throw new GridException('BETWEEN requires 2 values');
            case self::NBETWEEN:
                if (is_array($value) && count($value) >= 2) {
                    return [
                        $name => [
                            '$gte' => $value[1],
                            '$lte' => $value[0],
                        ],
                    ];
                }

                throw new GridException('NBETWEEN requires 2 values');
        }

        throw new GridException(sprintf('unknown operator [%s]', $operator));
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    private static function getValue(mixed $value): mixed
    {
        return is_array($value) ? $value[0] : $value;
    }

}
