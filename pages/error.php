<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Get error code and message from URL parameters
$error_code = isset($_GET['code']) ? intval($_GET['code']) : 400;
$error_message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'An error occurred';

// Set default values
$icon = 'fa-exclamation-circle';
$title = 'Error';
$description = 'An unexpected error occurred.';

// Customize based on error code
switch ($error_code) {
    case 400:
        $icon = 'fa-exclamation-triangle';
        $title = 'Bad Request';
        $description = 'The request could not be understood by the server due to malformed syntax.';
        break;
    case 401:
        $icon = 'fa-exclamation-triangle';
        $title = 'Unauthorized';
        $description = 'You are not authorized to access this resource.';
        break;
    case 403:
        $icon = 'fa-exclamation-triangle';
        $title = 'Forbidden';
        $description = 'You don\'t have permission to access this resource.';
        break;
    case 405:
        $icon = 'fa-exclamation-triangle';
        $title = 'Method Not Allowed';
        $description = 'The request method is not supported for this resource.';
        break;
    case 429:
        $icon = 'fa-exclamation-triangle';
        $title = 'Too Many Requests';
        $description = 'You have sent too many requests in a given amount of time.';
        break;
    default:
        $icon = 'fa-exclamation-circle';
        $title = 'Error';
        $description = 'An unexpected error occurred.';
        break;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> | <?php echo APP_NAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="/assets/css/style.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header Section -->
    <header class="bg-dark text-white py-3">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-4 d-flex align-items-center">
                    <img src="/assets/images/logo.png" alt="Logo" class="logo me-2">
                    <span class="tagline">Web3 Payment Solution</span>
                </div>
                <div class="col-8 text-end">
                    <!-- Empty for error pages -->
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container-fluid mt-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card bg-transparent border border-white border-opacity-10 rounded-4">
                    <div class="card-body text-center py-5">
                        <div class="error-icon mb-4">
                            <i class="fas <?php echo $icon; ?> text-warning fa-5x"></i>
                        </div>
                        <h1 class="display-1 fw-bold text-white mb-3"><?php echo $error_code; ?></h1>
                        <h2 class="text-white mb-3"><?php echo $title; ?></h2>
                        <p class="text-white-50 mb-4">
                            <?php echo $description; ?>
                        </p>
                        <div class="d-grid gap-2 col-6 mx-auto">
                            <a href="/" class="btn btn-primary btn-lg rounded-3">
                                <i class="fas fa-home me-2"></i>Go to Homepage
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>