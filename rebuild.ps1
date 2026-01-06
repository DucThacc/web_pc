# Script rebuild và restart dự án PC Store trên Windows
# Sử dụng: .\rebuild.ps1

Write-Host "🚀 Bắt đầu rebuild PC Store..." -ForegroundColor Green

# Dừng containers
Write-Host "⏹️  Dừng containers..." -ForegroundColor Yellow
docker-compose down

# Xóa volume cũ nếu có
Write-Host "🗑️  Xóa volume cũ..." -ForegroundColor Yellow
docker volume rm pc_store_uploads_data 2>$null

# Tạo thư mục uploads nếu chưa có
Write-Host "📁 Tạo thư mục uploads..." -ForegroundColor Yellow
New-Item -ItemType Directory -Force -Path "uploads\products" | Out-Null
New-Item -ItemType Directory -Force -Path "uploads\banners" | Out-Null

# Rebuild images
Write-Host "🔨 Rebuild Docker images..." -ForegroundColor Yellow
docker-compose build --no-cache

# Khởi động containers
Write-Host "▶️  Khởi động containers..." -ForegroundColor Yellow
docker-compose up -d

# Đợi 5 giây cho containers khởi động
Write-Host "⏳ Đợi containers khởi động..." -ForegroundColor Yellow
Start-Sleep -Seconds 5

# Kiểm tra quyền trong container
Write-Host "🔐 Kiểm tra quyền thư mục uploads..." -ForegroundColor Yellow
docker exec pc_store_web chmod -R 777 /var/www/html/uploads
docker exec pc_store_web chown -R www-data:www-data /var/www/html/uploads

# Hiển thị trạng thái
Write-Host "📊 Trạng thái containers:" -ForegroundColor Cyan
docker-compose ps

Write-Host ""
Write-Host "✅ Hoàn tất! Truy cập:" -ForegroundColor Green
Write-Host "   - Frontend: http://localhost:8080" -ForegroundColor White
Write-Host "   - Admin: http://localhost:8080/admin" -ForegroundColor White
Write-Host "   - phpMyAdmin: http://localhost:8081" -ForegroundColor White
Write-Host ""
Write-Host "📝 Xem logs: docker-compose logs -f web" -ForegroundColor Cyan
Write-Host "🛑 Dừng: docker-compose down" -ForegroundColor Cyan
