;<?php die(''); ?>
;for security reasons , don't remove or modify the first line


[responses]

[coordplugins]
jacl2=1
sessionauth=on

[coordplugin_jacl2]
on_error=2
error_message="jacl2~errors.action.right.needed"
on_error_action="jelix~error:badright"

[jacl2]
on_error=2
error_message="jacl2~errors.action.right.needed"
on_error_action="jelix~error:badright"
