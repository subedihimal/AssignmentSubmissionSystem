<?php
session_start();

// Check if assignment ID is set in the query parameters
if (!isset($_GET['assignment_id'])) {
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

// Get assignment ID from the query parameters
$assignment_id = $_GET['assignment_id'];

// Retrieve assignment details from the database
$sql = "SELECT pdf_filepath FROM assignment WHERE assignment_id = '$assignment_id'";
$result = mysqli_query($conn, $sql);

// Check if assignment exists
if (mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_assoc($result);
    $pdf_filepath = $row['pdf_filepath'];

    // Check if the file exists
    if (file_exists($pdf_filepath)) {
        // Set appropriate headers for file download
        header("Content-Type: application/pdf");
        header("Content-Disposition: attachment; filename=" . basename($pdf_filepath));
        header("Content-Length: " . filesize($pdf_filepath));
        
        // Read the file and output it to the browser
        readfile($pdf_filepath);
        exit;
    } else {
        // File not found, display an error message
        echo "File not found.";
        exit;
    }
} else {
    // Assignment not found, redirect back to student.php
    mysqli_close($conn);
    header("Location: ../student.php");
    exit;
}

mysqli_close($conn);
?>
