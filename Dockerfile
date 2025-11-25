# Sử dụng image PHP kèm Apache
FROM php:8.1-apache

# Cài đặt extension mysqli để kết nối MySQL (thường dùng trong các bài tập PHP)
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Copy toàn bộ code vào thư mục web của Apache
COPY . /var/www/html/

# Mở port 80
EXPOSE 80