;<?php die(''); ?>
;for security reasons , don't remove or modify the first line

startModule=app
startAction="default:index"

[responses]

[modules]
jacl2.access=2

[coordplugins]
jacl2=1

auth="index/auth.coord.ini.php"
auth.class=samlCoordPlugin
[coordplugin_jacl2]
on_error=2
error_message="jacl2~errors.action.right.needed"
on_error_action="jelix~error:badright"

[acl2]
driver=db
