<?php
// Check if already logged in as admin
require_once __DIR__ . '/../includes/config.php';
if (isset($_SESSION['admin_id']) && $_SESSION['admin_id']) {
    header('Location: admin_panel.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Certificate Generator</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        /* admin specific styles are retained inline for now */
    </style>
</head>
<body>
    <!-- admin login content same as before but simplified; core asset paths are relative to public/ -->
    <?php include __DIR__ . '/../original_admin_login.php'; ?>
</body>
</html>
