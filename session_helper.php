<?php
// MOCK SESSION HELPER FOR LOCAL CSRF LAB
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Automatically authenticate a mock victim user "alice" so the labs work out-of-the-box
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = 'alice';
    $_SESSION['email'] = 'alice@securesite.local';
    $_SESSION['balance'] = 1000.00; // For transfer-based CSRF scenarios
}

// Helper to reset the session to initial state
if (isset($_GET['reset_session'])) {
    $_SESSION['user'] = 'alice';
    $_SESSION['email'] = 'alice@securesite.local';
    $_SESSION['balance'] = 1000.00;
    header("Location: index.php");
    exit();
}

$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
// Calculate dynamic lab root directory (supports both direct hosting or subdirectories)
$lab_root = $base_url . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
?>