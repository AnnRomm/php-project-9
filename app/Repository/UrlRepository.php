<?php

namespace Hexlet\Code\Repository;

use PDO;

class UrlRepository
{
    private PDO $pdo;

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
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // Получение проверок URL по ID
    public function getUrlChecksByUrlId(int $id): array
    {
        $query = 'SELECT * FROM url_checks WHERE url_id = ? ORDER BY id DESC';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    public function findByName(string $url): ?int
    {
        $query = 'SELECT id FROM urls WHERE name = ?';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$url]);
        return $stmt->fetchColumn() ?: null;
    }
    public function insertNewUrl(string $url, string $currentTime): int
    {
        $query = 'INSERT INTO urls (name, created_at) VALUES (?, ?)';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$url, $currentTime]);

        return $this->pdo->lastInsertId();
    }
}
