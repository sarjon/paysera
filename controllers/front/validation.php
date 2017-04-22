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

class PayseraValidationModuleFrontController extends ModuleFrontController
{
    public $auth = true;

    public $ssl = true;

    /**
     * @var Paysera
     */
    public $module;

    /**
     * Process order validation
     */
    public function postProcess()
    {
        $this->processValidations();

        $cart = $this->context->cart;

        try {
            $orderValidation = $this->module->validateOrder(
                $cart->id,
                (int) Configuration::get('PAYSERA_AWAITING_PAYMENT_ORDER_STATE_ID'),
                $cart->getOrderTotal(),
                $this->module->displayName,
                null,
                [],
                $cart->id_currency,
                false,
                $this->context->customer->secure_key
            );
        } catch (Exception $e) {
            $orderValidation = false;
        }

        if (!$orderValidation) {
            Tools::redirect($this->context->link->getPageLink('order'));
        }

        $order = Order::getByCartId($cart->id);

        $params = [
            'id_cart' => $order->id_cart,
            'id_module' => $this->module->id,
            'id_order' => $order->id,
            'key' => $this->context->customer->secure_key,
        ];

        Tools::redirect(
            $this->context->link->getPageLink('order-confirmation', null, $this->context->language->id, $params)
        );
    }

    /**
     * Process validations (cart, module, currencies and etc.)
     */
    protected function processValidations()
    {
        $cart = $this->context->cart;

        if ($cart->id_customer == 0 ||
            $cart->id_address_delivery == 0 ||
            $cart->id_address_invoice == 0
        ) {
            Tools::redirect($this->context->link->getPageLink('order'));
        }

        if (!$this->module->active ||
            !$this->module->checkCurrency()
        ) {
            Tools::redirect($this->context->link->getPageLink('order'));
        }

        $authorized = false;
        $paymentModules = Module::getPaymentModules();

        foreach ($paymentModules as $module) {
            if ($module['name'] == $this->module->name) {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            $this->errors[] = $this->module->l('This payment method is not available.', 'redirect');
            $this->redirectWithNotifications($this->context->link->getPageLink('order'));
        }

        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect($this->context->link->getPageLink('order'));
        }
    }
}
