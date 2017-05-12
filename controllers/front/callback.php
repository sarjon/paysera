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

class PayseraCallbackModuleFrontController extends ModuleFrontController
{
    /**
     * @var Paysera
     */
    public $module;

    public function postProcess()
    {
        if (!$this->module->active) {
            exit;
        }

        $projectID = Configuration::get('PAYSERA_PROJECT_ID');
        $projectPassword = Configuration::get('PAYSERA_PROJECT_PASSWORD');
        $paymentAcceptedOrderStateID = (int) Configuration::get('PAYSERA_PAYMENT_ACCEPTED_ORDER_STATE_ID');

        try {
            $response = WebToPay::validateAndParseData($_GET, $projectID, $projectPassword);

            if (Paysera::PAYMENT_ACCEPTED == $response['status']) {
                $idOrder = $response['orderid'];
                $responseAmount = (int) $response['payamount'];
                $responseCurrency = $response['paycurrency'];

                $order = new Order($idOrder);
                if ($order->hasBeenPaid()) {
                    exit('OK');
                }

                $cart = new Cart($order->id_cart);
                $orderAmount = $this->module->getPayAmmountInCents($cart);
                $orderCurrency = Currency::getCurrency($order->id_currency);

                if ($responseAmount < $orderAmount) {
                    exit;
                }

                if ($responseCurrency != $orderCurrency['iso_code']) {
                    exit;
                }

                $history = new OrderHistory();
                $history->id_order = $order->id;
                $history->changeIdOrderState($paymentAcceptedOrderStateID, $order->id);
                $history->addWithemail();

                exit('OK');
            }
        } catch (Exception $e) {}

        exit;
    }
}
