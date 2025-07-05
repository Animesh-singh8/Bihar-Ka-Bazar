<?php
include 'config/session.php';
include 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$product_id = $_GET['id'] ?? 0;

if (!$product_id) {
    header("Location: marketplace.php");
    exit();
}

// Get product details with all necessary information
$query = "SELECT p.*, c.name as category_name, u.full_name as farmer_name, u.phone as farmer_phone, u.city as farmer_city, u.state as farmer_state
          FROM products p 
          JOIN categories c ON p.category_id = c.id 
          JOIN users u ON p.farmer_id = u.id 
          WHERE p.id = ? AND p.status = 'active'";
$stmt = $db->prepare($query);
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: marketplace.php");
    exit();
}

// Update views
$query = "UPDATE products SET views = views + 1 WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$product_id]);

// Get reviews
$query = "SELECT r.*, u.full_name as buyer_name FROM reviews r 
          JOIN users u ON r.buyer_id = u.id 
          WHERE r.product_id = ? 
          ORDER BY r.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute([$product_id]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get related products
$query = "SELECT p.*, u.full_name as farmer_name FROM products p 
          JOIN users u ON p.farmer_id = u.id 
          WHERE p.category_id = ? AND p.id != ? AND p.status = 'active' 
          LIMIT 4";
$stmt = $db->prepare($query);
$stmt->execute([$product['category_id'], $product_id]);
$related_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if in wishlist (for buyers)
$in_wishlist = false;
if (isLoggedIn() && isBuyer()) {
    $query = "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([getUserId(), $product_id]);
    $in_wishlist = $stmt->fetch() ? true : false;
}

// Product specifications (enhanced)
$specifications = [
    'Nutritional Info' => [
        'Protein' => '9.7g per 100g',
        'Carbohydrates' => '76.9g per 100g',
        'Fat' => '0.1g per 100g',
        'Fiber' => '14.5g per 100g'
    ],
    'Storage' => [
        'Temperature' => 'Room temperature',
        'Humidity' => 'Low humidity',
        'Shelf Life' => '12 months',
        'Container' => 'Airtight container'
    ],
    'Quality' => [
        'Grade' => 'Premium A+',
        'Processing' => 'Traditional roasting',
        'Additives' => 'No artificial colors',
        'Certification' => 'NPOP Certified'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['title']); ?> - Bihar ka Bazar</title>
    <meta name="description" content="<?php echo htmlspecialchars(substr($product['description'], 0, 160)); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($product['title'] . ', ' . $product['category_name'] . ', Bihar, organic, fresh'); ?>">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <!-- Enhanced Breadcrumb -->
    <section class="py-3 bg-light" style="margin-top: 80px;">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php" class="text-success"><i class="fas fa-home me-1"></i>Home</a></li>
                    <li class="breadcrumb-item"><a href="marketplace.php" class="text-success"><i class="fas fa-store me-1"></i>Marketplace</a></li>
                    <li class="breadcrumb-item"><a href="marketplace.php?category=<?php echo $product['category_id']; ?>" class="text-success"><?php echo $product['category_name']; ?></a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['title']); ?></li>
                </ol>
            </nav>
        </div>
    </section>

    <!-- Enhanced Product Details -->
    <section class="py-5">
        <div class="container">
            <div class="row g-5">
                <!-- Product Images -->
                <div class="col-lg-6" data-aos="fade-right">
                    <div class="product-gallery-enhanced">
                        <div class="main-image-container position-relative">
                            <img src="assets/images/products/<?php echo $product['image']; ?>" 
                                 alt="<?php echo htmlspecialchars($product['title']); ?>" 
                                 class="img-fluid rounded-3 shadow-lg w-100 main-product-image" 
                                 id="mainImage" style="height: 500px; object-fit: cover;">
                            
                            <?php if($product['is_organic']): ?>
                                <span class="organic-badge-enhanced">
                                    <i class="fas fa-leaf me-1"></i>Certified Organic
                                </span>
                            <?php endif; ?>
                            
                            <div class="image-zoom-overlay">
                                <i class="fas fa-search-plus"></i>
                                <span>Click to zoom</span>
                            </div>
                        </div>
                        
                        <!-- Trust Badges -->
                        <div class="trust-badges mt-3">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="trust-badge">
                                        <i class="fas fa-shield-alt text-success"></i>
                                        <small>Quality<br>Assured</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="trust-badge">
                                        <i class="fas fa-truck text-success"></i>
                                        <small>Fast<br>Delivery</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="trust-badge">
                                        <i class="fas fa-undo text-success"></i>
                                        <small>Easy<br>Returns</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Product Info -->
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="product-info-enhanced">
                        <!-- Product Meta -->
                        <div class="product-meta-enhanced mb-3">
                            <span class="badge bg-success me-2 category-badge">
                                <i class="fas fa-tag me-1"></i><?php echo $product['category_name']; ?>
                            </span>
                            <?php if($product['is_organic']): ?>
                                <span class="badge bg-warning text-dark organic-badge">
                                    <i class="fas fa-leaf me-1"></i>Organic
                                </span>
                            <?php endif; ?>
                            <span class="badge bg-info text-white">
                                <i class="fas fa-eye me-1"></i><?php echo number_format($product['views']); ?> views
                            </span>
                        </div>

                        <!-- Product Title -->
                        <h1 class="product-title-enhanced mb-3"><?php echo htmlspecialchars($product['title']); ?></h1>

                        <!-- Rating and Reviews -->
                        <div class="product-rating-enhanced mb-4">
                            <div class="d-flex align-items-center">
                                <div class="stars me-2">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $product['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="rating-text me-3"><?php echo number_format($product['rating'], 1); ?> out of 5</span>
                                <span class="reviews-count">(<?php echo $product['total_reviews']; ?> customer reviews)</span>
                            </div>
                        </div>

                        <!-- Price Section -->
                        <div class="price-section-enhanced mb-4">
                            <div class="current-price-enhanced">
                                <span class="currency">₹</span>
                                <span class="amount"><?php echo number_format($product['price'], 2); ?></span>
                                <span class="unit">/ <?php echo $product['unit']; ?></span>
                            </div>
                            <div class="price-benefits mt-2">
                                <small class="text-success">
                                    <i class="fas fa-truck me-1"></i>FREE shipping on orders above ₹500
                                </small>
                            </div>
                        </div>

                        <!-- Key Details Grid -->
                        <div class="product-details-grid mb-4">
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="detail-card">
                                        <i class="fas fa-map-marker-alt text-success"></i>
                                        <div>
                                            <strong>Location</strong>
                                            <p><?php echo htmlspecialchars($product['location']); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="detail-card">
                                        <i class="fas fa-user text-success"></i>
                                        <div>
                                            <strong>Farmer</strong>
                                            <p><?php echo htmlspecialchars($product['farmer_name']); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="detail-card">
                                        <i class="fas fa-box text-success"></i>
                                        <div>
                                            <strong>Available</strong>
                                            <p><?php echo $product['quantity_available']; ?> <?php echo $product['unit']; ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="detail-card">
                                        <i class="fas fa-shopping-cart text-success"></i>
                                        <div>
                                            <strong>Min Order</strong>
                                            <p><?php echo $product['min_order_quantity']; ?> <?php echo $product['unit']; ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Product Description -->
                        <div class="product-description-enhanced mb-4">
                            <h5 class="mb-3"><i class="fas fa-info-circle me-2"></i>Product Description</h5>
                            <div class="description-content">
                                <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                            </div>
                        </div>

                        <!-- BUYER ACTIONS - ENHANCED -->
                        <?php if (isLoggedIn() && isBuyer()): ?>
                            <!-- Quantity Selector -->
                            <div class="quantity-section-enhanced mb-4">
                                <label class="form-label fw-bold mb-2">
                                    <i class="fas fa-calculator me-1"></i>Select Quantity:
                                </label>
                                <div class="quantity-controls">
                                    <button class="btn btn-outline-secondary quantity-btn" type="button" onclick="changeQuantity(-1)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" class="form-control quantity-input text-center" id="productQuantity" 
                                           value="<?php echo $product['min_order_quantity']; ?>" 
                                           min="<?php echo $product['min_order_quantity']; ?>" 
                                           max="<?php echo $product['quantity_available']; ?>"
                                           onchange="updateTotalPrice()">
                                    <button class="btn btn-outline-secondary quantity-btn" type="button" onclick="changeQuantity(1)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <span class="unit-label"><?php echo $product['unit']; ?></span>
                                </div>
                                <div class="quantity-info mt-2">
                                    <small class="text-muted">
                                        Min: <?php echo $product['min_order_quantity']; ?> <?php echo $product['unit']; ?> | 
                                        Available: <?php echo $product['quantity_available']; ?> <?php echo $product['unit']; ?>
                                    </small>
                                </div>
                                <div class="total-price-display mt-2">
                                    <strong>Total: ₹<span id="totalPrice"><?php echo number_format($product['price'] * $product['min_order_quantity'], 2); ?></span></strong>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="action-buttons-enhanced">
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <button class="btn <?php echo $in_wishlist ? 'btn-danger' : 'btn-outline-success'; ?> w-100 wishlist-btn" 
                                                onclick="toggleWishlist(<?php echo $product_id; ?>)" id="wishlistBtn">
                                            <i class="fas fa-heart me-1"></i>
                                            <span class="btn-text"><?php echo $in_wishlist ? 'In Wishlist' : 'Add to Wishlist'; ?></span>
                                        </button>
                                    </div>
                                    <div class="col-6">
                                        <button class="btn btn-warning w-100 cart-btn" onclick="addToCart()">
                                            <i class="fas fa-shopping-cart me-1"></i>
                                            <span class="btn-text">Add to Cart</span>
                                        </button>
                                    </div>
                                </div>
                                <button class="btn btn-success btn-lg w-100 buy-now-btn" onclick="buyNow()">
                                    <i class="fas fa-shopping-bag me-2"></i>
                                    <span class="btn-text">Buy Now - Quick Checkout</span>
                                </button>
                            </div>

                            <!-- Delivery Information -->
                            <div class="delivery-info-enhanced mt-4">
                                <h6 class="mb-3"><i class="fas fa-truck me-2"></i>Delivery Information</h6>
                                <div class="delivery-details">
                                    <div class="delivery-item">
                                        <i class="fas fa-clock text-success"></i>
                                        <span>Delivery in 2-3 business days</span>
                                    </div>
                                    <div class="delivery-item">
                                        <i class="fas fa-shield-alt text-success"></i>
                                        <span>100% quality guarantee or money back</span>
                                    </div>
                                    <div class="delivery-item">
                                        <i class="fas fa-phone text-success"></i>
                                        <span>24/7 customer support available</span>
                                    </div>
                                </div>
                            </div>

                        <?php elseif (isLoggedIn() && isFarmer()): ?>
                            <div class="alert alert-info farmer-notice">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Farmer Account:</strong> You are logged in as a farmer. Only buyers can purchase products.
                                <div class="mt-2">
                                    <a href="register.php?type=buyer" class="btn btn-sm btn-success">Create Buyer Account</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="login-prompt-enhanced">
                                <div class="text-center p-4 bg-light rounded">
                                    <i class="fas fa-user-circle fa-3x text-muted mb-3"></i>
                                    <h5 class="mb-3">Login Required to Purchase</h5>
                                    <p class="text-muted mb-4">Please login or create an account to buy this product</p>
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                        <a href="login.php" class="btn btn-success btn-lg me-md-2">
                                            <i class="fas fa-sign-in-alt me-1"></i>Login
                                        </a>
                                        <a href="register.php?type=buyer" class="btn btn-outline-success btn-lg">
                                            <i class="fas fa-user-plus me-1"></i>Create Account
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Enhanced Product Tabs -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <ul class="nav nav-tabs nav-tabs-enhanced" id="productTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="specifications-tab" data-bs-toggle="tab" data-bs-target="#specifications" type="button">
                                <i class="fas fa-list-ul me-1"></i>Specifications
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button">
                                <i class="fas fa-star me-1"></i>Reviews (<?php echo count($reviews); ?>)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="farmer-tab" data-bs-toggle="tab" data-bs-target="#farmer" type="button">
                                <i class="fas fa-user me-1"></i>Farmer Info
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content tab-content-enhanced" id="productTabsContent">
                        <!-- Specifications Tab -->
                        <div class="tab-pane fade show active" id="specifications" role="tabpanel">
                            <div class="py-4">
                                <div class="row">
                                    <?php foreach($specifications as $category => $specs): ?>
                                    <div class="col-md-4 mb-4">
                                        <div class="spec-category">
                                            <h6 class="spec-category-title"><?php echo $category; ?></h6>
                                            <div class="spec-list">
                                                <?php foreach($specs as $key => $value): ?>
                                                <div class="spec-item">
                                                    <span class="spec-key"><?php echo $key; ?>:</span>
                                                    <span class="spec-value"><?php echo $value; ?></span>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Reviews Tab -->
                        <div class="tab-pane fade" id="reviews" role="tabpanel">
                            <div class="py-4">
                                <?php if (empty($reviews)): ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-star fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No reviews yet</h5>
                                        <p class="text-muted">Be the first to review this product!</p>
                                    </div>
                                <?php else: ?>
                                    <div class="reviews-list">
                                        <?php foreach($reviews as $review): ?>
                                            <div class="review-item-enhanced">
                                                <div class="review-header">
                                                    <div class="reviewer-info">
                                                        <h6 class="reviewer-name"><?php echo htmlspecialchars($review['buyer_name']); ?></h6>
                                                        <div class="review-rating">
                                                            <?php for($i = 1; $i <= 5; $i++): ?>
                                                                <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                                            <?php endfor; ?>
                                                        </div>
                                                    </div>
                                                    <small class="review-date"><?php echo timeAgo($review['created_at']); ?></small>
                                                </div>
                                                <?php if($review['review_text']): ?>
                                                    <div class="review-content">
                                                        <p><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Farmer Info Tab -->
                        <div class="tab-pane fade" id="farmer" role="tabpanel">
                            <div class="py-4">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="farmer-info-enhanced">
                                            <h5 class="mb-3"><i class="fas fa-user-tie me-2"></i>About the Farmer</h5>
                                            <div class="farmer-details">
                                                <div class="farmer-detail-item">
                                                    <i class="fas fa-user text-success"></i>
                                                    <div>
                                                        <strong>Name:</strong>
                                                        <p><?php echo htmlspecialchars($product['farmer_name']); ?></p>
                                                    </div>
                                                </div>
                                                <div class="farmer-detail-item">
                                                    <i class="fas fa-map-marker-alt text-success"></i>
                                                    <div>
                                                        <strong>Location:</strong>
                                                        <p><?php echo htmlspecialchars($product['farmer_city'] . ', ' . $product['farmer_state']); ?></p>
                                                    </div>
                                                </div>
                                                <div class="farmer-detail-item">
                                                    <i class="fas fa-phone text-success"></i>
                                                    <div>
                                                        <strong>Contact:</strong>
                                                        <p><?php echo htmlspecialchars($product['farmer_phone']); ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="contact-farmer-enhanced">
                                            <h5 class="mb-3"><i class="fas fa-phone-alt me-2"></i>Contact Farmer</h5>
                                            <p class="text-muted mb-3">Have questions about this product? Contact the farmer directly for more information.</p>
                                            <div class="contact-buttons">
                                                <a href="tel:<?php echo $product['farmer_phone']; ?>" class="btn btn-success me-2 mb-2">
                                                    <i class="fas fa-phone me-1"></i>Call Now
                                                </a>
                                                <button class="btn btn-outline-success mb-2" onclick="openWhatsApp('<?php echo $product['farmer_phone']; ?>', '<?php echo urlencode($product['title']); ?>')">
                                                    <i class="fab fa-whatsapp me-1"></i>WhatsApp
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Related Products -->
    <?php if (!empty($related_products)): ?>
    <section class="py-5">
        <div class="container">
            <h3 class="mb-4" data-aos="fade-up">
                <i class="fas fa-seedling me-2"></i>Related Products
            </h3>
            <div class="row g-4">
                <?php foreach($related_products as $index => $related): ?>
                    <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
                        <div class="product-card h-100">
                            <div class="product-image">
                                <img src="assets/images/products/<?php echo $related['image']; ?>" 
                                     alt="<?php echo htmlspecialchars($related['title']); ?>" 
                                     class="img-fluid">
                                <?php if($related['is_organic']): ?>
                                    <span class="organic-badge">Organic</span>
                                <?php endif; ?>
                            </div>
                            <div class="product-content">
                                <h6 class="product-title"><?php echo htmlspecialchars($related['title']); ?></h6>
                                <p class="product-farmer">
                                    <i class="fas fa-user me-1"></i>By <?php echo htmlspecialchars($related['farmer_name']); ?>
                                </p>
                                <div class="product-price">
                                    <?php echo formatCurrency($related['price']); ?> / <?php echo $related['unit']; ?>
                                </div>
                                <a href="product-details.php?id=<?php echo $related['id']; ?>" class="btn btn-success btn-sm w-100 mt-2">
                                    <i class="fas fa-eye me-1"></i>View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="assets/js/main.js"></script>
    
    <script>
        const productId = <?php echo $product_id; ?>;
        const productPrice = <?php echo $product['price']; ?>;
        const minQuantity = <?php echo $product['min_order_quantity']; ?>;
        const maxQuantity = <?php echo $product['quantity_available']; ?>;

        function changeQuantity(change) {
            const quantityInput = document.getElementById('productQuantity');
            let newQuantity = parseInt(quantityInput.value) + change;
            
            if (newQuantity >= minQuantity && newQuantity <= maxQuantity) {
                quantityInput.value = newQuantity;
                updateTotalPrice();
            }
        }

        function updateTotalPrice() {
            const quantity = parseInt(document.getElementById('productQuantity').value) || minQuantity;
            const total = quantity * productPrice;
            document.getElementById('totalPrice').textContent = total.toFixed(2);
        }

        function toggleWishlist(productId) {
            const btn = document.getElementById('wishlistBtn');
            const btnText = btn.querySelector('.btn-text');
            const isInWishlist = btn.classList.contains('btn-danger');
            
            // Add loading state
            btn.disabled = true;
            btnText.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing...';
            
            fetch('api/wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: isInWishlist ? 'remove' : 'add',
                    product_id: productId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (isInWishlist) {
                        btn.classList.remove('btn-danger');
                        btn.classList.add('btn-outline-success');
                        btnText.innerHTML = '<i class="fas fa-heart me-1"></i>Add to Wishlist';
                        showToast('Removed from wishlist', 'success');
                    } else {
                        btn.classList.remove('btn-outline-success');
                        btn.classList.add('btn-danger');
                        btnText.innerHTML = '<i class="fas fa-heart me-1"></i>In Wishlist';
                        showToast('Added to wishlist!', 'success');
                    }
                } else {
                    showToast(data.message || 'Error updating wishlist', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error updating wishlist', 'danger');
            })
            .finally(() => {
                btn.disabled = false;
            });
        }

        function addToCart() {
            const quantity = parseInt(document.getElementById('productQuantity').value);
            const btn = document.querySelector('.cart-btn');
            const btnText = btn.querySelector('.btn-text');
            
            // Add loading state
            btn.disabled = true;
            btnText.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Adding...';
            
            fetch('api/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'add',
                    product_id: productId,
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Added to cart successfully!', 'success');
                    updateCartCount();
                    
                    // Success animation
                    btn.classList.add('btn-success');
                    btnText.innerHTML = '<i class="fas fa-check me-1"></i>Added!';
                    
                    setTimeout(() => {
                        btn.classList.remove('btn-success');
                        btnText.innerHTML = '<i class="fas fa-shopping-cart me-1"></i>Add to Cart';
                    }, 2000);
                } else {
                    showToast(data.message || 'Error adding to cart', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error adding to cart', 'danger');
            })
            .finally(() => {
                btn.disabled = false;
                if (!btn.classList.contains('btn-success')) {
                    btnText.innerHTML = '<i class="fas fa-shopping-cart me-1"></i>Add to Cart';
                }
            });
        }

        function buyNow() {
            const quantity = parseInt(document.getElementById('productQuantity').value);
            const btn = document.querySelector('.buy-now-btn');
            const btnText = btn.querySelector('.btn-text');
            
            // Add loading state
            btn.disabled = true;
            btnText.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
            
            // Add to cart first, then redirect to checkout
            fetch('api/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'add',
                    product_id: productId,
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirect to checkout with direct purchase flag
                    window.location.href = `checkout.php?direct=1&product_id=${productId}&quantity=${quantity}`;
                } else {
                    showToast(data.message || 'Error processing order', 'danger');
                    btn.disabled = false;
                    btnText.innerHTML = '<i class="fas fa-shopping-bag me-2"></i>Buy Now - Quick Checkout';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error processing order', 'danger');
                btn.disabled = false;
                btnText.innerHTML = '<i class="fas fa-shopping-bag me-2"></i>Buy Now - Quick Checkout';
            });
        }

        function openWhatsApp(phone, productName) {
            const message = `Hi! I'm interested in your product: ${productName}. Can you provide more details?`;
            const url = `https://wa.me/91${phone}?text=${encodeURIComponent(message)}`;
            window.open(url, '_blank');
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

        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        // Initialize quantity change listener
        document.getElementById('productQuantity').addEventListener('change', updateTotalPrice);
        
        // Image zoom functionality
        document.getElementById('mainImage').addEventListener('click', function() {
            // Simple zoom modal (you can enhance this further)
            const modal = document.createElement('div');
            modal.className = 'image-zoom-modal';
            modal.innerHTML = `
                <div class="zoom-overlay" onclick="this.parentElement.remove()">
                    <img src="${this.src}" alt="${this.alt}" class="zoomed-image">
                    <button class="close-zoom" onclick="this.parentElement.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            document.body.appendChild(modal);
        });
    </script>

    <style>
        /* Enhanced Product Details Styles */
        .product-gallery-enhanced {
            position: relative;
        }

        .main-image-container {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .main-image-container:hover {
            transform: scale(1.02);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .main-product-image {
            cursor: zoom-in;
            transition: all 0.3s ease;
        }

        .organic-badge-enhanced {
            position: absolute;
            top: 15px;
            right: 15px;
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
            animation: badgePulse 2s infinite;
        }

        @keyframes badgePulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .image-zoom-overlay {
            position: absolute;
            bottom: 15px;
            left: 15px;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .main-image-container:hover .image-zoom-overlay {
            opacity: 1;
        }

        .trust-badges {
            background: rgba(255,255,255,0.95);
            border-radius: 10px;
            padding: 1rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .trust-badge {
            text-align: center;
            padding: 0.5rem;
        }

        .trust-badge i {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            display: block;
        }

        .product-info-enhanced {
            padding: 1rem;
        }

        .product-meta-enhanced .badge {
            font-size: 0.8rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            animation: badgeFloat 3s ease-in-out infinite;
        }

        @keyframes badgeFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-3px); }
        }

        .product-title-enhanced {
            font-size: 2.2rem;
            font-weight: 700;
            color: #2d3436;
            line-height: 1.3;
        }

        .product-rating-enhanced {
            background: rgba(255,255,255,0.95);
            padding: 1rem;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .price-section-enhanced {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 1.5rem;
            border-radius: 15px;
            border-left: 4px solid #28a745;
        }

        .current-price-enhanced {
            display: flex;
            align-items: baseline;
            gap: 0.5rem;
        }

        .current-price-enhanced .currency {
            font-size: 1.5rem;
            color: #28a745;
            font-weight: 600;
        }

        .current-price-enhanced .amount {
            font-size: 2.5rem;
            font-weight: 700;
            color: #28a745;
        }

        .current-price-enhanced .unit {
            font-size: 1.2rem;
            color: #6c757d;
        }

        .product-details-grid .detail-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: rgba(255,255,255,0.95);
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .product-details-grid .detail-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }

        .product-details-grid .detail-card i {
            font-size: 1.2rem;
            width: 20px;
        }

        .product-details-grid .detail-card strong {
            color: #2d3436;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
            display: block;
        }

        .product-details-grid .detail-card p {
            margin: 0;
            color: #636e72;
            font-size: 0.9rem;
        }

        .product-description-enhanced {
            background: rgba(255,255,255,0.95);
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .quantity-section-enhanced {
            background: rgba(248,249,250,0.95);
            padding: 1.5rem;
            border-radius: 15px;
            border: 2px solid rgba(40,167,69,0.1);
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            max-width: 300px;
        }

        .quantity-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .quantity-btn:hover {
            background: #28a745;
            color: white;
            transform: scale(1.1);
        }

        .quantity-input {
            width: 80px;
            height: 40px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-weight: 600;
        }

        .quantity-input:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40,167,69,0.25);
        }

        .unit-label {
            background: #28a745;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .total-price-display {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            font-size: 1.2rem;
        }

        .action-buttons-enhanced .btn {
            font-weight: 600;
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .action-buttons-enhanced .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .action-buttons-enhanced .btn:hover::before {
            left: 100%;
        }

        .action-buttons-enhanced .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }

        .wishlist-btn:hover {
            background: #dc3545 !important;
            border-color: #dc3545 !important;
            color: white !important;
        }

        .cart-btn:hover {
            background: #ffed4e !important;
            border-color: #ffed4e !important;
            color: #333 !important;
        }

        .buy-now-btn {
            background: linear-gradient(135deg, #28a745, #20c997) !important;
            border: none !important;
            font-size: 1.1rem !important;
            animation: buyNowPulse 2s infinite;
        }

        @keyframes buyNowPulse {
            0%, 100% { box-shadow: 0 4px 15px rgba(40,167,69,0.3); }
            50% { box-shadow: 0 8px 25px rgba(40,167,69,0.5); }
        }

        .delivery-info-enhanced {
            background: rgba(255,255,255,0.95);
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .delivery-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }

        .delivery-item:last-child {
            border-bottom: none;
        }

        .delivery-item i {
            width: 20px;
        }

        .farmer-notice {
            border-left: 4px solid #17a2b8;
            background: rgba(23,162,184,0.1);
        }

        .login-prompt-enhanced {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 15px;
            overflow: hidden;
        }

        /* Enhanced Tabs */
        .nav-tabs-enhanced {
            border: none;
            background: rgba(255,255,255,0.95);
            border-radius: 10px;
            padding: 0.5rem;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .nav-tabs-enhanced .nav-link {
            border: none;
            border-radius: 8px;
            color: #6c757d;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .nav-tabs-enhanced .nav-link:hover {
            background: rgba(40,167,69,0.1);
            color: #28a745;
        }

        .nav-tabs-enhanced .nav-link.active {
            background: #28a745;
            color: white;
        }

        .tab-content-enhanced {
            background: rgba(255,255,255,0.95);
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            margin-top: 1rem;
        }

        .spec-category {
            background: rgba(255,255,255,0.95);
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            height: 100%;
        }

        .spec-category-title {
            color: #28a745;
            font-weight: 700;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid rgba(40,167,69,0.2);
        }

        .spec-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }

        .spec-item:last-child {
            border-bottom: none;
        }

        .spec-key {
            font-weight: 600;
            color: #2d3436;
        }

        .spec-value {
            color: #636e72;
        }

        .review-item-enhanced {
            background: rgba(255,255,255,0.95);
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }

        .reviewer-name {
            color: #2d3436;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .farmer-info-enhanced,
        .contact-farmer-enhanced {
            background: rgba(255,255,255,0.95);
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            height: 100%;
        }

        .farmer-detail-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }

        .farmer-detail-item:last-child {
            border-bottom: none;
        }

        .farmer-detail-item i {
            width: 20px;
            font-size: 1.2rem;
        }

        .farmer-detail-item strong {
            color: #2d3436;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
            display: block;
        }

        .farmer-detail-item p {
            margin: 0;
            color: #636e72;
        }

        .contact-buttons .btn {
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .contact-buttons .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        /* Image Zoom Modal */
        .image-zoom-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .zoom-overlay {
            position: relative;
            max-width: 90%;
            max-height: 90%;
            cursor: pointer;
        }

        .zoomed-image {
            max-width: 100%;
            max-height: 100%;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        .close-zoom {
            position: absolute;
            top: -40px;
            right: 0;
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .close-zoom:hover {
            background: rgba(255,255,255,0.3);
            transform: scale(1.1);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .product-title-enhanced {
                font-size: 1.8rem;
            }

            .current-price-enhanced .amount {
                font-size: 2rem;
            }

            .quantity-controls {
                max-width: 100%;
            }

            .action-buttons-enhanced .row {
                gap: 0.5rem;
            }

            .spec-category {
                margin-bottom: 1rem;
            }
        }
    </style>
</body>
</html>
