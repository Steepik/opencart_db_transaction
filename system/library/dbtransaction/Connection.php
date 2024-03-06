<?php

namespace DbTransaction;

interface Connection
{
    /**
     * @param string $sql
     */
    public function query($sql);

    /**
     * @param string $value
     * @return string
     */
    public function escape($value);

    /**
     * @return int
     */
    public function countAffected();

    /**
     * @return int|null
     */
    public function getLastId();

    /**
     * @return bool
     */
    public function isConnected();

    /**
     * @return mixed
     */
    public function transaction(\Closure $callback);

    /**
     * @return void
     */
    public function commit();

    /**
     * @return void
     */
    public function rollBack();

    /**
     * @return int
     */
    public function transactionLevel();
}