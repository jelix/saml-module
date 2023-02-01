This is a module for Jelix, providing authentication for jAuth against SAML servers 

Installation
============

This module is for Jelix 1.6.21 and higher. It is compatible with jauth, 
jauthdb, jauthdb_admin and jcommunity modules.

It uses the [onelogin/php-saml](https://github.com/onelogin/php-saml/) library. 

The SAML module requires:

- php 7.0 or more
- some core PHP extensions like php-xml, php-date, php-zlib.
- openssl. Install the openssl library. It handles x509 certificates.
- gettext. Install that library and its php driver. It handles translations.
- curl. Install that library and its php driver if you plan to use the IdP Metadata parser.

Install files with Jelix 1.7 or higher
---------------------------------------

You should use Composer to install the module. Run this commands in a shell:
                                               
```
composer require "jelix/saml-module"
```

The module `jauthdb` as well as the `jauth` module are required, except if you
are using the `jcommunity` module.

In the `[modules]` section of `app/system/mainconfig.ini.php` or `var/config/localconfig.ini.php`,
you should have:

```ini
jauth.enable=1
jauthdb.enable=1
```

If you are using jCommunity, you must deactivate both modules:

```ini
jauth.enable=0
jauthdb.enable=0
```

Then you must enable the module.

If you are the developer of the application, launch the configurator to enable 
the module permanently with this command:

```bash
php yourapp/dev.php module:configure saml
```

If you install the module into an application you are using (like Lizmap), launch the configurator 
to enable the module, with this command:

```bash
php yourapp/install/configurator.php saml
php yourapp/install/configurator.php samladmin
```


Install files with Jelix 1.6
----------------------------

It works with Jelix 1.6.21 minimum.
If you are using Composer in your application, you should indicate the package
into your composer.json:

```
composer require "jelix/saml-module"
```

Else, if you are not using Composer, you must install [onelogin/php-saml 3.6.1](https://github.com/onelogin/php-saml/releases/tag/3.6.1) 
by hand, extract it somewhere, and add into your application.init.php
an include instruction to load its file `_toolkit_loader.php`. Then copy the `saml` 
directory of saml-module into the modules/ directory of your application.

Next you must say to Jelix that you want to use the module. Declare
it into the `mainconfig.ini.php` or `localconfig.ini.php` file (into yourapp/var/config/).

In the `[modules]` section, add:

```ini
saml.access=2
```

For the entrypoint dedicated to the administration (if you have one), 
in its configuration file, in the `[modules]` section, add:

```ini
samladmin.access=2
```


Module `jauthdb` or `jcommunity` is required. If you are using `jcommunity`, you have
to disable the `jauth` and `jauthdb` module.
In this same section you should then have:

```ini
jauth.access=1
jauthdb.access=1
```

or:

```ini
jcommunity.access=2
jauth.access=0
jauthdb.access=0
```


Configuring the installation
============================

For the moment, there is no configurator dedicated to the module for Jelix 1.7, 
so you should set some parameter by hand, like for Jelix 1.6.

Installation parameters are:

- `localconfig`: says that the configuration should be set into `localconfig.ini.php`, not `mainconfig.ini.php`
- `useradmin`: the login of the administrator. An account will be created and
   admin rights will be given to him.
-  `emailadmin`: email of the administrator

Indicate them into the `[modules]` section, like in this example:

```ini
saml.installparam="localconfig;useradmin=admin;emailadmin=foo@example.com"
```

Installing in an application already having user database
=========================================================

FIXME


Launch the installer
=====================

In the command line, launch:

```
php yourapp/install/installer.php
```

The installer:

- configure the plugin `auth` for jCoordinator
- create a `var/config/saml/saml.coord.ini.php` for the `saml` plugin or use the existing one 
- setup an admin user if there is an install parameter useradmin and emailadmin


Configuration
=============

You should setup parameters into `var/config/saml/saml.coord.ini.php`, and
mainly into `var/config/mainconfig.ini.php` or `var/config/localconfig.ini.php`,
into all `saml:*` sections.

To understand parameters into `saml:idp` and `saml:security`, see
the [README.md file of php-saml](https://github.com/onelogin/php-saml/blob/3.4.1/README.md).


Certificates
------------

With the samladmin module, you can generate a private key and a certificate from a web interface.

If you don't activate this module, you should put your service provider certificate 
and private key into respectively `var/config/saml/certs/sp.crt` and `var/config/saml/certs/sp.key`.

An example to create the certificate (only for tests, you probably have to generate
them against an external or internal certificates authority)

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

With the samladmin module, you can setup easily the mapping between SAML attributes
and the user dao record properties.

If you don't use the samladmin module, you should setup by hand.

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
