<?php

declare(strict_types=1);

namespace config;

use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Driver\AttributeDriver;

/**
 * Конфигурация подключения к базе данных для ODM
 *
 * @api
 * @psalm-suppress ClassCanBeFinal
 */
class ODM extends Config
{
    /**
     * @var array<string, array<array-key, mixed>|scalar|null>
     * @psalm-suppress InvalidAttribute
     */
    #[\Override]
    protected static array  $local    = [
        'hydrator'    => [
            'directory' => './app/Models/ODM/Hydrators',
            'namespace' => 'Hydrators',
        ],
        'default_db'  => 'simpledynamic_doctrine_odm',
        'driver_path' => './app/Models/ODM/Documents',
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
        $hydrator = $mongoDB['hydrator'] ?? [];
        $driverPath = is_string($mongoDB['driver_path']) ? $mongoDB['driver_path'] : './app/Models/ODM/Documents';
        $defaultDb = is_string($mongoDB['default_db']) ? $mongoDB['default_db'] : 'simpledynamic_doctrine_odm';

        $config = new Configuration();
        $config->setUseNativeLazyObject(true);
        $config->setHydratorDir((string) ($hydrator['directory'] ?? './app/Models/ODM/Hydrators'));
        $config->setHydratorNamespace((string) ($hydrator['namespace'] ?? 'Hydrators'));
        $config->setDefaultDB($defaultDb);

        $config->setMetadataDriverImpl(AttributeDriver::create([$driverPath]));

        return DocumentManager::create(config: $config);
    }
}
