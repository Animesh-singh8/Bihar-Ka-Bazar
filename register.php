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
$user_type = $_GET['type'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $pincode = trim($_POST['pincode']);
    $user_type = $_POST['user_type'];
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($full_name) || empty($user_type)) {
        $error = 'Please fill in all required fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = 'Username can only contain letters, numbers, and underscores';
    } else {
        // Check if username or email already exists
        $query = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            $error = 'Username or email already exists';
        } else {
            // Insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (username, email, password, full_name, phone, address, city, state, pincode, user_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([$username, $email, $hashed_password, $full_name, $phone, $address, $city, $state, $pincode, $user_type])) {
                $success = 'Registration successful! You can now login with your credentials.';
                
                // Send welcome email
                $welcome_message = "
                <h2>Welcome to Bihar ka Bazar!</h2>
                <p>Dear $full_name,</p>
                <p>Thank you for joining Bihar ka Bazar as a " . ucfirst($user_type) . ".</p>
                <p>You can now login and start " . ($user_type === 'farmer' ? 'selling your products' : 'buying fresh products') . ".</p>
                <p>Best regards,<br>Bihar ka Bazar Team<br>Powered by Bindisa Agritech</p>
                ";
                
                sendEmail($email, 'Welcome to Bihar ka Bazar!', $welcome_message);
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Bihar ka Bazar</title>
    <meta name="description" content="Join Bihar ka Bazar - Register as a farmer to sell products or as a buyer to purchase fresh produce">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <section class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-7">
                    <div class="card shadow-lg border-0 rounded-4" data-aos="fade-up">
                        <div class="card-body p-5">
                            <div class="text-center mb-4">
                                <i class="fas fa-user-plus fa-3x text-success mb-3"></i>
                                <h2 class="card-title">Join Bihar ka Bazar</h2>
                                <p class="text-muted">Create your account to get started</p>
                            </div>

                            <?php if ($error): ?>
                                <div class="alert alert-danger" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($success): ?>
                                <div class="alert alert-success" role="alert">
                                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                                    <div class="mt-2">
                                        <a href="login.php" class="btn btn-success btn-sm">Login Now</a>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <form method="POST" id="registerForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="username" class="form-label">
                                            <i class="fas fa-user me-2"></i>Username *
                                        </label>
                                        <input type="text" class="form-control" id="username" name="username" 
                                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                                        <div class="form-text">Only letters, numbers, and underscores allowed</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">
                                            <i class="fas fa-envelope me-2"></i>Email *
                                        </label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="full_name" class="form-label">
                                        <i class="fas fa-id-card me-2"></i>Full Name *
                                    </label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" 
                                           value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label">
                                            <i class="fas fa-lock me-2"></i>Password *
                                        </label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                        <div class="form-text">Minimum 6 characters</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="confirm_password" class="form-label">
                                            <i class="fas fa-lock me-2"></i>Confirm Password *
                                        </label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">
                                            <i class="fas fa-phone me-2"></i>Phone Number
                                        </label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="user_type" class="form-label">
                                            <i class="fas fa-users me-2"></i>I am a *
                                        </label>
                                        <select class="form-select" id="user_type" name="user_type" required>
                                            <option value="">Select Type</option>
                                            <option value="farmer" <?php echo ($user_type === 'farmer' || ($_POST['user_type'] ?? '') === 'farmer') ? 'selected' : ''; ?>>
                                                Farmer (Seller)
                                            </option>
                                            <option value="buyer" <?php echo ($user_type === 'buyer' || ($_POST['user_type'] ?? '') === 'buyer') ? 'selected' : ''; ?>>
                                                Buyer (Consumer)
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="city" class="form-label">
                                            <i class="fas fa-city me-2"></i>City
                                        </label>
                                        <input type="text" class="form-control" id="city" name="city" 
                                               value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="state" class="form-label">
                                            <i class="fas fa-map me-2"></i>State
                                        </label>
                                        <select class="form-select" id="state" name="state">
                                            <option value="">Select State</option>
                                            <option value="Bihar" <?php echo ($_POST['state'] ?? '') === 'Bihar' ? 'selected' : ''; ?>>Bihar</option>
                                            <option value="Jharkhand" <?php echo ($_POST['state'] ?? '') === 'Jharkhand' ? 'selected' : ''; ?>>Jharkhand</option>
                                            <option value="West Bengal" <?php echo ($_POST['state'] ?? '') === 'West Bengal' ? 'selected' : ''; ?>>West Bengal</option>
                                            <option value="Uttar Pradesh" <?php echo ($_POST['state'] ?? '') === 'Uttar Pradesh' ? 'selected' : ''; ?>>Uttar Pradesh</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="pincode" class="form-label">
                                            <i class="fas fa-map-pin me-2"></i>Pincode
                                        </label>
                                        <input type="text" class="form-control" id="pincode" name="pincode" 
                                               value="<?php echo htmlspecialchars($_POST['pincode'] ?? ''); ?>">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="address" class="form-label">
                                        <i class="fas fa-map-marker-alt me-2"></i>Address
                                    </label>
                                    <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                                </div>

                                <div class="mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="terms" required>
                                        <label class="form-check-label" for="terms">
                                            I agree to the <a href="terms-conditions.php" target="_blank">Terms & Conditions</a> and <a href="privacy-policy.php" target="_blank">Privacy Policy</a>
                                        </label>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-success btn-lg w-100 mb-3">
                                    <i class="fas fa-user-plus me-2"></i>Create Account
                                </button>
                            </form>

                            <div class="text-center">
                                <p class="mb-0">Already have an account? 
                                    <a href="login.php" class="text-success text-decoration-none fw-bold">Sign in here</a>
                                </p>
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
        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const username = document.getElementById('username').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                showToast('Passwords do not match', 'danger');
                return;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                showToast('Password must be at least 6 characters long', 'danger');
                return;
            }
            
            if (!/^[a-zA-Z0-9_]+$/.test(username)) {
                e.preventDefault();
                showToast('Username can only contain letters, numbers, and underscores', 'danger');
                return;
            }
            
            if (!validateForm('registerForm')) {
                e.preventDefault();
            }
        });

        // Real-time password validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
            }
        });

        // Username validation
        document.getElementById('username').addEventListener('input', function() {
            const username = this.value;
            
            if (username && !/^[a-zA-Z0-9_]+$/.test(username)) {
                this.setCustomValidity('Username can only contain letters, numbers, and underscores');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
            }
        });
    </script>
</body>
</html>
