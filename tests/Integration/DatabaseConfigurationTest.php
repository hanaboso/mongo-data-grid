<?php declare(strict_types=1);

namespace MongoDataGridTests\Integration;

use Exception;
use MongoDataGridTests\Document\Document;
use MongoDataGridTests\TestCaseAbstract;

/**
 * Class DatabaseConfigurationTest
 *
 * @package MongoDataGridTests\Integration
 */
final class DatabaseConfigurationTest extends TestCaseAbstract
{

    protected const string DATABASE = 'datagrid1';

    /**
     * @throws Exception
     */
    public function testConnection(): void
    {
        $this->dm->getClient()->dropDatabase(self::DATABASE);
        $this->dm->getSchemaManager()->createCollections();
        $this->dm->getSchemaManager()->ensureDocumentIndexes(Document::class);

        $this->dm->persist((new Document())->setString('Document'));
        $this->dm->flush();
        $this->dm->clear();

        /** @var Document[] $documents */
        $documents = $this->dm->getRepository(Document::class)->findAll();
        self::assertEquals(1, count($documents));
        self::assertSame('Document', $documents[0]->getString());
    }

}
