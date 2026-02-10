<?php declare(strict_types=1);

namespace MongoDataGridTests\Filter;

use Closure;
use Doctrine\ODM\MongoDB\Aggregation\Builder;
use Doctrine\ODM\MongoDB\Query\Expr;
use Hanaboso\MongoDataGrid\GridAggregationFilterAbstract;
use Hanaboso\Utils\Date\DateTimeUtils;
use MongoDataGridTests\Document\AggregationDocument;

/**
 * Class AggregationDocumentFilter
 *
 * @package MongoDataGridTests\Filter
 */
final class AggregationDocumentFilter extends GridAggregationFilterAbstract
{

    protected const string DATE_FORMAT = DateTimeUtils::DATE_TIME;

    /**
     * @return class-string
     */
    protected function getDocumentClass(): string
    {
        return AggregationDocument::class;
    }

    /**
     * @return string[]
     */
    protected function getConditions(): array
    {
        return [
            'bool'          => 'bool',
            'custom_string' => 'string',
            'date'          => 'date',
            'float'         => 'float',
            'id'            => '_id',
            'int'           => 'int',
            'string'        => 'string',
            'string2'       => 'string2',
        ];
    }

    /**
     * @return string[]
     */
    protected function getSortations(): array
    {
        return [
            'bool'          => 'bool',
            'custom_string' => 'string',
            'date'          => 'date',
            'float'         => 'float',
            'id'            => '_id',
            'int'           => 'int',
            'string'        => 'string',
        ];
    }

    /**
     * @return string[]
     */
    protected function getSearch(): array
    {
        return [
            'string',
            'custom_string',
        ];
    }

    /**
     * @param Builder         $builder
     * @param Closure(): void $addConditionsCallback
     * @param Closure(): void $addSortationsCallback
     * @param Closure(): void $addPaginationCallback
     *
     * @return void
     */
    protected function configureAggregationBuilder(
        Builder $builder,
        Closure $addConditionsCallback,
        Closure $addSortationsCallback,
        Closure $addPaginationCallback,
    ): void {
        $builder;

        $addConditionsCallback();
        $addSortationsCallback();
        $addPaginationCallback();
    }

    /**
     * @return array<string, Closure(Builder, mixed[], string, Expr, ?string): void>
     */
    protected function getConditionsCallbacks(): array
    {
        return [
            'custom_string' => static function (
                Builder $builder,
                array $value,
                string $name,
                Expr $expr,
                ?string $operator,
            ): void {
                $builder;
                $operator;

                $expr->field($name)->equals($value[0] ?? $value);
            },
        ];
    }

    /**
     * @return array<string, Closure(Builder): string[]>
     */
    protected function getSortationsCallbacks(): array
    {
        return [
            'custom_string' => static function (Builder $builder): array {
                $builder
                    ->addFields()
                    ->field('customString')
                    ->expression('$string');

                return ['customString'];
            },
        ];
    }

}
