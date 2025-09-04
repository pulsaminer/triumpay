<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('?page=login');
}

// Get user data
$user = get_user_by_id($_SESSION['user_id']);

// Get staking packages
$stmt = $pdo->prepare("SELECT * FROM staking_packages ORDER BY min_amount ASC");
$stmt->execute();
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get staking history
$stmt = $pdo->prepare("SELECT * FROM staking WHERE user_id = ? ORDER BY start_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$staking_history = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get deposit history
$stmt = $pdo->prepare("SELECT * FROM deposits WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$deposit_history = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle deposit form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['deposit'])) {
    // CSRF Protection
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = "Invalid CSRF token";
    } else {
        $amount_usd = floatval($_POST['amount_usd']);
        $tx_hash = sanitize_input($_POST['tx_hash']);
        
        // Validate amount
        if ($amount_usd < MIN_DEPOSIT || $amount_usd > MAX_DEPOSIT) {
            $error = "Amount must be between $" . MIN_DEPOSIT . " and $" . MAX_DEPOSIT;
        } else {
            // Convert USD to TRDX
            $amount_trdx = convert_usd_to_trdx($amount_usd);
            
            // Insert deposit record
            $stmt = $pdo->prepare("INSERT INTO deposits (user_id, amount_usd, amount_trdx, tx_hash) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$_SESSION['user_id'], $amount_usd, $amount_trdx, $tx_hash])) {
                // Update user balance
                $stmt = $pdo->prepare("UPDATE users SET balanceUSD = balanceUSD + ? WHERE id = ?");
                $stmt->execute([$amount_usd, $_SESSION['user_id']]);
                
                $success = "Deposit successful! Your balance has been updated.";
                
                // Log activity
                log_activity($_SESSION['user_id'], 'Deposit', "Deposited $" . $amount_usd . " (" . $amount_trdx . " TRDX)");
                
                // Refresh user data
                $user = get_user_by_id($_SESSION['user_id']);
                
                // Refresh deposit history
                $stmt = $pdo->prepare("SELECT * FROM deposits WHERE user_id = ? ORDER BY created_at DESC");
                $stmt->execute([$_SESSION['user_id']]);
                $deposit_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $error = "Failed to process deposit. Please try again.";
            }
        }
    }
}

