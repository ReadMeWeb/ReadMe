FROM php:apache

RUN sh -c 'echo  file_uploads = On > /usr/local/etc/php/conf.d/php.ini'
RUN docker-php-ext-install mysqli
RUN docker-php-ext-install exif
