FROM unit:1.33.0-php8.2

RUN apt update && apt install -y \
    curl unzip git libicu-dev libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libssl-dev libavif-dev supervisor \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-avif \
    && docker-php-ext-install -j$(nproc) pcntl opcache pdo pdo_mysql intl zip gd exif ftp bcmath calendar \
    && pecl install redis \
    && docker-php-ext-enable redis

RUN echo "opcache.enable=1" > /usr/local/etc/php/conf.d/custom.ini \
    && echo "opcache.jit=tracing" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "opcache.jit_buffer_size=256M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "memory_limit=512M" > /usr/local/etc/php/conf.d/custom.ini \        
    && echo "upload_max_filesize=64M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "post_max_size=64M" >> /usr/local/etc/php/conf.d/custom.ini

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/prestamos

RUN mkdir -p /var/www/prestamos/storage /var/www/prestamos/bootstrap/cache

RUN chown -R unit:unit /var/www/prestamos/storage bootstrap/cache && chmod -R 775 /var/www/prestamos/storage

COPY . .

RUN chown -R unit:unit storage bootstrap/cache && chmod -R 775 storage bootstrap/cache

RUN composer install --prefer-dist --optimize-autoloader --no-interaction

COPY unit.json /docker-entrypoint.d/unit.json
COPY queue-worker.conf /etc/supervisor/conf.d/queue-worker.conf

COPY .env.example .env
RUN php artisan key:generate
RUN php artisan storage:link

EXPOSE 8000

CMD ["unitd", "--no-daemon"]