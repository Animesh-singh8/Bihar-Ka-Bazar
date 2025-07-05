<?php 
include 'config/session.php';
include 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get featured products
$query = "SELECT p.*, c.name as category_name, u.full_name as farmer_name 
          FROM products p 
          JOIN categories c ON p.category_id = c.id 
          JOIN users u ON p.farmer_id = u.id 
          WHERE p.status = 'active' 
          ORDER BY p.created_at DESC 
          LIMIT 8";
$stmt = $db->prepare($query);
$stmt->execute();
$featured_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories
$query = "SELECT * FROM categories WHERE is_active = 1 ORDER BY name";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM users WHERE user_type = 'farmer') as total_farmers,
    (SELECT COUNT(*) FROM users WHERE user_type = 'buyer') as total_buyers,
    (SELECT COUNT(*) FROM products WHERE status = 'active') as total_products,
    (SELECT COUNT(*) FROM orders WHERE order_status = 'delivered') as total_orders";
$stmt = $db->prepare($stats_query);
$stmt->execute();
$stats = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bihar ka Bazar - Powered by Bindisa Agritech | Fresh Farm Products Direct from Bihar</title>
    <meta name="description" content="Bihar ka Bazar connects Bihar's farmers directly with consumers. Buy fresh, organic agricultural products from local farmers. Powered by Bindisa Agritech - Innovate, Cultivate, Elevate.">
    <meta name="keywords" content="Bihar agriculture, organic farming, fresh produce, Bihar farmers, online marketplace, Bindisa Agritech">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    <style>
        .animated-btn {
            position: relative;
            overflow: hidden;
            transition: all 0.4s ease;
            animation: buttonFloat 3s ease-in-out infinite;
        }

        @keyframes buttonFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-3px); }
        }

        .animated-btn:hover {
            animation: none;
            transform: translateY(-5px) scale(1.05);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Success/Info Messages -->
    <?php if (isset($_GET['message'])): ?>
        <div class="alert alert-success alert-dismissible fade show m-0" role="alert">
            <div class="container">
                <i class="fas fa-check-circle me-2"></i>
                <?php 
                    echo match($_GET['message']) {
                        'logged_out' => 'You have been successfully logged out.',
                        'registered' => 'Registration successful! You can now login.',
                        default => 'Operation completed successfully!'
                    };
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Hero Section - RESTORED WITH ALL ANIMATIONS -->
    <section class="hero-section-new">
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-6" data-aos="fade-right">
                    <div class="hero-content-new">
                        <h1 class="hero-title-new">
                            Welcome to <span class="bihar-ka-bazar-golden-new">Bihar ka Bazar</span>
                        </h1>
                        <p class="hero-subtitle-new">
                            Connecting Bihar's farmers directly with consumers. Fresh produce, organic fertilizers, and authentic agricultural products from the heart of Bihar.
                        </p>
                        <div class="hero-stats-new mb-4">
                            <div class="row text-center">
                                <div class="col-3">
                                    <div class="stat-item-new">
                                        <h3><?php echo $stats['total_farmers']; ?>+</h3>
                                        <small>Farmers</small>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="stat-item-new">
                                        <h3><?php echo $stats['total_buyers']; ?>+</h3>
                                        <small>Buyers</small>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="stat-item-new">
                                        <h3><?php echo $stats['total_products']; ?>+</h3>
                                        <small>Products</small>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="stat-item-new">
                                        <h3><?php echo $stats['total_orders']; ?>+</h3>
                                        <small>Orders</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="hero-buttons-new">
                            <a href="marketplace.php" class="btn btn-success btn-lg me-3 hero-btn-shop animated-btn" role="button">
                                <i class="fas fa-shopping-cart me-2"></i>Shop Now
                            </a>
                            <?php if (!isLoggedIn()): ?>
                            <a href="register.php" class="btn btn-outline-success btn-lg hero-btn-join animated-btn" role="button">
                                <i class="fas fa-user-plus me-2"></i>Join Us
                            </a>
                            <?php endif; ?>
                        </div>
                        <div class="hero-motto-new mt-4">
                            <p class="mb-0"><strong>Our Motto:</strong> <em>Innovate • Cultivate • Elevate</em></p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="hero-image-new">
                        <img src="assets/images/hero/bihar-farmer.jpg" alt="Bihar Farmer" class="img-fluid rounded-3 shadow-lg">
                        <div class="hero-badge-new">
                            <i class="fas fa-leaf"></i>
                            <span>100% Organic</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row text-center mb-5" data-aos="fade-up">
                <div class="col-12">
                    <h2 class="section-title">Why Choose Bihar ka Bazar?</h2>
                    <p class="section-subtitle">Empowering farmers, serving consumers with the best of Bihar</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-seedling"></i>
                        </div>
                        <h4>Fresh & Organic</h4>
                        <p>Direct from farms to your table. 100% fresh and organic products from Bihar's fertile lands with proper certifications.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h4>Direct Connection</h4>
                        <p>Connect directly with farmers. No middlemen, fair prices for both farmers and consumers. Support local agriculture.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <h4>Fast Delivery</h4>
                        <p>Quick and reliable delivery across Bihar. Supporting local logistics and communities with timely service.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="py-5">
        <div class="container">
            <div class="row text-center mb-5" data-aos="fade-up">
                <div class="col-12">
                    <h2 class="section-title">Product Categories</h2>
                    <p class="section-subtitle">Explore our wide range of agricultural products</p>
                </div>
            </div>
            <div class="row g-4">
                <?php foreach($categories as $index => $category): ?>
                <div class="col-md-6 col-lg-4" data-aos="zoom-in" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
                    <div class="category-card">
                        <div class="category-image">
                            <img src="assets/images/categories/<?php echo $category['image']; ?>" alt="<?php echo $category['name']; ?>" class="img-fluid">
                            <div class="category-overlay">
                                <a href="marketplace.php?category=<?php echo $category['id']; ?>" class="btn btn-success">
                                    View Products <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                        <div class="category-content">
                            <h5><?php echo $category['name']; ?></h5>
                            <p><?php echo $category['description']; ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row text-center mb-5" data-aos="fade-up">
                <div class="col-12">
                    <h2 class="section-title">Featured Products</h2>
                    <p class="section-subtitle">Fresh arrivals from our trusted farmers</p>
                </div>
            </div>
            <div class="row g-4">
                <?php foreach($featured_products as $index => $product): ?>
                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
                    <div class="product-card">
                        <div class="product-image">
                            <img src="assets/images/products/<?php echo $product['image']; ?>" alt="<?php echo $product['title']; ?>" class="img-fluid">
                            <?php if($product['is_organic']): ?>
                            <span class="organic-badge">Organic</span>
                            <?php endif; ?>
                            <div class="product-actions">
                                <?php if (isLoggedIn() && isBuyer()): ?>
                                    <button class="btn btn-sm btn-outline-light" onclick="addToWishlist(<?php echo $product['id']; ?>)">
                                        <i class="fas fa-heart"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-light" onclick="addToCart(<?php echo $product['id']; ?>)">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="product-content">
                            <div class="product-category">
                                <small class="text-success fw-bold"><?php echo $product['category_name']; ?></small>
                            </div>
                            <h6 class="product-title"><?php echo $product['title']; ?></h6>
                            <p class="product-location">
                                <i class="fas fa-map-marker-alt me-1"></i><?php echo $product['location']; ?>
                            </p>
                            <p class="product-farmer">
                                <i class="fas fa-user me-1"></i>By <?php echo $product['farmer_name']; ?>
                            </p>
                            <div class="product-rating mb-2">
                                <div class="stars">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $product['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <small class="text-muted">(<?php echo $product['total_reviews']; ?> reviews)</small>
                            </div>
                            <div class="product-price">
                                ₹<?php echo number_format($product['price'], 2); ?> / <?php echo $product['unit']; ?>
                            </div>
                            <a href="product-details.php?id=<?php echo $product['id']; ?>" class="btn btn-success btn-sm w-100 mt-2">
                                <i class="fas fa-eye me-1"></i>View Details
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4" data-aos="fade-up">
                <a href="marketplace.php" class="btn btn-outline-success btn-lg">
                    View All Products <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-5">
        <div class="container">
            <div class="row text-center mb-5" data-aos="fade-up">
                <div class="col-12">
                    <h2 class="section-title">What Our Users Say</h2>
                    <p class="section-subtitle">Real experiences from farmers and buyers</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <div class="stars mb-3">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <p>"Bihar ka Bazar has transformed my farming business. I can now sell directly to customers and get better prices for my organic produce."</p>
                        </div>
                        <div class="testimonial-author">
                            <img src="assets/images/testimonials/farmer1.jpg" alt="Ram Kumar" class="rounded-circle">
                            <div>
                                <h6>Ram Kumar Singh</h6>
                                <small class="text-muted">Farmer, Muzaffarpur</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <div class="stars mb-3">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <p>"Fresh vegetables delivered right to my doorstep. The quality is amazing and I love supporting local farmers directly."</p>
                        </div>
                        <div class="testimonial-author">
                            <img src="assets/images/testimonials/buyer1.jpg" alt="Priya Sharma" class="rounded-circle">
                            <div>
                                <h6>Priya Sharma</h6>
                                <small class="text-muted">Customer, Patna</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <div class="stars mb-3">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <p>"The platform is easy to use and the delivery is always on time. Great initiative by Bindisa Agritech!"</p>
                        </div>
                        <div class="testimonial-author">
                            <img src="assets/images/testimonials/buyer2.jpg" alt="Amit Kumar" class="rounded-circle">
                            <div>
                                <h6>Amit Kumar</h6>
                                <small class="text-muted">Customer, Gaya</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="row text-center" data-aos="fade-up">
                <div class="col-12">
                    <h2 class="text-white mb-4">Ready to Start Trading?</h2>
                    <p class="text-white-50 mb-4">Join thousands of farmers and buyers on Bihar ka Bazar</p>
                    <?php if (!isLoggedIn()): ?>
                    <div class="cta-buttons">
                        <a href="register.php?type=farmer" class="btn btn-light btn-lg me-3">
                            <i class="fas fa-seedling me-2"></i>Sell as Farmer
                        </a>
                        <a href="register.php?type=buyer" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-shopping-cart me-2"></i>Buy Products
                        </a>
                    </div>
                    <?php else: ?>
                    <a href="<?php echo isFarmer() ? 'dashboard/farmer.php' : 'marketplace.php'; ?>" class="btn btn-light btn-lg">
                        <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center" data-aos="fade-up">
                <div class="col-lg-6 text-center">
                    <h3 class="mb-3">Stay Updated</h3>
                    <p class="mb-4">Subscribe to our newsletter for the latest updates on fresh products and farming tips.</p>
                    <form class="newsletter-form" id="newsletterForm">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Enter your email" required>
                            <button class="btn btn-success" type="submit">
                                <i class="fas fa-paper-plane me-1"></i>Subscribe
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="assets/js/main.js"></script>
    
    <script>
        // Initialize AOS with restored settings
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 100
        });

        // Newsletter subscription
        document.getElementById('newsletterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
            
            fetch('api/newsletter.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email: email })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Successfully subscribed to newsletter!', 'success');
                    this.reset();
                } else {
                    showToast(data.message || 'Subscription failed', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Subscription failed', 'danger');
            });
        });

        // Add to wishlist function
        function addToWishlist(productId) {
            <?php if (isLoggedIn() && isBuyer()): ?>
            fetch('api/wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'add',
                    product_id: productId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Added to wishlist!', 'success');
                } else {
                    showToast(data.message || 'Error adding to wishlist', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error adding to wishlist', 'danger');
            });
            <?php else: ?>
            window.location.href = 'login.php';
            <?php endif; ?>
        }

        // Add to cart function
        function addToCart(productId) {
            <?php if (isLoggedIn() && isBuyer()): ?>
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
            <?php else: ?>
            window.location.href = 'login.php';
            <?php endif; ?>
        }
    </script>

<script>
// Ensure buttons are clickable and have proper events
document.addEventListener('DOMContentLoaded', function() {
    const animatedBtns = document.querySelectorAll('.animated-btn');
    
    animatedBtns.forEach(btn => {
        // Add click event
        btn.addEventListener('click', function(e) {
            // Add click animation
            this.style.transform = 'translateY(-2px) scale(1.02)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });
        
        // Add hover effects
        btn.addEventListener('mouseenter', function() {
            this.style.animation = 'none';
            this.style.transform = 'translateY(-5px) scale(1.05)';
            this.style.boxShadow = '0 15px 40px rgba(40, 167, 69, 0.4)';
        });
        
        btn.addEventListener('mouseleave', function() {
            this.style.animation = 'buttonFloat 3s ease-in-out infinite';
            this.style.transform = '';
            this.style.boxShadow = '';
        });
    });
});
</script>
</body>
</html>
