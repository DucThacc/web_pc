<?php
$pageTitle = 'Sản phẩm';
require_once __DIR__ . '/includes/header.php';

$conn = getDBConnection();

// Lấy tham số
$categorySlug = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';
$brand = $_GET['brand'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Build query
$where = ["p.status = 'active'"];
$params = [];
$types = '';

if ($categorySlug) {
    $where[] = "c.slug = ?";
    $params[] = $categorySlug;
    $types .= 's';
}

if ($search) {
    $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'ss';
}

if ($minPrice) {
    $where[] = "COALESCE(p.sale_price, p.price) >= ?";
    $params[] = $minPrice;
    $types .= 'd';
}

if ($maxPrice) {
    $where[] = "COALESCE(p.sale_price, p.price) <= ?";
    $params[] = $maxPrice;
    $types .= 'd';
}

if ($brand) {
    $where[] = "p.brand = ?";
    $params[] = $brand;
    $types .= 's';
}

$whereClause = implode(' AND ', $where);

// Sort
$orderBy = 'p.created_at DESC';
switch ($sort) {
    case 'price_asc':
        $orderBy = 'COALESCE(p.sale_price, p.price) ASC';
        break;
    case 'price_desc':
        $orderBy = 'COALESCE(p.sale_price, p.price) DESC';
        break;
    case 'newest':
    default:
        $orderBy = 'p.created_at DESC';
        break;
}

// Count total
$countQuery = "SELECT COUNT(DISTINCT p.id) as total 
               FROM products p 
               LEFT JOIN categories c ON p.category_id = c.id 
               WHERE $whereClause";
$countStmt = $conn->prepare($countQuery);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalProducts = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalProducts / $perPage);

// Get products
$query = "SELECT p.*, 
                 (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image,
                 c.name as category_name
          FROM products p
          LEFT JOIN categories c ON p.category_id = c.id
          WHERE $whereClause
          ORDER BY $orderBy
          LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
$limitParam = $perPage;
$offsetParam = $offset;
$types .= 'ii';
$params[] = $limitParam;
$params[] = $offsetParam;

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result();

// Get categories for filter
$categories = $conn->query("SELECT id, name, slug FROM categories ORDER BY name");

// Get brands
$brands = $conn->query("SELECT DISTINCT brand FROM products WHERE brand IS NOT NULL AND brand != '' ORDER BY brand");
?>

<div class="container my-5">
    <div class="row">
        <!-- Sidebar Filter -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h5>Bộ lọc</h5>
                </div>
                <div class="card-body">
                    <form method="GET">
                        <?php if ($categorySlug): ?>
                            <input type="hidden" name="category" value="<?php echo e($categorySlug); ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label class="form-label">Tìm kiếm</label>
                            <input type="text" name="search" class="form-control" value="<?php echo e($search); ?>" placeholder="Tên sản phẩm...">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Danh mục</label>
                            <select name="category" class="form-select">
                                <option value="">Tất cả</option>
                                <?php while ($cat = $categories->fetch_assoc()): ?>
                                    <option value="<?php echo e($cat['slug']); ?>" <?php echo $categorySlug === $cat['slug'] ? 'selected' : ''; ?>>
                                        <?php echo e($cat['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Hãng</label>
                            <select name="brand" class="form-select">
                                <option value="">Tất cả</option>
                                <?php while ($b = $brands->fetch_assoc()): ?>
                                    <option value="<?php echo e($b['brand']); ?>" <?php echo $brand === $b['brand'] ? 'selected' : ''; ?>>
                                        <?php echo e($b['brand']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Giá từ</label>
                            <input type="number" name="min_price" class="form-control" value="<?php echo e($minPrice); ?>" placeholder="0">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Giá đến</label>
                            <input type="number" name="max_price" class="form-control" value="<?php echo e($maxPrice); ?>" placeholder="100000000">
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Lọc</button>
                        <a href="/products.php" class="btn btn-secondary w-100 mt-2">Xóa bộ lọc</a>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Products List -->
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Sản phẩm</h2>
                <div>
                    <label>Sắp xếp:</label>
                    <select class="form-select d-inline-block w-auto" onchange="location = this.value">
                        <option value="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'newest'])); ?>" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                        <option value="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'price_asc'])); ?>" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Giá tăng dần</option>
                        <option value="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'price_desc'])); ?>" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Giá giảm dần</option>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <?php if ($products->num_rows > 0): ?>
                    <?php while ($product = $products->fetch_assoc()): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card product-card">
                            <a href="/product.php?slug=<?php echo e($product['slug']); ?>">
                                <img src="<?php echo getImageUrl($product['image'] ?: 'no-image.jpg', 'products'); ?>" class="card-img-top" alt="<?php echo e($product['name']); ?>">
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
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info">Không tìm thấy sản phẩm nào</div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <?php echo generatePagination($page, $totalPages, '/products.php?' . http_build_query(array_diff_key($_GET, ['page' => '']))); ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

