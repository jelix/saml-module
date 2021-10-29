<h1>{@samladmin~admin.idpconfig.title@}</h1>


{form $idpform, 'samladmin~idpconfig:save'}

<fieldset>
    <legend>{@samladmin~admin.idpconfig.form.autofill@}</legend>
    <p><label for="metadataurl">{@samladmin~admin.idpconfig.form.metadata.url@}</label>
    <input type="text" id="metadataurl" />
        <button id="metadataurl-button"
                type="button" data-url="{jurl 'samladmin~idpconfig:loadMetadata'}"
        >{@samladmin~admin.idpconfig.form.metadata.button@}</button>
    </p>
</fieldset>

<table class="jforms-table">
{formcontrols array(
    'serviceLabel',
    'entityId',
    'singleSignOnServiceUrl',
    'singleLogoutServiceUrl',
    'singleLogoutServiceResponseUrl'
)}
    <tr>
        <th>{ctrl_label}</th><td>{ctrl_control}</td>
    </tr>
{/formcontrols}
</table>



{formcontrols array('signingCertificate', 'encryptionCertificate')}
    <p>
        {ctrl_label}
        <br/>
        {ctrl_control}
    </p>
{/formcontrols}


<div>
{formsubmit}
<a href="{jurl 'samladmin~config:index'}">{@jelix~ui.buttons.cancel@}</a>
</div>

{/form}
