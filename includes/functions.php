<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once __DIR__ . '/../config/database.php';

// Initialize database connection
$database = new Database();
$conn = $database->getConnection();

function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function sanitize_input($data) {
    return sanitizeInput($data);
}

function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../auth/login.php");
        exit();
    }
}

function check_admin() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../index.php");
        exit();
    }
}

function requireLogin() {
    check_login();
}

function requireAdmin() {
    check_login();
    check_admin();
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function isLoggedIn() {
    return is_logged_in();
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// ==================== USER FUNCTIONS ====================

function get_user_by_id($user_id) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    } catch (Exception $e) {
        error_log("Error getting user: " . $e->getMessage());
        return false;
    }
}

function get_user_by_email($email) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    } catch (Exception $e) {
        error_log("Error getting user by email: " . $e->getMessage());
        return false;
    }
}

function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

function update_last_login($user_id) {
    global $conn;
    try {
        $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return true;
    } catch (Exception $e) {
        error_log("Error updating last login: " . $e->getMessage());
        return false;
    }
}

function get_customer_by_user_id($user_id) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT * FROM customers WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    } catch (Exception $e) {
        error_log("Error getting customer: " . $e->getMessage());
        return false;
    }
}

// ==================== PRODUCT FUNCTIONS ====================

function get_all_products($available_only = true) {
    global $conn;
    try {
        $sql = "SELECT * FROM products";
        if ($available_only) {
            $sql .= " WHERE is_available = 1";
        }
        $sql .= " ORDER BY category, name";
        
        $result = $conn->query($sql);
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        return $products;
    } catch (Exception $e) {
        error_log("Error getting products: " . $e->getMessage());
        return [];
    }
}

function get_product_by_id($product_id) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    } catch (Exception $e) {
        error_log("Error getting product: " . $e->getMessage());
        return false;
    }
}

function get_products_by_category($category) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT * FROM products WHERE category = ? AND is_available = 1 ORDER BY name");
        $stmt->bind_param("s", $category);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        return $products;
    } catch (Exception $e) {
        error_log("Error getting products by category: " . $e->getMessage());
        return [];
    }
}

// ==================== CART FUNCTIONS ====================

function add_to_cart($product_id, $quantity = 1) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
    
    return true;
}

function update_cart_item($product_id, $quantity) {
    if ($quantity <= 0) {
        remove_from_cart($product_id);
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
    return true;
}

function update_cart_quantity($product_id, $quantity) {
    return update_cart_item($product_id, $quantity);
}

function remove_from_cart($product_id) {
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
    return true;
}

function get_cart_items() {
    global $conn;
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return [];
    }
    
    $cart_items = [];
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $product = get_product_by_id($product_id);
        if ($product) {
            $product['quantity'] = $quantity;
            $product['subtotal'] = $product['price'] * $quantity;
            $cart_items[] = $product;
        }
    }
    
    return $cart_items;
}

function get_cart_total() {
    $cart_items = get_cart_items();
    $total = 0;
    
    foreach ($cart_items as $item) {
        $total += $item['subtotal'];
    }
    
    return $total;
}

function get_cart_count() {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }
    return array_sum($_SESSION['cart']);
}

function clear_cart() {
    $_SESSION['cart'] = [];
    return true;
}

// ==================== ORDER FUNCTIONS ====================

