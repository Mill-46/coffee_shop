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

$database = new Database();
$db = $database->getConnection();

// Get or create customer
$query = "SELECT * FROM customers WHERE user_id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();

if (!$customer) {
    $query = "INSERT INTO customers (user_id, full_name, email, phone_number, address, registration_date) 
              VALUES (?, ?, ?, '', '', NOW())";
    $stmt = $db->prepare($query);
    $user_name = $_SESSION['user_name'] ?? 'Guest';
    $user_email = $_SESSION['user_email'] ?? '';
    $stmt->bind_param('iss', $_SESSION['user_id'], $user_name, $user_email);
    $stmt->execute();
    
    $query = "SELECT * FROM customers WHERE user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Kafe Latte</title>
    <link rel="stylesheet" href="../assets/css/checkout.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
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
        <li><a href="cart.php">Cart</a></li>
        <li><a href="history.php">Orders</a></li>
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

    <section class="checkout-section">
        <div class="container">
            <div class="page-header">
                <h1><i class="fas fa-credit-card"></i> Checkout</h1>
                <p>Complete your order information</p>
            </div>

            <div class="checkout-content">
                <div class="checkout-form-wrapper">
                    <form id="checkoutForm" method="POST" action="process_payment.php">
                        <input type="hidden" name="cart_data" id="cartData">
                        
                        <div class="form-section">
                            <div class="section-header">
                                <i class="fas fa-user"></i>
                                <h2>Customer Information</h2>
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="full_name">Full Name *</label>
                                    <input type="text" id="full_name" name="full_name" 
                                           value="<?php echo htmlspecialchars($customer['full_name']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email *</label>
                                    <input type="email" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($customer['email']); ?>" required>
                                </div>
                                <div class="form-group full-width">
                                    <label for="phone">Phone Number *</label>
                                    <input type="tel" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($customer['phone_number'] ?: ''); ?>" 
                                           placeholder="08xxxxxxxxxx" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="section-header">
                                <i class="fas fa-map-marker-alt"></i>
                                <h2>Delivery Address</h2>
                            </div>
                            <div class="form-grid">
                                <div class="form-group full-width">
                                    <label for="address">Full Address *</label>
                                    <textarea id="address" name="address" rows="3" 
                                              placeholder="Enter your complete address..." required><?php echo htmlspecialchars($customer['address'] ?: ''); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="section-header">
                                <i class="fas fa-credit-card"></i>
                                <h2>Payment Method</h2>
                            </div>
                            <div class="payment-methods">
                                <div class="payment-method">
                                    <input type="radio" name="payment_method" id="cash" value="cash" checked>
                                    <label for="cash">
                                        <div class="payment-icon">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </div>
                                        <span class="payment-name">Cash</span>
                                        <span class="payment-desc">Pay on delivery</span>
                                    </label>
                                </div>
                                <div class="payment-method">
                                    <input type="radio" name="payment_method" id="card" value="card">
                                    <label for="card">
                                        <div class="payment-icon">
                                            <i class="fas fa-credit-card"></i>
                                        </div>
                                        <span class="payment-name">Card</span>
                                        <span class="payment-desc">Debit / Credit Card</span>
                                    </label>
                                </div>
                                <div class="payment-method">
                                    <input type="radio" name="payment_method" id="ewallet" value="e-wallet">
                                    <label for="ewallet">
                                        <div class="payment-icon">
                                            <i class="fas fa-wallet"></i>
                                        </div>
                                        <span class="payment-name">E-Wallet</span>
                                        <span class="payment-desc">GoPay, OVO, Dana</span>
                                    </label>
                                </div>
                                <div class="payment-method">
                                    <input type="radio" name="payment_method" id="bank" value="bank_transfer">
                                    <label for="bank">
                                        <div class="payment-icon">
                                            <i class="fas fa-university"></i>
                                        </div>
                                        <span class="payment-name">Transfer</span>
                                        <span class="payment-desc">Bank Transfer</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="section-header">
                                <i class="fas fa-sticky-note"></i>
                                <h2>Order Notes</h2>
                            </div>
                            <div class="form-grid">
                                <div class="form-group full-width">
                                    <label for="notes">Special Instructions (Optional)</label>
                                    <textarea id="notes" name="notes" rows="3" 
                                              placeholder="Example: Less sugar, no ice, etc..."></textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="order-summary-box">
                    <h2>Order Summary</h2>
                    <div id="orderItems" class="order-items-list"></div>
                    
                    <div class="summary-divider"></div>
                    
                    <div class="summary-calculations">
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span id="subtotal">Rp 0</span>
                        </div>
                        <div class="summary-row">
                            <span>Tax (10%)</span>
                            <span id="tax">Rp 0</span>
                        </div>
                        <div class="summary-row total">
                            <span>Total</span>
                            <span id="total">Rp 0</span>
                        </div>
                    </div>

                    <button type="submit" form="checkoutForm" class="btn-place-order">
                        <i class="fas fa-check-circle"></i>
                        <span>Place Order</span>
                    </button>

                    <div class="secure-badge">
                        <i class="fas fa-lock"></i>
                        <span>100% Secure Payment</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2024 Kafe Latte. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        function formatRupiah(number) {
            return 'Rp ' + number.toLocaleString('id-ID');
        }

        function displayOrderSummary() {
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            
            if (cart.length === 0) {
                alert('Your cart is empty!');
                window.location.href = 'cart.php';
                return;
            }
            
            let itemsHtml = '';
            let subtotal = 0;
            
            cart.forEach(item => {
                const itemTotal = item.price * item.quantity;
                subtotal += itemTotal;
                
                itemsHtml += `
                    <div class="order-item">
                        <div class="item-details">
                            <h4>${item.name}</h4>
                            <p class="item-quantity">x${item.quantity}</p>
                        </div>
                        <div class="item-price">${formatRupiah(itemTotal)}</div>
                    </div>
                `;
            });
            
            const tax = Math.round(subtotal * 0.1);
            const total = subtotal + tax;
            
            document.getElementById('orderItems').innerHTML = itemsHtml;
            document.getElementById('subtotal').textContent = formatRupiah(subtotal);
            document.getElementById('tax').textContent = formatRupiah(tax);
            document.getElementById('total').textContent = formatRupiah(total);
            
            document.getElementById('cartData').value = JSON.stringify(cart);
        }
        
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            const phone = document.getElementById('phone').value;
            const phoneRegex = /^(\+62|62|0)[0-9]{9,12}$/;
            
            if (!phoneRegex.test(phone)) {
                e.preventDefault();
                alert('Invalid phone number! Use format: 08xxxxxxxxxx');
                return false;
            }

            const cart = JSON.parse(document.getElementById('cartData').value);
            if (!cart || cart.length === 0) {
                e.preventDefault();
                alert('Your cart is empty!');
                return false;
            }
        });
        
        document.addEventListener('DOMContentLoaded', displayOrderSummary);
    </script>
</body>
</html>