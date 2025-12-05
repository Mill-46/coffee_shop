<?php
session_start();
require_once '../includes/functions.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Check login
if (!is_logged_in()) {
    header('Location: ../auth/login.php');
    exit;
}

$customer = get_customer_by_user_id($_SESSION['user_id']);

if (!$customer) {
    header('Location: ../index.php');
    exit;
}

$orders = get_customer_orders($customer['customer_id']);
$cart_count = get_cart_count();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - Kafe Latte</title>
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="../assets/css/history.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header Navigation -->
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <a href="../index.php" class="logo">
                    <i class="fas fa-coffee"></i>
                    <span>KAFE LATTE</span>
                </a>
                
                <ul class="nav-menu">
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="../index.php#menu">Menu</a></li>
                    <li><a href="../index.php#about">About</a></li>
                    <li>
                        <a href="cart.php" class="cart-link">
                            <i class="fas fa-shopping-bag"></i>
                            <span class="cart-badge"><?php echo $cart_count; ?></span>
                        </a>
                    </li>
                    <li><a href="history.php" class="active">Orders</a></li>
                    <li><a href="../auth/logout.php" class="btn-logout">Logout</a></li>
                </ul>
                
                <button class="hamburger" aria-label="Menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </nav>
        </div>
    </header>

    <section class="history-section">
        <div class="container">
            <div class="page-header">
                <h1><i class="fas fa-history"></i> Order History</h1>
                <p>View all your orders</p>
            </div>

            <?php if (empty($orders)): ?>
                <div class="empty-history">
                    <i class="fas fa-clipboard-list"></i>
                    <h2>No Orders Yet</h2>
                    <p>You haven't placed any orders yet</p>
                    <a href="../index.php#menu" class="btn-primary">
                        <i class="fas fa-shopping-bag"></i> Start Shopping
                    </a>
                </div>
            <?php else: ?>
                <div class="orders-list">
                    <?php foreach ($orders as $order): 
                        $order_items = get_order_details($order['order_id']);
                        $item_count = count($order_items);
                    ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div class="order-id">
                                    <h3>Order #<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></h3>
                                    <span class="order-date">
                                        <i class="fas fa-calendar"></i>
                                        <?php echo format_datetime($order['order_date']); ?>
                                    </span>
                                </div>
                                <div class="order-status">
                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                    <span class="payment-badge payment-<?php echo $order['payment_status']; ?>">
                                        <?php echo ucfirst($order['payment_status']); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="order-items">
                                <?php 
                                $display_items = array_slice($order_items, 0, 3);
                                foreach ($display_items as $item): 
                                ?>
                                    <div class="order-item-row">
                                        <img src="<?php echo htmlspecialchars($item['image_url'] ?: 'https://via.placeholder.com/60'); ?>" 
                                             alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                        <div class="item-info">
                                            <span class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></span>
                                            <span class="item-qty">x<?php echo $item['quantity']; ?></span>
                                        </div>
                                        <span class="item-price"><?php echo format_rupiah($item['sub_total']); ?></span>
                                    </div>
                                <?php endforeach; ?>
                                
                                <?php if ($item_count > 3): ?>
                                    <div class="more-items">
                                        <i class="fas fa-ellipsis-h"></i>
                                        <?php echo ($item_count - 3); ?> more items
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="order-footer">
                                <div class="order-total">
                                    <span>Total Payment:</span>
                                    <strong><?php echo format_rupiah($order['total_amount']); ?></strong>
                                </div>
                                <div class="order-actions">
                                    <a href="receipt.php?order_id=<?php echo $order['order_id']; ?>" class="btn-view">
                                        <i class="fas fa-receipt"></i> View Receipt
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h3>
                        <i class="fas fa-coffee"></i>
                        KAFE LATTE
                    </h3>
                    <p>Premium coffee for your best moments</p>
                    <div class="social-links">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                
                <div class="footer-col">
                    <h4>Hours</h4>
                    <p>Monday - Friday: 07:00 - 22:00</p>
                    <p>Saturday - Sunday: 08:00 - 23:00</p>
                </div>
                
                <div class="footer-col">
                    <h4>Contact</h4>
                    <p><i class="fas fa-map-marker-alt"></i> Depok, West Java, ID</p>
                    <p><i class="fas fa-phone"></i> (021) 1234-5678</p>
                    <p><i class="fas fa-envelope"></i> hello@kafelatte.com</p>
                </div>
                
                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="../index.php">Home</a></li>
                        <li><a href="../index.php#menu">Menu</a></li>
                        <li><a href="../index.php#about">About</a></li>
                        <li><a href="cart.php">Cart</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2024 Kafe Latte. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="../assets/js/script.js"></script>
</body>
</html>