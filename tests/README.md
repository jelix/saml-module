

How to launch the web server
-----------------------------

Add this lines into your /etc/hosts:

```
127.0.0.1 lemon.local auth.lemon.local manager.lemon.local handler.lemon.local test1.lemon.local test2.lemon.local
127.0.0.1 appsaml.local
```

Retrieve Jelix 1.6 and extract the source as indicated here:

```
JELIX_VERSION=1.6.27
wget https://download.jelix.org/jelix/releases/1.6.x/${JELIX_VERSION}/jelix-${JELIX_VERSION}-dev.zip
unzip jelix-${JELIX_VERSION}-dev.zip
mv jelix-${JELIX_VERSION}-dev jelix

```

Be sure you don't have a webserver on the port 80 on your machine. Stop it if it exists.


Then launch the containers for tests

```bash
docker-compose build
docker-compose up
``` 

You can then into your browser:

- `http://appsaml.local` to test the jelix application in which the SAML module is activated
- `http://manager.lemon.local` to manage LemonLdap configuration



