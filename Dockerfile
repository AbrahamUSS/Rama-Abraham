FROM php:8.2-apache

# Instalar extensión PDO MySQL necesaria para PHP
RUN docker-php-ext-install pdo pdo_mysql

# Habilitar mod_rewrite para permitir las URLs amigables de .htaccess
RUN a2enmod rewrite

# Permitir Override de .htaccess en el directorio de Apache
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Copiar los archivos del proyecto al directorio web de Apache
COPY . /var/www/html/

# Ajustar permisos de lectura/escritura
RUN chown -R www-data:www-data /var/www/html/

EXPOSE 80
