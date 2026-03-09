<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$isAdmin = ($_SESSION['role'] === 'Admin');

// Initialize variables for sticky forms and field-specific errors
$item_name = $price = $quantity = "";
$item_name_err = $price_err = $quantity_err = "";

// --- CREATE OPERATION ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_item']) && $isAdmin) {
    
    // 1. Validate Item Name
    if (empty(trim($_POST['item_name']))) {
        $item_name_err = "Item Name is required.";
    } else {
        $item_name = trim($_POST['item_name']);
    }

    // 2. Validate Price (Must not be negative)
    if ($_POST['price'] === "") {
        $price_err = "Price is required.";
    } elseif ($_POST['price'] < 0) {
        $price_err = "Negative values are not accepted.";
    } else {
        $price = $_POST['price'];
    }

    // 3. Validate Quantity (Must not be negative)
    if ($_POST['quantity'] === "") {
        $quantity_err = "Quantity is required.";
    } elseif ($_POST['quantity'] < 0) {
        $quantity_err = "Negative values are not accepted.";
    } else {
        $quantity = $_POST['quantity'];
    }

    // If NO errors, process the insertion
    if (empty($item_name_err) && empty($price_err) && empty($quantity_err)) {
        $stmt = $pdo->prepare("INSERT INTO campaign_materials (item_name, price, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$item_name, $price, $quantity]);
        
        // PRG Pattern Redirect
        header("Location: dashboard.php?msg=Item+Successfully+Added");
        exit();
    }
}

// --- PAGINATION & SEARCH (READ OPERATION) ---
$limit = 5; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$countQuery = "SELECT COUNT(*) FROM campaign_materials WHERE item_name LIKE ?";
$stmtCount = $pdo->prepare($countQuery);
$stmtCount->execute(["%$search%"]);
$total_rows = $stmtCount->fetchColumn();
$total_pages = ceil($total_rows / $limit);

$query = "SELECT * FROM campaign_materials WHERE item_name LIKE ? ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute(["%$search%"]);
$materials = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Campaign Materials Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .error-text { color: #dc3545; font-size: 0.85em; margin-top: 3px; display: block; }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <span class="navbar-brand">Voting System - Logistics</span>
        <div class="text-white d-flex align-items-center gap-3">
            <span>Welcome, <?= htmlspecialchars($_SESSION['name']) ?> (<?= $_SESSION['role'] ?>)</span>
            <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success fw-bold"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <div class="row">
        <?php if($isAdmin): ?>
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white fw-bold">Add New Material</div>
                <div class="card-body">
                    <form method="POST" action="dashboard.php">
                        <input type="hidden" name="create_item" value="1">
                        
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
                        
                        <button type="submit" class="btn btn-primary w-100 fw-bold">Save Item</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="<?= $isAdmin ? 'col-md-8' : 'col-md-12' ?>">
            <div class="card shadow">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Inventory List</span>
                    <form method="GET" class="d-flex">
                        <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Search items..." value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" class="btn btn-light btn-sm fw-bold">Search</button>
                    </form>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover table-striped m-0">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Item Name</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <?php if($isAdmin): ?><th>Actions</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($materials as $row): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['item_name']) ?></td>
                                <td>₱<?= number_format($row['price'], 2) ?></td>
                                <td><?= $row['quantity'] ?></td>
                                <?php if($isAdmin): ?>
                                <td>
                                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm fw-bold">Edit</a>
                                    <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm fw-bold" onclick="return confirm('Delete this record permanently?');">Delete</a>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($materials)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No records found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if($total_pages > 1): ?>
                <div class="card-footer bg-white pt-3">
                    <nav>
                        <ul class="pagination pagination-sm justify-content-center m-0">
                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>