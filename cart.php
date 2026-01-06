<?php
$pageTitle = 'Giỏ hàng';
require_once __DIR__ . '/includes/header.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$conn = getDBConnection();
$cart = $_SESSION['cart'];
$cartItems = [];
$total = 0;

foreach ($cart as $productId => $quantity) {
    $stmt = $conn->prepare("SELECT p.*, 
                                   (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
                            FROM products p 
                            WHERE p.id = ? AND p.status = 'active'");
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

// Handle update/remove
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        foreach ($_POST['quantity'] as $productId => $quantity) {
            if ($quantity > 0) {
                $_SESSION['cart'][$productId] = (int)$quantity;
            } else {
                unset($_SESSION['cart'][$productId]);
            }
        }
        redirect('/cart.php');
    } elseif (isset($_POST['remove'])) {
        unset($_SESSION['cart'][$_POST['product_id']]);
        redirect('/cart.php');
    }
}
?>

<div class="container my-5">
    <h2>Giỏ hàng</h2>
    
    <?php if (empty($cartItems)): ?>
        <div class="alert alert-info">
            <p>Giỏ hàng của bạn đang trống.</p>
            <a href="/products.php" class="btn btn-primary">Tiếp tục mua sắm</a>
        </div>
    <?php else: ?>
        <form method="POST">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Giá</th>
                            <th>Số lượng</th>
                            <th>Tổng</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                        <tr class="cart-item">
                            <td>
                                <div class="d-flex">
                                    <img src="<?php echo getImageUrl($item['product']['image'] ?: 'no-image.jpg', 'products'); ?>" 
                                         alt="<?php echo e($item['product']['name']); ?>" 
                                         style="width: 80px; height: 80px; object-fit: cover;">
                                    <div class="ms-3">
                                        <a href="/product.php?slug=<?php echo e($item['product']['slug']); ?>" class="text-decoration-none">
                                            <?php echo e($item['product']['name']); ?>
                                        </a>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo formatPrice($item['product']['sale_price'] ?: $item['product']['price']); ?></td>
                            <td>
                                <input type="number" 
                                       name="quantity[<?php echo $item['product']['id']; ?>]" 
                                       value="<?php echo $item['quantity']; ?>" 
                                       min="1" 
                                       max="<?php echo $item['product']['stock']; ?>"
                                       class="form-control" 
                                       style="width: 80px;">
                            </td>
                            <td><strong><?php echo formatPrice($item['subtotal']); ?></strong></td>
                            <td>
                                <button type="submit" name="remove" value="1" class="btn btn-sm btn-danger" onclick="return confirm('Xóa sản phẩm này?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Tổng cộng:</strong></td>
                            <td><strong class="text-danger"><?php echo formatPrice($total); ?></strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="d-flex justify-content-between mt-4">
                <a href="/products.php" class="btn btn-secondary">Tiếp tục mua sắm</a>
                <div>
                    <button type="submit" name="update" class="btn btn-warning">Cập nhật giỏ hàng</button>
                    <a href="/checkout.php" class="btn btn-primary">Thanh toán</a>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

