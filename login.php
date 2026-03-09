<?php
session_start();
require 'db.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Initialize variables for sticky forms and field-specific errors
$email = $password = "";
$email_err = $password_err = $login_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Validate Email
    if (empty(trim($_POST['email']))) {
        $email_err = "Please enter your email address.";
    } else {
        $email = trim($_POST['email']);
    }

    // 2. Validate Password
    if (empty($_POST['password'])) {
        $password_err = "Please enter your password.";
    } else {
        $password = $_POST['password'];
    }

    // 3. Process Login if no field errors exist
    if (empty($email_err) && empty($password_err)) {
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Check if user exists AND password matches the hash
        if ($user && password_verify($password, $user['password'])) {
            
            // Set Sessions
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            
            // Set Cookie for Last Login Info (Expires in 30 days)
            setcookie("last_login", date("Y-m-d h:i A"), time() + (86400 * 30), "/");

            header("Location: dashboard.php");
            exit();
            
        } else {
            // Generic error for security (don't reveal if email or password was wrong)
            $login_err = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login - Campaign Logistics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .error-text { color: #dc3545; font-size: 0.85em; margin-top: 3px; display: block; }
    </style>
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 400px;">
    <div class="card shadow">
        <div class="card-body">
            <h3 class="text-center fw-bold">System Login</h3>
            
            <?php if(isset($_COOKIE['last_login'])): ?>
                <div class="alert alert-info text-center py-2" style="font-size: 0.85em;">
                    <strong>Last login:</strong> <?= htmlspecialchars($_COOKIE['last_login']) ?>
                </div>
            <?php endif; ?>

            <?php if(!empty($login_err)): ?>
                <div class="alert alert-danger py-2 text-center fw-bold" style="font-size: 0.9em;">
                    <?= $login_err ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="login.php">
                
                <div class="mb-3">
                    <label class="fw-bold">Email</label>
                    <input type="text" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>">
                    <span class="error-text"><?= $email_err ?></span>
                </div>
                
                <div class="mb-3">
                    <label class="fw-bold">Password</label>
                    <input type="password" name="password" class="form-control">
                    <span class="error-text"><?= $password_err ?></span>
                </div>
                
                <button type="submit" class="btn btn-success w-100 fw-bold">Secure Login</button>
            </form>
            
            <div class="mt-3 text-center">
                <a href="register.php" class="text-decoration-none">Create an account</a>
            </div>
            
        </div>
    </div>
</div>
</body>
</html>