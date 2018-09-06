FROM php:7.2-apache

WORKDIR /var/www/html

COPY docker/start.sh /usr/local/bin/start
RUN chmod u+x /usr/local/bin/start
COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf

RUN apt-get update && \
    apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libpng-dev libgmp-dev libldap2-dev netcat sqlite3 libsqlite3-dev && \
    docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ && \
    docker-php-ext-install gd pdo pdo_mysql pdo_sqlite zip gmp bcmath pcntl ldap sysvmsg exif \
    && a2enmod rewrite

RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer
RUN chmod +x /usr/local/bin/composer

COPY . /var/www/html

RUN /usr/local/bin/composer install
RUN /usr/local/bin/php artisan key:generate
RUN /usr/local/bin/php artisan view:clear
RUN /usr/local/bin/php artisan config:cache

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD ["/usr/local/bin/start"]
