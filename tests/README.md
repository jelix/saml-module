

How to launch the web server
-----------------------------

In your /etc/hosts:

```
127.0.0.1 lemon.local auth.lemon.local manager.lemon.local handler.lemon.local test1.lemon.local test2.lemon.local
127.0.0.1 appsaml.local
```


```bash
docker-compose build
docker-compose run -V
```

Because a bug into the lemonldap-ng image, the `-V` option is required. 


Configuration
-------------

Go on manager.lemon.local. Login and password are dwho.


Go to the page `General parameters > Issuer modules > SAML > Activation`,
then choose `on`. 

Click on the `save` button.

Go to the page `SAML2 service > security parameters > Signature`, and click
on "New keys". Do not indicate a password. Then retrieve the content of the
public key, and store it into `tests/app/var/config/saml/idp_sig.crt`.

Go to the page `SAML2 service > security parameters > Encryption`, and click
on "New keys". Do not indicate a password. Then retrieve the content of the
public key, and store it into `tests/app/var/config/saml/idp_encrypt.crt`.

Go to the page `SAML2 service > security parameters > Signature method`, 
and choose SHA256.

Click on the `save` button.

Go to the page `SAML2 service providers` and click on `Add SAML SP`.
Indicate the name `appsaml`.

Go to the page `SAML2 service providers > appsaml > Metadata`.
Set the url `http://appsaml.local/index.php/saml/endpoint/metadata` into
the file `Load from Url` and then click on the button `load`.

Go to the page `SAML2 service providers > appsaml > Exported attributes`.
Add the following attributes:

```
-----------------------------------
| Key name   | Name   | Mandatory |
-----------------------------------
| Identifier | id     | off       |
-----------------------------------
| cn         | cn     | off       |
-----------------------------------
| mail       | mail   | off       |
-----------------------------------
```

Click on the `save` button.

