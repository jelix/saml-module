FROM alpine:3.9

ARG composer_version=1.8.6

RUN apk update && apk upgrade
RUN apk --no-cache add git php7 php7-fpm \
    php7-tokenizer \
    php7-opcache \
    php7-session \
    php7-iconv \
    php7-intl \
    php7-mbstring \
    php7-openssl \
    php7-fileinfo \
    php7-curl \
    php7-json \
    php7-sqlite3 \
    php7-gd \
    php7-dom \
    php7-xml \
    php7-xmlrpc \
    php7-xmlreader \
    php7-xmlwriter \
    php7-simplexml \
    php7-zip \
    php7-phar \
    wget

# 82 is the standard uid/gid for "www-data" in Alpine
RUN set -eux; \
	addgroup -g 82 -S www-data; \
	adduser -u 82 -D -S -G www-data www-data; \
	sed -i "/^user =/c\user = www-data"   /etc/php7/php-fpm.d/www.conf; \
    sed -i "/^group =/c\group = www-data" /etc/php7/php-fpm.d/www.conf; \
    sed -i "/^listen =/c\listen = 9000" /etc/php7/php-fpm.d/www.conf


## Install Composer
RUN wget -O /bin/composer https://getcomposer.org/download/$composer_version/composer.phar \
    && chmod +x /bin/composer

COPY entrypoint.sh /bin/
RUN chmod 755 /bin/entrypoint.sh

ENV PHP_INI_DIR /etc/php7

WORKDIR /opt/
ENTRYPOINT ["/bin/entrypoint.sh"]
CMD ["/usr/sbin/php-fpm7", "-F", "-O"]
