;<?php die(''); ?>
;for security reasons , don't remove or modify the first line

startModule=master_admin
startAction="default:index"

[responses]
html=adminHtmlResponse
htmlauth=adminLoginHtmlResponse

[coordplugins]
auth="saml/saml.coord.ini.php"

[modules]
jacl.access=0
jacldb.access=0
junittests.access=0
jsoap.access=0
jauth.access=2
master_admin.access=2

[simple_urlengine_entrypoints]
admin="jacl2db~*@classic, jauth~*@classic, jacl2db_admin~*@classic, jauthdb_admin~*@classic, master_admin~*@classic, jpref_admin~*@classic"

