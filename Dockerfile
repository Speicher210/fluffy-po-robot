FROM php:7.3-fpm

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

ENV COMPOSER_MEMORY_LIMIT=-1
ENV COMPOSER_HOME /var/www/composer
ENV COMPOSER_ALLOW_SUPERUSER 1
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN chown -R www-data:www-data $COMPOSER_HOME

COPY src /fluffy/src
COPY bin /fluffy/bin
COPY composer.json /fluffy
COPY composer.lock /fluffy
RUN composer install --working-dir=/fluffy

WORKDIR /app

ENTRYPOINT ["php", "/fluffy/bin/fpr"]
