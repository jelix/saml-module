window.addEventListener('load', function () {

    $("#tlsPrivateKeyButton").click(function()
    {
        let form = jFormsJQ.getForm('jforms_samladmin_spconfig').element;
        let length = form.elements['certKeyLength'].value;
        let url = $(this).attr('data-url');

        $.ajax({
            data: {
                keylength: length
            },
            error: function(xhr, status, error) {
                window.alert('Request error');
            },
            success: function(data) {

                let form = jFormsJQ.getForm('jforms_samladmin_spconfig').element;
                form.tlsPrivateKey.value = data.privateKey;
                form.tlsCertificate.value = '';
            },
            url: url
        })
    });


    let divDialog = $( "#dialogTlsCertificate" );

    let dialog = divDialog.dialog({
        autoOpen: false,
        height: 450,
        width: 500,
        modal: true,
        buttons: [
            {
                text: divDialog.attr('data-ok-button'),
                click: createCert
            },
            {
                text: divDialog.attr('data-cancel-button'),
                click: function() {
                    dialog.dialog("close");
                }
            }
        ],
        close: function() {
        }
    });

    let divDialogTLSGenerate = $( "#dialogTlsCertificateGenerate" );
    let dialogGenerate = divDialogTLSGenerate.dialog({
        autoOpen: false,
        height: 200,
        width: 350,
        modal: true,
    });


    function createCert()
    {
        let certform = jFormsJQ.getForm('jforms_samladmin_cert').element;
        if (jFormsJQ.verifyForm(certform)) {
            dialog.dialog( "close" );
            dialogGenerate.dialog('open');

            let formData = new FormData(certform);
            formData.append('privKey', jFormsJQ.getForm('jforms_samladmin_spconfig').element.tlsPrivateKey.value);

            let data = {}
            for(var pair of formData.entries()) {
                data[pair[0]] = pair[1];
            }

            $.ajax({
                method: 'post',
                data: data,
                error: function(xhr, status, error) {
                    window.alert('Request error '+status+' '+error);
                    dialogGenerate.dialog( "close" );
                },
                success: function(data) {
                    let form = jFormsJQ.getForm('jforms_samladmin_spconfig').element;
                    form.tlsCertificate.value = data.certificate;
                    showCertDetails(data);
                    dialogGenerate.dialog( "close" );
                },
                url: certform.getAttribute('action')
            })
        }
    }

    function loadCertDetails()
    {
        let form = jFormsJQ.getForm('jforms_samladmin_spconfig').element;
        let certContent = form.tlsCertificate.value;
        if (certContent == '') {
            $('#cert-details').hide();
            $('#cert-details-error').hide();
            return;
        }
        $.ajax({
            method: 'post',
            data: { cert: certContent },
            error: function(xhr, status, error) {
                console.log('Request error '+status+' '+error);
                $('#cert-details-error').show();
                $('#cert-details').hide();
                let tlsCertElem = jFormsJQ.getForm('jforms_samladmin_spconfig').element.elements['tlsCertificate'];
                tlsCertElem.classList.add('jforms-error');
                tlsCertElem.labels[0].classList.add('jforms-error');
            },
            success: function(data) {
                showCertDetails(data);
            },
            url: $('#cert-details').attr('data-url')
        })
    }

    function showCertDetails(data)
    {
        let tlsCertElem = jFormsJQ.getForm('jforms_samladmin_spconfig').element.elements['tlsCertificate'];
        $('#cert-details-error').hide();
        $('#cert-details-countryName').text(data.C);
        $('#cert-details-stateOrProvinceName').text(data.ST);
        $('#cert-details-localityName').text(data.L);
        $('#cert-details-organizationName').text(data.O);
        $('#cert-details-organizationalUnitName').text(data.OU);
        $('#cert-details-commonName').text(data.CN);
        $('#cert-details-validFrom').text(data.notBefore);
        $('#cert-details-validTo').text(data.notAfter);
        if (data.valid) {
            $('#cert-details-validFrom').removeClass('cert-details-invalid');
            $('#cert-details-validTo').removeClass('cert-details-invalid');
            tlsCertElem.classList.remove('jforms-error');
            tlsCertElem.labels[0].classList.remove('jforms-error');
        }
        else {
            $('#cert-details-validFrom').addClass('cert-details-invalid');
            $('#cert-details-validTo').addClass('cert-details-invalid');
            tlsCertElem.classList.add('jforms-error');
            tlsCertElem.labels[0].classList.add('jforms-error');
        }
        $('#cert-details').show();
    }

    dialog.find( "form" ).on( "submit", function( event ) {
        event.preventDefault();
        createCert();
    });

    $("#tlsCertificateButton").click(function()
    {
        let form = jFormsJQ.getForm('jforms_samladmin_spconfig').element;
        let certform = jFormsJQ.getForm('jforms_samladmin_cert').element;
        let certOrganizationName = certform.elements['certOrganizationName'].value;
        if (certOrganizationName == '') {
            certform.elements['certOrganizationName'].value = form.elements['organizationDisplayName'].value;
        }
        let certCommonName = certform.elements['certCommonName'].value;
        if (certCommonName == '') {
            let domain = form.elements['organizationUrl'].value;
            if (/^https?:\/\//.test(domain)) {
                let url = new URL(domain);
                domain = url.hostname
            }
            certform.elements['certCommonName'].value = domain;
        }
        dialog.dialog( "open" );
    });

    jFormsJQ.onFormReady('jforms_samladmin_spconfig', function(/* jFormsJQForm */ form){
        loadCertDetails();
        let certField = form.element.elements['tlsCertificate'];

        certField.addEventListener('change', function() {
            loadCertDetails();
        })
    });
});
