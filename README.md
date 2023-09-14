The "saml" module is a module for Jelix, providing authentication for jAuth 
against SAML servers.

A "samladmin" module is also provided to configure the "saml" module.


Installation
============

These modules are for Jelix 1.6.21 and higher. They are compatible with jauth, 
jauthdb, jauthdb_admin and jcommunity modules.

The saml module uses the [onelogin/php-saml](https://github.com/onelogin/php-saml/) library which is installed 
automatically when using Composer.

To install the SAML modules into Lizmap, read the file [README_lizmap.md](README_lizmap.md).

To install the SAML modules into any other application based on Jelix, read
the file [INSTALL.md](INSTALL.md).


Authentication
==============

The "saml" module adds a new button on the login form:

![screenshot_login.png](screenshot_login.png)

You can change the label of the button into the account configuration panel.

Clicking on the button redirect the user to your identity server. The user will then
have to authenticate himself if he's not already authenticated, and then he will
be redirected back to the application.

Configuration
=============

See [the configuration page](CONFIGURATION.md) to discover the samladmin
module or to know to configure the saml module without samladmin.


