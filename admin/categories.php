<?php
$pageTitle = 'Quản lý Danh mục';
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

$conn = getDBConnection();
$error = '';
$success = '';

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $name = $_POST['name'] ?? '';
        $slug = $_POST['slug'] ?? '';
        $description = $_POST['description'] ?? '';
        
        if (empty($name) || empty($slug)) {
            $error = 'Vui lòng điền đầy đủ thông tin';
        } else {
            $stmt = $conn->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $slug, $description);
            if ($stmt->execute()) {
                $success = 'Thêm danh mục thành công';
            } else {
                $error = 'Có lỗi xảy ra';
            }
        }
    } elseif (isset($_POST['edit'])) {
        $id = (int)$_POST['id'];
        $name = $_POST['name'] ?? '';
        $slug = $_POST['slug'] ?? '';
        $description = $_POST['description'] ?? '';
        
        $stmt = $conn->prepare("UPDATE categories SET name = ?, slug = ?, description = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $slug, $description, $id);
        if ($stmt->execute()) {
            $success = 'Cập nhật danh mục thành công';
        } else {
            $error = 'Có lỗi xảy ra';
        }
    } elseif (isset($_POST['delete'])) {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $success = 'Xóa danh mục thành công';
        } else {
            $error = 'Có lỗi xảy ra (có thể danh mục đang được sử dụng)';
        }
    }
}

// Get categories
$categories = $conn->query("SELECT * FROM categories ORDER BY name");

// Get category to edit
$editCategory = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $result = $conn->query("SELECT * FROM categories WHERE id = $editId");
    $editCategory = $result->fetch_assoc();
}
?>

<?php require_once __DIR__ . '/includes/sidebar.php'; ?>

<div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <h2>Quản lý Danh mục</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo e($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo e($success); ?></div>
    <?php endif; ?>
    
    <!-- Add/Edit Form -->
    <div class="card mb-4">
        <div class="card-header">
            <h5><?php echo $editCategory ? 'Sửa danh mục' : 'Thêm danh mục mới'; ?></h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <?php if ($editCategory): ?>
                    <input type="hidden" name="id" value="<?php echo $editCategory['id']; ?>">
                <?php endif; ?>
                <div class="mb-3">
                    <label class="form-label">Tên danh mục *</label>
                    <input type="text" name="name" class="form-control" value="<?php echo $editCategory ? e($editCategory['name']) : ''; ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Slug *</label>
                    <input type="text" name="slug" class="form-control" value="<?php echo $editCategory ? e($editCategory['slug']) : ''; ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mô tả</label>
                    <textarea name="description" class="form-control" rows="3"><?php echo $editCategory ? e($editCategory['description']) : ''; ?></textarea>
                </div>
                <button type="submit" name="<?php echo $editCategory ? 'edit' : 'add'; ?>" class="btn btn-primary">
                    <?php echo $editCategory ? 'Cập nhật' : 'Thêm mới'; ?>
                </button>
                <?php if ($editCategory): ?>
                    <a href="/admin/categories.php" class="btn btn-secondary">Hủy</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <!-- Categories List -->
    <div class="card">
        <div class="card-header">
            <h5>Danh sách danh mục</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên</th>
                            <th>Slug</th>
                            <th>Mô tả</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($cat = $categories->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $cat['id']; ?></td>
                            <td><?php echo e($cat['name']); ?></td>
                            <td><?php echo e($cat['slug']); ?></td>
                            <td><?php echo e($cat['description']); ?></td>
                            <td>
                                <a href="/admin/categories.php?edit=<?php echo $cat['id']; ?>" class="btn btn-sm btn-warning">Sửa</a>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Xóa danh mục này?');">
                                    <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
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

