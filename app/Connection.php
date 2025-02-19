<?php

namespace Hexlet\Code;

use Exception;
use PDO;

final class Connection
{
    private const DATABASE_DEFAULT_PORT = 5432;

    /**
     * @throws Exception
     */
    public static function create(string $databaseUrl): PDO
    {
        $databaseParams = parse_url($databaseUrl);

        $host = $databaseParams['host'] ?? '';
        $port = $databaseParams['port'] ?? self::DATABASE_DEFAULT_PORT;
        $user = $databaseParams['user'] ?? '';
        $password = $databaseParams['pass'] ?? '';
        $dbName = ltrim($databaseParams['path'] ?? '', '/');

        $dsn = "pgsql:
        host={$host};
        port={$port};
        dbname={$dbName};
        user={$user};
        password={$password}";

        try {
            $pdo = new \PDO($dsn);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (\PDOException $e) {
            throw new \PDOException("Ошибка подключения к базе данных: " . $e->getMessage());
        }
    }
}
