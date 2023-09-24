<?php

declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {

    // Global Settings Object
    $containerBuilder->addDefinitions([
        SettingsInterface::class => function () {
            return new Settings([
                'displayErrorDetails' => ($_ENV['APP_DEV']=="dev")?true:false, // Should be set to false in production
                'logError'            => false,
                'logErrorDetails'     => false,
                'logger' => [
                    'name' => 'slim-app',
                    'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                    'level' => Logger::DEBUG,
                ],
                'doctrine' => [
                    'proxy_dir' => dirname(__DIR__) . '/var/doctrine_proxy',
                    'metadata_dir' => dirname(__DIR__) . '/config/entity_meta',
                    'cache_dir' => dirname(__DIR__) . '/var/doctrine',
                    'database_url' => $_ENV['DATABASE_URL'],
                    'app_dev' => ($_ENV['APP_DEV']=="dev")?true:false,
                ],
            ]);
        }
    ]);
};
