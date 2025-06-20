# Gunakan Nginx sebagai base image untuk menyajikan file statis
FROM nginx:alpine

# Hapus konfigurasi default Nginx
RUN rm /etc/nginx/conf.d/default.conf

# Salin file konfigurasi Nginx kustom Anda
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Salin file HTML ke direktori default Nginx
COPY . /usr/share/nginx/html

# Port yang akan diekspos oleh container
EXPOSE 80

# Perintah untuk memulai Nginx saat container dijalankan
CMD ["nginx", "-g", "daemon off;"]
