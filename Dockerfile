### PHP version we are targetting
ARG PHP_VERSION=7.3

### Build JS/css assets
FROM --platform=$BUILDPLATFORM node:10 as frontend

USER node
WORKDIR /home/node

RUN mkdir -p /home/node/public/css /home/node/public/js /home/node/resources

USER root
# workaround for mix.version() webpack bug
RUN ln -s /home/node/public /public
USER node

COPY --chown=node:node package*.json webpack.mix.js .babelrc* tailwind.js /home/node/
COPY --chown=node:node resources/js* /home/node/resources/js
COPY --chown=node:node resources/sass* /home/node/resources/sass
COPY --chown=node:node resources/scss* /home/node/resources/scss
COPY --chown=node:node resources/css* /home/node/resources/css

RUN npm install && \
    npm run dev && \
    npm cache clean --force

### Prod php dependencies
FROM --platform=$BUILDPLATFORM uogsoe/soe-php-apache:${PHP_VERSION} as prod-composer
ENV APP_ENV=production
ENV APP_DEBUG=0

WORKDIR /var/www/html

USER nobody

#- make paths that the laravel composer.json expects to exist
RUN mkdir -p database/seeds database/factories

COPY composer.* ./

RUN composer install \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist

### And build the app
FROM uogsoe/soe-php-apache:${PHP_VERSION} as prod

WORKDIR /var/www/html

ENV APP_ENV=local
ENV APP_DEBUG=1

#- Copy our start scripts and php/ldap configs in
COPY docker/custom_php.ini /usr/local/etc/php/conf.d/custom_php.ini
COPY docker/app-start docker/app-healthcheck /usr/local/bin/
RUN chmod u+x /usr/local/bin/app-start /usr/local/bin/app-healthcheck

#- Copy in our prod php dep's
COPY --from=prod-composer /var/www/html/vendor /var/www/html/vendor

#- Copy in our front-end assets
RUN mkdir -p /var/www/html/public/js /var/www/html/public/css
COPY --from=frontend /home/node/public/js /var/www/html/public/js
COPY --from=frontend /home/node/public/css /var/www/html/public/css
COPY --from=frontend /home/node/mix-manifest.json /var/www/html/mix-manifest.json

#- Copy in our code
COPY . /var/www/html

#- make a temp sqlite file
RUN mkdir /tmp/sqlite && touch /tmp/sqlite/torrents.sqlite

#- Symlink the docker secret to the local .env so Laravel can see it
RUN ln -sf /run/secrets/.env /var/www/html/.env

#- Clean up and production-cache our apps settings/views/routing
RUN rm -fr /var/www/html/bootstrap/cache/*.php && \
    chown -R www-data:www-data storage bootstrap/cache

#- Set up the default healthcheck
HEALTHCHECK --start-period=30s CMD /usr/local/bin/app-healthcheck

#- And off we go...
CMD ["/usr/local/bin/app-start"]


