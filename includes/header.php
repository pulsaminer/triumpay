<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="./manifest.json">
    <link rel="icon" href="/assets/images/icon-192.png" type="image/x-icon">
    <meta name="theme-color" content="#00ffcc">
    <title><?php echo APP_NAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <?php
    // Determine if we should load auth.css or style.css
    // This variable is set in index.php before including the header
    global $page;
    $protected_pages = ['dashboard', 'staking', 'mining', 'ppob', 'referral', 'profile'];
    $should_load_auth_css = in_array($page, ['login', 'register']) || (in_array($page, $protected_pages) && !is_logged_in());
    if ($should_load_auth_css): ?>
        <link href="/assets/css/auth.css" rel="stylesheet">
    <?php else: ?>
        <link href="/assets/css/style.css" rel="stylesheet">
    <?php endif; ?>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Content Security Policy -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; connect-src 'self' https://solana-mainnet.g.alchemy.com https://api.mainnet-beta.solana.com; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; img-src 'self' data: https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; font-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com;">
    
    <!-- JavaScript Configuration -->
    <script>
        // Pass PHP configuration to JavaScript
        const SOLANA_RPC_ENDPOINT = <?php echo defined('SOLANA_RPC_ENDPOINT') ? json_encode(SOLANA_RPC_ENDPOINT) : '"https://api.mainnet-beta.solana.com"'; ?>;
    </script>
    </head>
<body>
    <!-- Header Section -->
    <?php
    // Determine if we should show the header
    global $page;
    $protected_pages = ['dashboard', 'staking', 'mining', 'ppob', 'referral', 'profile'];
    $should_show_header = !in_array($page, ['login', 'register']) && (in_array($page, $protected_pages) && is_logged_in() || !in_array($page, $protected_pages));
    if ($should_show_header): ?>
    <header class="bg-dark text-white py-3">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-4 d-flex align-items-center">
                    <img src="/assets/images/icon-192.png" alt="Logo" class="logo me-2">
                    <span class="tagline">Web3 Payment Solution</span>
                </div>
                <div class="col-8 text-end">
                    <?php if (is_logged_in()): ?>
                        <div class="d-flex justify-content-end align-items-center">
                            <!-- Scan Icon -->
                            <div class="header-icon me-3">
                                <a href="#" class="text-white">
                                    <i class="fas fa-qrcode fa-lg"></i>
                                </a>
                            </div>
                            
                            <!-- Notification Icon -->
                            <div class="header-icon me-3">
                                <a href="#" class="text-white position-relative">
                                    <i class="fas fa-bell fa-lg"></i>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        3
                                    </span>
                                </a>
                            </div>
                            
                            <!-- Avatar Icon (without circle background) -->
                            <div class="dropdown">
                                <a href="#" class="text-white dropdown-toggle" id="avatarDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user-circle fa-2x"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="avatarDropdown">
                                    <li><a class="dropdown-item" href="?page=dashboard"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                                    <li><a class="dropdown-item" href="?page=profile"><i class="fas fa-user me-2"></i>Profile</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="container-fluid mt-4">