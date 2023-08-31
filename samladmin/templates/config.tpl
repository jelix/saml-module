<h1>{@samladmin~admin.config.title@}</h1>

<p>{@samladmin~admin.config.intro@}</p>

<h2><span class="saml-step">1</span> {@samladmin~admin.step.spconfig.title@}
    <span class="saml-status">{if $sp_config_ok}✅{else}❌{/if}</span></h2>

<p><a href="{jurl 'samladmin~spconfig:initform'}">{@samladmin~admin.spconfig.link.label@}</a>
    <br><span class="link-details">{@samladmin~admin.spconfig.link.details@}</span>
    {if !$sp_config_ok}<br/><span class="saml-error">{@samladmin~admin.spconfig.error.not.set@}</span>{/if}</p>


<h2><span class="saml-step">2</span> {@samladmin~admin.step.idpserver.title@}  <span class="saml-status"></span></h2>


<p>{@samladmin~admin.step.idpserver.description@}</p>
    <p>{@samladmin~admin.step.idpserver.metadata.desc@}</p>

<p><a href="{$sp_metadata_url}">{$sp_metadata_url}</a></p>
{if ! $sp_config_ok}
    <p class="saml-error">{@samladmin~admin.sp.metadata.url.not.available@}</p>
{/if}

<p>{@samladmin~admin.step.idpserver.otherurls@}</p>
<dl class="saml-url">
    <dt>{@samladmin~admin.sp.sls.url@}</dt>
    <dd> {$sp_sls_url}</dd>
    <dt>{@samladmin~admin.sp.acs.url@}</dt>
    <dd> {$sp_acs_url}</dd>
</dl>

<h2><span class="saml-step">3</span> {@samladmin~admin.step.idpconfig.title@}
    <span class="saml-status">{if $idp_config_ok}✅{else}❌{/if}</span></h2>

<p>
    <a href="{jurl 'samladmin~idpconfig:initform'}">{@samladmin~admin.idpconfig.link.label@}</a>
    <br><span class="link-details">{@samladmin~admin.idpconfig.link.details@}</span>
    {if !$idp_config_ok}<br/><span class="saml-error">{@samladmin~admin.idpconfig.error.not.set@}</span>{/if}
</p>

<h2><span class="saml-step">4</span> {@samladmin~admin.step.attrmapping.title@}
    <span class="saml-status">{if $attr_config_ok}✅{else}❌{/if}</span></h2>

<p>
    <a href="{jurl 'samladmin~attrmapping:initform'}">{@samladmin~admin.attrmapping.link.label@}</a>
    <br><span class="link-details">{@samladmin~admin.attrmapping.link.details@}</span>
    {if !$attr_config_ok}<br/><span class="saml-error">{@samladmin~admin.attrmapping.error.not.set@}</span>{/if}
</p>



