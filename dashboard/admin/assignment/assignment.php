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
    <title>Assignment System</title>
</head>
<link rel="stylesheet" type="text/css" href="../main.css">
<body>
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

    // Function to upload PDF file to the specified path
    function uploadFile($file, $targetDirectory, $assignmentId)
    {
        $fileName = $assignmentId . "_" . basename($file["name"]);
        $targetFilePath = $targetDirectory . $fileName;

        if (move_uploaded_file($file["tmp_name"], $targetFilePath)) {
            return $targetFilePath;
        } else {
            return null;
        }
    }

    // Insert new assignment into the database
    if (isset($_POST['submit'])) {
        $subjectId = $_POST['subject_id'];
        $assignmentOption = $_POST['assignment_option'];
        $assignmentId = $subjectId . "-" . $assignmentOption;
        $assignmentName = $_POST['assignment_name'];
        $dueDate = $_POST['due_date'];

        // Upload the PDF file
        $targetDirectory = $_SERVER['DOCUMENT_ROOT'] . "/project_as/dashboard/storage/givenassignment/";
        $uploadedFilePath = uploadFile($_FILES['pdf_file'], $targetDirectory, $assignmentId);

        // Check if the subject ID exists
        $checkSubjectQuery = "SELECT COUNT(*) as count FROM subject WHERE subject_id = '$subjectId'";
        $checkSubjectResult = mysqli_query($conn, $checkSubjectQuery);
        $row = mysqli_fetch_assoc($checkSubjectResult);
        $subjectExists = $row['count'] > 0;

        if (!$subjectExists) {
            echo "The subject ID doesn't exist.";
        } else {
            // Check if the assignment already exists
            $sql = "SELECT COUNT(*) as count FROM assignment WHERE assignment_id = '$assignmentId'";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result);
            $assignmentExists = $row['count'] > 0;

            if ($assignmentExists) {
                echo "Assignment already exists.";
            } else {
                // Prepare and execute the SQL query to insert assignment data
                $sql = "INSERT INTO assignment (assignment_id, subject_id, assignment_name, due_date, pdf_filepath)
                        VALUES ('$assignmentId', '$subjectId', '$assignmentName', '$dueDate', '$uploadedFilePath')";
                if (mysqli_query($conn, $sql)) {
                    echo "Assignment inserted successfully.";
                } else {
                    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
                }
            }
        }
    }

    // Delete assignment from the database
