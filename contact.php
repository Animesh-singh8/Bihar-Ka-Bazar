<?php
include 'config/session.php';
include 'config/database.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please fill in all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        $query = "INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$name, $email, $phone, $subject, $message])) {
            $success = 'Thank you for your message! We will get back to you soon.';
            
            // Send confirmation email
            $confirmation_message = "
            <h2>Thank you for contacting Bihar ka Bazar!</h2>
            <p>Dear $name,</p>
            <p>We have received your message and will respond within 24 hours.</p>
            <p><strong>Your Message:</strong></p>
            <p>$message</p>
            <p>Best regards,<br>Bihar ka Bazar Team<br>Powered by Bindisa Agritech</p>
            ";
            
            sendEmail($email, 'Message Received - Bihar ka Bazar', $confirmation_message);
        } else {
            $error = 'Failed to send message. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Bihar ka Bazar | Get in Touch</title>
    <meta name="description" content="Contact Bihar ka Bazar for support, inquiries, or feedback. We're here to help farmers and buyers connect better.">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="py-5 bg-success text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <h1 class="display-4 fw-bold mb-4">Contact Us</h1>
                    <p class="lead">We're here to help! Get in touch with our team for any questions, support, or feedback.</p>
                    <nav aria-label="breadcrumb" class="mt-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php" class="text-white-50">Home</a></li>
                            <li class="breadcrumb-item active text-white">Contact</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="contact-hero-image text-center">
                        <i class="fas fa-envelope fa-5x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Information -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="contact-info-card text-center h-100">
                        <div class="contact-icon mb-3">
                            <i class="fas fa-map-marker-alt fa-3x text-success"></i>
                        </div>
                        <h5>Visit Our Office</h5>
                        <p class="text-muted">
                            Bindisa Agritech Pvt. Ltd.<br>
                            Boring Road, Patna<br>
                            Bihar 800001, India
                        </p>
                        <a href="https://maps.google.com" target="_blank" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-directions me-1"></i>Get Directions
                        </a>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="contact-info-card text-center h-100">
                        <div class="contact-icon mb-3">
                            <i class="fas fa-phone fa-3x text-primary"></i>
                        </div>
                        <h5>Call Us</h5>
                        <p class="text-muted">
                            Customer Support:<br>
                            <strong>+91 98765 43210</strong><br>
                            Business Inquiries:<br>
                            <strong>+91 98765 43211</strong>
                        </p>
                        <small class="text-muted">Mon-Sat: 9:00 AM - 6:00 PM</small>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="contact-info-card text-center h-100">
                        <div class="contact-icon mb-3">
                            <i class="fas fa-envelope fa-3x text-warning"></i>
                        </div>
                        <h5>Email Us</h5>
                        <p class="text-muted">
                            General Inquiries:<br>
                            <strong>info@bindisaagritech.com</strong><br>
                            Support:<br>
                            <strong>support@bindisaagritech.com</strong>
                        </p>
                        <small class="text-muted">We respond within 24 hours</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Form -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow border-0 rounded-4" data-aos="fade-up">
                        <div class="card-body p-5">
                            <div class="text-center mb-4">
                                <h2 class="card-title">Send Us a Message</h2>
                                <p class="text-muted">Fill out the form below and we'll get back to you as soon as possible</p>
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

                            <form method="POST" id="contactForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">
                                            <i class="fas fa-user me-2"></i>Full Name *
                                        </label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">
                                            <i class="fas fa-envelope me-2"></i>Email Address *
                                        </label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
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
                                        <label for="subject" class="form-label">
                                            <i class="fas fa-tag me-2"></i>Subject *
                                        </label>
                                        <select class="form-select" id="subject" name="subject" required>
                                            <option value="">Select Subject</option>
                                            <option value="General Inquiry" <?php echo ($_POST['subject'] ?? '') === 'General Inquiry' ? 'selected' : ''; ?>>General Inquiry</option>
                                            <option value="Technical Support" <?php echo ($_POST['subject'] ?? '') === 'Technical Support' ? 'selected' : ''; ?>>Technical Support</option>
                                            <option value="Farmer Registration" <?php echo ($_POST['subject'] ?? '') === 'Farmer Registration' ? 'selected' : ''; ?>>Farmer Registration</option>
                                            <option value="Buyer Support" <?php echo ($_POST['subject'] ?? '') === 'Buyer Support' ? 'selected' : ''; ?>>Buyer Support</option>
                                            <option value="Partnership" <?php echo ($_POST['subject'] ?? '') === 'Partnership' ? 'selected' : ''; ?>>Partnership Opportunity</option>
                                            <option value="Feedback" <?php echo ($_POST['subject'] ?? '') === 'Feedback' ? 'selected' : ''; ?>>Feedback</option>
                                            <option value="Other" <?php echo ($_POST['subject'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="message" class="form-label">
                                        <i class="fas fa-comment me-2"></i>Message *
                                    </label>
                                    <textarea class="form-control" id="message" name="message" rows="6" 
                                              placeholder="Please describe your inquiry in detail..." required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                                </div>

                                <div class="mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter">
                                        <label class="form-check-label" for="newsletter">
                                            Subscribe to our newsletter for updates and farming tips
                                        </label>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-success btn-lg w-100">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="section-title">Frequently Asked Questions</h2>
                <p class="section-subtitle">Quick answers to common questions</p>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion" id="faqAccordion" data-aos="fade-up">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    How do I register as a farmer on Bihar ka Bazar?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    To register as a farmer, click on "Register" and select "Farmer (Seller)". Fill in your details including your farming location, contact information, and upload necessary documents. Our team will verify your account within 24-48 hours.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    What are the delivery charges?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Delivery charges vary based on location and order value. Orders above ₹500 within Patna city get free delivery. For other locations, charges range from ₹30-100 depending on distance. Exact charges are shown at checkout.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    How do I ensure product quality?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    All our farmers are verified and products go through quality checks. We have a rating system where buyers can review products. For organic products, we ensure proper certification. If you're not satisfied, we have a return policy within 24 hours of delivery.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    What payment methods are accepted?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    We accept multiple payment methods including Cash on Delivery (COD), UPI, Net Banking, Credit/Debit Cards, and Digital Wallets. All online payments are secured with SSL encryption.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                    How can farmers get better prices for their products?
                                </button>
                            </h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    By selling directly through Bihar ka Bazar, farmers eliminate middlemen and get better prices. We also provide market insights, help with product photography, and offer promotional opportunities. Organic certified products typically get premium pricing.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-4" data-aos="fade-up">
                <h2 class="section-title">Find Us</h2>
                <p class="section-subtitle">Visit our office in Patna, Bihar</p>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="map-container rounded shadow" data-aos="fade-up">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3597.8967!2d85.1376!3d25.5941!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39f29937c52d4f05%3A0x831a0e05f607b270!2sBoring%20Road%2C%20Patna%2C%20Bihar!5e0!3m2!1sen!2sin!4v1635000000000!5m2!1sen!2sin" 
                            width="100%" 
                            height="400" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
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
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            if (!validateForm('contactForm')) {
                e.preventDefault();
            }
        });
    </script>

    <style>
        .contact-info-card {
            padding: 2rem;
            border-radius: 10px;
            background: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .contact-info-card:hover {
            transform: translateY(-5px);
        }

        .map-container {
            overflow: hidden;
        }

        .map-container iframe {
            border-radius: 10px;
        }

        .accordion-button:not(.collapsed) {
            background-color: #28a745;
            color: white;
        }

        .accordion-button:focus {
            box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.25);
        }
    </style>
</body>
</html>
