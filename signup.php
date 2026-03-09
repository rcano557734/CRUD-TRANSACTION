
<?php
session_start();

$name = $email = $password = $confirm_password = "";
$errors = [];

if (!isset($_SESSION['users_db'])) {
    $_SESSION['users_db'] = [];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $errors[] = "All fields are required.";
    }
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (!empty($password) && strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }
    if (!empty($password) && $password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        $_SESSION['users_db'][$email] = [
            'name' => htmlspecialchars($name),
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ];
        header("Location: login.php?signup=success");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Voter Signup</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Register to Vote</h2>
        
        <?php 
        if (!empty($errors)) {
            echo "<div class='error-box'>";
            foreach ($errors as $error) echo "<div>• $error</div>";
            echo "</div>";
        }
        ?>

        <form action="signup.php" method="POST">
            <div class="form-group">
                <label>Full Name:</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>">
            </div>
            <div class="form-group">
                <label>Email Address:</label>
                <input type="text" name="email" value="<?php echo htmlspecialchars($email); ?>">
            </div>
            <div class="form-group">
                <label>Password (Min 8 chars):</label>
                <input type="password" name="password">
            </div>
            <div class="form-group">
                <label>Confirm Password:</label>
                <input type="password" name="confirm_password">
            </div>
            
            <button type="submit">Sign Up</button>
        </form>
        <div class="text-center">
            Already registered? <a href="login.php">Login here</a>
        </div>
    </div>
</body>
</html>
