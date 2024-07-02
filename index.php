<?php
session_start();

$host = "localhost";
$username = "root";
$password = "";
$database = "project";
$message = "";

// Database Connection
$conn = mysqli_connect($host, $username, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_POST['login'])) {
    $check_username = $_POST['username'];
    $check_password = md5($_POST['password']);

    //For Admin
    $admin_sql = "SELECT * FROM login WHERE username = '$check_username' AND password = '$check_password' AND usertype = 'admin'";
    $result = mysqli_query($conn, $admin_sql);
    if (mysqli_num_rows($result) == 1) {
        $_SESSION['username'] = $check_username;
        $_SESSION['usertype'] = 'admin';
        header("Location: dashboard/admin/admin.php");
        exit();
    }

    //For student
    $student_sql = "SELECT * FROM login WHERE username = '$check_username' AND password = '$check_password' AND usertype = 'student'";
    $result = mysqli_query($conn, $student_sql);
    if (mysqli_num_rows($result) == 1) {
        $_SESSION['username'] = $check_username;
        $_SESSION['usertype'] = 'student';
        header("Location: dashboard/student/student.php");
        exit();
    } else {
        $message = "Invalid username or password.";      
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Page</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="container">
        <form action="index.php" method="POST">
            <h2>Assignment Submission System</h2>
            <div class="input-group">
                <label for="username">Username:</label>
                <input type="text" name="username" required>
            </div>
            <div class="input-group">
                <label for="password">Password:</label>
                <input type="password" name="password" required>
            </div>
            <span>
                <?php if ($message){
                    echo $message;
                } ?>
            </span>
            <div class="input-group">
                <input type="submit" name="login" class="btn" value="Login">
            </div>
        </form>
    </div>
</body>
</html>
