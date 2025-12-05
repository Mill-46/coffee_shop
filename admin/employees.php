<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

check_login();
check_admin();

$db = new Database();

// Get all employees
$stmt = $db->query("SELECT e.*, u.email, u.is_active as user_active 
                    FROM employees e 
                    LEFT JOIN users u ON e.user_id = u.user_id 
                    ORDER BY e.created_at DESC");
$employees = $stmt ? $stmt->fetchAll() : [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Karyawan - Admin Kafe Latte</title>
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
                <a href="employees.php" class="active">
                    <i class="fas fa-user-tie"></i> Karyawan
                </a>
                <a href="orders.php">
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
                <h1><i class="fas fa-user-tie"></i> Kelola Karyawan</h1>
                <div class="header-actions">
                    <button class="btn-primary" onclick="showAddModal()">
                        <i class="fas fa-plus"></i> Tambah Karyawan
                    </button>
                </div>
            </div>

            <div class="admin-content">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon blue">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo count($employees); ?></h3>
                            <p>Total Karyawan</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon green">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo count(array_filter($employees, fn($e) => $e['is_active'])); ?></h3>
                            <p>Karyawan Aktif</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon orange">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo count(array_unique(array_column($employees, 'department'))); ?></h3>
                            <p>Departemen</p>
                        </div>
                    </div>
                </div>

                <div class="table-container">
                    <div class="table-header">
                        <h3>Daftar Karyawan</h3>
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" placeholder="Cari karyawan...">
                        </div>
                    </div>

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Lengkap</th>
                                <th>Email</th>
                                <th>Posisi</th>
                                <th>Departemen</th>
                                <th>Tanggal Masuk</th>
                                <th>Gaji</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($employees as $employee): ?>
                                <tr>
                                    <td>#<?php echo str_pad($employee['employee_id'], 4, '0', STR_PAD_LEFT); ?></td>
                                    <td>
                                        <div class="user-cell">
                                            <div class="user-avatar">
                                                <?php echo strtoupper(substr($employee['full_name'], 0, 2)); ?>
                                            </div>
                                            <span><?php echo htmlspecialchars($employee['full_name']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($employee['email']); ?></td>
                                    <td><span class="badge badge-info"><?php echo htmlspecialchars($employee['position']); ?></span></td>
                                    <td><?php echo htmlspecialchars($employee['department']); ?></td>
                                    <td><?php echo format_date($employee['hire_date']); ?></td>
                                    <td><?php echo format_rupiah($employee['salary']); ?></td>
                                    <td>
                                        <?php if ($employee['is_active']): ?>
                                            <span class="status-badge status-active">Aktif</span>
                                        <?php else: ?>
                                            <span class="status-badge status-inactive">Nonaktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-icon btn-view" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn-icon btn-edit" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-icon btn-delete" title="Hapus">
                                                <i class="fas fa-trash"></i>
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

        function showAddModal() {
            alert('Fitur tambah karyawan akan segera tersedia');
        }
    </script>
</body>
</html>