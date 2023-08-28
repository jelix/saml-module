<h1>{@samladmin~admin.config.title@}</h1>

<ul id="saml-index">
    <li> <a href="{jurl 'samladmin~spconfig:initform'}">{@samladmin~admin.spconfig.link.label@}</a>
        <br><span class="link-details">{@samladmin~admin.spconfig.link.details@}</span>
        {if !$sp_config_ok}<br/><span class="saml-error">{@samladmin~admin.spconfig.error.not.set@}</span>{/if}
    </li>
    <li>
        <a href="{jurl 'samladmin~idpconfig:initform'}">{@samladmin~admin.idpconfig.link.label@}</a>
        <br><span class="link-details">{@samladmin~admin.idpconfig.link.details@}</span>
        {if !$idp_config_ok}<br/><span class="saml-error">{@samladmin~admin.idpconfig.error.not.set@}</span>{/if}
    </li>
    <li>
        <a href="{jurl 'samladmin~attrmapping:initform'}">{@samladmin~admin.attrmapping.link.label@}</a>
        <br><span class="link-details">{@samladmin~admin.attrmapping.link.details@}</span>
        {if !$attr_config_ok}<br/><span class="saml-error">{@samladmin~admin.attrmapping.error.not.set@}</span>{/if}
    </li>
</ul>

<h3>{@samladmin~admin.sp.metadata.url.title@}</h3>


    <p>{@samladmin~admin.sp.metadata.url@} : <a href="{$sp_metadata_url}">{$sp_metadata_url}</a></p>
    {if ! $sp_config_ok || ! $idp_config_ok}
    <p class="saml-error">{@samladmin~admin.sp.metadata.url.not.available@}
    </p>
    {/if}
