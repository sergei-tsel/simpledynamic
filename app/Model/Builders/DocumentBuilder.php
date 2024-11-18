<?php
declare(strict_types=1);
namespace App\Model\Builders;

use config\ODM;
use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * Создатель репозитрия для ODM
 */
class DocumentBuilder implements BuilderInterface
{
    public function createRepository(string $entity): object
    {
        $config = ODM::createMongoDBConfig();

        $documentManager = DocumentManager::create(config: $config);

        return new $entity($documentManager);
    }
}
