FROM coudot/lemonldap-ng

COPY lmConf-1.json /var/lib/lemonldap-ng/conf/lmConf-1.json

ENTRYPOINT ["dumb-init","--","/bin/sh", "/docker-entrypoint.sh"]
