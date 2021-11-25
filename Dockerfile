FROM php:fpm-alpine3.14

# Labels
LABEL vocgame.maintainer="inutwp <inutwp.com>"
LABEL vocgame.version="v1.0"
LABEL vocgame.base.image="php:fpm-alpine3.14"

# Arguments
ARG user
ARG uid
ARG config_dir
ARG work_dir
ARG src_dir

# Install Requirement
RUN apk --update --no-cache add \
	ca-certificates \
	bash \
	vim \
	tzdata \
    htop \
	supervisor \
# Set Timezone
	&& cp /usr/share/zoneinfo/Asia/Jakarta /etc/localtime \
	&& echo "Asia/Jakarta" > /etc/timezone \
# Remove Cache
	&& rm -rf /var/lib/apt/lists/* \
	&& rm -rf /var/cache/apk/*

# Configure php-fpm
COPY $config_dir/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# Create work dir
RUN mkdir -p $work_dir
WORKDIR $work_dir
COPY $src_dir $work_dir
RUN addgroup -g $uid -S $user \
    && adduser -S -D -H -u $uid -h $work_dir -s /bin/bash -G $user -g $user $user \
	&& chown -R $user:$user $work_dir \
	&& chmod -R 0644 $work_dir \
	&& find $work_dir -type d -print0 | xargs -0 chmod 0755 \
	&& chown -R $user:$user /run

# Expose Port FPM
EXPOSE 9000

# Change User
USER $user

# Run PHPFpm
CMD ["php-fpm", "--nodaemonize"]