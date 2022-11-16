;<?php die(''); ?>
;for security reasons , don't remove or modify the first line


[responses]
html=adminHtmlResponse
htmlauth=adminLoginHtmlResponse

[coordplugins]

jacl2=1
auth="admin/auth.coord.ini.php"
auth.class=samlCoordPlugin

[coordplugin_jacl2]
on_error=2
error_message="jacl2~errors.action.right.needed"
on_error_action="jelix~error:badright"

[acl2]
driver=db


hiddenRights=
hideRights=off
authAdapterClass=jAcl2JAuthAdapter
[jacl2]
on_error=2
error_message="jacl2~errors.action.right.needed"
on_error_action="jelix~error:badright"
