<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user accessed this page through referral link
$referral_username = '';
if (isset($_GET['ref'])) {
    $referral_username = sanitize_input($_GET['ref']);
    // Validate referral ID
    $referral_user = get_user_by_username($referral_username);
    if (!$referral_user) {
        $error = "Invalid referral link. The referral ID does not exist.";
    }
}

// Initialize variables
$username = '';
$fullname = '';
$email = '';
$phone = '';
$referral_id = '';

$error = '';
$success = '';
$wallet_connected = false;
$wallet_address = '';

// Process registration form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    // CSRF Protection
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = "Invalid CSRF token";
    } else {
        // Sanitize input
        $referral_username = sanitize_input($_POST['referral_id']);
        $username = sanitize_input($_POST['username']);
        $fullname = sanitize_input($_POST['fullname']);
        $email = sanitize_input($_POST['email']);
        $phone = sanitize_input($_POST['phone']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $wallet_address = sanitize_input($_POST['wallet_address']);
        
        // Validate input
        if (empty($referral_username)) {
            $error = "Referral ID is required";
        } elseif (empty($username)) {
            $error = "Username is required";
        } elseif (!validate_username($username)) {
            $error = "Username must be 3-20 characters and contain only letters, numbers, and underscores";
        } elseif (empty($fullname)) {
            $error = "Full name is required";
        } elseif (empty($email)) {
            $error = "Email is required";
        } elseif (!validate_email($email)) {
            $error = "Invalid email format";
        } elseif (empty($phone)) {
            $error = "Phone number is required";
        } elseif (empty($password)) {
            $error = "Password is required";
        } elseif (!validate_password($password)) {
            $error = "Password must be at least 8 characters with uppercase, lowercase, and number";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match";
        } elseif (empty($wallet_address)) {
            $error = "Wallet address is required";
        } else {
            // Check if referral user exists
            $referral_user = get_user_by_username($referral_username);
            if (!$referral_user) {
                $error = "Invalid referral ID";
            } else {
                $referral_id = $referral_user['id'];
                
                // Check if username already exists
                $existing_user = get_user_by_username($username);
                if ($existing_user) {
                    $error = "Username already exists";
                } else {
                    // Check if email already exists
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                    $stmt->execute([$email]);
                    if ($stmt->fetch()) {
                        $error = "Email already registered";
                    } else {
                        // Hash password
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        
                        // Insert new user
                        $stmt = $pdo->prepare("INSERT INTO users (referral_id, username, fullname, email, phone, password, wallet_address) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        if ($stmt->execute([$referral_id, $username, $fullname, $email, $phone, $hashed_password, $wallet_address])) {
                            $user_id = $pdo->lastInsertId();
                            
                            // Send welcome email
                            send_welcome_email($email, $fullname, $username);
                            
                            // Set session variables for auto-login
                            $_SESSION['user_id'] = $user_id;
                            $_SESSION['username'] = $username;
                            
                            // Redirect to dashboard with absolute path
                            redirect('https://triumpay.app/index.php?page=dashboard');
                        } else {
                            $error = "Registration failed. Please try again.";
                        }
                    }
                }
            }
        }
    }
}
?>

