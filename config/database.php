<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '39.105.214.152'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'nlsg_v4'),
            'username' => env('DB_USERNAME', 'bj_root'),
            'password' => env('DB_PASSWORD', 'NLSG_2019cs*beijin.0410BJ'),

            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            //添加model  解决mysql5.7的only_full_group_by问题
            'modes' => [
                'STRICT_ALL_TABLES',
                'ERROR_FOR_DIVISION_BY_ZERO',
                'NO_ZERO_DATE',
                'NO_ZERO_IN_DATE',
                'NO_AUTO_CREATE_USER',
            ],
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => 'rm-2ze0owc97gckfqz8q20210319.mysql.polardb.rds.aliyuncs.com',
            'port' => '3306',
            'database' => 'nlsg_v4',
            'username' => 'fangsinan',
            'password' => 'Rds&1014$NLSG^v3=',

            'unix_socket' => env('DB_OLD_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            //添加model  解决mysql5.7的only_full_group_by问题
            'modes' => [
                'STRICT_ALL_TABLES',
                'ERROR_FOR_DIVISION_BY_ZERO',
                'NO_ZERO_DATE',
                'NO_ZERO_IN_DATE',
                'NO_AUTO_CREATE_USER',
            ],
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'mysql_old' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_OLD_HOST', '127.0.0.1'),
            'port' => env('DB_OLD_PORT', '3306'),
            'database' => env('DB_OLD_DATABASE', 'forge'),
            'username' => env('DB_OLD_USERNAME', 'forge'),
            'password' => env('DB_OLD_PASSWORD', ''),

            'unix_socket' => env('DB_OLD_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            //添加model  解决mysql5.7的only_full_group_by问题
            'modes' => [
                'STRICT_ALL_TABLES',
                'ERROR_FOR_DIVISION_BY_ZERO',
                'NO_ZERO_DATE',
                'NO_ZERO_IN_DATE',
                'NO_AUTO_CREATE_USER',
            ],
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'mysql_old_zs' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => 'rm-2ze0owc97gckfqz8qko.mysql.rds.aliyuncs.com',
            'port' => '3306',
            'database' => env('DB_OLD_DATABASE', 'forge'),
            'username' => 'fangsinan',
            'password' => 'Rds&1014$NLSG^v3=',

            'unix_socket' => env('DB_OLD_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            //添加model  解决mysql5.7的only_full_group_by问题
            'modes' => [
                'STRICT_ALL_TABLES',
                'ERROR_FOR_DIVISION_BY_ZERO',
                'NO_ZERO_DATE',
                'NO_ZERO_IN_DATE',
                'NO_AUTO_CREATE_USER',
            ],
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'mysql_new_zs' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => 'rm-2ze0owc97gckfqz8qko.mysql.rds.aliyuncs.com',
            'port' => '3306',
            'database' => 'nlsg_v4',
            'username' => 'fangsinan',
            'password' => 'Rds&1014$NLSG^v3=',

            'unix_socket' => env('DB_OLD_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            //添加model  解决mysql5.7的only_full_group_by问题
            'modes' => [
                'STRICT_ALL_TABLES',
                'ERROR_FOR_DIVISION_BY_ZERO',
                'NO_ZERO_DATE',
                'NO_ZERO_IN_DATE',
                'NO_AUTO_CREATE_USER',
            ],
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '2'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '3'),
        ],

    ],

];
