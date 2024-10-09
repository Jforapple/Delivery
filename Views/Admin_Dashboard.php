<?php
session_start();
require_once '../Classes/Admin.php';
require_once '../Database/Database.php';
use DELIVERY\Classes\Admin\Admin;
use DELIVERY\Database\Database;

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Admin') {
    header('Location: ../Views/Login.php');
    exit;
}

if (isset($_POST['signout'])) {
    session_unset();
    session_destroy();
    header('Location: ../Views/Login.php');
    exit;
}

if (isset($_POST['add_order'])) {
  $Client_Name = $_POST['Client_Name'];
  $Address = $_POST['Address'];
  $admin = new Admin();
  $admin->createOrder($Client_Name, $Address, '');
  // Redirect to the same page to refresh the order list
  header('Location: Admin_Dashboard.php');
  exit;
}

if (isset($_POST['delete_order'])) {
    $order_id = $_POST['order_id'];

    $conn = new Database();
    // Delete the order history records first
    $query = "DELETE FROM order_history WHERE Order_ID = :ID";
    $statement = $conn->getStarted()->prepare($query);
    $statement->bindParam(":ID", $order_id);
    $statement->execute();

    // Then delete the order
    $query = "DELETE FROM orders WHERE ID = :ID";
    $statement = $conn->getStarted()->prepare($query);
    $statement->bindParam(":ID", $order_id);
    $statement->execute();

    // Refresh the page to reflect the changes
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_POST['assign_driver'])) {
    $order_id = $_POST['order_id'];
    $driver_id = $_POST['driver_id'];

    // Assign the driver to the order
    $conn = new \DELIVERY\Database\Database();
    $query = "UPDATE orders SET Driver_ID = :Driver_ID, Status = 'In Transit' WHERE ID = :ID";
    $statement = $conn->getStarted()->prepare($query);
    $statement->bindParam(":Driver_ID", $driver_id);
    $statement->bindParam(":ID", $order_id);
    $statement->execute();

    // Refresh the page to reflect the changes
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    $conn = new Database();
    $query = "UPDATE orders SET Status = :Status WHERE ID = :ID";
    $statement = $conn->getStarted()->prepare($query);
    $statement->bindParam(":ID", $order_id);
    $statement->bindParam(":Status", $status);
    $statement->execute();

    // Refresh the page to reflect the changes
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_POST['delete_driver'])) {
    $driver_id = $_POST['driver_id'];

    $conn = new Database();
    $query = "DELETE FROM drivers WHERE Driver_ID = :Driver_ID";
    $statement = $conn->getStarted()->prepare($query);
    $statement->bindParam(":Driver_ID", $driver_id);
    $statement->execute();

    // Refresh the page to reflect the changes
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$admin = new Admin();
$orders = $admin->viewOrders();
$drivers = $admin->viewDrivers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
    <div class="container">
        <h1>Admin Dashboard</h1>
        <p>Welcome, <?php echo $_SESSION['Full_Name']; ?>!</p>
        <!-- Add admin-specific features and functionality here -->
        <form method="post">
          <div class="row mb-3">
            <label for="inputClientName" class="col-sm-2 col-form-label">Client Name</label>
            <div class="col-sm-10">
              <select name="Client_Name" required>
                <?php
                $conn = new Database();
                $query = "SELECT ID, Full_Name FROM Users WHERE Permission = 'Client'";
                $statement = $conn->getStarted()->prepare($query);
                $statement->execute();
                $clients = $statement->fetchAll(PDO::FETCH_ASSOC);
                foreach ($clients as $client) {
                  echo "<option value='" . $client['Full_Name'] . "'>" . $client['Full_Name'] . "</option>";
                }
                ?>
              </select>
            </div>
          </div>
          <div class="row mb-3">
            <label for="inputAddress" class="col-sm-2 col-form-label">Address</label>
            <div class="col-sm-10">
              <input type="text" class="form-control" id="inputAddress" name="Address" required>
            </div>
          </div>
          <button type="submit" class="btn btn-primary" name="add_order">Add Order</button>
        </form>
        <br>
        <h2>Orders</h2>
        <table class="table table-striped">
          <thead>
            <tr>
              <th scope="col">ID</th>
              <th scope="col">Client Name</th>
              <th scope="col">Address</th>
              <th scope="col">Status</th>
              <th scope="col">Delete</th>
              <th scope="col">Assign Driver</th>
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
                  <td><?php echo $order['Client_Name']; ?></td>
                  <td><?php echo $order['Address']; ?></td>
                  <td><?php echo $order['Status']; ?></td>
                  <td>
                    <form method="post">
                      <input type="hidden" name="order_id" value="<?php echo $order['ID']; ?>">
                      <button type="submit" class="btn btn-danger" name="delete_order">Delete</button>
                    </form>
                  </td>
                  <td>
                    <form method="post">
                        <input type="hidden" name="order_id" value="<?php echo $order['ID']; ?>">
                        <select name="driver_id" required>
                            <?php
                            foreach ($drivers as $driver) {
                                ?>
                                <option value="<?php echo $driver['Driver_ID']; ?>"><?php echo $driver['Driver_ID']; ?></option>
                                <?php
                            }
                            ?>
                        </select>
                        <button type="submit" class="btn btn-primary" name="assign_driver">Assign Driver</button>
                    </form>
                </td>
                  <td>
                    <form method="post">
                      <input type="hidden" name="order_id" value="<?php echo $order['ID']; ?>">
                      <select name="status" required>
                        <option value="Pending">Pending</option>
                        <option value="In Transit">In Transit</option>
                        <option value="Delivered">Delivered</option>
                      </select>
                      <button type="submit" class="btn btn-primary" name="update_status">Update Status</button>
                    </form>
                  </td>
                </tr>
                <?php
              }
            } else {
              ?>
              <tr>
                <td colspan="7">No orders found.</td>
              </tr>
              <?php
            }
            ?>
          </tbody>
        </table>

        <h2>Drivers</h2>
        <table class="table table-striped">
          <thead>
            <tr>
              <th scope="col">Driver ID</th>
              <th scope="col">Driver Name</th>
              <th scope="col">Delete</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if (count($drivers) > 0) {
              foreach ($drivers as $driver) {
                ?>
                <tr>
                  <td><?php echo $driver['Driver_ID']; ?></td>
                  <td><?php echo $driver['Driver_Name']; ?></td>
                  <td>
                    <form method="post">
                      <input type="hidden" name="driver_id" value="<?php echo $driver['Driver_ID']; ?>">
                      <button type="submit" class="btn btn-danger" name="delete_driver">Delete</button>
                    </form>
                  </td>
                </tr>
                <?php
              }
            } else {
              ?>
              <tr>
                <td colspan="3">No drivers found.</td>
              </tr>
              <?php
            }
            ?>
          </tbody>
        </table>

        <form method="post">
          <button type="submit" class="btn btn-danger" name="signout">Sign Out</button>
        </form>
    </div>
</body>
</html>