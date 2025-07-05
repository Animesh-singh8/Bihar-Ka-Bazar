<?php
include 'config/session.php';
include 'config/database.php';

requireLogin();

if (!isBuyer()) {
    header("Location: index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get orders with items
$query = "SELECT o.*, u.full_name as farmer_name, u.phone as farmer_phone,
                 GROUP_CONCAT(CONCAT(oi.quantity, ' x ', p.title) SEPARATOR ', ') as items
          FROM orders o 
          JOIN users u ON o.farmer_id = u.id 
          JOIN order_items oi ON o.id = oi.order_id
          JOIN products p ON oi.product_id = p.id
          WHERE o.buyer_id = ? 
          GROUP BY o.id
          ORDER BY o.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute([getUserId()]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Bihar ka Bazar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1><i class="fas fa-shopping-bag me-3"></i>My Orders</h1>
                    <p>Track and manage your orders</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Orders Content -->
    <section class="py-5">
        <div class="container">
            <?php if (empty($orders)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-shopping-bag fa-4x text-muted mb-4"></i>
                    <h3 class="text-muted">No orders found</h3>
                    <p class="text-muted">You haven't placed any orders yet. Start shopping now!</p>
                    <a href="marketplace.php" class="btn btn-success btn-lg">
                        <i class="fas fa-shopping-cart me-2"></i>Start Shopping
                    </a>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3>Order History (<?php echo count($orders); ?>)</h3>
                            <div class="btn-group" role="group">
                                <input type="radio" class="btn-check" name="orderFilter" id="all" autocomplete="off" checked>
                                <label class="btn btn-outline-success" for="all">All</label>

                                <input type="radio" class="btn-check" name="orderFilter" id="pending" autocomplete="off">
                                <label class="btn btn-outline-warning" for="pending">Pending</label>

                                <input type="radio" class="btn-check" name="orderFilter" id="delivered" autocomplete="off">
                                <label class="btn btn-outline-success" for="delivered">Delivered</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="orders-container">
                    <?php foreach($orders as $order): ?>
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
                                        <p class="mb-1"><strong>Items:</strong> <?php echo htmlspecialchars($order['items']); ?></p>
                                        <p class="mb-1"><strong>Farmer:</strong> <?php echo htmlspecialchars($order['farmer_name']); ?></p>
                                        <p class="mb-1"><strong>Payment:</strong> <?php echo ucfirst($order['payment_method']); ?></p>
                                        <?php if ($order['delivery_date']): ?>
                                            <p class="mb-0"><strong>Delivery Date:</strong> <?php echo date('M d, Y', strtotime($order['delivery_date'])); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4 text-md-end">
                                        <div class="btn-group-vertical btn-group-sm">
                                            <a href="generate-receipt.php?order_id=<?php echo $order['id']; ?>" 
                                               class="btn btn-outline-success" target="_blank">
                                                <i class="fas fa-receipt me-1"></i>Download Receipt
                                            </a>
                                            <?php if ($order['order_status'] == 'delivered'): ?>
                                                <button class="btn btn-outline-primary" onclick="rateOrder(<?php echo $order['id']; ?>)">
                                                    <i class="fas fa-star me-1"></i>Rate Order
                                                </button>
                                            <?php endif; ?>
                                            <?php if (in_array($order['order_status'], ['pending', 'confirmed'])): ?>
                                                <button class="btn btn-outline-danger" onclick="cancelOrder(<?php echo $order['id']; ?>)">
                                                    <i class="fas fa-times me-1"></i>Cancel Order
                                                </button>
                                            <?php endif; ?>
                                            <a href="tel:<?php echo $order['farmer_phone']; ?>" class="btn btn-outline-info">
                                                <i class="fas fa-phone me-1"></i>Call Farmer
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
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

        function cancelOrder(orderId) {
            if (confirm('Are you sure you want to cancel this order?')) {
                fetch('api/orders.php', {
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

        function rateOrder(orderId) {
            // This would open a rating modal
            showToast('Rating feature coming soon!', 'info');
        }
    </script>

    <style>
        .order-card {
            transition: all 0.3s ease;
        }

        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
    </style>
</body>
</html>
