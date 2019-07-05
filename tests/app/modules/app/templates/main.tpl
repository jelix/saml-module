{meta_html css $j_jelixwww.'design/jelix.css'}
{meta_html css '/styles.css'}

<h1 class="apptitle">SAML test application</h1>

<div id="page">
    <div id="user">
    {ifuserconnected}You are identified. <a href="{jurl 'saml~auth:logout'}">logout</a>
    {else}You are not identified. <a href="{jurl 'saml~auth:login'}">login</a>{/ifuserconnected}
    </div>
{$MAIN}
</div>