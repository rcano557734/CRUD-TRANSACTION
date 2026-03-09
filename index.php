<?php
session_start();

// Initialize our temporary session "database" if it doesn't exist
if (!isset($_SESSION['users_db'])) {
    $_SESSION['users_db'] = [];
}

// --- 1. LOGOUT LOGIC ---
// Triggered when the user clicks the Logout link (uses GET)
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    unset($_SESSION['active_voter']);
    header("Location: index.php");
    exit();
}

// Initialize variables
$signup_name = $signup_email = $signup_password = $signup_confirm = "";
$login_email = $login_password = "";
$signup_errors = [];
$login_error = $signup_success = "";

// Check cookie for remembered email
$remembered_email = isset($_COOKIE['remember_voter']) ? $_COOKIE['remember_voter'] : "";

// --- 2. POST REQUESTS (Form Submissions) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // A. SIGNUP FORM LOGIC
    if (isset($_POST['form_type']) && $_POST['form_type'] == 'signup') {
        $signup_name = trim($_POST['name']);
        $signup_email = trim($_POST['email']);
        $signup_password = $_POST['password'];
        $signup_confirm = $_POST['confirm_password'];

        // Validation 1: Required Fields
        if (empty($signup_name) || empty($signup_email) || empty($signup_password) || empty($signup_confirm)) {
            $signup_errors[] = "All fields are required.";
        }

        // Validation 2: Email Format
        if (!empty($signup_email) && !filter_var($signup_email, FILTER_VALIDATE_EMAIL)) {
            $signup_errors[] = "Invalid email format.";
        }

        // Validation 3: Minimum Length
        if (!empty($signup_password) && strlen($signup_password) < 8) {
            $signup_errors[] = "Password must be at least 8 characters long.";
        }

        // Validation 4: Password Confirmation Match
        if (!empty($signup_password) && $signup_password !== $signup_confirm) {
            $signup_errors[] = "Passwords do not match.";
        }

        // If no errors, save user
        if (empty($signup_errors)) {
            $_SESSION['users_db'][$signup_email] = [
                'name' => htmlspecialchars($signup_name),
                'password' => password_hash($signup_password, PASSWORD_DEFAULT)
            ];
            $signup_success = "Registration successful! You may now log in.";
            $signup_name = $signup_email = ""; // Clear form
            
            // Redirect to the login view cleanly
            header("Location: index.php?view=login&success=1");
            exit();
        }
    }

    // B. LOGIN FORM LOGIC
    if (isset($_POST['form_type']) && $_POST['form_type'] == 'login') {
        $login_email = trim($_POST['email']);
        $login_password = $_POST['password'];

        if (isset($_SESSION['users_db'][$login_email])) {
            if (password_verify($login_password, $_SESSION['users_db'][$login_email]['password'])) {
                
                // Start Authenticated Session
                $_SESSION['active_voter'] = $_SESSION['users_db'][$login_email]['name'];
                
                // Set or destroy Cookie based on "Remember Me"
                if (isset($_POST['remember_me'])) {
                    setcookie("remember_voter", $login_email, time() + (86400 * 30), "/"); 
                } else {
                    setcookie("remember_voter", "", time() - 3600, "/"); 
                }

                header("Location: index.php"); // Redirect to Dashboard view
                exit();
            } else {
                $login_error = "Incorrect password.";
            }
        } else {
            $login_error = "No account found with that email.";
        }
    }
}

// --- 3. DETERMINE WHICH VIEW TO SHOW ---
$current_view = 'login'; // Default view
if (isset($_SESSION['active_voter'])) {
    $current_view = 'dashboard';
} elseif (isset($_GET['view']) && $_GET['view'] == 'signup') {
    $current_view = 'signup';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting System Portal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php if ($current_view == 'signup'): ?>
    <div class="container">
        <h2>Register to Vote</h2>
        
        <?php 
        if (!empty($signup_errors)) {
            echo "<div class='error-box'>";
            foreach ($signup_errors as $error) echo "<p>- $error</p>";
            echo "</div>";
        }
        ?>

        <form action="index.php?view=signup" method="POST">
            <input type="hidden" name="form_type" value="signup">
            
            <div class="form-group">
                <label>Full Name:</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($signup_name); ?>">
            </div>
            <div class="form-group">
                <label>Email Address:</label>
                <input type="text" name="email" value="<?php echo htmlspecialchars($signup_email); ?>">
            </div>
            <div class="form-group">
                <label>Password (Min 8 chars):</label>
                <input type="password" name="password">
            </div>
            <div class="form-group">
                <label>Confirm Password:</label>
                <input type="password" name="confirm_password">
            </div>
            
            <button type="submit">Create Account</button>
        </form>
        <p class="link-text">Already registered? <a href="index.php?view=login">Login here</a></p>
    </div>

<?php elseif ($current_view == 'login'): ?>
    <div class="container">
        <h2>Voter Login Portal</h2>

        <?php 
        if (isset($_GET['success']) && $_GET['success'] == '1') {
            echo "<div class='success-text'>Registration successful! Please log in.</div>";
        }
        if (!empty($login_error)) {
            echo "<div class='error-box'><p>$login_error</p></div>";
        }
        ?>

        <form action="index.php" method="POST">
            <input type="hidden" name="form_type" value="login">
            
            <div class="form-group">
                <label>Email Address:</label>
                <input type="text" name="email" value="<?php echo htmlspecialchars($remembered_email); ?>">
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password">
            </div>
            
            <div class="form-group" style="display: flex; align-items: center; gap: 5px;">
                <input type="checkbox" name="remember_me" id="remember">
                <label for="remember" style="margin: 0; font-weight: normal;">Remember my email</label>
            </div>

            <button type="submit">Secure Login</button>
        </form>
        <p class="link-text">Need an account? <a href="index.php?view=signup">Sign up here</a></p>
    </div>

<?php elseif ($current_view == 'dashboard'): ?>
    <div class="container dashboard-container">
        <h2>Welcome, <?php echo $_SESSION['active_voter']; ?>!</h2>
        <p style="color: #155724; font-weight: bold;">✓ Securely authenticated via PHP Sessions.</p>
        
        <hr>

        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px;">
            <h3>Search Candidates (GET Method Demo)</h3>
            <form action="index.php" method="GET">
                <div style="display: flex; gap: 10px;">
                    <input type="text" name="search_query" placeholder="Enter candidate name...">
                    <button type="submit" style="width: auto; margin: 0;">Search</button>
                </div>
            </form>

            <?php
            if (isset($_GET['search_query']) && !empty($_GET['search_query'])) {
                $query = htmlspecialchars($_GET['search_query']);
                echo "<p style='margin-top: 15px;'>Search results for: <strong>$query</strong></p>";
            }
            ?>
        </div>

        <hr>
        
        <div style="text-align: right;">
            <a href="index.php?action=logout" style="color: #dc3545; font-weight: bold; text-decoration: none;">🚪 Logout</a>
        </div>
    </div>

<?php endif; ?>

</body>
</html>