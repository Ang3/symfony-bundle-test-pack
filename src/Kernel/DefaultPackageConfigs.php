<?php

namespace Ang3\Bundle\Test\Kernel;

final class DefaultPackageConfigs
{
    public const FRAMEWORK = [
        'test' => true,
        'secret' => '%env(APP_SECRET)%',
        'session' => [
            'handler_id' => null,
            'cookie_secure' => 'auto',
            'cookie_samesite' => 'lax',
            'storage_id' => 'session.storage.mock_file',
        ],
        'php_errors' => [
            'log' => true,
        ],
        'cache' => [],
        'router' => [
            'utf8' => true,
        ],
    ];

    public const SECURITY = [
        'providers' => [
            'users_in_memory' => [
                'memory' => null,
            ],
        ],
        'firewalls' => [
            'dev' => [
                'pattern' => '^/(_(profiler|wdt)|css|images|js)/',
                'security' => false,
            ],
            'main' => [
                'anonymous' => true,
                'lazy' => true,
                'provider' => 'users_in_memory',
            ],
        ],
        'access_control' => [],
    ];

    public const DOCTRINE = [
        'dbal' => [
            'url' => '%env(resolve:DATABASE_URL)%',
        ],
        'orm' => [
            'auto_generate_proxy_classes' => true,
            'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware',
            'auto_mapping' => true,
            'mappings' => [
                'App' => [
                    'is_bundle' => false,
                    'type' => 'annotation',
                    'dir' => '%kernel.doctrine_entity_dir%',
                    'prefix' => 'App\Entity',
                    'alias' => 'App',
                ],
            ],
        ],
    ];

    public const API_PLATFORM = [
        'mapping' => [
            'paths' => [
                '%kernel.doctrine_entity_dir%',
            ],
        ],
        'patch_formats' => [
            'json' => [
                'application/merge-patch+json'
            ],
        ],
        'swagger' => [
            'versions' => [3],
        ],
    ];

    public const SWIFTMAILER = [
        'disable_delivery' => true,
    ];
}
