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
  <title>Assignment Management System - Admin</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
  <div class="container">
    <h2>Welcome, Admin!</h2>
    <h3>Assignment Submission System</h3>
    <div class="options">
      <h4>Options:</h4>
      <ul>
      <li><a href="users/users.php"><img src="../images/users.png" alt="User Management" class="icon">User Management</a></li>
        <li><a href="subject/subject.php"><img src="../images/subject.png" alt="Subject Management" class="icon">Subject Management</a></li>
        <li><a href="assignment/assignment.php"><img src="../images/assignment.png" alt="Assignment Management" class="icon">Assignment Management</a></li>
        <li><a href="view_assignment/view_assignment.php"><img src="../images/submitted.png" alt="Submitted Assignments" class="icon">Submitted Assignments</a></li>
      </ul>
      <form action="../../logout.php" method="post">
        <input type="submit" value="Logout">
      </form>
      <a href="../changepassword.php">Change Password</a>
    </div>
  </div>
</body>
</html>
