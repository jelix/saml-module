

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
``` 

You can then open in your browser:

- `http://appsaml.local` to test the jelix application in which the SAML module is activated
- `http://manager.lemon.local` to manage LemonLdap configuration
- `http://auth.lemon.local` for the authentication portal, with `http://auth.lemon.local/saml/metadata`
  as the url of the metadata.

The default user (and administrator of lemonldap):
- login: dwho
- password: dwho

To reset all data and docker containers, run `./run-docker reset`

