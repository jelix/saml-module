
<form action="{jurl 'saml~auth:login'}">
    <div>
        {if $auth_url_return}<input type="hidden" name="auth_url_return" value="{$auth_url_return|eschtml}"/>{/if}
        <button>login with Saml</button>
    </div>
</form>
<p>or </p>
