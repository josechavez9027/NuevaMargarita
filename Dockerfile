FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql

ENV SERVER_NAME=":8080"

COPY . /app/public