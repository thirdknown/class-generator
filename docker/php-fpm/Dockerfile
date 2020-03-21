FROM php:7.4-fpm-buster as base

ARG project_root=.

RUN apt-get update && apt-get install -y wget gnupg g++ locales unzip dialog apt-utils git && apt-get clean

COPY /docker/php-fpm/docker-install-composer /usr/local/bin/docker-install-composer

RUN chmod +x /usr/local/bin/docker-install-composer && docker-install-composer

RUN apt-get update && \
    apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    libpq-dev \
    vim \
    nano \
    mc \
    htop \
    autoconf && \
    apt-get clean

RUN docker-php-ext-install \
    bcmath \
    calendar \
    fileinfo \
    gd \
    intl \
    opcache \
    pgsql \
    pdo_pgsql \
    zip

RUN pecl install redis-4.1.1 && \
    docker-php-ext-enable redis

RUN sed -i -e 's/# en_US.UTF-8 UTF-8/en_US.UTF-8 UTF-8/' /etc/locale.gen && \
    locale-gen
ENV LANG en_US.UTF-8
ENV LANGUAGE en_US:en
ENV LC_ALL en_US.UTF-8

COPY /docker/php-fpm/php-ini-overrides.ini /usr/local/etc/php/php.ini

COPY /docker/php-fpm/docker-php-entrypoint /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-php-entrypoint

RUN usermod -m -d /home/www-data www-data && \
    mkdir -p /var/www/html && \
    chown -R www-data:www-data /home/www-data /var/www/html

USER www-data

RUN composer global require hirak/prestissimo

ENV COMPOSER_MEMORY_LIMIT=-1

########################################################################################################################

FROM base as development

USER root

# allow overwriting UID and GID o the user "www-data" to help solve issues with permissions in mounted volumes
# if the GID is already in use, we will assign GID 33 instead (33 is the standard uid/gid for "www-data" in Debian)
ARG www_data_uid
ARG www_data_gid
RUN if [ -n "$www_data_uid" ]; then deluser www-data && (addgroup --gid $www_data_gid www-data || addgroup --gid 33 www-data) && adduser --system --home /home/www-data --uid $www_data_uid --disabled-password --group www-data; fi;

# as the UID and GID might have changed, change the ownership of the home directory workdir again
RUN chown -R www-data:www-data /home/www-data /var/www/html

USER www-data
