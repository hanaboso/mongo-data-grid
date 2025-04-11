<?php declare(strict_types=1);

namespace MongoDataGridTests;

use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use Exception;
use Hanaboso\PhpCheckUtils\PhpUnit\Traits\PrivateTrait;
use Hanaboso\PhpCheckUtils\PhpUnit\Traits\RestoreErrorHandlersTrait;
use MongoDataGridTests\Document\Document;
use MongoDB\Client;
use PHPUnit\Framework\TestCase;

/**
 * Class TestCaseAbstract
 *
 * @package MongoDataGridTests
 */
abstract class TestCaseAbstract extends TestCase
{

    use PrivateTrait;
    use RestoreErrorHandlersTrait;

    protected const string TEMP_DIR      = '%s/../var//Doctrine2.ODM';
    protected const array CLIENT_TYPEMAP = ['root' => 'array', 'document' => 'array'];
    protected const string DATABASE      = 'datagrid';

    /**
     * @var DocumentManager
     */
    protected DocumentManager $dm;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $configuration = new Configuration();
        $configuration->setProxyNamespace('Proxy');
        $configuration->setHydratorNamespace('Hydrator');
        $configuration->setProxyDir(sprintf(self::TEMP_DIR, __DIR__));
        $configuration->setHydratorDir(sprintf(self::TEMP_DIR, __DIR__));
        $configuration->setMetadataDriverImpl(AnnotationDriver::create([sprintf('%s/Document', __DIR__)]));
        $configuration->setDefaultDB(static::DATABASE);

        $this->dm = DocumentManager::create(
            new Client(
                sprintf('mongodb://%s', getenv('MONGODB_HOST') ?: '127.0.0.1'),
                [],
                ['typeMap' => self::CLIENT_TYPEMAP],
            ),
            $configuration,
        );
        $this->dm->getClient()->dropDatabase(static::DATABASE);
        $this->dm->getSchemaManager()->createCollections();
        $this->dm->getSchemaManager()->ensureDocumentIndexes(Document::class);
    }

    /**
     * @return void
     */
    protected function tearDown(): void {
        parent::tearDown();

        $this->restoreErrorHandler();
        $this->restoreExceptionHandler();
    }

}
