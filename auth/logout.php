<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Include database connection
require_once '../config/database.php';
require_once '../includes/functions.php';

// Update last_login if user is logged in
if (isset($_SESSION['user_id'])) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        // Using mysqli prepared statement
        $query = "UPDATE users SET last_login = NOW() WHERE user_id = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt) {
            $stmt->bind_param('i', $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();
        }
    } catch (Exception $e) {
        // Log error but continue with logout
        error_log("Logout error: " . $e->getMessage());
    }
}

// Destroy all session data
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to home page
header("Location: ../index.php?logout=success");
exit();
?>