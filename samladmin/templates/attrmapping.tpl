<h1>{@samladmin~admin.attrmapping.title@}</h1>
{form $attrform, 'samladmin~attrmapping:save'}
    <div class="control-group">
        {ctrl_label 'login'}
        <div class="controls">
        {ctrl_control 'login'}
        </div>
    </div>

    <div  class="control-group">
        {ctrl_label 'attrsgroup'}
        {ctrl_control 'attrsgroup'}
    </div>

    <div  class="control-group">
        {ctrl_label 'groupsfromsaml'}
        {ctrl_control 'groupsfromsaml'}
    </div>
    <div  class="control-group">
        {ctrl_control 'automaticAccountCreation'}
        {ctrl_label 'automaticAccountCreation'}
    </div>
    <div  class="control-group">
        {ctrl_control 'allowSAMLAccountToUseLocalPassword'}
        {ctrl_label 'allowSAMLAccountToUseLocalPassword'}
    </div>
    <div  class="control-group">
        {ctrl_label 'forceSAMLAuthOnPrivatePage'}
        <div class="controls">
        {ctrl_control 'forceSAMLAuthOnPrivatePage'}
        </div>
    </div>
    <div class="control-group">
        {ctrl_label 'forceSAMLAuthOnLoginPage'}
        <div class="controls">
        {ctrl_control 'forceSAMLAuthOnLoginPage'}
        </div>
    </div>
    <div class="control-group">
        {ctrl_label 'redirectionAfterLogin'}
        <div class="controls">
            {ctrl_control 'redirectionAfterLogin'}
        </div>
    </div>

    <div class="control-group">
        {ctrl_label 'localLogoutOnly'}
        <div class="controls">
            {ctrl_control 'localLogoutOnly'}
        </div>
    </div>

    <p>(1) {@samladmin~admin.attrmapping.form.forceSAML.warning@}
        {if $loginFormUrl}
            {@samladmin~admin.attrmapping.form.forceSAMLAuthOnLoginPage.help@} <a href="{$loginFormUrl}">{$loginFormUrl}</a>.
        {/if}
    </p>
    <div class="control-group">
        {formsubmit}
        <a href="{jurl 'samladmin~config:index'}" class="btn">{@jelix~ui.buttons.cancel@}</a>
    </div>
{/form}
