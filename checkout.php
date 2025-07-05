<?php
include 'config/session.php';
include 'config/database.php';

requireLogin();

if (!isBuyer()) {
    header("Location: index.php");
    exit();
}

function generateOrderNumber() {
    return 'BKB' . date('Y') . rand(100000, 999999);
}

$database = new Database();
$db = $database->getConnection();

// Check if direct buy or from cart
$direct_buy = isset($_GET['direct']) && $_GET['direct'] == '1';
$cart_items = [];

if ($direct_buy) {
    // Direct buy from product page
    $product_id = $_GET['product_id'] ?? 0;
    $quantity = $_GET['quantity'] ?? 1;
    
    if ($product_id) {
        $query = "SELECT p.*, u.full_name as farmer_name FROM products p 
                  JOIN users u ON p.farmer_id = u.id 
                  WHERE p.id = ? AND p.status = 'active'";
        $stmt = $db->prepare($query);
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            $cart_items[] = [
                'product_id' => $product['id'],
                'title' => $product['title'],
                'price' => $product['price'],
                'image' => $product['image'],
                'unit' => $product['unit'],
                'quantity' => $quantity,
                'farmer_name' => $product['farmer_name'],
                'farmer_id' => $product['farmer_id']
            ];
        }
    }
} else {
    // From cart
    $query = "SELECT c.*, p.title, p.price, p.image, p.unit, u.full_name as farmer_name, p.farmer_id
              FROM cart c 
              JOIN products p ON c.product_id = p.id 
              JOIN users u ON p.farmer_id = u.id
              WHERE c.user_id = ? AND p.status = 'active'
              ORDER BY c.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute([getUserId()]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (empty($cart_items)) {
    header("Location: marketplace.php");
    exit();
}

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// Get user details
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([getUserId()]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid request. Please try again.';
    } else {
        $shipping_address = sanitize($_POST['shipping_address']);
        $phone = sanitize($_POST['phone']);
        $notes = sanitize($_POST['notes'] ?? '');
        $coupon_code = sanitize($_POST['coupon_code'] ?? '');
        
        // Calculate final amounts
        $shipping_cost = $subtotal > 500 ? 0 : 50;
        $tax_amount = $subtotal * 0.05;
        $discount_amount = 0;
        
        // Apply coupon if provided
        if ($coupon_code) {
            $query = "SELECT * FROM coupons WHERE code = ? AND is_active = 1 AND expires_at > NOW()";
            $stmt = $db->prepare($query);
            $stmt->execute([$coupon_code]);
            $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($coupon) {
                if ($coupon['discount_type'] == 'percentage') {
                    $discount_amount = ($subtotal * $coupon['discount_value']) / 100;
                } else {
                    $discount_amount = $coupon['discount_value'];
                }
                
                if ($discount_amount > $subtotal) {
                    $discount_amount = $subtotal;
                }
            }
        }
        
        $final_amount = $subtotal + $shipping_cost + $tax_amount - $discount_amount;
        
        try {
            $db->beginTransaction();
            
            // Group items by farmer
            $farmers_orders = [];
            foreach ($cart_items as $item) {
                $farmer_id = $item['farmer_id'];
                if (!isset($farmers_orders[$farmer_id])) {
                    $farmers_orders[$farmer_id] = [];
                }
                $farmers_orders[$farmer_id][] = $item;
            }
            
            $order_ids = [];
            
            // Create separate orders for each farmer
            foreach ($farmers_orders as $farmer_id => $farmer_items) {
                $farmer_subtotal = 0;
                foreach ($farmer_items as $item) {
                    $farmer_subtotal += $item['price'] * $item['quantity'];
                }
                
                $farmer_shipping = $farmer_subtotal > 500 ? 0 : 50;
                $farmer_tax = $farmer_subtotal * 0.05;
                $farmer_discount = ($farmer_subtotal / $subtotal) * $discount_amount;
                $farmer_total = $farmer_subtotal + $farmer_shipping + $farmer_tax - $farmer_discount;
                
                $order_number = 'BKB' . date('Y') . rand(100000, 999999);
                
                // Create order - make coupon_code optional
                if ($coupon_code) {
                    $query = "INSERT INTO orders (order_number, buyer_id, farmer_id, total_amount, shipping_cost, tax_amount, discount_amount, final_amount, shipping_address, notes, coupon_code, order_status, created_at) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
                    $stmt = $db->prepare($query);
                    $result = $stmt->execute([
                        $order_number, getUserId(), $farmer_id,
                        $farmer_subtotal, $farmer_shipping, $farmer_tax, $farmer_discount, $farmer_total,
                        $shipping_address, $notes, $coupon_code
                    ]);
                } else {
                    $query = "INSERT INTO orders (order_number, buyer_id, farmer_id, total_amount, shipping_cost, tax_amount, discount_amount, final_amount, shipping_address, notes, order_status, created_at) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
                    $stmt = $db->prepare($query);
                    $result = $stmt->execute([
                        $order_number, getUserId(), $farmer_id,
                        $farmer_subtotal, $farmer_shipping, $farmer_tax, $farmer_discount, $farmer_total,
                        $shipping_address, $notes
                    ]);
                }
                
                if (!$result) {
                    throw new Exception("Failed to create order: " . implode(", ", $stmt->errorInfo()));
                }
                
                $order_id = $db->lastInsertId();
                $order_ids[] = $order_id;
                
                // Create order items
                foreach ($farmer_items as $item) {
                    $query = "INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) 
                      VALUES (?, ?, ?, ?, ?)";
                    $stmt = $db->prepare($query);
                    $result = $stmt->execute([
                        $order_id, $item['product_id'], $item['quantity'], 
                        $item['price'], $item['price'] * $item['quantity']
                    ]);
                    
                    if (!$result) {
                        throw new Exception("Failed to create order item: " . implode(", ", $stmt->errorInfo()));
                    }
                    
                    // Update product quantity (check if column exists)
                    try {
                        $query = "UPDATE products SET quantity_available = GREATEST(0, quantity_available - ?) WHERE id = ?";
                        $stmt = $db->prepare($query);
                        $stmt->execute([$item['quantity'], $item['product_id']]);
                    } catch (Exception $e) {
                        // Column might not exist, continue without error
                    }
                }
                
                // Create notification for farmer (check if table exists)
                try {
                    $query = "INSERT INTO notifications (user_id, title, message, type, created_at) 
                      VALUES (?, ?, ?, 'order', NOW())";
                    $stmt = $db->prepare($query);
                    $stmt->execute([
                        $farmer_id,
                        'New Order Received',
                        "You have received a new order #{$order_number}"
                    ]);
                } catch (Exception $e) {
                    // Notification table might not exist, continue without error
                }
            }
            
            // Clear cart if not direct buy
            if (!$direct_buy) {
                $query = "DELETE FROM cart WHERE user_id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([getUserId()]);
            }
            
            $db->commit();
            
            // Redirect to payment
            $order_ids_str = implode(',', $order_ids);
            header("Location: payment.php?order_ids={$order_ids_str}");
            exit();
            
        } catch (Exception $e) {
            $db->rollBack();
            $error = 'Order creation failed: ' . $e->getMessage();
            error_log("Checkout Error: " . $e->getMessage()); // Log for debugging
        }
    }
}

$shipping_cost = $subtotal > 500 ? 0 : 50;
$tax_amount = $subtotal * 0.05;
$total = $subtotal + $shipping_cost + $tax_amount;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Bihar ka Bazar</title>
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
                    <h1><i class="fas fa-credit-card me-3"></i>Checkout</h1>
                    <p>Complete your order</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Checkout Content -->
    <section class="py-5">
        <div class="container">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="checkoutForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="row g-5">
                    <div class="col-lg-8">
                        <!-- Shipping Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-shipping-fast me-2"></i>Shipping Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="full_name" class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" id="full_name" 
                                               value="<?php echo htmlspecialchars($user['full_name']); ?>" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">Phone Number *</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                                    </div>
                                    <div class="col-12">
                                        <label for="shipping_address" class="form-label">Shipping Address *</label>
                                        <textarea class="form-control" id="shipping_address" name="shipping_address" 
                                                  rows="3" required placeholder="Enter your complete address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label for="notes" class="form-label">Order Notes (Optional)</label>
                                        <textarea class="form-control" id="notes" name="notes" 
                                                  rows="2" placeholder="Any special instructions for delivery"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Coupon Code -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-tag me-2"></i>Coupon Code (Optional)</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" id="coupon_code" name="coupon_code" 
                                               placeholder="Enter coupon code (optional)">
                                    </div>
                                    <div class="col-md-4">
                                        <button type="button" class="btn btn-outline-success w-100" onclick="applyCoupon()">
                                            Apply Coupon
                                        </button>
                                    </div>
                                </div>
                                <div id="couponMessage" class="mt-2"></div>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-shopping-bag me-2"></i>Order Items</h5>
                            </div>
                            <div class="card-body p-0">
                                <?php foreach ($cart_items as $item): ?>
                                    <div class="order-item p-3 border-bottom">
                                        <div class="row align-items-center">
                                            <div class="col-md-2">
                                                <img src="assets/images/products/<?php echo $item['image']; ?>" 
                                                     alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                                     class="img-fluid rounded">
                                            </div>
                                            <div class="col-md-5">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($item['title']); ?></h6>
                                                <small class="text-muted">By <?php echo htmlspecialchars($item['farmer_name']); ?></small>
                                            </div>
                                            <div class="col-md-2">
                                                <span class="fw-bold"><?php echo $item['quantity']; ?> <?php echo $item['unit']; ?></span>
                                            </div>
                                            <div class="col-md-2">
                                                <span class="fw-bold">₹<?php echo number_format($item['price'], 2); ?></span>
                                            </div>
                                            <div class="col-md-1">
                                                <span class="fw-bold text-success">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Order Summary -->
                        <div class="card sticky-top" style="top: 100px;">
                            <div class="card-header">
                                <h5 class="mb-0">Order Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="summary-item d-flex justify-content-between">
                                    <span>Subtotal:</span>
                                    <span id="subtotal">₹<?php echo number_format($subtotal, 2); ?></span>
                                </div>
                                <div class="summary-item d-flex justify-content-between">
                                    <span>Shipping:</span>
                                    <span id="shipping">₹<?php echo number_format($shipping_cost, 2); ?></span>
                                </div>
                                <div class="summary-item d-flex justify-content-between">
                                    <span>Tax (5%):</span>
                                    <span id="tax">₹<?php echo number_format($tax_amount, 2); ?></span>
                                </div>
                                <div class="summary-item d-flex justify-content-between text-success" id="discountRow" style="display: none;">
                                    <span>Discount:</span>
                                    <span id="discount">-₹0.00</span>
                                </div>
                                <hr>
                                <div class="summary-item d-flex justify-content-between">
                                    <strong>Total:</strong>
                                    <strong id="total" class="text-success">₹<?php echo number_format($total, 2); ?></strong>
                                </div>

                                <div class="mt-4">
                                    <div class="shipping-info mb-3 p-3 bg-light rounded">
                                        <h6 class="mb-2">Delivery Information</h6>
                                        <small class="text-muted">
                                            <i class="fas fa-truck me-1"></i>Free shipping on orders above ₹500<br>
                                            <i class="fas fa-clock me-1"></i>Delivery in 2-3 business days<br>
                                            <i class="fas fa-shield-alt me-1"></i>100% secure payment
                                        </small>
                                    </div>
                                    
                                    <button type="submit" name="place_order" class="btn btn-success w-100 btn-lg">
                                        <i class="fas fa-credit-card me-2"></i>Place Order
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        let subtotal = <?php echo $subtotal; ?>;
        let discountAmount = 0;

        function applyCoupon() {
            const couponCode = document.getElementById('coupon_code').value.trim();
            const messageDiv = document.getElementById('couponMessage');
            
            if (!couponCode) {
                messageDiv.innerHTML = '<div class="alert alert-warning alert-sm">Please enter a coupon code</div>';
                return;
            }
            
            fetch('api/validate-coupon.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    coupon_code: couponCode,
                    subtotal: subtotal
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    discountAmount = data.discount_amount;
                    updateOrderSummary();
                    messageDiv.innerHTML = `<div class="alert alert-success alert-sm">Coupon applied! You saved ₹${data.discount_amount.toFixed(2)}</div>`;
                } else {
                    messageDiv.innerHTML = `<div class="alert alert-danger alert-sm">${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                messageDiv.innerHTML = '<div class="alert alert-danger alert-sm">Error applying coupon</div>';
            });
        }

        function updateOrderSummary() {
            const shipping = subtotal > 500 ? 0 : 50;
            const tax = subtotal * 0.05;
            const total = subtotal + shipping + tax - discountAmount;
            
            document.getElementById('shipping').textContent = '₹' + shipping.toFixed(2);
            document.getElementById('tax').textContent = '₹' + tax.toFixed(2);
            document.getElementById('total').textContent = '₹' + total.toFixed(2);
            
            if (discountAmount > 0) {
                document.getElementById('discountRow').style.display = 'flex';
                document.getElementById('discount').textContent = '-₹' + discountAmount.toFixed(2);
            } else {
                document.getElementById('discountRow').style.display = 'none';
            }
        }

        // Form validation
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            const requiredFields = ['phone', 'shipping_address'];
            let isValid = true;
            
            requiredFields.forEach(field => {
                const input = document.getElementById(field);
                if (!input.value.trim()) {
                    input.classList.add('is-invalid');
                    isValid = false;
                } else {
                    input.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showToast('Please fill in all required fields', 'danger');
            }
        });
    </script>

    <style>
        .order-item:last-child {
            border-bottom: none !important;
        }

        .summary-item {
            padding: 0.5rem 0;
        }

        .shipping-info {
            font-size: 0.9rem;
        }

        .alert-sm {
            padding: 0.5rem;
            margin-bottom: 0;
            font-size: 0.875rem;
        }

        .sticky-top {
            z-index: 1020;
        }
    </style>
</body>
</html>
