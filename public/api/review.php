<?php
/**
 * Review API
 */
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = (int)($_POST['product_id'] ?? 0);
    $rating = (int)($_POST['rating'] ?? 0);
    $comment = $_POST['comment'] ?? '';
    
    if ($productId <= 0 || $rating < 1 || $rating > 5) {
        header('Location: /product.php?slug=' . ($_POST['slug'] ?? '') . '&error=invalid');
        exit;
    }
    
    $conn = getDBConnection();
    
    // Check if user already reviewed this product
    $stmt = $conn->prepare("SELECT id FROM reviews WHERE product_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $productId, $_SESSION['user_id']);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        // Update existing review
        $stmt = $conn->prepare("UPDATE reviews SET rating = ?, comment = ? WHERE product_id = ? AND user_id = ?");
        $stmt->bind_param("isii", $rating, $comment, $productId, $_SESSION['user_id']);
    } else {
        // Insert new review
        $stmt = $conn->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $productId, $_SESSION['user_id'], $rating, $comment);
    }
    
    if ($stmt->execute()) {
        header('Location: /product.php?slug=' . ($_POST['slug'] ?? '') . '&success=1');
    } else {
        header('Location: /product.php?slug=' . ($_POST['slug'] ?? '') . '&error=1');
    }
    exit;
}

header('Location: /');
exit;

