<?php
session_start();
// Check if assignment ID is set in the session
if (!isset($_SESSION['assignment_id'])) {
    header("Location: ../student.php");
    exit;
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

// Get assignment ID 
$assignment_id = $_GET['id'];

// Retrieve assignment details from the database
$assignment_sql = "SELECT assignment_name, due_date, pdf_filepath FROM assignment WHERE assignment_id = '$assignment_id'";
$assignment_result = mysqli_query($conn, $assignment_sql);

// Check if assignment exists
if (mysqli_num_rows($assignment_result) == 1) {
    $assignment_row = mysqli_fetch_assoc($assignment_result);
    $assignment_name = $assignment_row['assignment_name'];
    $due_date = $assignment_row['due_date'];
    $pdf_filepath = $assignment_row['pdf_filepath'];

    // Check if the current date is past the due date
    $current_date = date("Y-m-d");
    if (strtotime($current_date) > strtotime($due_date)) {
        echo "The due date has passed. You cannot upload the file.";
        exit;
    }
} else {
    // Assignment not found, redirect back to student.php
    mysqli_close($conn);
    header("Location: ../student.php");
    exit;
}

// Check if the user has already submitted the assignment
$username = $_SESSION['username'];
$submission_check_sql = "SELECT s_file FROM s_assignment WHERE username = '$username' AND assignment_id = '$assignment_id'";
$submission_check_result = mysqli_query($conn, $submission_check_sql);
$previous_file_path = '';
if (mysqli_num_rows($submission_check_result) > 0) {
    // User has already submitted the assignment
    echo "You have already submitted your assignment. Would you like to update it?";
    $submitButtonText = "Update";

    // Fetch the previous file path if available
    $previous_submission_row = mysqli_fetch_assoc($submission_check_result);
    $previous_file_path = $previous_submission_row['s_file'] ?? null;
} else {
    $submitButtonText = "Submit";
}

// File upload configuration
$targetDir = "../../storage/submittedassignment/";
$uploadOk = 1;

// Check if a file was submitted
if (isset($_FILES["assignment_file"])) {
    // Create the target directory if it doesn't exist
    if (!file_exists($targetDir)) {
        if (!mkdir($targetDir, 0777, true)) {
            die("Failed to create target directory.");
        }
    }

    $originalFileName = $_FILES["assignment_file"]["name"];
    $fileType = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));

    // Generate a new file name using username and assignment ID
    if ($previous_file_path && file_exists($previous_file_path)) {
        unlink($previous_file_path);
    }
    $username = $_SESSION['username'];
    $newFileName = $username . "_" . $assignment_id . "." . $fileType;
    $targetFile = $targetDir . $newFileName;

    // Check if the file is a PDF
    if ($fileType != "pdf") {
        echo "Only PDF files are allowed.";
        $uploadOk = 0;
    }

    // Check file size (limit to 500MB)
    if ($_FILES["assignment_file"]["size"] > 500 * 1024 * 1024) {
        echo "File size exceeds the allowed limit.";
        $uploadOk = 0;
    }

    // Upload the file if all checks pass
    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["assignment_file"]["tmp_name"], $targetFile)) {
            // File uploaded successfully, store the file path in the database
            $file_path = $targetFile;

            // Get the selected status from the form
            $status = $_POST['status'];

            // Insert/update submission details into the s_assignment table
            $submission_date = date("Y-m-d H:i:s");

            if (mysqli_num_rows($submission_check_result) > 0) {
                // User has already submitted, update the existing submission
                $update_sql = "UPDATE s_assignment SET s_file = '$file_path', submission_date = '$submission_date', status = '$status' WHERE username = '$username' AND assignment_id = '$assignment_id'";
                if (mysqli_query($conn, $update_sql)) {
                    echo "Assignment updated successfully.";
                } else {
                    echo "Error updating assignment.";
                }
            } else {
                // User hasn't submitted before, insert a new submission
                $insert_sql = "INSERT INTO s_assignment (username, s_file, assignment_id, submission_date, status) VALUES ('$username', '$file_path', '$assignment_id', '$submission_date', '$status')";
                if (mysqli_query($conn, $insert_sql)) {
                    echo "Assignment submitted successfully.";
                } else {
                    echo "Error submitting assignment.";
                }
            }
        } else {
            echo "Error uploading file.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assignment Details</title>
    <link rel="stylesheet" type="text/css" href="s_assignment.css">
    <div class = "home">
    <a href="../student.php">Home</a>
</div>
</head>
<body>
    <div class="container">
        <h2>Assignment Details</h2>
        <h3>Assignment Name: <?php echo $assignment_name; ?></h3>
        <p>Due Date: <?php echo $due_date; ?></p>
        <form action="" method="post" enctype="multipart/form-data">
            <label for="assignment_file">Upload Assignment File (PDF only):</label>
            <input type="file" name="assignment_file" id="assignment_file" accept=".pdf" required /><br><br>
            <label for="status">Status:</label>
            <select name="status" id="status">
                <option value="Partial">Partially Done</option>
                <option value="Complete">Completely Done</option>
            </select><br><br>
            <input type="submit" name="submit" value="<?php echo $submitButtonText; ?>" />
        </form>
        <a href="../download.php?assignment_id=<?php echo $assignment_id; ?>">Download Assignment File</a>
    </div>
</body>
</html>
