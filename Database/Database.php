<?php
namespace DELIVERY\Database;
require_once __DIR__ . '/../Configuration/config.php';
use PDO;
use PDOException;
class Database {
    private $connection;
    public function __construct() {
        try {
            $this->connection = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
        }
        catch(PDOException $ex) {
            die("Connection failed: " . $ex->getMessage());
        }
    }
    public function getStarted(){
        return $this->connection;
    }
}
?>