<?php
include 'config/session.php';
include 'config/database.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();

$order_id = $_GET['order_id'] ?? 0;

if (!$order_id) {
    header("Location: marketplace.php");
    exit();
}

// Get order details
$query = "SELECT o.*, u.full_name as farmer_name, u.phone as farmer_phone 
          FROM orders o 
          JOIN users u ON o.farmer_id = u.id 
          WHERE o.id = ? AND o.buyer_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$order_id, getUserId()]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header("Location: dashboard/buyer.php");
    exit();
}

// Get order items
$query = "SELECT oi.*, p.title, p.image, p.unit 
          FROM order_items oi 
          JOIN products p ON oi.product_id = p.id 
          WHERE oi.order_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle payment processing
$payment_success = false;
$payment_error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_method = $_POST['payment_method'];
    
    if ($payment_method == 'cod') {
        // Cash on Delivery
        try {
            $db->beginTransaction();
            
            // Update order status
            $query = "UPDATE orders SET payment_method = 'cod', order_status = 'confirmed' WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$order_id]);
            
            // Create notification for farmer
            $query = "INSERT INTO notifications (user_id, title, message, type) 
                      VALUES (?, ?, ?, 'order')";
            $stmt = $db->prepare($query);
            $stmt->execute([
                $order['farmer_id'],
                'Order Confirmed',
                "Order #{$order['order_number']} has been confirmed with Cash on Delivery payment"
            ]);
            
            $db->commit();
            $payment_success = true;
            
        } catch (Exception $e) {
            $db->rollBack();
            $payment_error = 'Failed to process payment. Please try again.';
        }
    } elseif ($payment_method == 'online') {
        // Online Payment (Simulated)
        $transaction_id = generateTransactionId();
        
        try {
            $db->beginTransaction();
            
            // Create payment transaction
            $query = "INSERT INTO payment_transactions (order_id, transaction_id, payment_gateway, amount, status) 
                      VALUES (?, ?, 'razorpay', ?, 'success')";
            $stmt = $db->prepare($query);
            $stmt->execute([$order_id, $transaction_id, $order['final_amount']]);
            
            // Update order
            $query = "UPDATE orders SET payment_method = 'online', payment_status = 'paid', payment_id = ?, transaction_id = ?, order_status = 'confirmed' WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$transaction_id, $transaction_id, $order_id]);
            
            // Create notification for farmer
            $query = "INSERT INTO notifications (user_id, title, message, type) 
                      VALUES (?, ?, ?, 'payment')";
            $stmt = $db->prepare($query);
            $stmt->execute([
                $order['farmer_id'],
                'Payment Received',
                "Payment of ₹{$order['final_amount']} received for order #{$order['order_number']}"
            ]);
            
            $db->commit();
            $payment_success = true;
            
        } catch (Exception $e) {
            $db->rollBack();
            $payment_error = 'Payment processing failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Bihar ka Bazar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <section class="py-5">
        <div class="container">
            <?php if ($payment_success): ?>
                <!-- Payment Success -->
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card border-0 shadow-lg">
                            <div class="card-body text-center p-5">
                                <div class="success-animation mb-4">
                                    <i class="fas fa-check-circle fa-5x text-success"></i>
                                </div>
                                <h2 class="text-success mb-3">Payment Successful!</h2>
                                <p class="lead mb-4">Your order has been confirmed successfully.</p>
                                
                                <div class="order-details bg-light p-4 rounded mb-4">
                                    <h5 class="mb-3">Order Details</h5>
                                    <div class="row text-start">
                                        <div class="col-md-6">
                                            <p><strong>Order Number:</strong> <?php echo $order['order_number']; ?></p>
                                            <p><strong>Total Amount:</strong> <?php echo formatCurrency($order['final_amount']); ?></p>
                                            <p><strong>Payment Method:</strong> <?php echo ucfirst($order['payment_method']); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Order Date:</strong> <?php echo date('M d, Y', strtotime($order['created_at'])); ?></p>
                                            <p><strong>Status:</strong> <span class="badge bg-success">Confirmed</span></p>
                                            <?php if ($order['transaction_id']): ?>
                                                <p><strong>Transaction ID:</strong> <?php echo $order['transaction_id']; ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="action-buttons">
                                    <a href="generate-receipt.php?order_id=<?php echo $order_id; ?>" class="btn btn-success btn-lg me-3" target="_blank">
                                        <i class="fas fa-download me-2"></i>Download Receipt
                                    </a>
                                    <a href="dashboard/buyer.php" class="btn btn-outline-success btn-lg me-3">
                                        <i class="fas fa-tachometer-alt me-2"></i>View Orders
                                    </a>
                                    <a href="marketplace.php" class="btn btn-outline-secondary btn-lg">
                                        <i class="fas fa-shopping-cart me-2"></i>Continue Shopping
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Payment Form -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Payment Options</h5>
                            </div>
                            <div class="card-body p-4">
                                <?php if ($payment_error): ?>
                                    <div class="alert alert-danger">
                                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $payment_error; ?>
                                    </div>
                                <?php endif; ?>

                                <form method="POST" id="paymentForm">
                                    <div class="payment-methods">
                                        <div class="form-check payment-option mb-3">
                                            <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod" checked>
                                            <label class="form-check-label w-100" for="cod">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-money-bill-wave fa-2x text-success me-3"></i>
                                                    <div>
                                                        <h6 class="mb-1">Cash on Delivery</h6>
                                                        <small class="text-muted">Pay when you receive your order</small>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>

                                        <div class="form-check payment-option mb-3">
                                            <input class="form-check-input" type="radio" name="payment_method" id="online" value="online">
                                            <label class="form-check-label w-100" for="online">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-credit-card fa-2x text-primary me-3"></i>
                                                    <div>
                                                        <h6 class="mb-1">Online Payment</h6>
                                                        <small class="text-muted">Pay securely using UPI, Cards, or Net Banking</small>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="payment-security mb-4">
                                        <div class="row">
                                            <div class="col-md-4 text-center">
                                                <i class="fas fa-shield-alt fa-2x text-success mb-2"></i>
                                                <p class="small mb-0">Secure Payment</p>
                                            </div>
                                            <div class="col-md-4 text-center">
                                                <i class="fas fa-lock fa-2x text-success mb-2"></i>
                                                <p class="small mb-0">SSL Encrypted</p>
                                            </div>
                                            <div class="col-md-4 text-center">
                                                <i class="fas fa-undo fa-2x text-success mb-2"></i>
                                                <p class="small mb-0">Easy Returns</p>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-success btn-lg w-100">
                                        <i class="fas fa-lock me-2"></i>Proceed to Pay ₹<?php echo number_format($order['final_amount'], 2); ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card border-0 shadow">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Order Summary</h6>
                            </div>
                            <div class="card-body">
                                <div class="order-info mb-3">
                                    <p class="mb-1"><strong>Order #:</strong> <?php echo $order['order_number']; ?></p>
                                    <p class="mb-1"><strong>Farmer:</strong> <?php echo htmlspecialchars($order['farmer_name']); ?></p>
                                    <p class="mb-0"><strong>Date:</strong> <?php echo date('M d, Y', strtotime($order['created_at'])); ?></p>
                                </div>

                                <div class="order-items mb-3">
                                    <?php foreach($order_items as $item): ?>
                                        <div class="d-flex align-items-center mb-2">
                                            <img src="assets/images/products/<?php echo $item['image']; ?>" 
                                                 alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                                 class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0 small"><?php echo htmlspecialchars($item['title']); ?></h6>
                                                <small class="text-muted"><?php echo $item['quantity']; ?> <?php echo $item['unit']; ?> × ₹<?php echo number_format($item['unit_price'], 2); ?></small>
                                            </div>
                                            <span class="fw-bold">₹<?php echo number_format($item['total_price'], 2); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <hr>

                                <div class="price-breakdown">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal:</span>
                                        <span>₹<?php echo number_format($order['total_amount'], 2); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Shipping:</span>
                                        <span>₹<?php echo number_format($order['shipping_cost'], 2); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Tax:</span>
                                        <span>₹<?php echo number_format($order['tax_amount'], 2); ?></span>
                                    </div>
                                    <?php if ($order['discount_amount'] > 0): ?>
                                        <div class="d-flex justify-content-between mb-2 text-success">
                                            <span>Discount:</span>
                                            <span>-₹<?php echo number_format($order['discount_amount'], 2); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <hr>
                                    <div class="d-flex justify-content-between fw-bold fs-5">
                                        <span>Total:</span>
                                        <span class="text-success">₹<?php echo number_format($order['final_amount'], 2); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow mt-3">
                            <div class="card-body">
                                <h6 class="mb-3">Delivery Information</h6>
                                <div class="delivery-info">
                                    <p class="mb-2"><i class="fas fa-truck text-success me-2"></i>Standard Delivery</p>
                                    <p class="mb-2"><i class="fas fa-clock text-success me-2"></i>2-3 Business Days</p>
                                    <p class="mb-0"><i class="fas fa-map-marker-alt text-success me-2"></i>Delivery to your address</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            const selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;
            
            if (selectedMethod === 'online') {
                e.preventDefault();
                
                // Simulate online payment processing
                showToast('Processing payment...', 'info');
                
                setTimeout(() => {
                    // Simulate successful payment
                    this.submit();
                }, 2000);
            }
        });
    </script>

    <style>
        .payment-option {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 1rem;
            transition: all 0.3s ease;
        }

        .payment-option:hover {
            border-color: #28a745;
            background-color: #f8f9fa;
        }

        .payment-option input:checked + label {
            border-color: #28a745;
            background-color: #f8f9fa;
        }

        .success-animation {
            animation: bounceIn 1s ease-out;
        }

        @keyframes bounceIn {
            0% {
                transform: scale(0.3);
                opacity: 0;
            }
            50% {
                transform: scale(1.05);
            }
            70% {
                transform: scale(0.9);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .order-items img {
            border: 1px solid #dee2e6;
        }

        .price-breakdown {
            font-size: 0.95rem;
        }

        .delivery-info p {
            font-size: 0.9rem;
        }
    </style>
</body>
</html>
