<?php

declare(strict_types=1);

namespace DbTransaction;

use Config;

final class Factory
{
    /**
     * @throws \Exception
     */
    public static function create(Config $config, string $driver): Connection
    {
        $hostname = $config->get('db_hostname');
        $username = $config->get('db_username');
        $password = $config->get('db_password');
        $database = $config->get('db_database');
        $port = $config->get('db_port');

        if ($driver == 'pdo') {
            return new PDOConnection($hostname, $username, $password, $database, $port);
        }

        throw new \InvalidArgumentException("Unsupported driver ($driver)");
    }
}