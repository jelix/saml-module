<p>
    Sorry, you cannot use this application because
    {if $error}you are not authenticated because of the following error:
    {else}you are not authorized.{/if}
</p>
{if $error}
    <div class="error"><pre>{$error|eschtml}</pre></div>
{else}
    <p><a href="{jurl 'saml~auth:login'}">Try again</a>.</p>
{/if}