<?php declare(strict_types=1);

namespace MongoDataGridTests\Integration;

use Exception;
use Hanaboso\MongoDataGrid\GridHandlerTrait;
use Hanaboso\MongoDataGrid\GridRequestDto;
use MongoDataGridTests\TestCaseAbstract;

/**
 * Class GridHandlerTraitTest
 *
 * @package MongoDataGridTests\Integration
 */
final class GridHandlerTraitTest extends TestCaseAbstract
{

    use GridHandlerTrait;

    protected const string DATABASE = 'datagrid-trait';

    /**
     * @throws Exception
     */
    public function testGetGridResponse(): void
    {
        $dto = new GridRequestDto([]);
        $res = $this->getGridResponse($dto, ['a']);

        self::assertEquals(
            [
                'filter' => [],
                'items'  => ['a'],
                'paging' => [
                    'itemsPerPage' => 10,
                    'lastPage'     => 1,
                    'nextPage'     => 1,
                    'page'         => 1,
                    'previousPage' => 1,
                    'total'        => 0,
                ],
                'sorter' => [],
            ],
            $res,
        );
    }

}
