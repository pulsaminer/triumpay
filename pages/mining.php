<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('?page=login');
}

// Get user data
$user = get_user_by_id($_SESSION['user_id']);

// Check if user can mine (24-hour cooldown)
$can_mine = true;
$next_mine_time = null;

$stmt = $pdo->prepare("SELECT created_at FROM mining_history WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$last_mine = $stmt->fetch(PDO::FETCH_ASSOC);

if ($last_mine) {
    $next_mine_timestamp = strtotime($last_mine['created_at']) + 24 * 60 * 60; // 24 hours
    if (time() < $next_mine_timestamp) {
        $can_mine = false;
        $next_mine_time = date('Y-m-d H:i:s', $next_mine_timestamp);
    }
}

// Handle mining form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mine'])) {
    // CSRF Protection
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = "Invalid CSRF token";
    } else {
        if (!$can_mine) {
            $error = "You can only mine once every 24 hours";
        } else {
            // Calculate reward
            $reward_trdx = 1.0; // Base reward for status 0
            $reward_usd = 0;
            
            if ($user['status'] == 1) {
                // 1% of staking amount for status 1
                $stmt = $pdo->prepare("SELECT SUM(amount_usd) as total_staking FROM staking WHERE user_id = ? AND status = 'active'");
                $stmt->execute([$_SESSION['user_id']]);
                $staking_data = $stmt->fetch(PDO::FETCH_ASSOC);
                $total_staking = $staking_data['total_staking'] ?? 0;
                $reward_trdx = $total_staking * 0.01; // 1% of staking amount
                $reward_usd = $reward_trdx * get_trdx_price();
            }
            
            // Insert mining history
            $stmt = $pdo->prepare("INSERT INTO mining_history (user_id, reward_trdx, reward_usd) VALUES (?, ?, ?)");
            if ($stmt->execute([$_SESSION['user_id'], $reward_trdx, $reward_usd])) {
                // Update user balance
                $stmt = $pdo->prepare("UPDATE users SET balanceTRDX = balanceTRDX + ? WHERE id = ?");
                $stmt->execute([$reward_trdx, $_SESSION['user_id']]);
                
                $success = "Mining successful! You earned " . format_currency($reward_trdx, 'TRDX');
                if ($reward_usd > 0) {
                    $success .= " (" . format_currency($reward_usd, 'USD') . ")";
                }
                
                // Log activity
                log_activity($_SESSION['user_id'], 'Mining', "Earned " . $reward_trdx . " TRDX");
                
                // Refresh user data
                $user = get_user_by_id($_SESSION['user_id']);
                
                // Update mining status
                $can_mine = false;
                $next_mine_time = date('Y-m-d H:i:s', time() + 24 * 60 * 60);
            } else {
                $error = "Failed to process mining. Please try again.";
            }
        }
    }
}

// Get mining history
$stmt = $pdo->prepare("SELECT * FROM mining_history WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->execute([$_SESSION['user_id']]);
$mining_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4"><i class="fas fa-hammer me-2"></i>Mining</h2>
        
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
        
        <!-- Mining Status Card -->
        <div class="card mb-4 mining-status-card">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><i class="fas fa-microchip me-2"></i>Mining Status</h4>
            </div>
            <div class="card-body text-center">
                <?php if ($can_mine): ?>
                    <div class="crypto-icon">
                        <i class="fas fa-hammer"></i>
                    </div>
                    <h3 class="text-success">Ready to Mine!</h3>
                    <p class="lead">You can mine now and earn rewards</p>
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <input type="hidden" name="mine" value="1">
                        <button type="submit" class="btn btn-success btn-lg mining-button">
                            <i class="fas fa-hammer me-2"></i>Mine Now
                        </button>
                    </form>
                <?php else: ?>
                    <div class="crypto-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="text-warning">Mining Cooldown</h3>
                    <p class="lead">Next mining available at:</p>
                    <div class="mining-timer"><?php echo date('M j, Y H:i', strtotime($next_mine_time)); ?></div>
                    <div class="mt-3">
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 50%"></div>
                        </div>
                        <p class="mt-2">Mining power recharging...</p>
                    </div>
                <?php endif; ?>
                
                <div class="mt-4">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Your Current Balance</h5>
                            <h3 class="crypto-balance"><?php echo format_currency($user['balanceTRDX'], 'TRDX'); ?></h3>
                        </div>
                        <div class="col-md-6">
                            <h5>Mining Status</h5>
                            <h3 class="<?php echo $user['status'] == 1 ? 'text-success' : 'text-warning'; ?>">
                                <?php echo $user['status'] == 1 ? 'Staking User' : 'Regular User'; ?>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mining Rewards Info -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h4 class="mb-0"><i class="fas fa-gift me-2"></i>Mining Rewards</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="crypto-card">
                            <h5 class="text-warning"><i class="fas fa-user me-2"></i>Regular User (Status 0)</h5>
                            <ul class="mt-3">
                                <li>1 TRDX per day</li>
                                <li>No staking required</li>
                                <li>Basic mining rewards</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="crypto-card">
                            <h5 class="text-success"><i class="fas fa-crown me-2"></i>Staking User (Status 1)</h5>
                            <ul class="mt-3">
                                <li>1% of your staking amount per day</li>
                                <li>Higher rewards with more staking</li>
                                <li>Premium mining benefits</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mining History -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-history me-2"></i>Mining History</h4>
            </div>
            <div class="card-body">
                <?php if (count($mining_history) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Reward (TRDX)</th>
                                    <th>Reward (USD)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mining_history as $record): ?>
                                    <tr>
                                        <td><?php echo date('M j, Y H:i', strtotime($record['created_at'])); ?></td>
                                        <td><?php echo format_currency($record['reward_trdx'], 'TRDX'); ?></td>
                                        <td><?php echo format_currency($record['reward_usd'], 'USD'); ?></td>
                                        <td><span class="badge bg-success">Completed</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center">
                        <div class="crypto-icon">
                            <i class="fas fa-history"></i>
                        </div>
                        <h4 class="text-muted">No mining history yet</h4>
                        <p>Start mining to see your rewards history</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>