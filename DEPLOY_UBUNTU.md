# Hướng dẫn Deploy trên Ubuntu Server 22.04

## 🚀 Các bước deploy

### 1. SSH vào server Ubuntu
```bash
ssh your-user@your-server-ip
```

### 2. Cài đặt Docker và Docker Compose
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Cài Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Cài Docker Compose
sudo apt install docker-compose -y

# Thêm user vào docker group
sudo usermod -aG docker $USER
newgrp docker

# Kiểm tra
docker --version
docker-compose --version
```

### 3. Upload source code lên server
```bash
# Option 1: Clone từ Git
cd /home/your-user
git clone your-repo-url pc-store
cd pc-store

# Option 2: Upload qua SCP (từ máy local)
scp -r /path/to/pc-store your-user@your-server-ip:/home/your-user/
```

### 4. Tạo thư mục uploads
```bash
cd /home/your-user/pc-store
mkdir -p uploads/products
mkdir -p uploads/banners
chmod -R 777 uploads
```

### 5. Build và chạy Docker containers
```bash
# Build và chạy
docker-compose up -d --build

# Kiểm tra containers
docker-compose ps

# Xem logs
docker-compose logs -f web
```

### 6. Kiểm tra quyền thư mục uploads
```bash
docker exec pc_store_web ls -la /var/www/html/uploads
docker exec pc_store_web chmod -R 777 /var/www/html/uploads
```

### 7. Truy cập website
- Frontend: http://your-server-ip:8080
- phpMyAdmin: http://your-server-ip:8081
- Admin: http://your-server-ip:8080/admin
  - Email: admin@pcstore.com
  - Password: password

---

## 🔧 Sửa lỗi upload ảnh

### Vấn đề: Ảnh upload không hiển thị hoặc cập nhật không nhận

**Nguyên nhân:**
1. Thư mục uploads không có quyền ghi
2. Docker volume mount sai
3. Browser cache ảnh cũ

**Giải pháp đã áp dụng:**

1. **Đã bỏ volume riêng cho uploads** - Giờ ảnh lưu trực tiếp trong source code
2. **Đã thêm cache busting** - URL ảnh có timestamp để tránh cache
3. **Đã sửa đường dẫn** - Ảnh lưu ở `/uploads` thay vì `/public/uploads`

### Kiểm tra nếu vẫn lỗi:

```bash
# 1. Kiểm tra quyền thư mục
docker exec pc_store_web ls -la /var/www/html/uploads

# 2. Set quyền nếu cần
docker exec pc_store_web chmod -R 777 /var/www/html/uploads
docker exec pc_store_web chown -R www-data:www-data /var/www/html/uploads

# 3. Restart container
docker-compose restart web

# 4. Clear browser cache
# Ctrl+Shift+R (Chrome) hoặc Ctrl+F5 (Firefox)
```

---

## 🔄 Cập nhật code sau khi sửa

```bash
# 1. Pull code mới (nếu dùng Git)
cd /home/your-user/pc-store
git pull

# 2. Rebuild containers
docker-compose down
docker-compose up -d --build

# 3. Kiểm tra
docker-compose ps
docker-compose logs -f web
```

---

## 🐛 Troubleshooting

### Lỗi: Cannot connect to database
```bash
# Kiểm tra database container
docker-compose ps
docker-compose logs db

# Restart database
docker-compose restart db
```

### Lỗi: Permission denied khi upload
```bash
# Fix quyền uploads
docker exec pc_store_web chmod -R 777 /var/www/html/uploads
```

### Lỗi: 404 Not Found
```bash
# Kiểm tra Apache config
docker exec pc_store_web cat /etc/apache2/sites-available/000-default.conf

# Restart Apache
docker-compose restart web
```

### Lỗi: Logout không hoạt động
- **Đã sửa:** Thêm session_unset() và xóa cookie
- Nếu vẫn lỗi, clear browser cache

---

## 📦 Backup & Restore

### Backup Database
```bash
docker exec pc_store_db mysqldump -u root -prootpassword pc_store > backup_$(date +%Y%m%d).sql
```

### Backup Uploads
```bash
cd /home/your-user/pc-store
tar -czf uploads_backup_$(date +%Y%m%d).tar.gz uploads/
```

### Restore Database
```bash
docker exec -i pc_store_db mysql -u root -prootpassword pc_store < backup_20260106.sql
```

### Restore Uploads
```bash
cd /home/your-user/pc-store
tar -xzf uploads_backup_20260106.tar.gz
chmod -R 777 uploads/
```

---

## 🔐 Bảo mật (Production)

### 1. Đổi mật khẩu mặc định
- Admin: admin@pcstore.com / password
- Database: root / rootpassword

### 2. Tắt phpMyAdmin (production)
```yaml
# Trong docker-compose.yml, comment section phpmyadmin
```

### 3. Sử dụng HTTPS
```bash
# Cài Nginx reverse proxy với Let's Encrypt
sudo apt install nginx certbot python3-certbot-nginx
```

### 4. Firewall
```bash
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 22/tcp
sudo ufw enable
```

---

## 📝 Lưu ý quan trọng

✅ **Thư mục uploads giờ nằm trong source code** - Dễ deploy và backup
✅ **Cache busting tự động** - Ảnh mới sẽ hiển thị ngay lập tức
✅ **Logout đã được sửa** - Session được xóa hoàn toàn
✅ **Docker không dùng volume riêng** - Mọi thứ trong source code

❌ **Không nên** - Xóa thư mục uploads khi đang chạy
❌ **Không nên** - Thay đổi quyền uploads về 755 (cần 777 để upload)
❌ **Không nên** - Dùng volume mount riêng cho uploads nữa
