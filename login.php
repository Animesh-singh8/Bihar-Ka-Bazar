<?php
include 'config/session.php';
include 'config/database.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: " . (isFarmer() ? 'dashboard/farmer.php' : (isBuyer() ? 'dashboard/buyer.php' : 'dashboard/admin.php')));
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']);
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $query = "SELECT id, username, email, password, full_name, user_type, profile_image, is_active FROM users WHERE (username = ? OR email = ?) AND is_active = 1";
        $stmt = $db->prepare($query);
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            setUserSession($user);
            
            if ($remember_me) {
                setRememberToken($user['id']);
            }
            
            // Redirect based on user type
            $redirect_url = match($user['user_type']) {
                'farmer' => 'dashboard/farmer.php',
                'buyer' => 'dashboard/buyer.php',
                'admin' => 'dashboard/admin.php',
                default => 'index.php'
            };
            
            header("Location: " . $redirect_url);
            exit();
        } else {
            $error = 'Invalid username/email or password';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bihar ka Bazar</title>
    <meta name="description" content="Login to Bihar ka Bazar - Access your farmer or buyer account">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <section class="py-5 bg-light min-vh-100 d-flex align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card shadow-lg border-0 rounded-4" data-aos="fade-up">
                        <div class="card-body p-5">
                            <div class="text-center mb-4">
                                <i class="fas fa-sign-in-alt fa-3x text-success mb-3"></i>
                                <h2 class="card-title">Welcome Back</h2>
                                <p class="text-muted">Sign in to your Bihar ka Bazar account</p>
                            </div>

                            <?php if ($error): ?>
                                <div class="alert alert-danger" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($success): ?>
                                <div class="alert alert-success" role="alert">
                                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" id="loginForm">
                                <div class="mb-3">
                                    <label for="username" class="form-label">
                                        <i class="fas fa-user me-2"></i>Username or Email
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="username" name="username" 
                                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Password
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                            <i class="fas fa-eye" id="passwordToggle"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remember_me" name="remember_me">
                                        <label class="form-check-label" for="remember_me">
                                            Remember me for 30 days
                                        </label>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-success btn-lg w-100 mb-3">
                                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                                </button>
                            </form>

                            <div class="text-center">
                                <p class="mb-2">Don't have an account? 
                                    <a href="register.php" class="text-success text-decoration-none fw-bold">Sign up here</a>
                                </p>
                                <a href="forgot-password.php" class="text-muted text-decoration-none small">
                                    <i class="fas fa-key me-1"></i>Forgot your password?
                                </a>
                            </div>

                            <!-- Demo Accounts -->
                            <div class="mt-4 pt-4 border-top">
                                <h6 class="text-center text-muted mb-3">Demo Accounts</h6>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <button class="btn btn-outline-primary btn-sm w-100" onclick="fillDemoAccount('farmer')">
                                            <i class="fas fa-seedling me-1"></i>Demo Farmer
                                        </button>
                                    </div>
                                    <div class="col-6">
                                        <button class="btn btn-outline-info btn-sm w-100" onclick="fillDemoAccount('buyer')">
                                            <i class="fas fa-shopping-cart me-1"></i>Demo Buyer
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordToggle = document.getElementById('passwordToggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordToggle.classList.remove('fa-eye');
                passwordToggle.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordToggle.classList.remove('fa-eye-slash');
                passwordToggle.classList.add('fa-eye');
            }
        }

        function fillDemoAccount(type) {
            const usernameField = document.getElementById('username');
            const passwordField = document.getElementById('password');
            
            if (type === 'farmer') {
                usernameField.value = 'farmer1';
                passwordField.value = 'password';
            } else if (type === 'buyer') {
                usernameField.value = 'buyer1';
                passwordField.value = 'password';
            }
        }

        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            if (!validateForm('loginForm')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
