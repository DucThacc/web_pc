<?php
$pageTitle = 'Quản lý Đơn hàng';
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

$conn = getDBConnection();
$error = '';
$success = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = (int)$_POST['order_id'];
    $status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $orderId);
    if ($stmt->execute()) {
        $success = 'Cập nhật trạng thái thành công';
    } else {
        $error = 'Có lỗi xảy ra';
    }
}

// Get orders
$statusFilter = $_GET['status'] ?? '';
$where = '';
if ($statusFilter) {
    $where = "WHERE o.status = '" . $conn->real_escape_string($statusFilter) . "'";
}

$orders = $conn->query("SELECT o.*, u.name as user_name 
                        FROM orders o 
                        LEFT JOIN users u ON o.user_id = u.id 
                        $where
                        ORDER BY o.created_at DESC");

$statusLabels = [
    'pending' => 'Chờ xử lý',
    'confirmed' => 'Đã xác nhận',
    'processing' => 'Đang xử lý',
    'shipped' => 'Đang giao hàng',
    'delivered' => 'Đã giao hàng',
    'cancelled' => 'Đã hủy'
];

$statusColors = [
    'pending' => 'warning',
    'confirmed' => 'info',
    'processing' => 'primary',
    'shipped' => 'info',
    'delivered' => 'success',
    'cancelled' => 'danger'
];
?>

<?php require_once __DIR__ . '/includes/sidebar.php'; ?>

<div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <h2>Quản lý Đơn hàng</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo e($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo e($success); ?></div>
    <?php endif; ?>
    
    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="d-flex gap-2">
                <select name="status" class="form-select" style="width: auto;">
                    <option value="">Tất cả trạng thái</option>
                    <?php foreach ($statusLabels as $key => $label): ?>
                        <option value="<?php echo $key; ?>" <?php echo $statusFilter === $key ? 'selected' : ''; ?>>
                            <?php echo $label; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">Lọc</button>
                <a href="/admin/orders.php" class="btn btn-secondary">Xóa bộ lọc</a>
            </form>
        </div>
    </div>
    
    <!-- Orders List -->
    <div class="card">
        <div class="card-header">
            <h5>Danh sách đơn hàng</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $orders->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo e($order['order_number']); ?></td>
                            <td><?php echo e($order['name']); ?></td>
                            <td><?php echo formatPrice($order['total_amount']); ?></td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <select name="status" class="form-select form-select-sm d-inline-block" style="width: auto;" onchange="this.form.submit()">
                                        <?php foreach ($statusLabels as $key => $label): ?>
                                            <option value="<?php echo $key; ?>" <?php echo $order['status'] === $key ? 'selected' : ''; ?>>
                                                <?php echo $label; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                            <td>
                                <a href="/admin/order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">Xem chi tiết</a>
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

