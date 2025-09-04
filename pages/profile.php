<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('?page=login');
}

// Get user data
$user = get_user_by_id($_SESSION['user_id']);

// Initialize variables
$fullname = $user['fullname'];
$email = $user['email'];
$phone = $user['phone'];
$wallet_address = $user['wallet_address'];

$error = '';
$success = '';

// Process profile update form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    // CSRF Protection
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = "Invalid CSRF token";
    } else {
        // Sanitize input
        $fullname = sanitize_input($_POST['fullname']);
        $email = sanitize_input($_POST['email']);
        $phone = sanitize_input($_POST['phone']);
        $wallet_address = sanitize_input($_POST['wallet_address']);
        
        // Validate input
        if (empty($fullname)) {
            $error = "Full name is required";
        } elseif (empty($email)) {
            $error = "Email is required";
        } elseif (!validate_email($email)) {
            $error = "Invalid email format";
        } elseif (empty($phone)) {
            $error = "Phone number is required";
        } else {
            // Check if email already exists (excluding current user)
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $_SESSION['user_id']]);
            if ($stmt->fetch()) {
                $error = "Email already registered by another user";
            } else {
                // Update user profile
                $stmt = $pdo->prepare("UPDATE users SET fullname = ?, email = ?, phone = ?, wallet_address = ? WHERE id = ?");
                if ($stmt->execute([$fullname, $email, $phone, $wallet_address, $_SESSION['user_id']])) {
                    $success = "Profile updated successfully!";
                    
                    // Refresh user data
                    $user = get_user_by_id($_SESSION['user_id']);
                    $fullname = $user['fullname'];
                    $email = $user['email'];
                    $phone = $user['phone'];
                    $wallet_address = $user['wallet_address'];
                } else {
                    $error = "Failed to update profile. Please try again.";
                }
            }
        }
    }
}

// Process password change form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    // CSRF Protection
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = "Invalid CSRF token";
    } else {
        // Sanitize input
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate input
        if (empty($current_password)) {
            $error = "Current password is required";
        } elseif (empty($new_password)) {
            $error = "New password is required";
        } elseif (!validate_password($new_password)) {
            $error = "Password must be at least 8 characters with uppercase, lowercase, and number";
        } elseif ($new_password !== $confirm_password) {
            $error = "New passwords do not match";
        } else {
            // Verify current password
            if (!password_verify($current_password, $user['password'])) {
                $error = "Current password is incorrect";
            } else {
                // Hash new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Update password
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                if ($stmt->execute([$hashed_password, $_SESSION['user_id']])) {
                    $success = "Password changed successfully!";
                } else {
                    $error = "Failed to change password. Please try again.";
                }
            }
        }
    }
}
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4"><i class="fas fa-user me-2"></i>User Profile</h2>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Profile Information -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-user-circle me-2"></i>Profile Information</h4>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="crypto-icon text-primary">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <h4 class="text-primary"><?php echo htmlspecialchars($user['username']); ?></h4>
                            <p class="text-muted">Member since <?php echo date('M j, Y', strtotime($user['created_at'])); ?></p>
                        </div>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            <input type="hidden" name="update_profile" value="1">
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label for="fullname" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo htmlspecialchars($fullname); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Mobile Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="wallet_address" class="form-label">Wallet Address</label>
                                <input type="text" class="form-control" id="wallet_address" name="wallet_address" value="<?php echo htmlspecialchars($wallet_address); ?>">
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Change Password -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="fas fa-lock me-2"></i>Change Password</h4>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="crypto-icon text-success">
                                <i class="fas fa-lock"></i>
                            </div>
                            <h4 class="text-success">Security Settings</h4>
                            <p class="text-muted">Update your password regularly for security</p>
                        </div>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            <input type="hidden" name="change_password" value="1">
                            
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <div class="form-text">At least 8 characters with uppercase, lowercase, and number</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-key me-2"></i>Change Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Account Statistics -->
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Account Statistics</h4>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="crypto-icon text-info">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <h5 class="text-info">Balance</h5>
                                <p class="display-6"><?php echo format_currency($user['balanceUSD'], 'USD'); ?></p>
                            </div>
                            <div class="col-6">
                                <div class="crypto-icon text-info">
                                    <i class="fas fa-coins"></i>
                                </div>
                                <h5 class="text-info">TRDX</h5>
                                <p class="display-6"><?php echo format_currency($user['balanceTRDX'], 'TRDX'); ?></p>
                            </div>
                        </div>
                        <div class="row text-center mt-3">
                            <div class="col-6">
                                <div class="crypto-icon text-warning">
                                    <i class="fas fa-gift"></i>
                                </div>
                                <h5 class="text-warning">Bonus</h5>
                                <p class="display-6"><?php echo format_currency($user['Totcommision'], 'USD'); ?></p>
                            </div>
                            <div class="col-6">
                                <div class="crypto-icon text-danger">
                                    <i class="fas fa-trophy"></i>
                                </div>
                                <h5 class="text-danger">Reward</h5>
                                <p class="display-6"><?php echo format_currency($user['omsetUSD'], 'USD'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>