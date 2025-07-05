<?php
include 'config/session.php';
include 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get filters
$category_filter = $_GET['category'] ?? '';
$search_query = $_GET['search'] ?? '';
$sort_by = $_GET['sort'] ?? 'newest';
$price_min = $_GET['price_min'] ?? '';
$price_max = $_GET['price_max'] ?? '';
$location_filter = $_GET['location'] ?? '';
$organic_only = isset($_GET['organic']);

// Build query
$where_conditions = ["p.status = 'active'"];
$params = [];

if ($category_filter) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_filter;
}

if ($search_query) {
    $where_conditions[] = "(p.title LIKE ? OR p.description LIKE ? OR p.location LIKE ? OR u.full_name LIKE ?)";
    $search_param = "%$search_query%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($price_min) {
    $where_conditions[] = "p.price >= ?";
    $params[] = $price_min;
}

if ($price_max) {
    $where_conditions[] = "p.price <= ?";
    $params[] = $price_max;
}

if ($location_filter) {
    $where_conditions[] = "p.location LIKE ?";
    $params[] = "%$location_filter%";
}

if ($organic_only) {
    $where_conditions[] = "p.is_organic = 1";
}

$where_clause = implode(' AND ', $where_conditions);

// Sort options
$order_by = match($sort_by) {
    'price_low' => 'p.price ASC',
    'price_high' => 'p.price DESC',
    'name' => 'p.title ASC',
    'rating' => 'p.rating DESC',
    'popular' => 'p.views DESC',
    default => 'p.created_at DESC'
};

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total 
                FROM products p 
                JOIN categories c ON p.category_id = c.id 
                JOIN users u ON p.farmer_id = u.id 
                WHERE $where_clause";
$stmt = $db->prepare($count_query);
$stmt->execute($params);
$total_products = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Pagination
$page = $_GET['page'] ?? 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;
$total_pages = ceil($total_products / $per_page);

$query = "SELECT p.*, c.name as category_name, u.full_name as farmer_name 
          FROM products p 
          JOIN categories c ON p.category_id = c.id 
          JOIN users u ON p.farmer_id = u.id 
          WHERE $where_clause 
          ORDER BY $order_by 
          LIMIT $per_page OFFSET $offset";

