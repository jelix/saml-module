<h1>{@samladmin~admin.attrmapping.form.title@}</h1>
{form $attrform, 'samladmin~attrmapping:save'}
    <p>
        {ctrl_label 'login'}
        {ctrl_control 'login'}
    </p>

    <div>
        {ctrl_label 'attrsgroup'}
        {ctrl_control 'attrsgroup'}
    </div>
    <p>
        {ctrl_control 'automaticAccountCreation'}
        {ctrl_label 'automaticAccountCreation'}

    </p>
    <div>
        {formsubmit}
        <a href="{jurl 'samladmin~config:index'}">{@jelix~ui.buttons.cancel@}</a>
    </div>
{/form}