<?php
$pageTitle = 'Trang chủ';
require_once __DIR__ . '/includes/header.php';

$conn = getDBConnection();

// Lấy banners cho slider
$banners = $conn->query("SELECT * FROM banners WHERE position = 'home_slider' AND status = 'active' ORDER BY sort_order");

// Sản phẩm nổi bật
$featuredProducts = $conn->query("
    SELECT p.*, 
           (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
    FROM products p 
    WHERE p.featured = 1 AND p.status = 'active' 
    ORDER BY p.created_at DESC 
    LIMIT 8
");

// Sản phẩm mới
$newProducts = $conn->query("
    SELECT p.*, 
           (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
    FROM products p 
    WHERE p.status = 'active' 
    ORDER BY p.created_at DESC 
    LIMIT 8
");

// Sản phẩm bán chạy (theo số lượng đã bán)
$bestSellingProducts = $conn->query("
    SELECT p.*, 
           (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image,
           COALESCE(SUM(oi.quantity), 0) as total_sold
    FROM products p
    LEFT JOIN order_items oi ON p.id = oi.product_id
    WHERE p.status = 'active'
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 8
");
?>

<!-- Banner Slider -->
<?php if ($banners->num_rows > 0): ?>
<div class="banner-slider">
    <?php 
    $firstBanner = true;
    while ($banner = $banners->fetch_assoc()): 
    ?>
    <div class="banner-slide <?php echo $firstBanner ? 'active' : ''; ?>">
        <?php if ($banner['link']): ?>
        <a href="<?php echo e($banner['link']); ?>">
        <?php endif; ?>
            <img src="/uploads/banners/<?php echo e($banner['image_path']); ?>" alt="<?php echo e($banner['title']); ?>">
        <?php if ($banner['link']): ?>
        </a>
        <?php endif; ?>
    </div>
    <?php 
        $firstBanner = false;
    endwhile; 
    ?>
</div>
<?php endif; ?>

<div class="container my-5">
    <!-- Sản phẩm nổi bật -->
    <section class="mb-5">
        <h2 class="mb-4">Sản phẩm nổi bật</h2>
        <div class="row">
            <?php while ($product = $featuredProducts->fetch_assoc()): ?>
            <div class="col-md-3 mb-4">
                <div class="card product-card">
                    <a href="/product.php?slug=<?php echo e($product['slug']); ?>">
                        <img src="/uploads/products/<?php echo e($product['image'] ?: 'no-image.jpg'); ?>" class="card-img-top" alt="<?php echo e($product['name']); ?>">
                    </a>
                    <div class="card-body">
                        <h5 class="card-title">
                            <a href="/product.php?slug=<?php echo e($product['slug']); ?>" class="text-decoration-none text-dark">
                                <?php echo e($product['name']); ?>
                            </a>
                        </h5>
                        <div class="product-price">
                            <?php if ($product['sale_price']): ?>
                                <span class="product-price-old"><?php echo formatPrice($product['price']); ?></span>
                                <?php echo formatPrice($product['sale_price']); ?>
                            <?php else: ?>
                                <?php echo formatPrice($product['price']); ?>
                            <?php endif; ?>
                        </div>
                        <button class="btn btn-primary btn-sm w-100 mt-2" onclick="addToCart(<?php echo $product['id']; ?>)">
                            <i class="bi bi-cart-plus"></i> Thêm vào giỏ
                        </button>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </section>

    <!-- Sản phẩm mới -->
    <section class="mb-5">
        <h2 class="mb-4">Sản phẩm mới</h2>
        <div class="row">
            <?php while ($product = $newProducts->fetch_assoc()): ?>
            <div class="col-md-3 mb-4">
                <div class="card product-card">
                    <a href="/product.php?slug=<?php echo e($product['slug']); ?>">
                        <img src="/uploads/products/<?php echo e($product['image'] ?: 'no-image.jpg'); ?>" class="card-img-top" alt="<?php echo e($product['name']); ?>">
                    </a>
                    <div class="card-body">
                        <h5 class="card-title">
                            <a href="/product.php?slug=<?php echo e($product['slug']); ?>" class="text-decoration-none text-dark">
                                <?php echo e($product['name']); ?>
                            </a>
                        </h5>
                        <div class="product-price">
                            <?php if ($product['sale_price']): ?>
                                <span class="product-price-old"><?php echo formatPrice($product['price']); ?></span>
                                <?php echo formatPrice($product['sale_price']); ?>
                            <?php else: ?>
                                <?php echo formatPrice($product['price']); ?>
                            <?php endif; ?>
                        </div>
                        <button class="btn btn-primary btn-sm w-100 mt-2" onclick="addToCart(<?php echo $product['id']; ?>)">
                            <i class="bi bi-cart-plus"></i> Thêm vào giỏ
                        </button>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </section>

    <!-- Sản phẩm bán chạy -->
    <section class="mb-5">
        <h2 class="mb-4">Sản phẩm bán chạy</h2>
        <div class="row">
            <?php while ($product = $bestSellingProducts->fetch_assoc()): ?>
            <div class="col-md-3 mb-4">
                <div class="card product-card">
                    <a href="/product.php?slug=<?php echo e($product['slug']); ?>">
                        <img src="/uploads/products/<?php echo e($product['image'] ?: 'no-image.jpg'); ?>" class="card-img-top" alt="<?php echo e($product['name']); ?>">
                    </a>
                    <div class="card-body">
                        <h5 class="card-title">
                            <a href="/product.php?slug=<?php echo e($product['slug']); ?>" class="text-decoration-none text-dark">
                                <?php echo e($product['name']); ?>
                            </a>
                        </h5>
                        <div class="product-price">
                            <?php if ($product['sale_price']): ?>
                                <span class="product-price-old"><?php echo formatPrice($product['price']); ?></span>
                                <?php echo formatPrice($product['sale_price']); ?>
                            <?php else: ?>
                                <?php echo formatPrice($product['price']); ?>
                            <?php endif; ?>
                        </div>
                        <button class="btn btn-primary btn-sm w-100 mt-2" onclick="addToCart(<?php echo $product['id']; ?>)">
                            <i class="bi bi-cart-plus"></i> Thêm vào giỏ
                        </button>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

