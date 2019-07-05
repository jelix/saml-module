

Configuration of LemonLdap-NG
=============================

Here are some instruction to configure  [LemonLDAP::NG](https://lemonldap-ng.org)
as an Identity Provider for the saml module (which is here the Service Provider).

Go to the manager of your LemonLDAP instance.

Activate the SAML issuer module
-------------------------------

Go to the page `General parameters > Issuer modules > SAML > Activation`,
then choose `on`.

Click on the `save` button.

Create certificates and keys
-----------------------------

You should create two certificates and their keys, for SAML signing and encryption,
if they are not yet registered into the LemonLDAP::ng manager.

For example:

```
openssl req -x509 -new -newkey rsa:4096 -keyout idp_encrypt.key -out idp_encrypt.pem  -nodes -days 3650 -subj "/C=FR/ST=France/L=Paris/O=jelix/OU=tests/CN=lemontest.jelix.org"
openssl req -x509 -new -newkey rsa:4096 -keyout idp_sig.key -out idp_sig.pem  -nodes -days 3650 -subj "/C=FR/ST=France/L=Paris/O=jelix/OU=tests/CN=lemontest.jelix.org"
```

Copy files `idp_sig.pem` and `idp_encrypt.pem` into the directory `var/config/saml/certs/`
and declare them into the `yourapp/var/config/localconfig.ini.php` file . See below.


In the LemonLDAP::ng manager, go to the page `SAML2 service > security parameters > Signature`, 
and indicate the contents of idp_sig.key and idp_sig.pem. Then go to the page 
`SAML2 service > security parameters > Encryption`, and indicate the contents 
of idp_sig.key and idp_sig.pem
on "New keys" if there are no keys yet. Indicate or not a password. Then retrieve 
the content of the public key, and store it into `tests/app/var/config/saml/idp_encrypt.crt`.

Go to the page `SAML2 service > security parameters > Signature method`,
and choose SHA256.

Click on the `save` button.

Declare your application as a service provider
----------------------------------------------

Go to the page `SAML2 service providers` and click on `Add SAML SP`.
Indicate the name of your jelix application, for example, `appsaml`.

Go to the page `SAML2 service providers > appsaml > Metadata`.
Set the url of the saml metadata given by the saml jelix module, into the field
`Load from Url` and then click on the button `load`. The path into the url
should point to the `saml/endpoint/metadata` controller. For example, if your 
application is accessible at `http://appsaml.local/index.php`, the url
will be `http://appsaml.local/index.php/saml/endpoint/metadata`.

Go to the page `SAML2 service providers > appsaml > Exported attributes`,
and indicate attributes to export.

For example

```

-------------------------------------
| Key name   | Name     | Mandatory |
-------------------------------------
| uid        | login    | off       |
-------------------------------------
| cn         | username | off       |
-------------------------------------
| mail       | mail     | off       |
-------------------------------------

```

Click on the `save` button.


configuration of the saml module for Jelix
-------------------------------------------

You should have a `saml:idp` section into `yourapp/var/config/localconfig.ini.php`.
Set the following values into this section.

- indicates the name of the certs files you created from the lemonldap certificats:

```
certs_signing_files=idp_sig.pem
certs_encryption_files=idp_encrypt.pem
```

- sets some url of the portal, for SSO and SLO, as well as the entity id. Here
the portal is accessible at the url `https://portal.lemon.local`.

```
entityId=https://portal.lemon.local/saml/metadata

singleSignOnServiceUrl=https://portal.lemon.local/saml/singleSignOn
singleLogoutServiceUrl=https://portal.lemon.local/saml/singleLogout
```

You should set up the mapping of attributes. In an example below, we setup
the exported attributes. The SAML jelix module will receive these attributes:
`login`, `username` and `mail`.

If the DAO user record have properties `login`, `name` and `email`, the 
mapping attributes should be (into the mainconfig.ini.php or localconfig.ini.pĥp):

```
[saml:attributes-mapping]
__login=login
; <dao property>=<saml attribute>
login=login
email=mail
name=username
``` 
