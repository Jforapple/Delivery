<?php
session_start();
require_once '../Classes/Admin.php';
require_once '../Classes/User.php';
require_once '../Classes/Client.php'; 
use DELIVERY\Classes\Admin\Admin;
use DELIVERY\Client\Client; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['signup'])) {
        $FullName = $_POST['FullName'];
        $Email = $_POST['Email'];
        $Password = $_POST['Password'];
        $ConfirmPassword = $_POST['ConfirmPassword'];
        $Role = $_POST['Role'];

        if ($Password == $ConfirmPassword) {
            if ($Role == 'Admin') {
                ?>
                <div class="alert alert-danger" role="alert">
                    You are not allowed to sign up as an Admin.
                </div>
                <?php
            } else {
                $Client = new Client();
                $result = $Client->createUser ($Email, $Password, $FullName, $Role);
                // Store the result in a session variable
                $_SESSION['result'] = $result;
                // Store the role in the session
                $_SESSION['role'] = $Role;

                // Insert the newly signed-up driver into the drivers table
                if ($Role == 'Driver') {
                    $conn = new \DELIVERY\Database\Database();
                    $query = "INSERT INTO drivers (Driver_Name) VALUES (:Driver_Name)";
                    $statement = $conn->getStarted()->prepare($query);
                    $statement->bindParam(":Driver_Name", $FullName);
                    $statement->execute();
                }

                // Redirect to login page
                header('Location: ../Views/Login.php');
                exit;
            }
        } else {
            ?>
            <div class="alert alert-danger" role="alert">
                Passwords do not match.
            </div>
            <?php
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow p-3 mb-5 bg-white rounded">
                <h2>Sign Up</h2>
                <form method="post">
                    <div class="mb-3">
                        <label for="exampleInputFullName" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="exampleInputFullName" aria-describedby="fullNameHelp" name="FullName">
                        <div id="fullNameHelp" class="form-text">Please enter your full name.</div>
                    </div>
                    <div class="mb-3">
                        <label for="exampleInputEmail1" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" name="Email">
                    </div>
                    <div class="mb-3">
                        <label for="exampleInputPassword1" class="form-label">Password</label>
                        <input type="password" class="form-control" id="exampleInputPassword1" name="Password">
                    </div>
                    <div class="mb-3">
                        <label for="exampleInputPassword2" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="exampleInputPassword2" name="ConfirmPassword">
                    </div>
                    <div class="mb-3">
                        <label for="exampleInputRole" class="form-label">Role</label>
                        <select class="form-select" id="exampleInputRole" aria-label="Default select example" name="Role">
                            <option selected>Choose role</option>
                            <option value="Client">Client</option>
                            <option value="Driver">Driver</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" name="signup">Sign Up</button>
                </form>
                <?php
                if (isset($_POST['signup'])) {
                    $Password = $_POST['Password'];
                    $ConfirmPassword = $_POST['ConfirmPassword'];
                    if ($Password != $ConfirmPassword) {
                        ?>
                        <div class="alert alert-danger" role="alert">
                            Passwords do not match.
                        </div>
                        <?php
                    }
                }
                ?><br>
                <p>Already have an account? <a href="Login.php">Log In</a></p>
            </div>
        </div>
    </div>
</div>
</body>
</html>