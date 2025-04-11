<?php declare(strict_types=1);

namespace Hanaboso\MongoDataGrid\Exception;

use Exception;

/**
 * Class GridException
 *
 * @package Hanaboso\MongoDataGrid\Exception
 */
final class GridException extends Exception
{

    public const int FILTER_COLS_ERROR     = 1;
    public const int ORDER_COLS_ERROR      = 2;
    public const int SEARCHABLE_COLS_ERROR = 3;
    public const int SORT_COLS_ERROR       = 4;

}
