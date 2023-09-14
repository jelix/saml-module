Installation of the module into Lizmap Web Client 3.6 and higher
================================================================

There are already some things pre-configured for the SAML module into [Lizmap Web Client](https://github.com/3liz/lizmap-web-client).
So the installation into Lizmap is a bit different than into other Jelix applications.

The SAML module requires:

- **Lizmap 3.6** or higher.
- php 7.4 or more
- some core PHP extensions like php-xml, php-date, php-zlib.
- openssl. Install the openssl library. It handles x509 certificates.
- gettext. Install that library and its php driver. It handles translations.
- curl. Install that library and its php driver if you plan to use the IdP Metadata parser.



Installation with Composer and lizmap 3.6 or higher
---------------------------------------------------

* into `lizmap/my-packages`, create the file `composer.json` (if it doesn't exist)
  by copying the file `composer.json.dist`:

```bash
cp -n lizmap/my-packages/composer.json.dist lizmap/my-packages/composer.json
```

* then install the module

```bash
composer require --working-dir=lizmap/my-packages "jelix/saml-module"
```

* Then go into `lizmap/install/` and execute Lizmap install scripts :

```bash
php configurator.php saml
php configurator.php samladmin
php installer.php
./clean_vartmp.sh
./set_rights.sh
```


Configuration
=============

Go to the administration pages of Lizmap with your browser, you will see a new menu entry "SAML configuration".
Click on this item, then you'll fill different forms to configure the SAML access.