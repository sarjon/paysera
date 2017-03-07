$(document).ready(function() {
    var $paymentMethods = $('.js-paysera-payment-methods');

    $('.js-paysera-payment-country').on('change', togglePaymentMethods);

    /**
     * Toggle payment methods when country is changed
     */
    function togglePaymentMethods(event)
    {
        var value = event.target.value;

        $paymentMethods.hide();
        $('#payseraPaymentMethods_' + value).show();
    }
});
