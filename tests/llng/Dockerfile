FROM coudot/lemonldap-ng:2.0.12
# 2.0.13 does not work, invalid signature on login

COPY lmConf-1.json /var/lib/lemonldap-ng/conf/lmConf-1.json

ENTRYPOINT ["dumb-init","--","/bin/sh", "/docker-entrypoint.sh"]
