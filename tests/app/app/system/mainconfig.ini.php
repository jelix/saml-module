;<?php die(''); ?>
;for security reasons , don't remove or modify the first line
;this file doesn't list all possible properties. See lib/jelix/core/defaultconfig.ini.php for that


locale=en_US
availableLocales="en_US,fr_FR"
charset=UTF-8

; see http://www.php.net/manual/en/timezones.php for supported values
timeZone="Europe/Paris"

theme=default



; default domain name to use with jfullurl for example.
; Let it empty to use $_SERVER['SERVER_NAME'] value instead.
domainName=


[modules]
jelix.enabled=on
jelix.installparam[wwwfiles]=vhost
jacl2db.enabled=on
jacl2db.installparam[defaultgroups]=on
jacl2db.installparam[defaultuser]=on
jauth.enabled=on
jauthdb.enabled=on
jauthdb.installparam[defaultuser]=on
app.enabled=on
jacl2db_admin.enabled=on
jauthdb_admin.enabled=on
master_admin.enabled=on
jacl2.enabled=on

saml.path="/opt/saml/"
saml.enabled=on
saml.installparam[useradmin]=dwho
saml.installparam[emailadmin]="dwho@lemon.local"


samladmin.path="/opt/samladmin/"
samladmin.enabled=on

[coordplugins]
;name = file_ini_name or 1

[tplplugins]
defaultJformsBuilder=html

[responses]
html=myHtmlResponse
htmlauth=myHtmlResponse

[error_handling]
;errorMessage="A technical error has occured (code: %code%). Sorry for this inconvenience."

;[compilation]
;checkCacheFiletime  = on
;force  = off

[urlengine]

; this is the url path to the jelix-www content (you can found this content in lib/jelix-www/)
; because the jelix-www directory is outside the yourapp/www/ directory, you should create a link to
; jelix-www, or copy its content in yourapp/www/ (with a name like 'jelix' for example)
; so you should indicate the relative path of this link/directory to the basePath, or an absolute path.
; if you change it, you probably want to change path in datepickers, wikieditors and htmleditors sections
jelixWWWPath="jelix/"
jqueryPath="jelix/jquery/"

; enable the parsing of the url. Set it to off if the url is already parsed by another program
; (like mod_rewrite in apache), if the rewrite of the url corresponds to a simple url, and if
; you use the significant engine. If you use the simple url engine, you can set to off.
enableParser=on

multiview=off

; basePath corresponds to the path to the base directory of your application.
; so if the url to access to your application is http://foo.com/aaa/bbb/www/index.php, you should
; set basePath = "/aaa/bbb/www/".
; if it is http://foo.com/index.php, set basePath="/"
; Jelix can guess the basePath, so you can keep basePath empty. But in the case where there are some
; entry points which are not in the same directory (ex: you have two entry point : http://foo.com/aaa/index.php
; and http://foo.com/aaa/bbb/other.php ), you MUST set the basePath (ex here, the higher entry point is index.php so
; : basePath="/aaa/" )
basePath=




notFoundAct="jelix~error:notfound"
[jResponseHtml]
; list of active plugins for jResponseHtml
; remove the debugbar plugin on production server, and in this case don't forget
; to remove the memory logger from the logger section
plugins=debugbar


[logger]
; list of loggers for each categories of log messages
; available loggers : file, syslog, firebug, mail, memory. see plugins for others

; _all category is the category containing loggers executed for any categories
_all=

; default category is the category used when a given category is not declared here
default=file
error=file
warning=file
notice=file
deprecated=
strict=
debug=
sql=
soap=

[fileLogger]
default=messages.log

[mailLogger]
;email = root@localhost
;emailHeaders = "Content-Type: text/plain; charset=UTF-8\nFrom: webmaster@yoursite.com\nX-Mailer: Jelix\nX-Priority: 1 (Highest)\n"

[mailer]
webmasterEmail="root@localhost"
webmasterName=

