        </main>

        <!-- Bottom Navigation Menu -->
        <?php if (is_logged_in()): ?>
        <nav class="navbar fixed-bottom navbar-dark bg-dark">
            <div class="container">
                <div class="row w-100">
                    <div class="col text-center">
                        <a href="?page=dashboard" class="nav-link <?php echo (!isset($_GET['page']) || $_GET['page'] == 'dashboard') ? 'active' : ''; ?>">
                            <i class="fas fa-home fa-2x"></i>
                            <span class="d-block">Home</span>
                        </a>
                    </div>
                    <div class="col text-center">
                        <a href="?page=staking" class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'staking') ? 'active' : ''; ?>">
                            <i class="fas fa-coins fa-2x"></i>
                            <span class="d-block">Staking</span>
                        </a>
                    </div>
                    <div class="col text-center">
                        <a href="?page=ppob" class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'ppob') ? 'active' : ''; ?>">
                            <i class="fas fa-receipt fa-2x"></i>
                            <span class="d-block">PPOB</span>
                        </a>
                    </div>
                    <div class="col text-center">
                        <a href="?page=mining" class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'mining') ? 'active' : ''; ?>">
                            <i class="fas fa-hammer fa-2x"></i>
                            <span class="d-block">Mining</span>
                        </a>
                    </div>
                    <div class="col text-center">
                        <a href="?page=referral" class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'referral') ? 'active' : ''; ?>">
                            <i class="fas fa-users fa-2x"></i>
                            <span class="d-block">Referral</span>
                        </a>
                    </div>
                </div>
            </div>
        </nav>
        <?php endif; ?>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Phantom Wallet Integration -->
        <script src="/assets/js/libs/web3.iife.js?v=2.0"></script>
        <script src="/assets/js/libs/spl-token.iife.js?v=2.0"></script>
        <script src="/assets/js/wallet.js?v=1.0"></script>
    </body>
</html>