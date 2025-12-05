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

// Verify order belongs to current user (if not admin)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
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
}

// Get order items
$query = "SELECT od.*, p.name as product_name, p.image_url
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?> - Kafe Latte</title>
    <link rel="stylesheet" href="../assets/css/receipt.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="receipt-container">
        <div class="receipt-header">
            <div class="brand-icon">
                <i class="fas fa-coffee"></i>
            </div>
            <h1>Kafe Latte</h1>
            <p class="receipt-subtitle">Thank you for your order!</p>
            <p class="store-info">Jl. Kopi Nikmat No. 123, Jakarta</p>
            <p class="store-info">Phone: (021) 1234-5678</p>
        </div>

        <div class="receipt-divider"></div>

        <div class="order-info-section">
            <div class="info-row">
                <span class="label">Order Number:</span>
                <span class="value order-number">#<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></span>
            </div>
            <div class="info-row">
                <span class="label">Date:</span>
                <span class="value"><?php echo date('d M Y, H:i', strtotime($order['order_date'])); ?></span>
            </div>
            <div class="info-row">
                <span class="label">Customer:</span>
                <span class="value"><?php echo htmlspecialchars($order['customer_name']); ?></span>
            </div>
            <div class="info-row">
                <span class="label">Payment Method:</span>
                <span class="value payment-method">
                    <i class="fas fa-<?php 
                        echo $order['payment_method'] === 'cash' ? 'money-bill-wave' : 
                            ($order['payment_method'] === 'card' ? 'credit-card' : 
                            ($order['payment_method'] === 'e-wallet' ? 'wallet' : 'university')); 
                    ?>"></i>
                    <?php echo strtoupper(str_replace(['_', '-'], ' ', $order['payment_method'])); ?>
                </span>
            </div>
            <div class="info-row">
                <span class="label">Status:</span>
                <span class="status-badge status-<?php echo $order['payment_status']; ?>">
                    <?php echo strtoupper($order['payment_status']); ?>
                </span>
            </div>
        </div>

        <div class="receipt-divider"></div>

        <div class="items-section">
            <h2>Order Items</h2>
            <?php foreach ($order_items as $item): ?>
                <div class="receipt-item">
                    <div class="item-header">
                        <span class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></span>
                        <span class="item-total"><?php echo format_rupiah($item['sub_total']); ?></span>
                    </div>
                    <div class="item-details">
                        <span><?php echo $item['quantity']; ?>x @ <?php echo format_rupiah($item['unit_price']); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="receipt-divider"></div>

        <div class="totals-section">
            <div class="total-row subtotal">
                <span>Subtotal:</span>
                <span><?php echo format_rupiah($subtotal); ?></span>
            </div>
            <div class="total-row tax">
                <span>Tax (10%):</span>
                <span><?php echo format_rupiah($tax); ?></span>
            </div>
            <div class="total-row grand-total">
                <span>Total:</span>
                <span><?php echo format_rupiah($total); ?></span>
            </div>
        </div>

        <?php if (!empty($order['notes'])): ?>
        <div class="receipt-divider"></div>
        <div class="notes-section">
            <h3>Notes:</h3>
            <p><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
        </div>
        <?php endif; ?>

        <div class="receipt-footer">
            <p class="thank-you">Thank you for choosing Kafe Latte!</p>
            <p class="footer-message">Have a great day! ☕</p>
            <p class="print-time">Printed: <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>

        <div class="action-buttons no-print">
            <button onclick="window.print()" class="btn btn-print">
                <i class="fas fa-print"></i>
                <span>Print</span>
            </button>
            <a href="history.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Back</span>
            </a>
            <a href="../index.php" class="btn btn-secondary">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
        </div>
    </div>
</body>
</html>