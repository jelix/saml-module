
<h2>Logged out</h2>
{if $error}
<p>Sorry, an error appear during the logout:</p>
 <div class="error">{$error}</div>
{else}
<p>Sucessfully logged out</p>
{/if}