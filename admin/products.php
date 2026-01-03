<?php
$pageTitle = 'Quản lý Sản phẩm';
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

$conn = getDBConnection();
$error = '';
$success = '';

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add']) || isset($_POST['edit'])) {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $name = $_POST['name'] ?? '';
        $slug = $_POST['slug'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'] ?? 0;
        $salePrice = !empty($_POST['sale_price']) ? $_POST['sale_price'] : null;
        $stock = (int)($_POST['stock'] ?? 0);
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $brand = $_POST['brand'] ?? '';
        $sku = $_POST['sku'] ?? '';
        $featured = isset($_POST['featured']) ? 1 : 0;
        $status = $_POST['status'] ?? 'active';
        
        if (empty($name) || empty($slug) || $categoryId <= 0) {
            $error = 'Vui lòng điền đầy đủ thông tin';
        } else {
            if ($id > 0) {
                // Edit
                $stmt = $conn->prepare("UPDATE products SET name = ?, slug = ?, description = ?, price = ?, sale_price = ?, stock = ?, category_id = ?, brand = ?, sku = ?, featured = ?, status = ? WHERE id = ?");
                $stmt->bind_param("ssddsisssssi", $name, $slug, $description, $price, $salePrice, $stock, $categoryId, $brand, $sku, $featured, $status, $id);
                if ($stmt->execute()) {
                    $success = 'Cập nhật sản phẩm thành công';
                    $productId = $id;
                } else {
                    $error = 'Có lỗi xảy ra';
                }
            } else {
                // Add
                $stmt = $conn->prepare("INSERT INTO products (name, slug, description, price, sale_price, stock, category_id, brand, sku, featured, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssddsisssss", $name, $slug, $description, $price, $salePrice, $stock, $categoryId, $brand, $sku, $featured, $status);
                if ($stmt->execute()) {
                    $productId = $conn->insert_id;
                    $success = 'Thêm sản phẩm thành công';
                } else {
                    $error = 'Có lỗi xảy ra';
                }
            }
            
            // Handle images upload
            if (isset($productId) && isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                require_once __DIR__ . '/../includes/functions.php';
                foreach ($_FILES['images']['name'] as $key => $filename) {
                    if (!empty($filename)) {
                        $file = [
                            'name' => $_FILES['images']['name'][$key],
                            'type' => $_FILES['images']['type'][$key],
                            'tmp_name' => $_FILES['images']['tmp_name'][$key],
                            'error' => $_FILES['images']['error'][$key],
                            'size' => $_FILES['images']['size'][$key]
                        ];
                        $uploadResult = uploadFile($file, 'products');
                        if ($uploadResult['success']) {
                            $isPrimary = ($key === 0) ? 1 : 0;
                            $conn->query("INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES ($productId, '{$uploadResult['filename']}', $isPrimary, $key)");
                        }
                    }
                }
            }
        }
    } elseif (isset($_POST['delete'])) {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $success = 'Xóa sản phẩm thành công';
        } else {
            $error = 'Có lỗi xảy ra';
        }
    }
}

// Get products
$products = $conn->query("SELECT p.*, c.name as category_name 
                          FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          ORDER BY p.created_at DESC");

// Get categories
$categories = $conn->query("SELECT * FROM categories ORDER BY name");

// Get product to edit
$editProduct = null;
$editImages = [];
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $result = $conn->query("SELECT * FROM products WHERE id = $editId");
    $editProduct = $result->fetch_assoc();
    if ($editProduct) {
        $editImages = $conn->query("SELECT * FROM product_images WHERE product_id = $editId ORDER BY is_primary DESC, sort_order");
    }
}
?>

<?php require_once __DIR__ . '/includes/sidebar.php'; ?>

