{meta_html jquery_ui 'default'}
<h1>{@samladmin~admin.spconfig.title@}</h1>


{form $spform, 'samladmin~spconfig:save'}
<fieldset>
    <table class="jforms-table">
        {formcontrols array('entityId')}
            <tr>
                <th>{ctrl_label}</th><td>{ctrl_control}</td>
            </tr>
        {/formcontrols}
    </table>
</fieldset>
<fieldset>
    <legend>{@samladmin~admin.spconfig.form.organization@}</legend>

    <table class="jforms-table">
    {formcontrols array('organizationName', 'organizationDisplayName', 'organizationUrl')}
        <tr>
            <th>{ctrl_label}</th><td>{ctrl_control}</td>
        </tr>
    {/formcontrols}
    </table>

</fieldset>

<fieldset>
    <legend>{@samladmin~admin.spconfig.form.contacts@}</legend>

    <table class="jforms-table">
        {formcontrols array('supportContactPersonName',
        'supportContactPersonEmail',
        'technicalContactPersonName',
        'technicalContactPersonEmail',
        )}
            <tr>
                <th>{ctrl_label}</th><td>{ctrl_control}</td>
            </tr>
        {/formcontrols}
    </table>


</fieldset>

<fieldset>
    <legend>{@samladmin~admin.spconfig.form.certificate@}</legend>
    <p>{@samladmin~admin.spconfig.form.certificate.help@}</p>


    <p>
        {ctrl_label 'tlsPrivateKey'}
        <br/>
        {ctrl_control 'tlsPrivateKey'}
        <br />
        {ctrl_label 'certKeyLength'} {ctrl_control 'certKeyLength'}
        <button type="button" id="tlsPrivateKeyButton"
                data-url="{jurl 'samladmin~spconfig:generateKey'}"
        >{@samladmin~admin.spconfig.form.cert.keyGenerate.label@}</button>
    </p>

    <p>
        {ctrl_label 'tlsCertificate'}
        <br/>
        {ctrl_control 'tlsCertificate'}
        <br />
        <button type="button" id="tlsCertificateButton"
        >{@samladmin~admin.spconfig.form.cert.generate.label@}</button>


    </p>


</fieldset>

<div>
{formsubmit}
<a href="{jurl 'samladmin~config:index'}">{@jelix~ui.buttons.cancel@}</a>
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