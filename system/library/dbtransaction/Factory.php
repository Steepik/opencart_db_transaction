<?php

namespace DbTransaction;

use Config;

final class Factory
{
    /**
     * @param Config $config
     * @param string $driver
     *
     * @return PDOConnection
     *
     * @throws \Exception
     */
    public static function create($config, $driver)
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