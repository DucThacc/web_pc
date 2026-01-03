<?php
$pageTitle = 'Đơn hàng của tôi';
require_once __DIR__ . '/includes/header.php';
requireLogin();

$conn = getDBConnection();

// Get orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$orders = $stmt->get_result();

// Status labels
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

<div class="container my-5">
    <h2>Đơn hàng của tôi</h2>
    
    <?php if ($orders->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Ngày đặt</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $orders->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo e($order['order_number']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                        <td><?php echo formatPrice($order['total_amount']); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $statusColors[$order['status']]; ?>">
                                <?php echo $statusLabels[$order['status']]; ?>
                            </span>
                        </td>
                        <td>
                            <a href="/order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">Xem chi tiết</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <p>Bạn chưa có đơn hàng nào.</p>
            <a href="/products.php" class="btn btn-primary">Mua sắm ngay</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

