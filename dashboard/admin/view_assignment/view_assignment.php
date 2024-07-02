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

// Retrieve available semesters
$semester_sql = "SELECT DISTINCT semester FROM subject";
$semester_result = mysqli_query($conn, $semester_sql);

// Check if a semester is selected
if (isset($_POST['semester'])) {
    $selected_semester = $_POST['semester'];

    // Retrieve available subjects for the selected semester
    $subject_sql = "SELECT subject_id, subject_name FROM subject WHERE semester = '$selected_semester'";
    $subject_result = mysqli_query($conn, $subject_sql);

    // Check if a subject is selected
    if (isset($_POST['subject'])) {
        $selected_subject = $_POST['subject'];

        // Retrieve available assignments for the selected subject
        $assignment_sql = "SELECT assignment_id, assignment_name FROM assignment WHERE subject_id = '$selected_subject'";
        $assignment_result = mysqli_query($conn, $assignment_sql);

        // Check if an assignment is selected
        if (isset($_POST['assignment'])) {
            $selected_assignment = $_POST['assignment'];

            // Retrieve submissions for the selected assignment
            $submission_sql = "SELECT DISTINCT login.username, s_assignment.submission_date, s_assignment.status, s_assignment.s_file FROM login LEFT JOIN s_assignment ON login.username = s_assignment.username AND s_assignment.assignment_ID = '$selected_assignment' WHERE login.semester = '$selected_semester';";
            $submission_result = mysqli_query($conn, $submission_sql);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Assignments</title>
    <link rel="stylesheet" href="view_assignment.css">
</head>
<body>
    <h2>View Assignments</h2>
    <div class = "home">
    <a href="../admin.php">Home</a>
</div>

    <form action="" method="post">
        <label for="semester">Select Semester:</label>
        <select name="semester" id="semester" onchange="this.form.submit()">
            <option value="">-- Select Semester --</option>
            <?php
            while ($semester_row = mysqli_fetch_assoc($semester_result)) {
                $semester = $semester_row['semester'];
                echo "<option value='$semester' " . ($selected_semester == $semester ? "selected" : "") . ">$semester</option>";
            }
            ?>
        </select>
        

        <?php if (isset($selected_semester)) : ?>
            <label for="subject">Select Subject:</label>
            <select name="subject" id="subject" onchange="this.form.submit()">
                <option value="">-- Select Subject --</option>
                <?php
                while ($subject_row = mysqli_fetch_assoc($subject_result)) {
                    $subject_id = $subject_row['subject_id'];
                    $subject_name = $subject_row['subject_name'];
                    echo "<option value='$subject_id' " . ($selected_subject == $subject_id ? "selected" : "") . ">$subject_name ($subject_id)</option>";
                }
                ?>
            </select>
        <?php endif; ?>

        <?php if (isset($selected_subject)) : ?>
            <label for="assignment">Select Assignment:</label>
            <select name="assignment" id="assignment" onchange="this.form.submit()">
                <option value="">-- Select Assignment --</option>
                <?php
                while ($assignment_row = mysqli_fetch_assoc($assignment_result)) {
                    $assignment_id = $assignment_row['assignment_id'];
                    $assignment_name = $assignment_row['assignment_name'];
                    echo "<option value='$assignment_id'>$assignment_name ($assignment_id)</option>";
                }
                ?>
            </select>
        <?php endif; ?>
    </form>

    <?php if (isset($selected_assignment)) : ?>
        <h3>Submissions for Assignment <?php echo $selected_assignment; ?></h3>

        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Submission Date</th>
                    <th>Status</th>
                    <th>Download File</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($submission_row = mysqli_fetch_assoc($submission_result)) : ?>
                    <tr>
                        <td><?php echo $submission_row['username']; ?></td>
                        <td><?php echo $submission_row['submission_date']; ?></td>
                        <td><?php echo $submission_row['status']; ?></td>
                        <td>
                            <?php if ($submission_row['s_file']) : ?>
                                <?php $file_path = $submission_row['s_file']; ?>
                                <a href="<?php echo $file_path; ?>" download>Download</a>
                            <?php else : ?>
                                Assignment not submitted
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            // Handle subject selection change
            $('#subject').change(function() {
                var subjectId = $(this).val();

                // Make AJAX request to get assignments for the selected subject
                $.ajax({
                    url: 'get_assignments.php',
                    method: 'POST',
                    data: { subjectId: subjectId },
                    success: function(response) {
                        // Update the assignment select options with the retrieved data
                        $('#assignment').html(response);
                    }
                });
            });
        });
    </script>
</body>
</html>
