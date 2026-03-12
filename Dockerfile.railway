# Kimai — Railway single-stage Apache build
# Connects to Railway MySQL service via DATABASE_URL env var

FROM php:8.3-apache-bookworm

ARG TIMEZONE="America/Edmonton"

# ── System deps ──────────────────────────────────────────────────────
RUN apt-get update && apt-get install -y --no-install-recommends \
        bash \
        coreutils \
        haveged \
        unzip \
        curl \
        # PHP extension build deps
        libldap2-dev \
        libicu-dev \
        libpng-dev \
        libzip-dev \
        libxslt1-dev \
        libfreetype6-dev \
        # Runtime libs
        libicu72 \
        libldap-common \
        libpng16-16 \
        libzip4 \
        libxslt1.1 \
        libfreetype6 \
    && rm -rf /var/lib/apt/lists/*

# ── PHP extensions ───────────────────────────────────────────────────
RUN docker-php-ext-configure gd --with-freetype && \
    docker-php-ext-install -j$(nproc) \
        gd \
        intl \
        ldap \
        pdo_mysql \
        zip \
        xsl \
        opcache

# ── Timezone ─────────────────────────────────────────────────────────
RUN ln -snf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime && \
    echo ${TIMEZONE} > /etc/timezone

# ── Composer ─────────────────────────────────────────────────────────
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ── Node 20 (for Webpack Encore asset build) ─────────────────────────
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install -y --no-install-recommends nodejs && \
    npm install -g yarn && \
    rm -rf /var/lib/apt/lists/*

# ── Apache configuration ────────────────────────────────────────────
RUN echo "Listen 8001" > /etc/apache2/ports.conf && \
    a2enmod rewrite && \
    touch /use_apache

COPY .docker/000-default.conf /etc/apache2/sites-available/000-default.conf

# ── Copy application source ─────────────────────────────────────────
COPY --chown=www-data:www-data . /opt/kimai

# ── Copy Docker assets ──────────────────────────────────────────────
COPY .docker /assets
COPY .docker/dbtest.php /dbtest.php
COPY .docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# ── Composer install (production) ────────────────────────────────────
ENV COMPOSER_MEMORY_LIMIT=-1
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN export COMPOSER_HOME=/composer && \
    mkdir -p /composer && \
    composer --no-ansi install --working-dir=/opt/kimai --no-dev --optimize-autoloader && \
    composer --no-ansi clearcache && \
    composer --no-ansi require --update-no-dev --working-dir=/opt/kimai laminas/laminas-ldap

# ── Build frontend assets (Webpack Encore) ───────────────────────────
WORKDIR /opt/kimai
RUN yarn install && \
    npx encore production && \
    rm -rf node_modules

# ── PHP production ini ───────────────────────────────────────────────
RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini && \
    sed -i "s/expose_php = On/expose_php = Off/g" /usr/local/etc/php/php.ini && \
    sed -i "s/;opcache.enable=1/opcache.enable=1/g" /usr/local/etc/php/php.ini && \
    sed -i "s/;opcache.memory_consumption=128/opcache.memory_consumption=256/g" /usr/local/etc/php/php.ini && \
    sed -i "s/;opcache.interned_strings_buffer=8/opcache.interned_strings_buffer=24/g" /usr/local/etc/php/php.ini && \
    sed -i "s/;opcache.max_accelerated_files=10000/opcache.max_accelerated_files=100000/g" /usr/local/etc/php/php.ini && \
    sed -i "s/opcache.validate_timestamps=1/opcache.validate_timestamps=0/g" /usr/local/etc/php/php.ini && \
    sed -i "s/session.gc_maxlifetime = 1440/session.gc_maxlifetime = 604800/g" /usr/local/etc/php/php.ini

# ── Permissions & version file ───────────────────────────────────────
RUN mkdir -p /opt/kimai/var/logs && chmod 777 /opt/kimai/var/logs && \
    sed "s/128M/-1/g" /usr/local/etc/php/php.ini-development > /opt/kimai/php-cli.ini && \
    chown -R www-data:www-data /opt/kimai /usr/local/etc/php/php.ini && \
    /opt/kimai/bin/console kimai:version | awk '{print $2}' > /opt/kimai/version.txt

# ── Remove Node (no longer needed at runtime) ───────────────────────
RUN apt-get purge -y nodejs && apt-get autoremove -y && rm -rf /var/lib/apt/lists/*

# ── Environment defaults ────────────────────────────────────────────
ENV APP_ENV=prod
ENV TIMEZONE=America/Edmonton
ENV DATABASE_URL=
ENV APP_SECRET=change_this_to_something_unique
ENV TRUSTED_PROXIES=nginx,localhost,127.0.0.1
ENV MAILER_FROM=timekeeping@inboxai-mail.dedyn.io
ENV MAILER_URL=null://localhost
ENV MAILER_DSN=smtp://resend:re_PLACEHOLDER@smtp.resend.dev:465
ENV ADMINPASS=
ENV ADMINMAIL=
ENV USER_ID=
ENV GROUP_ID=
ENV memory_limit=512M

VOLUME ["/opt/kimai/var"]

EXPOSE 8001

CMD ["/entrypoint.sh"]
