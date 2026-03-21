$(document).ready(() => {
    const value_form  = $('#AGAFFILIATE_COOKIE');

    value_form.keyup(function() {
        $(this).val(this.value.replace(/\D/g, ''));
    });
});