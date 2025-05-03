<?php
class Database {
    private static $pdo;
    public static function get() {
        if (!self::$pdo) {
            $c = require __DIR__ . '/config.php';
            $dsn = sprintf(
              "mysql:host=%s;port=%s;dbname=%s;charset=%s",
              $c['db_host'],
              $c['db_port'],
              $c['db_name'],
              $c['db_charset']
            );
            self::$pdo = new PDO($dsn, $c['db_user'], $c['db_pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }
        return self::$pdo;
    }
}
