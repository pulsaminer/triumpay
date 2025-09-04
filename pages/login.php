<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Initialize variables
$username = '';
$error = '';

// Generate anti-bot calculation (only if not already set)
if (!isset($_SESSION['anti_bot_num1']) || !isset($_SESSION['anti_bot_num2'])) {
    $_SESSION['anti_bot_num1'] = rand(1, 10);
    $_SESSION['anti_bot_num2'] = rand(1, 10);
}

$num1 = $_SESSION['anti_bot_num1'];
$num2 = $_SESSION['anti_bot_num2'];
$expected_result = $num1 + $num2;

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF Protection
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = "Invalid CSRF token";
    } else {
        // Sanitize input
        $username = sanitize_input($_POST['username']);
        $password = $_POST['password'];
        $anti_bot = isset($_POST['anti_bot']) ? $_POST['anti_bot'] : '';
        
        // Anti-bot verification
        if (empty($anti_bot) || $anti_bot != $expected_result) {
            $error = "Anti-bot verification failed";
        } else {
            // Check if user exists
            $user = get_user_by_username($username);
            
            if ($user && password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                // Clear anti-bot session variables
                unset($_SESSION['anti_bot_num1']);
                unset($_SESSION['anti_bot_num2']);
                
                // Redirect to dashboard
                redirect('?page=dashboard');
            } else {
                $error = "Invalid username or password";
            }
        }
    }
    
    // Regenerate anti-bot calculation after failed attempt
    $_SESSION['anti_bot_num1'] = rand(1, 10);
    $_SESSION['anti_bot_num2'] = rand(1, 10);
    $num1 = $_SESSION['anti_bot_num1'];
    $num2 = $_SESSION['anti_bot_num2'];
}
?>

<div class="auth-wrapper">
    <div class="auth-container">
    <div class="auth-header">
        <img src="/assets/images/logo.png" alt="Logo" class="auth-logo">
        <h1>Welcome Back</h1>
        <p>Sign in to continue your journey</p>
    </div>
    
    <div class="auth-body">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <span class="alert-icon"><i class="fas fa-exclamation-circle"></i></span>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            
            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" placeholder="Enter your username" required>
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Anti-Bot Verification</label>
                <input type="text" class="form-control mb-2" value="<?php echo $num1; ?> + <?php echo $num2; ?> = ?" readonly>
                <input type="text" class="form-control" name="anti_bot" placeholder="Enter answer" required>
                <div class="form-text">Please solve this simple math problem to verify you're human</div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-lg btn-block">
                <i class="fas fa-sign-in-alt me-2"></i>Sign In
            </button>
        </form>
        
        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
        </div>
    </div>
    </div>
</div>