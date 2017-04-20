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

class PayseraAcceptModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if (!$this->module->active) {
            Tools::redirect($this->context->link->getPageLink('index'));
        }

        $idOrder = (int) Tools::getValue('id_order');

        $order = new Order($idOrder);
        $customer = $this->context->customer;

        if (!Validate::isLoadedObject($customer) ||
            !Validate::isLoadedObject($order)
        ) {
            Tools::redirect($this->context->link->getPageLink('index'));
        }

        $params = [
            'id_cart' => $order->id_cart,
            'id_module' => $this->module->id,
            'id_order' => $order->id,
            'key' => $customer->secure_key,
        ];

        Tools::redirect($this->context->link->getPageLink('order-confirmation', null, null, $params));
    }
}
