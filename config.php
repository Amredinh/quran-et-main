<?php
// Database configuration - UPDATE THESE with your cPanel credentials
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'quran_et');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Admin secret key - CHANGE THIS in production
define('ADMIN_SECRET_KEY', getenv('ADMIN_SECRET_KEY') ?: 'admin123');

// Site URL (for SEO canonicals)
define('SITE_URL', getenv('SITE_URL') ?: '');

// Paths
define('DATA_DIR', __DIR__ . '/data');
define('ARABIC_XML_PATH', DATA_DIR . '/arabic.xml');
define('AMHARIC_XML_PATH', DATA_DIR . '/amharic.xml');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Session for admin
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
