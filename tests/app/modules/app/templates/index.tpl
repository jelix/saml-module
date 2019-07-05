
<div class="block">
    <h2>Homepage</h2>
    <div class="blockcontent">
        <p>Welcome to this application tests.</p>
        {ifuserconnected}
            <p><a href="{jurl 'app~pages:profile'}">Access to your profile</a></p>
        {else}
            <p>You can try to <a href="{jurl 'app~pages:profile'}">access to your profile</a>
            although your are not authenticated yet.</p>
        {/ifuserconnected}
    </div>
</div>