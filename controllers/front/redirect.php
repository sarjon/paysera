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
        //@todo: perform validations

        $paymentData = $this->collectPaymentData();

        if (null === $paymentData) {
            //@todo: do something when incorrect data
        }

        //@todo: redirect to payment
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

        //@todo: create order
        //$order = $this->module->validateOrder();

        //@todo: replace demo data
        $data = [
            'projectid'     => $projectID,
            'sign_password' => $projectPassword,
            'orderid'       => 0,
            'amount'        => 1000,
            'currency'      => 'EUR',
            'country'       => 'LT',
            'accepturl'     => $this->context->link->getModuleLink($this->module->name, 'accept'),
            'cancelurl'     => $this->context->link->getModuleLink($this->module->name, 'cancel'),
            'callbackurl'   => $this->context->link->getModuleLink($this->module->name, 'callback'),
            'test'          => (int) $testingMode,
        ];

        return $data;
    }
}
