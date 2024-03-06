<?php

declare(strict_types=1);

namespace DbTransaction;

use PDO;

final class PDOConnection implements Connection
{
    private ?\PDO $pdo;

    private array $data = [];

    private int $affected;

    private int $transactions = 0;

    public function __construct(string $hostname, string $username, string $password, string $database, string $port = '3306')
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

    public function query(string $sql)
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

    public function escape(?string $value): string
    {
        if ($value === '+00:00') {
            return '+00:00';
        }

        $key = ':' . count($this->data);

        $this->data[$key] = $value;

        return $key;
    }

    public function countAffected(): int
    {
        return $this->affected;
    }

    public function getLastId(): ?int
    {
        $id = $this->pdo->lastInsertId();

        return $id ? (int)$id : null;
    }

    public function isConnected(): bool
    {
        return $this->pdo !== null;
    }

    public function transaction(\Closure $callback)
    {
        $this->beginTransaction();

        try {
            $result = $callback($this);
            $this->commit();

            return $result;
        } catch (\Throwable $exception) {
            $this->rollBack();

            throw $exception;
        }
    }

    public function beginTransaction(): void
    {
        ++$this->transactions;

        if ($this->transactions == 1) {
            $this->pdo->beginTransaction();
        }
    }

    public function commit(): void
    {
        if ($this->transactions == 1) {
            $this->pdo->commit();
        }

        --$this->transactions;
    }

    public function rollBack(): void
    {
        if ($this->transactions == 1) {
            $this->transactions = 0;
            $this->pdo->rollBack();
        } else {
            --$this->transactions;
        }
    }

    public function transactionLevel(): int
    {
        return $this->transactions;
    }

    public function __destruct()
    {
        $this->pdo = null;
    }
}