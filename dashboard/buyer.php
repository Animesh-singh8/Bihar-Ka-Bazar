<?php
include '../config/session.php';
include '../config/database.php';

requireLogin();

if (!isBuyer()) {
    header("Location: ../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get buyer statistics
$stats_query = "SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
    SUM(CASE WHEN order_status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
    SUM(final_amount) as total_spent
    FROM orders WHERE buyer_id = ?";
$stmt = $db->prepare($stats_query);
$stmt->execute([getUserId()]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Get recent orders
$orders_query = "SELECT o.*, u.full_name as farmer_name 
                 FROM orders o 
                 JOIN users u ON o.farmer_id = u.id 
                 WHERE o.buyer_id = ? 
                 ORDER BY o.created_at DESC 
                 LIMIT 10";
$stmt = $db->prepare($orders_query);
$stmt->execute([getUserId()]);
$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get wishlist items
$wishlist_query = "SELECT w.*, p.title, p.price, p.image, p.unit, u.full_name as farmer_name
                   FROM wishlist w 
                   JOIN products p ON w.product_id = p.id 
                   JOIN users u ON p.farmer_id = u.id
                   WHERE w.user_id = ? 
                   ORDER BY w.created_at DESC 
                   LIMIT 5";
$stmt = $db->prepare($wishlist_query);
$stmt->execute([getUserId()]);
$wishlist_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get notifications
$notifications_query = "SELECT * FROM notifications 
                        WHERE user_id = ? 
                        ORDER BY created_at DESC 
                        LIMIT 5";
$stmt = $db->prepare($notifications_query);
$stmt->execute([getUserId()]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Dashboard - Bihar ka Bazar</title>
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
                        <small class="text-muted">Buyer Account</small>
                    </div>

                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#dashboard" data-bs-toggle="pill">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#orders" data-bs-toggle="pill">
                                <i class="fas fa-shopping-bag me-2"></i>My Orders
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#wishlist" data-bs-toggle="pill">
                                <i class="fas fa-heart me-2"></i>Wishlist
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#profile" data-bs-toggle="pill">
                                <i class="fas fa-user me-2"></i>Profile
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#notifications" data-bs-toggle="pill">
                                <i class="fas fa-bell me-2"></i>Notifications
                                <?php if (count($notifications) > 0): ?>
                                    <span class="badge bg-danger ms-1"><?php echo count($notifications); ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    </ul>

                    <hr>

                    <div class="mt-auto">
                        <a href="../marketplace.php" class="btn btn-success btn-sm w-100 mb-2">
                            <i class="fas fa-shopping-cart me-1"></i>Shop Now
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
                            <h2>Dashboard</h2>
                            <div class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                <?php echo date('F d, Y'); ?>
                            </div>
                        </div>

                        <!-- Statistics Cards -->
                        <div class="row g-4 mb-4">
                            <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
                                <div class="card stat-card border-0 shadow-sm">
                                    <div class="card-body text-center">
                                        <div class="stat-icon bg-primary text-white mb-3">
                                            <i class="fas fa-shopping-bag fa-2x"></i>
                                        </div>
                                        <h3 class="mb-1"><?php echo $stats['total_orders']; ?></h3>
                                        <p class="text-muted mb-0">Total Orders</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
                                <div class="card stat-card border-0 shadow-sm">
                                    <div class="card-body text-center">
                                        <div class="stat-icon bg-success text-white mb-3">
                                            <i class="fas fa-check-circle fa-2x"></i>
                                        </div>
                                        <h3 class="mb-1"><?php echo $stats['delivered_orders']; ?></h3>
                                        <p class="text-muted mb-0">Delivered</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
                                <div class="card stat-card border-0 shadow-sm">
                                    <div class="card-body text-center">
                                        <div class="stat-icon bg-warning text-white mb-3">
                                            <i class="fas fa-clock fa-2x"></i>
                                        </div>
                                        <h3 class="mb-1"><?php echo $stats['pending_orders']; ?></h3>
                                        <p class="text-muted mb-0">Pending</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3" data-aos="fade-up" data-aos-delay="400">
                                <div class="card stat-card border-0 shadow-sm">
                                    <div class="card-body text-center">
                                        <div class="stat-icon bg-info text-white mb-3">
                                            <i class="fas fa-rupee-sign fa-2x"></i>
                                        </div>
                                        <h3 class="mb-1">₹<?php echo number_format($stats['total_spent'] ?? 0, 0); ?></h3>
                                        <p class="text-muted mb-0">Total Spent</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Orders -->
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="card border-0 shadow-sm" data-aos="fade-up">
                                    <div class="card-header bg-white">
                                        <h5 class="mb-0">Recent Orders</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (empty($recent_orders)): ?>
                                            <div class="text-center py-4">
                                                <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                                                <h6 class="text-muted">No orders yet</h6>
                                                <p class="text-muted">Start shopping to see your orders here</p>
                                                <a href="../marketplace.php" class="btn btn-success">
                                                    <i class="fas fa-shopping-cart me-1"></i>Shop Now
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Order #</th>
                                                            <th>Farmer</th>
                                                            <th>Amount</th>
                                                            <th>Status</th>
                                                            <th>Date</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach($recent_orders as $order): ?>
                                                            <tr>
                                                                <td>
                                                                    <strong><?php echo $order['order_number']; ?></strong>
                                                                </td>
                                                                <td><?php echo htmlspecialchars($order['farmer_name']); ?></td>
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
                                                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                                                <td>
                                                                    <a href="../generate-receipt.php?order_id=<?php echo $order['id']; ?>" 
                                                                       class="btn btn-sm btn-outline-success" target="_blank">
                                                                        <i class="fas fa-receipt"></i>
                                                                    </a>
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
                                            <a href="../marketplace.php" class="btn btn-success">
                                                <i class="fas fa-shopping-cart me-2"></i>Browse Products
                                            </a>
                                            <a href="#wishlist" class="btn btn-outline-success" data-bs-toggle="pill">
                                                <i class="fas fa-heart me-2"></i>View Wishlist
                                            </a>
                                            <a href="#orders" class="btn btn-outline-primary" data-bs-toggle="pill">
                                                <i class="fas fa-list me-2"></i>All Orders
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-0 shadow-sm mt-3" data-aos="fade-up" data-aos-delay="300">
                                    <div class="card-header bg-white">
                                        <h6 class="mb-0">Support</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="small text-muted mb-3">Need help with your orders?</p>
                                        <div class="d-grid gap-2">
                                            <a href="tel:+919876543210" class="btn btn-outline-success btn-sm">
                                                <i class="fas fa-phone me-1"></i>Call Support
                                            </a>
                                            <a href="mailto:support@bindisaagritech.com" class="btn btn-outline-info btn-sm">
                                                <i class="fas fa-envelope me-1"></i>Email Us
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Orders Tab -->
                    <div class="tab-pane fade" id="orders">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2>My Orders</h2>
                            <div class="btn-group" role="group">
                                <input type="radio" class="btn-check" name="orderFilter" id="all" autocomplete="off" checked>
                                <label class="btn btn-outline-success" for="all">All</label>

                                <input type="radio" class="btn-check" name="orderFilter" id="pending" autocomplete="off">
                                <label class="btn btn-outline-warning" for="pending">Pending</label>

                                <input type="radio" class="btn-check" name="orderFilter" id="delivered" autocomplete="off">
                                <label class="btn btn-outline-success" for="delivered">Delivered</label>
                            </div>
                        </div>

                        <div class="orders-container">
                            <?php if (empty($recent_orders)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-shopping-bag fa-4x text-muted mb-4"></i>
                                    <h4 class="text-muted">No orders found</h4>
                                    <p class="text-muted">You haven't placed any orders yet. Start shopping now!</p>
                                    <a href="../marketplace.php" class="btn btn-success btn-lg">
                                        <i class="fas fa-shopping-cart me-2"></i>Start Shopping
                                    </a>
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
                                                <div class="col-md-8">
                                                    <p class="mb-1"><strong>Farmer:</strong> <?php echo htmlspecialchars($order['farmer_name']); ?></p>
                                                    <p class="mb-1"><strong>Payment:</strong> <?php echo ucfirst($order['payment_method']); ?></p>
                                                    <?php if ($order['delivery_date']): ?>
                                                        <p class="mb-0"><strong>Delivery Date:</strong> <?php echo date('M d, Y', strtotime($order['delivery_date'])); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-md-4 text-md-end">
                                                    <div class="btn-group-vertical btn-group-sm">
                                                        <a href="../generate-receipt.php?order_id=<?php echo $order['id']; ?>" 
                                                           class="btn btn-outline-success" target="_blank">
                                                            <i class="fas fa-receipt me-1"></i>Receipt
                                                        </a>
                                                        <?php if ($order['order_status'] == 'delivered'): ?>
                                                            <button class="btn btn-outline-primary" onclick="rateOrder(<?php echo $order['id']; ?>)">
                                                                <i class="fas fa-star me-1"></i>Rate
                                                            </button>
                                                        <?php endif; ?>
                                                        <?php if (in_array($order['order_status'], ['pending', 'confirmed'])): ?>
                                                            <button class="btn btn-outline-danger" onclick="cancelOrder(<?php echo $order['id']; ?>)">
                                                                <i class="fas fa-times me-1"></i>Cancel
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Wishlist Tab -->
                    <div class="tab-pane fade" id="wishlist">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2>My Wishlist</h2>
                            <?php if (!empty($wishlist_items)): ?>
                                <button class="btn btn-outline-danger" onclick="clearWishlist()">
                                    <i class="fas fa-trash me-1"></i>Clear All
                                </button>
                            <?php endif; ?>
                        </div>

                        <?php if (empty($wishlist_items)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-heart fa-4x text-muted mb-4"></i>
                                <h4 class="text-muted">Your wishlist is empty</h4>
                                <p class="text-muted">Add products to your wishlist to save them for later</p>
                                <a href="../marketplace.php" class="btn btn-success btn-lg">
                                    <i class="fas fa-shopping-cart me-2"></i>Browse Products
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="row g-4">
                                <?php foreach($wishlist_items as $item): ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card wishlist-card h-100">
                                            <div class="position-relative">
                                                <img src="../assets/images/products/<?php echo $item['image']; ?>" 
                                                     alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                                     class="card-img-top" style="height: 200px; object-fit: cover;">
                                                <button class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2" 
                                                        onclick="removeFromWishlist(<?php echo $item['product_id']; ?>)">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            <div class="card-body">
                                                <h6 class="card-title"><?php echo htmlspecialchars($item['title']); ?></h6>
                                                <p class="text-muted mb-2">
                                                    <i class="fas fa-user me-1"></i>By <?php echo htmlspecialchars($item['farmer_name']); ?>
                                                </p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="h5 text-success mb-0">₹<?php echo number_format($item['price'], 2); ?></span>
                                                    <span class="text-muted">/ <?php echo $item['unit']; ?></span>
                                                </div>
                                            </div>
                                            <div class="card-footer bg-transparent">
                                                <a href="../product-details.php?id=<?php echo $item['product_id']; ?>" 
                                                   class="btn btn-success w-100">
                                                    <i class="fas fa-eye me-1"></i>View Product
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
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
                                                    <label for="address" class="form-label">Address</label>
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

                                <div class="card border-0 shadow-sm mt-4">
                                    <div class="card-header bg-white">
                                        <h5 class="mb-0">Change Password</h5>
                                    </div>
                                    <div class="card-body">
                                        <form id="passwordForm">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label for="current_password" class="form-label">Current Password</label>
                                                    <input type="password" class="form-control" id="current_password" name="current_password">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="new_password" class="form-label">New Password</label>
                                                    <input type="password" class="form-control" id="new_password" name="new_password">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="confirm_password" class="form-label">Confirm Password</label>
                                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                                </div>
                                            </div>
                                            <div class="mt-4">
                                                <button type="submit" class="btn btn-warning">
                                                    <i class="fas fa-key me-1"></i>Change Password
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
                                        <h6 class="mb-0">Account Settings</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                                            <label class="form-check-label" for="emailNotifications">
                                                Email Notifications
                                            </label>
                                        </div>
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="smsNotifications">
                                            <label class="form-check-label" for="smsNotifications">
                                                SMS Notifications
                                            </label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="marketingEmails">
                                            <label class="form-check-label" for="marketingEmails">
                                                Marketing Emails
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notifications Tab -->
                    <div class="tab-pane fade" id="notifications">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2>Notifications</h2>
                            <?php if (!empty($notifications)): ?>
                                <button class="btn btn-outline-secondary" onclick="markAllRead()">
                                    <i class="fas fa-check-double me-1"></i>Mark All Read
                                </button>
                            <?php endif; ?>
                        </div>

                        <?php if (empty($notifications)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-bell fa-4x text-muted mb-4"></i>
                                <h4 class="text-muted">No notifications</h4>
                                <p class="text-muted">You're all caught up! New notifications will appear here.</p>
                            </div>
                        <?php else: ?>
                            <div class="notifications-list">
                                <?php foreach($notifications as $notification): ?>
                                    <div class="card notification-card mb-3 <?php echo $notification['is_read'] ? '' : 'unread'; ?>">
                                        <div class="card-body">
                                            <div class="d-flex align-items-start">
                                                <div class="notification-icon me-3">
                                                    <i class="fas fa-<?php 
                                                        echo match($notification['type']) {
                                                            'order' => 'shopping-bag',
                                                            'payment' => 'credit-card',
                                                            'product' => 'box',
                                                            default => 'bell'
                                                        };
                                                    ?> fa-lg text-<?php 
                                                        echo match($notification['type']) {
                                                            'order' => 'primary',
                                                            'payment' => 'success',
                                                            'product' => 'info',
                                                            default => 'secondary'
                                                        };
                                                    ?>"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($notification['title']); ?></h6>
                                                    <p class="mb-2 text-muted"><?php echo htmlspecialchars($notification['message']); ?></p>
                                                    <small class="text-muted">
                                                        <i class="fas fa-clock me-1"></i>
                                                        <?php echo timeAgo($notification['created_at']); ?>
                                                    </small>
                                                </div>
                                                <?php if (!$notification['is_read']): ?>
                                                    <div class="notification-badge">
                                                        <span class="badge bg-primary rounded-pill">New</span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        // Initialize AOS
        AOS.init();

        // Order filtering
        document.querySelectorAll('input[name="orderFilter"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const status = this.id;
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

        // Remove from wishlist
        function removeFromWishlist(productId) {
            fetch('../api/wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'remove',
                    product_id: productId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Removed from wishlist', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(data.message || 'Error removing from wishlist', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error removing from wishlist', 'danger');
            });
        }

        // Clear wishlist
        function clearWishlist() {
            if (confirm('Are you sure you want to clear your entire wishlist?')) {
                fetch('../api/wishlist.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'clear'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Wishlist cleared', 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast(data.message || 'Error clearing wishlist', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error clearing wishlist', 'danger');
                });
            }
        }

        // Cancel order
        function cancelOrder(orderId) {
            if (confirm('Are you sure you want to cancel this order?')) {
                fetch('../api/orders.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'cancel',
                        order_id: orderId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Order cancelled successfully', 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast(data.message || 'Error cancelling order', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error cancelling order', 'danger');
                });
            }
        }

        // Rate order
        function rateOrder(orderId) {
            // This would open a rating modal
            showToast('Rating feature coming soon!', 'info');
        }

        // Mark all notifications as read
        function markAllRead() {
            fetch('../api/notifications.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'mark_all_read'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('All notifications marked as read', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(data.message || 'Error marking notifications as read', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error marking notifications as read', 'danger');
            });
        }

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

        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                showToast('Passwords do not match', 'danger');
                return;
            }
            
            const formData = new FormData(this);
            
            fetch('../api/change-password.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Password changed successfully!', 'success');
                    this.reset();
                } else {
                    showToast(data.message || 'Error changing password', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error changing password', 'danger');
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

        .order-card {
            transition: all 0.3s ease;
        }

        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .wishlist-card {
            transition: transform 0.3s ease;
        }

        .wishlist-card:hover {
            transform: translateY(-5px);
        }

        .notification-card.unread {
            border-left: 4px solid #007bff;
            background-color: #f8f9fa;
        }

        .notification-icon {
            width: 40px;
            text-align: center;
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
    </style>
</body>
</html>
