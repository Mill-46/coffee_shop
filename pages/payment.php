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

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if (!$order_id) {
    header('Location: history.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Get order with customer details
$query = "SELECT o.*, c.full_name as customer_name, c.email as customer_email, 
                 c.phone_number, c.address
          FROM orders o
          JOIN customers c ON o.customer_id = c.customer_id
          WHERE o.order_id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param('i', $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    header('Location: history.php');
    exit;
}

// Verify order belongs to current user
$query = "SELECT customer_id FROM customers WHERE user_id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();

if (!$customer || $order['customer_id'] != $customer['customer_id']) {
    header('Location: history.php');
    exit;
}

// Get order items
$query = "SELECT od.*, p.name as product_name
          FROM order_details od
          JOIN products p ON od.product_id = p.product_id
          WHERE od.order_id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param('i', $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order_items = $result->fetch_all(MYSQLI_ASSOC);

$subtotal = $order['total_amount'];
$tax = intval($subtotal * 0.1);
$total = $subtotal + $tax;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Process - Kafe Latte</title>
    <link rel="stylesheet" href="../assets/css/payment.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="payment-container">
        <div class="payment-box">
            <div class="payment-icon">
                <i class="fas fa-credit-card"></i>
            </div>
            <h1>Payment Process</h1>
            <p>Please wait while we process your order...</p>
            
            <div class="payment-method-display">
                <i class="fas fa-<?php 
                    echo $order['payment_method'] === 'cash' ? 'money-bill-wave' : 
                        ($order['payment_method'] === 'card' ? 'credit-card' : 
                        ($order['payment_method'] === 'e-wallet' ? 'wallet' : 'university')); 
                ?>"></i>
                <span><?php echo strtoupper(str_replace(['_', '-'], ' ', $order['payment_method'])); ?></span>
            </div>
            
            <div class="payment-details">
                <div class="row">
                    <span>Order ID:</span>
                    <strong>#<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></strong>
                </div>
                <div class="row">
                    <span>Subtotal:</span>
                    <span>Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></span>
                </div>
                <div class="row">
                    <span>Tax (10%):</span>
                    <span>Rp <?php echo number_format($tax, 0, ',', '.'); ?></span>
                </div>
                <div class="row total">
                    <span>Total:</span>
                    <span>Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
                </div>
            </div>
            
            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>
            
            <p class="secure-text">
                <i class="fas fa-lock"></i> Your payment is secure & encrypted
            </p>
        </div>
    </div>

    <script>
        // Simulate payment processing with 3 second delay
        setTimeout(() => {
            // Clear cart from localStorage
            localStorage.removeItem('cart');
            
            // Update order payment status
            fetch('update_payment_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'order_id=<?php echo $order_id; ?>'
            })
            .then(response => response.json())
            .then(data => {
                // Redirect to receipt regardless of update result
                window.location.href = 'receipt.php?order_id=<?php echo $order_id; ?>';
            })
            .catch(error => {
                console.error('Error:', error);
                // Still redirect to receipt
                window.location.href = 'receipt.php?order_id=<?php echo $order_id; ?>';
            });
        }, 3000);
    </script>
</body>
</html>