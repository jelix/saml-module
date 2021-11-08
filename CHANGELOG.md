Changes
=======

Version 2.0.0 (not released yet)
--------------------------------

It allows now to choice the authentication method : by the classical login/password
(provided by the jauth or the jcommunity module), or by SAML. It means that
instead of redirecting the user to the identity provider web site when authentication
is required, it shows the classical login/password form with an additional button
to authenticate with Saml.

It fixes the logout initiated by the identity provider: PHP session linked to
closed SAML session are now destroyed.

It provides a new module, samladmin, which allows to configure SAML within an
administration web interface, with a generator of private key/certificate,
with an automatic fill of idp parameters by retrieving a given metadata url of the idp.

For developers:
- the jauth module is now required, except if you are using the jcommunity module
- no more specific configuration for the jcommunity module
- configuration changes:
  - no more `after_login` and `after_logout` in the `saml:sp` section
  - a `jcache:saml` profile can be setup to store correspondance between PHP session
    id and SAML session id.
  - new `label` parameter into  `[saml:idp]` for the login button 
- API changes: 
  - `Jelix\Saml\Configuration` does not required anymore a `jRequest` object for
     its constructor.


Version 1.0.3
-------------

- Composer: add autoconfiguration for the Lizmap application

Version 1.0.2
-------------

- Fix SP metadata : some data, like the sp certificate, were missing from metadata.
- Upgrade PHP-Saml to 3.6.1

Version 1.0.1
-------------

- Fix: installer should add the admin user into the admins group
- Fix: kill full session when logout
- Fix: do not cache the logout response into the browser

Version 1.0.0
-------------

Initial release.