<div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <h2>Quản lý Sản phẩm</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo e($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo e($success); ?></div>
    <?php endif; ?>
    
    <!-- Add/Edit Form -->
    <div class="card mb-4">
        <div class="card-header">
            <h5><?php echo $editProduct ? 'Sửa sản phẩm' : 'Thêm sản phẩm mới'; ?></h5>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <?php if ($editProduct): ?>
                    <input type="hidden" name="id" value="<?php echo $editProduct['id']; ?>">
                <?php endif; ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Tên sản phẩm *</label>
                            <input type="text" name="name" class="form-control" value="<?php echo $editProduct ? e($editProduct['name']) : ''; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Slug *</label>
                            <input type="text" name="slug" class="form-control" value="<?php echo $editProduct ? e($editProduct['slug']) : ''; ?>" required>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mô tả</label>
                    <textarea name="description" class="form-control" rows="4"><?php echo $editProduct ? e($editProduct['description']) : ''; ?></textarea>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Giá *</label>
                            <input type="number" name="price" class="form-control" value="<?php echo $editProduct ? $editProduct['price'] : ''; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Giá khuyến mãi</label>
                            <input type="number" name="sale_price" class="form-control" value="<?php echo $editProduct ? ($editProduct['sale_price'] ?? '') : ''; ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Số lượng *</label>
                            <input type="number" name="stock" class="form-control" value="<?php echo $editProduct ? $editProduct['stock'] : '0'; ?>" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Danh mục *</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Chọn danh mục</option>
                                <?php 
                                $categories->data_seek(0);
                                while ($cat = $categories->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo ($editProduct && $editProduct['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                        <?php echo e($cat['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Hãng</label>
                            <input type="text" name="brand" class="form-control" value="<?php echo $editProduct ? e($editProduct['brand'] ?? '') : ''; ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">SKU</label>
                            <input type="text" name="sku" class="form-control" value="<?php echo $editProduct ? e($editProduct['sku'] ?? '') : ''; ?>">
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Hình ảnh</label>
                    <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                    <small class="text-muted">Có thể chọn nhiều ảnh, ảnh đầu tiên sẽ là ảnh chính</small>
                </div>
                <?php if ($editProduct && $editImages->num_rows > 0): ?>
                    <div class="mb-3">
                        <label class="form-label">Ảnh hiện tại</label>
                        <div class="d-flex flex-wrap gap-2">
                            <?php while ($img = $editImages->fetch_assoc()): ?>
                                <img src="/uploads/products/<?php echo e($img['image_path']); ?>" style="width: 100px; height: 100px; object-fit: cover;" alt="Product Image">
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="featured" class="form-check-input" value="1" <?php echo ($editProduct && $editProduct['featured']) ? 'checked' : ''; ?>>
                        <label class="form-check-label">Sản phẩm nổi bật</label>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="active" <?php echo ($editProduct && $editProduct['status'] === 'active') ? 'selected' : ''; ?>>Kích hoạt</option>
                        <option value="inactive" <?php echo ($editProduct && $editProduct['status'] === 'inactive') ? 'selected' : ''; ?>>Tắt</option>
                    </select>
                </div>
                <button type="submit" name="<?php echo $editProduct ? 'edit' : 'add'; ?>" class="btn btn-primary">
                    <?php echo $editProduct ? 'Cập nhật' : 'Thêm mới'; ?>
                </button>
                <?php if ($editProduct): ?>
                    <a href="/admin/products.php" class="btn btn-secondary">Hủy</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <!-- Products List -->
    <div class="card">
        <div class="card-header">
            <h5>Danh sách sản phẩm</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ảnh</th>
                            <th>Tên</th>
                            <th>Giá</th>
                            <th>Tồn kho</th>
                            <th>Danh mục</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $products->data_seek(0);
                        while ($product = $products->fetch_assoc()): 
                            $image = $conn->query("SELECT image_path FROM product_images WHERE product_id = {$product['id']} AND is_primary = 1 LIMIT 1")->fetch_assoc();
                        ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td>
                                <?php if ($image): ?>
                                    <img src="/uploads/products/<?php echo e($image['image_path']); ?>" style="width: 50px; height: 50px; object-fit: cover;" alt="">
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($product['name']); ?></td>
                            <td><?php echo formatPrice($product['sale_price'] ?: $product['price']); ?></td>
                            <td><?php echo $product['stock']; ?></td>
                            <td><?php echo e($product['category_name']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $product['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo e($product['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="/admin/products.php?edit=<?php echo $product['id']; ?>" class="btn btn-sm btn-warning">Sửa</a>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Xóa sản phẩm này?');">
                                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" name="delete" class="btn btn-sm btn-danger">Xóa</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

