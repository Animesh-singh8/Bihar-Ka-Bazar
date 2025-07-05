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
$action = $input['action'] ?? '';

switch ($action) {
    case 'add':
        $product_id = (int)$input['product_id'];
        
        // Check if product exists
        $query = "SELECT id FROM products WHERE id = ? AND status = 'active'";
        $stmt = $db->prepare($query);
        $stmt->execute([$product_id]);
        
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit();
        }
        
        // Check if already in wishlist
        $query = "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([getUserId(), $product_id]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Already in wishlist']);
            exit();
        }
        
        // Add to wishlist
        $query = "INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([getUserId(), $product_id]);
        
        echo json_encode(['success' => true, 'message' => 'Added to wishlist']);
        break;
        
    case 'remove':
        $product_id = (int)$input['product_id'];
        
        $query = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([getUserId(), $product_id]);
        
        echo json_encode(['success' => true, 'message' => 'Removed from wishlist']);
        break;
        
    case 'clear':
        $query = "DELETE FROM wishlist WHERE user_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([getUserId()]);
        
        echo json_encode(['success' => true, 'message' => 'Wishlist cleared']);
        break;
        
    case 'get':
        $query = "SELECT w.*, p.title, p.price, p.image, p.unit, u.full_name as farmer_name
                  FROM wishlist w 
                  JOIN products p ON w.product_id = p.id 
                  JOIN users u ON p.farmer_id = u.id
                  WHERE w.user_id = ? AND p.status = 'active'
                  ORDER BY w.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute([getUserId()]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'items' => $items]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>
