ARG PHP_VERSION
FROM 3liz/liz-php-fpm:${PHP_VERSION}

ENV JELIX_VERSION=1.6.35
ENV JELIX_STABILITY=release

RUN apt-get update; \
    apt-get -y install \
    php${PHP_VERSION}-xdebug \
    ; \
    apt-get clean

COPY test_entrypoint.sh /bin/entrypoint.d/
COPY appctl.sh /bin/
RUN chmod 755 /bin/entrypoint.d/test_entrypoint.sh /bin/appctl.sh

