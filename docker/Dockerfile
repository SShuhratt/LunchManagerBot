FROM php:8.4-fpm
WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libonig-dev libxml2-dev libzip-dev \
    zip unzip git curl libssl-dev libpq-dev gnupg \
    && docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

RUN curl -sSL https://bin.equinox.io/c/bNyj1mQVY4c/ngrok-v3-stable-linux-amd64.tgz \
    -o ngrok.tgz && \
    tar -xzf ngrok.tgz && \
    mv ngrok /usr/local/bin/ && \
    rm ngrok.tgz

RUN chown -R www-data:www-data /var/www

EXPOSE 9000 4040

CMD ["sh", "-c", "ngrok http 9000 & php-fpm"]
