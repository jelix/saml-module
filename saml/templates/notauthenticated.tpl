<div id="saml-not-authenticated-message">
<p>{if $error}{@saml~auth.authentication.error.explanation@}
    {else}{@saml~auth.authentication.error.explanation.not.authorized@}{/if}
</p>
{if $error}
    <div class="error"><pre>{$error|eschtml}</pre></div>
{else}
    <p><a href="{jurl 'saml~auth:login'}">{@saml~auth.authentication.error.try.again@}</a>.</p>
{/if}
</div>