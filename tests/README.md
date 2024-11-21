

How to launch the web server
-----------------------------

Add this lines into your /etc/hosts:

```
127.0.0.1 lemon.local auth.lemon.local manager.lemon.local handler.lemon.local test1.lemon.local test2.lemon.local
127.0.0.1 appsaml.local
```

Be sure you don't have a webserver on the port 80 on your machine. Stop it if it exists.


Then launch the containers for tests

```bash
./run-docker build
./run-docker up
./app-ctl reset
./app-ctl ldap-reset
``` 

During the startup, there can be errors from the nginx-proxy, containers, like
`error: open /etc/nginx/certs: no such file or directory` or like
`worker process 35 exited with code 0`. Ignore them.


You can then open in your browser:

- `http://appsaml.local` to test the jelix application in which the SAML module is activated
- `http://manager.lemon.local` to manage LemonLdap configuration
- `http://auth.lemon.local` for the authentication portal, with `http://auth.lemon.local/saml/metadata`
  as the url of the metadata.

The default user (and administrator of lemonldap):
- login: dwho
- password: dwho

Other users : 
- john / passjohn (who is into two groups: group1 and group2)
- jane / passjane (who is into the group1 group)

To reset all data and docker containers, run `./run-docker reset`

