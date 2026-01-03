<?php
$pageTitle = 'Quản lý Banners';
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

$conn = getDBConnection();
$error = '';
$success = '';

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add']) || isset($_POST['edit'])) {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $title = $_POST['title'] ?? '';
        $link = $_POST['link'] ?? '';
        $position = $_POST['position'] ?? 'home_slider';
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $status = $_POST['status'] ?? 'active';
        
        require_once __DIR__ . '/../includes/functions.php';
        
        if ($id > 0) {
            // Edit
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = uploadFile($_FILES['image'], 'banners');
                if ($uploadResult['success']) {
                    // Delete old image
                    $oldImage = $conn->query("SELECT image_path FROM banners WHERE id = $id")->fetch_assoc();
                    if ($oldImage) {
                        deleteFile($oldImage['image_path'], 'banners');
                    }
                    $stmt = $conn->prepare("UPDATE banners SET title = ?, image_path = ?, link = ?, position = ?, sort_order = ?, status = ? WHERE id = ?");
                    $stmt->bind_param("ssssisi", $title, $uploadResult['filename'], $link, $position, $sortOrder, $status, $id);
                } else {
                    $error = $uploadResult['message'];
                    goto skip_update;
                }
            } else {
                $stmt = $conn->prepare("UPDATE banners SET title = ?, link = ?, position = ?, sort_order = ?, status = ? WHERE id = ?");
                $stmt->bind_param("sssisi", $title, $link, $position, $sortOrder, $status, $id);
            }
            if ($stmt->execute()) {
                $success = 'Cập nhật banner thành công';
            } else {
                $error = 'Có lỗi xảy ra';
            }
            skip_update:
        } else {
            // Add
            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                $error = 'Vui lòng chọn ảnh';
            } else {
                $uploadResult = uploadFile($_FILES['image'], 'banners');
                if ($uploadResult['success']) {
                    $stmt = $conn->prepare("INSERT INTO banners (title, image_path, link, position, sort_order, status) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssis", $title, $uploadResult['filename'], $link, $position, $sortOrder, $status);
                    if ($stmt->execute()) {
                        $success = 'Thêm banner thành công';
                    } else {
                        $error = 'Có lỗi xảy ra';
                    }
                } else {
                    $error = $uploadResult['message'];
                }
            }
        }
    } elseif (isset($_POST['delete'])) {
        $id = (int)$_POST['id'];
        require_once __DIR__ . '/../includes/functions.php';
        $banner = $conn->query("SELECT image_path FROM banners WHERE id = $id")->fetch_assoc();
        if ($banner) {
            deleteFile($banner['image_path'], 'banners');
        }
        $stmt = $conn->prepare("DELETE FROM banners WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $success = 'Xóa banner thành công';
        } else {
            $error = 'Có lỗi xảy ra';
        }
    }
}

// Get banners
$banners = $conn->query("SELECT * FROM banners ORDER BY position, sort_order");

// Get banner to edit
$editBanner = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $result = $conn->query("SELECT * FROM banners WHERE id = $editId");
    $editBanner = $result->fetch_assoc();
}
?>

<?php require_once __DIR__ . '/includes/sidebar.php'; ?>

<div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <h2>Quản lý Banners</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo e($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo e($success); ?></div>
    <?php endif; ?>
    
    <!-- Add/Edit Form -->
    <div class="card mb-4">
        <div class="card-header">
            <h5><?php echo $editBanner ? 'Sửa banner' : 'Thêm banner mới'; ?></h5>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <?php if ($editBanner): ?>
                    <input type="hidden" name="id" value="<?php echo $editBanner['id']; ?>">
                <?php endif; ?>
                <div class="mb-3">
                    <label class="form-label">Tiêu đề</label>
                    <input type="text" name="title" class="form-control" value="<?php echo $editBanner ? e($editBanner['title']) : ''; ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Hình ảnh <?php echo $editBanner ? '' : '*'; ?></label>
                    <input type="file" name="image" class="form-control" accept="image/*" <?php echo $editBanner ? '' : 'required'; ?>>
                    <?php if ($editBanner): ?>
                        <small class="text-muted">Để trống nếu không muốn thay đổi ảnh</small>
                        <div class="mt-2">
                            <img src="/uploads/banners/<?php echo e($editBanner['image_path']); ?>" style="max-width: 300px; height: auto;" alt="Banner">
                        </div>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label">Link</label>
                    <input type="text" name="link" class="form-control" value="<?php echo $editBanner ? e($editBanner['link']) : ''; ?>" placeholder="/products hoặc URL">
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Vị trí</label>
                            <select name="position" class="form-select">
                                <option value="home_slider" <?php echo ($editBanner && $editBanner['position'] === 'home_slider') ? 'selected' : ''; ?>>Slider trang chủ</option>
                                <option value="home_banner" <?php echo ($editBanner && $editBanner['position'] === 'home_banner') ? 'selected' : ''; ?>>Banner trang chủ</option>
                                <option value="category" <?php echo ($editBanner && $editBanner['position'] === 'category') ? 'selected' : ''; ?>>Danh mục</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Thứ tự sắp xếp</label>
                            <input type="number" name="sort_order" class="form-control" value="<?php echo $editBanner ? $editBanner['sort_order'] : '0'; ?>">
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="active" <?php echo ($editBanner && $editBanner['status'] === 'active') ? 'selected' : ''; ?>>Kích hoạt</option>
                        <option value="inactive" <?php echo ($editBanner && $editBanner['status'] === 'inactive') ? 'selected' : ''; ?>>Tắt</option>
                    </select>
                </div>
                <button type="submit" name="<?php echo $editBanner ? 'edit' : 'add'; ?>" class="btn btn-primary">
                    <?php echo $editBanner ? 'Cập nhật' : 'Thêm mới'; ?>
                </button>
                <?php if ($editBanner): ?>
                    <a href="/admin/banners.php" class="btn btn-secondary">Hủy</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <!-- Banners List -->
    <div class="card">
        <div class="card-header">
            <h5>Danh sách banners</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ảnh</th>
                            <th>Tiêu đề</th>
                            <th>Link</th>
                            <th>Vị trí</th>
                            <th>Thứ tự</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($banner = $banners->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <img src="/uploads/banners/<?php echo e($banner['image_path']); ?>" style="width: 100px; height: auto; max-height: 60px; object-fit: cover;" alt="Banner">
                            </td>
                            <td><?php echo e($banner['title']); ?></td>
                            <td><?php echo e($banner['link']); ?></td>
                            <td><?php echo e($banner['position']); ?></td>
                            <td><?php echo $banner['sort_order']; ?></td>
                            <td>
                                <span class="badge bg-<?php echo $banner['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo e($banner['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="/admin/banners.php?edit=<?php echo $banner['id']; ?>" class="btn btn-sm btn-warning">Sửa</a>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Xóa banner này?');">
                                    <input type="hidden" name="id" value="<?php echo $banner['id']; ?>">
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

