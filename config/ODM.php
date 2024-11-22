<?php

declare(strict_types=1);

namespace config;

use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\Mapping\Driver\AttributeDriver;

/**
 * Конфигурация поключения к базе данных для ODM
 */
class ODM extends Config
{
    protected static array  $local    = [
        'proxy'       => [
            'directory' => './app/Model/ODM/Proxies',
            'namespace' => 'Proxies',
        ],
        'hydrator'    => [
            'directory' => './app/Model/ODM/Hydrators',
            'namespace' => 'Hydrators',
        ],
        'default_db'  => 'simpledynamic_doctrine_odm',
        'driver_path' => '.\app\Model\ODM\Documents',
    ];
    protected static string $filename = '';

    /**
     * Создать конфигурвцию для поключния к базе данных MongoDB
     */
    public static function createMongoDBConfig(): Configuration
    {
        $mongoDB = self::getConfig();

        $config = new Configuration();
        $config->setProxyDir($mongoDB['proxy']['directory']);
        $config->setProxyNamespace($mongoDB['proxy']['namespace']);
        $config->setHydratorDir($mongoDB['hydrator']['directory']);
        $config->setHydratorNamespace($mongoDB['hydrator']['namespace']);
        $config->setDefaultDB($mongoDB['default_db']);

        $config->setMetadataDriverImpl(AttributeDriver::create($mongoDB['driver_path']));

        return $config;
    }
}
