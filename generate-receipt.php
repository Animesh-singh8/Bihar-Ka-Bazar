<?php
include 'config/session.php';
include 'config/database.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();

$order_id = $_GET['order_id'] ?? 0;

if (!$order_id) {
    header("Location: dashboard/buyer.php");
    exit();
}

// Get order details
$query = "SELECT o.*, u.full_name as farmer_name, u.phone as farmer_phone, u.address as farmer_address,
                 b.full_name as buyer_name, b.phone as buyer_phone, b.email as buyer_email
          FROM orders o 
          JOIN users u ON o.farmer_id = u.id 
          JOIN users b ON o.buyer_id = b.id
          WHERE o.id = ? AND o.buyer_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$order_id, getUserId()]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header("Location: dashboard/buyer.php");
    exit();
}

// Get order items
$query = "SELECT oi.*, p.title, p.unit 
          FROM order_items oi 
          JOIN products p ON oi.product_id = p.id 
          WHERE oi.order_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - Order #<?php echo $order['order_number']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 12px; }
        }
        
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .receipt-header {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .receipt-body {
            padding: 2rem;
        }
        
        .company-info {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .order-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        
        .items-table {
            margin-bottom: 2rem;
        }
        
        .total-section {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
        }
        
        .footer-note {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #28a745;
            color: #6c757d;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="receipt-container">
            <!-- Receipt Header -->
            <div class="receipt-header">
                <h1 class="mb-0">Bihar ka Bazar</h1>
                <p class="mb-0">Powered by Bindisa Agritech</p>
                <p class="mb-0 small">Innovate • Cultivate • Elevate</p>
            </div>

            <!-- Receipt Body -->
            <div class="receipt-body">
                <!-- Company Info -->
                <div class="company-info">
                    <h4>PAYMENT RECEIPT</h4>
                    <p class="mb-1">Email: info@bindisaagritech.com</p>
                    <p class="mb-1">Phone: +91 98765 43210</p>
                    <p class="mb-0">Address: Patna, Bihar, India</p>
                </div>

                <!-- Order Information -->
                <div class="order-info">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Order Details</h6>
                            <p class="mb-1"><strong>Order Number:</strong> <?php echo $order['order_number']; ?></p>
                            <p class="mb-1"><strong>Order Date:</strong> <?php echo date('F d, Y', strtotime($order['created_at'])); ?></p>
                            <p class="mb-1"><strong>Payment Method:</strong> <?php echo ucfirst($order['payment_method']); ?></p>
                            <p class="mb-0"><strong>Status:</strong> <?php echo ucfirst($order['order_status']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Customer Details</h6>
                            <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($order['buyer_name']); ?></p>
                            <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($order['buyer_email']); ?></p>
                            <p class="mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($order['buyer_phone']); ?></p>
                            <?php if ($order['transaction_id']): ?>
                                <p class="mb-0"><strong>Transaction ID:</strong> <?php echo $order['transaction_id']; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Farmer Information -->
                <div class="order-info">
                    <h6>Farmer Details</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($order['farmer_name']); ?></p>
                            <p class="mb-0"><strong>Phone:</strong> <?php echo htmlspecialchars($order['farmer_phone']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-0"><strong>Address:</strong> <?php echo htmlspecialchars($order['farmer_address']); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="items-table">
                    <h6>Order Items</h6>
                    <table class="table table-bordered">
                        <thead class="table-success">
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($order_items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['title']); ?></td>
                                    <td><?php echo $item['quantity']; ?> <?php echo $item['unit']; ?></td>
                                    <td>₹<?php echo number_format($item['unit_price'], 2); ?></td>
                                    <td>₹<?php echo number_format($item['total_price'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Shipping Address -->
                <div class="order-info">
                    <h6>Shipping Address</h6>
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                </div>

                <!-- Total Section -->
                <div class="total-section">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Payment Summary</h6>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>₹<?php echo number_format($order['total_amount'], 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping:</span>
                                <span>₹<?php echo number_format($order['shipping_cost'], 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tax (5%):</span>
                                <span>₹<?php echo number_format($order['tax_amount'], 2); ?></span>
                            </div>
                            <?php if ($order['discount_amount'] > 0): ?>
                                <div class="d-flex justify-content-between mb-2 text-success">
                                    <span>Discount:</span>
                                    <span>-₹<?php echo number_format($order['discount_amount'], 2); ?></span>
                                </div>
                            <?php endif; ?>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold fs-5">
                                <span>Total Paid:</span>
                                <span class="text-success">₹<?php echo number_format($order['final_amount'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Note -->
                <div class="footer-note">
                    <p class="mb-2"><strong>Thank you for choosing Bihar ka Bazar!</strong></p>
                    <p class="mb-2">Supporting local farmers, serving fresh produce</p>
                    <p class="mb-0 small">This is a computer-generated receipt and does not require a signature.</p>
                    <p class="mb-0 small">For any queries, contact us at info@bindisaagritech.com</p>
                </div>
            </div>
        </div>

        <!-- Print Button -->
        <div class="text-center mt-4 no-print">
            <button onclick="window.print()" class="btn btn-success btn-lg me-3">
                <i class="fas fa-print me-2"></i>Print Receipt
            </button>
            <button onclick="downloadPDF()" class="btn btn-outline-success btn-lg me-3">
                <i class="fas fa-download me-2"></i>Download PDF
            </button>
            <a href="dashboard/buyer.php" class="btn btn-outline-secondary btn-lg">
                <i class="fas fa-arrow-left me-2"></i>Back to Orders
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function downloadPDF() {
            const element = document.querySelector('.receipt-container');
            const opt = {
                margin: 1,
                filename: 'receipt-<?php echo $order['order_number']; ?>.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
            };

            html2pdf().set(opt).from(element).save();
        }

        // Auto-print on load if requested
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('print') === '1') {
            window.onload = function() {
                setTimeout(() => {
                    window.print();
                }, 1000);
            };
        }
    </script>
</body>
</html>
