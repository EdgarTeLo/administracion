<?php
namespace App\Config;

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use PDO;
use PDOException;

class Database {
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $conn;
    private static $instances = [];

    private function __construct($dbname) {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();

        $this->host = $_ENV['DB_HOST'];
        $this->dbname = $dbname;
        $this->username = $_ENV['DB_USER'];
        $this->password = $_ENV['DB_PASS'];
    }

    public static function getInstance($dbname) {
        if (!isset(self::$instances[$dbname])) {
            self::$instances[$dbname] = new self($dbname);
        }
        return self::$instances[$dbname];
    }

    public function getConnection() {
        if ($this->conn !== null) {
            return $this->conn;
        }

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->dbname,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch (PDOException $e) {
            error_log("Error de conexión a {$this->dbname}: " . $e->getMessage());
            die("No se pudo conectar a la base de datos {$this->dbname}. Por favor, intenta de nuevo más tarde.");
        }

        return $this->conn;
    }

    private function __clone() {}
}