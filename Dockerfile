FROM php:7.4-fpm

# Arguments defined in docker-compose.yml
ARG user
ARG uid
ARG WORK_DIR=/var/www/vocgame

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Make working directory and vendor directory
RUN mkdir -p ${WORK_DIR}

# Set working directory
WORKDIR ${WORK_DIR}

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d ${WORK_DIR} $user
RUN chown -R $user:$user ${WORK_DIR}

USER $user
