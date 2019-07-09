;<?php die(''); ?>
;for security reasons , don't remove or modify the first line
;this file doesn't list all possible properties. See lib/jelix/core/defaultconfig.ini.php for that

startModule=app
startAction="default:index"

locale=en_US
availableLocales=en_US
charset=UTF-8

; see http://www.php.net/manual/en/timezones.php for supported values
timeZone="Europe/Paris"

theme=default

pluginsPath="app:plugins/,lib:jelix-plugins/"

modulesPath="lib:jelix-modules/,app:modules/"

; default domain name to use with jfullurl for example.
; Let it empty to use $_SERVER['SERVER_NAME'] value instead.
domainName=


[modules]
; modulename.access = x where x =
; 0 if installed but not used (database schema is ok for example)
; 1 if accessible by other modules (other modules can use it, but it is not accessible directly through the web)
; 2 if public (accessible through the web)

jelix.access=2

; jacldb is deprecated. keep it uninstall if possible. install jacl2db instead
jacldb.access=0

jacl2db.access=0
jauth.access=0
jauthdb.access=1
junittests.access=0
jsoap.access=0

app.access=2

saml.access=2
saml.path="/opt/saml/"
saml.installparam="useradmin=dwho;emailadmin=dwho@lemon.local"

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
; name of url engine :  "simple", "basic_significant" or "significant"
engine=basic_significant

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

defaultEntrypoint=index

; action to show the 'page not found' error
notfoundAct="jelix~error:notfound"

; list of actions which require https protocol for the simple url engine
; syntax of the list is the same as explained in the simple_urlengine_entrypoints
simple_urlengine_https=

[simple_urlengine_entrypoints]
; parameters for the simple url engine. This is the list of entry points
; with list of actions attached to each entry points

; script_name_without_suffix = "list of action selectors separated by a space"
; selector syntax :
;   m~a@r    -> for the action "a" of the module "m" and for the request of type "r"
;   m~c:*@r  -> for all actions of the controller "c" of the module "m" and for the request of type "r"
;   m~*@r    -> for all actions of the module "m" and for the request of type "r"
;   @r       -> for all actions for the request of type "r"

index="@classic"


[basic_significant_urlengine_entrypoints]
; for each entry point, it indicates if the entry point name
; should be include in the url or not
index=on
xmlrpc=on
jsonrpc=on

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
; define input type for datetime widgets : "textboxes" or "menulists"
;controls.datetime.input = "menulists"
; define the way month labels are displayed widgets: "numbers", "names" or "shortnames"
;controls.datetime.months.labels = "names"
; define the default config for datepickers in jforms
;datepicker = default

[datepickers]
;default = jelix/js/jforms/datepickers/default/init.js


[saml:sp]

; enable debug or not in the SAML library
saml_debug=off

; action to redirect to after a successfully login.
after_login =

; action to redirect to after a successfully logout
after_logout =


; Contact information template, it is recommended to supply a technical and
; support contacts
technicalContactPerson[givenName]=
technicalContactPerson[emailAddress]=
supportContactPerson[givenName]=
supportContactPerson[emailAddress]=

organization[name] =
organization[displayname] =
organization[url] =

compressRequests=on
compressResponses=on


; If you need to specify requested attributes, set the service name into attrcs_service_name
; and fill the saml:sp:requestedAttributes section with the list of requested attributes
attrcs_service_name=
attrcs_service_description=

[saml:sp:requestedAttributes]
;<attribute name>[isRequired]=on or off
;<attribute name>[nameFormat]=  ; optional
;<attribute name>[friendlyName]= ; optional
;<attribute name>[attributeValue]= ; optional


[saml:attributes-mapping]
; indicate on which properties of the DAO record, attributes given by the idp should be stored
; __login is a not a property, but indicates which attribute contains the user login.
__login=login
; <dao property>=<saml attribute>
login=login
email=mail



; identity provider
[saml:idp]
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

certs_signing_files=idp_sig.crt
certs_encryption_files=idp_encrypt.crt

; signatures and encryptions offered
[saml:security]

; Indicates that the nameID of the <samlp:logoutRequest> sent by this SP
; will be encrypted.
nameIdEncrypted=off

; Indicates whether the <samlp:AuthnRequest> messages sent by this SP
; will be signed.              [The Metadata of the SP will offer this info]
authnRequestsSigned=off

; Indicates whether the <samlp:logoutRequest> messages sent by this SP
; will be signed.
logoutRequestSigned=off

; Indicates whether the <samlp:logoutResponse> messages sent by this SP
; will be signed.
logoutResponseSigned=off

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