$stmt = $db->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for filter
$query = "SELECT * FROM categories WHERE is_active = 1 ORDER BY name";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique locations for filter
$query = "SELECT DISTINCT location FROM products WHERE status = 'active' AND location IS NOT NULL ORDER BY location";
$stmt = $db->prepare($query);
$stmt->execute();
$locations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace - Bihar ka Bazar | Fresh Farm Products from Bihar</title>
    <meta name="description" content="Browse fresh agricultural products from Bihar's farmers. Organic fruits, vegetables, grains, and fertilizers available for direct purchase.">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8" data-aos="fade-right">
                    <h1 class="display-4 fw-bold mb-3">Marketplace</h1>
                    <p class="lead mb-0">Discover fresh products from Bihar's finest farmers</p>
                    <nav aria-label="breadcrumb" class="mt-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php" class="text-white-50">Home</a></li>
                            <li class="breadcrumb-item active text-white">Marketplace</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-md-4 text-md-end" data-aos="fade-left">
                    <div class="d-flex align-items-center justify-content-md-end">
                        <i class="fas fa-store fa-5x" style="color: white; opacity: 0.7;"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Rest of the marketplace content remains the same -->
    <!-- Filters and Search -->
    <section class="py-4 bg-light">
        <div class="container">
            <form method="GET" id="filterForm">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search Products</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" id="search"
                                   placeholder="Search products..." 
                                   value="<?php echo htmlspecialchars($search_query); ?>">
                            <button class="btn btn-success" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" name="category" id="category">
                            <option value="">All Categories</option>
                            <?php foreach($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" 
                                        <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo $category['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="location" class="form-label">Location</label>
                        <select class="form-select" name="location" id="location">
                            <option value="">All Locations</option>
                            <?php foreach($locations as $location): ?>
                                <option value="<?php echo $location['location']; ?>" 
                                        <?php echo $location_filter == $location['location'] ? 'selected' : ''; ?>>
                                    <?php echo $location['location']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="sort" class="form-label">Sort By</label>
                        <select class="form-select" name="sort" id="sort">
                            <option value="newest" <?php echo $sort_by === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="price_low" <?php echo $sort_by === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo $sort_by === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="name" <?php echo $sort_by === 'name' ? 'selected' : ''; ?>>Name A-Z</option>
                            <option value="rating" <?php echo $sort_by === 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                            <option value="popular" <?php echo $sort_by === 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Price Range (₹)</label>
                        <div class="row g-1">
                            <div class="col-6">
                                <input type="number" class="form-control form-control-sm" name="price_min" 
                                       placeholder="Min" value="<?php echo htmlspecialchars($price_min); ?>">
                            </div>
                            <div class="col-6">
                                <input type="number" class="form-control form-control-sm" name="price_max" 
                                       placeholder="Max" value="<?php echo htmlspecialchars($price_max); ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="organic" id="organic" 
                                   <?php echo $organic_only ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="organic">
                                <i class="fas fa-leaf text-success me-1"></i>Organic Products Only
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <button type="submit" class="btn btn-success me-2">
                            <i class="fas fa-filter me-1"></i>Apply Filters
                        </button>
                        <a href="marketplace.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Clear All
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Products Grid -->
    <section class="py-5">
        <div class="container">
            <?php if (empty($products)): ?>
                <div class="text-center py-5" data-aos="fade-up">
                    <i class="fas fa-search fa-4x text-muted mb-4"></i>
                    <h3 class="text-muted">No products found</h3>
                    <p class="text-muted">Try adjusting your search criteria or browse all categories.</p>
                    <a href="marketplace.php" class="btn btn-success">View All Products</a>
                </div>
            <?php else: ?>
                <div class="row mb-4" data-aos="fade-up">
                    <div class="col-md-6">
                        <h4>Found <?php echo $total_products; ?> product(s)</h4>
                        <?php if ($search_query): ?>
                            <p class="text-muted">Search results for: <strong>"<?php echo htmlspecialchars($search_query); ?>"</strong></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <small class="text-muted">
                            Showing <?php echo (($page - 1) * $per_page) + 1; ?> - <?php echo min($page * $per_page, $total_products); ?> of <?php echo $total_products; ?> products
                        </small>
                    </div>
                </div>

                <div class="row g-4">
                    <?php foreach($products as $index => $product): ?>
                        <div class="col-md-6 col-lg-4 col-xl-3" data-aos="fade-up" data-aos-delay="<?php echo ($index % 8) * 100; ?>">
                            <div class="product-card h-100">
                                <div class="product-image">
                                    <img src="/placeholder.svg?height=200&width=250&text=<?php echo urlencode($product['title']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['title']); ?>" 
                                         class="img-fluid">
                                    <?php if($product['is_organic']): ?>
                                        <span class="organic-badge">Organic</span>
                                    <?php endif; ?>
                                    <?php if($product['quantity_available'] <= 5): ?>
                                        <span class="low-stock-badge">Low Stock</span>
                                    <?php endif; ?>
                                    <div class="product-actions">
                                        <?php if (isLoggedIn() && isBuyer()): ?>
                                            <button class="btn btn-sm btn-outline-light" onclick="addToWishlist(<?php echo $product['id']; ?>)" title="Add to Wishlist">
                                                <i class="fas fa-heart"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-light" onclick="addToCart(<?php echo $product['id']; ?>)" title="Add to Cart">
                                                <i class="fas fa-shopping-cart"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="product-content">
                                    <div class="product-category">
                                        <small class="text-success fw-bold"><?php echo $product['category_name']; ?></small>
                                    </div>
                                    <h6 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h6>
                                    <p class="product-description">
                                        <?php echo htmlspecialchars(substr($product['description'], 0, 80)) . '...'; ?>
                                    </p>
                                    <div class="product-meta">
                                        <p class="product-location mb-1">
                                            <i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($product['location']); ?>
                                        </p>
                                        <p class="product-farmer mb-2">
                                            <i class="fas fa-user me-1"></i>By <?php echo htmlspecialchars($product['farmer_name']); ?>
                                        </p>
                                        <p class="product-stock mb-2">
                                            <i class="fas fa-box me-1"></i><?php echo $product['quantity_available']; ?> <?php echo $product['unit']; ?> available
                                        </p>
                                    </div>
                                    <div class="product-rating mb-2">
                                        <div class="stars">
                                            <?php for($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $product['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <small class="text-muted">(<?php echo $product['total_reviews']; ?> reviews) • <?php echo $product['views']; ?> views</small>
                                    </div>
                                    <div class="product-price mb-3">
                                        ₹<?php echo number_format($product['price'], 2); ?> / <?php echo $product['unit']; ?>
                                    </div>
                                    <div class="product-actions-bottom">
                                        <a href="product-details.php?id=<?php echo $product['id']; ?>" 
                                           class="btn btn-success btn-sm w-100 mb-2">
                                            <i class="fas fa-eye me-1"></i>View Details
                                        </a>
                                        <?php if (isLoggedIn() && isBuyer()): ?>
                                            <button class="btn btn-outline-success btn-sm w-100" 
                                                    onclick="quickOrder(<?php echo $product['id']; ?>)">
                                                <i class="fas fa-shopping-cart me-1"></i>Quick Order
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Products pagination" class="mt-5">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        function quickOrder(productId) {
            if (!<?php echo isLoggedIn() ? 'true' : 'false'; ?>) {
                window.location.href = 'login.php';
                return;
            }
            
            window.location.href = `product-details.php?id=${productId}#order-form`;
        }

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

        // Auto-submit form on filter change
        document.querySelectorAll('#filterForm select').forEach(select => {
            select.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        });

        // Auto-submit form on checkbox change
        document.getElementById('organic').addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    </script>
</body>
</html>