<div class="auth-wrapper">
    <div class="auth-container">
    <div class="auth-header">
        <img src="/assets/images/logo.png" alt="Logo" class="auth-logo">
        <h1>Create Account</h1>
        <p>Join our community today</p>
        
        <!-- Registration steps -->
        <div class="steps-container">
            <div class="step completed">
                <div class="step-icon">1</div>
                <div class="step-label">Wallet</div>
            </div>
            <div class="step active">
                <div class="step-icon">2</div>
                <div class="step-label">Details</div>
            </div>
            <div class="step">
                <div class="step-icon">3</div>
                <div class="step-label">Confirm</div>
            </div>
        </div>
    </div>
    
    <div class="auth-body">
        <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <span class="alert-icon"><i class="fas fa-exclamation-circle"></i></span>
                        <span><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <span class="alert-icon"><i class="fas fa-check-circle"></i></span>
                        <span><?php echo $success; ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (empty($error) && !$wallet_connected): ?>
                    <div class="wallet-section">
                        <div class="wallet-icon">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <h3>Connect Your Wallet</h3>
                        <p>Please connect your Phantom Wallet to continue</p>
                        <button id="connectWallet" class="btn btn-primary btn-lg btn-block mt-3">
                            <i class="fas fa-plug me-2"></i>Connect Phantom Wallet
                        </button>
                    </div>
                <?php elseif (empty($error)): ?>
                    <div class="wallet-section">
                        <div class="wallet-icon wallet-connected">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3>Wallet Connected</h3>
                        <p>Your wallet is successfully connected</p>
                        <div class="wallet-address">
                            <?php echo substr($wallet_address, 0, 6) . '...' . substr($wallet_address, -4); ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (empty($error)): ?>
                <form method="POST" action="" id="registrationForm" style="<?php echo $wallet_connected ? '' : 'display:none'; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="register" value="1">
                    <input type="hidden" id="wallet_address" name="wallet_address" value="<?php echo htmlspecialchars($wallet_address); ?>">
                    
                    <div class="form-group">
                        <label for="referral_id" class="form-label">Referral ID *</label>
                        <input type="text" class="form-control" id="referral_id" name="referral_id" value="<?php echo htmlspecialchars($referral_username); ?>" required readonly>
                        <div class="form-text">You must register through a referral link</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="username" class="form-label">Username *</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" placeholder="Enter your username" required>
                        <div class="form-text">3-20 characters, letters, numbers, and underscores only</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="fullname" class="form-label">Full Name *</label>
                        <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo htmlspecialchars($fullname); ?>" placeholder="Enter your full name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address *</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Enter your email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone" class="form-label">Mobile Phone *</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" placeholder="Enter your phone number" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Password *</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                        <div class="form-text">At least 8 characters with uppercase, lowercase, and number</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm Password *</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        <i class="fas fa-user-plus me-2"></i>Create Account
                    </button>
                </form>
                <?php endif; ?>
        
        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
        </div>
    </div>
</div>
</div>

<script>
document.getElementById('connectWallet').addEventListener('click', async function() {
    if (typeof window.solana !== 'undefined' && window.solana.isPhantom) {
        try {
            const response = await window.solana.connect();
            const walletAddress = response.publicKey.toString();
            
            document.getElementById('wallet_address').value = walletAddress;
            
            // Update UI to show connected state
            const walletSection = document.querySelector('.wallet-section');
            walletSection.innerHTML = `
                <div class="wallet-icon wallet-connected">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3>Wallet Connected</h3>
                <p>Your wallet is successfully connected</p>
                <div class="wallet-address">
                    ${walletAddress.substring(0, 6)}...${walletAddress.substring(walletAddress.length - 4)}
                </div>
            `;
            
            // Show registration form
            document.getElementById('registrationForm').style.display = 'block';
            
            console.log('Connected to wallet:', walletAddress);
        } catch (err) {
            console.error('Connection failed:', err);
            
            // Show error message
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger';
            alertDiv.innerHTML = `
                <span class="alert-icon"><i class="fas fa-exclamation-circle"></i></span>
                <span>Failed to connect to Phantom Wallet. Please try again.</span>
            `;
            document.querySelector('.auth-body').insertBefore(alertDiv, document.querySelector('.wallet-section').nextSibling);
        }
    } else {
        // Show error message
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger';
        alertDiv.innerHTML = `
            <span class="alert-icon"><i class="fas fa-exclamation-triangle"></i></span>
            <span>Phantom Wallet is not installed. Please install it first.</span>
        `;
        document.querySelector('.auth-body').insertBefore(alertDiv, document.querySelector('.wallet-section').nextSibling);
    }
});
</script>