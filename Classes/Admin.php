<?php
namespace DELIVERY\Classes\Admin;

require_once __DIR__ . '/../Database/Database.php';
require_once __DIR__ . '/User.php';
use DELIVERY\User\User;
use DELIVERY\Database\Database;
use Exception;
use PDO;

class Admin extends User {
    public function __construct() {
        parent::__construct('', '', '');
    }

    public function login($Email, $Password){
        // Connect
        $conn = new \DELIVERY\Database\Database();
        // Prepare the Request
        $query = "SELECT * FROM Users WHERE Email = :Email";
        $statement = $conn->getStarted()->prepare($query);
        $statement->bindParam(":Email", $Email);
        $statement->execute();
        // Fetch the Result
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if($result) {
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

    public function createOrder($Client_Name, $Address, $Details){
        // Connect
        $conn = new \DELIVERY\Database\Database();
        // Get Client ID
        $query = "SELECT ID FROM Users WHERE Full_Name = :Client_Name AND Permission = 'Client'";
        $statement = $conn->getStarted()->prepare($query);
        $statement->bindParam(":Client_Name", $Client_Name);
        $statement->execute();
        $client_id = $statement->fetch(PDO::FETCH_ASSOC)['ID'];
        // Prepare the Request
        $query = "INSERT INTO orders (Client_ID, Client_Name, Address, Status) VALUES (:Client_ID, :Client_Name, :Address, 'Pending')";
        $statement = $conn->getStarted()->prepare($query);
        $statement->bindParam(":Client_ID", $client_id);
        $statement->bindParam(":Client_Name", $Client_Name);
        $statement->bindParam(":Address", $Address);
        $statement->execute();
      }

    public function assignOrderToDriver($Order_ID, $Driver_ID){
        // Connect
        $conn = new \DELIVERY\Database\Database();
        // Prepare the Request
        $query = "UPDATE orders SET Driver_ID = :Driver_ID WHERE ID = :ID";
        $statement = $conn->getStarted()->prepare($query);
        $statement->bindParam(":Driver_ID", $Driver_ID);
        $statement->bindParam(":ID", $Order_ID);
        $statement->execute();
    }

    public function viewOrders() {
        // Connect
        $conn = new \DELIVERY\Database\Database();
        // Prepare the Request
        $query = "SELECT o.ID, o.Client_ID, o.Client_Name, o.Address, o.Status, o.Driver_ID FROM orders o";
        $statement = $conn->getStarted()->prepare($query);
        $statement->execute();
        // Fetch the Result
        $orders = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $orders;
    }

    public function viewDrivers() {
        // Connect
        $conn = new \DELIVERY\Database\Database();
        // Prepare the Request
        $query = "SELECT Driver_ID, Driver_Name, Available FROM drivers WHERE Available = 'Yes'";
        $statement = $conn->getStarted()->prepare($query);
        $statement->execute();
        // Fetch the Result
        $drivers = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $drivers;
    }
}