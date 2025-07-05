<?php
include '../config/session.php';
include '../config/database.php';

requireLogin();

if (!isFarmer()) {
    header("Location: ../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get farmer statistics
$stats_query = "SELECT 
    COUNT(DISTINCT p.id) as total_products,
    COUNT(DISTINCT o.id) as total_orders,
    SUM(CASE WHEN o.order_status = 'delivered' THEN 1 ELSE 0 END) as completed_orders,
    SUM(o.final_amount) as total_earnings,
    SUM(p.views) as total_views
    FROM products p 
    LEFT JOIN order_items oi ON p.id = oi.product_id
    LEFT JOIN orders o ON oi.order_id = o.id
    WHERE p.farmer_id = ?";
$stmt = $db->prepare($stats_query);
$stmt->execute([getUserId()]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Get recent orders
$orders_query = "SELECT o.*, u.full_name as buyer_name, u.phone as buyer_phone
                 FROM orders o 
                 JOIN users u ON o.buyer_id = u.id 
                 WHERE o.farmer_id = ? 
                 ORDER BY o.created_at DESC 
                 LIMIT 10";
$stmt = $db->prepare($orders_query);
$stmt->execute([getUserId()]);
$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get products
$products_query = "SELECT * FROM products WHERE farmer_id = ? ORDER BY created_at DESC";
$stmt = $db->prepare($products_query);
$stmt->execute([getUserId()]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for product form
$categories_query = "SELECT * FROM categories WHERE is_active = 1 ORDER BY name";
$stmt = $db->prepare($categories_query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle product form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $price = (float)$_POST['price'];
    $unit = sanitize($_POST['unit']);
    $quantity = (int)$_POST['quantity_available'];
    $min_quantity = (int)$_POST['min_order_quantity'];
    $category_id = (int)$_POST['category_id'];
    $location = sanitize($_POST['location']);
    $harvest_date = $_POST['harvest_date'] ?: null;
    $expiry_date = $_POST['expiry_date'] ?: null;
    $is_organic = isset($_POST['is_organic']) ? 1 : 0;
    $certification = sanitize($_POST['certification']);
    
    // Handle image upload
    $image_name = 'default-product.jpg';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = '../assets/images/products/';
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = uniqid() . '.' . $file_extension;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_name)) {
            // Image uploaded successfully
        } else {
            $image_name = 'default-product.jpg';
        }
    }
    
    try {
        $query = "INSERT INTO products (farmer_id, category_id, title, description, price, unit, quantity_available, min_order_quantity, image, location, harvest_date, expiry_date, is_organic, certification) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            getUserId(), $category_id, $title, $description, $price, $unit, 
            $quantity, $min_quantity, $image_name, $location, $harvest_date, 
            $expiry_date, $is_organic, $certification
        ]);
        
        header("Location: farmer.php?success=product_added");
        exit();
    } catch (Exception $e) {
        $error = "Failed to add product. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Dashboard - Bihar ka Bazar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar bg-light">
                <div class="d-flex flex-column p-3">
                    <div class="text-center mb-4">
                        <img src="../assets/images/avatars/<?php echo $_SESSION['profile_image']; ?>" 
                             alt="Profile" class="rounded-circle mb-2" style="width: 80px; height: 80px; object-fit: cover;">
                        <h6 class="mb-0"><?php echo htmlspecialchars(getFullName()); ?></h6>
                        <small class="text-muted">Farmer Account</small>
                    </div>

                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#dashboard" data-bs-toggle="pill">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#products" data-bs-toggle="pill">
                                <i class="fas fa-box me-2"></i>My Products
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#orders" data-bs-toggle="pill">
                                <i class="fas fa-shopping-bag me-2"></i>Orders
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#add-product" data-bs-toggle="pill">
                                <i class="fas fa-plus me-2"></i>Add Product
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#analytics" data-bs-toggle="pill">
                                <i class="fas fa-chart-bar me-2"></i>Analytics
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#profile" data-bs-toggle="pill">
                                <i class="fas fa-user me-2"></i>Profile
                            </a>
                        </li>
                    </ul>

                    <hr>

                    <div class="mt-auto">
                        <a href="../marketplace.php" class="btn btn-success btn-sm w-100 mb-2">
                            <i class="fas fa-store me-1"></i>View Marketplace
                        </a>
                        <a href="../auth/logout.php" class="btn btn-outline-danger btn-sm w-100">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="tab-content p-4">
                    <!-- Dashboard Tab -->
                    <div class="tab-pane fade show active" id="dashboard">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2>Farmer Dashboard</h2>
                            <div class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                <?php echo date('F d, Y'); ?>
                            </div>
                        </div>

                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php 
                                    echo match($_GET['success']) {
                                        'product_added' => 'Product added successfully!',
                                        'product_updated' => 'Product updated successfully!',
                                        'order_updated' => 'Order status updated successfully!',
                                        default => 'Operation completed successfully!'
                                    };
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Statistics Cards -->
                        <div class="row g-4 mb-4">
                            <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
                                <div class="card stat-card border-0 shadow-sm">
                                    <div class="card-body text-center">
                                        <div class="stat-icon bg-primary text-white mb-3">
                                            <i class="fas fa-box fa-2x"></i>
                                        </div>
                                        <h3 class="mb-1"><?php echo $stats['total_products']; ?></h3>
                                        <p class="text-muted mb-0">Total Products</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
                                <div class="card stat-card border-0 shadow-sm">
                                    <div class="card-body text-center">
                                        <div class="stat-icon bg-success text-white mb-3">
                                            <i class="fas fa-shopping-bag fa-2x"></i>
                                        </div>
                                        <h3 class="mb-1"><?php echo $stats['total_orders']; ?></h3>
                                        <p class="text-muted mb-0">Total Orders</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
                                <div class="card stat-card border-0 shadow-sm">
                                    <div class="card-body text-center">
                                        <div class="stat-icon bg-info text-white mb-3">
                                            <i class="fas fa-rupee-sign fa-2x"></i>
                                        </div>
                                        <h3 class="mb-1">₹<?php echo number_format($stats['total_earnings'] ?? 0, 0); ?></h3>
                                        <p class="text-muted mb-0">Total Earnings</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3" data-aos="fade-up" data-aos-delay="400">
                                <div class="card stat-card border-0 shadow-sm">
                                    <div class="card-body text-center">
                                        <div class="stat-icon bg-warning text-white mb-3">
                                            <i class="fas fa-eye fa-2x"></i>
                                        </div>
                                        <h3 class="mb-1"><?php echo number_format($stats['total_views'] ?? 0); ?></h3>
                                        <p class="text-muted mb-0">Product Views</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Orders -->
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="card border-0 shadow-sm" data-aos="fade-up">
                                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">Recent Orders</h5>
                                        <a href="#orders" class="btn btn-sm btn-outline-success" data-bs-toggle="pill">View All</a>
                                    </div>
                                    <div class="card-body">
                                        <?php if (empty($recent_orders)): ?>
                                            <div class="text-center py-4">
                                                <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                                                <h6 class="text-muted">No orders yet</h6>
                                                <p class="text-muted">Orders will appear here when customers buy your products</p>
                                            </div>
                                        <?php else: ?>
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Order #</th>
                                                            <th>Customer</th>
                                                            <th>Amount</th>
                                                            <th>Status</th>
                                                            <th>Date</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach(array_slice($recent_orders, 0, 5) as $order): ?>
                                                            <tr>
                                                                <td><strong><?php echo $order['order_number']; ?></strong></td>
                                                                <td><?php echo htmlspecialchars($order['buyer_name']); ?></td>
                                                                <td>₹<?php echo number_format($order['final_amount'], 2); ?></td>
                                                                <td>
                                                                    <span class="badge bg-<?php 
                                                                        echo match($order['order_status']) {
                                                                            'pending' => 'warning',
                                                                            'confirmed' => 'info',
                                                                            'processing' => 'primary',
                                                                            'shipped' => 'secondary',
                                                                            'delivered' => 'success',
                                                                            'cancelled' => 'danger',
                                                                            default => 'secondary'
                                                                        };
                                                                    ?>">
                                                                        <?php echo ucfirst($order['order_status']); ?>
                                                                    </span>
                                                                </td>
                                                                <td><?php echo date('M d', strtotime($order['created_at'])); ?></td>
                                                                <td>
                                                                    <div class="btn-group btn-group-sm">
                                                                        <button class="btn btn-outline-primary" onclick="viewOrder(<?php echo $order['id']; ?>)">
                                                                            <i class="fas fa-eye"></i>
                                                                        </button>
                                                                        <button class="btn btn-outline-success" onclick="updateOrderStatus(<?php echo $order['id']; ?>)">
                                                                            <i class="fas fa-edit"></i>
                                                                        </button>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="card border-0 shadow-sm" data-aos="fade-up" data-aos-delay="200">
                                    <div class="card-header bg-white">
                                        <h6 class="mb-0">Quick Actions</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-grid gap-2">
                                            <a href="#add-product" class="btn btn-success" data-bs-toggle="pill">
                                                <i class="fas fa-plus me-2"></i>Add New Product
                                            </a>
                                            <a href="#products" class="btn btn-outline-primary" data-bs-toggle="pill">
                                                <i class="fas fa-box me-2"></i>Manage Products
                                            </a>
                                            <a href="#orders" class="btn btn-outline-info" data-bs-toggle="pill">
                                                <i class="fas fa-list me-2"></i>View All Orders
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-0 shadow-sm mt-3" data-aos="fade-up" data-aos-delay="300">
                                    <div class="card-header bg-white">
                                        <h6 class="mb-0">Tips for Success</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="tips-list">
                                            <div class="tip-item mb-3">
                                                <i class="fas fa-camera text-success me-2"></i>
                                                <small>Upload high-quality product images</small>
                                            </div>
                                            <div class="tip-item mb-3">
                                                <i class="fas fa-edit text-info me-2"></i>
                                                <small>Write detailed product descriptions</small>
                                            </div>
                                            <div class="tip-item mb-3">
                                                <i class="fas fa-tag text-warning me-2"></i>
                                                <small>Set competitive prices</small>
                                            </div>
                                            <div class="tip-item">
                                                <i class="fas fa-shipping-fast text-primary me-2"></i>
                                                <small>Update order status promptly</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Products Tab -->
                    <div class="tab-pane fade" id="products">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2>My Products</h2>
                            <a href="#add-product" class="btn btn-success" data-bs-toggle="pill">
                                <i class="fas fa-plus me-1"></i>Add New Product
                            </a>
                        </div>

                        <?php if (empty($products)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-box fa-4x text-muted mb-4"></i>
                                <h4 class="text-muted">No products yet</h4>
                                <p class="text-muted">Start by adding your first product to the marketplace</p>
                                <a href="#add-product" class="btn btn-success btn-lg" data-bs-toggle="pill">
                                    <i class="fas fa-plus me-2"></i>Add Your First Product
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="row g-4">
                                <?php foreach($products as $product): ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card product-card h-100">
                                            <div class="position-relative">
                                                <img src="../assets/images/products/<?php echo $product['image']; ?>" 
                                                     alt="<?php echo htmlspecialchars($product['title']); ?>" 
                                                     class="card-img-top" style="height: 200px; object-fit: cover;">
                                                <div class="position-absolute top-0 end-0 m-2">
                                                    <span class="badge bg-<?php echo $product['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                        <?php echo ucfirst($product['status']); ?>
                                                    </span>
                                                </div>
                                                <?php if ($product['is_organic']): ?>
                                                    <span class="organic-badge">Organic</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="card-body">
                                                <h6 class="card-title"><?php echo htmlspecialchars($product['title']); ?></h6>
                                                <p class="text-muted small mb-2"><?php echo htmlspecialchars(substr($product['description'], 0, 80)) . '...'; ?></p>
                                                <div class="product-stats mb-3">
                                                    <div class="row text-center">
                                                        <div class="col-4">
                                                            <small class="text-muted">Price</small>
                                                            <div class="fw-bold">₹<?php echo number_format($product['price'], 2); ?></div>
                                                        </div>
                                                        <div class="col-4">
                                                            <small class="text-muted">Stock</small>
                                                            <div class="fw-bold"><?php echo $product['quantity_available']; ?></div>
                                                        </div>
                                                        <div class="col-4">
                                                            <small class="text-muted">Views</small>
                                                            <div class="fw-bold"><?php echo $product['views']; ?></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-footer bg-transparent">
                                                <div class="btn-group w-100">
                                                    <a href="../product-details.php?id=<?php echo $product['id']; ?>" 
                                                       class="btn btn-outline-success btn-sm" target="_blank">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button class="btn btn-outline-primary btn-sm" onclick="editProduct(<?php echo $product['id']; ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-outline-warning btn-sm" onclick="toggleProductStatus(<?php echo $product['id']; ?>, '<?php echo $product['status']; ?>')">
                                                        <i class="fas fa-<?php echo $product['status'] === 'active' ? 'pause' : 'play'; ?>"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger btn-sm" onclick="deleteProduct(<?php echo $product['id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Orders Tab -->
                    <div class="tab-pane fade" id="orders">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2>Orders Management</h2>
                            <div class="btn-group" role="group">
                                <input type="radio" class="btn-check" name="orderFilter" id="all-orders" autocomplete="off" checked>
                                <label class="btn btn-outline-success" for="all-orders">All</label>

                                <input type="radio" class="btn-check" name="orderFilter" id="pending-orders" autocomplete="off">
                                <label class="btn btn-outline-warning" for="pending-orders">Pending</label>

                                <input type="radio" class="btn-check" name="orderFilter" id="confirmed-orders" autocomplete="off">
                                <label class="btn btn-outline-info" for="confirmed-orders">Confirmed</label>

                                <input type="radio" class="btn-check" name="orderFilter" id="delivered-orders" autocomplete="off">
                                <label class="btn btn-outline-success" for="delivered-orders">Delivered</label>
                            </div>
                        </div>

                        <div class="orders-container">
                            <?php if (empty($recent_orders)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-shopping-bag fa-4x text-muted mb-4"></i>
                                    <h4 class="text-muted">No orders found</h4>
                                    <p class="text-muted">Orders from customers will appear here</p>
                                </div>
                            <?php else: ?>
                                <?php foreach($recent_orders as $order): ?>
                                    <div class="card order-card mb-3" data-status="<?php echo $order['order_status']; ?>">
                                        <div class="card-header">
                                            <div class="row align-items-center">
                                                <div class="col-md-6">
                                                    <h6 class="mb-0">Order #<?php echo $order['order_number']; ?></h6>
                                                    <small class="text-muted">Placed on <?php echo date('M d, Y', strtotime($order['created_at'])); ?></small>
                                                </div>
                                                <div class="col-md-6 text-md-end">
                                                    <span class="badge bg-<?php 
                                                        echo match($order['order_status']) {
                                                            'pending' => 'warning',
                                                            'confirmed' => 'info',
                                                            'processing' => 'primary',
                                                            'shipped' => 'secondary',
                                                            'delivered' => 'success',
                                                            'cancelled' => 'danger',
                                                            default => 'secondary'
                                                        };
                                                    ?> me-2">
                                                        <?php echo ucfirst($order['order_status']); ?>
                                                    </span>
                                                    <strong>₹<?php echo number_format($order['final_amount'], 2); ?></strong>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Customer:</strong> <?php echo htmlspecialchars($order['buyer_name']); ?></p>
                                                    <p class="mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($order['buyer_phone']); ?></p>
                                                    <p class="mb-1"><strong>Payment:</strong> <?php echo ucfirst($order['payment_method']); ?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Shipping Address:</strong></p>
                                                    <p class="text-muted small"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-md-8">
                                                    <?php if ($order['notes']): ?>
                                                        <p class="mb-1"><strong>Special Instructions:</strong></p>
                                                        <p class="text-muted small"><?php echo htmlspecialchars($order['notes']); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-md-4 text-md-end">
                                                    <div class="btn-group-vertical btn-group-sm">
                                                        <button class="btn btn-outline-primary" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                                            <i class="fas fa-eye me-1"></i>View Details
                                                        </button>
                                                        <button class="btn btn-outline-success" onclick="updateOrderStatus(<?php echo $order['id']; ?>)">
                                                            <i class="fas fa-edit me-1"></i>Update Status
                                                        </button>
                                                        <a href="tel:<?php echo $order['buyer_phone']; ?>" class="btn btn-outline-info">
                                                            <i class="fas fa-phone me-1"></i>Call Customer
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Add Product Tab -->
                    <div class="tab-pane fade" id="add-product">
                        <h2 class="mb-4">Add New Product</h2>
                        
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-white">
                                        <h5 class="mb-0">Product Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" enctype="multipart/form-data" id="productForm">
                                            <div class="row g-3">
                                                <div class="col-md-8">
                                                    <label for="title" class="form-label">Product Title *</label>
                                                    <input type="text" class="form-control" id="title" name="title" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="category_id" class="form-label">Category *</label>
                                                    <select class="form-select" id="category_id" name="category_id" required>
                                                        <option value="">Select Category</option>
                                                        <?php foreach($categories as $category): ?>
                                                            <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <label for="description" class="form-label">Description *</label>
                                                    <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="price" class="form-label">Price (₹) *</label>
                                                    <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="unit" class="form-label">Unit *</label>
                                                    <select class="form-select" id="unit" name="unit" required>
                                                        <option value="kg">Kilogram (kg)</option>
                                                        <option value="gram">Gram (g)</option>
                                                        <option value="liter">Liter (L)</option>
                                                        <option value="piece">Piece</option>
                                                        <option value="dozen">Dozen</option>
                                                        <option value="quintal">Quintal</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="location" class="form-label">Location *</label>
                                                    <input type="text" class="form-control" id="location" name="location" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="quantity_available" class="form-label">Available Quantity *</label>
                                                    <input type="number" class="form-control" id="quantity_available" name="quantity_available" min="0" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="min_order_quantity" class="form-label">Minimum Order Quantity *</label>
                                                    <input type="number" class="form-control" id="min_order_quantity" name="min_order_quantity" min="1" value="1" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="harvest_date" class="form-label">Harvest Date</label>
                                                    <input type="date" class="form-control" id="harvest_date" name="harvest_date">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="expiry_date" class="form-label">Expiry Date</label>
                                                    <input type="date" class="form-control" id="expiry_date" name="expiry_date">
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="is_organic" name="is_organic">
                                                        <label class="form-check-label" for="is_organic">
                                                            This is an organic product
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-12" id="certification_field" style="display: none;">
                                                    <label for="certification" class="form-label">Organic Certification</label>
                                                    <input type="text" class="form-control" id="certification" name="certification" 
                                                           placeholder="e.g., Organic India Certified, NPOP Certified">
                                                </div>
                                                <div class="col-12">
                                                    <label for="image" class="form-label">Product Image *</label>
                                                    <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                                                    <div class="form-text">Upload a high-quality image of your product (Max: 5MB)</div>
                                                </div>
                                            </div>
                                            <div class="mt-4">
                                                <button type="submit" name="add_product" class="btn btn-success btn-lg">
                                                    <i class="fas fa-plus me-2"></i>Add Product
                                                </button>
                                                <button type="reset" class="btn btn-outline-secondary btn-lg ms-2">
                                                    <i class="fas fa-undo me-2"></i>Reset Form
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-white">
                                        <h6 class="mb-0">Image Preview</h6>
                                    </div>
                                    <div class="card-body text-center">
                                        <img id="imagePreview" src="../assets/images/placeholder-product.jpg" 
                                             alt="Product Preview" class="img-fluid rounded mb-3" 
                                             style="max-height: 200px; object-fit: cover;">
                                        <p class="text-muted small">Image preview will appear here</p>
                                    </div>
                                </div>

                                <div class="card border-0 shadow-sm mt-3">
                                    <div class="card-header bg-white">
                                        <h6 class="mb-0">Product Tips</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="tips-list">
                                            <div class="tip-item mb-3">
                                                <i class="fas fa-camera text-success me-2"></i>
                                                <small>Use clear, well-lit photos</small>
                                            </div>
                                            <div class="tip-item mb-3">
                                                <i class="fas fa-edit text-info me-2"></i>
                                                <small>Write detailed descriptions</small>
                                            </div>
                                            <div class="tip-item mb-3">
                                                <i class="fas fa-tag text-warning me-2"></i>
                                                <small>Set competitive prices</small>
                                            </div>
                                            <div class="tip-item mb-3">
                                                <i class="fas fa-certificate text-primary me-2"></i>
                                                <small>Mention certifications</small>
                                            </div>
                                            <div class="tip-item">
                                                <i class="fas fa-clock text-danger me-2"></i>
                                                <small>Update stock regularly</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Analytics Tab -->
                    <div class="tab-pane fade" id="analytics">
                        <h2 class="mb-4">Analytics & Insights</h2>
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-white">
                                        <h6 class="mb-0">Sales Overview</h6>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="salesChart" width="400" height="200"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-white">
                                        <h6 class="mb-0">Product Performance</h6>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="productChart" width="400" height="200"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mt-2">
                            <div class="col-12">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-white">
                                        <h6 class="mb-0">Top Performing Products</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Product</th>
                                                        <th>Views</th>
                                                        <th>Orders</th>
                                                        <th>Revenue</th>
                                                        <th>Rating</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach(array_slice($products, 0, 5) as $product): ?>
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <img src="../assets/images/products/<?php echo $product['image']; ?>" 
                                                                         alt="<?php echo htmlspecialchars($product['title']); ?>" 
                                                                         class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                                                    <span><?php echo htmlspecialchars($product['title']); ?></span>
                                                                </div>
                                                            </td>
                                                            <td><?php echo $product['views']; ?></td>
                                                            <td>-</td>
                                                            <td>-</td>
                                                            <td>
                                                                <div class="stars">
                                                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                                                        <i class="fas fa-star <?php echo $i <= $product['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                                                    <?php endfor; ?>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Tab -->
                    <div class="tab-pane fade" id="profile">
                        <h2 class="mb-4">Profile Settings</h2>
                        
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-white">
                                        <h5 class="mb-0">Personal Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <form id="profileForm">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label for="full_name" class="form-label">Full Name</label>
                                                    <input type="text" class="form-control" id="full_name" name="full_name" 
                                                           value="<?php echo htmlspecialchars(getFullName()); ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="email" class="form-label">Email</label>
                                                    <input type="email" class="form-control" id="email" name="email" 
                                                           value="<?php echo htmlspecialchars($_SESSION['email']); ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="phone" class="form-label">Phone</label>
                                                    <input type="tel" class="form-control" id="phone" name="phone">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="city" class="form-label">City</label>
                                                    <input type="text" class="form-control" id="city" name="city">
                                                </div>
                                                <div class="col-12">
                                                    <label for="address" class="form-label">Farm Address</label>
                                                    <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                                                </div>
                                            </div>
                                            <div class="mt-4">
                                                <button type="submit" class="btn btn-success">
                                                    <i class="fas fa-save me-1"></i>Update Profile
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-white">
                                        <h6 class="mb-0">Profile Picture</h6>
                                    </div>
                                    <div class="card-body text-center">
                                        <img src="../assets/images/avatars/<?php echo $_SESSION['profile_image']; ?>" 
                                             alt="Profile" class="rounded-circle mb-3" 
                                             style="width: 120px; height: 120px; object-fit: cover;" id="profilePreview">
                                        <div class="mb-3">
                                            <input type="file" class="form-control" id="profileImage" accept="image/*" onchange="previewProfileImage(this)">
                                        </div>
                                        <button class="btn btn-success btn-sm" onclick="uploadProfileImage()">
                                            <i class="fas fa-upload me-1"></i>Upload
                                        </button>
                                    </div>
                                </div>

                                <div class="card border-0 shadow-sm mt-3">
                                    <div class="card-header bg-white">
                                        <h6 class="mb-0">Farm Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <form id="farmForm">
                                            <div class="mb-3">
                                                <label for="farm_name" class="form-label">Farm Name</label>
                                                <input type="text" class="form-control" id="farm_name" name="farm_name">
                                            </div>
                                            <div class="mb-3">
                                                <label for="farm_size" class="form-label">Farm Size (acres)</label>
                                                <input type="number" class="form-control" id="farm_size" name="farm_size" step="0.1">
                                            </div>
                                            <div class="mb-3">
                                                <label for="farming_type" class="form-label">Farming Type</label>
                                                <select class="form-select" id="farming_type" name="farming_type">
                                                    <option value="">Select Type</option>
                                                    <option value="organic">Organic</option>
                                                    <option value="conventional">Conventional</option>
                                                    <option value="mixed">Mixed</option>
                                                </select>
                                            </div>
                                            <button type="submit" class="btn btn-success btn-sm w-100">
                                                <i class="fas fa-save me-1"></i>Update Farm Info
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        // Initialize AOS
        AOS.init();

        // Image preview for product form
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagePreview').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        // Show/hide certification field
        document.getElementById('is_organic').addEventListener('change', function() {
            const certField = document.getElementById('certification_field');
            if (this.checked) {
                certField.style.display = 'block';
            } else {
                certField.style.display = 'none';
            }
        });

        // Order filtering
        document.querySelectorAll('input[name="orderFilter"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const status = this.id.replace('-orders', '').replace('all-', 'all');
                const orders = document.querySelectorAll('.order-card');
                
                orders.forEach(order => {
                    if (status === 'all' || order.dataset.status === status) {
                        order.style.display = 'block';
                    } else {
                        order.style.display = 'none';
                    }
                });
            });
        });

        // Product management functions
        function editProduct(productId) {
            // This would open an edit modal or redirect to edit page
            showToast('Edit functionality coming soon!', 'info');
        }

        function toggleProductStatus(productId, currentStatus) {
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            
            fetch('../api/products.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'toggle_status',
                    product_id: productId,
                    status: newStatus
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(`Product ${newStatus === 'active' ? 'activated' : 'deactivated'} successfully`, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(data.message || 'Error updating product status', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error updating product status', 'danger');
            });
        }

        function deleteProduct(productId) {
            if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
                fetch('../api/products.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'delete',
                        product_id: productId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Product deleted successfully', 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast(data.message || 'Error deleting product', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error deleting product', 'danger');
                });
            }
        }

        // Order management functions
        function viewOrder(orderId) {
            // This would open order details modal
            showToast('Order details modal coming soon!', 'info');
        }

        function viewOrderDetails(orderId) {
            // This would open detailed order view
            showToast('Detailed order view coming soon!', 'info');
        }

        function updateOrderStatus(orderId) {
            // This would open status update modal
            const statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
            const currentStatus = prompt('Enter new status (pending, confirmed, processing, shipped, delivered):');
            
            if (currentStatus && statuses.includes(currentStatus.toLowerCase())) {
                fetch('../api/orders.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'update_status',
                        order_id: orderId,
                        status: currentStatus.toLowerCase()
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Order status updated successfully', 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast(data.message || 'Error updating order status', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error updating order status', 'danger');
                });
            }
        }

        // Profile image preview
        function previewProfileImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profilePreview').src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Upload profile image
        function uploadProfileImage() {
            const fileInput = document.getElementById('profileImage');
            if (!fileInput.files[0]) {
                showToast('Please select an image first', 'warning');
                return;
            }

            const formData = new FormData();
            formData.append('profile_image', fileInput.files[0]);

            fetch('../api/upload-profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Profile image updated successfully!', 'success');
                } else {
                    showToast(data.message || 'Failed to upload image', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error uploading image', 'danger');
            });
        }

        // Initialize charts
        function initializeCharts() {
            // Sales Chart
            const salesCtx = document.getElementById('salesChart').getContext('2d');
            new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Sales (₹)',
                        data: [1200, 1900, 3000, 5000, 2000, 3000],
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Product Performance Chart
            const productCtx = document.getElementById('productChart').getContext('2d');
            new Chart(productCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Fruits', 'Vegetables', 'Grains', 'Fertilizers'],
                    datasets: [{
                        data: [30, 25, 20, 25],
                        backgroundColor: [
                            '#28a745',
                            '#17a2b8',
                            '#ffc107',
                            '#dc3545'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        // Initialize charts when analytics tab is shown
        document.querySelector('a[href="#analytics"]').addEventListener('shown.bs.tab', function() {
            setTimeout(initializeCharts, 100);
        });

        // Form submissions
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('../api/update-profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Profile updated successfully!', 'success');
                } else {
                    showToast(data.message || 'Error updating profile', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error updating profile', 'danger');
            });
        });

        document.getElementById('farmForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('../api/update-farm-info.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Farm information updated successfully!', 'success');
                } else {
                    showToast(data.message || 'Error updating farm information', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error updating farm information', 'danger');
            });
        });
    </script>

    <style>
        .sidebar {
            min-height: calc(100vh - 76px);
            position: sticky;
            top: 76px;
        }

        .main-content {
            min-height: calc(100vh - 76px);
        }

        .stat-card {
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }

        .product-card {
            transition: transform 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .order-card {
            transition: all 0.3s ease;
        }

        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .nav-pills .nav-link {
            color: #6c757d;
            border-radius: 8px;
            margin-bottom: 0.25rem;
        }

        .nav-pills .nav-link.active {
            background-color: #28a745;
        }

        .nav-pills .nav-link:hover:not(.active) {
            background-color: #e9ecef;
        }

        .tip-item {
            display: flex;
            align-items: center;
        }

        .product-stats {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
        }

        #imagePreview {
            border: 2px dashed #dee2e6;
            padding: 1rem;
        }

        .stars i {
            font-size: 0.9rem;
        }
    </style>
</body>
</html>
