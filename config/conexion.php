<?php
class Database {
    private $host = 'localhost';
    private $dbname = 'jesca01_jesca';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->dbname, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8"); // Opcional, para soporte UTF-8
        } catch(PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
            die();
        }
        return $this->conn;
    }
}
?>