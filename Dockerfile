FROM php:8.1.7-fpm

RUN apt-get update \
    && apt-get install -y git libzip-dev zlib1g-dev unzip

# core extensions
RUN docker-php-ext-enable opcache
RUN docker-php-ext-install bcmath \
    && docker-php-ext-install zip

# intl
RUN apt-get install -y libicu-dev \
    && docker-php-ext-configure intl --enable-intl \
    && docker-php-ext-install intl

COPY --from=composer:2.3 /usr/bin/composer /usr/local/bin/composer

COPY src /fluffy/src
COPY bin /fluffy/bin
COPY composer.json /fluffy
COPY composer.lock /fluffy
RUN composer install --no-dev --working-dir=/fluffy
RUN composer check-platform-reqs --working-dir=/fluffy

WORKDIR /app

ENTRYPOINT ["php", "/fluffy/bin/fpr"]
