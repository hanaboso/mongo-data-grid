<?php declare(strict_types=1);

namespace Hanaboso\MongoDataGrid;

use Closure;
use DateTime;
use Doctrine\ODM\MongoDB\Aggregation\Builder;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Expr;
use Exception;
use Hanaboso\MongoDataGrid\Exception\GridException;
use Hanaboso\MongoDataGrid\Result\ResultData;
use Hanaboso\Utils\Date\DateTimeUtils;
use MongoDB\BSON\Regex;

/**
 * Class GridAggregationFilterAbstract
 *
 * @package Hanaboso\MongoDataGrid
 */
abstract class GridAggregationFilterAbstract
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

    private const array NO_VALUE_OPERATORS = [self::EMPTY, self::NEMPTY, self::EXIST, self::NEXIST];

    /**
     * @return class-string
     */
    abstract protected function getDocumentClass(): string;

    /**
     * @return string[]
     */
    abstract protected function getConditions(): array;

    /**
     * @return string[]
     */
    abstract protected function getSortations(): array;

    /**
     * @return string[]
     */
    abstract protected function getSearch(): array;

    /**
     * @param Builder         $builder
     * @param Closure(): void $addConditionsCallback
     * @param Closure(): void $addSortationsCallback
     * @param Closure(): void $addPaginationCallback
     *
     * @return void
     */
    abstract protected function configureAggregationBuilder(
        Builder $builder,
        Closure $addConditionsCallback,
        Closure $addSortationsCallback,
        Closure $addPaginationCallback,
    ): void;


    /**
     * GridAggregationFilterAbstract constructor.
     *
     * @param DocumentManager $dm
     */
    public function __construct(protected DocumentManager $dm)
    {
    }

    /**
     * @param GridRequestDtoInterface $gridRequestDto
     *
     * @return ResultData
     * @throws Exception
     */
    public function getData(GridRequestDtoInterface $gridRequestDto): ResultData
    {
        $documentClass = $this->getDocumentClass();

        $builder = $this
            ->dm
            ->getRepository($documentClass)
            ->createAggregationBuilder();

        $countBuilder = $this
            ->dm
            ->getRepository($documentClass)
            ->createAggregationBuilder();

        $this->configureAggregationBuilder(
            $builder,
            function () use ($gridRequestDto, $builder): void {
                $this->processConditions($gridRequestDto, $builder);
            },
            function () use ($gridRequestDto, $builder): void {
                $this->processSortations($gridRequestDto, $builder);
            },
            function () use ($gridRequestDto, $builder): void {
                $this->processPagination($gridRequestDto, $builder);
            },
        );

        $this->configureCountAggregationBuilder(
            $countBuilder,
            function () use ($gridRequestDto, $countBuilder): void {
                $this->processConditions($gridRequestDto, $countBuilder);
            },
        );

        $resultData = new ResultData(
            $builder
                ->getAggregation()
                ->getIterator()
                ->toArray(),
            static::DATE_FORMAT,
        );

        if ($gridRequestDto->getFilter() === []
            && $gridRequestDto->getSearch() === NULL
            && $this->canUseFasterCount($builder->getPipeline())) {
            $count = $this
                ->dm
                ->getRepository($documentClass)
                ->createQueryBuilder()
                ->count()
                ->getQuery()
                ->execute();
        } else {
            // @phpstan-ignore-next-line
            $count = $countBuilder
                ->count('count')
                ->getAggregation()
                ->getSingleResult()['count'] ?? 0;
        }

        $gridRequestDto->setTotal($count);

        return $resultData;
    }

    /**
     * @param Builder         $builder
     * @param Closure(): void $addConditionsCallback
     *
     * @return void
     */
    protected function configureCountAggregationBuilder(Builder $builder, Closure $addConditionsCallback): void
    {
        $builder;

        $addConditionsCallback();
    }

    /**
     * @return array<string, Closure(Builder, mixed[], string, Expr, ?string): void>
     */
    protected function getConditionsCallbacks(): array
    {
        return [];
    }

    /**
     * @return array<string, Closure(Builder): string[]>
     */
    protected function getSortationsCallbacks(): array
    {
        return [];
    }

    /**
     * @param GridRequestDtoInterface $dto
     * @param Builder                 $builder
     *
     * @throws Exception
     */
    private function processConditions(GridRequestDtoInterface $dto, Builder $builder): void
    {
        $expressions               = [];
        $searchConfig              = $this->getSearch();
        $conditionsConfig          = $this->getConditions();
        $conditionsCallbacksConfig = $this->getConditionsCallbacks();

        foreach ($dto->getFilter() as $andCondition) {
            $andExpression   = $builder->matchExpr();
            $hasOrExpression = FALSE;

            foreach ($andCondition as $orCondition) {
                $hasColumn         = array_key_exists(self::COLUMN, $orCondition);
                $hasOperator       = array_key_exists(self::OPERATOR, $orCondition);
                $hasValue          = array_key_exists(self::VALUE, $orCondition);
                $isNoValueOperator = in_array($orCondition[self::OPERATOR] ?? NULL, self::NO_VALUE_OPERATORS, TRUE);

                if (!$hasColumn || !$hasOperator || !$hasValue && !$isNoValueOperator) {
                    throw GridException::throwAdvancedFilterMissingRequiredFieldException();
                }

                if (!array_key_exists(self::VALUE, $orCondition)) {
                    $orCondition[self::VALUE] = '';
                }

                $column = $orCondition[self::COLUMN];

                if (!isset($conditionsConfig[$column])) {
                    throw GridException::throwMissingConditionColumnException($column, static::class);
                }

                $hasOrExpression          = TRUE;
                $orCondition[self::VALUE] = $this->processValue($orCondition[self::VALUE]);

                if (isset($conditionsCallbacksConfig[$column])) {
                    $orExpression = $builder->matchExpr();

                    $conditionsCallbacksConfig[$column](
                        $builder,
                        $orCondition[self::VALUE],
                        $conditionsConfig[$column],
                        $orExpression,
                        $orCondition[self::OPERATOR]
                    );

                    $andExpression->addOr($orExpression);

                    continue;
                }

                $andExpression->addOr(
                    self::getCondition(
                        $builder,
                        $conditionsConfig[$column],
                        $orCondition[self::VALUE],
                        $orCondition[self::OPERATOR],
                    ),
                );
            }

            if ($hasOrExpression) {
                $expressions[] = $andExpression;
            }
        }

        $search = $dto->getSearch();

        if ($search) {
            if (!$searchConfig) {
                throw GridException::throwMissingSearchException(static::class);
            }

            $searchExpression = $builder->matchExpr();

            foreach ($searchConfig as $column) {
                if (!array_key_exists($column, $conditionsConfig)) {
                    throw GridException::throwMissingSearchSearchException($column, static::class);
                }

                if (isset($conditionsCallbacksConfig[$column])) {
                    $andExpression = $builder->matchExpr();

                    $conditionsCallbacksConfig[$column](
                        $builder,
                        (array) $search,
                        $conditionsConfig[$column],
                        $andExpression,
                        NULL
                    );

                    $searchExpression->addOr($andExpression);

                    continue;
                }

                $searchExpression->addOr(self::getCondition($builder, $conditionsConfig[$column], $search, self::LIKE));
            }

            $expressions[] = $searchExpression;
        }

        if ($expressions) {
            $builder
                ->match()
                ->addAnd(...$expressions);
        }
    }

    /**
     * @param GridRequestDtoInterface $dto
     * @param Builder                 $builder
     *
     * @throws GridException
     */
    private function processSortations(GridRequestDtoInterface $dto, Builder $builder): void
    {
        $sortationsConfig          = $this->getSortations();
        $sortationsCallbacksConfig = $this->getSortationsCallbacks();
        $sortations                = [];
        $unsets                    = [];

        foreach ($dto->getOrderBy() as $sortation) {
            $column    = $sortation[self::COLUMN];
            $direction = $sortation[self::DIRECTION];

            if (!isset($sortationsConfig[$column])) {
                throw GridException::throwMissingSortationColumnException($column, static::class);
            }

            if (isset($sortationsCallbacksConfig[$column])) {
                $unsets = [...$unsets, ...$sortationsCallbacksConfig[$column]($builder)];
            }

            $sortations[$sortationsConfig[$column]] = $direction;
        }

        if ($sortations === []) {
            return;
        }

        $builder->sort($sortations);

        if ($unsets !== []) {
            $builder->unset(...$unsets);
        }
    }

    /**
     * @param GridRequestDtoInterface $dto
     * @param Builder                 $builder
     */
    private function processPagination(GridRequestDtoInterface $dto, Builder $builder): void
    {
        $page         = $dto->getPage();
        $itemsPerPage = $dto->getItemsPerPage();

        // @phpstan-ignore-next-line
        $builder
            ->skip(--$page * $itemsPerPage)
            ->limit($itemsPerPage);
    }

    /**
     * @param mixed $value
     *
     * @return mixed[]
     * @throws Exception
     */
    private function processValue(mixed $value): array
    {
        $values = $value;

        if (!is_array($value)) {
            $values = [$value];
        }

        foreach ($values as $index => $innerValue) {
            if (is_string($innerValue) && preg_match('/\d{4}-\d{2}-\d{2}.\d{2}:\d{2}:\d{2}/', $innerValue)) {
                $values[$index] = new DateTime($innerValue);
            }
        }

        return $values;
    }

    /**
     * @param mixed[] $pipeline
     *
     * @return bool
     */
    private function canUseFasterCount(array $pipeline): bool
    {
        foreach ($pipeline as $key => $stage) {
            if ($key === '$group') {
                return FALSE;
            }

            if (is_array($stage)) {
                if (!$this->canUseFasterCount($stage)) {
                    return FALSE;
                }
            }
        }

        return TRUE;
    }

    /**
     * @param Builder $builder
     * @param string  $name
     * @param mixed   $value
     * @param ?string $operator
     *
     * @return Expr
     */
    private static function getCondition(Builder $builder, string $name, mixed $value, ?string $operator = NULL): Expr
    {
        return match ($operator) {
            self::EQ, self::IN => is_array($value) && count($value) > 1
                ? $builder->matchExpr()->field($name)->in($value)
                : $builder->matchExpr()->field($name)->equals(self::getValue($value)),
            self::NEQ, self::NIN => is_array($value) && count($value) > 1
                ? $builder->matchExpr()->field($name)->notIn($value)
                : $builder->matchExpr()->field($name)->notEqual(self::getValue($value)),
            self::GTE => $builder->matchExpr()->field($name)->gte(self::getValue($value)),
            self::GT => $builder->matchExpr()->field($name)->gt(self::getValue($value)),
            self::LTE => $builder->matchExpr()->field($name)->lte(self::getValue($value)),
            self::LT => $builder->matchExpr()->field($name)->lt(self::getValue($value)),
            self::NEMPTY => $builder->matchExpr()
                ->addOr($builder->matchExpr()->field($name)->notEqual(NULL))
                ->addOr($builder->matchExpr()->field($name)->notEqual(self::getValue($value))),
            self::EMPTY => $builder->matchExpr()
                ->addOr($builder->matchExpr()->field($name)->equals(NULL))
                ->addOr($builder->matchExpr()->field($name)->equals(self::getValue($value))),
            self::LIKE => $builder->matchExpr()->field($name)->equals(
                new Regex(sprintf('%s', preg_quote(self::getValue($value))), 'i'),
            ),
            self::STARTS => $builder->matchExpr()->field($name)->equals(
                new Regex(sprintf('^%s', preg_quote(self::getValue($value))), 'i'),
            ),
            self::ENDS => $builder->matchExpr()->field($name)->equals(
                new Regex(sprintf('%s$', preg_quote(self::getValue($value))), 'i'),
            ),
            self::BETWEEN => is_array($value) && count($value) >= 2
                ? $builder->matchExpr()->field($name)->gte($value[0])->field($name)->lte($value[1])
                : $builder->matchExpr()->field($name)->equals(self::getValue($value)),
            self::NBETWEEN => is_array($value) && count($value) >= 2
                ? $builder->matchExpr()->addOr(
                    $builder->matchExpr()->field($name)->lte($value[0]),
                    $builder->matchExpr()->field($name)->gte($value[1]),
                ) : $builder->matchExpr()->field($name)->notEqual(self::getValue($value)),
            self::EXIST => $builder->matchExpr()->field($name)->exists(TRUE),
            self::NEXIST => $builder->matchExpr()->field($name)->exists(FALSE),
            default => $builder->matchExpr()->field($name)->equals(self::getValue($value)),
        };
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
