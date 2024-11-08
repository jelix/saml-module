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

    </p>
    <div>
        {formsubmit}
        <a href="{jurl 'samladmin~config:index'}">{@jelix~ui.buttons.cancel@}</a>
    </div>
{/form}
