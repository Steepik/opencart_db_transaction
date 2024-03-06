<?php

namespace DbTransaction;

use PDO;

final class PDOConnection implements Connection
{
    /**
     * @var PDO|null
     */
    private $pdo;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var int
     */
    private $affected;

    /**
     * @var int
     */
    private $transactions = 0;

    /**
     * @param string $hostname
     * @param string $username
     * @param string $password
     * @param string $database
     * @param string $port
     *
     * @throws \Exception
     */
    public function __construct($hostname, $username, $password, $database, $port = '3306')
    {
        try {
            $this->pdo = new PDO('mysql:host=' . $hostname . ';port=' . $port . ';dbname=' . $database . ';charset=utf8mb4', $username, $password, [PDO::ATTR_PERSISTENT => false, PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_general_ci']);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);;
        } catch (\PDOException $exception) {
            throw new \Exception('Error: ' . $exception->getMessage());
        }

        $this->query("SET SESSION sql_mode = 'NO_ZERO_IN_DATE,NO_ENGINE_SUBSTITUTION'");
        $this->query("SET FOREIGN_KEY_CHECKS = 0");
        $this->query("SET time_zone = '" . $this->escape(date('P')) . "'");
    }

    /**
     * @param string $sql
     * @return bool|\stdClass
     *
     * @throws \Exception
     */
    public function query($sql)
    {
        try {
            $sql = preg_replace('/(?:\'\:)([a-z0-9]*.)(?:\')/', ':$1', $sql);

            $statement = $this->pdo->prepare($sql);

            if ($statement && $statement->execute($this->data)) {
                $this->data = [];

                if ($statement->columnCount()) {
                    $data = $statement->fetchAll(PDO::FETCH_ASSOC);
                    $statement->closeCursor();

                    $result = new \stdClass();
                    $result->row = $data[0] ?? [];
                    $result->rows = $data;
                    $result->num_rows = count($data);
                    $this->affected = 0;

                    return $result;
                } else {
                    $this->affected = $statement->rowCount();
                    $statement->closeCursor();

                    return true;
                }
            } else {
                return false;
            }
        } catch (\PDOException $exception) {
            throw new \Exception('Error: ' . $exception->getMessage() . ' <br/>Error Code : ' . $exception->getCode() . ' <br/>' . $sql);
        }
    }

    /**
     * @param string|null $value
     *
     * @return string
     */
    public function escape($value)
    {
        if ($value === '+00:00') {
            return '+00:00';
        }

        $key = ':' . count($this->data);

        $this->data[$key] = $value;

        return $key;
    }

    /**
     * @return int
     */
    public function countAffected()
    {
        return $this->affected;
    }

    /**
     * @return int|null
     */
    public function getLastId()
    {
        $id = $this->pdo->lastInsertId();

        return $id ? (int)$id : null;
    }

    /**
     * @return bool
     */
    public function isConnected()
    {
        return $this->pdo !== null;
    }

    /**
     * @return mixed
     *
     * @throws \Exception
     */
    public function transaction(\Closure $callback)
    {
        $this->beginTransaction();

        try {
            $result = $callback($this);
            $this->commit();

            return $result;
        } catch (\Exception $exception) {
            $this->rollBack();

            throw $exception;
        }
    }

    /**
     * @return void
     */
    public function beginTransaction()
    {
        ++$this->transactions;

        if ($this->transactions == 1) {
            $this->pdo->beginTransaction();
        }
    }

    /**
     * @return void
     */
    public function commit()
    {
        if ($this->transactions == 1) {
            $this->pdo->commit();
        }

        --$this->transactions;
    }

    /**
     * @return void
     */
    public function rollBack()
    {
        if ($this->transactions == 1) {
            $this->transactions = 0;
            $this->pdo->rollBack();
        } else {
            --$this->transactions;
        }
    }

    /**
     * @return int
     */
    public function transactionLevel()
    {
        return $this->transactions;
    }

    public function __destruct()
    {
        $this->pdo = null;
    }
}