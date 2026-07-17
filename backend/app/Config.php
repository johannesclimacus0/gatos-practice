<?php

namespace App;
class Config
{
    protected array $config = [];

    public function __construct($env)
    {
        $this->config = [
            'db'=> [
                'host' =>$env['DB_HOST'],
                'username' =>$env['DB_USER'],
                'password' =>$env['DB_PASS'],
                'database' =>$env['DB_DATABASE'],
                'driver' =>$env['DB_DRIVER'] ?? 'mysql',
                'charset' =>$env['DB_CHARSET'] ?? 'utf8',
                'collation' =>$env['DB_COLLATION'] ?? 'utf8_unicode_ci',
                'prefix' =>$env['DB_PREFIX'] ?? '',
            ],
        ];
    }
    public function __get(string $name)
    {
        return $this->config[$name] ?? null;
    }
}