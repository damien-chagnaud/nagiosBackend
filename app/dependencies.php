<?php

declare(strict_types=1);

use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\Configuration\Migration\ConfigurationLoader as DoctrineConfigurationLoader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return function (ContainerBuilder $containerBuilder) {

    $container = $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $loggerSettings = $settings->get('logger');
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },

        EntityManager::class => static function (ContainerInterface $c,LoggerInterface $logger) {
            $settings = $c->get(SettingsInterface::class);

            $doctrineSettings = $settings->get('doctrine');

            $is_dev = $doctrineSettings['app_dev']; //($_SERVER['APP_ENV'] !== 'production');
            $proxy_dir = null;
    
            if (!$is_dev) {
                $proxy_dir = $doctrineSettings['proxy_dir'];
            }
    
            //Type::addType(UserIdType::USER_ID, UserIdType::class);
    
            $config = Setup::createXMLMetadataConfiguration([$doctrineSettings['metadata_dir']], $is_dev, $proxy_dir);
    
            $conn = [
                'url' => $doctrineSettings['database_url'],//$_SERVER['DATABASE_URL'],
            ];
    
            return EntityManager::create($conn, $config);
        },
    
    ]);
};
