<?php declare(strict_types=1);

namespace MongoDataGridTests\Integration;

use DateTime;
use DateTimeZone;
use Exception;
use Hanaboso\MongoDataGrid\Exception\GridException;
use Hanaboso\MongoDataGrid\GridFilterAbstract;
use Hanaboso\MongoDataGrid\GridRequestDto;
use MongoDataGridTests\Document\AggregationDocument;
use MongoDataGridTests\Filter\AggregationDocumentFilter;
use MongoDataGridTests\TestCaseAbstract;
use MongoDB\Driver\Exception\CommandException;

/**
 * Class AggregationFilterTest
 *
 * @package MongoDataGridTests\Integration
 */
final class AggregationFilterTest extends TestCaseAbstract
{

    protected const string PAGING = 'paging';

    private const string DATETIME       = 'Y-m-d H:i:s';
    private const string SORTER         = 'sorter';
    private const string FILTER         = 'filter';
    private const string PAGE           = 'page';
    private const string SEARCH         = 'search';
    private const string ITEMS_PER_PAGE = 'itemsPerPage';

    /**
     * @var DateTime
     */
    private DateTime $today;

    /**
     * @throws Exception
     */
    public function testBasic(): void
    {
        $result = (new AggregationDocumentFilter($this->dm))->getData(new GridRequestDto([]))->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => TRUE,
                    'date'   => $this->today->format(self::DATETIME),
                    'float'  => 0.0,
                    'id'     => $result[0]['id'],
                    'int'    => 0,
                    'string' => 'String 0',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 1.1,
                    'id'     => $result[1]['id'],
                    'int'    => 1,
                    'string' => 'String 1',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 2.2,
                    'id'     => $result[2]['id'],
                    'int'    => 2,
                    'string' => 'String 2',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 3.3,
                    'id'     => $result[3]['id'],
                    'int'    => 3,
                    'string' => 'String 3',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 4.4,
                    'id'     => $result[4]['id'],
                    'int'    => 4,
                    'string' => 'String 4',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 5.5,
                    'id'     => $result[5]['id'],
                    'int'    => 5,
                    'string' => 'String 5',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 6.6,
                    'id'     => $result[6]['id'],
                    'int'    => 6,
                    'string' => 'String 6',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 7.7,
                    'id'     => $result[7]['id'],
                    'int'    => 7,
                    'string' => 'String 7',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 8.8,
                    'id'     => $result[8]['id'],
                    'int'    => 8,
                    'string' => 'String 8',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 9.9,
                    'id'     => $result[9]['id'],
                    'int'    => 9,
                    'string' => 'String 9',
                ],
            ],
            $result,
        );
    }

    /**
     * @throws Exception
     */
    public function testSortations(): void
    {
        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::SORTER => [
                        [
                            'column'    => 'id',
                            'direction' => 'ASC',
                        ],
                    ],
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => TRUE,
                    'date'   => $this->today->format(self::DATETIME),
                    'float'  => 0.0,
                    'id'     => $result[0]['id'],
                    'int'    => 0,
                    'string' => 'String 0',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 1.1,
                    'id'     => $result[1]['id'],
                    'int'    => 1,
                    'string' => 'String 1',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 2.2,
                    'id'     => $result[2]['id'],
                    'int'    => 2,
                    'string' => 'String 2',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 3.3,
                    'id'     => $result[3]['id'],
                    'int'    => 3,
                    'string' => 'String 3',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 4.4,
                    'id'     => $result[4]['id'],
                    'int'    => 4,
                    'string' => 'String 4',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 5.5,
                    'id'     => $result[5]['id'],
                    'int'    => 5,
                    'string' => 'String 5',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 6.6,
                    'id'     => $result[6]['id'],
                    'int'    => 6,
                    'string' => 'String 6',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 7.7,
                    'id'     => $result[7]['id'],
                    'int'    => 7,
                    'string' => 'String 7',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 8.8,
                    'id'     => $result[8]['id'],
                    'int'    => 8,
                    'string' => 'String 8',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 9.9,
                    'id'     => $result[9]['id'],
                    'int'    => 9,
                    'string' => 'String 9',
                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::SORTER => [
                        [
                            'column'    => 'id',
                            'direction' => 'DESC',
                        ],
                    ],
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => FALSE,
                    'date'   => $this->today->format(self::DATETIME),
                    'float'  => 9.9,
                    'id'     => $result[0]['id'],
                    'int'    => 9,
                    'string' => 'String 9',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 8.8,
                    'id'     => $result[1]['id'],
                    'int'    => 8,
                    'string' => 'String 8',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 7.7,
                    'id'     => $result[2]['id'],
                    'int'    => 7,
                    'string' => 'String 7',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 6.6,
                    'id'     => $result[3]['id'],
                    'int'    => 6,
                    'string' => 'String 6',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 5.5,
                    'id'     => $result[4]['id'],
                    'int'    => 5,
                    'string' => 'String 5',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 4.4,
                    'id'     => $result[5]['id'],
                    'int'    => 4,
                    'string' => 'String 4',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 3.3,
                    'id'     => $result[6]['id'],
                    'int'    => 3,
                    'string' => 'String 3',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 2.2,
                    'id'     => $result[7]['id'],
                    'int'    => 2,
                    'string' => 'String 2',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 1.1,
                    'id'     => $result[8]['id'],
                    'int'    => 1,
                    'string' => 'String 1',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 0.0,
                    'id'     => $result[9]['id'],
                    'int'    => 0,
                    'string' => 'String 0',
                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::SORTER => [
                        [
                            'column'    => 'string',
                            'direction' => 'ASC',
                        ],
                    ],
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => TRUE,
                    'date'   => $this->today->format(self::DATETIME),
                    'float'  => 0.0,
                    'id'     => $result[0]['id'],
                    'int'    => 0,
                    'string' => 'String 0',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 1.1,
                    'id'     => $result[1]['id'],
                    'int'    => 1,
                    'string' => 'String 1',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 2.2,
                    'id'     => $result[2]['id'],
                    'int'    => 2,
                    'string' => 'String 2',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 3.3,
                    'id'     => $result[3]['id'],
                    'int'    => 3,
                    'string' => 'String 3',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 4.4,
                    'id'     => $result[4]['id'],
                    'int'    => 4,
                    'string' => 'String 4',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 5.5,
                    'id'     => $result[5]['id'],
                    'int'    => 5,
                    'string' => 'String 5',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 6.6,
                    'id'     => $result[6]['id'],
                    'int'    => 6,
                    'string' => 'String 6',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 7.7,
                    'id'     => $result[7]['id'],
                    'int'    => 7,
                    'string' => 'String 7',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 8.8,
                    'id'     => $result[8]['id'],
                    'int'    => 8,
                    'string' => 'String 8',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 9.9,
                    'id'     => $result[9]['id'],
                    'int'    => 9,
                    'string' => 'String 9',
                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::SORTER => [
                        [
                            'column'    => 'string',
                            'direction' => 'DESC',
                        ],
                    ],
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => FALSE,
                    'date'   => $this->today->format(self::DATETIME),
                    'float'  => 9.9,
                    'id'     => $result[0]['id'],
                    'int'    => 9,
                    'string' => 'String 9',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 8.8,
                    'id'     => $result[1]['id'],
                    'int'    => 8,
                    'string' => 'String 8',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 7.7,
                    'id'     => $result[2]['id'],
                    'int'    => 7,
                    'string' => 'String 7',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 6.6,
                    'id'     => $result[3]['id'],
                    'int'    => 6,
                    'string' => 'String 6',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 5.5,
                    'id'     => $result[4]['id'],
                    'int'    => 5,
                    'string' => 'String 5',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 4.4,
                    'id'     => $result[5]['id'],
                    'int'    => 4,
                    'string' => 'String 4',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 3.3,
                    'id'     => $result[6]['id'],
                    'int'    => 3,
                    'string' => 'String 3',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 2.2,
                    'id'     => $result[7]['id'],
                    'int'    => 2,
                    'string' => 'String 2',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 1.1,
                    'id'     => $result[8]['id'],
                    'int'    => 1,
                    'string' => 'String 1',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 0.0,
                    'id'     => $result[9]['id'],
                    'int'    => 0,
                    'string' => 'String 0',
                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::SORTER => [
                        [
                            'column'    => 'int',
                            'direction' => 'ASC',
                        ],
                    ],
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => TRUE,
                    'date'   => $this->today->format(self::DATETIME),
                    'float'  => 0.0,
                    'id'     => $result[0]['id'],
                    'int'    => 0,
                    'string' => 'String 0',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 1.1,
                    'id'     => $result[1]['id'],
                    'int'    => 1,
                    'string' => 'String 1',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 2.2,
                    'id'     => $result[2]['id'],
                    'int'    => 2,
                    'string' => 'String 2',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 3.3,
                    'id'     => $result[3]['id'],
                    'int'    => 3,
                    'string' => 'String 3',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 4.4,
                    'id'     => $result[4]['id'],
                    'int'    => 4,
                    'string' => 'String 4',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 5.5,
                    'id'     => $result[5]['id'],
                    'int'    => 5,
                    'string' => 'String 5',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 6.6,
                    'id'     => $result[6]['id'],
                    'int'    => 6,
                    'string' => 'String 6',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 7.7,
                    'id'     => $result[7]['id'],
                    'int'    => 7,
                    'string' => 'String 7',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 8.8,
                    'id'     => $result[8]['id'],
                    'int'    => 8,
                    'string' => 'String 8',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 9.9,
                    'id'     => $result[9]['id'],
                    'int'    => 9,
                    'string' => 'String 9',
                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::SORTER => [
                        [
                            'column'    => 'int',
                            'direction' => 'DESC',
                        ],
                    ],
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => FALSE,
                    'date'   => $this->today->format(self::DATETIME),
                    'float'  => 9.9,
                    'id'     => $result[0]['id'],
                    'int'    => 9,
                    'string' => 'String 9',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 8.8,
                    'id'     => $result[1]['id'],
                    'int'    => 8,
                    'string' => 'String 8',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 7.7,
                    'id'     => $result[2]['id'],
                    'int'    => 7,
                    'string' => 'String 7',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 6.6,
                    'id'     => $result[3]['id'],
                    'int'    => 6,
                    'string' => 'String 6',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 5.5,
                    'id'     => $result[4]['id'],
                    'int'    => 5,
                    'string' => 'String 5',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 4.4,
                    'id'     => $result[5]['id'],
                    'int'    => 4,
                    'string' => 'String 4',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 3.3,
                    'id'     => $result[6]['id'],
                    'int'    => 3,
                    'string' => 'String 3',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 2.2,
                    'id'     => $result[7]['id'],
                    'int'    => 2,
                    'string' => 'String 2',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 1.1,
                    'id'     => $result[8]['id'],
                    'int'    => 1,
                    'string' => 'String 1',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 0.0,
                    'id'     => $result[9]['id'],
                    'int'    => 0,
                    'string' => 'String 0',
                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::SORTER => [
                        [
                            'column'    => 'float',
                            'direction' => 'ASC',
                        ],
                    ],
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => TRUE,
                    'date'   => $this->today->format(self::DATETIME),
                    'float'  => 0.0,
                    'id'     => $result[0]['id'],
                    'int'    => 0,
                    'string' => 'String 0',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 1.1,
                    'id'     => $result[1]['id'],
                    'int'    => 1,
                    'string' => 'String 1',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 2.2,
                    'id'     => $result[2]['id'],
                    'int'    => 2,
                    'string' => 'String 2',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 3.3,
                    'id'     => $result[3]['id'],
                    'int'    => 3,
                    'string' => 'String 3',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 4.4,
                    'id'     => $result[4]['id'],
                    'int'    => 4,
                    'string' => 'String 4',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 5.5,
                    'id'     => $result[5]['id'],
                    'int'    => 5,
                    'string' => 'String 5',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 6.6,
                    'id'     => $result[6]['id'],
                    'int'    => 6,
                    'string' => 'String 6',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 7.7,
                    'id'     => $result[7]['id'],
                    'int'    => 7,
                    'string' => 'String 7',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 8.8,
                    'id'     => $result[8]['id'],
                    'int'    => 8,
                    'string' => 'String 8',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 9.9,
                    'id'     => $result[9]['id'],
                    'int'    => 9,
                    'string' => 'String 9',
                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::SORTER => [
                        [
                            'column'    => 'float',
                            'direction' => 'DESC',
                        ],
                    ],
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => FALSE,
                    'date'   => $this->today->format(self::DATETIME),
                    'float'  => 9.9,
                    'id'     => $result[0]['id'],
                    'int'    => 9,
                    'string' => 'String 9',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 8.8,
                    'id'     => $result[1]['id'],
                    'int'    => 8,
                    'string' => 'String 8',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 7.7,
                    'id'     => $result[2]['id'],
                    'int'    => 7,
                    'string' => 'String 7',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 6.6,
                    'id'     => $result[3]['id'],
                    'int'    => 6,
                    'string' => 'String 6',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 5.5,
                    'id'     => $result[4]['id'],
                    'int'    => 5,
                    'string' => 'String 5',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 4.4,
                    'id'     => $result[5]['id'],
                    'int'    => 4,
                    'string' => 'String 4',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 3.3,
                    'id'     => $result[6]['id'],
                    'int'    => 3,
                    'string' => 'String 3',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 2.2,
                    'id'     => $result[7]['id'],
                    'int'    => 2,
                    'string' => 'String 2',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 1.1,
                    'id'     => $result[8]['id'],
                    'int'    => 1,
                    'string' => 'String 1',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 0.0,
                    'id'     => $result[9]['id'],
                    'int'    => 0,
                    'string' => 'String 0',
                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::SORTER => [
                        [
                            'column'    => 'bool',
                            'direction' => 'ASC',
                        ],
                    ],
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('7 day')->format(self::DATETIME),
                    'float'  => 7.7,
                    'id'     => $result[0]['id'],
                    'int'    => 7,
                    'string' => 'String 7',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('2 day')->format(self::DATETIME),
                    'float'  => 9.9,
                    'id'     => $result[1]['id'],
                    'int'    => 9,
                    'string' => 'String 9',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('- 6 day')->format(self::DATETIME),
                    'float'  => 3.3,
                    'id'     => $result[2]['id'],
                    'int'    => 3,
                    'string' => 'String 3',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('2 day')->format(self::DATETIME),
                    'float'  => 5.5,
                    'id'     => $result[3]['id'],
                    'int'    => 5,
                    'string' => 'String 5',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('- 4 day')->format(self::DATETIME),
                    'float'  => 1.1,
                    'id'     => $result[4]['id'],
                    'int'    => 1,
                    'string' => 'String 1',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('7 day')->format(self::DATETIME),
                    'float'  => 8.8,
                    'id'     => $result[5]['id'],
                    'int'    => 8,
                    'string' => 'String 8',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('- 4 day')->format(self::DATETIME),
                    'float'  => 4.4,
                    'id'     => $result[6]['id'],
                    'int'    => 4,
                    'string' => 'String 4',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('- 4 day')->format(self::DATETIME),
                    'float'  => 0.0,
                    'id'     => $result[7]['id'],
                    'int'    => 0,
                    'string' => 'String 0',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('2 day')->format(self::DATETIME),
                    'float'  => 2.2,
                    'id'     => $result[8]['id'],
                    'int'    => 2,
                    'string' => 'String 2',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('4 day')->format(self::DATETIME),
                    'float'  => 6.6,
                    'id'     => $result[9]['id'],
                    'int'    => 6,
                    'string' => 'String 6',
                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::SORTER => [
                        [
                            'column'    => 'bool',
                            'direction' => 'DESC',
                        ],
                    ],
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('2 day')->format(self::DATETIME),
                    'float'  => 8.8,
                    'id'     => $result[0]['id'],
                    'int'    => 8,
                    'string' => 'String 8',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-4 day')->format(self::DATETIME),
                    'float'  => 4.4,
                    'id'     => $result[1]['id'],
                    'int'    => 4,
                    'string' => 'String 4',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-4 day')->format(self::DATETIME),
                    'float'  => 0.0,
                    'id'     => $result[2]['id'],
                    'int'    => 0,
                    'string' => 'String 0',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('2 day')->format(self::DATETIME),
                    'float'  => 2.2,
                    'id'     => $result[3]['id'],
                    'int'    => 2,
                    'string' => 'String 2',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('4 day')->format(self::DATETIME),
                    'float'  => 6.6,
                    'id'     => $result[4]['id'],
                    'int'    => 6,
                    'string' => 'String 6',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('-3 day')->format(self::DATETIME),
                    'float'  => 3.3,
                    'id'     => $result[5]['id'],
                    'int'    => 3,
                    'string' => 'String 3',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('4 day')->format(self::DATETIME),
                    'float'  => 7.7,
                    'id'     => $result[6]['id'],
                    'int'    => 7,
                    'string' => 'String 7',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('- 6 day')->format(self::DATETIME),
                    'float'  => 1.1,
                    'id'     => $result[7]['id'],
                    'int'    => 1,
                    'string' => 'String 1',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('8 day')->format(self::DATETIME),
                    'float'  => 9.9,
                    'id'     => $result[8]['id'],
                    'int'    => 9,
                    'string' => 'String 9',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('- 4 day')->format(self::DATETIME),
                    'float'  => 5.5,
                    'id'     => $result[9]['id'],
                    'int'    => 5,
                    'string' => 'String 5',
                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::SORTER => [
                        [
                            'column'    => 'date',
                            'direction' => 'ASC',
                        ],
                    ],
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-5 day')->format(self::DATETIME),
                    'float'  => 0.0,
                    'id'     => $result[0]['id'],
                    'int'    => 0,
                    'string' => 'String 0',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 1.1,
                    'id'     => $result[1]['id'],
                    'int'    => 1,
                    'string' => 'String 1',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 2.2,
                    'id'     => $result[2]['id'],
                    'int'    => 2,
                    'string' => 'String 2',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 3.3,
                    'id'     => $result[3]['id'],
                    'int'    => 3,
                    'string' => 'String 3',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 4.4,
                    'id'     => $result[4]['id'],
                    'int'    => 4,
                    'string' => 'String 4',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 5.5,
                    'id'     => $result[5]['id'],
                    'int'    => 5,
                    'string' => 'String 5',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 6.6,
                    'id'     => $result[6]['id'],
                    'int'    => 6,
                    'string' => 'String 6',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 7.7,
                    'id'     => $result[7]['id'],
                    'int'    => 7,
                    'string' => 'String 7',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 8.8,
                    'id'     => $result[8]['id'],
                    'int'    => 8,
                    'string' => 'String 8',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 9.9,
                    'id'     => $result[9]['id'],
                    'int'    => 9,
                    'string' => 'String 9',
                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::SORTER => [
                        [
                            'column'    => 'date',
                            'direction' => 'DESC',
                        ],
                    ],
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => FALSE,
                    'date'   => $this->today->format(self::DATETIME),
                    'float'  => 9.9,
                    'id'     => $result[0]['id'],
                    'int'    => 9,
                    'string' => 'String 9',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 8.8,
                    'id'     => $result[1]['id'],
                    'int'    => 8,
                    'string' => 'String 8',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 7.7,
                    'id'     => $result[2]['id'],
                    'int'    => 7,
                    'string' => 'String 7',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 6.6,
                    'id'     => $result[3]['id'],
                    'int'    => 6,
                    'string' => 'String 6',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 5.5,
                    'id'     => $result[4]['id'],
                    'int'    => 5,
                    'string' => 'String 5',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 4.4,
                    'id'     => $result[5]['id'],
                    'int'    => 4,
                    'string' => 'String 4',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 3.3,
                    'id'     => $result[6]['id'],
                    'int'    => 3,
                    'string' => 'String 3',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 2.2,
                    'id'     => $result[7]['id'],
                    'int'    => 2,
                    'string' => 'String 2',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 1.1,
                    'id'     => $result[8]['id'],
                    'int'    => 1,
                    'string' => 'String 1',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 0.0,
                    'id'     => $result[9]['id'],
                    'int'    => 0,
                    'string' => 'String 0',
                ],
            ],
            $result,
        );

        try {
            (new AggregationDocumentFilter($this->dm))->getData(
                new GridRequestDto(
                    [
                        self::SORTER => [
                            [
                                'column'    => 'Unknown',
                                'direction' => 'ASC',
                            ],
                        ],
                    ],
                ),
            )->toArray();
            self::fail();
        } catch (Exception $e) {
            self::assertEquals(GridException::MISSING_SORTATION_COLUMN, $e->getCode());
            self::assertSame(
                "Column 'Unknown' cannot be used as sortation! Have you forgotten add it to 'MongoDataGridTests\Filter\AggregationDocumentFilter::getSortations'?",
                $e->getMessage(),
            );
        }
    }

    /**
     * @throws Exception
     */
    public function testAdvancedSortations(): void
    {
        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::SORTER => [
                        [
                            'column'    => 'custom_string',
                            'direction' => 'ASC',
                        ],
                    ],
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => TRUE,
                    'date'   => $this->today->format(self::DATETIME),
                    'float'  => 0.0,
                    'id'     => $result[0]['id'],
                    'int'    => 0,
                    'string' => 'String 0',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 1.1,
                    'id'     => $result[1]['id'],
                    'int'    => 1,
                    'string' => 'String 1',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 2.2,
                    'id'     => $result[2]['id'],
                    'int'    => 2,
                    'string' => 'String 2',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 3.3,
                    'id'     => $result[3]['id'],
                    'int'    => 3,
                    'string' => 'String 3',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 4.4,
                    'id'     => $result[4]['id'],
                    'int'    => 4,
                    'string' => 'String 4',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 5.5,
                    'id'     => $result[5]['id'],
                    'int'    => 5,
                    'string' => 'String 5',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 6.6,
                    'id'     => $result[6]['id'],
                    'int'    => 6,
                    'string' => 'String 6',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 7.7,
                    'id'     => $result[7]['id'],
                    'int'    => 7,
                    'string' => 'String 7',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 8.8,
                    'id'     => $result[8]['id'],
                    'int'    => 8,
                    'string' => 'String 8',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 9.9,
                    'id'     => $result[9]['id'],
                    'int'    => 9,
                    'string' => 'String 9',
                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::SORTER => [
                        [
                            'column'    => 'custom_string',
                            'direction' => 'DESC',
                        ],
                    ],
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => FALSE,
                    'date'   => $this->today->format(self::DATETIME),
                    'float'  => 9.9,
                    'id'     => $result[0]['id'],
                    'int'    => 9,
                    'string' => 'String 9',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 8.8,
                    'id'     => $result[1]['id'],
                    'int'    => 8,
                    'string' => 'String 8',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 7.7,
                    'id'     => $result[2]['id'],
                    'int'    => 7,
                    'string' => 'String 7',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 6.6,
                    'id'     => $result[3]['id'],
                    'int'    => 6,
                    'string' => 'String 6',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 5.5,
                    'id'     => $result[4]['id'],
                    'int'    => 5,
                    'string' => 'String 5',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 4.4,
                    'id'     => $result[5]['id'],
                    'int'    => 4,
                    'string' => 'String 4',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 3.3,
                    'id'     => $result[6]['id'],
                    'int'    => 3,
                    'string' => 'String 3',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 2.2,
                    'id'     => $result[7]['id'],
                    'int'    => 2,
                    'string' => 'String 2',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 1.1,
                    'id'     => $result[8]['id'],
                    'int'    => 1,
                    'string' => 'String 1',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 0.0,
                    'id'     => $result[9]['id'],
                    'int'    => 0,
                    'string' => 'String 0',
                ],
            ],
            $result,
        );
    }

    /**
     * @throws Exception
     */
    public function testConditions(): void
    {
        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::FILTER => [
                        [
                            [
                                'column'   => 'string',
                                'operator' => 'EQ',
                                'value'    => ['String 1'],
                            ],
                        ],
                    ],
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 1.1,
                    'id'     => $result[0]['id'],
                    'int'    => 1,
                    'string' => 'String 1',
                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::FILTER => [
                        [
                            [
                                'column'   => 'int',
                                'operator' => 'EQ',
                                'value'    => [2],
                            ],
                        ],
                    ],
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 2.2,
                    'id'     => $result[0]['id'],
                    'int'    => 2,
                    'string' => 'String 2',
                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::FILTER => [
                        [
                            [
                                'column'   => 'float',
                                'operator' => 'EQ',
                                'value'    => [3.3],
                            ],
                        ],
                    ],
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 3.3,
                    'id'     => $result[0]['id'],
                    'int'    => 3,
                    'string' => 'String 3',
                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::FILTER => [
                        [
                            [
                                'column'   => 'bool',
                                'operator' => 'EQ',
                                'value'    => [TRUE],
                            ],
                        ],
                        [
                            [
                                'column'   => 'string',
                                'operator' => 'EQ',
                                'value'    => ['String 4'],
                            ],
                        ],
                    ],
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 4.4,
                    'id'     => $result[0]['id'],
                    'int'    => 4,
                    'string' => 'String 4',
                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::FILTER => [
                        [
                            [
                                'column'   => 'date',
                                'operator' => 'EQ',
                                'value'    => [(clone $this->today)->modify('1 day')->format(self::DATETIME)],
                            ],
                        ],
                    ],
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 5.5,
                    'id'     => $result[0]['id'],
                    'int'    => 5,
                    'string' => 'String 5',
                ],
            ],
            $result,
        );

        $dto    = new GridRequestDto(
            [
                self::FILTER => [
                    [
                        [
                            'column'   => 'int',
                            'operator' => 'EQ',
                            'value'    => [6, 7, 8],
                        ],
                    ],
                ],
            ],
        );
        $result = (new AggregationDocumentFilter($this->dm))->getData($dto)->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 6.6,
                    'id'     => $result[0]['id'],
                    'int'    => 6,
                    'string' => 'String 6',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 7.7,
                    'id'     => $result[1]['id'],
                    'int'    => 7,
                    'string' => 'String 7',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 8.8,
                    'id'     => $result[2]['id'],
                    'int'    => 8,
                    'string' => 'String 8',
                ],
            ],
            $result,
        );
        self::assertEquals(
            [
                'filter'       => '[[{"column":"int","operator":"EQ","value":[6,7,8]}]]',
                'itemsPerPage' => 10,
                'page'         => 1,
                'search'       => NULL,
                'sorter'       => NULL,
                'total'        => 3,
            ],
            $dto->getParamsForHeader(),
        );
        self::assertSame(3, $dto->getTotal());

        $dto    = new GridRequestDto([self::SEARCH => 'String 9']);
        $result = (new AggregationDocumentFilter($this->dm))->getData($dto)->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 9.9,
                    'id'     => $result[0]['id'],
                    'int'    => 9,
                    'string' => 'String 9',

                ],
            ],
            $result,
        );
        self::assertEquals(
            [
                'filter'       => '[]',
                'itemsPerPage' => 10,
                'page'         => 1,
                'search'       => 'String 9',
                'sorter'       => NULL,
                'total'        => 1,
            ],
            $dto->getParamsForHeader(),
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::FILTER => [
                        [
                            [
                                'column'   => 'int',
                                'operator' => 'GTE',
                                'value'    => [8],
                            ],
                        ],
                    ],
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 8.8,
                    'id'     => $result[0]['id'],
                    'int'    => 8,
                    'string' => 'String 8',

                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 9.9,
                    'id'     => $result[1]['id'],
                    'int'    => 9,
                    'string' => 'String 9',

                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::FILTER => [
                        [
                            [
                                'column'   => 'int',
                                'operator' => 'GT',
                                'value'    => [8],
                            ],
                        ],
                    ],
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => FALSE,
                    'date'   => $this->today->format(self::DATETIME),
                    'float'  => 9.9,
                    'id'     => $result[0]['id'],
                    'int'    => 9,
                    'string' => 'String 9',

                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::FILTER => [
                        [
                            [
                                'column'   => 'int',
                                'operator' => 'LT',
                                'value'    => [1],
                            ],
                        ],
                    ],
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-9 day')->format(self::DATETIME),
                    'float'  => 0.0,
                    'id'     => $result[0]['id'],
                    'int'    => 0,
                    'string' => 'String 0',

                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::FILTER => [
                        [
                            [
                                'column'   => 'int',
                                'operator' => 'LTE',
                                'value'    => [1],
                            ],
                        ],
                    ],
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => TRUE,
                    'date'   => $this->today->format(self::DATETIME),
                    'float'  => 0.0,
                    'id'     => $result[0]['id'],
                    'int'    => 0,
                    'string' => 'String 0',

                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 1.1,
                    'id'     => $result[1]['id'],
                    'int'    => 1,
                    'string' => 'String 1',

                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::FILTER => [
                        [
                            [
                                'column'   => 'custom_string',
                                'operator' => 'EQ',
                                'value'    => ['String 0'],
                            ],
                        ],
                    ],
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 0.0,
                    'id'     => $result[0]['id'],
                    'int'    => 0,
                    'string' => 'String 0',
                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::FILTER => [
                        [
                            [
                                'column'   => 'string',
                                'operator' => 'EMPTY',
                            ],
                        ],
                    ],
                ],
            ),
        )->toArray();
        self::assertEquals([], $result);

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::FILTER => [
                        [
                            [
                                'column'   => 'string',
                                'operator' => 'NEMPTY',
                            ],
                        ],
                    ],
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => TRUE,
                    'date'   => $this->today->format(self::DATETIME),
                    'float'  => 0.0,
                    'id'     => $result[0]['id'],
                    'int'    => 0,
                    'string' => 'String 0',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 1.1,
                    'id'     => $result[1]['id'],
                    'int'    => 1,
                    'string' => 'String 1',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 2.2,
                    'id'     => $result[2]['id'],
                    'int'    => 2,
                    'string' => 'String 2',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 3.3,
                    'id'     => $result[3]['id'],
                    'int'    => 3,
                    'string' => 'String 3',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 4.4,
                    'id'     => $result[4]['id'],
                    'int'    => 4,
                    'string' => 'String 4',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 5.5,
                    'id'     => $result[5]['id'],
                    'int'    => 5,
                    'string' => 'String 5',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 6.6,
                    'id'     => $result[6]['id'],
                    'int'    => 6,
                    'string' => 'String 6',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 7.7,
                    'id'     => $result[7]['id'],
                    'int'    => 7,
                    'string' => 'String 7',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 8.8,
                    'id'     => $result[8]['id'],
                    'int'    => 8,
                    'string' => 'String 8',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 9.9,
                    'id'     => $result[9]['id'],
                    'int'    => 9,
                    'string' => 'String 9',
                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            (new GridRequestDto(
                [
                    self::FILTER => [
                        [
                            [
                                'column'   => 'string',
                                'operator' => 'NEMPTY',
                            ],
                        ],
                    ],
                ],
            ))->setAdditionalFilters(
                [
                    [
                        [
                            'column'   => 'string',
                            'operator' => 'EMPTY',
                        ],
                    ],
                ],
            ),
        )->toArray();
        self::assertEquals([], $result);

        $dto    = new GridRequestDto(
            [
                self::SEARCH => 'Unknown',
            ],
        );
        $result = (new AggregationDocumentFilter($this->dm))->getData($dto)->toArray();
        self::assertEquals([], $result);
        self::assertEquals(
            [
                'filter'       => '[]',
                'itemsPerPage' => 10,
                'page'         => 1,
                'search'       => 'Unknown',
                'sorter'       => NULL,
                'total'        => 0,
            ],
            $dto->getParamsForHeader(),
        );

        try {
            (new AggregationDocumentFilter($this->dm))->getData(
                new GridRequestDto(
                    [
                        self::FILTER => [
                            [
                                [
                                    'column'   => 'Unknown',
                                    'operator' => 'EQ',
                                    'value'    => 'abc',
                                ],
                            ],
                        ],
                    ],
                ),
            )->toArray();
            self::fail();
        } catch (Exception $e) {
            self::assertEquals(GridException::MISSING_CONDITION_COLUMN, $e->getCode());
            self::assertSame(
                "Column 'Unknown' cannot be used as condition! Have you forgotten add it to 'MongoDataGridTests\Filter\AggregationDocumentFilter::getConditions'?",
                $e->getMessage(),
            );
        }

        $documentFilter = $this
            ->getMockBuilder(AggregationDocumentFilter::class)
            ->setConstructorArgs([$this->dm])
            ->onlyMethods(['getSearch'])
            ->getMock();
        $documentFilter
            ->expects($this->once())
            ->method('getSearch')
            ->willReturn([]);
        try {
            $documentFilter->getData(
                new GridRequestDto(
                    [
                        self::SEARCH => 'Unknown',
                    ],
                ),
            )->toArray();
            self::fail();
        } catch (Exception $e) {
            self::assertEquals(GridException::MISSING_SEARCH_COLUMN, $e->getCode());
            self::assertSame(
                sprintf(
                    "Column cannot be used for searching! Have you forgotten add it to '%s::getSearch'?",
                    $documentFilter::class,
                ),
                $e->getMessage(),
            );
        }
    }

    /**
     * @throws Exception
     */
    public function testAdvancedConditions(): void
    {
        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::FILTER =>
                        [
                            [
                                [
                                    'column'   => 'string',
                                    'operator' => GridFilterAbstract::EQ,
                                    'value'    => 'String 1',
                                ],
                            ],
                        ]
                    ,
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 1.1,
                    'id'     => $result[0]['id'],
                    'int'    => 1,
                    'string' => 'String 1',
                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::FILTER =>
                        [
                            [
                                [
                                    'column'   => 'int',
                                    'operator' => GridFilterAbstract::EQ,
                                    'value'    => 2,
                                ],
                            ],
                        ]
                    ,
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 2.2,
                    'id'     => $result[0]['id'],
                    'int'    => 2,
                    'string' => 'String 2',
                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::FILTER =>
                        [
                            [
                                [
                                    'column'   => 'float',
                                    'operator' => GridFilterAbstract::EQ,
                                    'value'    => 3.3,
                                ],
                            ],
                        ]
                    ,
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 3.3,
                    'id'     => $result[0]['id'],
                    'int'    => 3,
                    'string' => 'String 3',
                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::FILTER =>
                        [
                            [
                                [
                                    'column'   => 'bool',
                                    'operator' => GridFilterAbstract::EQ,
                                    'value'    => TRUE,
                                ],
                            ], [
                                [
                                    'column'   => 'string',
                                    'operator' => GridFilterAbstract::EQ,
                                    'value'    => 'String 4',
                                ],
                            ],
                        ]
                    ,
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 4.4,
                    'id'     => $result[0]['id'],
                    'int'    => 4,
                    'string' => 'String 4',
                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::FILTER =>
                        [
                            [
                                [
                                    'column'   => 'date',
                                    'operator' => GridFilterAbstract::EQ,
                                    'value'    => (clone $this->today)->modify('1 day')->format(self::DATETIME),
                                ],
                            ],
                        ]
                    ,
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 5.5,
                    'id'     => $result[0]['id'],
                    'int'    => 5,
                    'string' => 'String 5',
                ],
            ],
            $result,
        );

        $dto    = new GridRequestDto(
            [
                self::FILTER =>
                    [
                        [
                            [
                                'column'   => 'int',
                                'operator' => GridFilterAbstract::EQ,
                                'value'    => [6, 7, 8],
                            ],
                        ],
                    ]
                ,
            ],
        );
        $result = (new AggregationDocumentFilter($this->dm))->getData($dto)->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 6.6,
                    'id'     => $result[0]['id'],
                    'int'    => 6,
                    'string' => 'String 6',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 7.7,
                    'id'     => $result[1]['id'],
                    'int'    => 7,
                    'string' => 'String 7',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 8.8,
                    'id'     => $result[2]['id'],
                    'int'    => 8,
                    'string' => 'String 8',
                ],
            ],
            $result,
        );
        self::assertSame(3, $dto->getTotal());

        $dto    = new GridRequestDto(
            [
                self::FILTER =>
                    [
                        [
                            [
                                'column'   => 'string',
                                'operator' => GridFilterAbstract::EQ,
                                'value'    => 'String 9',
                            ],
                        ],
                    ]
                ,
            ],
        );
        $result = (new AggregationDocumentFilter($this->dm))->getData($dto)->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 9.9,
                    'id'     => $result[0]['id'],
                    'int'    => 9,
                    'string' => 'String 9',
                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::FILTER =>
                        [
                            [
                                [
                                    'column'   => 'int',
                                    'operator' => GridFilterAbstract::GTE,
                                    'value'    => 8,
                                ],
                            ],
                        ]
                    ,
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 8.8,
                    'id'     => $result[0]['id'],
                    'int'    => 8,
                    'string' => 'String 8',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 9.9,
                    'id'     => $result[1]['id'],
                    'int'    => 9,
                    'string' => 'String 9',
                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::FILTER =>
                        [
                            [
                                [
                                    'column'   => 'int',
                                    'operator' => GridFilterAbstract::GT,
                                    'value'    => 8,
                                ],
                            ],
                        ]
                    ,
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => FALSE,
                    'date'   => $this->today->format(self::DATETIME),
                    'float'  => 9.9,
                    'id'     => $result[0]['id'],
                    'int'    => 9,
                    'string' => 'String 9',
                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::FILTER =>
                        [
                            [
                                [
                                    'column'   => 'int',
                                    'operator' => GridFilterAbstract::LT,
                                    'value'    => 1,
                                ],
                            ],
                        ]
                    ,
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-9 day')->format(self::DATETIME),
                    'float'  => 0.0,
                    'id'     => $result[0]['id'],
                    'int'    => 0,
                    'string' => 'String 0',
                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::FILTER =>
                        [
                            [
                                [
                                    'column'   => 'int',
                                    'operator' => GridFilterAbstract::LTE,
                                    'value'    => 1,
                                ],
                            ],
                        ]
                    ,
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => TRUE,
                    'date'   => $this->today->format(self::DATETIME),
                    'float'  => 0.0,
                    'id'     => $result[0]['id'],
                    'int'    => 0,
                    'string' => 'String 0',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 1.1,
                    'id'     => $result[1]['id'],
                    'int'    => 1,
                    'string' => 'String 1',
                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::FILTER =>
                        [
                            [
                                [
                                    'column'   => 'custom_string',
                                    'operator' => GridFilterAbstract::EQ,
                                    'value'    => ['String 0'],
                                ],
                            ],
                        ]
                    ,
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 0.0,
                    'id'     => $result[0]['id'],
                    'int'    => 0,
                    'string' => 'String 0',
                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::FILTER =>
                        [
                            [
                                [
                                    'column'   => 'string',
                                    'operator' => GridFilterAbstract::EMPTY,
                                ],
                                [
                                    'column'   => 'string',
                                    'operator' => 'Unknown',
                                    'value'    => 'Unknown',
                                ],
                            ],
                        ]
                    ,
                ],
            ),
        )->toArray();
        self::assertEquals([], $result);

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::FILTER =>
                        [
                            [
                                [
                                    'column'   => 'string',
                                    'operator' => GridFilterAbstract::NEMPTY,
                                ],
                            ],
                        ]
                    ,
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => TRUE,
                    'date'   => $this->today->format(self::DATETIME),
                    'float'  => 0.0,
                    'id'     => $result[0]['id'],
                    'int'    => 0,
                    'string' => 'String 0',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 1.1,
                    'id'     => $result[1]['id'],
                    'int'    => 1,
                    'string' => 'String 1',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 2.2,
                    'id'     => $result[2]['id'],
                    'int'    => 2,
                    'string' => 'String 2',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 3.3,
                    'id'     => $result[3]['id'],
                    'int'    => 3,
                    'string' => 'String 3',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 4.4,
                    'id'     => $result[4]['id'],
                    'int'    => 4,
                    'string' => 'String 4',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 5.5,
                    'id'     => $result[5]['id'],
                    'int'    => 5,
                    'string' => 'String 5',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 6.6,
                    'id'     => $result[6]['id'],
                    'int'    => 6,
                    'string' => 'String 6',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 7.7,
                    'id'     => $result[7]['id'],
                    'int'    => 7,
                    'string' => 'String 7',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 8.8,
                    'id'     => $result[8]['id'],
                    'int'    => 8,
                    'string' => 'String 8',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 9.9,
                    'id'     => $result[9]['id'],
                    'int'    => 9,
                    'string' => 'String 9',
                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            (new GridRequestDto(
                [
                    self::FILTER =>
                        [
                            [
                                [
                                    'column'   => 'string',
                                    'operator' => GridFilterAbstract::NEMPTY,
                                    'value'    => '_MODIFIER_VAL_NOT_NULL',
                                ],
                            ],
                        ],
                ],
            ))->setAdditionalFilters(
                [
                    [
                        [
                            'column'   => 'string',
                            'operator' => 'EMPTY',
                        ],
                    ],
                ],
            ),
        )->toArray();
        self::assertEquals([], $result);

        $dto    = new GridRequestDto(
            [
                self::SEARCH => 'Unknown',
            ],
        );
        $result = (new AggregationDocumentFilter($this->dm))->getData($dto)->toArray();
        self::assertEquals([], $result);

        try {
            (new AggregationDocumentFilter($this->dm))->getData(
                new GridRequestDto(
                    [
                        self::FILTER =>
                            [
                                [
                                    [
                                        'column'   => 'Unknown',
                                        'operator' => GridFilterAbstract::EQ,
                                        'value'    => '',
                                    ],
                                ],
                            ]
                        ,
                    ],
                ),
            )->toArray();
            self::fail();
        } catch (Exception $e) {
            self::assertEquals(GridException::MISSING_CONDITION_COLUMN, $e->getCode());
            self::assertSame(
                "Column 'Unknown' cannot be used as condition! Have you forgotten add it to 'MongoDataGridTests\Filter\AggregationDocumentFilter::getConditions'?",
                $e->getMessage(),
            );
        }

        $documentFilter = $this
            ->getMockBuilder(AggregationDocumentFilter::class)
            ->setConstructorArgs([$this->dm])
            ->onlyMethods(['getSearch'])
            ->getMock();
        $documentFilter
            ->expects($this->once())
            ->method('getSearch')
            ->willReturn([]);
        try {
            $documentFilter->getData(
                new GridRequestDto(
                    [
                        self::FILTER =>
                            [
                                [
                                    [
                                        'column'   => '_MODIFIER_SEARCH',
                                        'operator' => GridFilterAbstract::EQ,
                                        'value'    => 'Unknown',
                                    ],
                                ],
                            ]
                        ,
                    ],
                ),
            )->toArray();
            self::fail();
        } catch (Exception $e) {
            self::assertEquals(GridException::MISSING_CONDITION_COLUMN, $e->getCode());
            self::assertSame(
                sprintf(
                    "Column '_MODIFIER_SEARCH' cannot be used as condition! Have you forgotten add it to '%s::getConditions'?",
                    $documentFilter::class,
                ),
                $e->getMessage(),
            );
        }

        try {
            (new AggregationDocumentFilter($this->dm))->getData(
                new GridRequestDto(
                    [
                        self::FILTER =>
                            [
                                [
                                    [
                                        'Unknown' => 'Unknown',
                                    ],
                                ],
                            ]
                        ,
                    ],
                ),
            )->toArray();
            self::fail();
        } catch (GridException $e) {
            self::assertSame(
                "Missing one of required advanced filter fields ('column', 'operator' or 'value')!",
                $e->getMessage(),
            );
        }

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::FILTER =>
                        [
                            [
                                [
                                    'column'   => 'string',
                                    'operator' => GridFilterAbstract::EQ,
                                    'value'    => ['String 0', 'String 1'],
                                ],
                            ],
                        ]
                    ,
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-9 day')->format(self::DATETIME),
                    'float'  => 0.0,
                    'id'     => $result[0]['id'],
                    'int'    => 0,
                    'string' => 'String 0',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 1.1,
                    'id'     => $result[1]['id'],
                    'int'    => 1,
                    'string' => 'String 1',
                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::FILTER =>
                        [
                            [
                                [
                                    'column'   => 'string',
                                    'operator' => GridFilterAbstract::NEQ,
                                    'value'    => [
                                        'String 0',
                                        'String 1',
                                        'String 3',
                                        'String 4',
                                        'String 5',
                                        'String 6',
                                        'String 7',
                                        'String 8',
                                        'String 9',
                                    ],
                                ],
                            ],
                        ]
                    ,
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 2.2,
                    'id'     => $result[0]['id'],
                    'int'    => 2,
                    'string' => 'String 2',
                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::FILTER =>
                        [
                            [
                                [
                                    'column'   => 'string',
                                    'operator' => GridFilterAbstract::IN,
                                    'value'    => [
                                        'String 0',
                                        'String 1',
                                    ],
                                ],
                            ],
                        ]
                    ,
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-2 day')->format(self::DATETIME),
                    'float'  => 0.0,
                    'id'     => $result[0]['id'],
                    'int'    => 0,
                    'string' => 'String 0',
                ],
                [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 1.1,
                    'id'     => $result[1]['id'],
                    'int'    => 1,
                    'string' => 'String 1',
                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::FILTER =>
                        [
                            [
                                [
                                    'column'   => 'string',
                                    'operator' => GridFilterAbstract::NIN,
                                    'value'    => [
                                        'String 2',
                                        'String 3',
                                        'String 4',
                                        'String 5',
                                        'String 6',
                                        'String 7',
                                        'String 8',
                                        'String 9',
                                    ],
                                ],
                            ],
                        ]
                    ,
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 0.0,
                    'id'     => $result[0]['id'],
                    'int'    => 0,
                    'string' => 'String 0',
                ],
                [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 1.1,
                    'id'     => $result[1]['id'],
                    'int'    => 1,
                    'string' => 'String 1',
                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::FILTER =>
                        [
                            [
                                [
                                    'column'   => 'string',
                                    'operator' => GridFilterAbstract::STARTS,
                                    'value'    => 'St',
                                ],
                            ], [
                                [
                                    'column'   => 'string',
                                    'operator' => GridFilterAbstract::LIKE,
                                    'value'    => 'ri',
                                ],
                            ], [
                                [
                                    'column'   => 'string',
                                    'operator' => GridFilterAbstract::ENDS,
                                    'value'    => 'ng 3',
                                ],
                            ],
                        ]
                    ,
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('2 day')->format(self::DATETIME),
                    'float'  => 3.3,
                    'id'     => $result[0]['id'],
                    'int'    => 3,
                    'string' => 'String 3',
                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::FILTER =>
                        [
                            [
                                [
                                    'column'   => 'int',
                                    'operator' => GridFilterAbstract::BETWEEN,
                                    'value'    => [4, 7],
                                ], [
                                    'column'   => 'int',
                                    'operator' => GridFilterAbstract::BETWEEN,
                                    'value'    => [5],
                                ],
                            ], [
                                [
                                    'column'   => 'float',
                                    'operator' => GridFilterAbstract::NBETWEEN,
                                    'value'    => [1.1, 3.3],
                                ],
                                [
                                    'column'   => 'float',
                                    'operator' => GridFilterAbstract::NBETWEEN,
                                    'value'    => 2.2,
                                ],
                            ], [
                                [
                                    'column'   => 'float',
                                    'operator' => GridFilterAbstract::NBETWEEN,
                                    'value'    => [6.6, 9.9],
                                ],
                            ],
                        ]
                    ,
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 4.4,
                    'id'     => $result[0]['id'],
                    'int'    => 4,
                    'string' => 'String 4',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 5.5,
                    'id'     => $result[1]['id'],
                    'int'    => 5,
                    'string' => 'String 5',
                ], [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 6.6,
                    'id'     => $result[2]['id'],
                    'int'    => 6,
                    'string' => 'String 6',
                ],
            ],
            $result,
        );

        $result = (new AggregationDocumentFilter($this->dm))->getData(
            new GridRequestDto(
                [
                    self::FILTER =>
                        [
                            [
                                [
                                    'column'   => 'string',
                                    'operator' => GridFilterAbstract::EQ,
                                    'value'    => 'String 5',
                                ], [
                                    'column'   => 'custom_string',
                                    'operator' => GridFilterAbstract::EQ,
                                    'value'    => ['String 5'],
                                ],
                            ], [
                                [
                                    'column'   => 'int',
                                    'operator' => GridFilterAbstract::GTE,
                                    'value'    => 5,
                                ], [
                                    'column'   => 'int',
                                    'operator' => GridFilterAbstract::LTE,
                                    'value'    => 5,
                                ],
                            ],
                        ]
                    ,
                ],
            ),
        )->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 5.5,
                    'id'     => $result[0]['id'],
                    'int'    => 5,
                    'string' => 'String 5',
                ],
            ],
            $result,
        );
    }

    /**
     * @throws Exception
     */
    public function testPagination(): void
    {
        $dto    = new GridRequestDto(
            [
                self::PAGING => [self::PAGE => '3', self::ITEMS_PER_PAGE => '2'],
                self::SORTER    => [
                    [
                        'column'    => 'id',
                        'direction' => 'ASC',
                    ],
                ],
            ],
        );
        $result = (new AggregationDocumentFilter($this->dm))->getData($dto)->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('4 day')->format(self::DATETIME),
                    'float'  => 4.4,
                    'id'     => $result[0]['id'],
                    'int'    => 4,
                    'string' => 'String 4',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 5.5,
                    'id'     => $result[1]['id'],
                    'int'    => 5,
                    'string' => 'String 5',
                ],
            ],
            $result,
        );
        self::assertEquals(
            [
                'filter'       => '[]',
                'itemsPerPage' => 2,
                'page'         => 3,
                'search'       => NULL,
                'sorter'       => '[{"column":"id","direction":"ASC"}]',
                'total'        => 10,
            ],
            $dto->getParamsForHeader(),
        );

        $dto    = (new GridRequestDto(
            [
                self::PAGING => [self::PAGE => '3'],
                self::SORTER    => [
                    [
                        'column'    => 'id',
                        'direction' => 'ASC',
                    ],
                ],
            ],
        ))->setItemsPerPage(2);
        $result = (new AggregationDocumentFilter($this->dm))->getData($dto)->toArray();
        self::assertEquals(
            [
                [
                    'bool'   => TRUE,
                    'date'   => $this->today->modify('-1 day')->format(self::DATETIME),
                    'float'  => 4.4,
                    'id'     => $result[0]['id'],
                    'int'    => 4,
                    'string' => 'String 4',
                ], [
                    'bool'   => FALSE,
                    'date'   => $this->today->modify('1 day')->format(self::DATETIME),
                    'float'  => 5.5,
                    'id'     => $result[1]['id'],
                    'int'    => 5,
                    'string' => 'String 5',
                ],
            ],
            $result,
        );
        self::assertEquals(
            [
                'filter'       => '[]',
                'itemsPerPage' => 2,
                'page'         => 3,
                'search'       => NULL,
                'sorter'       => '[{"column":"id","direction":"ASC"}]',
                'total'        => 10,
            ],
            $dto->getParamsForHeader(),
        );
    }

    /**
     * @throws Exception
     */
    public function testSearchCallback(): void
    {
        $dto    = new GridRequestDto(
            [
                self::SEARCH => 'Unknown',
            ],
        );
        $result = (new AggregationDocumentFilter($this->dm))->getData($dto)->toArray();
        self::assertEquals([], $result);
        self::assertEquals(
            [
                'filter'       => '[]',
                'itemsPerPage' => 10,
                'page'         => 1,
                'search'       => 'Unknown',
                'sorter'       => NULL,
                'total'        => 0,
            ],
            $dto->getParamsForHeader(),
        );
    }

    /**
     * @throws Exception
     */
    public function testSearchBadSearchFields(): void
    {
        $dto = new GridRequestDto(
            [
                self::SEARCH => 'Unknown',
            ],
        );
        $f   = $this
            ->getMockBuilder(AggregationDocumentFilter::class)
            ->setConstructorArgs([$this->dm])
            ->onlyMethods(['getConditions'])
            ->getMock();
        $f
            ->expects($this->once())
            ->method('getConditions')
            ->willReturn([]);

        self::expectException(GridException::class);
        self::expectExceptionCode(GridException::MISSING_SEARCH_COLUMN);
        $f->getData($dto)->toArray();
    }

    /**
     * @throws Exception
     */
    public function testGetDataThrow(): void
    {
        $dto = self::createPartialMock(GridRequestDto::class, ['setTotal']);
        $dto->expects($this->once())->method('setTotal')->willThrowException(new CommandException('', 123));
        $this->setProperty($dto, 'headers', []);

        self::expectException(CommandException::class);
        self::expectExceptionCode(123);
        (new AggregationDocumentFilter($this->dm))->getData($dto)->toArray();
    }

    /**
     * @throws Exception
     */
    public function testGetOrderBy(): void
    {
        $dto = new GridRequestDto(
            [
                self::SORTER => [[]],
            ],
        );

        self::expectException(GridException::class);
        $dto->getOrderBy();
    }

    /**
     * @throws Exception
     */
    public function testGetOrderByBadFormat(): void
    {
        $dto = new GridRequestDto(
            [
                self::SORTER => ['a'],
            ],
        );

        self::expectException(GridException::class);
        $dto->getOrderBy();
    }

    /**
     * @throws Exception
     */
    public function testGetOrderByBadDirection(): void
    {
        $dto = new GridRequestDto(
            [
                self::SORTER => [['column' => 'a', 'direction' => 'b']],
            ],
        );

        self::expectException(GridException::class);
        $dto->getOrderBy();
    }

    /**
     * @throws Exception
     */
    public function testGetFilterBadFormat(): void
    {
        $dto = new GridRequestDto(
            [
                self::FILTER => ['a'],
            ],
        );

        self::expectException(GridException::class);
        $dto->getFilter(FALSE);
    }

    /**
     * @throws Exception
     */
    public function testGetFilterBadFormat2(): void
    {
        $dto = new GridRequestDto(
            [
                self::FILTER => [[[]]],
            ],
        );

        self::expectException(GridException::class);
        $dto->getFilter(FALSE);
    }

    /**
     * @throws Exception
     */
    public function testGetFilter(): void
    {
        $dto = new GridRequestDto(
            [
                self::FILTER => [[['column' => 'a', 'operator' => 'b']]],
            ],
        );

        self::assertNotEmpty($dto->getFilter(FALSE));
    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->today = new DateTime('today', new DateTimeZone('UTC'));

        for ($i = 0; $i < 10; $i++) {
            $this->dm->persist(
                (new AggregationDocument())
                    ->setString(sprintf('String %s', $i))
                    ->setInt($i)
                    ->setFloat((float) sprintf('%s.%s', $i, $i))
                    ->setBool($i % 2 === 0)
                    ->setDate(new DateTime(sprintf('today +%s day', $i), new DateTimeZone('UTC'))),
            );
        }

        $this->dm->flush();
    }

}
