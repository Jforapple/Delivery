<?php
namespace DELIVERY\Driver;

require_once __DIR__ . '/User.php';
use DELIVERY\User\User;
use DELIVERY\Database\Database;

class Driver extends User {
    public function __construct() {
        parent::__construct('', '', '');
    }

    public function login($Email, $Password) {}

    public function updateOrderStatus($Order_ID, $Status) {
        // Connect
        $conn = new Database();
        // Prepare the Request
        $query = "UPDATE orders SET Status = :Status WHERE ID = :ID AND Driver_ID = :Driver_ID";
        $statement = $conn->getStarted()->prepare($query);
        $statement->bindParam(":ID", $Order_ID);
        $statement->bindParam(":Status", $Status);
        $statement->bindParam(":Driver_ID", $_SESSION['Driver_ID']);
        $statement->execute();
    }

    public function viewAssignedOrder() {}

    public function updateAvailability($driver_id, $availability) {
        $conn = new Database();
        $query = "UPDATE drivers SET Available = :Availability WHERE Driver_ID = :Driver_ID";
        $statement = $conn->getStarted()->prepare($query);
        $statement->bindParam(":Availability", $availability);
        $statement->bindParam(":Driver_ID", $driver_id);
        $statement->execute();
    }
}
?>