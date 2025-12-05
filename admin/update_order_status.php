<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

header('Content-Type: application/json');

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$status = isset($_POST['status']) ? sanitizeInput($_POST['status']) : '';

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit();
}

$allowed_statuses = ['pending', 'confirmed', 'processing', 'completed', 'cancelled'];
if (!in_array($status, $allowed_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

if (update_order_status($order_id, $status)) {
    echo json_encode(['success' => true, 'message' => 'Order status updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update order status']);
}
?>