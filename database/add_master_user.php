<?php
// Script to add master user to the database
require_once __DIR__ . '/../config/config.php';

try {
    // Check if master user already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute(['master']);
    
    if ($stmt->fetch()) {
        echo "Master user already exists in the database.\n";
    } else {
        // Hash the password
        $hashed_password = password_hash('123456', PASSWORD_DEFAULT);
        
        // Insert master user
        $stmt = $pdo->prepare("INSERT INTO users (username, fullname, email, phone, password, wallet_address, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            'master',
            'Master User',
            'master@triumpay.app',
            '0000000000',
            $hashed_password,
            '74KEB2sE9jANeFXyqqkLDUxdwnMscCVj8HSUHNFb8c4h',
            1
        ]);
        
        echo "Master user successfully added to the database.\n";
        echo "Username: master\n";
        echo "Password: 123456\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>