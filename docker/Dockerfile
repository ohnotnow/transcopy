# Set up php dependancies
FROM composer:1.7 as vendor

RUN mkdir -p database/seeds
RUN mkdir -p database/factories

COPY composer.json composer.json
COPY composer.lock composer.lock

RUN composer install \
    --ignore-platform-reqs \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --no-dev \
    --prefer-dist

# Build JS/css assets
FROM node:latest as frontend

RUN node --version
RUN mkdir -p /app/public

COPY package.json webpack.mix.js tailwind.js package-lock.json /app/
RUN mkdir /app/resources
RUN mkdir -p /app/public/js
RUN mkdir -p /app/public/css
RUN touch /app/public/js/app.js
RUN touch /app/public/css/app.css
COPY resources/ /app/resources/
RUN ls -lR /app/resources

WORKDIR /app

RUN yarn install
RUN yarn production

# And build the app
FROM php:7.2-apache

RUN apt-get update && \
    apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libpng-dev libgmp-dev libldap2-dev netcat sqlite3 libsqlite3-dev && \
    docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ && \
    docker-php-ext-install gd pdo pdo_mysql pdo_sqlite zip gmp bcmath pcntl sysvmsg exif \
    && a2enmod rewrite

COPY vhost.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 80

COPY docker/start.sh /usr/local/bin/start
RUN chmod u+x /usr/local/bin/start

COPY . /var/www/html
COPY --from=vendor /app/vendor/ /var/www/html/vendor/
COPY --from=frontend /app/public/js/ /var/www/html/public/js/
COPY --from=frontend /app/public/css/ /var/www/html/public/css/
COPY --from=frontend /app/mix-manifest.json /var/www/html/mix-manifest.json

RUN php /var/www/html/artisan view:clear
RUN php /var/www/html/artisan config:cache
RUN chown -R www-data:www-data /var/www/html/storage
RUN chown -R www-data:www-data /var/www/html/bootstrap/cache

CMD ["/usr/local/bin/start"]
