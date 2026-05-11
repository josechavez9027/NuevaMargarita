FROM dunglas/frankenphp

RUN install-php-extensions mysqli pdo pdo_mysql

COPY . /app