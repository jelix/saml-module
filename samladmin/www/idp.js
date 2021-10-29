//$("document").ready( function () {
window.addEventListener('load', function () {
    $("#metadataurl-button").click(function()
    {
         let url = this.getAttribute('data-url');
         let metadataUrl = $("#metadataurl").val();
         console.log(metadataUrl);
         $.ajax({
             data: {
                 metadata_url: metadataUrl
             },
             error: function(xhr, status, error) {
                 window.alert('Request error');
             },
             success: function(data) {

                 let form = jFormsJQ.getForm('jforms_samladmin_idpconfig').element;

                 form.entityId.value = data.entityId;
                 form.singleSignOnServiceUrl.value = data.singleSignOnServiceUrl;
                 form.singleLogoutServiceUrl.value = data.singleLogoutServiceUrl;
                 form.singleLogoutServiceResponseUrl.value = data.singleLogoutServiceResponseUrl;
                 form.signingCertificate.value = data.signingCertificate;
                 form.encryptionCertificate.value = data.encryptionCertificate;
             },
             url: url
         })
    });

});
