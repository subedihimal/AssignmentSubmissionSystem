<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // Redirect to the login page if not logged in
    header("Location: ../../index.php");
    exit();
}

// Database connection settings
$host = "localhost";
$username = "root";
$password = "";
$database = "project";

// Establishing connection to the database
$conn = mysqli_connect($host, $username, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch the current username from the session
$username = $_SESSION['username'];

// Change password form submission
if (isset($_POST['change_password'])) {
    // Retrieve form data
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    // Fetch the user's current password from the database
    $query = "SELECT password FROM login WHERE username = '$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $current_password_hashed = md5($current_password);
        $stored_password_hashed = $row['password'];

        // Verify if the current password matches the stored hashed password
        if ($current_password_hashed === $stored_password_hashed) {
            // Hash the new password
            $hashed_new_password = md5($new_password);

            // Update the password in the database
            $update_query = "UPDATE login SET password = '$hashed_new_password' WHERE username = '$username'";
            if (mysqli_query($conn, $update_query)) {
                echo "Password changed successfully.";
            } else {
                echo "Error updating password: " . mysqli_error($conn);
            }
        } else {
            echo "Invalid current password.";
        }
    }
}

// Close the database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
    <style>
        /* changepassword.css */

.container {
  max-width: 400px;
  margin: 0 auto;
  padding: 20px;
  background-color: #f4f4f4;
  border-radius: 8px;
}

.container h2 {
  text-align: center;
  margin-bottom: 20px;
  color: #333;
}

.container label {
  display: block;
  margin-bottom: 10px;
  color: #555;
}

.container input[type="password"] {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  margin-bottom: 20px;
}

.container input[type="submit"] {
  width: 100%;
  padding: 10px;
  background-color: #4caf50;
  color: #fff;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.container input[type="submit"]:hover {
  background-color: #45a049;
}

.container .error {
  color: #f44336;
  margin-bottom: 10px;
}

    </style>
</head>
<body>
    <div class="container">
        <h2>Change Password</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="current_password">Current Password:</label>
            <input type="password" id="current_password" name="current_password" required><br>

            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" required><br>

            <input type="submit" name="change_password" value="Change Password">
        </form>
    </div>
</body>
</html>
