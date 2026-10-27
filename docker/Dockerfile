# Dependências PHP (PHPMailer) instaladas com Composer, fora da imagem final
FROM composer:2 AS vendor
WORKDIR /app
COPY app/composer.json app/composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --ignore-platform-reqs

FROM php:8.3-apache
RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite \
    && sed -ri 's!(<Directory /var/www/>[^<]*AllowOverride) None!\1 All!' /etc/apache2/apache2.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-gm-helpdesk.ini
COPY app/ /var/www/html/
COPY --from=vendor /app/vendor /var/www/html/vendor
RUN chown -R www-data:www-data /var/www/html/uploads /var/www/html/assets/img/perfil
