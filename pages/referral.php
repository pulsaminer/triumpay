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

// Get referral statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as total_referrals FROM users WHERE referral_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$referral_stats = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT SUM(Totcommision) as total_commission FROM users WHERE referral_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$commission_stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Get referral list
$stmt = $pdo->prepare("SELECT username, fullname, created_at FROM users WHERE referral_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$referrals = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get commission history
$stmt = $pdo->prepare("SELECT rc.*, u.username as referral_username FROM referral_commissions rc JOIN users u ON rc.referral_id = u.id WHERE rc.user_id = ? ORDER BY rc.created_at DESC LIMIT 10");
$stmt->execute([$_SESSION['user_id']]);
$commission_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4"><i class="fas fa-users me-2"></i>Referral Program</h2>
        
        <!-- Referral Link Card -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-link me-2"></i>Your Referral Link</h4>
            </div>
            <div class="card-body">
                <div class="referral-link-container">
                    <div class="input-group">
                        <input type="text" class="form-control" value="<?php echo $referral_link; ?>" readonly>
                        <button class="btn btn-outline-secondary" type="button" id="copyReferral">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <button class="btn btn-success" id="shareReferral">
                        <i class="fas fa-share-alt me-2"></i>Share Referral Link
                    </button>
                </div>
                <div class="text-center mt-2">
                    <small class="text-muted">Share this link with others to earn referral commissions</small>
                </div>
            </div>
        </div>
        
        <!-- Referral Statistics -->
        <div class="row mb-4">
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="card h-100 crypto-card">
                    <div class="card-body text-center">
                        <div class="crypto-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="crypto-label">Total Referrals</div>
                        <div class="crypto-balance"><?php echo $referral_stats['total_referrals']; ?></div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="card h-100 crypto-card">
                    <div class="card-body text-center">
                        <div class="crypto-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="crypto-label">Total Commission</div>
                        <div class="crypto-balance"><?php echo format_currency($commission_stats['total_commission'] ?? 0, 'USD'); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="card h-100 crypto-card">
                    <div class="card-body text-center">
                        <div class="crypto-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="crypto-label">Active Referrals</div>
                        <div class="crypto-balance">0</div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="card h-100 crypto-card">
                    <div class="card-body text-center">
                        <div class="crypto-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="crypto-label">Pending Commission</div>
                        <div class="crypto-balance"><?php echo format_currency(0, 'USD'); ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Referral List -->
        <div class="card mb-4">
            <div class="card-header bg-secondary text-white">
                <h4 class="mb-0"><i class="fas fa-list me-2"></i>Your Referrals</h4>
            </div>
            <div class="card-body">
                <?php if (count($referrals) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Full Name</th>
                                    <th>Join Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($referrals as $referral): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($referral['username']); ?></td>
                                        <td><?php echo htmlspecialchars($referral['fullname']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($referral['created_at'])); ?></td>
                                        <td><span class="badge bg-success">Active</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center">
                        <div class="crypto-icon">
                            <i class="fas fa-user-friends"></i>
                        </div>
                        <h4 class="text-muted">No referrals yet</h4>
                        <p>Share your referral link to start earning commissions</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Commission History -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h4 class="mb-0"><i class="fas fa-history me-2"></i>Commission History</h4>
            </div>
            <div class="card-body">
                <?php if (count($commission_history) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Referral</th>
                                    <th>Commission (USD)</th>
                                    <th>Commission (TRDX)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($commission_history as $commission): ?>
                                    <tr>
                                        <td><?php echo date('M j, Y H:i', strtotime($commission['created_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($commission['referral_username']); ?></td>
                                        <td><?php echo format_currency($commission['commission_usd'], 'USD'); ?></td>
                                        <td><?php echo format_currency($commission['commission_trdx'], 'TRDX'); ?></td>
                                        <td><span class="badge bg-success">Paid</span></td>
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
                        <h4 class="text-muted">No commission history yet</h4>
                        <p>Start referring users to earn commissions</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('copyReferral').addEventListener('click', function() {
    const referralInput = document.querySelector('input[value="<?php echo $referral_link; ?>"]');
    referralInput.select();
    document.execCommand('copy');
    
    const originalText = this.innerHTML;
    this.innerHTML = '<i class="fas fa-check"></i>';
    setTimeout(() => {
        this.innerHTML = originalText;
    }, 2000);
});

document.getElementById('shareReferral').addEventListener('click', function() {
    if (navigator.share) {
        navigator.share({
            title: 'Join me on Triumpay App',
            text: 'Use my referral link to join Triumpay App and start earning rewards!',
            url: '<?php echo $referral_link; ?>'
        }).catch(console.error);
    } else {
        // Fallback for browsers that don't support Web Share API
        const referralInput = document.querySelector('input[value="<?php echo $referral_link; ?>"]');
        referralInput.select();
        document.execCommand('copy');
        
        const originalText = document.getElementById('copyReferral').innerHTML;
        document.getElementById('copyReferral').innerHTML = '<i class="fas fa-check"></i>';
        setTimeout(() => {
            document.getElementById('copyReferral').innerHTML = originalText;
        }, 2000);
        
        alert('Referral link copied to clipboard!');
    }
});
</script>