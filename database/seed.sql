-- Seed Data for PC Store
USE pc_store;

-- Tạo admin user (password: password)
-- Password hash cho "password" là: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
INSERT INTO users (name, email, password, phone, address, role) VALUES
('Admin', 'admin@pcstore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0123456789', '123 Admin Street', 'admin'),
('Nguyễn Văn A', 'user@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0987654321', '456 User Street', 'user');
-- Password cho cả 2 user: "password"

-- Categories
INSERT INTO categories (name, slug, description) VALUES
('PC Gaming', 'pc-gaming', 'Máy tính chơi game'),
('Mainboard', 'mainboard', 'Bo mạch chủ'),
('CPU', 'cpu', 'Bộ vi xử lý'),
('GPU', 'gpu', 'Card đồ họa'),
('RAM', 'ram', 'Bộ nhớ RAM'),
('SSD / HDD', 'ssd-hdd', 'Ổ cứng lưu trữ'),
('PSU', 'psu', 'Nguồn máy tính'),
('Case', 'case', 'Vỏ máy tính'),
('Monitor', 'monitor', 'Màn hình máy tính'),
('Phụ kiện', 'phu-kien', 'Phụ kiện máy tính');

-- Products
INSERT INTO products (name, slug, description, price, sale_price, stock, category_id, brand, sku, featured, status) VALUES
('PC Gaming RTX 4060', 'pc-gaming-rtx-4060', 'PC Gaming cấu hình mạnh với RTX 4060, Intel i5-12400F, 16GB RAM', 25000000, 22900000, 10, 1, 'Custom Build', 'PC001', TRUE, 'active'),
('PC Gaming RTX 4070', 'pc-gaming-rtx-4070', 'PC Gaming cao cấp với RTX 4070, Intel i7-13700F, 32GB RAM', 35000000, 32900000, 5, 1, 'Custom Build', 'PC002', TRUE, 'active'),
('ASUS ROG STRIX B650E-F', 'asus-rog-strix-b650e-f', 'Mainboard AMD AM5, hỗ trợ DDR5, PCIe 5.0', 7500000, 6990000, 15, 2, 'ASUS', 'MB001', FALSE, 'active'),
('MSI MAG B550 TOMAHAWK', 'msi-mag-b550-tomahawk', 'Mainboard AMD AM4, hỗ trợ PCIe 4.0', 4500000, NULL, 20, 2, 'MSI', 'MB002', FALSE, 'active'),
('Intel Core i5-13400F', 'intel-core-i5-13400f', 'CPU Intel 13th Gen, 10 cores, 16 threads', 5500000, 5290000, 30, 3, 'Intel', 'CPU001', TRUE, 'active'),
('AMD Ryzen 7 7700X', 'amd-ryzen-7-7700x', 'CPU AMD Ryzen 7000, 8 cores, 16 threads', 8500000, 8290000, 25, 3, 'AMD', 'CPU002', TRUE, 'active'),
('NVIDIA RTX 4060 8GB', 'nvidia-rtx-4060-8gb', 'Card đồ họa RTX 4060 8GB GDDR6', 12000000, 11500000, 12, 4, 'NVIDIA', 'GPU001', TRUE, 'active'),
('NVIDIA RTX 4070 12GB', 'nvidia-rtx-4070-12gb', 'Card đồ họa RTX 4070 12GB GDDR6X', 18000000, 17500000, 8, 4, 'NVIDIA', 'GPU002', TRUE, 'active'),
('Corsair Vengeance 16GB DDR4', 'corsair-vengeance-16gb-ddr4', 'RAM 16GB DDR4 3200MHz', 1500000, 1390000, 50, 5, 'Corsair', 'RAM001', FALSE, 'active'),
('Kingston Fury 32GB DDR5', 'kingston-fury-32gb-ddr5', 'RAM 32GB DDR5 5600MHz', 4500000, 4290000, 30, 5, 'Kingston', 'RAM002', FALSE, 'active'),
('Samsung 980 PRO 1TB', 'samsung-980-pro-1tb', 'SSD NVMe PCIe 4.0 1TB', 3500000, 3290000, 40, 6, 'Samsung', 'SSD001', FALSE, 'active'),
('Seagate BarraCuda 2TB', 'seagate-barracuda-2tb', 'HDD 2TB 7200RPM', 1500000, NULL, 60, 6, 'Seagate', 'HDD001', FALSE, 'active'),
('Corsair RM750x 750W', 'corsair-rm750x-750w', 'Nguồn 750W 80 Plus Gold', 3500000, 3290000, 25, 7, 'Corsair', 'PSU001', FALSE, 'active'),
('NZXT H7 Flow', 'nzxt-h7-flow', 'Case ATX Mid Tower, tản nhiệt tốt', 3500000, NULL, 20, 8, 'NZXT', 'CASE001', FALSE, 'active'),
('ASUS ROG Strix XG27AQ', 'asus-rog-strix-xg27aq', 'Màn hình 27 inch 1440p 170Hz', 12000000, 11500000, 15, 9, 'ASUS', 'MON001', FALSE, 'active'),
('Logitech G502 HERO', 'logitech-g502-hero', 'Chuột gaming có dây', 1500000, 1390000, 100, 10, 'Logitech', 'ACC001', FALSE, 'active');

-- Product Images
INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES
(1, 'pc1.jpg', TRUE, 1),
(2, 'pc2.jpg', TRUE, 1),
(3, 'mb1.jpg', TRUE, 1),
(4, 'mb2.jpg', TRUE, 1),
(5, 'cpu1.jpg', TRUE, 1),
(6, 'cpu2.jpg', TRUE, 1),
(7, 'gpu1.jpg', TRUE, 1),
(8, 'gpu2.jpg', TRUE, 1),
(9, 'ram1.jpg', TRUE, 1),
(10, 'ram2.jpg', TRUE, 1),
(11, 'ssd1.jpg', TRUE, 1),
(12, 'hdd1.jpg', TRUE, 1),
(13, 'psu1.jpg', TRUE, 1),
(14, 'case1.jpg', TRUE, 1),
(15, 'mon1.jpg', TRUE, 1),
(16, 'acc1.jpg', TRUE, 1);

-- Banners
INSERT INTO banners (title, image_path, link, position, sort_order, status) VALUES
('Black Friday Sale', 'banner1.jpg', '/products', 'home_slider', 1, 'active'),
('Giảm giá 50%', 'banner2.jpg', '/products?category=pc-gaming', 'home_slider', 2, 'active'),
('Sản phẩm mới', 'banner3.jpg', '/products?sort=newest', 'home_slider', 3, 'active');

