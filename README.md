# PC Store - Website Bán Hàng PC & Linh Kiện Máy Tính

Website thương mại điện tử bán PC & linh kiện máy tính được xây dựng bằng PHP thuần, MySQL, HTML/CSS/JavaScript và Bootstrap.

## 🚀 Tính năng

### Trang Client (Người dùng)
- ✅ Trang chủ với banner slider, sản phẩm nổi bật/mới/bán chạy
- ✅ Danh mục sản phẩm (PC Gaming, Mainboard, CPU, GPU, RAM, SSD/HDD, PSU, Case, Monitor, Phụ kiện)
- ✅ Tìm kiếm, lọc và sắp xếp sản phẩm
- ✅ Trang chi tiết sản phẩm với hình ảnh, mô tả, đánh giá
- ✅ Giỏ hàng và thanh toán
- ✅ Đăng ký/Đăng nhập
- ✅ Trang cá nhân (xem/sửa thông tin, đổi mật khẩu, lịch sử đơn hàng)

### Trang Admin
- ✅ Dashboard với thống kê và biểu đồ
- ✅ Quản lý danh mục (CRUD)
- ✅ Quản lý sản phẩm (CRUD, upload nhiều ảnh)
- ✅ Quản lý đơn hàng (xem, cập nhật trạng thái)
- ✅ Quản lý banners/slider

## 📋 Yêu cầu hệ thống

- PHP 7.4 hoặc cao hơn (khuyến nghị PHP 8.1+)
- MySQL 5.7+ hoặc MariaDB 10.3+
- Apache với mod_rewrite (hoặc Nginx)
- Extension PHP: mysqli, pdo_mysql, gd, mbstring, zip

## 🛠️ Cài đặt

### 1. Local (VS Code + PHP + MySQL)

#### Bước 1: Clone/Copy project
```bash
cd /path/to/project
```

#### Bước 2: Cấu hình database
- Tạo database MySQL:
```sql
CREATE DATABASE pc_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

- Import schema và dữ liệu mẫu:
```bash
mysql -u root -p pc_store < database/schema.sql
mysql -u root -p pc_store < database/seed.sql
```

#### Bước 3: Cấu hình .env
Copy file `.env.example` thành `.env` và chỉnh sửa:
```env
DB_HOST=localhost
DB_NAME=pc_store
DB_USER=root
DB_PASS=your_password
DB_PORT=3306
APP_URL=http://localhost
APP_ENV=development
```

#### Bước 4: Tạo thư mục uploads
```bash
mkdir -p uploads/products
mkdir -p uploads/banners
chmod -R 777 uploads
```

#### Bước 5: Cấu hình web server

**Apache:**
- Đảm bảo mod_rewrite đã được bật
- DocumentRoot trỏ đến thư mục project
- File `.htaccess` đã được copy vào thư mục gốc

**PHP Built-in Server (để test nhanh):**
```bash
php -S localhost:8000 -t .
```
Truy cập: http://localhost:8000

#### Bước 6: Truy cập website
- Frontend: http://localhost (hoặc port bạn cấu hình)
- Admin: http://localhost/admin/
  - Email: admin@pcstore.com
  - Password: password

---

### 2. Docker + Docker Compose

#### Bước 1: Đảm bảo đã cài Docker và Docker Compose
```bash
docker --version
docker-compose --version
```

#### Bước 2: Build và chạy containers
```bash
docker-compose up -d --build
```

#### Bước 3: Kiểm tra containers đang chạy
```bash
docker-compose ps
```

#### Bước 4: Truy cập website
- Frontend: http://localhost:8080
- phpMyAdmin: http://localhost:8081
  - Server: db
  - Username: root
  - Password: rootpassword
- Admin: http://localhost:8080/admin/
  - Email: admin@pcstore.com
  - Password: password

#### Bước 5: Dừng containers
```bash
docker-compose down
```

#### Bước 6: Xóa tất cả (bao gồm database)
```bash
docker-compose down -v
```

**Lưu ý:** Database sẽ tự động được tạo và import dữ liệu mẫu khi container db khởi động lần đầu.

---

### 3. Deploy trên Azure

#### Option A: Azure App Service (PHP)

1. **Tạo App Service:**
   - Đăng nhập Azure Portal
   - Tạo Resource Group mới
   - Tạo App Service (chọn PHP 8.1)
   - Tạo MySQL Database (Azure Database for MySQL)

2. **Cấu hình Database:**
   - Ghi nhớ thông tin kết nối từ Azure Database for MySQL
   - Tạo database `pc_store`
   - Import file `database/schema.sql` và `database/seed.sql`

3. **Cấu hình App Service:**
   - Vào Configuration → Application Settings
   - Thêm các biến môi trường:
     ```
     DB_HOST=your-mysql-server.mysql.database.azure.com
     DB_NAME=pc_store
     DB_USER=your_user@your-mysql-server
     DB_PASS=your_password
     DB_PORT=3306
     APP_URL=https://your-app.azurewebsites.net
     APP_ENV=production
     ```

4. **Deploy code:**
   - Option 1: Deploy qua Git
     - Vào Deployment Center
     - Kết nối repository GitHub/GitLab
   - Option 2: Deploy qua FTP/VS Code Extension
     - Sử dụng Azure App Service Extension trong VS Code

5. **Cấu hình thư mục uploads:**
   - Tạo thư mục `public/uploads/products` và `public/uploads/banners`
   - Hoặc sử dụng Azure Blob Storage để lưu ảnh (cần chỉnh sửa code)

#### Option B: Azure VM (Ubuntu Server)

1. **Tạo VM:**
   - Tạo Ubuntu Server VM trên Azure
   - Mở port 80, 443, 22

2. **SSH vào VM và cài đặt:**
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Apache, PHP, MySQL
sudo apt install -y apache2 php php-mysql php-mbstring php-gd php-zip mysql-server

# Enable mod_rewrite
sudo a2enmod rewrite

# Clone/Copy project
cd /var/www/html
# Upload code vào đây (qua Git, SCP, hoặc FTP)

# Cấu hình Apache
sudo nano /etc/apache2/sites-available/000-default.conf
# Đảm bảo AllowOverride All trong <Directory /var/www/html>

# Tạo database
sudo mysql -u root -p
CREATE DATABASE pc_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
# Import schema và seed

# Cấu hình .env
nano .env
# Điền thông tin database

# Set permissions
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
sudo mkdir -p uploads/products uploads/banners
sudo chmod -R 777 uploads

# Restart Apache
sudo systemctl restart apache2
```

