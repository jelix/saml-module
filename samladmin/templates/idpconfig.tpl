<h1>{@samladmin~admin.idpconfig.title@}</h1>

<button id="metadata-loader-open" type="button">{@samladmin~admin.idpconfig.form.autofill@} &gt;</button>
<fieldset id="metadata-loader">
    <legend>{@samladmin~admin.idpconfig.form.autofill@}</legend>
    <p>{@samladmin~admin.idpconfig.form.metadata.help@}</p>

    <div><label for="metadataurl">{@samladmin~admin.idpconfig.form.metadata.url@}</label>
        <br>
        <input type="text" id="metadataurl" size="50"/>
    </div>
    <div><label for="metadata-content">{@samladmin~admin.idpconfig.form.metadata.content@}</label>
        <br>
        <textarea id="metadata-content" cols="40" rows="10"></textarea>
    </div>


    <p>
    <button id="metadataurl-button"
            type="button" data-url="{jurl 'samladmin~idpconfig:loadMetadata'}"
    >{@samladmin~admin.idpconfig.form.metadata.button@}</button>

    <button id="metadata-loader-close">{@samladmin~admin.idpconfig.form.autofill.close@}</button>
    </p>
</fieldset>
<div id="idpform">
{form $idpform, 'samladmin~idpconfig:save'}


    <table class="jforms-table">
    <tr>
        <th>{ctrl_label 'serviceLabel'}</th><td>{ctrl_control 'serviceLabel'}</td>
    </tr>
    </table>



<table class="jforms-table">
{formcontrols array(
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
</div>