<?php
// ============================================================
//  src/Core/Database.php — Singleton PDO
// ============================================================

//Clase Database que implementa el patrón Singleton para gestionar una única conexión PDO a la base de datos MySQL, utilizando las credenciales definidas en config/database.php.
// Proporciona un método estático getConnection() para obtener la instancia de PDO.
// Por más que se llame muchas veces en el código, siempre usa la misma conexión en vez de abrir una nueva cada vez.
namespace OfficeHub\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $cfg = require BASE_PATH . '/config/database.php';

            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $cfg['host'],
                $cfg['port'],
                $cfg['dbname'],
                $cfg['charset']
            );

            try {
                self::$instance = new PDO($dsn, $cfg['username'], $cfg['password'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                die('Error de conexión a la base de datos: ' . $e->getMessage());
            }
        }

        return self::$instance;
    }

    private function __construct() {}
    private function __clone() {}
}
