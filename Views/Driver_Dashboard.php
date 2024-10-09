<?php
session_start();
require_once '../Classes/Driver.php';
use DELIVERY\Driver\Driver;
use DELIVERY\Database\Database;

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Driver') {
    header('Location: ../Views/Login.php');
    exit;
}

if (isset($_POST['signout'])) {
    session_unset();
    session_destroy();
    header('Location: ../Views/Login.php');
    exit;
}

if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    // Update the order status
    $conn = new \DELIVERY\Database\Database();
    $query = "UPDATE orders SET Status = :Status WHERE ID = :ID";
    $statement = $conn->getStarted()->prepare($query);
    $statement->bindParam(":ID", $order_id);
    $statement->bindParam(":Status", $status);
    $statement->execute();

    // Insert a new record into the order_history table
    $query = "INSERT INTO order_history (Order_ID, Client_ID, Status, Updated_At) 
              SELECT ID, Client_ID, :Status, :Updated_At FROM orders WHERE ID = :ID";
    $statement = $conn->getStarted()->prepare($query);
    $statement->bindParam(":ID", $order_id);
    $statement->bindParam(":Status", $status);
    $current_date = date("Y-m-d H:i:s");
    $statement->bindParam(":Updated_At", $current_date);
    $statement->execute();

    // Redirect to the same page to refresh the order list
    header('Location: Driver_Dashboard.php');
    exit;
}

if (!isset($_SESSION['Driver_ID'])) {
    $conn = new \DELIVERY\Database\Database();
    $query = "SELECT Driver_ID FROM drivers WHERE Driver_Name = :Driver_Name";
    $statement = $conn->getStarted()->prepare($query);
    $statement->bindParam(":Driver_Name", $_SESSION['Full_Name']);
    $statement->execute();
    $driver_id = $statement->fetch(PDO::FETCH_ASSOC);
    $_SESSION['Driver_ID'] = $driver_id['Driver_ID'];
}

// Fetch orders from database and display them in the table
$conn = new \DELIVERY\Database\Database();
$query = "SELECT o.ID, o.Client_ID, o.Client_Name, o.Address, o.Status AS Status, o.Driver_ID FROM orders o WHERE o.Driver_ID = :Driver_ID";
$statement = $conn->getStarted()->prepare($query);
$statement->bindParam(":Driver_ID", $_SESSION['Driver_ID']);
$statement->execute();
$orders = $statement->fetchAll(PDO::FETCH_ASSOC);

// Fetch current driver's information
$conn = new \DELIVERY\Database\Database();
$query = "SELECT Driver_ID, Driver_Name, Available FROM drivers WHERE Driver_ID = :Driver_ID";
$statement = $conn->getStarted()->prepare($query);
$statement->bindParam(":Driver_ID", $_SESSION['Driver_ID']);
$statement->execute();
$driver = $statement->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .container {
            padding-top: 20vh;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Driver Dashboard</h1>
    <p>Welcome, <?php echo $_SESSION['Full_Name']; ?>!</p>
    <h2>Driver Information</h2>
    <table class="table table-striped">
      <thead>
        <tr>
          <th scope="col">Driver ID</th>
          <th scope="col">Driver Name</th>
          <th scope="col">Availability</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><?= $driver['Driver_ID'] ?></td>
          <td><?= $driver['Driver_Name'] ?></td>
          <td>
            <form action="" method="post">
              <input type="hidden" name="driverId" value="<?= $driver['Driver_ID'] ?>">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="availability-switch" name="availability" <?php if ($driver['Available'] == 'Yes') echo 'checked'; ?>>
                <label class="form-check-label" for="availability-switch">Available</label>
              </div>
              <button type="submit" class="btn btn-primary" name="update_availability">Update</button>
            </form>
          </td>
        </tr>
      </tbody>
    </table>

    <?php
    if (isset($_POST['update_availability'])) {
      $driverId = $_POST['driverId'];
      $availability = isset($_POST['availability']) ? 'Yes' : 'No';
      // Update the driver's availability in the database
      $conn = new \DELIVERY\Database\Database();
      $query = "UPDATE drivers SET Available = :Availability WHERE Driver_ID = :Driver_ID";
      $statement = $conn->getStarted()->prepare($query);
      $statement->bindParam(":Availability", $availability);
      $statement->bindParam(":Driver_ID", $driverId);
      $statement->execute();
    }
    ?>
    <h2>Orders</h2>
    <table class="table table-striped">
      <thead>
        <tr>
          <th scope="col">Order ID</th>
          <th scope="col">Client ID</th>
          <th scope="col">Client Name</th>
          <th scope="col">Address</th>
          <th scope="col">Status</th>
          <th scope="col">Update Status</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if (count($orders) > 0) {
          foreach ($orders as $order) {
        ?>
            <tr>
              <td><?php echo $order['ID']; ?></td>
              <td><?php echo $order['Client_ID']; ?></td>
              <td><?php echo $order['Client_Name']; ?></td>
              <td><?php echo $order['Address']; ?></td>
              <td><?php echo $order['Status']; ?></td>
              <td>
                <form action="" method="post">
                  <input type="hidden" name="order_id" value="<?php echo $order['ID']; ?>">
                  <select name="status">
                    <option value="Pending">Pending</option>
                    <option value="In Transit">In Transit</option>
                    <option value="Delivered">Delivered</option>
                  </select>
                  <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                </form>
              </td>
            </tr>
        <?php
          }
        } else {
        ?>
          <tr>
            <td colspan="6">No orders.</td>
          </tr>
        <?php
        }
        ?>
      </tbody>
    </table>
    <form action="" method="post">
      <button type="submit" name="signout" class="btn btn-danger">Sign out</button>
    </form>
</div>
</body>
</html>