<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

$conn = getDBConnection();

// Statistics
$totalOrders = $conn->query("SELECT COUNT(*) as total FROM orders")->fetch_assoc()['total'];
$totalRevenue = $conn->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE status != 'cancelled'")->fetch_assoc()['total'];
$totalProducts = $conn->query("SELECT COUNT(*) as total FROM products WHERE status = 'active'")->fetch_assoc()['total'];
$pendingOrders = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'")->fetch_assoc()['total'];

// Recent orders
$recentOrders = $conn->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 10");

// Weekly revenue (last 7 days)
$weeklyRevenue = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $result = $conn->query("SELECT COALESCE(SUM(total_amount), 0) as total 
                            FROM orders 
                            WHERE DATE(created_at) = '$date' AND status != 'cancelled'");
    $weeklyRevenue[$date] = $result->fetch_assoc()['total'];
}

// Monthly revenue (last 12 months)
$monthlyRevenue = [];
for ($i = 11; $i >= 0; $i--) {
    $date = date('Y-m', strtotime("-$i months"));
    $result = $conn->query("SELECT COALESCE(SUM(total_amount), 0) as total 
                            FROM orders 
                            WHERE DATE_FORMAT(created_at, '%Y-%m') = '$date' AND status != 'cancelled'");
    $monthlyRevenue[$date] = $result->fetch_assoc()['total'];
}
?>

<?php require_once __DIR__ . '/includes/sidebar.php'; ?>

<div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <h2>Dashboard</h2>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="dashboard-stat">
                <h6 class="text-muted">Tổng đơn hàng</h6>
                <h3><?php echo number_format($totalOrders); ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-stat">
                <h6 class="text-muted">Doanh thu</h6>
                <h3><?php echo formatPrice($totalRevenue); ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-stat">
                <h6 class="text-muted">Sản phẩm</h6>
                <h3><?php echo number_format($totalProducts); ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-stat">
                <h6 class="text-muted">Đơn chờ xử lý</h6>
                <h3><?php echo number_format($pendingOrders); ?></h3>
            </div>
        </div>
    </div>
    
    <!-- Charts -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Doanh thu 7 ngày qua</h5>
                </div>
                <div class="card-body">
                    <canvas id="weeklyChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Doanh thu 12 tháng qua</h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Orders -->
    <div class="card">
        <div class="card-header">
            <h5>Đơn hàng gần đây</h5>
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
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $recentOrders->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo e($order['order_number']); ?></td>
                            <td><?php echo e($order['name']); ?></td>
                            <td><?php echo formatPrice($order['total_amount']); ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $order['status'] === 'pending' ? 'warning' : 
                                        ($order['status'] === 'delivered' ? 'success' : 'info'); 
                                ?>">
                                    <?php echo e($order['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                            <td>
                                <a href="/admin/order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">Xem</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Weekly Chart
const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
new Chart(weeklyCtx, {
    type: 'line',
    data: {
        labels: [<?php echo implode(',', array_map(function($date) { return "'" . date('d/m', strtotime($date)) . "'"; }, array_keys($weeklyRevenue))); ?>],
        datasets: [{
            label: 'Doanh thu (₫)',
            data: [<?php echo implode(',', array_values($weeklyRevenue)); ?>],
            borderColor: 'rgb(75, 192, 192)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true
    }
});

// Monthly Chart
const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
new Chart(monthlyCtx, {
    type: 'bar',
    data: {
        labels: [<?php echo implode(',', array_map(function($date) { return "'" . $date . "'"; }, array_keys($monthlyRevenue))); ?>],
        datasets: [{
            label: 'Doanh thu (₫)',
            data: [<?php echo implode(',', array_values($monthlyRevenue)); ?>],
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

