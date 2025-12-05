<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Check login
if (!is_logged_in()) {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: checkout.php');
    exit;
}

// Get form data
$full_name = sanitizeInput($_POST['full_name'] ?? '');
$email = sanitizeInput($_POST['email'] ?? '');
$phone = sanitizeInput($_POST['phone'] ?? '');
$address = sanitizeInput($_POST['address'] ?? '');
$payment_method = sanitizeInput($_POST['payment_method'] ?? 'cash');
$notes = sanitizeInput($_POST['notes'] ?? '');
$cart_data = json_decode($_POST['cart_data'] ?? '[]', true);

// Validate cart
if (empty($cart_data)) {
    header('Location: cart.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Get customer
$query = "SELECT customer_id FROM customers WHERE user_id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();

if (!$customer) {
    header('Location: checkout.php');
    exit;
}

$customer_id = $customer['customer_id'];

// Calculate totals
$subtotal = 0;
foreach ($cart_data as $item) {
    $subtotal += (intval($item['price']) * intval($item['quantity']));
}

$tax = intval($subtotal * 0.1);
$total = $subtotal + $tax;

try {
    $db->begin_transaction();
    
    // Create order
    $query = "INSERT INTO orders (customer_id, order_date, total_amount, status, payment_method, payment_status, notes) 
              VALUES (?, NOW(), ?, 'pending', ?, 'paid', ?)";
    $stmt = $db->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $db->error);
    }
    
    $stmt->bind_param('idss', $customer_id, $total, $payment_method, $notes);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $order_id = $db->insert_id;
    
    if ($order_id === 0) {
        throw new Exception("Failed to get order ID");
    }
    
    // Insert order details
    foreach ($cart_data as $item) {
        $product_id = intval($item['productId']);
        $quantity = intval($item['quantity']);
        $unit_price = intval($item['price']);
        $sub_total = $unit_price * $quantity;
        
        // Get product for stock check
        $product = get_product_by_id($product_id);
        if (!$product || $product['stock'] < $quantity) {
            throw new Exception("Insufficient stock for product: " . $item['name']);
        }
        
        $query = "INSERT INTO order_details (order_id, product_id, quantity, unit_price, sub_total) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $db->error);
        }
        
        $stmt->bind_param('iiiid', $order_id, $product_id, $quantity, $unit_price, $sub_total);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        // Update product stock
        $query = "UPDATE products SET stock = stock - ? WHERE product_id = ?";
        $stmt = $db->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $db->error);
        }
        
        $stmt->bind_param('ii', $quantity, $product_id);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
    }
    
    // Update customer info
    $query = "UPDATE customers SET full_name = ?, email = ?, phone_number = ?, address = ? WHERE customer_id = ?";
    $stmt = $db->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $db->error);
    }
    
    $stmt->bind_param('ssssi', $full_name, $email, $phone, $address, $customer_id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    // Update loyalty points
    $points = intval($total / 10000);
    $query = "UPDATE customers SET loyalty_points = loyalty_points + ? WHERE customer_id = ?";
    $stmt = $db->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $db->error);
    }
    
    $stmt->bind_param('ii', $points, $customer_id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $db->commit();
    
    // Clear session cart
    $_SESSION['cart'] = [];
    
    // Store order_id in session for receipt
    $_SESSION['last_order_id'] = $order_id;
    $_SESSION['order_success'] = true;
    
    // Redirect to payment page
    header('Location: payment.php?order_id=' . $order_id);
    exit;
    
} catch (Exception $e) {
    $db->rollback();
    $_SESSION['error_message'] = 'Failed to process order: ' . $e->getMessage();
    header('Location: checkout.php');
    exit;
}
?>