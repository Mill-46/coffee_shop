<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

// Get product from database
$product = get_product_by_id($product_id);

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

if (!isset($product['is_available']) || !$product['is_available']) {
    echo json_encode(['success' => false, 'message' => 'Product is not available']);
    exit;
}

// Initialize cart in session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Get current quantity in cart
$current_quantity = isset($_SESSION['cart'][$product_id]) ? intval($_SESSION['cart'][$product_id]) : 0;

// Check stock availability
if (($current_quantity + $quantity) > intval($product['stock'])) {
    $available = intval($product['stock']) - $current_quantity;
    echo json_encode([
        'success' => false,
        'message' => 'Insufficient stock. Available: ' . $available . ' items'
    ]);
    exit;
}

// Add to cart
if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id] += $quantity;
} else {
    $_SESSION['cart'][$product_id] = $quantity;
}

// Calculate total cart items
$cart_count = 0;
foreach ($_SESSION['cart'] as $qty) {
    $cart_count += intval($qty);
}

echo json_encode([
    'success' => true,
    'message' => $product['name'] . ' added to cart successfully',
    'cart_count' => $cart_count,
    'product_name' => $product['name']
]);
?>