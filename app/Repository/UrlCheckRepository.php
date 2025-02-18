<?php

namespace Hexlet\Code\Repository;

use PDO;

class UrlCheckRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function createCheck(int $urlId, array $result): void
    {
        $query = 'INSERT INTO url_checks (url_id, status_code, h1, title, description, created_at)
                  VALUES (?, ?, ?, ?, ?, ?)';
        $stmt = $this->pdo->prepare($query);

        $stmt->execute([
            $urlId,
            $result['statusCode'],
            $result['h1'],
            $result['title'],
            $result['description'],
            date('Y-m-d H:i:s')
        ]);
    }
}
