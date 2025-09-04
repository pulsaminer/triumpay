<?php
// Main entry point for Triumpay App
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/security.php';

// Set default page
// Check if user is logged in for protected pages
$protected_pages = ['dashboard', 'staking', 'mining', 'ppob', 'referral', 'profile'];

// Make $page variable global so header.php can access it
global $page;

// Set page after includes to ensure functions are available
$page = isset($_GET['page']) ? sanitize_input($_GET['page']) : 'landing';

if (in_array($page, $protected_pages) && !is_logged_in()) {
    $page = 'login';
}

// Error handling
    try {
        // Include header for all pages except landing
        if ($page !== 'landing') {
            include 'includes/header.php';
        }
        
        // Page routing
        switch($page) {
            case 'landing':
                include 'pages/landing.php';
                break;
            case 'login':
                include 'pages/login.php';
                break;
            case 'register':
                include 'pages/register.php';
                break;
            case 'dashboard':
                include 'pages/dashboard.php';
                break;
            case 'staking':
                include 'pages/staking.php';
                break;
            case 'mining':
                include 'pages/mining.php';
                break;
            case 'ppob':
                include 'pages/ppob.php';
                break;
            case 'referral':
                include 'pages/referral.php';
                break;
            case 'profile':
                include 'pages/profile.php';
                break;
            default:
                // Handle 404 errors for unknown pages
                http_response_code(404);
                include 'pages/404.php';
                exit();
                break;
        }
        
        // Include footer for all pages except landing
        if ($page !== 'landing') {
            include 'includes/footer.php';
        }
    } catch (Exception $e) {
        // Handle 500 errors for uncaught exceptions
        http_response_code(500);
        include 'pages/500.php';
        exit();
    } catch (Error $e) {
        // Handle 500 errors for PHP errors
        http_response_code(500);
        include 'pages/500.php';
        exit();
    }
?>