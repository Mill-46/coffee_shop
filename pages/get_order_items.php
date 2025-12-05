<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

header('Content-Type: application/json');

requireLogin();

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Verify order belongs to user - Using mysqli
$query = "SELECT o.order_id FROM orders o
          JOIN customers c ON o.customer_id = c.customer_id
          WHERE o.order_id = ? AND c.user_id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param('ii', $order_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit();
}

// Get order items
$query = "SELECT od.product_id, od.quantity, od.unit_price, od.sub_total,
                 p.name as product_name, p.image_url
          FROM order_details od
          JOIN products p ON od.product_id = p.product_id
          WHERE od.order_id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param('i', $order_id);
$stmt->execute();
$result = $stmt->get_result();
$items = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode([
    'success' => true,
    'items' => $items
]);
?>