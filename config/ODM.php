<?php

declare(strict_types=1);

namespace config;

use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Driver\AttributeDriver;

/**
 * Конфигурация подключения к базе данных для ODM
 *
 * @psalm-suppress ClassCanBeFinal
 */
class ODM extends Config
{
    /**
     * @psalm-suppress InvalidAttribute
     */
    #[\Override]
    protected static array  $local    = [
        'hydrator'    => [
            'directory' => './app/Model/ODM/Hydrators',
            'namespace' => 'Hydrators',
        ],
        'default_db'  => 'simpledynamic_doctrine_odm',
        'driver_path' => '.\app\Model\ODM\Documents',
    ];
    /**
     * @psalm-suppress InvalidAttribute
     */
    #[\Override]
    protected static string $filename = '';

    /**
     * Создать конфигурацию для подключения к базе данных MongoDB
     */
    public static function createDoctrineMongoDB(): DocumentManager
    {
        $mongoDB = self::getConfig();

        $config = new Configuration();
        $config->setUseNativeLazyObject(true);
        $config->setHydratorDir($mongoDB['hydrator']['directory']);
        $config->setHydratorNamespace($mongoDB['hydrator']['namespace']);
        $config->setDefaultDB($mongoDB['default_db']);

        $config->setMetadataDriverImpl(AttributeDriver::create($mongoDB['driver_path']));

        return DocumentManager::create(config: $config);
    }
}
