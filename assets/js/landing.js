// Triumpay Landing Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize any landing page specific functionality
    
    // Hamburger menu toggle
    const hamburgerMenu = document.querySelector('.hamburger-menu');
    const navLinks = document.querySelector('.nav-links');
    const hamburgerIcon = document.querySelector('.hamburger-menu i');
    
    if (hamburgerMenu && navLinks && hamburgerIcon) {
        hamburgerMenu.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            
            // Change icon based on menu state
            if (navLinks.classList.contains('active')) {
                hamburgerIcon.classList.remove('fa-bars');
                hamburgerIcon.classList.add('fa-times');
            } else {
                hamburgerIcon.classList.remove('fa-times');
                hamburgerIcon.classList.add('fa-bars');
            }
        });
    }
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const href = this.getAttribute('href');
            // Check if href is not just "#" and is a valid selector
            if (href && href !== '#' && href.startsWith('#')) {
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
            
            // Close mobile menu when clicking on a link
            if (navLinks && navLinks.classList.contains('active')) {
                navLinks.classList.remove('active');
            }
        });
    });
    
    // Animation for feature cards on scroll
    const featureCards = document.querySelectorAll('.feature-card');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = 1;
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });
    
    featureCards.forEach(card => {
        card.style.opacity = 0;
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observer.observe(card);
    });
    
    
    // Form validation for any future forms
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Add any form validation logic here if needed in the future
        });
    });
    
    // Button hover effects
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});

// Function to handle sign in button click
function handleSignIn() {
    window.location.href = '?page=login';
}

// Function to handle get started button click
function handleGetStarted() {
    window.location.href = '?page=register';
}