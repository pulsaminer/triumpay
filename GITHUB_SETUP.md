# GitHub Repository Setup Guide for TriumPay

This guide will help you set up your TriumPay project as a GitHub repository.

## Prerequisites

1. Git installed on your system
2. A GitHub account
3. This project folder ready locally

## Step 1: Install Git (if not already installed)

### Windows:
- Download Git from https://git-scm.com/download/win
- Run the installer with default settings

### macOS:
```bash
# Using Homebrew
brew install git

# Or download from https://git-scm.com/download/mac
```

### Linux (Ubuntu/Debian):
```bash
sudo apt update
sudo apt install git
```

## Step 2: Configure Git

Open a terminal/command prompt and configure your Git identity:

```bash
git config --global user.name "Your Name"
git config --global user.email "your.email@example.com"
```

## Step 3: Initialize the Repository Locally

Navigate to your project directory and initialize Git:

```bash
cd trium-X
git init
git add .
git commit -m "Initial commit: TriumPay Web3 Application"
```

## Step 4: Create GitHub Repository

1. Go to https://github.com
2. Click "New" repository button
3. Name your repository (e.g., "trium-X" or "triumpay")
4. Choose visibility (Public or Private)
5. **Do NOT initialize with a README**, .gitignore, or license
6. Click "Create repository"

## Step 5: Push to GitHub

After creating the repository on GitHub, you'll get instructions. Follow the "…or push an existing repository from the command line" section:

```bash
git remote add origin https://github.com/yourusername/repository-name.git
git branch -M main
git push -u origin main
```

## Step 6: Verify Your Repository

Visit your GitHub repository URL to verify that all files have been uploaded correctly.

## Additional Recommendations

### 1. Add a License File
Consider adding a LICENSE file to your repository:
- For open-source projects, choose an appropriate license (MIT, Apache 2.0, etc.)
- For proprietary software, you might want to add a custom license

### 2. Protect Sensitive Information
Before pushing to GitHub, ensure you've:
- Added `config/config.php` to `.gitignore` (already done)
- Removed any API keys, passwords, or sensitive data from code
- Used environment variables for sensitive configuration

### 3. Set Up Branch Protection
In your GitHub repository settings:
- Go to Settings > Branches
- Add branch protection rules for your main branch
- Require pull request reviews before merging

### 4. Add GitHub Actions (Optional)
For automated testing and deployment, consider adding GitHub Actions:
- Create `.github/workflows` directory
- Add workflow files for CI/CD

### 5. Add Contributing Guidelines
Create `CONTRIBUTING.md` to guide potential contributors.

### 6. Add Issue Templates
Create `.github/ISSUE_TEMPLATE` directory with templates for bug reports and feature requests.

## Repository Structure

Your repository will contain:

```
trium-X/
├── assets/           # CSS, JavaScript, and image files
├── config/           # Configuration files
├── database/         # Database schema and initialization scripts
├── includes/         # PHP include files
├── pages/            # Application pages
├── src/              # Source files for Web3/Solana integration
├── .gitignore        # Git ignore file
├── .htaccess         # Apache configuration
├── index.php         # Main entry point
├── logout.php        # Logout functionality
├── manifest.json     # Web app manifest
├── package.json      # Node.js dependencies
├── README.md         # Project documentation
└── GITHUB_SETUP.md   # This file
```

## Security Notes

1. Never commit sensitive information like:
   - Database credentials
   - API keys
   - Private keys
   - Passwords

2. The `.gitignore` file is already configured to prevent committing sensitive files.

3. Always review your commits before pushing to ensure no sensitive data is included.

## Support

For any issues with setting up the repository, refer to:
- GitHub Docs: https://docs.github.com
- Git Docs: https://git-scm.com/doc