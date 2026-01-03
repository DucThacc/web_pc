<div class="container-fluid">
    <div class="row">
        <nav class="col-md-3 col-lg-2 sidebar d-md-block bg-light">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>" href="/admin/">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'category') !== false ? 'active' : ''; ?>" href="/admin/categories.php">
                            <i class="bi bi-tags"></i> Danh mục
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'product') !== false ? 'active' : ''; ?>" href="/admin/products.php">
                            <i class="bi bi-box"></i> Sản phẩm
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'order') !== false ? 'active' : ''; ?>" href="/admin/orders.php">
                            <i class="bi bi-cart-check"></i> Đơn hàng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'banner') !== false ? 'active' : ''; ?>" href="/admin/banners.php">
                            <i class="bi bi-image"></i> Banners
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