**📖 Xem hướng dẫn chi tiết:** [DEPLOY_UBUNTU.md](DEPLOY_UBUNTU.md)

---

### 4. Deploy trên AWS

#### Option A: AWS Elastic Beanstalk (PHP)

1. **Cài đặt EB CLI:**
```bash
pip install awsebcli
```

2. **Khởi tạo Elastic Beanstalk:**
```bash
eb init -p php-8.1 pc-store
```

3. **Tạo RDS MySQL Database:**
   - Vào AWS Console → RDS
   - Tạo MySQL database
   - Ghi nhớ Endpoint, Username, Password
   - Tạo database `pc_store` và import schema/seed

4. **Cấu hình Environment Variables:**
   - Vào Elastic Beanstalk Console → Configuration → Software
   - Thêm Environment Properties:
     ```
     DB_HOST=your-rds-endpoint.amazonaws.com
     DB_NAME=pc_store
     DB_USER=admin
     DB_PASS=your_password
     DB_PORT=3306
     APP_URL=http://your-app.elasticbeanstalk.com
     APP_ENV=production
     ```

5. **Deploy:**
```bash
eb create pc-store-env
# hoặc nếu đã có environment
eb deploy
```

#### Option B: AWS EC2 (Ubuntu Server)

1. **Tạo EC2 Instance:**
   - Launch EC2 Ubuntu Server
   - Tạo Security Group mở port 22, 80, 443
   - Tạo/key pair và SSH vào instance

2. **Cài đặt trên EC2:**
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install LAMP Stack
sudo apt install -y apache2 php php-mysql php-mbstring php-gd php-zip

# Install MySQL (hoặc sử dụng RDS)
sudo apt install -y mysql-server

# Enable mod_rewrite
sudo a2enmod rewrite
sudo systemctl restart apache2

# Clone/Copy project
cd /var/www/html
# Upload code

# Cấu hình database (nếu dùng RDS, skip bước này)
sudo mysql -u root -p
CREATE DATABASE pc_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
# Import schema và seed

# Cấu hình .env
nano .env

# Set permissions
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
sudo mkdir -p uploads/products uploads/banners
sudo chmod -R 777 uploads

