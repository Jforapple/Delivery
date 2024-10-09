<?php
namespace DELIVERY\Client;

use PDO;
use PDOException;
use DELIVERY\Database\Database;

class Client
{
    public function login($Email, $Password)
    {
        // Connect
        $conn = new \DELIVERY\Database\Database();
        // Prepare the Request
        $query = "SELECT * FROM Users WHERE Email = :Email";
        $statement = $conn->getStarted()->prepare($query);
        $statement->bindParam(":Email", $Email);
        $statement->execute();
        // Fetch the Result
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            if (password_verify($Password, $result['Password'])) {
                // Login successful, set session variables
                $_SESSION['ID'] = $result['ID'];
                $_SESSION['Email'] = $result['Email'];
                $_SESSION['Full_Name'] = $result['Full_Name'];
                $_SESSION['Permission'] = $result['Permission'];
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function createUser($Email, $Password, $Full_Name, $Role)
    {
        if ($Role == 'Admin') {
            return "You are not allowed to sign up as an Admin.";
        } else {
            // Connect
            $conn = new \DELIVERY\Database\Database();
            // Prepare the Request
            $query = "SELECT * FROM Users WHERE Email = :Email";
            $statement = $conn->getStarted()->prepare($query);
            $statement->bindParam(":Email", $Email);
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                return "Email address already exists.";
            } else {
                // Prepare the Request
                $query = "INSERT INTO Users (Email, Password, Full_Name, Permission, Created_At) VALUES (:Email, :Password, :Full_Name, :Permission, :Created_At)";
                $statement = $conn->getStarted()->prepare($query);
                // Encryption
                $encrypted_password = password_hash($Password, PASSWORD_BCRYPT);
                $statement->bindParam(":Email", $Email);
                $statement->bindParam(":Password", $encrypted_password);
                $statement->bindParam(":Full_Name", $Full_Name);
                $statement->bindParam(":Permission", $Role);
                $current_date = date("Y-m-d H:i:s");
                $statement->bindParam(":Created_At", $current_date);
                $statement->execute();
                // Store the role in the session
                $_SESSION['role'] = $Role;
                return "User  account created successfully!";
            }
        }
    }

    public function viewOrders()
    {
        // Connect
        $conn = new \DELIVERY\Database\Database();
        // Prepare the Request
        $query = "SELECT * FROM orders WHERE Client_ID = :Client_ID";
        $statement = $conn->getStarted()->prepare($query);
        $statement->bindParam(":Client_ID", $_SESSION['ID']);
        $statement->execute();
        // Fetch the Result
        $orders = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $orders;
    }

    public function viewOrderHistory()
    {
        $conn = new Database();
        $query = "SELECT oh.Order_ID, oh.Client_ID, o.Address, oh.Status, oh.Updated_At 
                  FROM order_history oh 
                  JOIN orders o ON oh.Order_ID = o.ID 
                  WHERE oh.Client_ID = :Client_ID";
        $statement = $conn->getStarted()->prepare($query);
        $statement->bindParam(":Client_ID", $_SESSION['ID']);
        $statement->execute();
        return $statement->fetchAll();
    }
}
?>