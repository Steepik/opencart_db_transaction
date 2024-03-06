<?php

declare(strict_types=1);

namespace DbTransaction;

interface Connection
{
    public function query(string $sql);
    public function escape(string $value): string;
    public function countAffected(): int;
    public function getLastId(): ?int;
    public function isConnected(): bool;
    public function transaction(\Closure $callback);
    public function commit(): void;
    public function rollBack(): void;
    public function transactionLevel(): int;
}