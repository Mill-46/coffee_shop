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

// Get all customers with their order statistics
$query = "SELECT c.*, u.email as user_email, u.is_active as account_active,
          COUNT(DISTINCT o.order_id) as total_orders,
          COALESCE(SUM(o.total_amount), 0) as total_spent
          FROM customers c
          LEFT JOIN users u ON c.user_id = u.user_id
          LEFT JOIN orders o ON c.customer_id = o.customer_id AND o.payment_status = 'paid'
          GROUP BY c.customer_id
          ORDER BY c.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers Management - Kafe Latte</title>
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
                <a href="dashboard.php">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>
                <a href="customers.php" class="active">
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
                <h1>Customers Management</h1>
                <p>Total Customers: <?php echo count($customers); ?></p>
            </div>

            <div class="content-box">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Loyalty Points</th>
                                <th>Total Orders</th>
                                <th>Total Spent</th>
                                <th>Status</th>
                                <th>Joined Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td><?php echo $customer['customer_id']; ?></td>
                                <td><?php echo htmlspecialchars($customer['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                <td><?php echo htmlspecialchars($customer['phone_number'] ?: '-'); ?></td>
                                <td>
                                    <span class="badge" style="background: #ffc107; color: #000;">
                                        <i class="fas fa-star"></i> <?php echo $customer['loyalty_points']; ?>
                                    </span>
                                </td>
                                <td><?php echo $customer['total_orders']; ?></td>
                                <td><?php echo formatRupiah($customer['total_spent']); ?></td>
                                <td>
                                    <?php if ($customer['account_active']): ?>
                                    <span class="badge badge-completed">Active</span>
                                    <?php else: ?>
                                    <span class="badge badge-cancelled">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($customer['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-info btn-sm" onclick='viewCustomerDetails(<?php echo json_encode($customer); ?>)'>
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Customer Details Modal -->
    <div id="customerModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Customer Details</h2>
                <span class="modal-close" onclick="closeModal()">&times;</span>
            </div>
            <div id="customerDetails"></div>
        </div>
    </div>

    <script>
        function viewCustomerDetails(customer) {
            const details = `
                <div style="padding: 1rem;">
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 2rem;">
                        <div>
                            <strong>Customer ID:</strong>
                            <p>#${customer.customer_id}</p>
                        </div>
                        <div>
                            <strong>Full Name:</strong>
                            <p>${customer.full_name}</p>
                        </div>
                        <div>
                            <strong>Email:</strong>
                            <p>${customer.email}</p>
                        </div>
                        <div>
                            <strong>Phone:</strong>
                            <p>${customer.phone_number || '-'}</p>
                        </div>
                        <div>
                            <strong>Address:</strong>
                            <p>${customer.address || '-'}</p>
                        </div>
                        <div>
                            <strong>Date of Birth:</strong>
                            <p>${customer.date_of_birth || '-'}</p>
                        </div>
                        <div>
                            <strong>Loyalty Points:</strong>
                            <p><i class="fas fa-star" style="color: #ffc107;"></i> ${customer.loyalty_points}</p>
                        </div>
                        <div>
                            <strong>Total Orders:</strong>
                            <p>${customer.total_orders}</p>
                        </div>
                        <div>
                            <strong>Total Spent:</strong>
                            <p>Rp ${parseInt(customer.total_spent).toLocaleString('id-ID')}</p>
                        </div>
                        <div>
                            <strong>Member Since:</strong>
                            <p>${new Date(customer.created_at).toLocaleDateString('id-ID')}</p>
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('customerDetails').innerHTML = details;
            document.getElementById('customerModal').classList.add('active');
        }
        
        function closeModal() {
            document.getElementById('customerModal').classList.remove('active');
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('customerModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>