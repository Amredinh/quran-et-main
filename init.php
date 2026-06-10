<?php
/**
 * Database Initialization Script
 * Run this once to set up the database tables.
 * Usage: php init.php
 * Or visit: /init.php in browser (will auto-delete after setup)
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

header('Content-Type: text/plain; charset=utf-8');

echo "Quran.et Database Initialization\n";
echo "================================\n\n";

try {
    Database::init();
    echo "✓ Database tables created successfully.\n";
    echo "✓ Default reciters seeded.\n";
    echo "✓ Default donation config seeded.\n";
    echo "✓ Default blog posts seeded.\n\n";
    echo "Setup complete! You can now use the website.\n";
    echo "Admin access key: " . ADMIN_SECRET_KEY . "\n";
    echo "\nFor security, delete this file (init.php) after setup.\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
    echo "Please check your database credentials in config.php\n";
    echo "DB_HOST: " . DB_HOST . "\n";
    echo "DB_NAME: " . DB_NAME . "\n";
    echo "DB_USER: " . DB_USER . "\n";
}
