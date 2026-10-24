<?php

namespace App\Database;

use PDO;
use PDOException;

class Database {
    private static ?Database $instance = null;
    private ?PDO $connection = null;

    // Credenciales basadas en el docker-compose 
    private string $host = 'db';          // Nombre del servicio de MariaDB en Docker
    private string $dbName = 'RESERVAS_MISTERPLAN';
    private string $username = 'root';
    private string $password = 'rootpassword';

    // Constructor privado para evitar que se instancie directamente (Singleton)
    private function __construct() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset=utf8mb4";
            
            $this->connection = new PDO($dsn, $this->username, $this->password, [
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

    // Evitamos que el objeto pueda ser clonado (Singleton)
    private function __clone() {}

    // Evitamos que pueda ser deserializado
    public function __wakeup() {}
}