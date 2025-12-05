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

// Handle product actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $name = sanitizeInput($_POST['name']);
        $description = sanitizeInput($_POST['description']);
        $price = floatval($_POST['price']);
        $stock = intval($_POST['stock']);
        $category = sanitizeInput($_POST['category']);
        $image_url = sanitizeInput($_POST['image_url']);
        
        $query = "INSERT INTO products (name, description, price, stock, category, image_url) 
                  VALUES (:name, :description, :price, :stock, :category, :image_url)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':image_url', $image_url);
        
        if ($stmt->execute()) {
            setFlashMessage('success', 'Product added successfully!');
        } else {
            setFlashMessage('error', 'Failed to add product.');
        }
        redirect('products.php');
    }
    
    if ($action === 'update') {
        $id = intval($_POST['product_id']);
        $name = sanitizeInput($_POST['name']);
        $description = sanitizeInput($_POST['description']);
        $price = floatval($_POST['price']);
        $stock = intval($_POST['stock']);
        $category = sanitizeInput($_POST['category']);
        $is_available = isset($_POST['is_available']) ? 1 : 0;
        $image_url = sanitizeInput($_POST['image_url']);
        
        $query = "UPDATE products SET name = :name, description = :description, price = :price, 
                  stock = :stock, category = :category, is_available = :is_available, image_url = :image_url 
                  WHERE product_id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':is_available', $is_available);
        $stmt->bindParam(':image_url', $image_url);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            setFlashMessage('success', 'Product updated successfully!');
        } else {
            setFlashMessage('error', 'Failed to update product.');
        }
        redirect('products.php');
    }
    
    if ($action === 'delete') {
        $id = intval($_POST['product_id']);
        $query = "DELETE FROM products WHERE product_id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            setFlashMessage('success', 'Product deleted successfully!');
        } else {
            setFlashMessage('error', 'Failed to delete product.');
        }
        redirect('products.php');
    }
}

// Get all products
$query = "SELECT * FROM products ORDER BY category, name";
$stmt = $db->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management - Kafe Latte</title>
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
                <a href="customers.php">
                    <i class="fas fa-users"></i> Customers
                </a>
                <a href="employees.php">
                    <i class="fas fa-user-tie"></i> Employees
                </a>
                <a href="orders.php">
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
                <a href="products.php" class="active">
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
                <h1>Products Management</h1>
                <button class="btn btn-primary" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Add New Product
                </button>
            </div>

            <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo $flash['message']; ?>
            </div>
            <?php endif; ?>

            <div class="content-box">
                <h2>All Products</h2>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo $product['product_id']; ?></td>
                                <td>
                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;"
                                         onerror="this.src='../assets/images/placeholder.jpg'">
                                </td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['category']); ?></td>
                                <td><?php echo formatRupiah($product['price']); ?></td>
                                <td><?php echo $product['stock']; ?></td>
                                <td>
                                    <?php if ($product['is_available']): ?>
                                    <span class="badge badge-completed">Available</span>
                                    <?php else: ?>
                                    <span class="badge badge-cancelled">Unavailable</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-info btn-sm" onclick='editProduct(<?php echo json_encode($product); ?>)'>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="deleteProduct(<?php echo $product['product_id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>')">
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

    <!-- Add/Edit Modal -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add New Product</h2>
                <span class="modal-close" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST" action="" id="productForm">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="product_id" id="productId">
                
                <div class="form-group">
                    <label>Product Name *</label>
                    <input type="text" name="name" id="productName" required>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="productDescription" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category" id="productCategory" required>
                        <option value="Coffee">Coffee</option>
                        <option value="Tea">Tea</option>
                        <option value="Pastry">Pastry</option>
                        <option value="Dessert">Dessert</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Price (Rp) *</label>
                    <input type="number" name="price" id="productPrice" step="1000" required>
                </div>
                
                <div class="form-group">
                    <label>Stock *</label>
                    <input type="number" name="stock" id="productStock" required>
                </div>
                
                <div class="form-group">
                    <label>Image URL</label>
                    <input type="text" name="image_url" id="productImageUrl" placeholder="assets/images/product.jpg">
                </div>
                
                <div class="form-group" id="availabilityGroup" style="display: none;">
                    <label>
                        <input type="checkbox" name="is_available" id="productAvailable">
                        Available for sale
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Product
                </button>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Add New Product';
            document.getElementById('formAction').value = 'add';
            document.getElementById('productForm').reset();
            document.getElementById('availabilityGroup').style.display = 'none';
            document.getElementById('productModal').classList.add('active');
        }
        
        function editProduct(product) {
            document.getElementById('modalTitle').textContent = 'Edit Product';
            document.getElementById('formAction').value = 'update';
            document.getElementById('productId').value = product.product_id;
            document.getElementById('productName').value = product.name;
            document.getElementById('productDescription').value = product.description;
            document.getElementById('productCategory').value = product.category;
            document.getElementById('productPrice').value = product.price;
            document.getElementById('productStock').value = product.stock;
            document.getElementById('productImageUrl').value = product.image_url;
            document.getElementById('productAvailable').checked = product.is_available == 1;
            document.getElementById('availabilityGroup').style.display = 'block';
            document.getElementById('productModal').classList.add('active');
        }
        
        function deleteProduct(id, name) {
            if (confirm(`Are you sure you want to delete "${name}"?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="product_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function closeModal() {
            document.getElementById('productModal').classList.remove('active');
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('productModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
    
    <style>
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</body>
</html>