<?php
/**
 * This file is part of the paysera module.
 *
 * @author    Sarunas Jonusas, https://github.com/sarjon
 * @copyright Copyright (c) Sarunas Jonusas
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class PayseraRedirectModuleFrontController extends ModuleFrontController
{
    public $auth = true;

    public $ssl = true;

    /**
     * @var Paysera
     */
    public $module;

    /**
     * Process redirection to Paysera system
     */
    public function postProcess()
    {
        $paymentData = $this->collectPaymentData();

        if (null === $paymentData) {
            Tools::redirect($this->context->link->getPageLink('order'));
        }

        $request = WebToPay::buildRequest($paymentData);
        $paymentUrl = WebToPay::getPaymentUrl().'?'.http_build_query($request);

        Tools::redirect($paymentUrl);
    }

    /**
     * Collect payment information from order
     *
     * @return array|null
     */
    protected function collectPaymentData()
    {
        $projectID       = Configuration::get('PAYSERA_PROJECT_ID');
        $projectPassword = Configuration::get('PAYSERA_PROJECT_PASSWORD');
        $testingMode     = Configuration::get('PAYSERA_TESTING_MODE');

        $cart     = $this->context->cart;
        $order    = Order::getByCartId($cart->id);
        $currency = new Currency($order->id_currency);
        $address  = new Address($order->id_address_invoice);
        $country  = new Country($address->id_country);
        $state    = new State($address->id_state);
        $customer = $this->context->customer;

        $urlParams = ['id_order' => $order->id];

        $data = [
            'projectid'     => $projectID,
            'sign_password' => $projectPassword,
            'orderid'       => $order->id,
            'amount'        => $cart->getOrderTotal() * 100,
            'currency'      => $currency->iso_code,
            'country'       => strtoupper($country->iso_code),
            'accepturl'     => $this->context->link->getModuleLink($this->module->name, 'accept', $urlParams),
            'cancelurl'     => $this->context->link->getModuleLink($this->module->name, 'cancel', $urlParams),
            'callbackurl'   => $this->context->link->getModuleLink($this->module->name, 'callback'),
            'test'          => (int) $testingMode,
            'payment'       => Tools::getValue('paysera_payment_method'),
            'p_firstname'   => $customer->firstname,
            'p_lastname'    => $customer->lastname,
            'p_email'       => $customer->email,
            'p_street'      => $address->address1,
            'p_city'        => $address->city,
            'p_state'       => $state->iso_code,
            'p_zip'         => $address->postcode,
            'p_countrycode' => strtoupper($country->iso_code),
            'lang'          => $this->getPayseraLangCode(),
        ];

        return $data;
    }

    /**
     * Get language code which will be sent to paysera
     *
     * @return string
     */
    protected function getPayseraLangCode()
    {
        $langISO = $this->context->language->iso_code;

        switch ($langISO) {
            case 'lt':
                return 'LIT';
            case 'lv':
                return 'LAV';
            case 'ee':
                return 'EST';
            case 'ru':
                return 'RUS';
            case 'de':
                return 'GER';
            case 'pl':
                return 'POL';
            default:
            case 'en':
                return 'ENG';
        }
    }
}
