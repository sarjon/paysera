<?php
/**
 * This file is part of the paysera module.
 *
 * @author    Šarūnas Jonušas, https://github.com/sarjon
 * @copyright Copyright (c) Šarūnas Jonušas
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class PayseraRedirectModuleFrontController extends ModuleFrontController
{
    /**
     * @var Paysera
     */
    public $module;

    /**
     * Process redirection to Paysera system
     */
    public function postProcess()
    {
        $idOrder = Tools::getValue('id_order');
        $order = new Order($idOrder);
        $customer = $this->context->customer;

        if (!Validate::isLoadedObject($order) ||
            $customer->id != $order->id_customer ||
            $order->module != $this->module->name ||
            !$this->module->active
        ) {
            $this->setRedirectAfter('404');
            return;
        }

        if ($order->hasBeenPaid()) {
            $idLang = $this->context->language->id;
            $this->setRedirectAfter(
                $this->context->link->getPageLink('order-detail', true, $idLang, ['id_order' => $order->id])
            );
            return;
        }

        $paymentData = $this->collectPaymentData($order);

        WebToPay::redirectToPayment($paymentData, true);
    }

    /**
     * Collect payment information from order
     *
     * @param Order $order
     *
     * @return array|null
     */
    protected function collectPaymentData(Order $order)
    {
        $projectID       = Configuration::get('PAYSERA_PROJECT_ID');
        $projectPassword = Configuration::get('PAYSERA_PROJECT_PASSWORD');
        $testingMode     = Configuration::get('PAYSERA_TESTING_MODE');

        $customer = $this->context->customer;

        $cart     = new Cart($order->id_cart);
        $currency = new Currency($order->id_currency);
        $address  = new Address($order->id_address_invoice);
        $country  = new Country($address->id_country);
        $state    = new State($address->id_state);

        $urlParams = ['id_order' => $order->id];

        $data = [
            'projectid'     => $projectID,
            'sign_password' => $projectPassword,
            'orderid'       => $order->id,
            'amount'        => $this->module->getPayAmmountInCents($cart),
            'currency'      => $currency->iso_code,
            'country'       => strtoupper($country->iso_code),
            'accepturl'     => $this->context->link->getModuleLink($this->module->name, 'accept', $urlParams),
            'cancelurl'     => $this->context->link->getModuleLink($this->module->name, 'cancel', $urlParams),
            'callbackurl'   => $this->context->link->getModuleLink($this->module->name, 'callback'),
            'test'          => (int) $testingMode,
            'payment'       => Tools::getValue('payment_method'),
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
        $langIso = $this->context->language->iso_code;

        switch ($langIso) {
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
