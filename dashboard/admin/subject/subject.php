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
  <title>Subject Management</title>
  <link rel="stylesheet" type="text/css" href="../main.css">
</head>
<body>
  <?php
  $conn = mysqli_connect('localhost', 'root', '', 'project');
  // Check connection
  if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
  }

  // Function to sanitize user input
  function sanitizeInput($data)
  {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
  }

// Inserting a new subject
if (isset($_POST['insert'])) {
  $subject_id = sanitizeInput($_POST['subject_id']);
  $subject_name = sanitizeInput($_POST['subject_name']);
  $semester = sanitizeInput($_POST['semester']);

  // Check if the subject already exists
  $check_query = "SELECT * FROM subject WHERE subject_id = '$subject_id'";
  $check_result = mysqli_query($conn, $check_query);
  if (mysqli_num_rows($check_result) > 0) {
    echo "Subject already exists.";
  } else {
    // Insert the subject into the subject table
    $insert_query = "INSERT INTO subject (subject_id, subject_name, semester) VALUES ('$subject_id', '$subject_name', $semester)";
    if (mysqli_query($conn, $insert_query)) {
      echo "Subject inserted successfully.";
    } else {
      echo "Error inserting subject: " . mysqli_error($conn);
    }
  }
}


// Deleting a subject
if (isset($_POST['delete'])) {
  $delete_subject = sanitizeInput($_POST['delete-subject']);
  
  // Check if the subject exists
  $check_query = "SELECT * FROM subject WHERE subject_id = '$delete_subject'";
  $check_result = mysqli_query($conn, $check_query);

  if (mysqli_num_rows($check_result) > 0) {
      // Retrieve assignment IDs associated with the subject
      $assignmentIdsQuery = "SELECT assignment_id FROM assignment WHERE subject_id = '$delete_subject'";
      $assignmentIdsResult = mysqli_query($conn, $assignmentIdsQuery);
      
      while ($assignmentIdRow = mysqli_fetch_assoc($assignmentIdsResult)) {
          $assignmentId = $assignmentIdRow['assignment_id'];
          
          // Retrieve assignment file paths from s_assignment table
          $sAssignmentFilePathsQuery = "SELECT s_file FROM s_assignment WHERE assignment_id = '$assignmentId'";
          $sAssignmentResult = mysqli_query($conn, $sAssignmentFilePathsQuery);


          // Delete submitted assignment files from s_file
          while ($sAssignmentRow = mysqli_fetch_assoc($sAssignmentResult)) {
              $sAssignmentFilePath = $sAssignmentRow['s_file'];
              if (!empty($sAssignmentFilePath) && file_exists($sAssignmentFilePath)) {
                  unlink($sAssignmentFilePath);
              }
          }

          // Delete records from s_assignment
          $deleteSAssignmentQuery = "DELETE FROM s_assignment WHERE assignment_id = '$assignmentId'";
          if (!mysqli_query($conn, $deleteSAssignmentQuery)) {
              echo "Error deleting s_assignment records: " . mysqli_error($conn);
              exit();
          }
          // Retrieve assignment PDF file paths
          $getFilePathQuery = "SELECT pdf_filepath FROM assignment WHERE assignment_id = '$assignmentId'";
          $filePathResult = mysqli_query($conn, $getFilePathQuery);

          if ($filePathResult) {
              $filePathRow = mysqli_fetch_assoc($filePathResult);
              $filePath = $filePathRow['pdf_filepath'];

              if (!empty($filePath) && file_exists($filePath)) {
                  // Delete the file
                  if (unlink($filePath)) {
                      // File deleted successfully, now delete the record from the assignment table
                      $deleteAssignmentQuery = "DELETE FROM assignment WHERE assignment_id = '$assignmentId'";
                      if (!mysqli_query($conn, $deleteAssignmentQuery)) {
                          echo "Error deleting assignment record: " . mysqli_error($conn);
                          exit();
                      }
                  } else {
                      echo "Error deleting file.";
                  }
              } else {
                  echo "File not found.";
              }
          } else {
              echo "Error retrieving file path: " . mysqli_error($conn);
          }
      }

      // Delete the subject RECORD from the subject table
      $deleteSubjectQuery = "DELETE FROM subject WHERE subject_id = '$delete_subject'";
      if (mysqli_query($conn, $deleteSubjectQuery)) {
          echo "Subject Deleted Successfully.";
      } else {
          echo "Error deleting subject: " . mysqli_error($conn);
      }
  } else {
      echo "Error: Subject doesn't Exist";
  }
}






  // Retrieve the list of subjects from the subject table
  $query = "SELECT * FROM subject ORDER BY semester ASC";
  $result = mysqli_query($conn, $query);
  $subjects = array();
  if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
      $subjects[] = $row;
    }
  }

  // Close the database connection
  mysqli_close($conn);
  ?>
  <div class = container>
    <div class = "section">
  <h2>Add Subject<img src="../../images/subject.png" class="icon"></h2>
  <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <label for="subject_id">Subject ID:</label>
    <input type="text" id="subject_id" name="subject_id" maxlength="30" required><br>

    <label for="subject_name">Subject Name:</label>
    <input type="text" id="subject_name" name="subject_name" maxlength="30" required><br>

    <label for="semester">Semester:</label>
    <select id="semester" name="semester" required>
      <?php for ($i = 1; $i <= 8; $i++) { ?>
        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
      <?php } ?>
    </select><br><br>

    <input type="submit" name="insert" value="Insert Subject">
  </form>
      </div>
      <div class = "section">
  <h2>Delete Subject<img src="../../images/delete.png" class="icon"></h2>
  <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <label for="delete-subject">Subject ID:</label>
    <input type="text" id="delete-subject" name="delete-subject" maxlength="30" placeholder="Search subject ID" required list="subject-id-list" ><br>

    <datalist id="subject-id-list">
      <?php foreach ($subjects as $subject) { ?>
        <option value="<?php echo $subject['subject_id']; ?>">
      <?php } ?>
    </datalist>

    <input type="submit" name="delete" class="delete-btn" value="Delete Subject" onclick="return confirm('Are you sure you want to delete this subject ?');">
  </form>
      </div>
      <a href="../admin.php">Home</a>
      </div>

  <h2>Existing Subjects</h2>
  <table>
    <tr>
      <th>Subject ID</th>
      <th>Subject Name</th>
      <th>Semester</th>
    </tr>
    <?php foreach ($subjects as $subject) { ?>
      <tr>
        <td><?php echo $subject['subject_id']; ?></td>
        <td><?php echo $subject['subject_name']; ?></td>
        <td><?php echo $subject['semester']; ?></td>
      </tr>
    <?php } ?>
  </table>
</body>
</html>
