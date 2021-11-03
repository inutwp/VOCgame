FROM php:7.4-fpm

# Arguments defined in docker-compose.yml
ARG user
ARG uid
ARG BASE_DIR=/var/www/
ARG CONFIG_DIR=/config
ARG WORK_DIR=/var/www/vocgame

# Install system dependencies
RUN apt-get upgrade -y \
    && apt-get update -y \
    && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    htop \
    bash \
    vim \
    procps \
    supervisor \
# Clear cache
    && apt-get clean && rm -rf /var/lib/apt/lists/* \
# Install PHP extensions
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Config supervisord
COPY ${CONFIG_DIR}/supervisord/vocgame.conf /etc/supervisor/conf.d/vocgame.conf

# Set working directory
WORKDIR ${WORK_DIR}

# RUN groupadd -g $uid $user

RUN useradd -G www-data,root -u $uid -d ${WORK_DIR} $user \
    && chown -R $user:$user ${WORK_DIR} \
    && chmod -R 0644 ${WORK_DIR} \
    && find ${WORK_DIR} -type d -print0 | xargs -0 chmod 0755

USER $user

EXPOSE 9000

# Run supervisord
CMD ["/usr/bin/supervisord", "-n"]