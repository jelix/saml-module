
<div class="block">
    <h2>Homepage</h2>
    <div class="blockcontent">
        <p>Welcome to this application tests.</p>
        {ifuserauthenticated}
            <p><a href="{jurl 'app~pages:profile'}">Access to your profile</a></p>
        {else}
            <p>You can try to <a href="{jurl 'app~pages:profile'}">access to your profile</a>
            although your are not authenticated yet.</p>
        {/ifuserauthenticated}
    </div>
</div>