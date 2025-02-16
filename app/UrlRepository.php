<?php

namespace Hexlet\Code;

use PDO;

class UrlRepository
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAllUrls(): array
    {
        $query = 'SELECT id, name FROM urls ORDER BY created_at DESC';
        return $this->pdo->query($query)->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Получение последних проверок URL
    public function getLastUrlChecks(): array
    {
        $query = 'SELECT DISTINCT ON (url_id) url_id, created_at, status_code
                  FROM url_checks
                  ORDER BY url_id, created_at DESC;';
        return $this->pdo->query($query)->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Получение URL по ID
    public function getUrlById(int $id): ?array
    {
        $query = 'SELECT * FROM urls WHERE id = ?';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    // Получение проверок URL по ID
    public function getUrlChecksByUrlId(int $id): array
    {
        $query = 'SELECT * FROM url_checks WHERE url_id = ? ORDER BY id DESC';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    // Проверяет, существует ли URL в базе
    public function findByName(string $url): ?int
    {
        $query = 'SELECT id FROM urls WHERE name = ?';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$url]);
        return $stmt->fetchColumn() ?: null;
    }

    // Добавляет новый URL в базу
    public function create(string $url): int
    {
        $query = 'INSERT INTO urls (name, created_at) VALUES (?, ?)';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$url, date("Y-m-d H:i:s")]);
        return (int)$this->pdo->lastInsertId();
    }
}
