{meta_html css $j_jelixwww.'design/jelix.css'}
{meta_html css '/styles.css'}

<h1 class="apptitle">SAML test application</h1>

<div id="page">
    <div id="user">
    {ifuserconnected}
        You are authenticated. <a href="{jurl 'jauth~login:out'}">logout</a>
    {else}
        You are not authenticated.
        <a href="{jurl 'jauth~login:form'}">login</a>
        <a href="{jurl 'jauth~login:form', array('auth_url_return'=>$profile_url)}">login and redirection to profile</a>

    {/ifuserconnected}
        <a href="{jurl 'app~default:index'}">home</a>
        <a href="{jurl 'master_admin~default:index'}">admin</a>
        <a href="{jurl 'saml~endpoint:metadata'}">saml metadata</a>
    </div>
{$MAIN}
</div>
