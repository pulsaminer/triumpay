<?php
// Security middleware for Triumpay App

// Prevent direct access to this file
if (basename($_SERVER['SCRIPT_NAME']) == 'security.php') {
    header('HTTP/1.0 403 Forbidden');
    die('Forbidden');
}

// Set security headers
function setSecurityHeaders() {
    // Prevent clickjacking
    header('X-Frame-Options: DENY');
    
    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // Enable XSS protection
    header('X-XSS-Protection: 1; mode=block');
    
    // Content Security Policy
    header("Content-Security-Policy: default-src 'self'; connect-src 'self' https://solana-mainnet.g.alchemy.com/v2/o5TDGgQ0FQlrqjyaV4p9F; script-src 'self' 'unsafe-inline' https://unpkg.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; img-src 'self' data: https:; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com;");
    
    // Referrer Policy
    header('Referrer-Policy: no-referrer-when-downgrade');
    
    // Strict Transport Security (for HTTPS)
    // Uncomment the following line if using HTTPS
    // header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}

// Sanitize output to prevent XSS
function sanitize_output($data) {
    if (is_array($data)) {
        return array_map('sanitize_output', $data);
    } else {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
}

// Validate and sanitize user input
function validate_input($data, $type = 'string') {
    $data = trim($data);
    $data = stripslashes($data);
    
    switch ($type) {
        case 'email':
            return filter_var($data, FILTER_VALIDATE_EMAIL) ? htmlspecialchars($data, ENT_QUOTES, 'UTF-8') : false;
        case 'url':
            return filter_var($data, FILTER_VALIDATE_URL) ? htmlspecialchars($data, ENT_QUOTES, 'UTF-8') : false;
        case 'int':
            return filter_var($data, FILTER_VALIDATE_INT);
        case 'float':
            return filter_var($data, FILTER_VALIDATE_FLOAT);
        case 'string':
        default:
            return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
}

// Generate secure session
function startSecureSession() {
    // Start session if not already started
    if (session_status() == PHP_SESSION_NONE) {
        // Set session cookie parameters before starting session
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        
        // Force HTTPS if available
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            ini_set('session.cookie_secure', 1);
        }
        
        session_start();
    }
    
    // Prevent session fixation
    if (!isset($_SESSION['initiated'])) {
        session_regenerate_id(true);
        $_SESSION['initiated'] = true;
    }
    
    // Set session timeout
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        session_start();
    }
    $_SESSION['LAST_ACTIVITY'] = time();
}

// Rate limiting function
function checkRateLimit($identifier, $max_requests = 10, $time_window = 60) {
    $rate_limit_file = __DIR__ . '/../tmp/rate_limit_' . md5($identifier) . '.txt';
    
    // Create tmp directory if it doesn't exist
    if (!file_exists(__DIR__ . '/../tmp')) {
        mkdir(__DIR__ . '/../tmp', 0755, true);
    }
    
    // Read current count and timestamp
    $data = file_exists($rate_limit_file) ? json_decode(file_get_contents($rate_limit_file), true) : ['count' => 0, 'timestamp' => 0];
    
    $current_time = time();
    
    // Reset count if time window has passed
    if ($current_time - $data['timestamp'] > $time_window) {
        $data['count'] = 0;
        $data['timestamp'] = $current_time;
    }
    
    // Increment count
    $data['count']++;
    
    // Save data
    file_put_contents($rate_limit_file, json_encode($data));
    
    // Check if limit exceeded
    return $data['count'] <= $max_requests;
}

// IP blocking function
function isBlockedIP($max_attempts = 5, $block_duration = 3600) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $block_file = __DIR__ . '/../tmp/block_' . md5($ip) . '.txt';
    
    // Create tmp directory if it doesn't exist
    if (!file_exists(__DIR__ . '/../tmp')) {
        mkdir(__DIR__ . '/../tmp', 0755, true);
    }
    
    // Check if IP is blocked
    if (file_exists($block_file)) {
        $block_time = (int)file_get_contents($block_file);
        if (time() - $block_time < $block_duration) {
            return true;
        } else {
            // Unblock IP
            unlink($block_file);
        }
    }
    
    return false;
}

// Log failed attempts
function logFailedAttempt() {
    $ip = $_SERVER['REMOTE_ADDR'];
    $log_file = __DIR__ . '/../tmp/failed_attempts.log';
    
    // Create tmp directory if it doesn't exist
    if (!file_exists(__DIR__ . '/../tmp')) {
        mkdir(__DIR__ . '/../tmp', 0755, true);
    }
    
    $log_entry = date('Y-m-d H:i:s') . " - Failed attempt from IP: $ip\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

// Block IP after too many failed attempts
function blockIP($max_attempts = 5) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $attempt_file = __DIR__ . '/../tmp/attempts_' . md5($ip) . '.txt';
    $block_file = __DIR__ . '/../tmp/block_' . md5($ip) . '.txt';
    
    // Create tmp directory if it doesn't exist
    if (!file_exists(__DIR__ . '/../tmp')) {
        mkdir(__DIR__ . '/../tmp', 0755, true);
    }
    
    // Read current attempts
    $attempts = file_exists($attempt_file) ? (int)file_get_contents($attempt_file) : 0;
    
    // Increment attempts
    $attempts++;
    
    // Save attempts
    file_put_contents($attempt_file, $attempts);
    
    // Block IP if too many attempts
    if ($attempts >= $max_attempts) {
        file_put_contents($block_file, time());
        return true;
    }
    
    return false;
}

// Initialize security measures
setSecurityHeaders();
?>