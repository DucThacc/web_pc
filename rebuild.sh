#!/bin/bash

# Script rebuild và restart dự án PC Store trên Ubuntu Server
# Sử dụng: bash rebuild.sh

echo "🚀 Bắt đầu rebuild PC Store..."

# Dừng containers
echo "⏹️  Dừng containers..."
docker-compose down

# Xóa volume cũ nếu có
echo "🗑️  Xóa volume cũ..."
docker volume rm pc_store_uploads_data 2>/dev/null || true

# Tạo thư mục uploads nếu chưa có
echo "📁 Tạo thư mục uploads..."
mkdir -p uploads/products
mkdir -p uploads/banners
chmod -R 777 uploads

# Rebuild images
echo "🔨 Rebuild Docker images..."
docker-compose build --no-cache

# Khởi động containers
echo "▶️  Khởi động containers..."
docker-compose up -d

# Đợi 5 giây cho containers khởi động
echo "⏳ Đợi containers khởi động..."
sleep 5

# Kiểm tra quyền trong container
echo "🔐 Kiểm tra quyền thư mục uploads..."
docker exec pc_store_web chmod -R 777 /var/www/html/uploads
docker exec pc_store_web chown -R www-data:www-data /var/www/html/uploads

# Hiển thị trạng thái
echo "📊 Trạng thái containers:"
docker-compose ps

echo ""
echo "✅ Hoàn tất! Truy cập:"
echo "   - Frontend: http://localhost:8080"
echo "   - Admin: http://localhost:8080/admin"
echo "   - phpMyAdmin: http://localhost:8081"
echo ""
echo "📝 Xem logs: docker-compose logs -f web"
echo "🛑 Dừng: docker-compose down"
