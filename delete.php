<?php
session_start();
require 'db.php';

// Session & Role Protection
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: dashboard.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM campaign_materials WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: dashboard.php?msg=Item+Deleted");
exit();
?>