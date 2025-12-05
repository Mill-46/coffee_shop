<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;

if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Verify order belongs to user
$query = "SELECT o.order_id FROM orders o
          JOIN customers c ON o.customer_id = c.customer_id
          WHERE o.order_id = ? AND c.user_id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param('ii', $order_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}

try {
    // Update payment status to paid
    $query = "UPDATE orders SET payment_status = 'paid' WHERE order_id = ?";
    $stmt = $db->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $db->error);
    }
    
    $stmt->bind_param('i', $order_id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    echo json_encode(['success' => true, 'message' => 'Payment status updated']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>