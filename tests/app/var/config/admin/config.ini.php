;<?php die(''); ?>
;for security reasons , don't remove or modify the first line

startModule=master_admin
startAction="default:index"

[responses]
html=adminHtmlResponse
htmlauth=adminLoginHtmlResponse

[coordplugins]

jacl2=1
auth="admin/auth.coord.ini.php"
auth.class=samlCoordPlugin

[modules]
jauth.access=2
master_admin.access=2
jacl2db_admin.access=2
jauthdb_admin.access=2
jacl2.access=2
samladmin.access=2
samladmin.path="/opt/samladmin/"


[coordplugin_jacl2]
on_error=2
error_message="jacl2~errors.action.right.needed"
on_error_action="jelix~error:badright"

[acl2]
driver=db

