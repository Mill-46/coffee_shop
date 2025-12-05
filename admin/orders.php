<?php
require_once '../includes/functions.php';

check_login();
check_admin();

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$db = new Database();

// Get all orders with customer info
$stmt = $db->query("SELECT o.*, c.full_name as customer_name, c.email as customer_email,
                           (SELECT COUNT(*) FROM order_details WHERE order_id = o.order_id) as item_count
                    FROM orders o
                    LEFT JOIN customers c ON o.customer_id = c.customer_id
                    ORDER BY o.order_date DESC");
$orders = $stmt ? $stmt->fetchAll() : [];

// Calculate statistics
$total_orders = count($orders);
$pending_orders = count(array_filter($orders, fn($o) => $o['status'] === 'pending'));
$completed_orders = count(array_filter($orders, fn($o) => $o['status'] === 'completed'));
$total_revenue = array_sum(array_column(array_filter($orders, fn($o) => $o['payment_status'] === 'paid'), 'total_amount'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Admin Kafe Latte</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <i class="fas fa-coffee"></i>
                <h2>Kafe Latte</h2>
                <span>Admin Panel</span>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="customers.php">
                    <i class="fas fa-users"></i> Pelanggan
                </a>
                <a href="employees.php">
                    <i class="fas fa-user-tie"></i> Karyawan
                </a>
                <a href="orders.php" class="active">
                    <i class="fas fa-shopping-bag"></i> Pesanan
                </a>
                <a href="products.php">
                    <i class="fas fa-coffee"></i> Produk
                </a>
                <a href="../index.php">
                    <i class="fas fa-globe"></i> Lihat Website
                </a>
                <a href="../auth/logout.php" class="logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h1><i class="fas fa-shopping-bag"></i> Kelola Pesanan</h1>
            </div>

            <div class="admin-content">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon blue">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_orders; ?></h3>
                            <p>Total Pesanan</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon orange">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $pending_orders; ?></h3>
                            <p>Pesanan Pending</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon green">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $completed_orders; ?></h3>
                            <p>Selesai</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon purple">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo format_rupiah($total_revenue); ?></h3>
                            <p>Total Pendapatan</p>
                        </div>
                    </div>
                </div>

                <div class="table-container">
                    <div class="table-header">
                        <h3>Daftar Pesanan</h3>
                        <div class="filter-group">
                            <select id="statusFilter" class="filter-select">
                                <option value="">Semua Status</option>
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="processing">Processing</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" id="searchInput" placeholder="Cari pesanan...">
                            </div>
                        </div>
                    </div>

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Pelanggan</th>
                                <th>Tanggal</th>
                                <th>Item</th>
                                <th>Total</th>
                                <th>Pembayaran</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr data-status="<?php echo $order['status']; ?>">
                                    <td><strong>#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                    <td>
                                        <div class="user-cell">
                                            <div class="user-avatar">
                                                <?php echo strtoupper(substr($order['customer_name'], 0, 2)); ?>
                                            </div>
                                            <div>
                                                <div><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                                <small><?php echo htmlspecialchars($order['customer_email']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo format_datetime($order['order_date']); ?></td>
                                    <td><span class="badge badge-info"><?php echo $order['item_count']; ?> items</span></td>
                                    <td><strong><?php echo format_rupiah($order['total_amount']); ?></strong></td>
                                    <td>
                                        <span class="badge badge-<?php echo $order['payment_status'] === 'paid' ? 'success' : 'warning'; ?>">
                                            <?php echo strtoupper($order['payment_method']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <select class="status-select" onchange="updateOrderStatus(<?php echo $order['order_id']; ?>, this.value)">
                                            <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="confirmed" <?php echo $order['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-icon btn-view" onclick="viewOrderDetails(<?php echo $order['order_id']; ?>)" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn-icon btn-print" onclick="printReceipt(<?php echo $order['order_id']; ?>)" title="Cetak">
                                                <i class="fas fa-print"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Order Details Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div id="orderDetailsContent"></div>
        </div>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('.data-table tbody tr');
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchValue) ? '' : 'none';
            });
        });

        // Filter by status
        document.getElementById('statusFilter').addEventListener('change', function() {
            const filterValue = this.value;
            const tableRows = document.querySelectorAll('.data-table tbody tr');
            
            tableRows.forEach(row => {
                if (!filterValue || row.dataset.status === filterValue) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        function updateOrderStatus(orderId, status) {
            if (confirm('Update status pesanan ini?')) {
                fetch('update_order_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `order_id=${orderId}&status=${status}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Status berhasil diupdate');
                        location.reload();
                    } else {
                        alert('Gagal update status');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan');
                });
            }
        }

        function viewOrderDetails(orderId) {
            fetch(`get_order_details.php?order_id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    const content = `
                        <h2>Detail Pesanan #${String(orderId).padStart(6, '0')}</h2>
                        <div class="order-detail-content">
                            ${data.html}
                        </div>
                    `;
                    document.getElementById('orderDetailsContent').innerHTML = content;
                    document.getElementById('orderModal').style.display = 'block';
                })
                .catch(error => console.error('Error:', error));
        }

        function closeModal() {
            document.getElementById('orderModal').style.display = 'none';
        }

        function printReceipt(orderId) {
            window.open(`../pages/receipt.php?order_id=${orderId}`, '_blank');
        }

        window.onclick = function(event) {
            const modal = document.getElementById('orderModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>