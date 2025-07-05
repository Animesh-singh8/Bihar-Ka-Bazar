<?php
// Get cart count for logged in buyers
$cart_count = 0;
if (isLoggedIn() && isBuyer()) {
    $database = new Database();
    $db = $database->getConnection();
    $query = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([getUserId()]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $cart_count = (int)($result['total'] ?? 0);
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-success fixed-top navbar-enhanced">
    <div class="container">
        <!-- Enhanced Logo with Golden Leaf -->
        <a class="navbar-brand fw-bold brand-enhanced" href="index.php">
            <i class="fas fa-leaf golden-leaf"></i>
            <span class="brand-text-enhanced">Bihar ka Bazar</span>
            <small class="d-block brand-subtitle">Powered by Bindisa Agritech</small>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link nav-link-enhanced" href="index.php">
                        <i class="fas fa-home me-1"></i>Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link-enhanced" href="marketplace.php">
                        <i class="fas fa-store me-1"></i>Marketplace
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link-enhanced" href="about.php">
                        <i class="fas fa-info-circle me-1"></i>About
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link-enhanced" href="contact.php">
                        <i class="fas fa-envelope me-1"></i>Contact
                    </a>
                </li>
            </ul>
            
            <!-- Enhanced Search Bar -->
            <form class="d-flex me-3" action="marketplace.php" method="GET">
                <div class="input-group search-enhanced">
                    <input class="form-control search-input" type="search" name="search" placeholder="Search products..." 
                           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    <button class="btn btn-warning search-btn" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
            
            <ul class="navbar-nav">
                <?php if (isLoggedIn()): ?>
                    <?php if (isBuyer()): ?>
                        <li class="nav-item">
                            <a class="nav-link nav-link-enhanced position-relative" href="wishlist.php">
                                <i class="fas fa-heart"></i>
                                <span class="d-none d-lg-inline ms-1">Wishlist</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-enhanced position-relative" href="cart.php">
                                <i class="fas fa-shopping-cart"></i>
                                <span class="d-none d-lg-inline ms-1">Cart</span>
                                <?php if ($cart_count > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark cart-badge">
                                        <?php echo $cart_count; ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-link-enhanced dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            <span class="d-none d-lg-inline"><?php echo htmlspecialchars(getFullName()); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-enhanced">
                            <?php if (isFarmer()): ?>
                                <li><a class="dropdown-item" href="dashboard/farmer.php">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                </a></li>
                                <li><a class="dropdown-item" href="my-products.php">
                                    <i class="fas fa-seedling me-2"></i>My Products
                                </a></li>
                            <?php elseif (isBuyer()): ?>
                                <li><a class="dropdown-item" href="dashboard/buyer.php">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                </a></li>
                                <li><a class="dropdown-item" href="my-orders.php">
                                    <i class="fas fa-shopping-bag me-2"></i>My Orders
                                </a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="profile.php">
                                <i class="fas fa-user me-2"></i>Profile
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="auth/logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link nav-link-enhanced" href="login.php">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-enhanced" href="register.php">
                            <i class="fas fa-user-plus me-1"></i>Register
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<style>
/* Enhanced Navbar Styles */
.navbar-enhanced {
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}

/* Enhanced Brand with Golden Leaf */
.brand-enhanced {
    font-size: 1.4rem !important; /* Reduced from 1.8rem */
    transition: all 0.3s ease;
}

.brand-enhanced:hover {
    transform: scale(1.05);
}

.golden-leaf {
    color: #ffd700;
    font-size: 1.5rem;
    margin-right: 0.5rem !important; /* Reduced from me-2 (1rem) to 0.5rem */
    animation: leafGlowSpin 3s ease-in-out infinite; /* Added spinning animation */
    filter: drop-shadow(0 0 8px #ffd700);
}

@keyframes leafGlowSpin {
    0% { 
        color: #ffd700;
        filter: drop-shadow(0 0 8px #ffd700);
        transform: rotate(0deg);
    }
    50% { 
        color: #ffed4e;
        filter: drop-shadow(0 0 12px #ffed4e);
        transform: rotate(180deg);
    }
    100% { 
        color: #ffd700;
        filter: drop-shadow(0 0 8px #ffd700);
        transform: rotate(360deg);
    }
}

.brand-text-enhanced {
    background: linear-gradient(45deg, #ffd700, #ffed4e, #ffc107, #ffd700);
    background-size: 300% 300%;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 700; /* Reduced from 800 */
    animation: goldShine 3s ease-in-out infinite;
    /* REMOVED: text-shadow, -webkit-text-stroke, filter */
    display: inline-block;
}

@keyframes goldShine {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

.brand-subtitle {
    font-size: 0.6rem; /* Reduced from 0.7rem */
    color: rgba(255,255,255,0.9);
    margin-top: -3px; /* Reduced from -5px */
    text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
}

/* Enhanced Navigation Links - WHITE with GOLDEN hover */
.nav-link-enhanced {
    color: white !important;
    font-weight: 600;
    padding: 0.75rem 1rem !important;
    border-radius: 8px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.nav-link-enhanced::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,215,0,0.2), transparent);
    transition: left 0.5s;
}

.nav-link-enhanced:hover {
    color: #ffd700 !important;
    background: rgba(255,215,0,0.1);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(255,215,0,0.3);
}

.nav-link-enhanced:hover::before {
    left: 100%;
}

/* Enhanced Search */
.search-enhanced {
    border-radius: 25px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.search-input {
    border: none;
    background: rgba(255,255,255,0.95);
    color: #333;
}

.search-input:focus {
    background: white;
    box-shadow: none;
    border: none;
}

.search-btn {
    border: none;
    background: #ffd700;
    color: #333;
    font-weight: bold;
    transition: all 0.3s ease;
}

.search-btn:hover {
    background: #ffed4e;
    transform: scale(1.05);
}

/* Enhanced Dropdown */
.dropdown-enhanced {
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    backdrop-filter: blur(10px);
    background: rgba(255,255,255,0.95);
}

.dropdown-enhanced .dropdown-item:hover {
    background: linear-gradient(45deg, #28a745, #20c997);
    color: white;
    transform: translateX(5px);
}

/* Cart Badge Animation */
.cart-badge {
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0) translateX(-50%); }
    40% { transform: translateY(-5px) translateX(-50%); }
    60% { transform: translateY(-3px) translateX(-50%); }
}
</style>