function create_order($customer_id, $payment_method, $notes = '') {
    global $conn;
    try {
        $conn->begin_transaction();
        
        // Get cart items
        $cart_items = get_cart_items();
        if (empty($cart_items)) {
            throw new Exception("Cart is empty");
        }
        
        // Calculate total
        $total_amount = get_cart_total();
        
        // Create order
        $stmt = $conn->prepare("
            INSERT INTO orders (customer_id, order_date, total_amount, status, payment_method, payment_status, notes) 
            VALUES (?, NOW(), ?, 'pending', ?, 'pending', ?)
        ");
        $stmt->bind_param("idss", $customer_id, $total_amount, $payment_method, $notes);
        $stmt->execute();
        $order_id = $stmt->insert_id;
        
        // Create order details
        $stmt = $conn->prepare("
            INSERT INTO order_details (order_id, product_id, quantity, unit_price, sub_total) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        foreach ($cart_items as $item) {
            $subtotal = $item['price'] * $item['quantity'];
            $stmt->bind_param("iiidd", $order_id, $item['product_id'], $item['quantity'], $item['price'], $subtotal);
            $stmt->execute();
            
            // Update product stock
            $update_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?");
            $update_stock->bind_param("ii", $item['quantity'], $item['product_id']);
            $update_stock->execute();
        }
        
        $conn->commit();
        
        // Clear cart
        clear_cart();
        
        return $order_id;
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error creating order: " . $e->getMessage());
        return false;
    }
}

function get_order_by_id($order_id) {
    global $conn;
    try {
        $stmt = $conn->prepare("
            SELECT o.*, c.full_name as customer_name, c.email as customer_email, c.phone_number 
            FROM orders o 
            LEFT JOIN customers c ON o.customer_id = c.customer_id 
            WHERE o.order_id = ?
        ");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    } catch (Exception $e) {
        error_log("Error getting order: " . $e->getMessage());
        return false;
    }
}

function get_order_details($order_id) {
    global $conn;
    try {
        $stmt = $conn->prepare("
            SELECT od.*, p.name as product_name, p.image_url 
            FROM order_details od 
            JOIN products p ON od.product_id = p.product_id 
            WHERE od.order_id = ?
        ");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        return $items;
    } catch (Exception $e) {
        error_log("Error getting order details: " . $e->getMessage());
        return [];
    }
}

function get_customer_orders($customer_id) {
    global $conn;
    try {
        $stmt = $conn->prepare("
            SELECT * FROM orders 
            WHERE customer_id = ? 
            ORDER BY order_date DESC
        ");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        return $orders;
    } catch (Exception $e) {
        error_log("Error getting customer orders: " . $e->getMessage());
        return [];
    }
}

function update_payment_status($order_id, $status) {
    global $conn;
    try {
        $stmt = $conn->prepare("UPDATE orders SET payment_status = ?, updated_at = NOW() WHERE order_id = ?");
        $stmt->bind_param("si", $status, $order_id);
        $stmt->execute();
        return true;
    } catch (Exception $e) {
        error_log("Error updating payment status: " . $e->getMessage());
        return false;
    }
}

function update_order_status($order_id, $status) {
    global $conn;
    try {
        $stmt = $conn->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE order_id = ?");
        $stmt->bind_param("si", $status, $order_id);
        $stmt->execute();
        return true;
    } catch (Exception $e) {
        error_log("Error updating order status: " . $e->getMessage());
        return false;
    }
}

// ==================== FORMAT FUNCTIONS ====================

function format_currency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function formatRupiah($amount) {
    return format_currency($amount);
}

function format_rupiah($amount) {
    return format_currency($amount);
}

function format_date($date) {
    if (empty($date)) return '-';
    return date('d/m/Y', strtotime($date));
}

function format_datetime($date) {
    if (empty($date)) return '-';
    return date('d/m/Y H:i', strtotime($date));
}

function format_date_short($date) {
    if (empty($date)) return '-';
    return date('d/m/Y', strtotime($date));
}

// ==================== STATUS BADGE FUNCTIONS ====================

function get_status_badge($status) {
    $badges = [
        'pending' => '<span class="badge badge-warning">Pending</span>',
        'confirmed' => '<span class="badge badge-info">Confirmed</span>',
        'processing' => '<span class="badge badge-info">Processing</span>',
        'completed' => '<span class="badge badge-success">Completed</span>',
        'cancelled' => '<span class="badge badge-danger">Cancelled</span>',
        'paid' => '<span class="badge badge-success">Paid</span>',
        'unpaid' => '<span class="badge badge-warning">Unpaid</span>'
    ];
    
    return isset($badges[$status]) ? $badges[$status] : $status;
}

// ==================== NOTIFICATION FUNCTIONS ====================

function set_flash_message($type, $message) {
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_message'] = $message;
}

function setFlashMessage($type, $message) {
    set_flash_message($type, $message);
}

function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_type'] ?? 'info';
        $message = $_SESSION['flash_message'];
        
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        
        return ['type' => $type, 'message' => $message];
    }
    return null;
}

function getFlashMessage() {
    return get_flash_message();
}

// ==================== IMAGE UPLOAD FUNCTION ====================

function upload_product_image($file) {
    $target_dir = __DIR__ . "/../assets/images/products/";
    
    // Create directory if not exists
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Check if file is an actual image
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return ['success' => false, 'message' => 'File is not an image.'];
    }
    
    // Check file size (max 5MB)
    if ($file["size"] > 5000000) {
        return ['success' => false, 'message' => 'File is too large. Max 5MB.'];
    }
    
    // Allow certain file formats
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($file_extension, $allowed_types)) {
        return ['success' => false, 'message' => 'Only JPG, JPEG, PNG, GIF & WEBP files are allowed.'];
    }
    
    // Upload file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ['success' => true, 'filename' => 'assets/images/products/' . $new_filename];
    } else {
        return ['success' => false, 'message' => 'Error uploading file.'];
    }
}

// ==================== REDIRECT FUNCTION ====================

function redirect($url) {
    header("Location: " . $url);
    exit();
}

// ==================== VALIDATION FUNCTIONS ====================

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validate_password($password, $min_length = 6) {
    return strlen($password) >= $min_length;
}

?>

