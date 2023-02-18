FROM php:8.2.2-cli

RUN apt-get --quiet=2 update \
    && apt-get --quiet=2 install zip unzip \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install mysqli pdo_mysql calendar opcache > /dev/null

RUN curl -sS https://getcomposer.org/installer | \
    php -- --install-dir=/usr/local/bin --filename=composer --version=2.5.2

WORKDIR /work/

COPY tests/default/data/runMysqlQuery.php tests/default/data/runMysqlQuery.php
COPY composer.json .
RUN composer update --no-interaction \
    && composer require sqlftw/sqlftw --ignore-platform-req=php+ \
    && composer require doctrine/dbal --ignore-platform-req=php+
