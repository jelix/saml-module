<div id="saml-logout-section">
<h2>{@saml~auth.logout.title@}</h2>
{if $error}
<p>{@saml~auth.logout.error.explanation@}</p>
 <div class="error">{$error}</div>
{else}
<p>{@saml~auth.logout.success@}</p>
<p><a href="{jurl 'saml~auth:login'}">{@saml~auth.logout.login.again@}</a>.</p>
{/if}
</div>