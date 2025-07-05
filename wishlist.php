<?php
include 'config/session.php';
include 'config/database.php';

// Check if user is logged in and is a buyer
if (!isLoggedIn() || !isBuyer()) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get wishlist items
$query = "SELECT w.*, p.title, p.price, p.unit, p.image, p.is_organic, p.quantity_available, 
                 u.full_name as farmer_name, c.name as category_name
          FROM wishlist w
          JOIN products p ON w.product_id = p.id
          JOIN users u ON p.farmer_id = u.id
          JOIN categories c ON p.category_id = c.id
          WHERE w.user_id = ? AND p.status = 'active'
          ORDER BY w.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute([getUserId()]);
$wishlist_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - Bihar ka Bazar</title>
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
                    <h1><i class="fas fa-heart me-3"></i>My Wishlist</h1>
                    <p>Your favorite products saved for later</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Wishlist Content -->
    <section class="py-5">
        <div class="container">
            <?php if (empty($wishlist_items)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-heart fa-5x text-muted mb-4"></i>
                    <h3 class="text-muted mb-3">Your wishlist is empty</h3>
                    <p class="text-muted mb-4">Start adding products you love to your wishlist</p>
                    <a href="marketplace.php" class="btn btn-success btn-lg">
                        <i class="fas fa-shopping-cart me-2"></i>Start Shopping
                    </a>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4><?php echo count($wishlist_items); ?> item(s) in your wishlist</h4>
                            <button class="btn btn-outline-danger" onclick="clearWishlist()">
                                <i class="fas fa-trash me-1"></i>Clear All
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <?php foreach($wishlist_items as $item): ?>
                        <div class="col-md-6 col-lg-4" id="wishlist-item-<?php echo $item['product_id']; ?>">
                            <div class="product-card h-100">
                                <div class="product-image position-relative">
                                    <img src="assets/images/products/<?php echo $item['image']; ?>" 
                                         alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                         class="img-fluid">
                                    <?php if($item['is_organic']): ?>
                                        <span class="organic-badge">Organic</span>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2" 
                                            onclick="removeFromWishlist(<?php echo $item['product_id']; ?>)"
                                            title="Remove from wishlist">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="product-content">
                                    <div class="product-category mb-2">
                                        <small class="text-success fw-bold"><?php echo $item['category_name']; ?></small>
                                    </div>
                                    <h6 class="product-title"><?php echo htmlspecialchars($item['title']); ?></h6>
                                    <p class="product-farmer">
                                        <i class="fas fa-user me-1"></i>By <?php echo htmlspecialchars($item['farmer_name']); ?>
                                    </p>
                                    <div class="product-price mb-3">
                                        ₹<?php echo number_format($item['price'], 2); ?> / <?php echo $item['unit']; ?>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-warning" onclick="addToCart(<?php echo $item['product_id']; ?>)">
                                            <i class="fas fa-shopping-cart me-1"></i>Add to Cart
                                        </button>
                                        <a href="product-details.php?id=<?php echo $item['product_id']; ?>" class="btn btn-outline-success">
                                            <i class="fas fa-eye me-1"></i>View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Continue Shopping -->
                <div class="text-center mt-5">
                    <a href="marketplace.php" class="btn btn-success btn-lg">
                        <i class="fas fa-plus me-2"></i>Continue Shopping
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        function removeFromWishlist(productId) {
            if (confirm('Remove this item from your wishlist?')) {
                fetch('api/wishlist.php', {
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
                        document.getElementById(`wishlist-item-${productId}`).remove();
                        showToast('Removed from wishlist', 'success');
                        
                        // Check if wishlist is empty
                        const remainingItems = document.querySelectorAll('[id^="wishlist-item-"]');
                        if (remainingItems.length === 0) {
                            location.reload();
                        }
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

        function clearWishlist() {
            if (confirm('Are you sure you want to clear your entire wishlist?')) {
                fetch('api/wishlist.php', {
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
                        location.reload();
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

        function addToCart(productId) {
            fetch('api/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'add',
                    product_id: productId,
                    quantity: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Added to cart!', 'success');
                    updateCartCount();
                } else {
                    showToast(data.message || 'Error adding to cart', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error adding to cart', 'danger');
            });
        }

        function updateCartCount() {
            fetch('api/cart.php?action=count')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const cartBadge = document.querySelector('.navbar .cart-badge');
                    if (cartBadge) {
                        cartBadge.textContent = data.count;
                        if (data.count === 0) {
                            cartBadge.style.display = 'none';
                        } else {
                            cartBadge.style.display = 'inline';
                        }
                    }
                }
            })
            .catch(error => console.error('Error updating cart count:', error));
        }
    </script>
</body>
</html>
