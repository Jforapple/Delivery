<?php
session_start();
require_once '../Classes/Client.php';
require_once '../Database/Database.php';
use DELIVERY\Client\Client;
use DELIVERY\Database\Database;

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Client') {
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

    $conn = new Database();
    $query = "INSERT INTO orders (Client_ID, Client_Name, Address, Status) VALUES (:Client_ID, :Client_Name, :Address, 'Pending')";
    $statement = $conn->getStarted()->prepare($query);
    $statement->bindParam(":Client_ID", $_SESSION['ID']);
    $statement->bindParam(":Client_Name", $Client_Name);
    $statement->bindParam(":Address", $Address);
    $statement->execute();

    // Redirect to the same page to refresh the order list
    header('Location: Client_Dashboard.php');
    exit;
}

if (isset($_POST['cancel_order'])) {
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
    header('Location: Client_Dashboard.php');
    exit;
}

$client = new Client();
$orders = $client->viewOrders();

$total_pages = ceil(count($orders) / 10); // assuming 10 orders per page
$current_page = isset($_GET['page']) ? $_GET['page'] : 1;
$start = ($current_page - 1) * 10;
$orders = array_slice($orders, $start, 10);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
    <div class="container">
        <h1>Client Dashboard</h1>
        <p>Welcome, <?php echo $_SESSION['Full_Name']; ?>!</p>
        <h2>Orders</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">Order ID</th>
                    <th scope="col">Client ID</th>
                    <th scope="col">Address</th>
                    <th scope="col">Status</th>
                    <th scope="col">Actions</th>
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
                            <td><?php echo $order['Address']; ?></td>
                            <td><?php echo $order['Status']; ?></td>
                            <td>
                                <form method=" post">
                                    <input type="hidden" name="order_id" value="<?php echo $order['ID']; ?>">
                                    <button type="submit" class="btn btn-danger" name="cancel_order">Cancel Order</button>
                                </form>
                            </td>
                        </tr>
                <?php
                    }
                } else {
                ?>
                    <tr>
                        <td colspan="5">No orders found.</td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
        <nav aria-label="Page navigation example">
            <ul class="pagination">
                <li class="page-item disabled">
                    <a class=" page-link" tabindex="-1">Previous</a>
                </li>
                <?php
                for ($i = 1; $i <= $total_pages; $i++) {
                    if ($i == $current_page) {
                        echo "<li class='page-item active' aria-current='page'>
                                <a class='page-link' href='?page=$i'>$i</a>
                              </li>";
                    } else {
                        echo "<li class='page-item'>
                                <a class='page-link' href='?page=$i'>$i</a>
                              </li>";
                    }
                }
                ?>
                <li class="page-item">
                    <a class="page-link" href="#">Next</a>
                </li>
            </ul>
        </nav>
        <form method="post">
            <button type="submit" class="btn btn-primary" name="signout">Sign Out</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-qTO6oMS2hdBx1jzZdGcxC3QjLUfQVvBxUxVZOlLfQMO7WDRmO7pGK38MvhgFZT" crossorigin="anonymous"></script>
</body>
</html>