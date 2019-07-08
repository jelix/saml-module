;<?php die(''); ?>
;for security reasons , don't remove or modify the first line

driver = saml

;============ Parameters for the plugin
; session variable name
session_name = "JELIX_USER"

; set to 'on' to destroy the session on logout
session_destroy = on

; If the value is "on", the user must be authentificated for all actions, except those
; for which a plugin parameter  auth.required is false
; If the value is "off", the authentification is not required for all actions, except those
; for which a plugin parameter  auth.required is true
auth_required = on

; What to do if an authentification is required but the user is not authentificated
; 1 = generate an error. This value should be set for web services (xmlrpc, jsonrpc...)
; 2 = redirect to an action
on_error = 2

; locale key for the error message when on_error=1
error_message = "saml~auth.notlogged"

; action to execute on a missing authentification when on_error=2
on_error_action = "saml~auth:notauthenticated"


;------- parameters for the "saml" driver
[saml]
compatiblewithdb = on

; name of the dao to get user data
dao = "jauthdb~jelixuser"

; profile to use for jDb 
profile = ""

; name of the form for the jauthdb_admin module
form = "jauthdb_admin~jelixuser"

; path of the directory where to store files uploaded by the form (jauthdb_admin module)
; should be related to the var directory of the application
uploadsDirectory= ""

; value "on": create the user in the database if it doesn't exist when the user is authenticated
automaticAccountCreation=on

; other parameters for SAML service provider and identity provider are
; into saml:* sections into the main configuration.
