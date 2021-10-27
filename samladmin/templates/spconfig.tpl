<h1>{@samladmin~admin.spconfig.title@}</h1>


{form $spform, 'samladmin~spconfig:save'}
<fieldset>
    <legend>{@samladmin~admin.spconfig.form.organization@}</legend>

    <table class="table">
    {formcontrols array('organizationName', 'organizationDisplayName', 'organizationUrl')}
        <tr>
            <th>{ctrl_label}</th><td>{ctrl_control}</td>
        </tr>
    {/formcontrols}
    </table>

</fieldset>

<fieldset>
    <legend>{@samladmin~admin.spconfig.form.contacts@}</legend>

    <table class="table">
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

    {formcontrols array('tlsPrivateKey', 'tlsCertificate')}
        <div>
            {ctrl_label}
            <br/>
            {ctrl_control}
        </div>
    {/formcontrols}

</fieldset>

<div>
{formsubmit}
<a href="{jurl 'samladmin~config:index'}">{@jelix~ui.buttons.cancel@}</a>
</div>

{/form}
