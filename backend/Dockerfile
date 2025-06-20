# Gunakan image PHP dengan Apache
FROM php:8.2-apache

# Menginstal ekstensi mysqli yang diperlukan untuk koneksi MySQL
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Salin file PHP ke direktori kerja Apache
COPY . /var/www/html/

# Ubah kepemilikan file agar Apache dapat membacanya
RUN chown -R www-data:www-data /var/www/html/

# Port yang akan diekspos oleh container
EXPOSE 80

# Apache sudah dikonfigurasi untuk menjalankan PHP, tidak perlu CMD tambahan
