<h1>{@samladmin~admin.attrmapping.title@}</h1>
{form $attrform, 'samladmin~attrmapping:save'}
    <p>
        {ctrl_label 'login'}
        {ctrl_control 'login'}
    </p>

    <div>
        {ctrl_label 'attrsgroup'}
        {ctrl_control 'attrsgroup'}
    </div>

    <div>
        {ctrl_label 'groupsfromsaml'}
        {ctrl_control 'groupsfromsaml'}
    </div>

    <p>
        {ctrl_control 'automaticAccountCreation'}
        {ctrl_label 'automaticAccountCreation'}
    </p>
    <p>
        {ctrl_control 'allowSAMLAccountToUseLocalPassword'}
        {ctrl_label 'allowSAMLAccountToUseLocalPassword'}

    </p>
    <p>
        {ctrl_control 'forceSAMLAuthOnPrivatePage'}
        {ctrl_label 'forceSAMLAuthOnPrivatePage'}
        <br/> {@samladmin~admin.attrmapping.form.forceSAML.warning@}
    </p>
    <p>
        {ctrl_control 'forceSAMLAuthOnLoginPage'}
        {ctrl_label 'forceSAMLAuthOnLoginPage'}
        {if $loginFormUrl}<br/>
        {@samladmin~admin.attrmapping.form.forceSAMLAuthOnLoginPage.help@} <a href="{$loginFormUrl}">{$loginFormUrl}</a>.
        {/if}
        <br />{@samladmin~admin.attrmapping.form.forceSAML.warning@}
    </p>
    <p>
        {ctrl_label 'redirectionAfterLogin'}<br/>
        {ctrl_control 'redirectionAfterLogin'}
    </p>

    <div>
        {formsubmit}
        <a href="{jurl 'samladmin~config:index'}">{@jelix~ui.buttons.cancel@}</a>
    </div>
{/form}
