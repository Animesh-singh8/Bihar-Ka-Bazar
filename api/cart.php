<?php
include '../config/session.php';
include '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isBuyer()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        $product_id = (int)$input['product_id'];
        $quantity = (int)($input['quantity'] ?? 1);
        
        // Check if product exists and is active
        $query = "SELECT * FROM products WHERE id = ? AND status = 'active'";
        $stmt = $db->prepare($query);
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit();
        }
        
        // Check if already in cart
        $query = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([getUserId(), $product_id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            // Update quantity
            $new_quantity = $existing['quantity'] + $quantity;
            if ($new_quantity > $product['quantity_available']) {
                echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
                exit();
            }
            
            $query = "UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$new_quantity, $existing['id']]);
        } else {
            // Add new item
            if ($quantity > $product['quantity_available']) {
                echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
                exit();
            }
            
            $query = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->execute([getUserId(), $product_id, $quantity]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Added to cart']);
        break;
        
    case 'remove':
        $product_id = (int)$input['product_id'];
        
        $query = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([getUserId(), $product_id]);
        
        echo json_encode(['success' => true, 'message' => 'Removed from cart']);
        break;
        
    case 'update':
        $product_id = (int)$input['product_id'];
        $quantity = (int)$input['quantity'];
        
        if ($quantity <= 0) {
            $query = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([getUserId(), $product_id]);
        } else {
            // Check stock availability
            $query = "SELECT quantity_available FROM products WHERE id = ? AND status = 'active'";
            $stmt = $db->prepare($query);
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$product || $quantity > $product['quantity_available']) {
                echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
                exit();
            }
            
            $query = "UPDATE cart SET quantity = ?, updated_at = NOW() WHERE user_id = ? AND product_id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$quantity, getUserId(), $product_id]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Cart updated']);
        break;
        
    case 'get':
        $query = "SELECT c.*, p.title, p.price, p.image, p.unit, p.quantity_available, u.full_name as farmer_name
                  FROM cart c 
                  JOIN products p ON c.product_id = p.id 
                  JOIN users u ON p.farmer_id = u.id
                  WHERE c.user_id = ? AND p.status = 'active'
                  ORDER BY c.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute([getUserId()]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'items' => $items]);
        break;
        
    case 'count':
        $query = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([getUserId()]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'count' => (int)($result['total'] ?? 0)]);
        break;
        
    case 'clear':
        $query = "DELETE FROM cart WHERE user_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([getUserId()]);
        
        echo json_encode(['success' => true, 'message' => 'Cart cleared']);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>
