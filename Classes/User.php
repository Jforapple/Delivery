<?php
namespace DELIVERY\User;

require_once __DIR__ . '/../Database/Database.php';
use DELIVERY\Database\Database;

abstract class User {
    private $ID;
    private $Email;
    private $Password;
    private $Full_Name;
    private $Permission;
    public function __construct($Email, $Password, $Full_Name) {
        $this->Email = $Email;
        $this->Password = $Password;
        $this->Full_Name = $Full_Name;
    }
    public function createUser($Email, $Password, $Full_Name){
        // Connect
        $conn = new Database();
        // Prepare the Request
        $query = "INSERT INTO User (Email, Password, Full_Name) VALUES (:Email, :Password, :Full_Name)";
        $statement = $conn->getStarted()->prepare($query);
        // Encryption
        $encrypted_password = password_hash($Password, PASSWORD_BCRYPT);
        $statement->bindParam(":Email", $Email);
        $statement->bindParam(":Password", $encrypted_password);
        $statement->bindParam(":Full_Name", $Full_Name);
        $statement->execute();
    }
    
    abstract public function login($Email, $Password);
}
?>