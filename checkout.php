<?php
$pageTitle = 'Thanh toán';
require_once __DIR__ . '/includes/header.php';
requireLogin();

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    redirect('/cart.php');
}

$conn = getDBConnection();
$cart = $_SESSION['cart'];
$cartItems = [];
$total = 0;

foreach ($cart as $productId => $quantity) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND status = 'active'");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    
    if ($product) {
        $price = $product['sale_price'] ?: $product['price'];
        $subtotal = $price * $quantity;
        $total += $subtotal;
        
        $cartItems[] = [
            'product' => $product,
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
    }
}

$user = getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    if (empty($name) || empty($email) || empty($phone) || empty($address)) {
        $error = 'Vui lòng điền đầy đủ thông tin';
    } else {
        // Generate order number
        $orderNumber = 'ORD' . date('YmdHis') . rand(1000, 9999);
        
        // Create order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, order_number, name, email, phone, address, total_amount, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("isssssds", $_SESSION['user_id'], $orderNumber, $name, $email, $phone, $address, $total, $notes);
        
        if ($stmt->execute()) {
            $orderId = $conn->insert_id;
            
            // Create order items
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
            
            foreach ($cartItems as $item) {
                $price = $item['product']['sale_price'] ?: $item['product']['price'];
                $stmt->bind_param("iisdid", 
                    $orderId, 
                    $item['product']['id'], 
                    $item['product']['name'], 
                    $price, 
                    $item['quantity'], 
                    $item['subtotal']
                );
                $stmt->execute();
                
                // Update stock
                $conn->query("UPDATE products SET stock = stock - {$item['quantity']} WHERE id = {$item['product']['id']}");
            }
            
            // Update user info if changed
            if ($user['name'] !== $name || $user['email'] !== $email || $user['phone'] !== $phone || $user['address'] !== $address) {
                $updateStmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
                $updateStmt->bind_param("ssssi", $name, $email, $phone, $address, $_SESSION['user_id']);
                $updateStmt->execute();
                $success = 'Đơn hàng đã được đặt thành công! Vui lòng cập nhật thông tin cá nhân để tiện cho lần mua tiếp theo.';
            } else {
                $success = 'Đơn hàng đã được đặt thành công!';
            }
            
            // Clear cart
            $_SESSION['cart'] = [];
            
            // Redirect to order details
            redirect("/order.php?id={$orderId}");
        } else {
            $error = 'Có lỗi xảy ra, vui lòng thử lại';
        }
    }
}

// Pre-fill from user profile
$checkoutName = $user['name'] ?? '';
$checkoutEmail = $user['email'] ?? '';
$checkoutPhone = $user['phone'] ?? '';
$checkoutAddress = $user['address'] ?? '';
?>

<div class="container my-5">
    <h2>Thanh toán</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo e($error); ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Thông tin giao hàng</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Họ và tên *</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo e($checkoutName); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo e($checkoutEmail); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Số điện thoại *</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo e($checkoutPhone); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Địa chỉ *</label>
                            <textarea class="form-control" id="address" name="address" rows="3" required><?php echo e($checkoutAddress); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Ghi chú</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg w-100">Đặt hàng</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Đơn hàng</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($cartItems as $item): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <div>
                            <?php echo e($item['product']['name']); ?>
                            <small class="text-muted">x<?php echo $item['quantity']; ?></small>
                        </div>
                        <div><?php echo formatPrice($item['subtotal']); ?></div>
                    </div>
                    <?php endforeach; ?>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Tổng cộng:</strong>
                        <strong class="text-danger"><?php echo formatPrice($total); ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

