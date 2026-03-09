<?php
session_start();
require 'db.php';

$errors = [];
$success = "";
$name = $email = $role = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // VALIDATION 1: Required Fields
    if (empty($name) || empty($email) || empty($password)) {
        $errors[] = "All fields are required.";
    }

    // VALIDATION 2: Email Format
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // VALIDATION 3: Password Length
    if (!empty($password) && strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$name, $email, $hashed_password, $role])) {
            $success = "Registration successful! You can now login.";
            $name = $email = ""; // Clear form on success
        } else {
            $errors[] = "Registration failed. This email might already be registered.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register - Campaign Logistics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 500px;">
    <div class="card shadow">
        <div class="card-body">
            <h3 class="text-center">Register Account</h3>
            
            <?php 
            if (!empty($errors)) {
                echo "<div class='alert alert-danger p-2 mb-3'>";
                foreach ($errors as $error) echo "<div style='font-size: 0.9em;'>• $error</div>";
                echo "</div>";
            }
            if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; 
            ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name) ?>">
                </div>
                <div class="mb-3">
                    <label>Email</label>
                    <input type="text" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>">
                </div>
                <div class="mb-3">
                    <label>Password (Min 8 chars)</label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Role</label>
                    <select name="role" class="form-select">
                        <option value="User" <?= ($role == 'User') ? 'selected' : '' ?>>Voter/User (View Only)</option>
                        <option value="Admin" <?= ($role == 'Admin') ? 'selected' : '' ?>>Election Admin (Full Access)</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>
            <div class="mt-3 text-center"><a href="login.php" class="text-decoration-none">Already have an account? Login</a></div>
        </div>
    </div>
</div>
</body>
</html>