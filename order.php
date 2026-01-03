<?php
$pageTitle = 'Chi tiết đơn hàng';
require_once __DIR__ . '/includes/header.php';
requireLogin();

$orderId = (int)($_GET['id'] ?? 0);
if (!$orderId) {
    redirect('/orders.php');
}

$conn = getDBConnection();

// Get order
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $orderId, $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    redirect('/orders.php');
}

// Get order items
$items = $conn->query("SELECT oi.*, p.slug 
                       FROM order_items oi 
                       LEFT JOIN products p ON oi.product_id = p.id 
                       WHERE oi.order_id = {$orderId}");

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
    <h2>Chi tiết đơn hàng</h2>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Thông tin đơn hàng</h5>
                </div>
                <div class="card-body">
                    <p><strong>Mã đơn:</strong> <?php echo e($order['order_number']); ?></p>
                    <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                    <p><strong>Trạng thái:</strong> 
                        <span class="badge bg-<?php echo $statusColors[$order['status']]; ?>">
                            <?php echo $statusLabels[$order['status']]; ?>
                        </span>
                    </p>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Sản phẩm</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Giá</th>
                                    <th>Số lượng</th>
                                    <th>Tổng</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($item = $items->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?php if ($item['slug']): ?>
                                            <a href="/product.php?slug=<?php echo e($item['slug']); ?>">
                                                <?php echo e($item['product_name']); ?>
                                            </a>
                                        <?php else: ?>
                                            <?php echo e($item['product_name']); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo formatPrice($item['product_price']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td><?php echo formatPrice($item['subtotal']); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Tổng cộng:</strong></td>
                                    <td><strong class="text-danger"><?php echo formatPrice($order['total_amount']); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Thông tin giao hàng</h5>
                </div>
                <div class="card-body">
                    <p><strong>Họ tên:</strong><br><?php echo e($order['name']); ?></p>
                    <p><strong>Email:</strong><br><?php echo e($order['email']); ?></p>
                    <p><strong>Điện thoại:</strong><br><?php echo e($order['phone']); ?></p>
                    <p><strong>Địa chỉ:</strong><br><?php echo nl2br(e($order['address'])); ?></p>
                    <?php if ($order['notes']): ?>
                        <p><strong>Ghi chú:</strong><br><?php echo nl2br(e($order['notes'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-4">
        <a href="/orders.php" class="btn btn-secondary">Quay lại danh sách đơn hàng</a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

