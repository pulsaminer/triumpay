<?php
// Security Functions
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function redirect($url) {
    // For relative paths within the app
    if (strpos($url, 'http') === false) {
        // If it's a page parameter
        if (strpos($url, '?page=') === 0) {
            header("Location: index.php" . $url);
        } else {
            header("Location: " . $url);
        }
    } else {
        // For absolute URLs
        header("Location: " . $url);
    }
    exit();
}

function get_user_by_id($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_user_by_username($username) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Email Functions
function send_welcome_email($email, $fullname, $username) {
    $api_key = MAILKETING_API_KEY;
    $url = MAILKETING_API_URL;
    
    $data = array(
        'api_key' => $api_key,
        'email' => $email,
        'name' => $fullname,
        'message' => "Welcome to Triumpay App, $fullname!\n\nYour username is: $username\n\nBest regards,\nTriumpay Team"
    );
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}

// Wallet Functions
function get_trdx_price() {
    // Try to get the price from Dexscreener API
    // Note: TRDX contract address on Solana needed
    $contract_address = '7EV2VjMrdZuJLbdZ39279TbRqkW8zWFwbLTQeg5swSyK'; // TRDX contract address on Solana
    $api_url = 'https://api.dexscreener.com/latest/dex/tokens/' . $contract_address;
    
    // Use cURL to fetch the price
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Check if the request was successful
    if ($http_code === 200 && $response) {
        $data = json_decode($response, true);
        
        // Check if price is in the response
        if (isset($data['pairs']) && is_array($data['pairs']) && count($data['pairs']) > 0) {
            // Get the first pair (most popular)
            $pair = $data['pairs'][0];
            if (isset($pair['priceUsd'])) {
                return floatval($pair['priceUsd']);
            }
        }
    }
    
    // Fallback to a default price if API request fails
    return 0.00014; // Example price
}

function convert_usd_to_trdx($usd_amount) {
    $trdx_price = get_trdx_price();
    if ($trdx_price > 0) {
        return $usd_amount / $trdx_price;
    }
    return 0;
}

// Format Functions
function format_currency($amount, $currency = 'USD') {
    if ($currency == 'USD') {
        return '$' . number_format($amount, 2);
    } else if ($currency == 'TRDX') {
        return number_format($amount, 6) . ' TRDX';
    }
    return $amount;
}

// Validation Functions
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validate_username($username) {
    return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
}

function validate_password($password) {
    // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&]{8,}$/', $password);
}

// Referral Functions
function generate_referral_link($username) {
    return APP_URL . '/ref/' . $username;
}

// Logging Functions
function log_activity($user_id, $activity, $details = '') {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, activity, details, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$user_id, $activity, $details]);
}

// Function to get RPC endpoint for JavaScript
function get_js_config() {
    $config = [
        'SOLANA_RPC_ENDPOINT' => defined('SOLANA_RPC_ENDPOINT') ? SOLANA_RPC_ENDPOINT : 'https://api.mainnet-beta.solana.com'
    ];
    return json_encode($config);
}
?>