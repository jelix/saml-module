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
        height: 400,
        width: 350,
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

    function createCert()
    {
        let certform = jFormsJQ.getForm('jforms_samladmin_cert').element;
        if (jFormsJQ.verifyForm(certform)) {
            dialog.dialog( "close" );

            let dialogGenerate = $( "#dialogTlsCertificateGenerate" ).dialog({
                height: 200,
                width: 350,
                modal: true,
            });

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
                    dialogGenerate.dialog( "close" );
                },
                url: certform.getAttribute('action')
            })


        }
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
});
