{meta_html jquery_ui 'default'}
<h1>{@samladmin~admin.spconfig.title@}</h1>


{form $spform, 'samladmin~spconfig:save'}
<fieldset>
    <div class="control-group">
        {ctrl_label 'entityId'}
        <div class="controls">
            {ctrl_control 'entityId'}
        </div>
    </div>
</fieldset>
    <div class="control-group">
        {ctrl_label 'organization'}
        <div class="controls">
            {ctrl_control 'organization'}
        </div>
    </div>

    <div class="control-group">
        {ctrl_label 'contacts'}
        <div class="controls">
            {ctrl_control 'contacts'}
        </div>
    </div>

<fieldset>
    <legend>{@samladmin~admin.spconfig.form.certificate@}</legend>
    <p>{@samladmin~admin.spconfig.form.certificate.help@}</p>


    <div class="control-group">
        {ctrl_label 'tlsPrivateKey'}
        <div class="controls">
        {ctrl_control 'tlsPrivateKey'}
        <br />

        <button type="button" id="tlsPrivateKeyButton"
                data-url="{jurl 'samladmin~spconfig:generateKey'}"
        >{@samladmin~admin.spconfig.form.cert.keyGenerate.label@}</button>
            {ctrl_label 'certKeyLength'} {ctrl_control 'certKeyLength'}
        </div>
    </div>

    <div class="control-group">
        {ctrl_label 'tlsCertificate'}
        <div class="controls">
        {ctrl_control 'tlsCertificate'}
        <br />
        <button type="button" id="tlsCertificateButton"
        >{@samladmin~admin.spconfig.form.cert.generate.label@}</button>
        </div>

    </div>


</fieldset>

<div class="control-group">
{formsubmit}
<a href="{jurl 'samladmin~config:index'}" class="btn">{@jelix~ui.buttons.cancel@}</a>
</div>
{/form}

<div id="dialogTlsCertificate"
     title="{@samladmin~admin.spconfig.form.cert.generate.title@}"
     data-ok-button="{@samladmin~admin.spconfig.form.cert.create.button@}"
     data-cancel-button="{@jelix~ui.buttons.cancel@}"
>
    {form $certForm, 'samladmin~spconfig:generateCert'}
    {formcontrols array('certCountryName', 'certStateOrProvinceName', 'certLocalityName', 'certOrganizationName', 'certOrganizationalUnitName', 'certCommonName', 'certDaysValidity', )}
        <p>
            {ctrl_label} {ctrl_control}
        </p>
    {/formcontrols}
    {/form}
</div>


<div id="dialogTlsCertificateGenerate" title="{@samladmin~admin.spconfig.form.cert.generate.title@}">
    <p>{@samladmin~admin.spconfig.form.cert.generate.process@}</p>
    <p><progress></progress></p>
</div>