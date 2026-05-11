FROM php:8.2-apache
 
RUN docker-php-ext-install mysqli pdo pdo_mysql
 
COPY . /var/www/html/
 
RUN rm -f /var/www/html/Dockerfile /var/www/html/railway.toml
 
EXPOSE 80
 
CMD ["apache2-foreground"]