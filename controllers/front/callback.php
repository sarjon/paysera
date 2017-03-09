<?php

class PayseraCallbackModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if (!$this->module->active) {
            exit;
        }

        $projectID         = Configuration::get('PAYSERA_PROJECT_ID');
        $projectPassword   = Configuration::get('PAYSERA_PROJECT_PASSWORD');
        $payseraOrderState = (int) Configuration::get('PAYSERA_ORDER_STATE_ID');
        $paymentAcceptedOrderStateID = (int) Configuration::get('PS_OS_PAYMENT');

        try {
            $response = WebToPay::validateAndParseData($_GET, $projectID, $projectPassword);

            if ($response['status'] == 1) {
                $idOrder = $response['orderid'];
                $responseAmount = (int) $response['payamount'];
                $responseCurrency = $response['paycurrency'];

                $order = new Order($idOrder);
                if (!Validate::isLoadedObject($order) ||
                    $order->getCurrentState() != $payseraOrderState
                ) {
                    exit('OK');
                }

                $orderAmount = (int) $order->getOrdersTotalPaid() * 100;
                $orderCurrency = Currency::getCurrency($order->id_currency);

                if ($responseAmount < $orderAmount) {
                    exit(sprintf('Bad amount: %s', $responseAmount));
                }

                if ($responseCurrency != $orderCurrency['iso_code']) {
                    exit(sprintf('Bad currency: %s', $responseCurrency));
                }

                $history = new OrderHistory();
                $history->id_order = $order->id;
                $history->changeIdOrderState($paymentAcceptedOrderStateID, $order->id);
                $history->addWithemail();

                exit('OK');
            }
        } catch (Exception $e) {
            exit($e->getMessage());
        }

        exit('Not paid');
    }
}
