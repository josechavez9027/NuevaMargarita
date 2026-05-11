FROM php:8.1-apache
 
# Deshabilitar mpm_event, activar mpm_prefork (evita "More than one MPM loaded")
RUN a2dismod mpm_event 2>/dev/null; \
    a2enmod mpm_prefork; \
    a2enmod rewrite
 
# Instalar mysqli
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
 
# Copiar proyecto
COPY . /var/www/html/
 
# Limpiar archivos de config del webroot
RUN rm -f /var/www/html/Dockerfile /var/www/html/railway.toml /var/www/html/start.sh
 
# Permisos
RUN chown -R www-data:www-data /var/www/html
 
# Configurar Apache para usar $PORT de Railway
CMD bash -c "sed -i \"s/80/\${PORT:-80}/g\" /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf && apache2-foreground"