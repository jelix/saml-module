Installation
============

The SAML module requires:

- Jelix 1.6.21+
- php 7.4 or more
- some core PHP extensions like php-xml, php-date, php-zlib.
- openssl. Install the openssl library. It handles x509 certificates.
- gettext. Install that library and its php driver. It handles translations.
- curl. Install that library and its php driver if you plan to use the IdP Metadata parser.

To install the SAML module into Lizmap, read the file [README_lizmap.md](README_lizmap.md) instead of
reading next sections here.



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
----------------------------

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



Launch the installer
---------------------

In the command line, launch:

```
php yourapp/install/installer.php
```

The installer:

- configure the plugin `auth` for jCoordinator
- create a `var/config/saml/saml.coord.ini.php` for the `saml` plugin or use the existing one
- setup an admin user if there is an install parameter useradmin and emailadmin

