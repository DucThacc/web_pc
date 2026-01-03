<?php
$pageTitle = 'Chi tiết Đơn hàng';
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

$orderId = (int)($_GET['id'] ?? 0);
if (!$orderId) {
    redirect('/admin/orders.php');
}

$conn = getDBConnection();

// Get order
$stmt = $conn->prepare("SELECT o.*, u.name as user_name, u.email as user_email 
                        FROM orders o 
                        LEFT JOIN users u ON o.user_id = u.id 
                        WHERE o.id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    redirect('/admin/orders.php');
}

// Get order items
$items = $conn->query("SELECT oi.*, p.slug 
                       FROM order_items oi 
                       LEFT JOIN products p ON oi.product_id = p.id 
                       WHERE oi.order_id = $orderId");

$statusLabels = [
    'pending' => 'Chờ xử lý',
    'confirmed' => 'Đã xác nhận',
    'processing' => 'Đang xử lý',
    'shipped' => 'Đang giao hàng',
    'delivered' => 'Đã giao hàng',
    'cancelled' => 'Đã hủy'
];
?>

<?php require_once __DIR__ . '/includes/sidebar.php'; ?>

<div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <h2>Chi tiết Đơn hàng</h2>
    
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
                        <span class="badge bg-<?php 
                            echo $order['status'] === 'pending' ? 'warning' : 
                                ($order['status'] === 'delivered' ? 'success' : 'info'); 
                        ?>">
                            <?php echo $statusLabels[$order['status']]; ?>
                        </span>
                    </p>
                    <form method="POST" class="mt-3">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <div class="d-flex gap-2">
                            <select name="status" class="form-select" style="width: auto;">
                                <?php foreach ($statusLabels as $key => $label): ?>
                                    <option value="<?php echo $key; ?>" <?php echo $order['status'] === $key ? 'selected' : ''; ?>>
                                        <?php echo $label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-primary">Cập nhật trạng thái</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card">
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
                    <h5>Thông tin khách hàng</h5>
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
        <a href="/admin/orders.php" class="btn btn-secondary">Quay lại</a>
    </div>
</div>

<?php
// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = (int)$_POST['order_id'];
    $status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $orderId);
    if ($stmt->execute()) {
        redirect("/admin/order.php?id=$orderId");
    }
}
?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

