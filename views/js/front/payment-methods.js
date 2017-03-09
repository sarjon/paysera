/**
 * This file is part of the paysera module.
 *
 * @author    Sarunas Jonusas, https://github.com/sarjon
 * @copyright Copyright (c) Sarunas Jonusas
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$(document).ready(function() {
    $('.js-paysera-payment-country').on('change', togglePaymentMethods);
    $('.js-paysera-payment-method').on('change', togglePaymentMethod);

    /**
     * Toggle payment methods when country is changed
     *
     * @param event
     */
    function togglePaymentMethods(event)
    {
        var value = event.target.value;

        $('.js-paysera-payment-methods').hide();
        $('#payseraPaymentMethods_' + value).show();
    }

    /**
     * Toggle payment method value
     *
     * @param event
     */
    function togglePaymentMethod(event)
    {
        $('input[name="paysera_payment_method"]').val(event.target.value);
    }
});
