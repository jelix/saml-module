This is a module for Jelix, providing authentication for jAuth against SAML servers 

Installation
============

This module is for Jelix 1.6.21 and higher. It replaces the jauth module,
and it is compatible with jauthdb, jauthdb_admin modules.

Install files with Jelix 1.7
----------------------------
You should use Composer to install the module. Run this commands in a shell:
                                               
```
composer require "jelix/saml-module"
```

Launch the configurator for your application to enable the module

```bash
php yourapp/dev.php module:configure saml

php yourapp/install/installer.php

```
Install files with Jelix 1.6.21
-------------------------------

Copy the `saml` directory into the modules/ directory of your application.

Next you must say to Jelix that you want to use the module. Declare
it into the `mainconfig.ini.php` file (into yourapp/var/config/).

In the `[modules]` section, add:

```ini
saml.access=2
```

The module `jauthdb` is required, and the `jauth` module should be deactivated.
In this same section you should then have:

```ini
jauth.access=0
jauthdb.access=1
```

In the command line, launch:

```
php yourapp/install/installer.php
```

The installer:

- deactivate the plugin `auth` for jCoordinator, and replace it by the `saml` plugin
- create a `var/config/saml.coord.ini.php` for the `saml` plugin
- remove roles `auth.user.change.password` and `auth.users.change.password'` from jAcl2 data
- setup an admin user if there is an install parameter useradmin and emailadmin

Configuration
=============

You should setup parameters into `var/config/saml.coord.ini.php`, and
mainly into `var/config/mainconfig.ini.php` or `var/config/localconfig.ini.php`,
into all `saml:*` sections.

Certificates
------------

You should put your service provider certificate and private key into 
respectively `var/config/saml/certs/sp.crt` and `var/config/saml/certs/sp.key`.

An example to create the certificate (only for tests, you probably have to generate
them against an external or internal cerficates authority)

```
openssl req -x509 -newkey rsa:4096 -keyout var/config/saml/certs/sp.key -out var/config/saml/certs/sp.crt -days 365 -nodes -subj "/C=FR/ST=France/L=Paris/O=jelix/OU=tests/CN=samltest.jelix.org"
```

If you plan to update the certificate and private key you can save a new 
x509cert into `var/config/saml/certs/sp_new.crt` and it will be published on the 
SP metadata so Identity Providers can read them and get ready for rollover.

Attributes
----------

The SAML Identity Provider will give you them some attributes about the user.
If not, you should configure the Identity Provider so it will include some
attributes into the login response.


There should be at least one attribute, indicating the user login. You should
indicate the name of this attribute into the `__login` option of the `saml:attributes-mapping`
section of the configuration. In this example, the login value is given into
the `uid` attribute given by the Identity Provider:
                             
```ini
[saml:attributes-mapping]
__login=uid
```

In the same section you can list the list of the user dao record properties that
will receive the values of some SAML attributes. Keys are property names, values
are attributes names.

```ini
[saml:attributes-mapping]
__login=uid

login=uid
email=mail

```

Here, the user dao object will have its `login` property receiving the value
of the `uid` attribute, and the  `email` property receiving the value
of the `mail` attribute.
