<?php
require_once '../Classes/Database.php';
use DELIVERY\Database\Database;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $driverId = $_POST['driverId'];
    $availability = $_POST['availability'];

    $conn = new Database();
    $query = "UPDATE drivers SET Available = :availability WHERE Driver_ID = :driverId";
    $statement = $conn->getStarted()->prepare($query);
    $statement->bindParam(":driverId", $driverId);
    $statement->bindParam(":availability", $availability);

    try {
        $statement->execute();
        echo "Availability updated successfully!";

        // Verify that the update was successful
        $query = "SELECT Available FROM drivers WHERE Driver_ID = :driverId";
        $statement = $conn->getStarted()->prepare($query);
        $statement->bindParam(":driverId", $driverId);
        $statement->execute();
        $result = $statement->fetch();

        if ($result['Available'] == $availability) {
            echo "Availability updated correctly: " . $result['Available'];
        } else {
            echo "Availability not updated correctly: " . $result['Available'];
        }
    } catch (PDOException $e) {
        echo "Error updating availability: " . $e->getMessage();
    }
} else {
    echo "Invalid request method";
}
?>