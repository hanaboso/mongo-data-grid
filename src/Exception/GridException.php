<?php declare(strict_types=1);

namespace Hanaboso\MongoDataGrid\Exception;

use Exception;
use Hanaboso\MongoDataGrid\GridAggregationFilterAbstract;

/**
 * Class GridException
 *
 * @package Hanaboso\MongoDataGrid\Exception
 */
final class GridException extends Exception
{

    public const int MISSING_CONDITION_COLUMN               = 1;
    public const int MISSING_SORTATION_COLUMN               = 2;
    public const int MISSING_SEARCH_COLUMN                  = 3;
    public const int MISSING_ADVANCED_FILTER_REQUIRED_FIELD = 4;

    /**
     * @return GridException
     */
    public static function throwAdvancedFilterMissingRequiredFieldException(): self {
        return new self(
            sprintf(
                "Missing one of required advanced filter fields ('%s', '%s' or '%s')!",
                GridAggregationFilterAbstract::COLUMN,
                GridAggregationFilterAbstract::OPERATOR,
                GridAggregationFilterAbstract::VALUE,
            ),
            self::MISSING_ADVANCED_FILTER_REQUIRED_FIELD,
        );
    }

    /**
     * @param string $column
     * @param string $class
     *
     * @return GridException
     */
    public static function throwMissingConditionColumnException(string $column, string $class): self {
        return new self(
            sprintf(
                "Column '%s' cannot be used as condition! Have you forgotten add it to '%s::getConditions'?",
                $column,
                $class,
            ),
            self::MISSING_CONDITION_COLUMN,
        );
    }

    /**
     * @param string $column
     * @param string $class
     *
     * @return GridException
     */
    public static function throwMissingSortationColumnException(string $column, string $class): self {
        return new self(
            sprintf(
                "Column '%s' cannot be used as sortation! Have you forgotten add it to '%s::getSortations'?",
                $column,
                $class,
            ),
            self::MISSING_SORTATION_COLUMN,
        );
    }

    /**
     * @param string $class
     *
     * @return GridException
     */
    public static function throwMissingSearchException(string $class): self {
        return new self(
            sprintf("Column cannot be used for searching! Have you forgotten add it to '%s::getSearch'?", $class),
            self::MISSING_SEARCH_COLUMN,
        );
    }

    /**
     * @param string $column
     * @param string $class
     *
     * @return GridException
     */
    public static function throwMissingSearchSearchException(string $column, string $class): self {
        return new self(
            sprintf(
                "Column '%s' cannot be used for searching! Have you forgotten add it to '%s::getSortations'?",
                $column,
                $class,
            ),
            self::MISSING_SEARCH_COLUMN,
        );
    }

}
