//$("document").ready( function () {
window.addEventListener('load', function () {

    $("#metadataurl-button").click(function()
    {
         let url = this.getAttribute('data-url');
         let metadataUrl = $("#metadataurl").val();
         let metadataContent = $("#metadata-content").val();
         $.ajax({
             method: 'POST',
             data: {
                 metadata_url: metadataUrl,
                 metadata_content: metadataContent
             },
             error: function(xhr, status, error) {

                 let response = null;
                 let errorMessage = 'Unknown error';
                 let parserError = '';
                 if (xhr.responseJSON !== undefined) {
                     response = xhr.responseJSON;
                     if (response['error'] !== undefined) {
                         errorMessage = response['error'];
                     }
                     if (response['parserError'] !== undefined) {
                         parserError = response['parserError'];
                     }
                 }

                 if (xhr.status == 500) {
                     window.alert(errorMessage+"\n"+parserError);
                 }
                 else {
                     window.alert(errorMessage);
                 }
             },
             success: function(data) {

                 let form = jFormsJQ.getForm('jforms_samladmin_idpconfig').element;

                 form.entityId.value = data.entityId;
                 form.singleSignOnServiceUrl.value = data.singleSignOnServiceUrl;
                 form.singleLogoutServiceUrl.value = data.singleLogoutServiceUrl;
                 form.singleLogoutServiceResponseUrl.value = data.singleLogoutServiceResponseUrl;
                 form.signingCertificate.value = data.signingCertificate;
                 form.encryptionCertificate.value = data.encryptionCertificate;
                 $("#metadata-loader").hide();
                 $("#idpform").show();
                 $("#metadata-loader-open").show();
                 loadCertDetails('signing-cert-details', 'signingCertificate');
                 loadCertDetails('encrypt-cert-details', 'encryptionCertificate');

             },
             url: url
         })
    });
    $("#metadata-loader-open").click(function()
    {
        $("#metadata-loader").show();
        $("#idpform").hide();
        $("#metadata-loader-open").hide();

    });
    $("#metadata-loader-close").click(function()
    {
        $("#metadata-loader").hide();
        $("#idpform").show();
        $("#metadata-loader-open").show();

    });


    function showCertDetails(divid, certField, data)
    {
        let tlsCertElem = jFormsJQ.getForm('jforms_samladmin_idpconfig').element.elements[certField];
        $('#'+divid+' .cert-details-countryName').text(data.C);
        $('#'+divid+' .cert-details-stateOrProvinceName').text(data.ST);
        $('#'+divid+' .cert-details-localityName').text(data.L);
        $('#'+divid+' .cert-details-organizationName').text(data.O);
        $('#'+divid+' .cert-details-organizationalUnitName').text(data.OU);
        $('#'+divid+' .cert-details-commonName').text(data.CN);
        $('#'+divid+' .cert-details-validFrom').text(data.notBefore);
        $('#'+divid+' .cert-details-validTo').text(data.notAfter);

        if (data.valid) {
            $('#'+divid+' .cert-details-validFrom').removeClass('cert-details-invalid');
            $('#'+divid+' .cert-details-validTo').removeClass('cert-details-invalid');
            tlsCertElem.classList.remove('jforms-error');
            tlsCertElem.labels[0].classList.remove('jforms-error');
        }
        else {
            $('#'+divid+' .cert-details-validFrom').addClass('cert-details-invalid');
            $('#'+divid+' .cert-details-validTo').addClass('cert-details-invalid');
            tlsCertElem.classList.add('jforms-error');
            tlsCertElem.labels[0].classList.add('jforms-error');
        }
        $('#'+divid+'-error').hide();
        $('#'+divid).show();
    }

    function loadCertDetails(divid, certField)
    {
        let tlsCertElem = jFormsJQ.getForm('jforms_samladmin_idpconfig').element.elements[certField];
        let certContent = tlsCertElem.value;
        if (certContent == '') {
            $('#'+divid).hide();
            $('#'+divid+'-error').hide();
            tlsCertElem.classList.remove('jforms-error');
            tlsCertElem.labels[0].classList.remove('jforms-error');
            return;
        }
        $.ajax({
            method: 'post',
            data: { cert: certContent },
            error: function(xhr, status, error) {
                console.log('Request error '+status+' '+error);
                $('#'+divid+'-error').show();
                $('#'+divid).hide();
                tlsCertElem.classList.add('jforms-error');
                tlsCertElem.labels[0].classList.add('jforms-error');
            },
            success: function(data) {
                showCertDetails(divid, certField, data);
            },
            url: $('#idpform').attr('data-details-url')
        })
    }

    jFormsJQ.onFormReady('jforms_samladmin_idpconfig', function(/* jFormsJQForm */ form){
        loadCertDetails('signing-cert-details', 'signingCertificate');
        loadCertDetails('encrypt-cert-details', 'encryptionCertificate');

        let signCertField = form.element.elements['signingCertificate'];
        signCertField.addEventListener('change', function() {
            loadCertDetails('signing-cert-details', 'signingCertificate');
        })

        let cryptCertField = form.element.elements['encryptionCertificate'];
        cryptCertField.addEventListener('change', function() {
            loadCertDetails('encrypt-cert-details', 'encryptionCertificate');
        })
    });

});
