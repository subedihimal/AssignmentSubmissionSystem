<?php
session_start();
if (!isset($_SESSION['username'])){
    header("Location: ../../index.php");
    exit();
} elseif ($_SESSION['usertype'] === 'student') {
    header("Location: ../../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>User Management</title>
  <link rel="stylesheet" type="text/css" href="../main.css">
</head>
<body>
  <?php
  $conn = mysqli_connect('localhost','root','','project');
  // Check connection
  if (!$conn) {
      die("Connection failed: " . mysqli_connect_error());
  }

  // Function to sanitize user input
  function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
  }

  // Inserting a new user
  if (isset($_POST['insert'])) {
    $username = sanitizeInput($_POST['username']);
    $password = sanitizeInput(md5($_POST['password']));
    $usertype = sanitizeInput($_POST['usertype']);
    $semester = sanitizeInput($_POST['semester']);

    // Check if the username already exists
    $checkQuery = "SELECT * FROM login WHERE username = '$username'";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
      echo "Error: Username already exists.";
    } else {
      // Insert the user into the login table
      $query = "INSERT INTO login (username, password, usertype, semester) VALUES ('$username', '$password', '$usertype', $semester)";
      if (mysqli_query($conn, $query)) {
        echo "User inserted successfully.";
      } else {
        echo "Error inserting user: " . mysqli_error($conn);
      }
    }
  }

    // Deleting a user
  if (isset($_POST['delete'])) {
    $username = sanitizeInput($_POST['delete-username']);

    // Check if the username exists
    $checkQuery = "SELECT * FROM login WHERE username = '$username'";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
    // Retrieve the file paths from the s_assignment table
    $assignmentIds = array();
    $getFileQuery = "SELECT assignment_id, s_file FROM s_assignment WHERE username = '$username'";
    $getFileResult = mysqli_query($conn, $getFileQuery);

    if ($getFileResult) {
        while ($fileRow = mysqli_fetch_assoc($getFileResult)) {
            $assignmentIds[] = $fileRow['assignment_id'];
            $filePath = $fileRow['s_file'];

            // Delete the corresponding PDF file
            if (!empty($filePath) && file_exists($filePath)) {
                if (unlink($filePath)) {
                    echo "File deleted successfully: <br>";
                } else {
                    echo "Error deleting file: <br>";
                }
            } else {
                echo "File does not exist: <br>";
            }
        }
    } else {
        echo "Error retrieving file paths: " . mysqli_error($conn);
    }

    // Delete the related records from the s_assignment table
    $deleteAssignmentQuery = "DELETE FROM s_assignment WHERE username = '$username'";
    if (mysqli_query($conn, $deleteAssignmentQuery)) {
        // Delete the user from the login table
        $deleteUserQuery = "DELETE FROM login WHERE username = '$username'";
        if (mysqli_query($conn, $deleteUserQuery)) {
            echo "User deleted successfully.<br>";
        } else {
            echo "Error deleting user: " . mysqli_error($conn) . "<br>";
        }
    } else {
        echo "Error deleting user's assignments: " . mysqli_error($conn) . "<br>";
    }
    }else{
      echo"Username doesn't exist";
    }
  }


  // Changing a user's password
  if (isset($_POST['change-password'])) {
    $username = sanitizeInput($_POST['change-username']);
    $newPassword = sanitizeInput(md5($_POST['new-password']));

    $checkQuery = "SELECT * FROM login WHERE username = '$username'";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
      // Update the user's password
    $updateSql = "UPDATE login SET password = '$newPassword' WHERE username = '$username'";
    if (mysqli_query($conn, $updateSql)) {
      echo "Password changed successfully!";
    } else {
      echo "Error changing password: " . mysqli_error($conn);
    }
      
    } else {
      echo "Error: Username doesnt exist.";
    
  }
}

  // Upgrade Semester
  if (isset($_POST['upgrade-semester'])) {
    $upgradeSql = "UPDATE login SET semester = semester + 1 WHERE semester > 0";
    if (mysqli_query($conn, $upgradeSql)) {
      echo "Semester upgraded successfully!";
      // Refresh the page to show updated data
      echo '<meta http-equiv="refresh" content="1">';
    } else {
      echo "Error upgrading semester: " . mysqli_error($conn);
    }
  }
  // Retrieve the list of users from the login table
  $query = "SELECT * FROM login";
  $result = mysqli_query($conn, $query);
  $users = array();
  if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
      $users[] = $row;
    }
  }

  // Close the database connection
  mysqli_close($conn);
  ?>
 <div class="container">
    <div class="section">
      <!--Add user section -->
      <h2>Add User<img src="../../images/users.png" class="icon"></h2>
      <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" maxlength="30" required><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" maxlength="30" required><br>

        <label for="usertype">User Type:</label>
        <select id="usertype" name="usertype">
          <option value="student" selected>Student</option>
          <option value="admin">Admin</option>
        </select><br>

        <label for="semester">Semester:</label>
        <select id="semester" name="semester" required>
          <?php for ($i = 0; $i <= 8; $i++) { ?>
            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
          <?php } ?>
        </select><br><br>

        <input type="submit" name="insert" value="Insert">
      </form>
    </div>
    <!--  'Change User Password' section -->
    <div class="section">
      <h2>Change User Password<img src="../../images/password.png" class="icon"></h2>
      <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="change-username">Username:</label>
        <input type="text" id="change-username" name="change-username" maxlength="30" placeholder="Enter username" required list="change-username-list"><br>

        <datalist id="change-username-list">
          <?php foreach ($users as $user) { ?>
            <option value="<?php echo $user['username']; ?>">
          <?php } ?>
        </datalist>

        <label for="new-password">New Password:</label>
        <input type="password" id="new-password" name="new-password" maxlength="30" required><br>

        <input type="submit" name="change-password" value="Change Password">
      </form>
    </div>

    <!-- The 'Delete User' section -->
    <div class="section">
      <h2>Delete User<img src="../../images/deleteuser.png" class="icon"></h2>
      <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="delete-username">Username:</label>
        <input type="text" id="delete-username" name="delete-username" maxlength="30" placeholder="Search username" required list="username-list"><br>

        <datalist id="username-list">
          <?php foreach ($users as $user) { ?>
            <option value="<?php echo $user['username']; ?>">
          <?php } ?>
        </datalist>

        <input type="submit" name="delete" class="delete-btn" value="Delete"onclick="return confirm('Are you sure you want to delete this user and their assignments?');">
      </form>
    </div>
    <a href="../admin.php">Home</a>
        

  </div>

  <h2>Existing Users</h2>
  <!-- Increase Semester -->
  <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <input type="submit" name="upgrade-semester" value="Increase Semester">
      </form>
  <table>
    <tr>
      <th>Username</th>
      <th>User Type</th>
      <th>Semester</th>
    </tr>
    <?php foreach ($users as $user) { ?>
      <tr>
        <td><?php echo $user['username']; ?></td>
        <td><?php echo $user['usertype']; ?></td>
        <td><?php echo $user['semester']; ?></td>
      </tr>
    <?php } ?>
  </table>
</body>
</html>