; How to send mail : "mail" (mail()), "sendmail" (call sendmail), "smtp" (send directly to a smtp)
;                   or "file" (store the mail into a file, in filesDir directory)
mailerType=mail
; Sets the hostname to use in Message-Id and Received headers
; and as default HELO string. If empty, the value returned
; by SERVER_NAME is used or 'localhost.localdomain'.
hostname=
sendmailPath="/usr/sbin/sendmail"

; if mailer = file, fill the following parameters
; this should be the directory in the var/ directory, where to store mail as files
filesDir="mails/"

; if mailer = smtp , fill the following parameters

; SMTP hosts.  All hosts must be separated by a semicolon : "smtp1.example.com:25;smtp2.example.com"
smtpHost=localhost
; default SMTP server port
smtpPort=25
; secured connection or not. possible values: "", "ssl", "tls"
smtpSecure=
; SMTP HELO of the message (Default is hostname)
smtpHelo=
; SMTP authentication
smtpAuth=off
smtpUsername=
smtpPassword=
; SMTP server timeout in seconds
smtpTimeout=10



[acl2]
; example of driver: "db"
driver=

[sessions]
; If several applications are installed in the same documentRoot but with
; a different basePath, shared_session indicates if these application
; share the same php session
shared_session=off

; indicate a session name for each applications installed with the same
; domain and basePath, if their respective sessions shouldn't be shared
name=

; Use alternative storage engines for sessions
;storage = "files"
;files_path = "app:var/sessions/"
;
; or
;
;storage = "dao"
;dao_selector = "jelix~jsession"
;dao_db_profile = ""


[forms]


[saml:sp]

; Contact information template, it is recommended to supply a technical and
; support contacts
technicalContactPerson[givenName]=
technicalContactPerson[emailAddress]=
supportContactPerson[givenName]=
supportContactPerson[emailAddress]=

organization[name]=
organization[displayname]=
organization[url]=

compressRequests=on
compressResponses=on


; If you need to specify requested attributes, set the service name into attrcs_service_name
; and fill the saml:sp:requestedAttributes section with the list of requested attributes
attrcs_service_name=
attrcs_service_description=



; enable debug or not in the SAML library
saml_debug=off

; list of dao properties that can be used for mapping. Empty means all dao properties.
daoPropertiesForMapping="login,email"

[saml:sp:requestedAttributes]
;<attribute name>[isRequired]=on or off
;<attribute name>[nameFormat]=  ; optional
;<attribute name>[friendlyName]= ; optional
;<attribute name>[attributeValue]= ; optional


[saml:attributes-mapping]
; indicate on which properties of the DAO record, attributes given by the idp should be stored
; __login is a not a property, but indicates which SAML attribute contains the user login.
__login=
; <dao property>=<saml attribute>




; identity provider
[saml:idp]

; label to display in the interface and in the login form
label=

; Identifier of the IdP entity  (must be a URI)
entityId=

; --  SSO endpoint info of the IdP. (Authentication Request protocol)
;  URL Target of the IdP where the SP will send the Authentication Request Message
singleSignOnServiceUrl=
; SAML protocol binding to be used when returning the <Response> message.
; possible binding: http-post, http-redirect, http-artifact, soap, deflate
singleSignOnServiceBinding=http-redirect

; -- SLO endpoint info of the IdP.
; URL Location of the IdP where the SP will send the SLO Request
singleLogoutServiceUrl=
; URL location of the IdP where the SP SLO Response will be sent (ResponseLocation)
; if not set, url for the SLO Request will be used
singleLogoutServiceResponseUrl=
; SAML protocol binding to be used when returning the <Response> message.
; possible binding: http-post, http-redirect, http-artifact, soap, deflate
singleLogoutServiceBinding=http-redirect

; In some scenarios the IdP uses different certificates for
; signing/encryption, or is under key rollover phase and more
; than one certificate is published on IdP metadata.
; In order to handle that, you must indicate certificate files names,
; separated by a coma. Files should be stored into the var/config/saml/certs/ directory
; If there is only one certificate file for signing/encryption, just leave
; following parameter empty, and put a idp.crt file into var/config/saml/certs/

