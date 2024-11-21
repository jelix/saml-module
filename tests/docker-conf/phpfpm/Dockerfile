ARG php_version
FROM 3liz/liz-php-fpm:${php_version}

ARG php_version
ENV JELIX_VERSION=1.6.37
ENV JELIX_STABILITY=release
ENV PHP_VERSION=${php_version}

RUN apt-get update; \
    apt-get -y install \
    php${PHP_VERSION}-xdebug \
    ; \
    apt-get clean

COPY test_entrypoint.sh /bin/entrypoint.d/
COPY appctl.sh /bin/
RUN chmod 755 /bin/entrypoint.d/test_entrypoint.sh /bin/appctl.sh

