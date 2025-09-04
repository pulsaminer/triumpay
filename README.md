# Triumpay App

A PHP-based Web3 mobile application with Phantom Wallet integration.

## Features

- Phantom Wallet integration for Solana blockchain transactions
- Staking system with multiple packages (Silver, Gold, Platinum)
- Mining rewards system
- Referral program
- Responsive mobile-first design
- Secure authentication system
- Clean URL structure

## Project Structure

```
trium-X/
├── assets/
│   ├── css/
│   ├── images/
│   └── js/
├── config/
│   └── config.php
├── database/
│   └── schema.sql
├── includes/
│   ├── functions.php
│   ├── header.php
│   └── footer.php
├── pages/
│   ├── dashboard.php
│   ├── login.php
│   ├── mining.php
│   ├── ppob.php
│   ├── referral.php
│   └── staking.php
├── src/
│   ├── buffer-shim.js
│   ├── spl-entry.js
│   └── web3-entry.js
├── .htaccess
├── index.php
├── logout.php
├── manifest.json
├── package.json
├── GITHUB_SETUP.md
└── README.md
```

## Installation

1. Clone or download the project files to your web server directory
2. Create a MySQL database named `triumpay_db`
3. Import the database schema from `database/schema.sql`
4. Update the database configuration in `config/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_database_user');
   define('DB_PASS', 'your_database_password');
   define('DB_NAME', 'triumpay_db');
   ```
5. Configure the Mailketing API key in `config/config.php`:
   ```php
   define('MAILKETING_API_KEY', 'your_mailketing_api_key_here');
   ```
6. Set up your web server to point to the `trium-X` directory

## Configuration

### Database Settings
Update the database connection settings in `config/config.php`:
- DB_HOST: Database host (default: localhost)
- DB_USER: Database username (default: root)
- DB_PASS: Database password (default: empty)
- DB_NAME: Database name (default: triumpay_db)

### Mailketing API
To enable welcome emails, configure your Mailketing API key:
- MAILKETING_API_KEY: Your Mailketing API key
- MAILKETING_API_URL: API endpoint (default: https://mailketing.co.id/api/send)

### Security Settings
- SECRET_KEY: Used for CSRF protection
- SESSION_TIMEOUT: Session expiration time in seconds (default: 3600)

### Staking Configuration
- RECEIVER_WALLET_ADDRESS: Wallet address for deposits
- MIN_DEPOSIT: Minimum deposit amount (default: 10)
- MAX_DEPOSIT: Maximum deposit amount (default: 1000)

## Usage

1. Access the application through your web browser
2. Register through a referral link (e.g., `https://triumpay.app/ref/username`)
3. Connect your Phantom Wallet during registration
4. Deposit TRDX tokens to start staking
5. Mine daily for rewards
6. Share your referral link to earn commissions

## Security Features

- CSRF protection tokens
- Input sanitization
- Secure session handling
- Rate limiting
- Login attempt throttling

## Technologies Used

- PHP 7.4+
- MySQL
- Bootstrap 5
- Font Awesome
- Phantom Wallet integration (Solana blockchain)

## GitHub Repository Setup

For instructions on setting up this project as a GitHub repository, please refer to [GITHUB_SETUP.md](GITHUB_SETUP.md).

## License

This project is proprietary and confidential. All rights reserved.

## Support

For support, please contact the development team.