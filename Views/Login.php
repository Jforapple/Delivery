<?php
session_start();
require_once '../Classes/Admin.php';
require_once '../Classes/User.php';
require_once '../Classes/Client.php'; 
use DELIVERY\Classes\Admin\Admin;
use DELIVERY\Client\Client; 

$result = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login'])) {
        $Email = $_POST['Email'];
        $Password = $_POST['Password'];

        $Client = new Client();
        $result = $Client->login($Email, $Password);

        // Login.php
        if ($result) {
            $conn = new \DELIVERY\Database\Database();
            // Retrieve the role and full name of the user from the database
            $query = "SELECT Permission, Full_Name FROM Users WHERE Email = :Email";
            $statement = $conn->getStarted()->prepare($query);
            $statement->bindParam(":Email", $Email);
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            $role = $result['Permission'];
            $full_name = $result['Full_Name'];
    
            // Store the role and full name in the session
            $_SESSION['role'] = $role;
            $_SESSION['Full_Name'] = $full_name;
    
            // Redirect to the dashboard based on the role
            if ($_SESSION['role'] == 'Admin') {
                header('Location: Admin_Dashboard.php');
            } elseif ($_SESSION['role'] == 'Client') {
                header('Location: Client_Dashboard.php');
            } elseif ($_SESSION['role'] == 'Driver') {
                header('Location: Driver_Dashboard.php');
            }
            exit;
        } 
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .container {
            padding-top: 20vh;
        }
    </style>
</head>
<body>
<div class="container">
    <?php
    if (isset($_POST['login']) && $result === false) {
        ?>
        <div class="alert alert-danger" role="alert">
            Incorrect email or password.
        </div>
        <?php
    }
    ?>
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow p-3 mb-5 bg-white rounded">
                <h2>Login</h2>
                <form method="post">
                    <div class="mb-3">
                        <label for="exampleInputEmail1" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" name="Email">
                    </div>
                    <div class="mb-3">
                        <label for="exampleInputPassword1" class="form-label">Password</label>
                        <input type="password" class="form-control" id="exampleInputPassword1" name="Password">
                    </div>
                    <button type="submit" class="btn btn-primary" name="login">Login</button>
                </form><br>
                <p>Don't have an account? <a href="Signup.php">Sign Up</a></p>
            </div>
        </div>
    </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-qTO6oMS2hdBnEpQRZxTcOKDR8uJGWcgNs9Q50RiAvUe5dZhHZe9qkT3gshiT84wG" crossorigin="anonymous"></script>
</body>
</html>