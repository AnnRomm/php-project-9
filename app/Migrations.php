<?php

namespace Hexlet\Code;

use Exception;
use PDO;

class Migrations
{
    /**
     * @throws Exception
     */
    public function run(PDO $conn): void
    {
        $migrationsFilePath = dirname(__DIR__) . '/database.sql';
        $sql = file_get_contents($migrationsFilePath);

        if ($sql === false) {
            throw new Exception('Unable to read migrations file!');
        }

        $conn->exec($sql);
    }
}
