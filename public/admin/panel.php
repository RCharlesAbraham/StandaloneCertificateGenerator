<?php
require_once __DIR__ . '/../includes/config.php';

// Check if admin is authenticated
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$admin_username = $_SESSION['admin_username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Certificate Generator</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        /* admin styles retained for now */
    </style>
</head>
<body>
    <?php include __DIR__ . '/../original_admin_panel.php'; ?>
</body>
</html>
