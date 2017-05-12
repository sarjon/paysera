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

class PayseraValidationModuleFrontController extends ModuleFrontController
{
    /**
     * @var Paysera
     */
    public $module;

    /**
     * Process order validation
     */
    public function postProcess()
    {
        $cart = $this->context->cart;

        if (0 == $cart->id_customer ||
            0 == $cart->id_address_delivery ||
            0 == $cart->id_address_invoice ||
            !$this->module->active
        ) {
            $this->setRedirectAfter($this->context->link->getPageLink('order'));
            $this->redirect();
        }

        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == $this->module->name) {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            $this->errors[] = $this->module->l('This payment method is not available.', 'validation');
            $this->redirectWithNotifications($this->context->link->getPageLink('order'));
        }

        $idOrder = $this->processOrderCreate();

        $params = [
            'id_order' => $idOrder,
        ];

        $paymentMethod = Tools::getValue('paysera_payment_method');
        if ($paymentMethod) {
            $params['payment_method'] = $paymentMethod;
        }

        $this->setRedirectAfter(
            $this->context->link->getModuleLink($this->module->name, 'redirect', $params)
        );
    }

    /**
     * Create order
     *
     * @return int
     */
    protected function processOrderCreate()
    {
        $cart     = $this->context->cart;
        $customer = new Customer($cart->id_customer);
        $currency = $this->context->currency;
        $total    = (float) $cart->getOrderTotal();
        $idCart   = $cart->id;

        $idAwaitingOrderState = (int) Configuration::get('PAYSERA_AWAITING_PAYMENT_ORDER_STATE_ID');

        $this->module->validateOrder(
            $idCart,
            $idAwaitingOrderState,
            $total,
            $this->module->displayName,
            null,
            array(),
            $currency->id,
            false,
            $customer->secure_key
        );

        $idOrder = (int) Order::getIdByCartId($idCart);

        return $idOrder;
    }
}
