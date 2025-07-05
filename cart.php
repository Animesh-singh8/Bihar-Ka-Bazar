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

// Get cart items
$query = "SELECT c.*, p.title, p.price, p.image, p.unit, p.quantity_available, u.full_name as farmer_name, p.farmer_id
          FROM cart c 
          JOIN products p ON c.product_id = p.id 
          JOIN users u ON p.farmer_id = u.id
          WHERE c.user_id = ? AND p.status = 'active'
          ORDER BY c.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute([getUserId()]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = $subtotal > 500 ? 0 : 50;
$tax = $subtotal * 0.05;
$total = $subtotal + $shipping + $tax;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Bihar ka Bazar</title>
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
                    <h1><i class="fas fa-shopping-cart me-3"></i>Shopping Cart</h1>
                    <p>Review your items before checkout</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Cart Content -->
    <section class="py-5">
        <div class="container">
            <?php if (empty($cart_items)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-shopping-cart fa-4x text-muted mb-4"></i>
                    <h3 class="text-muted">Your cart is empty</h3>
                    <p class="text-muted">Add some products to your cart to get started</p>
                    <a href="marketplace.php" class="btn btn-success btn-lg">
                        <i class="fas fa-shopping-cart me-2"></i>Start Shopping
                    </a>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Cart Items (<?php echo count($cart_items); ?>)</h5>
                            </div>
                            <div class="card-body p-0">
                                <?php foreach ($cart_items as $item): ?>
                                    <div class="cart-item p-3 border-bottom" data-product-id="<?php echo $item['product_id']; ?>">
                                        <div class="row align-items-center">
                                            <div class="col-md-2">
                                                <img src="assets/images/products/<?php echo $item['image']; ?>" 
                                                     alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                                     class="img-fluid rounded">
                                            </div>
                                            <div class="col-md-4">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($item['title']); ?></h6>
                                                <small class="text-muted">By <?php echo htmlspecialchars($item['farmer_name']); ?></small>
                                                <div class="mt-1">
                                                    <span class="badge bg-success">₹<?php echo number_format($item['price'], 2); ?> / <?php echo $item['unit']; ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="quantity-controls">
                                                    <div class="input-group">
                                                        <button class="btn btn-outline-secondary btn-sm" onclick="updateQuantity(<?php echo $item['product_id']; ?>, -1)">-</button>
                                                        <input type="number" class="form-control form-control-sm text-center quantity-input" 
                                                               value="<?php echo $item['quantity']; ?>" 
                                                               min="1" max="<?php echo $item['quantity_available']; ?>"
                                                               onchange="updateQuantity(<?php echo $item['product_id']; ?>, 0, this.value)">
                                                        <button class="btn btn-outline-secondary btn-sm" onclick="updateQuantity(<?php echo $item['product_id']; ?>, 1)">+</button>
                                                    </div>
                                                    <small class="text-muted">Max: <?php echo $item['quantity_available']; ?></small>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <strong class="item-total">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong>
                                            </div>
                                            <div class="col-md-1">
                                                <button class="btn btn-outline-danger btn-sm" onclick="removeFromCart(<?php echo $item['product_id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="mt-3">
                            <a href="marketplace.php" class="btn btn-outline-success">
                                <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                            </a>
                            <button class="btn btn-outline-danger ms-2" onclick="clearCart()">
                                <i class="fas fa-trash me-2"></i>Clear Cart
                            </button>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
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
                                    <span id="shipping">₹<?php echo number_format($shipping, 2); ?></span>
                                </div>
                                <div class="summary-item d-flex justify-content-between">
                                    <span>Tax (5%):</span>
                                    <span id="tax">₹<?php echo number_format($tax, 2); ?></span>
                                </div>
                                <hr>
                                <div class="summary-item d-flex justify-content-between">
                                    <strong>Total:</strong>
                                    <strong id="total">₹<?php echo number_format($total, 2); ?></strong>
                                </div>

                                <div class="mt-4">
                                    <div class="shipping-info mb-3">
                                        <small class="text-muted">
                                            <i class="fas fa-truck me-1"></i>Free shipping on orders above ₹500<br>
                                            <i class="fas fa-clock me-1"></i>Delivery in 2-3 business days
                                        </small>
                                    </div>
                                    <button class="btn btn-success w-100 btn-lg" onclick="proceedToCheckout()">
                                        <i class="fas fa-credit-card me-2"></i>Proceed to Checkout
                                    </button>
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
        function updateQuantity(productId, change, newValue = null) {
            const cartItem = document.querySelector(`[data-product-id="${productId}"]`);
            const quantityInput = cartItem.querySelector('.quantity-input');
            
            let quantity;
            if (newValue !== null) {
                quantity = parseInt(newValue);
            } else {
                quantity = parseInt(quantityInput.value) + change;
            }
            
            if (quantity < 1) quantity = 1;
            
            fetch('api/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'update',
                    product_id: productId,
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    showToast(data.message || 'Error updating cart', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error updating cart', 'danger');
            });
        }

        function removeFromCart(productId) {
            if (confirm('Remove this item from cart?')) {
                fetch('api/cart.php', {
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
                        showToast('Item removed from cart', 'success');
                        location.reload();
                    } else {
                        showToast(data.message || 'Error removing item', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error removing item', 'danger');
                });
            }
        }

        function clearCart() {
            if (confirm('Clear all items from cart?')) {
                fetch('api/cart.php', {
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
                        showToast('Cart cleared', 'success');
                        location.reload();
                    } else {
                        showToast(data.message || 'Error clearing cart', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error clearing cart', 'danger');
                });
            }
        }

        function proceedToCheckout() {
            window.location.href = 'checkout.php';
        }
    </script>

    <style>
        .cart-item {
            transition: all 0.3s ease;
        }

        .cart-item:hover {
            background-color: #f8f9fa;
        }

        .quantity-controls .input-group {
            width: 120px;
        }

        .summary-item {
            padding: 0.5rem 0;
        }

        .shipping-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
        }
    </style>
</body>
</html>
