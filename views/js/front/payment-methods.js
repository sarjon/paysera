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
    var $checkoutPaymentStep = $('#checkout-payment-step');

    $checkoutPaymentStep.find('.js-paysera-payment-country').on('change', togglePaymentMethods);
    $checkoutPaymentStep.find('.js-paysera-payment-method').on('change', togglePaymentMethod);

    /**
     * Toggle payment methods when country is changed
     *
     * @param event
     */
    function togglePaymentMethods()
    {
        var selectedCountry = $(this).val();

        $checkoutPaymentStep.find('.js-paysera-payment-methods').hide();
        $checkoutPaymentStep.find('#payseraPaymentMethods_' + selectedCountry).show();
    }

    /**
     * Toggle payment method value
     *
     * @param event
     */
    function togglePaymentMethod()
    {
        var selectedPaymentMethod = $(this).val();

        $checkoutPaymentStep.find('input[name="paysera_payment_method"]').val(selectedPaymentMethod);
    }
});
