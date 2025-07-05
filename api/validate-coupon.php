<?php
include '../config/session.php';
include '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$coupon_code = $input['coupon_code'] ?? '';
$subtotal = $input['subtotal'] ?? 0;

if (!$coupon_code) {
    echo json_encode(['success' => false, 'message' => 'Coupon code is required']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Check if coupon exists and is valid
$query = "SELECT * FROM coupons WHERE code = ? AND is_active = 1 AND expires_at > NOW()";
$stmt = $db->prepare($query);
$stmt->execute([$coupon_code]);
$coupon = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$coupon) {
    echo json_encode(['success' => false, 'message' => 'Invalid or expired coupon code']);
    exit();
}

// Check minimum order amount
if ($subtotal < $coupon['min_order_amount']) {
    echo json_encode([
        'success' => false, 
        'message' => "Minimum order amount of ₹{$coupon['min_order_amount']} required for this coupon"
    ]);
    exit();
}

// Calculate discount
$discount_amount = 0;
if ($coupon['discount_type'] == 'percentage') {
    $discount_amount = ($subtotal * $coupon['discount_value']) / 100;
    if ($coupon['max_discount_amount'] > 0 && $discount_amount > $coupon['max_discount_amount']) {
        $discount_amount = $coupon['max_discount_amount'];
    }
} else {
    $discount_amount = $coupon['discount_value'];
}

// Ensure discount doesn't exceed subtotal
if ($discount_amount > $subtotal) {
    $discount_amount = $subtotal;
}

echo json_encode([
    'success' => true,
    'discount_amount' => $discount_amount,
    'coupon_type' => $coupon['discount_type'],
    'coupon_value' => $coupon['discount_value']
]);
?>
