<?php

namespace App\Database;

use PDO;
use PDOException;

class Database {
    private static ?Database $instance = null;
    private ?PDO $connection = null;

    // Constructor privado para evitar que se instancie directamente (Singleton)
    private function __construct() {
        // Leemos de variables de entorno con fallback a tus valores locales
        $host     = getenv('DB_HOST')     ?: 'db';
        $dbName   = getenv('DB_NAME')     ?: 'RESERVAS_MISTERPLAN';
        $username = getenv('DB_USER')     ?: 'root';
        $password = getenv('DB_PASS')     ?: 'rootpassword';

        try {
            $dsn = "mysql:host={$host};dbname={$dbName};charset=utf8mb4";
            
            $this->connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,            
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,    
                PDO::ATTR_EMULATE_PREPARES => false,                
            ]);
        } catch (PDOException $e) {
            throw new \Exception("Error de conexión a la Base de Datos: " . $e->getMessage());
        }
    }

    // Método estático global para obtener la única instancia de la clase
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Método rápido para obtener directamente el objeto PDO y hacer consultas
    public static function getConnection(): PDO {
        return self::getInstance()->connection;
    }

    // Evita que el objeto pueda ser clonado (Singleton)
    private function __clone() {}

    // Evita que pueda ser deserializado
    public function __wakeup() {}
}