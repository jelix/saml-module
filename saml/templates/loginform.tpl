
<form action="{jurl 'saml~auth:login'}" id="saml-login-form">
    <div id="saml-connection-button-container">
        {if $auth_url_return}<input type="hidden" name="auth_url_return" value="{$auth_url_return|eschtml}"/>{/if}
        <button id="saml-connection-button">{$button_label|eschtml}</button>
    </div>
</form>
<p id="saml-login-text">{@saml~auth.authentication.or.label@}</p>
