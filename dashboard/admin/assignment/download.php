<?php
if (isset($_GET['file'])) {
    $filepath = $_GET['file'];

    if (file_exists($filepath)) {
        header("Content-Type: application/pdf");
        header("Content-Disposition: attachment; filename=" . basename($filepath));
        readfile($filepath);
        exit;
    } else {
        echo "File not found.";
    }
}
?>