# Cấu hình Apache
sudo nano /etc/apache2/sites-available/000-default.conf
# AllowOverride All

sudo systemctl restart apache2
```

3. **Tạo RDS (khuyến nghị):**
   - Tạo RDS MySQL instance
   - Cấu hình Security Group cho phép EC2 kết nối
   - Cập nhật .env với RDS endpoint

---

## 📁 Cấu trúc thư mục

```
pc-store/
├── admin/                 # Trang quản trị
│   ├── includes/
│   │   ├── sidebar.php
│   │   └── footer.php
│   ├── index.php         # Dashboard
│   ├── categories.php    # Quản lý danh mục
│   ├── products.php      # Quản lý sản phẩm
│   ├── orders.php        # Quản lý đơn hàng
│   ├── order.php         # Chi tiết đơn hàng
│   └── banners.php       # Quản lý banners
├── config/               # Cấu hình
│   ├── config.php
│   └── database.php
├── database/             # Database
│   ├── schema.sql        # Cấu trúc database
│   └── seed.sql          # Dữ liệu mẫu
├── includes/             # Shared includes
│   ├── functions.php     # Helper functions
│   ├── header.php        # Header
│   └── footer.php        # Footer
├── public/               # Public assets
│   ├── assets/
│   │   ├── css/
│   │   │   └── style.css
│   │   └── js/
│   │       └── main.js
│   └── api/
│       ├── cart.php
│       └── review.php
├── uploads/              # Uploaded files (tách ra ngoài public)
│   ├── products/
│   └── banners/
├── .env.example          # File cấu hình mẫu
├── .htaccess             # Apache rewrite rules
├── Dockerfile            # Docker image
├── docker-compose.yml    # Docker Compose config
├── index.php             # Trang chủ
├── products.php          # Danh sách sản phẩm
├── product.php           # Chi tiết sản phẩm
├── cart.php              # Giỏ hàng
├── checkout.php          # Thanh toán
├── profile.php           # Trang cá nhân
├── orders.php            # Đơn hàng của tôi
├── order.php             # Chi tiết đơn hàng
├── login.php             # Đăng nhập
├── register.php          # Đăng ký
└── logout.php            # Đăng xuất
```

## 🔐 Tài khoản mặc định

**Admin:**
- Email: `admin@pcstore.com`
- Password: `password`

**User:**
- Email: `user@example.com`
- Password: `password`

**Lưu ý:** Đổi mật khẩu ngay sau khi deploy!

## 🛡️ Bảo mật

- Mật khẩu được hash bằng `password_hash()` (bcrypt)
- SQL Injection được ngăn chặn bằng Prepared Statements
- XSS được ngăn chặn bằng `htmlspecialchars()`
- Session management an toàn
- Phân quyền admin/user
- File upload được validate (chỉ cho phép ảnh)

## 📝 Ghi chú

- File `.env` chứa thông tin nhạy cảm, không commit vào Git
- Thư mục `uploads` cần quyền ghi (chmod 777) - **ĐÃ TÁCH RA NGOÀI public/**
- Trên production, nên tắt `display_errors` trong PHP
- Khuyến nghị sử dụng HTTPS trên production
- Nên backup database thường xuyên
- **Cache busting tự động:** Ảnh có timestamp để tránh cache browser
- **Xem hướng dẫn deploy:** [DEPLOY_UBUNTU.md](DEPLOY_UBUNTU.md)

## 🐛 Troubleshooting

**Lỗi kết nối database:**
- Kiểm tra thông tin trong `.env`
- Đảm bảo MySQL đang chạy
- Kiểm tra firewall/security group

**Lỗi upload ảnh:**
- Kiểm tra quyền thư mục `uploads` (cần 777)
- Kiểm tra `upload_max_filesize` và `post_max_size` trong php.ini
- Xóa cache browser (Ctrl+Shift+R) nếu ảnh cũ vẫn hiển thị
- Xem chi tiết: [DEPLOY_UBUNTU.md](DEPLOY_UBUNTU.md)

**Lỗi 404:**
- Kiểm tra mod_rewrite đã được bật
- Kiểm tra file `.htaccess` đã tồn tại

## 📄 License

MIT License - Tự do sử dụng và chỉnh sửa

## 👨‍💻 Developer

Senior Fullstack Developer (PHP, MySQL, Docker, Cloud)

