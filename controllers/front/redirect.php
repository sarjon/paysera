<?php

class PayseraRedirectModuleFrontController extends ModuleFrontController
{
    public $auth = true;

    /**
     * @var string If page is accessed directly then redirect to 404
     */
    protected $redirect_after = '404';

    /**
     * @var Paysera
     */
    public $module;

    /**
     * Process redirection to Paysera system
     */
    public function postProcess()
    {
        if (!$this->module->active || !$this->module->checkCurrency()) {
            Tools::redirect($this->context->link->getPageLink('order'));
        }

        $cart = $this->context->cart;

        $this->module->validateOrder(
            $cart->id,
            (int) Configuration::get('PAYSERA_ORDER_STATE_ID'),
            $cart->getOrderTotal(),
            $this->module->displayName,
            null,
            [],
            $cart->id_currency,
            false,
            $this->context->cart->secure_key
        );

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
        $projectID = Configuration::get('PAYSERA_PROJECT_ID');
        $projectPassword = Configuration::get('PAYSERA_PROJECT_PASSWORD');
        $testingMode = Configuration::get('PAYSERA_TESTING_MODE');
        $cart = $this->context->cart;

        $order    = Order::getByCartId($cart->id);
        $currency = new Currency($order->id_currency);
        $address  = new Address($order->id_address_delivery);
        $country  = new Country($address->id_country);
        $customer = $this->context->customer;

        $data = [
            'projectid'     => $projectID,
            'sign_password' => $projectPassword,
            'orderid'       => $order->id,
            'amount'        => $cart->getOrderTotal() * 100,
            'currency'      => $currency->iso_code,
            'country'       => strtoupper($country->iso_code),
            'accepturl'     => $this->context->link->getModuleLink($this->module->name, 'accept'),
            'cancelurl'     => $this->context->link->getModuleLink($this->module->name, 'cancel'),
            'callbackurl'   => $this->context->link->getModuleLink($this->module->name, 'callback'),
            'test'          => (int) $testingMode,
            'payment'       => Tools::getValue('paysera_mayment_method'),
            'p_firstname'   => $customer->firstname,
            'p_lastname'    => $customer->lastname,
            'p_email'       => $customer->email,
            'p_street'      => $address->address1,
            'p_city'        => $address->city,
            'p_zip'         => $address->postcode,
            'p_countrycode' => $country->iso_code,
            //'system'        => 'PrestaShop 1.7',
        ];

        return $data;
    }
}
