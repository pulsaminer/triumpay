<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('?page=login');
}

// Get user data
$user = get_user_by_id($_SESSION['user_id']);
$referral_link = generate_referral_link($user['username']);
?>

<div class="container-fluid">
    <!-- Balance Section -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-transparent border border-white border-opacity-10 rounded-4 mb-4">
                <div class="card-body">
                    <h6 class="text-white mb-3">Your available balance</h6>
                    <h1 class="text-white display-5 fw-bold mb-1"><?php echo format_currency($user['balanceTRDX'], 'TRDX'); ?></h1>
                    <h4 class="text-white-50 fw-bold mb-3"><?php echo format_currency($user['balanceUSD'], 'USD'); ?></h4>
                    <div class="d-flex align-items-center">
                        <span class="text-success me-2">
                            <i class="fas fa-caret-up"></i> 2.5%
                        </span>
                        <span class="text-white-50 small">Last 24 Hours</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mb-4">
        <div class="col-3 mb-3 text-center">
            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 60px; height: 60px;">
                <i class="fas fa-arrow-up text-white fs-4"></i>
            </div>
            <div class="text-white small">Send</div>
        </div>
        <div class="col-3 mb-3 text-center">
            <div class="rounded-circle bg-success d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 60px; height: 60px;">
                <i class="fas fa-arrow-down text-white fs-4"></i>
            </div>
            <div class="text-white small">Receive</div>
        </div>
        <div class="col-3 mb-3 text-center">
            <div class="rounded-circle bg-warning d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 60px; height: 60px;">
                <i class="fas fa-exchange-alt text-white fs-4"></i>
            </div>
            <div class="text-white small">Swap</div>
        </div>
        <div class="col-3 mb-3 text-center">
            <div class="rounded-circle bg-info d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 60px; height: 60px;">
                <i class="fas fa-shopping-cart text-white fs-4"></i>
            </div>
            <div class="text-white small">Buy</div>
        </div>
    </div>

    <!-- Staking Status -->
    <div class="row">
        <div class="col-12 col-md-6 mb-4">
            <div class="card bg-transparent border border-white border-opacity-10 rounded-4 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="text-white mb-0">Staking Status</h5>
                        <i class="fas fa-piggy-bank text-primary"></i>
                    </div>
                    <?php if ($user['packageStaking']): ?>
                        <div class="text-center">
                            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-crown text-white fs-3"></i>
                            </div>
                            <h4 class="text-white">Active Staking</h4>
                            <p class="text-white-50">You are currently staking with the <strong class="text-primary"><?php echo $user['packageStaking']; ?></strong> package.</p>
                            <a href="?page=staking" class="btn btn-primary btn-sm">View Details</a>
                        </div>
                    <?php else: ?>
                        <div class="text-center">
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-piggy-bank text-white fs-3"></i>
                            </div>
                            <h4 class="text-white">No Active Staking</h4>
                            <p class="text-white-50">You are not currently staking.</p>
                            <a href="?page=staking" class="btn btn-primary btn-sm">Start Staking</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Mining Status -->
        <div class="col-12 col-md-6 mb-4">
            <div class="card bg-transparent border border-white border-opacity-10 rounded-4 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="text-white mb-0">Mining Status</h5>
                        <i class="fas fa-hammer text-warning"></i>
                    </div>
                    <?php
                    // Check if user can mine (24-hour cooldown)
                    $can_mine = true;
                    $stmt = $pdo->prepare("SELECT created_at FROM mining_history WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
                    $stmt->execute([$_SESSION['user_id']]);
                    $last_mine = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($last_mine) {
                        $next_mine_timestamp = strtotime($last_mine['created_at']) + 24 * 60 * 60; // 24 hours
                        if (time() < $next_mine_timestamp) {
                            $can_mine = false;
                        }
                    }
                    ?>
                    <div class="text-center">
                        <div class="rounded-circle bg-warning d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-microchip text-white fs-3"></i>
                        </div>
                        <?php if ($can_mine): ?>
                            <h4 class="text-white">Ready to Mine</h4>
                            <p class="text-white-50">Click the mining button to earn rewards.</p>
                            <a href="?page=mining" class="btn btn-warning btn-sm">Start Mining</a>
                        <?php else: ?>
                            <h4 class="text-white">Mining Cooldown</h4>
                            <p class="text-white-50">Next mining available in 24 hours.</p>
                            <a href="?page=mining" class="btn btn-secondary btn-sm">View History</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-transparent border border-white border-opacity-10 rounded-4">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <h5 class="text-white mb-0">Recent Activity</h5>
                    <i class="fas fa-chevron-right text-white-50"></i>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-borderless mb-0">
                            <tbody>
                                <?php
                                $stmt = $pdo->prepare("SELECT * FROM activity_log WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
                                $stmt->execute([$_SESSION['user_id']]);
                                $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                if (count($activities) > 0):
                                    foreach ($activities as $activity):
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php
                                            // Determine icon based on activity type
                                            $icon = 'fa-info-circle';
                                            $icon_class = 'bg-primary';
                                            if (strpos($activity['activity'], 'Deposit') !== false) {
                                                $icon = 'fa-arrow-down';
                                                $icon_class = 'bg-success';
                                            } elseif (strpos($activity['activity'], 'Send') !== false) {
                                                $icon = 'fa-arrow-up';
                                                $icon_class = 'bg-danger';
                                            } elseif (strpos($activity['activity'], 'Staking') !== false) {
                                                $icon = 'fa-piggy-bank';
                                                $icon_class = 'bg-warning';
                                            } elseif (strpos($activity['activity'], 'Mining') !== false) {
                                                $icon = 'fa-hammer';
                                                $icon_class = 'bg-info';
                                            }
                                            ?>
                                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3 <?php echo $icon_class; ?>" style="width: 40px; height: 40px;">
                                                <i class="fas <?php echo $icon; ?> text-white"></i>
                                            </div>
                                            <div>
                                                <div class="text-white fw-bold"><?php echo htmlspecialchars($activity['activity']); ?></div>
                                                <div class="text-white-50 small"><?php echo htmlspecialchars($activity['details']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-white-50 text-end">
                                        <?php echo date('M j, Y', strtotime($activity['created_at'])); ?>
                                        <div class="small"><?php echo date('H:i', strtotime($activity['created_at'])); ?></div>
                                    </td>
                                </tr>
                                <?php
                                    endforeach;
                                else:
                                ?>
                                <tr>
                                    <td colspan="2" class="text-center text-white-50 py-5">
                                        <i class="fas fa-inbox fa-2x mb-3"></i>
                                        <div>No recent activity</div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Add any necessary JavaScript here
</script>
