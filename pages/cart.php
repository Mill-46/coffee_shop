<?php
session_start();
require_once '../includes/functions.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update') {
        $product_id = intval($_POST['product_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 0);
        
        if ($product_id > 0 && $quantity > 0) {
            $_SESSION['cart'][$product_id] = $quantity;
        } else if ($quantity === 0) {
            unset($_SESSION['cart'][$product_id]);
        }
        
        header('Location: cart.php');
        exit;
    } else if ($action === 'remove') {
        $product_id = intval($_POST['product_id'] ?? 0);
        if ($product_id > 0) {
            unset($_SESSION['cart'][$product_id]);
        }
        header('Location: cart.php');
        exit;
    } else if ($action === 'clear') {
        $_SESSION['cart'] = [];
        header('Location: cart.php');
        exit;
    }
}

// Calculate cart
$cart_items = [];
$cart_total = 0;
$cart_count = 0;

if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $quantity = intval($quantity);
        
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$product_id]);
            continue;
        }
        
        $product = get_product_by_id($product_id);
        
        if ($product) {
            $subtotal = $product['price'] * $quantity;
            $cart_items[] = [
                'product_id' => $product_id,
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $quantity,
                'image_url' => $product['image_url'],
                'description' => $product['description'],
                'stock' => $product['stock'],
                'subtotal' => $subtotal
            ];
            $cart_total += $subtotal;
            $cart_count += $quantity;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Kafe Latte</title>
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="../assets/css/cart.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
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
                    <li><a href="cart.php" class="active">Cart</a></li>
                    <?php if (is_logged_in()): ?>
                        <li><a href="history.php">Orders</a></li>
                        <li><a href="../auth/logout.php" class="btn-logout">Logout</a></li>
                    <?php else: ?>
                        <li><a href="../auth/login.php" class="btn-login">Login</a></li>
                    <?php endif; ?>
                </ul>
                <button class="hamburger" aria-label="Menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </nav>
        </div>
    </header>

    <section class="cart-section">
        <div class="container">
            <div class="page-header">
                <h1><i class="fas fa-shopping-bag"></i> Shopping Cart</h1>
                <p><?php echo $cart_count; ?> items in your cart</p>
            </div>

            <?php if (empty($cart_items)): ?>
                <div class="empty-cart-container">
                    <div class="empty-cart-illustration">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <h2>Your Cart is Empty</h2>
                    <p>Looks like you haven't added any items to your cart yet.</p>
                    <p class="empty-cart-subtitle">Let's explore our menu!</p>
                    <a href="../index.php#menu" class="btn-start-shopping">
                        <i class="fas fa-coffee"></i>
                        <span>Start Shopping</span>
                    </a>
                    <div class="empty-cart-features">
                        <div class="feature-item">
                            <i class="fas fa-star"></i>
                            <span>Premium Quality</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-shipping-fast"></i>
                            <span>Fast Delivery</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-percentage"></i>
                            <span>Great Offers</span>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="cart-layout">
                    <!-- Cart Items -->
                    <div class="cart-items">
                        <?php if ($cart_count > 1): ?>
                            <div class="cart-actions">
                                <button type="button" class="btn-clear" onclick="showClearCartModal()">
                                    <i class="fas fa-trash-alt"></i>
                                    <span>Clear Cart</span>
                                </button>
                            </div>
                        <?php endif; ?>

                        <?php foreach ($cart_items as $index => $item): ?>
                            <article class="cart-item" style="animation-delay: <?php echo $index * 0.05; ?>s;">
                                <div class="item-image">
                                    <img src="<?php echo htmlspecialchars($item['image_url'] ?: 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=400&q=80'); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                
                                <div class="item-details">
                                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <p class="item-price"><?php echo format_rupiah($item['price']); ?></p>
                                    <p class="item-description"><?php echo htmlspecialchars($item['description']); ?></p>
                                </div>
                                
                                <div class="item-actions">
                                    <form method="POST" class="quantity-form">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                        
                                        <button type="button" class="qty-btn minus" onclick="changeQuantity(this, -1)">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        
                                        <input type="number" 
                                               name="quantity" 
                                               value="<?php echo $item['quantity']; ?>" 
                                               min="1" 
                                               max="<?php echo $item['stock']; ?>" 
                                               class="qty-input" 
                                               readonly>
                                        
                                        <button type="button" class="qty-btn plus" onclick="changeQuantity(this, 1)">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </form>
                                    
                                    <div class="item-subtotal">
                                        <p class="subtotal-label">Subtotal</p>
                                        <p class="subtotal-price"><?php echo format_rupiah($item['subtotal']); ?></p>
                                    </div>
                                    
                                    <button type="button" class="btn-remove" onclick="showRemoveModal(<?php echo $item['product_id']; ?>, '<?php echo htmlspecialchars(addslashes($item['name'])); ?>')" title="Remove item">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <!-- Cart Summary -->
                    <aside class="cart-summary">
                        <h3>Order Summary</h3>
                        
                        <div class="summary-row">
                            <span>Subtotal (<?php echo $cart_count; ?> items)</span>
                            <span><?php echo format_rupiah($cart_total); ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Service Fee</span>
                            <span><?php echo format_rupiah(5000); ?></span>
                        </div>
                        
                        <div class="summary-divider"></div>
                        
                        <div class="summary-row total">
                            <span>Total</span>
                            <span><?php echo format_rupiah($cart_total + 5000); ?></span>
                        </div>
                        
                        <?php if (is_logged_in()): ?>
                            <button type="button" class="btn-checkout" onclick="proceedToCheckout()">
                                <i class="fas fa-lock"></i>
                                <span>Proceed to Checkout</span>
                            </button>
                        <?php else: ?>
                            <a href="../auth/login.php" class="btn-checkout">
                                <i class="fas fa-sign-in-alt"></i>
                                <span>Login to Checkout</span>
                            </a>
                        <?php endif; ?>
                        
                        <a href="../index.php#menu" class="btn-continue">
                            <i class="fas fa-arrow-left"></i>
                            <span>Continue Shopping</span>
                        </a>
                        
                        <div class="trust-badges">
                            <div class="trust-badge">
                                <i class="fas fa-shield-alt"></i>
                                <span>Secure Payment</span>
                            </div>
                            <div class="trust-badge">
                                <i class="fas fa-truck"></i>
                                <span>Fast Delivery</span>
                            </div>
                            <div class="trust-badge">
                                <i class="fas fa-headset"></i>
                                <span>24/7 Support</span>
                            </div>
                        </div>
                    </aside>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Modal - Remove Item -->
    <div id="removeModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fas fa-exclamation-circle"></i>
                <h3>Remove Item</h3>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to remove <strong id="itemName"></strong> from your cart?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('removeModal')">Cancel</button>
                <form method="POST" id="removeForm" style="display: inline;">
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="product_id" id="removeProductId">
                    <button type="submit" class="btn-confirm">Yes, Remove</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal - Clear Cart -->
    <div id="clearCartModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fas fa-trash-alt"></i>
                <h3>Clear Cart</h3>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to clear your entire shopping cart?</p>
                <p class="warning-text">All items will be removed and cannot be recovered.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('clearCartModal')">Cancel</button>
                <form method="POST" id="clearForm" style="display: inline;">
                    <input type="hidden" name="action" value="clear">
                    <button type="submit" class="btn-confirm">Yes, Clear</button>
                </form>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2024 Kafe Latte. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="../assets/js/script.js"></script>
    <script>
        function proceedToCheckout() {
            const cartItems = <?php echo json_encode($cart_items); ?>;
            const cart = cartItems.map(item => ({
                productId: item.product_id,
                name: item.name,
                price: item.price,
                quantity: item.quantity
            }));
            
            localStorage.setItem('cart', JSON.stringify(cart));
            window.location.href = 'checkout.php';
        }

        function changeQuantity(button, change) {
            const form = button.closest('.quantity-form');
            const input = form.querySelector('.qty-input');
            const currentValue = parseInt(input.value);
            const max = parseInt(input.max);
            const min = parseInt(input.min);
            
            let newValue = currentValue + change;
            
            if (newValue < min) newValue = min;
            if (newValue > max) newValue = max;
            
            if (newValue !== currentValue) {
                input.value = newValue;
                form.submit();
            }
        }

        function showRemoveModal(productId, itemName) {
            document.getElementById('removeProductId').value = productId;
            document.getElementById('itemName').textContent = itemName;
            document.getElementById('removeModal').style.display = 'flex';
        }

        function showClearCartModal() {
            document.getElementById('clearCartModal').style.display = 'flex';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                document.querySelectorAll('.modal').forEach(modal => {
                    modal.style.display = 'none';
                });
            }
        });
    </script>
</body>
</html>