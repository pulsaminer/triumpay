<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('?page=login');
}

// Get user data
$user = get_user_by_id($_SESSION['user_id']);
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4"><i class="fas fa-receipt me-2"></i>PPOB (Payment Point Online Bank)</h2>
        
        <div class="alert alert-info">
            <h4 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Coming Soon!</h4>
            <p>The PPOB feature is currently under development. This feature will allow you to:</p>
            <div class="row">
                <div class="col-md-6">
                    <ul>
                        <li><i class="fas fa-bolt me-2"></i>Pay electricity bills</li>
                        <li><i class="fas fa-tint me-2"></i>Pay water bills</li>
                        <li><i class="fas fa-mobile-alt me-2"></i>Buy phone credits</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <ul>
                        <li><i class="fas fa-wifi me-2"></i>Pay internet bills</li>
                        <li><i class="fas fa-tv me-2"></i>TV subscription payments</li>
                        <li><i class="fas fa-ellipsis-h me-2"></i>And many other payment services</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-list me-2"></i>Available Services</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 crypto-card text-center">
                            <div class="card-body">
                                <div class="crypto-icon text-warning">
                                    <i class="fas fa-bolt"></i>
                                </div>
                                <h5 class="text-warning">Electricity</h5>
                                <p class="card-text">Pay your electricity bills quickly and easily</p>
                                <button class="btn btn-outline-warning" disabled>
                                    <i class="fas fa-lock me-2"></i>Coming Soon
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 crypto-card text-center">
                            <div class="card-body">
                                <div class="crypto-icon text-primary">
                                    <i class="fas fa-tint"></i>
                                </div>
                                <h5 class="text-primary">Water</h5>
                                <p class="card-text">Pay your water bills with just a few clicks</p>
                                <button class="btn btn-outline-primary" disabled>
                                    <i class="fas fa-lock me-2"></i>Coming Soon
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 crypto-card text-center">
                            <div class="card-body">
                                <div class="crypto-icon text-success">
                                    <i class="fas fa-mobile-alt"></i>
                                </div>
                                <h5 class="text-success">Phone Credit</h5>
                                <p class="card-text">Top up your phone credit instantly</p>
                                <button class="btn btn-outline-success" disabled>
                                    <i class="fas fa-lock me-2"></i>Coming Soon
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 crypto-card text-center">
                            <div class="card-body">
                                <div class="crypto-icon text-info">
                                    <i class="fas fa-wifi"></i>
                                </div>
                                <h5 class="text-info">Internet</h5>
                                <p class="card-text">Pay your internet bills without hassle</p>
                                <button class="btn btn-outline-info" disabled>
                                    <i class="fas fa-lock me-2"></i>Coming Soon
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 crypto-card text-center">
                            <div class="card-body">
                                <div class="crypto-icon text-danger">
                                    <i class="fas fa-tv"></i>
                                </div>
                                <h5 class="text-danger">TV Subscription</h5>
                                <p class="card-text">Pay for your TV subscription packages</p>
                                <button class="btn btn-outline-danger" disabled>
                                    <i class="fas fa-lock me-2"></i>Coming Soon
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 crypto-card text-center">
                            <div class="card-body">
                                <div class="crypto-icon text-secondary">
                                    <i class="fas fa-ellipsis-h"></i>
                                </div>
                                <h5 class="text-secondary">More Services</h5>
                                <p class="card-text">Many more payment services coming soon</p>
                                <button class="btn btn-outline-secondary" disabled>
                                    <i class="fas fa-lock me-2"></i>Coming Soon
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><i class="fas fa-bell me-2"></i>Stay Updated</h4>
            </div>
            <div class="card-body text-center">
                <div class="crypto-icon text-success">
                    <i class="fas fa-bell"></i>
                </div>
                <h4 class="text-success">Get Notified When Services Are Available</h4>
                <p class="lead">Subscribe to our newsletter to be the first to know when new PPOB services are launched.</p>
                <form>
                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="email" class="form-control" placeholder="Enter your email">
                                <button class="btn btn-success" type="button">
                                    <i class="fas fa-paper-plane me-2"></i>Subscribe
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>