<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="./manifest.json">
    <link rel="icon" href="/assets/images/icon-192.png" type="image/x-icon">
    <meta name="theme-color" content="#00ffcc">
    <title>Triumpay App - Web3 Payment Solution</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="/assets/css/landing.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Content Security Policy -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; connect-src 'self' https://solana-mainnet.g.alchemy.com https://api.mainnet-beta.solana.com; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; img-src 'self' data: https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; font-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com;">
</head>
<body>
    <!-- Header Section -->
    <header class="landing-header">
        <nav class="navbar">
            <div class="logo-container">
                <img src="/assets/images/icon-192.png" alt="Logo" class="logo">
                <span class="logo-text">Triumpay</span>
            </div>
            
            <button class="hamburger-menu" aria-label="Toggle navigation menu">
                <i class="fas fa-bars"></i>
            </button>
            
            <ul class="nav-links">
                <li><a href="#features">Features</a></li>
                <li><a href="#how-it-works">How It Works</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
            
            <div class="auth-buttons">
                <a href="?page=login" class="btn btn-outline me-2">Sign In</a>
                <a href="?page=register" class="btn btn-primary">Get Started</a>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Next-Gen <span>Web3 Payment</span> Platform</h1>
            <p>Experience seamless crypto transactions, staking, and mining with our cutting-edge blockchain technology. Join thousands of users in the future of finance.</p>
            <div class="hero-buttons">
                <a href="?page=register" class="btn btn-primary">Get Started</a>
                <a href="#features" class="btn btn-outline">Learn More</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="features-header">
            <h2>Powerful Features</h2>
            <p>Discover the comprehensive suite of tools that make Triumpay the ultimate Web3 payment solution</p>
        </div>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <h3>Secure Wallet</h3>
                <p>Store, send, and receive cryptocurrencies with our military-grade security wallet system.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-piggy-bank"></i>
                </div>
                <h3>Staking Rewards</h3>
                <p>Earn passive income by staking your tokens with our high-yield staking program.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-microchip"></i>
                </div>
                <h3>Mining</h3>
                <p>Participate in our mining program to earn additional rewards through computational work.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <h3>Instant Swaps</h3>
                <p>Swap between different cryptocurrencies instantly with minimal fees.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h3>Payment Gateway</h3>
                <p>Accept crypto payments for your business with our easy-to-integrate payment gateway.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Bank-Grade Security</h3>
                <p>Rest easy with our multi-layered security system protecting your assets 24/7.</p>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works" id="how-it-works">
        <div class="features-header">
            <h2>How It Works</h2>
            <p>Get started with Triumpay in just a few simple steps</p>
        </div>
        
        <div class="steps">
            <div class="step">
                <div class="step-number">1</div>
                <h3>Create Account</h3>
                <p>Sign up and verify your identity to get started with our platform.</p>
            </div>
            
            <div class="step">
                <div class="step-number">2</div>
                <h3>Deposit Funds</h3>
                <p>Add crypto to your wallet through our secure deposit system.</p>
            </div>
            
            <div class="step">
                <div class="step-number">3</div>
                <h3>Start Earning</h3>
                <p>Begin staking, mining, or using our payment services to grow your assets.</p>
            </div>
            
            <div class="step">
                <div class="step-number">4</div>
                <h3>Withdraw Profits</h3>
                <p>Withdraw your earnings anytime with low fees and fast processing.</p>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="cta-content">
            <h2>Ready to Join the Future of Finance?</h2>
            <p>Sign up today and get started with our Web3 payment platform. Experience the power of blockchain technology.</p>
            <a href="?page=register" class="btn btn-primary">Create Free Account</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="landing-footer" id="contact">
        <div class="footer-content">
            <div class="footer-column">
                <h3>Triumpay</h3>
                <p>Web3 payment solution for the modern world. Secure, fast, and decentralized.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-telegram"></i></a>
                    <a href="#"><i class="fab fa-discord"></i></a>
                    <a href="#"><i class="fab fa-medium"></i></a>
                </div>
            </div>
            
            <div class="footer-column">
                <h3>Products</h3>
                <ul class="footer-links">
                    <li><a href="#">Wallet</a></li>
                    <li><a href="#">Staking</a></li>
                    <li><a href="#">Mining</a></li>
                    <li><a href="#">Payment Gateway</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3>Resources</h3>
                <ul class="footer-links">
                    <li><a href="#">Documentation</a></li>
                    <li><a href="#">API</a></li>
                    <li><a href="#">Blog</a></li>
                    <li><a href="#">Support</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3>Company</h3>
                <ul class="footer-links">
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Careers</a></li>
                    <li><a href="#">Contact</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                </ul>
            </div>
        </div>
        
        <div class="copyright">
            <p>&copy; 2025 Triumpay App. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="/assets/js/landing.js"></script>
</body>
</html>