$(document).ready(()=>{

    document.getElementById('jforms_samladmin_attrmapping').addEventListener('jformsready', ()=>{
        let form = jFormsJQ.getForm('jforms_samladmin_attrmapping').element;
        let onlyNameIdCheckbox = form.elements['useOnlyNameIDAssertionToAuthenticate'];
        let nameIdPlaceholder = form.elements['nameIdPlaceholder'].value;
        if (onlyNameIdCheckbox) {
            onlyNameIdCheckbox.addEventListener('change', (e)=>{
                return useOnlyNameIDAssertionToAuthenticateChange(e.target.checked);
            })

            useOnlyNameIDAssertionToAuthenticateChange(onlyNameIdCheckbox.checked);
        }

        function useOnlyNameIDAssertionToAuthenticateChange(checked){
            let attrRequired = document.querySelectorAll("#jforms_samladmin_attrmapping_attrsgroup input.jforms-required");
            let nameIdFileds = [form.login, ...attrRequired];

            nameIdFileds.forEach((f)=>{
                f.value = checked ? nameIdPlaceholder : f.value;
                f.disabled = checked ? true : false;//!f.disabled;
            })
        }
    })
})