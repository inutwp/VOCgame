FROM alpine:3.15

# Argument list
ARG ALPINE_VERSION=3.15
ARG user
ARG uid
ARG work_dir
ARG config_dir
ARG src_dir

# Install Requirement
RUN echo "http://dl-cdn.alpinelinux.org/alpine/v${ALPINE_VERSION}/main" >> /etc/apk/repositories \
    && echo "http://dl-cdn.alpinelinux.org/alpine/v${ALPINE_VERSION}/community" >> /etc/apk/repositories \
	&& apk --update --no-cache add \
	ca-certificates \
	bash \
	vim \
    curl \
    tzdata \
    htop \
    supervisor \
	php8 \
    php8-common \
    php8-fpm \
    php8-openssl \
    php8-mbstring \
    php8-intl \
    php8-phar \
    php8-session \
    php8-gd \
    php8-zip \
    php8-zlib \
    php8-json \
    php8-curl \
    php8-opcache \
	&& ln -s /usr/bin/php8 /usr/bin/php \
# Set Timezone
	&& cp /usr/share/zoneinfo/Asia/Jakarta /etc/localtime \
	&& echo "Asia/Jakarta" > /etc/timezone \
# Remove Cache
	&& rm -rf /var/lib/apt/lists/* \
	&& rm -rf /var/cache/apk/* \
# Set Config PHP
    && rm -rf /etc/php8/php-fpm.d/www.conf
COPY $config_dir/php/www.conf /etc/php8/php-fpm.d/www.conf

# Configure supervisord
COPY $config_dir/supervisord/supervisord.app.conf /etc/supervisor/conf.d/supervisord.conf

# Set working directory
WORKDIR $work_dir
COPY $src_dir $work_dir
RUN chown -R $user:$user $work_dir \
	&& chmod -R 0644 $work_dir \
	&& find $work_dir -type d -print0 | xargs -0 chmod 0755 \
    && chown -R $user:$user /var/log/ \
	&& chown -R $user:$user /var/tmp/ \
    && chown -R $user:$user /run

EXPOSE 9000

USER $user

# Run supervisord
CMD ["/usr/bin/supervisord", "-n", "-c" ,"/etc/supervisor/conf.d/supervisord.conf"]