if (isset($_POST['delete'])) {
    $assignmentId = $_POST['delete_assignment_id'];

    // Check if the assignment exists
    $checkAssignmentQuery = "SELECT COUNT(*) as count FROM assignment WHERE assignment_id = '$assignmentId'";
    $checkAssignmentResult = mysqli_query($conn, $checkAssignmentQuery);
    $row = mysqli_fetch_assoc($checkAssignmentResult);
    $assignmentExists = $row['count'] > 0;

    if (!$assignmentExists) {
        echo "The assignment doesn't exist.";
    } else {

    // Retrieve the PDF file path for deletion from the assignment table
    $sql = "SELECT pdf_filepath FROM assignment WHERE assignment_id = '$assignmentId'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $pdfFilePath = $row['pdf_filepath'];

    // Retrieve submitted assignment files for deletion
    $submittedAssignmentFiles = glob($_SERVER['DOCUMENT_ROOT'] . "/project_as/dashboard/storage/submittedassignment/{$assignmentId}_*.pdf");

    // Retrieve and delete associated records from s_assignment table
    $selectSAssignmentQuery = "SELECT s_file FROM s_assignment WHERE assignment_id = '$assignmentId'";
    $result = mysqli_query($conn, $selectSAssignmentQuery);

    // Delete associated records from s_assignment table
    $deleteSAssignmentQuery = "DELETE FROM s_assignment WHERE assignment_id = '$assignmentId'";
    mysqli_query($conn, $deleteSAssignmentQuery);

    // Delete assignment data from the database
    $sql = "DELETE FROM assignment WHERE assignment_id = '$assignmentId'";
    if (mysqli_query($conn, $sql)) {
        // Delete the PDF file from the assignment table
        if (!empty($pdfFilePath) && file_exists($pdfFilePath)) {
            unlink($pdfFilePath);
        }

        // Delete submitted assignment files
        foreach ($submittedAssignmentFiles as $submittedFile) {
            if (file_exists($submittedFile)) {
                unlink($submittedFile);
            }
        }

        // Delete associated s_assignment files
        while ($sRow = mysqli_fetch_assoc($result)) {
            $sPdfFilePath = $sRow['s_file'];
            if (!empty($sPdfFilePath) && file_exists($sPdfFilePath)) {
                unlink($sPdfFilePath);
            }
        }
        echo "Assignment and associated submitted assignments deleted successfully.";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}
}

    // Reset assignments
if (isset($_POST['reset-assignments'])) {
    // Retrieve all s_file paths from s_assignment table
    $sFilePathsQuery = "SELECT s_file FROM s_assignment";
    $sResult = mysqli_query($conn, $sFilePathsQuery);

    // Delete all records from s_assignment table
    $deleteSAssignmentQuery = "DELETE FROM s_assignment";
    mysqli_query($conn, $deleteSAssignmentQuery);

    // Delete submitted assignment files
    while ($sRow = mysqli_fetch_assoc($sResult)) {
        $sPdfFilePath = $sRow['s_file'];
        if (!empty($sPdfFilePath) && file_exists($sPdfFilePath)) {
            unlink($sPdfFilePath);
        }
    }

    // Retrieve the PDF file paths for deletion from the assignment table
    $pdfFilePathsQuery = "SELECT pdf_filepath FROM assignment";
    $result = mysqli_query($conn, $pdfFilePathsQuery);

    // Delete all assignments from the assignment table
    $deleteAssignmentsQuery = "DELETE FROM assignment";
    if (mysqli_query($conn, $deleteAssignmentsQuery)) {
        // Delete the PDF files from the assignment table
        while ($row = mysqli_fetch_assoc($result)) {
            $pdfFilePath = $row['pdf_filepath'];
            if (!empty($pdfFilePath) && file_exists($pdfFilePath)) {
                unlink($pdfFilePath);
            }
        }

        echo "All assignments and associated submitted assignments reset successfully.";
    } else {
        echo "Error: " . $deleteAssignmentsQuery . "<br>" . mysqli_error($conn);
    }
}

    ?>

    <!-- Assignment Form -->
    <div class = "container">
        <div class = "section">
    <h2>Assignment Management<img src="../../images/assignment.png" class="icon"></h2>
    <form method="post" enctype="multipart/form-data">
        <label for="subject_id">Subject ID:</label>
        <input type="text" name="subject_id" required list="subject-id-list"><br>
        <datalist id="subject-id-list">
            <?php
            // Retrieve the list of subject IDs from the subject table
            $sql = "SELECT subject_id FROM subject";
            $result = mysqli_query($conn, $sql);
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<option value='" . $row['subject_id'] . "'>";
            }
            ?>
        </datalist>

        <label for="assignment_option">Assignment No:</label>
        <select name="assignment_option" required>
            <?php
            // Generate assignment options from 1 to 30
            for ($i = 1; $i <= 30; $i++) {
                echo "<option value='" . $i . "'>" . $i . "</option>";
            }
            ?>
        </select><br>

        <label for="assignment_name">Assignment Name:</label>
        <input type="text" name="assignment_name" required><br>

        <label for="due_date">Due Date:</label>
        <input type="date" name="due_date" required min="<?php echo date('Y-m-d'); ?>"><br>

        <label for="pdf_file">PDF File:</label>
        <input type="file" name="pdf_file" required><br>
        <br>
        <input type="submit" name="submit" value="Insert Assignment">
    </form>
        </div>
    <!-- Delete Assignment -->
    <div class = "section">
    <h2>Delete Assignment<img src="../../images/delete.png" class="icon"></h2>
    <form method="post">
        <label for="delete_assignment_id">Assignment ID:</label>
        <input type="text" name="delete_assignment_id" required list="assignment-id-list"><br>
        <datalist id="assignment-id-list">
            <?php
            // Retrieve the list of assignment IDs from the assignment table
            $sql = "SELECT assignment_id FROM assignment";
            $result = mysqli_query($conn, $sql);
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<option value='" . $row['assignment_id'] . "'>";
            }
            ?>
        </datalist>

        <input type="submit" class = "delete-btn" name="delete" value="Delete Assignment"onclick="return confirm('Are you sure you want to delete this assignment ?');">
    </form>
        </div>
        <a href="../admin.php">Home</a>
        </div>

    <!-- Display Existing Assignments -->
    <h2>Existing Assignments</h2>
    <div class="section">
                <form method="post">
                    <input type="submit" name="reset-assignments" value="Reset All Assignments">
    <?php
    // Retrieve existing assignments with subject information from the database
    $sql = "SELECT a.assignment_id, a.subject_id, a.assignment_name, a.due_date, a.pdf_filepath, s.subject_name, s.semester 
            FROM assignment a
            INNER JOIN subject s ON a.subject_id = s.subject_id";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        echo "<table>";
        echo "<tr><th>Assignment ID</th><th>Subject ID</th><th>Subject Name</th><th>Semester</th><th>Assignment Name</th><th>Due Date</th><th>PDF File</th></tr>";

        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['assignment_id'] . "</td>";
            echo "<td>" . $row['subject_id'] . "</td>";
            echo "<td>" . $row['subject_name'] . "</td>";
            echo "<td>" . $row['semester'] . "</td>";
            echo "<td>" . $row['assignment_name'] . "</td>";
            echo "<td>" . $row['due_date'] . "</td>";
            echo "<td><a href='download.php?file=" . urlencode($row['pdf_filepath']) . "'>Download</a></td>";
            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "No assignments found.";
    }

    // Close the database connection
    mysqli_close($conn);
    ?>
</body>
</html>