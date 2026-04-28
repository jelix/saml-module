<h1>{@samladmin~admin.idpconfig.title@}</h1>

<button id="metadata-loader-open" type="button">{@samladmin~admin.idpconfig.form.autofill@} &gt;</button>
<fieldset id="metadata-loader">
    <legend>{@samladmin~admin.idpconfig.form.autofill@}</legend>
    <p>{@samladmin~admin.idpconfig.form.metadata.help@}</p>

    <div class="control-group"><label for="metadataurl" class="control-label">{@samladmin~admin.idpconfig.form.metadata.url@}</label>
        <div class="controls">
        <input type="text" id="metadataurl" size="50"/>
        </div>
    </div>
    <div class="control-group">
        <label for="metadata-content" class="control-label">{@samladmin~admin.idpconfig.form.metadata.content@}</label>
        <div class="controls">
        <textarea id="metadata-content" cols="40" rows="10"></textarea>
        </div>
    </div>


    <p>
    <button id="metadataurl-button" class="btn"
            type="button" data-url="{jurl 'samladmin~idpconfig:loadMetadata'}"
    >{@samladmin~admin.idpconfig.form.metadata.button@}</button>

    <button id="metadata-loader-close" class="btn">{@samladmin~admin.idpconfig.form.autofill.close@}</button>
    </p>
</fieldset>



<div id="idpform" data-details-url="{jurl 'samladmin~spconfig:certDetails'}">
{form $idpform, 'samladmin~idpconfig:save'}

    <div class="control-group">
        {ctrl_label 'serviceLabel'}
        <div class="controls">
            {ctrl_control 'serviceLabel'}
        </div>
    </div>

    <div class="control-group">
        {ctrl_label 'endpoints'}
        <div class="controls">
            {ctrl_control 'endpoints'}
        </div>
    </div>

    <fieldset>
        <legend>{@samladmin~admin.idpconfig.form.certificates.label@}</legend>
        <div class="samladmin-cert-block">
            <div class="control-group">
                {ctrl_label 'signingCertificate'}
                <div class="controls">
                    {ctrl_control 'signingCertificate'}
                </div>
            </div>
            <div id="signing-cert-details" class="cert-details">
                <table>
                    <tr>
                        <th>{@samladmin~admin.spconfig.form.cert.countryName.label@} :</th>
                        <td><span class="cert-details-countryName"></span></td>
                    </tr>
                    <tr>
                        <th>{@samladmin~admin.spconfig.form.cert.stateOrProvinceName.label@} :</th>
                        <td><span class="cert-details-stateOrProvinceName"></span></td>
                    </tr>
                    <tr>
                        <th>{@samladmin~admin.spconfig.form.cert.localityName.label@} :</th>
                        <td><span class="cert-details-localityName"></span></td>
                    </tr>
                    <tr>
                        <th>{@samladmin~admin.spconfig.form.cert.organizationName.label@} :</th>
                        <td><span class="cert-details-organizationName"></span></td>
                    </tr>
                    <tr>
                        <th>{@samladmin~admin.spconfig.form.cert.organizationalUnitName.label@} :</th>
                        <td><span class="cert-details-organizationalUnitName"></span></td>
                    </tr>
                    <tr>
                        <th>{@samladmin~admin.spconfig.form.cert.commonName.label@} :</th>
                        <td><span class="cert-details-commonName"></span></td>
                    </tr>
                    <tr>
                        <th>{@samladmin~admin.spconfig.form.cert.validFrom.label@} :</th>
                        <td><span class="cert-details-validFrom"></span></td>
                    </tr>
                    <tr>
                        <th>{@samladmin~admin.spconfig.form.cert.validTo.label@} :</th>
                        <td><span class="cert-details-validTo"></span></td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="samladmin-cert-block">
            <div class="control-group">
                {ctrl_label 'encryptionCertificate'}
                <div class="controls">
                    {ctrl_control 'encryptionCertificate'}
                </div>
            </div>
            <div id="encrypt-cert-details" class="cert-details" data-url="{jurl 'samladmin~spconfig:certDetails'}">
                <table>
                    <tr>
                        <th>{@samladmin~admin.spconfig.form.cert.countryName.label@} :</th>
                        <td><span class="cert-details-countryName"></span></td>
                    </tr>
                    <tr>
                        <th>{@samladmin~admin.spconfig.form.cert.stateOrProvinceName.label@} :</th>
                        <td><span class="cert-details-stateOrProvinceName"></span></td>
                    </tr>
                    <tr>
                        <th>{@samladmin~admin.spconfig.form.cert.localityName.label@} :</th>
                        <td><span class="cert-details-localityName"></span></td>
                    </tr>
                    <tr>
                        <th>{@samladmin~admin.spconfig.form.cert.organizationName.label@} :</th>
                        <td><span class="cert-details-organizationName"></span></td>
                    </tr>
                    <tr>
                        <th>{@samladmin~admin.spconfig.form.cert.organizationalUnitName.label@} :</th>
                        <td><span class="cert-details-organizationalUnitName"></span></td>
                    </tr>
                    <tr>
                        <th>{@samladmin~admin.spconfig.form.cert.commonName.label@} :</th>
                        <td><span class="cert-details-commonName"></span></td>
                    </tr>
                    <tr>
                        <th>{@samladmin~admin.spconfig.form.cert.validFrom.label@} :</th>
                        <td><span class="cert-details-validFrom"></span></td>
                    </tr>
                    <tr>
                        <th>{@samladmin~admin.spconfig.form.cert.validTo.label@} :</th>
                        <td><span class="cert-details-validTo"></span></td>
                    </tr>
                </table>
            </div>
        </div>

    </fieldset>

<div class="control-group">
{formsubmit}
<a href="{jurl 'samladmin~config:index'}" class="btn">{@jelix~ui.buttons.cancel@}</a>
</div>

{/form}
</div>
