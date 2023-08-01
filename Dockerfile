FROM php:7.3-apache

RUN apt-get update && apt-get install -y sqlite3 

RUN docker-php-ext-configure pdo_mysql
RUN docker-php-ext-install pdo_mysql

RUN php -m
RUN a2enmod rewrite