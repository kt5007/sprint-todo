FROM php:8.1-apache
COPY php.ini /usr/local/etc/php/

# Composer Version
ARG composer_ver=2.5.0
ARG composer_hash=b571610e5451785f76389a08e9575d91c3d6e38fee1df7a9708fe307013c8424
# Composer installation path
ARG composer_path=/usr/local/bin/composer
# Composer Install
RUN apt-get update \
    && php -r "copy('https://getcomposer.org/download/$composer_ver/composer.phar', '$composer_path');" \
    && chmod 755 $composer_path  \
    && php -r "if (hash_file('sha256', '$composer_path') === '$composer_hash') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('$composer_path'); } echo PHP_EOL;" \
    && php $composer_path

# Install node.js v18
RUN curl -sL https://deb.nodesource.com/setup_18.x | bash -
RUN apt-get install -y nodejs

RUN apt-get -y install libzip-dev vim \
    && docker-php-ext-install zip

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN a2enmod rewrite