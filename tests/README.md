

How to launch the web server
-----------------------------

Add this lines into your /etc/hosts:

```
127.0.0.1 lemon.local auth.lemon.local manager.lemon.local handler.lemon.local test1.lemon.local test2.lemon.local
127.0.0.1 appsaml.local
```

Retrieve Jelix 1.6 from https://download.jelix.org/jelix/releases/1.6.x/1.6.25/jelix-1.6.25-dev.zip

Unzip its content here, and rename the directory jelix-1.6.25 to jelix..

Then launch the containers for tests


```bash
docker-compose build
docker-compose up
``` 


