FROM composer:latest

RUN docker-php-ext-install pdo pdo_mysql

RUN apk --no-cache -q add autoconf g++ make \ 
  && pecl -q install xdebug \
  && docker-php-ext-enable xdebug \
  && apk del autoconf g++ make