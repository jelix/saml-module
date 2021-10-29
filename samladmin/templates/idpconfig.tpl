<h1>{@samladmin~admin.idpconfig.title@}</h1>


{form $idpform, 'samladmin~idpconfig:save'}

<table class="table">
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
