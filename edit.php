<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: dashboard.php");
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: dashboard.php");
    exit();
}

// Fetch existing data
$stmt = $pdo->prepare("SELECT * FROM campaign_materials WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    header("Location: dashboard.php");
    exit();
}

// Initialize error variables
$item_name_err = $price_err = $quantity_err = "";

// Set initial values from the database
$item_name = $item['item_name'];
$price = $item['price'];
$quantity = $item['quantity'];

// --- UPDATE OPERATION ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate Item Name
    if (empty(trim($_POST['item_name']))) {
        $item_name_err = "Item Name is required.";
    } else {
        $item_name = trim($_POST['item_name']);
    }

    // Validate Price
    if ($_POST['price'] === "") {
        $price_err = "Price is required.";
    } elseif ($_POST['price'] < 0) {
        $price_err = "Negative values are not accepted.";
    } else {
        $price = $_POST['price'];
    }

    // Validate Quantity
    if ($_POST['quantity'] === "") {
        $quantity_err = "Quantity is required.";
    } elseif ($_POST['quantity'] < 0) {
        $quantity_err = "Negative values are not accepted.";
    } else {
        $quantity = $_POST['quantity'];
    }

    // If NO errors, process the update
    if (empty($item_name_err) && empty($price_err) && empty($quantity_err)) {
        $updateStmt = $pdo->prepare("UPDATE campaign_materials SET item_name = ?, price = ?, quantity = ? WHERE id = ?");
        $updateStmt->execute([$item_name, $price, $quantity, $id]);
        
        header("Location: dashboard.php?msg=Item+Successfully+Updated");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Material</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .error-text { color: #dc3545; font-size: 0.85em; margin-top: 3px; display: block; }
    </style>
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 500px;">
    <div class="card shadow">
        <div class="card-header bg-warning fw-bold">Update Campaign Material</div>
        <div class="card-body">
            
            <form method="POST">
                <div class="mb-3">
                    <label class="fw-bold">Item Name</label>
                    <input type="text" name="item_name" class="form-control" value="<?= htmlspecialchars($item_name) ?>">
                    <span class="error-text"><?= $item_name_err ?></span>
                </div>
                
                <div class="mb-3">
                    <label class="fw-bold">Price (₱)</label>
                    <input type="number" step="0.01" name="price" class="form-control" value="<?= htmlspecialchars($price) ?>">
                    <span class="error-text"><?= $price_err ?></span>
                </div>
                
                <div class="mb-3">
                    <label class="fw-bold">Quantity</label>
                    <input type="number" name="quantity" class="form-control" value="<?= htmlspecialchars($quantity) ?>">
                    <span class="error-text"><?= $quantity_err ?></span>
                </div>
                
                <div class="d-flex gap-2">
                    <a href="dashboard.php" class="btn btn-secondary w-50 fw-bold">Cancel</a>
                    <button type="submit" class="btn btn-warning w-50 fw-bold">Update Item</button>
                </div>
            </form>
            
        </div>
    </div>
</div>
</body>
</html>