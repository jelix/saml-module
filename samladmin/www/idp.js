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
});
