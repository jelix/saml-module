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
saml.access=1
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
- setup an admin user

configuration
-------------

You should setup parameters into `var/config/saml.coord.ini.php`, and
mainly into `var/config/mainconfig.ini.php` or `var/config/localconfig.ini.php`

You should put your service provider certificate and private key into 
respectively `var/config/saml/certs/sp.crt` and `var/config/saml/certs/sp.key`.

If you plan to update the certificate and private key you can save a new 
x509cert into `var/config/saml/certs/sp_new.crt` and it will be published on the 
SP metadata so Identity Providers can read them and get ready for rollover.

