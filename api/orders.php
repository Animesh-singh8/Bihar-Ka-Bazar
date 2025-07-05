<?php
include '../config/session.php';
include '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

switch ($action) {
    case 'cancel':
        if (!isBuyer()) {
            echo json_encode(['success' => false, 'message' => 'Only buyers can cancel orders']);
            exit();
        }
        
        $order_id = (int)$input['order_id'];
        
        // Check if order belongs to user and can be cancelled
        $query = "SELECT * FROM orders WHERE id = ? AND buyer_id = ? AND order_status IN ('pending', 'confirmed')";
        $stmt = $db->prepare($query);
        $stmt->execute([$order_id, getUserId()]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found or cannot be cancelled']);
            exit();
        }
        
        try {
            $db->beginTransaction();
            
            // Update order status
            $query = "UPDATE orders SET order_status = 'cancelled', updated_at = NOW() WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$order_id]);
            
            // Restore product quantities
            $query = "SELECT oi.product_id, oi.quantity FROM order_items oi WHERE oi.order_id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$order_id]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($items as $item) {
                $query = "UPDATE products SET quantity_available = quantity_available + ? WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$item['quantity'], $item['product_id']]);
            }
            
            // Notify farmer
            $query = "INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, 'order')";
            $stmt = $db->prepare($query);
            $stmt->execute([
                $order['farmer_id'],
                'Order Cancelled',
                "Order #{$order['order_number']} has been cancelled by the buyer"
            ]);
            
            $db->commit();
            echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);
            
        } catch (Exception $e) {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Failed to cancel order']);
        }
        break;
        
    case 'update_status':
        if (!isFarmer()) {
            echo json_encode(['success' => false, 'message' => 'Only farmers can update order status']);
            exit();
        }
        
        $order_id = (int)$input['order_id'];
        $status = sanitize($input['status']);
        
        $valid_statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($status, $valid_statuses)) {
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            exit();
        }
        
        // Check if order belongs to farmer
        $query = "SELECT * FROM orders WHERE id = ? AND farmer_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$order_id, getUserId()]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            exit();
        }
        
        // Update order status
        $query = "UPDATE orders SET order_status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$status, $order_id]);
        
        // Set delivery date if delivered
        if ($status === 'delivered') {
            $query = "UPDATE orders SET delivery_date = NOW() WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$order_id]);
        }
        
        // Notify buyer
        $query = "INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, 'order')";
        $stmt = $db->prepare($query);
        $stmt->execute([
            $order['buyer_id'],
            'Order Status Updated',
            "Your order #{$order['order_number']} status has been updated to: " . ucfirst($status)
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Order status updated successfully']);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>
