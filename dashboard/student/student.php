<?php
session_start();
if (!isset($_SESSION['username'])){
    header("Location: ../../index.php");
    exit();
} elseif ($_SESSION['usertype'] === 'admin') {
    header("Location: ../../index.php");
    exit();
}
?>

<?php
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

// Retrieve student's semester from the login table based on the username
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    // Retrieve student's semester from the login table
    $semester_sql = "SELECT semester FROM login WHERE username = '$username'";
    $semester_result = mysqli_query($conn, $semester_sql);
    $semester_row = mysqli_fetch_assoc($semester_result);

    // Check if semester exists for the student
    if ($semester_row && isset($semester_row['semester'])) {
        $semester = $semester_row['semester'];
        $_SESSION['semester'] = $semester;
    } else {
        // Default to 1st semester if semester is not set in the login table
        $semester = 0;
    }
} else {
    // Redirect to login page if username is not set in the session
    header("Location: ../../index.php");
    exit();
}

// Retrieve subjects based on student's semester
$sql = "SELECT * FROM subject WHERE semester = '$semester'";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Portal</title>
    <link rel="stylesheet" type="text/css" href="student.css">
</head>
<body>
    <div class="header">
        <a href="student.php">Welcome User: <?php echo $username; ?></a>
        <div class="header-links">
            <a href="../changepassword.php">Change Password</a>
            <form action="../../logout.php" method="post" class = "logout-form">
                <input type="submit" value="Logout">
            </form>
        </div>
    </div>
    <div class="main">
        <?php
        if (mysqli_num_rows($result) > 0) {
            // Subjects available for the selected semester
            echo "<div class='container'>";
            echo "<h3>Subjects Available in Semester $semester</h3><hr>";

            while ($row = mysqli_fetch_assoc($result)) {
                $subject_id = $row['subject_id'];
                $subject_name = $row['subject_name'];

                // Retrieve assignment ID and assignment name based on subject ID
                $assignment_sql = "SELECT assignment_id, assignment_name FROM assignment WHERE subject_id = '$subject_id'";
                $assignment_result = mysqli_query($conn, $assignment_sql);

                echo "<h4>$subject_name</h4>";

                if (mysqli_num_rows($assignment_result) > 0) {
                    echo "<ul>";

                    while ($assignment_row = mysqli_fetch_assoc($assignment_result)) {
                        $assignment_id = $assignment_row['assignment_id'];
                        $assignment_name = $assignment_row['assignment_name'];

                        // Set assignment ID in session
                        $_SESSION['assignment_id'] = $assignment_id;

                        // Create a URL with assignment ID and username as query parameters
                        $url = "s_assignment/s_assignment.php?id=$assignment_id";

                        echo "<li><a href='$url'>$assignment_name - ($assignment_id)</a></li>";
                    }

                    echo "</ul>";
                } else {
                    echo "<p>No assignments found for this subject.</p>";
                }
            }

            echo "</div>";
        } else {
            echo "<div class='container'>";
            echo "<p>No subjects found for Semester $semester.</p>";
            echo "</div>";
        }

        // Close the database connection
        mysqli_close($conn);
        ?>
    </div>
</body>
</html>
