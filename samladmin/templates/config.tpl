<h1>{@samladmin~admin.config.title@}</h1>

<ul>
    <li>
        <a href="{jurl 'samladmin~spconfig:initform'}">{@samladmin~admin.spconfig.title@}</a>
        {if !$sp_config_ok}<span class="error">{@samladmin~admin.spconfig.error.not.set@}</span>{/if}
    </li>
    <li>
        <a href="{jurl 'samladmin~idpconfig:initform'}">{@samladmin~admin.idpconfig.title@}</a>
        {if !$idp_config_ok}<span class="error">{@samladmin~admin.idpconfig.error.not.set@}</span>{/if}
    </li>
    <li>
        <a href="{jurl 'samladmin~attrmapping:initform'}">{@samladmin~admin.attrmapping.title@}</a>
        {if !$attr_config_ok}<span class="error">{@samladmin~admin.attrmapping.error.not.set@}</span>{/if}
    </li>
</ul>


<p>{@samladmin~admin.sp.metadata.url@}
{if $sp_config_ok && $attr_config_ok}
    <a href="{$sp_metadata_url}">{$sp_metadata_url}</a>
{else}
    <span class="error">{@samladmin~admin.sp.metadata.url.not.available@}</span>
{/if}
</p>
