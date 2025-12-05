<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

requireAdmin();

$database = new Database();
$db = $database->getConnection();

// Get statistics
$stats = [];

// Total customers
$query = "SELECT COUNT(*) as total FROM customers";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['customers'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total employees
$query = "SELECT COUNT(*) as total FROM employees WHERE is_active = 1";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['employees'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total orders
$query = "SELECT COUNT(*) as total FROM orders";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total revenue
$query = "SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'paid'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?: 0;

// Recent orders
$query = "SELECT o.*, c.full_name as customer_name 
          FROM orders o 
          LEFT JOIN customers c ON o.customer_id = c.customer_id 
          ORDER BY o.created_at DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Kafe Latte</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-coffee"></i>
                <h2>Kafe Latte Admin</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="active">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>
                <a href="customers.php">
                    <i class="fas fa-users"></i> Customers
                </a>
                <a href="employees.php">
                    <i class="fas fa-user-tie"></i> Employees
                </a>
                <a href="orders.php">
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
                <a href="products.php">
                    <i class="fas fa-coffee"></i> Products
                </a>
                <a href="../index.php">
                    <i class="fas fa-home"></i> Back to Site
                </a>
                <a href="../auth/logout.php" class="logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h1>Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['customers']); ?></h3>
                        <p>Total Customers</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['employees']); ?></h3>
                        <p>Active Employees</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['orders']); ?></h3>
                        <p>Total Orders</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo formatRupiah($stats['revenue']); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="content-box">
                <h2>Recent Orders</h2>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['order_id']; ?></td>
                                <td><?php echo htmlspecialchars($order['customer_name'] ?: 'Guest'); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                                <td><?php echo formatRupiah($order['total_amount']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $order['payment_status']; ?>">
                                        <?php echo ucfirst($order['payment_status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>