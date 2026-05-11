FROM php:8.2-apache
 
# Corregir error "AH00534: More than one MPM loaded"
# Deshabilitar mpm_event/worker y dejar solo mpm_prefork (compatible con mysqli)
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true && \
    a2enmod mpm_prefork
 
# Instalar extensión mysqli
RUN docker-php-ext-install mysqli pdo pdo_mysql && \
    docker-php-ext-enable mysqli
 
# Habilitar mod_rewrite
RUN a2enmod rewrite
 
# Copiar archivos del proyecto
COPY . /var/www/html/
 
# Limpiar archivos de config del directorio web (no deben ser accesibles)
RUN rm -f /var/www/html/start.sh /var/www/html/railway.toml
 
# Permisos
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html
 
# Script de inicio para ajustar el puerto dinámico de Railway ($PORT)
COPY start.sh /start.sh
RUN chmod +x /start.sh
 
EXPOSE 80
 
CMD ["/start.sh"]
 