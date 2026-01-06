<?php
$pageTitle = 'Chi tiết sản phẩm';
require_once __DIR__ . '/includes/header.php';

$conn = getDBConnection();
$slug = $_GET['slug'] ?? '';

if (!$slug) {
    redirect('/products.php');
}

// Get product
$stmt = $conn->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug 
                        FROM products p 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        WHERE p.slug = ? AND p.status = 'active'");
$stmt->bind_param("s", $slug);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    redirect('/products.php');
}

// Update views
$conn->query("UPDATE products SET views = views + 1 WHERE id = {$product['id']}");

// Get product images
$images = $conn->query("SELECT * FROM product_images WHERE product_id = {$product['id']} ORDER BY is_primary DESC, sort_order");

// Get reviews
$reviews = $conn->query("SELECT r.*, u.name as user_name 
                         FROM reviews r 
                         LEFT JOIN users u ON r.user_id = u.id 
                         WHERE r.product_id = {$product['id']} 
                         ORDER BY r.created_at DESC");

// Calculate average rating
$ratingResult = $conn->query("SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM reviews WHERE product_id = {$product['id']}");
$ratingData = $ratingResult->fetch_assoc();
$avgRating = $ratingData['avg_rating'] ?? 0;
$ratingCount = $ratingData['count'] ?? 0;

// Get related products (same category)
$relatedProducts = $conn->query("SELECT p.*, 
                                         (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
                                  FROM products p 
                                  WHERE p.category_id = {$product['category_id']} 
                                    AND p.id != {$product['id']} 
                                    AND p.status = 'active' 
                                  ORDER BY RAND() 
                                  LIMIT 4");
?>

<div class="container my-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="/products.php?category=<?php echo e($product['category_slug']); ?>"><?php echo e($product['category_name']); ?></a></li>
            <li class="breadcrumb-item active"><?php echo e($product['name']); ?></li>
        </ol>
    </nav>
    
    <div class="row">
        <!-- Product Images -->
        <div class="col-md-5">
            <div class="product-images">
                <?php
                $imagesArray = $images->fetch_all(MYSQLI_ASSOC);
                $primaryImage = !empty($imagesArray) ? $imagesArray[0]['image_path'] : 'no-image.jpg';
                ?>
                <img src="<?php echo getImageUrl($primaryImage, 'products'); ?>" class="product-main-image" id="mainImage" alt="<?php echo e($product['name']); ?>">
                
                <?php if (count($imagesArray) > 1): ?>
                <div class="product-thumbnails">
                    <?php foreach ($imagesArray as $img): ?>
                        <img src="/uploads/products/<?php echo e($img['image_path']); ?>" 
                             class="product-thumbnail <?php echo $img['is_primary'] ? 'active' : ''; ?>" 
                             onclick="document.getElementById('mainImage').src = this.src"
                             alt="<?php echo e($product['name']); ?>">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Product Info -->
        <div class="col-md-7">
            <h1><?php echo e($product['name']); ?></h1>
            
            <div class="mb-3">
                <span class="badge bg-secondary"><?php echo e($product['category_name']); ?></span>
                <?php if ($product['brand']): ?>
                    <span class="badge bg-info"><?php echo e($product['brand']); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="product-detail-price mb-3">
                <?php if ($product['sale_price']): ?>
                    <span class="product-price-old"><?php echo formatPrice($product['price']); ?></span>
                    <?php echo formatPrice($product['sale_price']); ?>
                <?php else: ?>
                    <?php echo formatPrice($product['price']); ?>
                <?php endif; ?>
            </div>
            
            <div class="mb-3">
                <strong>Tình trạng:</strong> 
                <?php if ($product['stock'] > 0): ?>
                    <span class="text-success">Còn hàng (<?php echo $product['stock']; ?> sản phẩm)</span>
                <?php else: ?>
                    <span class="text-danger">Hết hàng</span>
                <?php endif; ?>
            </div>
            
            <?php if ($product['description']): ?>
            <div class="mb-4">
                <h5>Mô tả sản phẩm</h5>
                <p><?php echo nl2br(e($product['description'])); ?></p>
            </div>
            <?php endif; ?>
            
            <div class="mb-4">
                <label class="form-label">Số lượng:</label>
                <input type="number" class="form-control w-auto d-inline-block" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
            </div>
            
            <div class="d-grid gap-2 d-md-block">
                <button class="btn btn-primary btn-lg" onclick="addToCart(<?php echo $product['id']; ?>, parseInt(document.getElementById('quantity').value))" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                    <i class="bi bi-cart-plus"></i> Thêm vào giỏ hàng
                </button>
            </div>
        </div>
    </div>
    
    <!-- Reviews Section -->
    <div class="row mt-5">
        <div class="col-12">
            <h3>Đánh giá sản phẩm</h3>
            
            <div class="mb-4">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <div class="h2 mb-0"><?php echo number_format($avgRating, 1); ?></div>
                        <div class="stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi bi-star<?php echo $i <= round($avgRating) ? '-fill' : ''; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <small class="text-muted">(<?php echo $ratingCount; ?> đánh giá)</small>
                    </div>
                </div>
            </div>
            
            <?php if (isLoggedIn()): ?>
                <?php
                // Check if user can review (has ordered this product)
                $canReview = false;
                $userOrders = $conn->query("SELECT o.id FROM orders o 
                                            JOIN order_items oi ON o.id = oi.order_id 
                                            WHERE o.user_id = {$_SESSION['user_id']} 
                                              AND oi.product_id = {$product['id']} 
                                              AND o.status = 'delivered' 
                                            LIMIT 1");
                if ($userOrders->num_rows > 0) {
                    $canReview = true;
                }
                ?>
                
                <?php if ($canReview): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <h5>Viết đánh giá</h5>
                        <form method="POST" action="/api/review.php">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <input type="hidden" name="slug" value="<?php echo e($product['slug']); ?>">
                            <div class="mb-3">
                                <label>Đánh giá:</label>
                                <select name="rating" class="form-select" required>
                                    <option value="">Chọn sao</option>
                                    <option value="5">5 sao - Tuyệt vời</option>
                                    <option value="4">4 sao - Tốt</option>
                                    <option value="3">3 sao - Bình thường</option>
                                    <option value="2">2 sao - Không tốt</option>
                                    <option value="1">1 sao - Rất tệ</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Bình luận:</label>
                                <textarea name="comment" class="form-control" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Gửi đánh giá</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <div class="reviews-list">
                <?php if ($reviews->num_rows > 0): ?>
                    <?php $reviews->data_seek(0); ?>
                    <?php while ($review = $reviews->fetch_assoc()): ?>
                    <div class="review-item">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong><?php echo e($review['user_name']); ?></strong>
                                <div class="stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi bi-star<?php echo $i <= $review['rating'] ? '-fill' : ''; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <small class="text-muted"><?php echo date('d/m/Y', strtotime($review['created_at'])); ?></small>
                        </div>
                        <?php if ($review['comment']): ?>
                            <p class="mt-2"><?php echo nl2br(e($review['comment'])); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted">Chưa có đánh giá nào</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Related Products -->
    <?php if ($relatedProducts->num_rows > 0): ?>
    <div class="row mt-5">
        <div class="col-12">
            <h3>Sản phẩm liên quan</h3>
            <div class="row">
                <?php while ($related = $relatedProducts->fetch_assoc()): ?>
                <div class="col-md-3 mb-4">
                    <div class="card product-card">
                        <a href="/product.php?slug=<?php echo e($related['slug']); ?>">
                            <img src="<?php echo getImageUrl($related['image'] ?: 'no-image.jpg', 'products'); ?>" class="card-img-top" alt="<?php echo e($related['name']); ?>">
                        </a>
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="/product.php?slug=<?php echo e($related['slug']); ?>" class="text-decoration-none text-dark">
                                    <?php echo e($related['name']); ?>
                                </a>
                            </h5>
                            <div class="product-price">
                                <?php if ($related['sale_price']): ?>
                                    <span class="product-price-old"><?php echo formatPrice($related['price']); ?></span>
                                    <?php echo formatPrice($related['sale_price']); ?>
                                <?php else: ?>
                                    <?php echo formatPrice($related['price']); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

