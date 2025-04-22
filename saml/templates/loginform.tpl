
<form action="{jurl 'saml~auth:login'}" id="saml-login-form">
    <div id="saml-connection-button-container">
        {if $auth_url_return}<input type="hidden" name="auth_url_return" value="{$auth_url_return|eschtml}"/>{/if}
        <button id="saml-connection-button">{$button_label|eschtml}</button>
    </div>
</form>

{if $showOnlySaml}
    {if $redirectToSaml}
    <script type="text/javascript">{literal}
        window.addEventListener('load', function(){
            document.getElementById('saml-connection-button').click();
        })
        {/literal}</script>
    {/if}
<div style="display:none">
{else}
<p id="saml-login-text">{@saml~auth.authentication.or.label@}</p>
{/if}