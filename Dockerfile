FROM php:8.2-apache
 
# Instalar extensión mysqli (necesaria para el proyecto)
RUN docker-php-ext-install mysqli pdo pdo_mysql && \
    docker-php-ext-enable mysqli
 
# Habilitar mod_rewrite de Apache
RUN a2enmod rewrite
 
# Copiar todos los archivos del proyecto al directorio de Apache
COPY . /var/www/html/
 
# Dar permisos correctos
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html
 
# Copiar y hacer ejecutable el script de inicio
COPY start.sh /start.sh
RUN chmod +x /start.sh
 

CMD ["/start.sh"]
 