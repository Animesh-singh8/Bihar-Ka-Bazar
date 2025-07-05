<?php
include 'config/session.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Bihar ka Bazar | Connecting Farmers with Consumers</title>
    <meta name="description" content="Learn about Bihar ka Bazar's mission to connect Bihar's farmers directly with consumers. Powered by Bindisa Agritech - Innovate, Cultivate, Elevate.">
    
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
                    <h1 class="display-4 fw-bold mb-3">About Bihar ka Bazar</h1>
                    <p class="lead mb-0">Empowering Bihar's agricultural community through technology and direct connections</p>
                    <nav aria-label="breadcrumb" class="mt-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php" class="text-white-50">Home</a></li>
                            <li class="breadcrumb-item active text-white">About</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-md-4 text-md-end" data-aos="fade-left">
                    <div class="d-flex align-items-center justify-content-md-end">
                        <i class="fas fa-info-circle fa-5x" style="color: white; opacity: 0.7;"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission Section -->
    <section class="py-5">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6" data-aos="fade-right">
                    <h2 class="section-title">Our Mission</h2>
                    <p class="lead">To revolutionize Bihar's agricultural marketplace by connecting farmers directly with consumers, ensuring fair prices, fresh produce, and sustainable farming practices.</p>
                    <p>Bihar ka Bazar is more than just an online marketplace - it's a movement towards empowering local farmers and providing consumers with access to the freshest, most authentic agricultural products from Bihar's fertile lands.</p>
                    <div class="row g-3 mt-4">
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span>Direct farmer connections</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span>Fair pricing for all</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span>Organic certification</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span>Quality assurance</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <img src="assets/images/about/mission.jpg" alt="Our Mission" class="img-fluid rounded-3 shadow">
                </div>
            </div>
        </div>
    </section>

    <!-- Bindisa Agritech Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6" data-aos="fade-right">
                    <img src="assets/images/about/bindisa-agritech.jpg" alt="Bindisa Agritech" class="img-fluid rounded-3 shadow">
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <h2 class="section-title">Powered by Bindisa Agritech</h2>
                    <div class="motto-box p-4 rounded-3 mb-4">
                        <h5 class="text-center mb-3">Our Company Motto</h5>
                        <h3 class="text-center mb-3">Innovate • Cultivate • Elevate</h3>
                        <p class="text-center mb-0">We believe in using innovative technology to cultivate better farming practices and elevate the entire agricultural ecosystem of Bihar.</p>
                    </div>
                    <p>Bindisa Agritech is a technology-driven company focused on transforming agriculture through digital solutions. Founded in 2025, we are committed to bridging the gap between traditional farming and modern technology.</p>
                    <p>Our platform leverages cutting-edge technology to create a seamless marketplace where farmers can showcase their products and consumers can access fresh, authentic produce directly from the source.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="py-5">
        <div class="container">
            <div class="row text-center mb-5" data-aos="fade-up">
                <div class="col-12">
                    <h2 class="section-title">Our Core Values</h2>
                    <p class="section-subtitle">The principles that guide everything we do</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h4>Trust & Transparency</h4>
                        <p>We believe in building trust through transparent practices, honest communication, and reliable service for both farmers and consumers.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <h4>Sustainability</h4>
                        <p>Promoting sustainable farming practices that protect our environment while ensuring long-term agricultural prosperity for Bihar.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4>Community First</h4>
                        <p>Our commitment to strengthening local communities by supporting farmers and providing consumers with access to local produce.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row text-center mb-5" data-aos="fade-up">
                <div class="col-12">
                    <h2 class="section-title">Our Impact</h2>
                    <p class="section-subtitle">Making a difference in Bihar's agricultural landscape</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-item-new text-center">
                        <h3>500+</h3>
                        <small>Farmers Empowered</small>
                    </div>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-item-new text-center">
                        <h3>10,000+</h3>
                        <small>Happy Customers</small>
                    </div>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
                    <div class="stat-item-new text-center">
                        <h3>50+</h3>
                        <small>Districts Covered</small>
                    </div>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="400">
                    <div class="stat-item-new text-center">
                        <h3>₹2Cr+</h3>
                        <small>Farmer Income Generated</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Vision Section -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center" data-aos="fade-up">
                <div class="col-lg-8 text-center">
                    <h2 class="section-title">Our Vision for 2025 & Beyond</h2>
                    <p class="lead mb-4">To become Bihar's leading agricultural marketplace, setting the standard for farmer-consumer connections across India.</p>
                    <div class="row g-4 mt-4">
                        <div class="col-md-6">
                            <div class="vision-item">
                                <i class="fas fa-globe text-success fa-2x mb-3"></i>
                                <h5>Expand Reach</h5>
                                <p>Connecting farmers from every district of Bihar to consumers across India and beyond.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="vision-item">
                                <i class="fas fa-mobile-alt text-success fa-2x mb-3"></i>
                                <h5>Technology Innovation</h5>
                                <p>Developing cutting-edge mobile and web solutions for seamless agricultural commerce.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="vision-item">
                                <i class="fas fa-graduation-cap text-success fa-2x mb-3"></i>
                                <h5>Farmer Education</h5>
                                <p>Providing training and resources to help farmers adopt modern, sustainable practices.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="vision-item">
                                <i class="fas fa-award text-success fa-2x mb-3"></i>
                                <h5>Quality Standards</h5>
                                <p>Establishing industry-leading quality and certification standards for agricultural products.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact CTA -->
    <section class="cta-section">
        <div class="container">
            <div class="row text-center" data-aos="fade-up">
                <div class="col-12">
                    <h2 class="text-white mb-4">Join Our Mission</h2>
                    <p class="text-white-50 mb-4">Be part of Bihar's agricultural revolution. Whether you're a farmer or a consumer, we welcome you to our community.</p>
                    <div class="cta-buttons">
                        <a href="contact.php" class="btn btn-light btn-lg me-3">
                            <i class="fas fa-envelope me-2"></i>Contact Us
                        </a>
                        <a href="register.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-user-plus me-2"></i>Join Today
                        </a>
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
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
    </script>

    <style>
        .vision-item {
            padding: 2rem;
            background: rgba(255,255,255,0.95);
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
        }

        .vision-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
    </style>
</body>
</html>
