<?php

class PayseraAcceptModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if (!$this->module->active) {
            Tools::redirect($this->context->link->getPageLink('order'));
        }

        $projectID         = Configuration::get('PAYSERA_PROJECT_ID');
        $projectPassword   = Configuration::get('PAYSERA_PROJECT_PASSWORD');

        $response = WebToPay::validateAndParseData($_REQUEST, $projectID, $projectPassword);

        $idOrder = $response['orderid'];
        $order = new Order($idOrder);
        $customer = $this->context->customer;

        if (!Validate::isLoadedObject($customer) ||
            !Validate::isLoadedObject($order)
        ) {
            Tools::redirect($this->context->link->getPageLink('order'));
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
