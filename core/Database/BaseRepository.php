<?php

declare(strict_types=1);

namespace Core\Database;

use PDO;
use PDOStatement;
use App\Exceptions\DatabaseException;

abstract class BaseRepository
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    protected function query(string $sql, array $params = []): PDOStatement
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (\PDOException $e) {
            throw new DatabaseException('Query failed: ' . $e->getMessage());
        }
    }

    protected function fetchOne(string $sql, array $params = []): ?array
    {
        $result = $this->query($sql, $params)->fetch();
        return $result !== false ? $result : null;
    }

    protected function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    protected function fetchColumn(string $sql, array $params = []): mixed
    {
        return $this->query($sql, $params)->fetchColumn();
    }

    protected function execute(string $sql, array $params = []): int
    {
        return $this->query($sql, $params)->rowCount();
    }

    protected function insert(string $sql, array $params = []): string|false
    {
        $this->query($sql, $params);
        return $this->db->lastInsertId();
    }

    protected function lastInsertId(): string
    {
        return $this->db->lastInsertId();
    }

    protected function transaction(callable $callback): mixed
    {
        $this->db->beginTransaction();
        try {
            $result = $callback($this->db);
            $this->db->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    protected function paginate(string $sql, array $params, int $page, int $perPage): array
    {
        $offset     = ($page - 1) * $perPage;
        $countSql   = 'SELECT COUNT(*) FROM (' . $sql . ') AS _count_query';
        $total      = (int) $this->fetchColumn($countSql, $params);
        $items      = $this->fetchAll($sql . ' LIMIT ? OFFSET ?', [...$params, $perPage, $offset]);

        return ['items' => $items, 'total' => $total];
    }
}