certs_signing_files=idp_sig.pem
certs_encryption_files=idp_encrypt.pem

; signatures and encryptions offered
[saml:security]

; Indicates that the nameID of the <samlp:logoutRequest> sent by this SP
; will be encrypted.
nameIdEncrypted=off

; Indicates whether the <samlp:AuthnRequest> messages sent by this SP
; will be signed.              [The Metadata of the SP will offer this info]
authnRequestsSigned=on

; Indicates whether the <samlp:logoutRequest> messages sent by this SP
; will be signed.
logoutRequestSigned=on

; Indicates whether the <samlp:logoutResponse> messages sent by this SP
; will be signed.
logoutResponseSigned=on

; Sign the Metadata
signMetadata=off

;-- signatures and encryptions required

; Indicates a requirement for the <samlp:Response>, <samlp:LogoutRequest> and
; <samlp:LogoutResponse> elements received by this SP to be signed.
wantMessagesSigned=off

; Indicates a requirement for the <saml:Assertion> elements received by
; this SP to be encrypted.
wantAssertionsEncrypted=off

; Indicates a requirement for the <saml:Assertion> elements received by
; this SP to be signed.        [The Metadata of the SP will offer this info]
wantAssertionsSigned=off

; Indicates a requirement for the NameID element on the SAMLResponse received
; by this SP to be present.
wantNameId=on

; Indicates a requirement for the NameID received by
; this SP to be encrypted.
wantNameIdEncrypted=off

; Authentication context.
; Set to false and no AuthContext will be sent in the AuthNRequest,
; Set true or don't present this parameter and you will get an AuthContext 'exact' 'urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport'
; Set an array with the possible auth context values: array('urn:oasis:names:tc:SAML:2.0:ac:classes:Password', 'urn:oasis:names:tc:SAML:2.0:ac:classes:X509'),
requestedAuthnContext=off

; Allows the authn comparison parameter to be set, defaults to 'exact' if
; the setting is not present.
requestedAuthnContextComparison=exact

; Indicates if the SP will validate all received xmls.
; (In order to validate the xml, 'strict' and 'wantXMLValidation' must be true).
wantXMLValidation=on

; If true, SAMLResponses with an empty value at its Destination
; attribute will not be rejected for this fact.
relaxDestinationValidation=off

; If true, Destination URL should strictly match to the address to
; which the response has been sent.
; Notice that if 'relaxDestinationValidation' is true an empty Destintation
; will be accepted.
destinationStrictlyMatches=off

; If true, SAMLResponses with an InResponseTo value will be rejectd if not
; AuthNRequest ID provided to the validation method.
rejectUnsolicitedResponsesWithInResponseTo=off

; Algorithm that the toolkit will use on signing process. Options:
;    'http://www.w3.org/2000/09/xmldsig#rsa-sha1'
;    'http://www.w3.org/2000/09/xmldsig#dsa-sha1'
;    'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256'
;    'http://www.w3.org/2001/04/xmldsig-more#rsa-sha384'
;    'http://www.w3.org/2001/04/xmldsig-more#rsa-sha512'
; Notice that rsa-sha1 is a deprecated algorithm and should not be used
signatureAlgorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"

; Algorithm that the toolkit will use on digest process. Options:
;    'http://www.w3.org/2000/09/xmldsig#sha1'
;    'http://www.w3.org/2001/04/xmlenc#sha256'
;    'http://www.w3.org/2001/04/xmldsig-more#sha384'
;    'http://www.w3.org/2001/04/xmlenc#sha512'
; Notice that sha1 is a deprecated algorithm and should not be used
digestAlgorithm="http://www.w3.org/2001/04/xmlenc#sha256"

; ADFS URL-Encodes SAML data as lowercase, and the toolkit by default uses
; uppercase. Turn it True for ADFS compatibility on signature verification
lowercaseUrlencoding=off


[coordplugin_auth]
driver=saml