// Handle staking form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['start_staking'])) {
    // CSRF Protection
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = "Invalid CSRF token";
    } else {
        $package_id = intval($_POST['package_id']);
        $amount_usd = floatval($_POST['staking_amount']);
        
        // Get package details
        $stmt = $pdo->prepare("SELECT * FROM staking_packages WHERE id = ?");
        $stmt->execute([$package_id]);
        $package = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$package) {
            $error = "Invalid staking package";
        } elseif ($amount_usd < $package['min_amount'] || $amount_usd > $package['max_amount']) {
            $error = "Amount must be between $" . $package['min_amount'] . " and $" . $package['max_amount'];
        } elseif ($user['balanceUSD'] < $amount_usd) {
            $error = "Insufficient balance";
        } else {
            // Calculate end date
            $start_date = date('Y-m-d');
            $end_date = date('Y-m-d', strtotime("+$package[term_days] days"));
            
            // Insert staking record
            $stmt = $pdo->prepare("INSERT INTO staking (user_id, package, amount_usd, start_date, end_date, roi_rate) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$_SESSION['user_id'], $package['name'], $amount_usd, $start_date, $end_date, $package['roi_rate']])) {
                // Update user package and status
                $stmt = $pdo->prepare("UPDATE users SET packageStaking = ?, status = 1, balanceUSD = balanceUSD - ? WHERE id = ?");
                $stmt->execute([$package['name'], $amount_usd, $_SESSION['user_id']]);
                
                $success = "Staking started successfully!";
                
                // Log activity
                log_activity($_SESSION['user_id'], 'Start Staking', "Started " . $package['name'] . " package with $" . $amount_usd);
                
                // Refresh user data
                $user = get_user_by_id($_SESSION['user_id']);
                
                // Refresh staking history
                $stmt = $pdo->prepare("SELECT * FROM staking WHERE user_id = ? ORDER BY start_date DESC");
                $stmt->execute([$_SESSION['user_id']]);
                $staking_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $error = "Failed to start staking. Please try again.";
            }
        }
    }
}
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-transparent border border-white border-opacity-10 rounded-4 mb-4">
                <div class="card-body text-center py-5">
                    <h1 class="text-white display-5 fw-bold mb-3">Staking</h1>
                    <p class="text-white-50 mb-0">Earn passive income by staking your TRDX tokens</p>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Staking Packages -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-transparent border border-white border-opacity-10 rounded-4">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h3 class="text-white mb-0">Staking Packages</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <input type="hidden" name="start_staking" value="1">
                        
                        <div class="row align-items-end">
                            <!-- Package Selection -->
                            <div class="col-md-4 mb-3">
                                <label for="package_id" class="form-label text-white">Select Package</label>
                                <select class="form-select bg-dark text-white border border-white border-opacity-10 rounded-3" id="package_id" name="package_id" required>
                                    <option value="">Choose a package</option>
                                    <?php foreach ($packages as $package): ?>
                                        <option value="<?php echo $package['id']; ?>" 
                                                data-min="<?php echo $package['min_amount']; ?>" 
                                                data-max="<?php echo $package['max_amount']; ?>"
                                                data-roi="<?php echo $package['roi_rate']; ?>"
                                                data-term="<?php echo $package['term_days']; ?>">
                                            <?php echo $package['name']; ?> ($<?php echo $package['min_amount']; ?> - $<?php echo $package['max_amount']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Amount Input -->
                            <div class="col-md-4 mb-3">
                                <label for="staking_amount" class="form-label text-white">Amount in USD</label>
                                <input type="number" class="form-control bg-dark text-white border border-white border-opacity-10 rounded-3" id="staking_amount" name="staking_amount" step="0.01" placeholder="Enter amount" required>
                                <div class="form-text text-white-50">Your Balance: $<?php echo $user['balanceUSD']; ?></div>
                            </div>
                            
                            <!-- Submit Button -->
                            <div class="col-md-4 mb-3">
                                <button type="submit" class="btn btn-success rounded-3 w-100" id="start_staking_button" disabled>
                                    <i class="fas fa-play-circle me-2"></i>Start Staking
                                </button>
                            </div>
                        </div>
                        
                        <!-- Package Details Display -->
                        <div class="row mt-3" id="package_details" style="display: none;">
                            <div class="col-12">
                                <div class="bg-dark border border-white border-opacity-10 rounded-3 p-3">
                                    <h5 class="text-white mb-3">Package Details</h5>
                                    <div class="row">
                                        <div class="col-md-3 mb-2">
                                            <span class="text-white-50">Package:</span>
                                            <span class="text-white fw-bold" id="detail_package_name"></span>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <span class="text-white-50">Investment:</span>
                                            <span class="text-white fw-bold" id="detail_investment_range"></span>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <span class="text-white-50">ROI:</span>
                                            <span class="text-white fw-bold" id="detail_roi"></span>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <span class="text-white-50">Term:</span>
                                            <span class="text-white fw-bold" id="detail_term"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Deposit Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-transparent border border-white border-opacity-10 rounded-4">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h3 class="text-white mb-0">Deposit TRDX</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="" id="depositForm">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <input type="hidden" name="deposit" value="1">
                        <input type="hidden" id="tx_hash" name="tx_hash" value="">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="amount_usd" class="form-label text-white">Amount in USD</label>
                                <input type="number" class="form-control bg-dark text-white border border-white border-opacity-10 rounded-3" id="amount_usd" name="amount_usd" min="<?php echo MIN_DEPOSIT; ?>" max="<?php echo MAX_DEPOSIT; ?>" step="0.01" required>
                                <div class="form-text text-white-50">Min: $<?php echo MIN_DEPOSIT; ?>, Max: $<?php echo MAX_DEPOSIT; ?></div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="amount_trdx" class="form-label text-white">Amount in TRDX</label>
                                <input type="text" class="form-control bg-dark text-white border border-white border-opacity-10 rounded-3" id="amount_trdx" readonly>
                                <div class="form-text text-white-50">Current TRDX Price: $<span id="trdx_price"><?php echo get_trdx_price(); ?></span></div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-white">Receiver Wallet Address</label>
                            <input type="text" class="form-control bg-dark text-white border border-white border-opacity-10 rounded-3" value="<?php echo RECEIVER_WALLET_ADDRESS; ?>" readonly>
                            <div class="form-text text-white-50">Note: You need a small amount of SOL in your wallet to cover transaction fees (typically less than 0.001 SOL)</div>
                        </div>
                        
                        <div class="mb-3 wallet-buttons">
                                                    <button type="button" id="connectWallet" class="btn btn-primary rounded-3">
                                                        <i class="fas fa-wallet me-2"></i>Connect Phantom Wallet
                                                    </button>
                                                    <button type="button" id="disconnectWallet" class="btn btn-danger rounded-3" style="display: none;">
                                                        <i class="fas fa-times-circle me-2"></i>Disconnect Wallet
                                                    </button>
                                                    <button type="button" id="sendPayment" class="btn btn-success rounded-3" disabled>
                                                        <i class="fas fa-paper-plane me-2"></i>Send Payment
                                                    </button>
                                                    <div id="wallet_balance" class="wallet-balance text-white" style="display: none;"></div>
                                                </div>
                        <button type="submit" class="btn btn-primary rounded-3 w-100" id="confirmDeposit" disabled>
                            <i class="fas fa-check-circle me-2"></i>Confirm Deposit
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Staking History -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-transparent border border-white border-opacity-10 rounded-4">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h3 class="text-white mb-0">Staking History</h3>
                </div>
                <div class="card-body">
                    <?php if (count($staking_history) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-dark table-borderless mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-white-50">Package</th>
                                        <th class="text-white-50">Amount</th>
                                        <th class="text-white-50">ROI</th>
                                        <th class="text-white-50">Start Date</th>
                                        <th class="text-white-50">End Date</th>
                                        <th class="text-white-50">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($staking_history as $history): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php
                                                    $icon = 'fa-box';
                                                    $icon_class = 'bg-primary';
                                                    if ($history['package'] == 'Silver') {
                                                        $icon = 'fa-coins';
                                                        $icon_class = 'bg-secondary';
                                                    } elseif ($history['package'] == 'Gold') {
                                                        $icon = 'fa-trophy';
                                                        $icon_class = 'bg-warning';
                                                    } elseif ($history['package'] == 'Platinum') {
                                                        $icon = 'fa-crown';
                                                        $icon_class = 'bg-info';
                                                    }
                                                    ?>
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3 <?php echo $icon_class; ?>" style="width: 30px; height: 30px;">
                                                        <i class="fas <?php echo $icon; ?> text-white fs-6"></i>
                                                    </div>
                                                    <span class="text-white"><?php echo $history['package']; ?></span>
                                                </div>
                                            </td>
                                            <td class="text-white">$<?php echo number_format($history['amount_usd'], 2); ?></td>
                                            <td class="text-white"><?php echo $history['roi_rate']; ?>% daily</td>
                                            <td class="text-white"><?php echo date('M j, Y', strtotime($history['start_date'])); ?></td>
                                            <td class="text-white"><?php echo date('M j, Y', strtotime($history['end_date'])); ?></td>
                                            <td>
                                                <?php if ($history['status'] == 'active'): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php elseif ($history['status'] == 'completed'): ?>
                                                    <span class="badge bg-info">Completed</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Cancelled</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-history fa-3x text-white-50 mb-3"></i>
                            <p class="text-white-50 mb-0">No staking history found</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Deposit History -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-transparent border border-white border-opacity-10 rounded-4">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h3 class="text-white mb-0">Deposit History</h3>
                </div>
                <div class="card-body">
                    <?php if (count($deposit_history) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-dark table-borderless mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-white-50">Transaction Hash</th>
                                        <th class="text-white-50">Amount (USD)</th>
                                        <th class="text-white-50">Amount (TRDX)</th>
                                        <th class="text-white-50">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($deposit_history as $deposit): ?>
                                        <tr>
                                            <td class="text-white">
                                                <a href="https://solscan.io/tx/<?php echo $deposit['tx_hash']; ?>" target="_blank" class="text-primary">
                                                    <?php echo substr($deposit['tx_hash'], 0, 10); ?>...<?php echo substr($deposit['tx_hash'], -10); ?>
                                                </a>
                                            </td>
                                            <td class="text-white">$<?php echo number_format($deposit['amount_usd'], 2); ?></td>
                                            <td class="text-white"><?php echo number_format($deposit['amount_trdx'], 6); ?> TRDX</td>
                                            <td class="text-white"><?php echo date('M j, Y H:i', strtotime($deposit['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-receipt fa-3x text-white-50 mb-3"></i>
                            <p class="text-white-50 mb-0">No deposit history found</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Calculate TRDX amount when USD amount changes
document.getElementById('amount_usd').addEventListener('input', function() {
    const usdAmount = parseFloat(this.value);
    if (!isNaN(usdAmount) && usdAmount > 0) {
        const trdxPrice = <?php echo get_trdx_price(); ?>;
        const trdxAmount = usdAmount / trdxPrice;
        document.getElementById('amount_trdx').value = trdxAmount.toFixed(6) + ' TRDX';
    } else {
        document.getElementById('amount_trdx').value = '';
    }
    
    // Enable/disable send payment button based on amount
    const sendPaymentButton = document.getElementById('sendPayment');
    if (sendPaymentButton) {
        const amount = parseFloat(this.value);
        if (!isNaN(amount) && amount >= <?php echo MIN_DEPOSIT; ?> && amount <= <?php echo MAX_DEPOSIT; ?>) {
            // Only enable if wallet is connected
            if (typeof window.solana !== 'undefined' && window.solana.isPhantom && window.solana.isConnected) {
                sendPaymentButton.disabled = false;
            }
        } else {
            sendPaymentButton.disabled = true;
        }
    }
});

// Check for existing Phantom Wallet connection on page load
document.addEventListener('DOMContentLoaded', async function() {
    if (typeof window.solana !== 'undefined' && window.solana.isPhantom) {
        try {
            // Check if already connected
            if (window.solana.isConnected) {
                // Update UI to show connected state
                showConnectedState();
                // Show wallet balance
                await showWalletBalance();
            }
        } catch (err) {
            console.log('No existing Phantom connection found');
        }
    }
});

// Show connected state
function showConnectedState() {
    document.getElementById('connectWallet').style.display = 'none';
    document.getElementById('disconnectWallet').style.display = 'inline-block';
    // Don't automatically enable sendPayment button - it should only be enabled when
    // both wallet is connected AND valid amount is entered
    // Check if there's already a valid amount entered
    const amountInput = document.getElementById('amount_usd');
    const sendPaymentButton = document.getElementById('sendPayment');
    if (amountInput && sendPaymentButton && amountInput.value) {
        const amount = parseFloat(amountInput.value);
        if (!isNaN(amount) && amount >= <?php echo MIN_DEPOSIT; ?> && amount <= <?php echo MAX_DEPOSIT; ?>) {
            sendPaymentButton.disabled = false;
        } else {
            sendPaymentButton.disabled = true;
        }
    } else if (sendPaymentButton) {
        sendPaymentButton.disabled = true;
    }
}

// Show wallet connection status with wallet address and TRDX balance
async function showWalletBalance() {
    if (typeof window.solana !== 'undefined' && window.solana.isPhantom && window.solana.isConnected) {
        // Get the wallet address and format it
        const walletAddress = window.solana.publicKey.toString();
        const formattedAddress = walletAddress.substring(0, 6) + '...' + walletAddress.substring(walletAddress.length - 6);
        
        // Display connection status with wallet address in the UI
        const balanceElement = document.getElementById('wallet_balance');
        if (balanceElement) {
            // Show loading state while fetching balance
            balanceElement.textContent = 'Connected: ' + formattedAddress + ' | TRDX Balance: Loading...';
            balanceElement.style.display = 'block';
            
            // Try to get TRDX balance
            try {
                if (typeof window.TriumpayWallet !== 'undefined' && typeof window.TriumpayWallet.getTRDXBalance === 'function') {
                    const trdxBalance = await window.TriumpayWallet.getTRDXBalance();
                    balanceElement.textContent = 'Connected: ' + formattedAddress + ' | TRDX Balance: ' + trdxBalance.toFixed(6);
                } else {
                    balanceElement.textContent = 'Connected: ' + formattedAddress + ' | TRDX Balance: Unable to fetch';
                }
            } catch (err) {
                console.error('Error fetching TRDX balance:', err);
                balanceElement.textContent = 'Connected: ' + formattedAddress + ' | TRDX Balance: Error fetching';
            }
        }
    }
}

// Show disconnected state
function showDisconnectedState() {
    document.getElementById('connectWallet').style.display = 'inline-block';
    document.getElementById('disconnectWallet').style.display = 'none';
    document.getElementById('sendPayment').disabled = true;
    document.getElementById('confirmDeposit').disabled = true;
    document.getElementById('connectWallet').innerHTML = '<i class="fas fa-wallet me-2"></i>Connect Phantom Wallet';
    document.getElementById('connectWallet').classList.remove('btn-success');
    document.getElementById('connectWallet').classList.add('btn-primary');
}

// Phantom Wallet integration
document.getElementById('connectWallet').addEventListener('click', async function() {
    if (typeof window.solana !== 'undefined' && window.solana.isPhantom) {
        try {
            const response = await window.solana.connect();
            const walletAddress = response.publicKey.toString();
            
            // Change button text to "Connected"
            this.innerHTML = '<i class="fas fa-check-circle me-2"></i>Connected';
            this.classList.remove('btn-primary');
            this.classList.add('btn-success');
            
            // Show disconnect button and hide connect button
            showConnectedState();
            
            // Fetch and display wallet balance
            await showWalletBalance();
            
            console.log('Connected to wallet:', walletAddress);
        } catch (err) {
            console.error('Connection failed:', err);
            alert('Failed to connect to Phantom Wallet. Please try again.');
        }
    } else {
        alert('Phantom Wallet is not installed. Please install it first.');
    }
});

// Disconnect wallet
document.getElementById('disconnectWallet').addEventListener('click', async function() {
    if (typeof window.solana !== 'undefined' && window.solana.isPhantom) {
        try {
            await window.solana.disconnect();
            showDisconnectedState();
            console.log('Disconnected from wallet');
        } catch (err) {
            console.error('Disconnection failed:', err);
            alert('Failed to disconnect from Phantom Wallet. Please try again.');
        }
    }
});

document.addEventListener("DOMContentLoaded", async () => {
    const connectBtn = document.getElementById("connectWallet");
    const disconnectBtn = document.getElementById("disconnectWallet");
    const balanceEl = document.getElementById("wallet_balance");


    function updateWalletButtons(connected) {
        if (connected) {
            connectBtn.style.display = "none";
            disconnectBtn.style.display = "inline-block";
            balanceEl.style.display = "block";
        } else {
            connectBtn.style.display = "inline-block";
            disconnectBtn.style.display = "none";
            balanceEl.style.display = "none";
        }
    }


    // Initial state check
    if (window.solana && window.solana.isPhantom) {
        updateWalletButtons(window.solana.isConnected);
    }


    // Listen for Phantom wallet events
    if (window.solana && window.solana.on) {
        window.solana.on("connect", async () => {
            updateWalletButtons(true);
            if (typeof window.showWalletBalance === "function") {
                balanceEl.textContent = 'Fetching balance…';
                await window.showWalletBalance();
            }
        });
        window.solana.on("disconnect", () => {
            updateWalletButtons(false);
        });
        window.solana.on("accountChanged", async () => {
            updateWalletButtons(window.solana.isConnected);
            if (window.solana.isConnected && typeof window.showWalletBalance === "function") {
                balanceEl.textContent = 'Fetching balance…';
                await window.showWalletBalance();
            }
        });
    }


    // Manual connect
    connectBtn.addEventListener("click", async () => {
        try {
            await window.TriumpayWallet.connectPhantomWallet();
            updateWalletButtons(true);
            if (typeof window.showWalletBalance === "function") {
                balanceEl.textContent = 'Fetching balance…';
                await window.showWalletBalance();
            }
        } catch (err) {
            alert("❌ Failed to connect: " + (err.message || err));
        }
    });


    // Manual disconnect
    disconnectBtn.addEventListener("click", async () => {
        try {
            await window.TriumpayWallet.disconnectPhantomWallet();
            updateWalletButtons(false);
        } catch (err) {
            alert("❌ Failed to disconnect: " + (err.message || err));
        }
    });
});

// Wait for libraries to be loaded before attaching event listeners
function initializeStakingPage() {
    // Check if required libraries are loaded
    if (typeof solanaWeb3 === 'undefined' || typeof splToken === 'undefined' || typeof window.solana === 'undefined') {
        console.log('Waiting for libraries to load...');
        setTimeout(initializeStakingPage, 100);
        return;
    }
    
    console.log('Libraries loaded successfully');
    
    document.getElementById('sendPayment').addEventListener('click', async function() {
        if (typeof window.solana !== 'undefined' && window.solana.isPhantom) {
            try {
                // Check if wallet is connected
                if (!window.solana.isConnected) {
                    alert('Please connect your Phantom Wallet first.');
                    return;
                }
                
                const amount_usd = parseFloat(document.getElementById('amount_usd').value);
                
                if (isNaN(amount_usd) || amount_usd <= 0) {
                    alert('Please enter a valid amount');
                    return;
                }
                
                // Convert USD to TRDX
                const trdxPrice = <?php echo get_trdx_price(); ?>;
                const amount_trdx = amount_usd / trdxPrice;
                
                // Get receiver address
                const receiverAddress = '<?php echo RECEIVER_WALLET_ADDRESS; ?>';
                
                // Show loading state
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
                this.disabled = true;
                
                try {
                    // Check if the libraries are loaded correctly
                    if (typeof solanaWeb3 === 'undefined' || typeof splToken === 'undefined') {
                        throw new Error('Solana Web3 or SPL Token library not found');
                    }
                    
                    // Create connection to Solana cluster using Alchemy RPC
                    const connection = new solanaWeb3.Connection(SOLANA_RPC_ENDPOINT);
                    
                    // TRDX token mint address
                    const tokenMintAddress = new solanaWeb3.PublicKey("7EV2VjMrdZuJLbdZ39279TbRqkW8zWFwbLTQeg5swSyK"); // TRDX mint
                    const receiverPubKey = new solanaWeb3.PublicKey(receiverAddress);
                    
                    const fromWallet = window.solana.publicKey;
                    const fromTokenAccount = await splToken.getAssociatedTokenAddress(tokenMintAddress, fromWallet);
                    const toTokenAccount = await splToken.getAssociatedTokenAddress(tokenMintAddress, receiverPubKey);
                    
                    console.log('Receiver public key:', receiverPubKey.toString());
                    console.log('To token account address:', toTokenAccount.toString());

                    // Check if receiver has an associated token account
                    const accountInfo = await connection.getAccountInfo(toTokenAccount);
                    console.log('Account info result:', accountInfo);
                    
                    // Create transaction
                    const tx = new solanaWeb3.Transaction();
                    
                    // If receiver doesn't have an ATA, we need to create it
                    if (!accountInfo) {
                        console.log('Receiver has no TRDX account (ATA missing). Creating associated token account...');
                        
                        // Create instruction to create associated token account
                        // Using the correct method from the SPL Token library
                        const createATAInstruction = splToken.createAssociatedTokenAccountInstruction(
                            fromWallet,  // payer
                            toTokenAccount,  // associatedToken
                            receiverPubKey,  // owner
                            tokenMintAddress  // mint
                        );
                        
                        tx.add(createATAInstruction);
                    }
                    
                    // Convert amount to token decimals (TRDX has 6 decimals)
                    const tokenAmount = Math.round(amount_trdx * 1000000);
                    
                    // Create transfer instruction
                    const transferInstruction = splToken.createTransferInstruction(
                        fromTokenAccount,
                        toTokenAccount,
                        fromWallet,
                        tokenAmount
                    );
                    
                    tx.add(transferInstruction);

                    tx.feePayer = fromWallet;
                    const { blockhash } = await connection.getLatestBlockhash();
                    tx.recentBlockhash = blockhash;

                    const signed = await window.solana.signTransaction(tx);
                    const sig = await connection.sendRawTransaction(signed.serialize());

                    // Confirm the transaction
                  //  await connection.confirmTransaction({
                  //     signature: sig,
                  //      blockhash: blockhash,
                   //     lastValidBlockHeight: (await connection.getBlockHeight()).valueOf()
                   // }, 'confirmed');

                    console.log('Transaction sent successfully with signature:', sig);
                    
                    // Check if we got a valid signature
                    if (!sig) {
                        throw new Error('Failed to get transaction signature from Phantom Wallet');
                    }
                    
                    // Set the real transaction hash
                    document.getElementById('tx_hash').value = sig;
                    
                    // Reset send payment button
                    this.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Send Payment';
                    
                    // Show success message with the transaction hash
                    alert('Payment sent successfully! Transaction hash: ' + sig);
                    
                    // Automatically submit the deposit form instead of just enabling the button
                    document.getElementById('depositForm').submit();
                } catch (err) {
                    console.error('Payment failed:', err);
                    console.error('Error stack:', err.stack);
                    // Reset send payment button
                    this.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Send Payment';
                    this.disabled = false;
                    
                    // Handle specific error cases
                    if (err.message.includes('User rejected the request')) {
                        alert('Transaction cancelled by user.');
                    } else {
                        alert('Failed to send payment. Please try again. Error: ' + err.message);
                    }
                }
            } catch (err) {
                console.error('Payment failed:', err);
                // Reset send payment button
                this.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Send Payment';
                this.disabled = false;
                
                // Handle specific error cases
                if (err.message.includes('User rejected the request')) {
                    alert('Transaction cancelled by user.');
                } else {
                    alert('Failed to send payment. Please try again. Error: ' + err.message);
                }
            }
        } else {
            alert('Phantom Wallet is not installed. Please install it first.');
        }
    });
}

// Initialize the staking page when the DOM is loaded
document.addEventListener('DOMContentLoaded', initializeStakingPage);

// Package selection and details display
document.getElementById('package_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const packageDetails = document.getElementById('package_details');
    const startButton = document.getElementById('start_staking_button');
    
    if (selectedOption.value) {
        const packageName = selectedOption.text.split(' ')[0];
        const minAmount = selectedOption.getAttribute('data-min');
        const maxAmount = selectedOption.getAttribute('data-max');
        const roi = selectedOption.getAttribute('data-roi');
        const term = selectedOption.getAttribute('data-term');
        
        // Update package details display
        document.getElementById('detail_package_name').textContent = packageName;
        document.getElementById('detail_investment_range').textContent = '$' + minAmount + ' - $' + maxAmount;
        document.getElementById('detail_roi').textContent = roi + '% daily';
        document.getElementById('detail_term').textContent = term + ' days';
        
        // Show package details
        packageDetails.style.display = 'block';
        
        // Update amount input constraints
        document.getElementById('staking_amount').min = minAmount;
        document.getElementById('staking_amount').max = maxAmount;
        document.getElementById('staking_amount').placeholder = 'Min: $' + minAmount + ', Max: $' + maxAmount;
        
        // Reset amount and disable button
        document.getElementById('staking_amount').value = '';
        startButton.disabled = true;
    } else {
        // Hide package details
        packageDetails.style.display = 'none';
        
        // Reset amount and disable button
        document.getElementById('staking_amount').value = '';
        startButton.disabled = true;
    }
});

// Validate staking amount and enable/disable button
document.getElementById('staking_amount').addEventListener('input', function() {
    const amount = parseFloat(this.value);
    const userBalance = <?php echo $user['balanceUSD']; ?>;
    const minAmount = parseFloat(this.min);
    const maxAmount = parseFloat(this.max);
    const startButton = document.getElementById('start_staking_button');
    
    if (isNaN(amount) || amount < minAmount || amount > maxAmount || amount > userBalance) {
        startButton.disabled = true;
        startButton.classList.remove('btn-success');
        startButton.classList.add('btn-secondary');
    } else {
        startButton.disabled = false;
        startButton.classList.remove('btn-secondary');
        startButton.classList.add('btn-success');
    }
});
</script>