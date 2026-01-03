<?php
/**
 * Cart API
 */
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
    $productId = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? $_GET['quantity'] ?? 1);
    
    if ($productId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product']);
        exit;
    }
    
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, name, price, sale_price, stock FROM products WHERE id = ? AND status = 'active'");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
    
    if ($product['stock'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Not enough stock']);
        exit;
    }
    
    // Add to cart
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Added to cart',
        'cart_count' => array_sum($_SESSION['cart'])
    ]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update') {
    $productId = (int)($_POST['product_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 0);
    
    if ($quantity <= 0) {
        unset($_SESSION['cart'][$productId]);
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
    
    echo json_encode(['success' => true]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'remove') {
    $productId = (int)($_POST['product_id'] ?? 0);
    unset($_SESSION['cart'][$productId]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

