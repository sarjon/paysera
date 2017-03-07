<?php

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

class Paysera extends PaymentModule
{
    public function __construct()
    {
        $this->name = 'paysera';
        $this->author = 'Šarūnas Jonušas';
        $this->version = '1.0.0';
        $this->tab = 'payments_gateways';
        $this->compatibility = ['min' => '1.7.0.0', 'max' => _PS_VERSION_];
        $this->controllers = ['redirect', 'validation', 'accept', 'cancel'];

        parent::__construct();

        $this->displayName = $this->l('Paysera');
        $this->description = $this->l('Accept payments by Paysera system');

        $this->autoload();
    }

    /**
     * Redirect to configuration controller
     */
    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminPayseraConfiguration'));
    }

    /**
     * Install module
     *
     * @return bool
     */
    public function install()
    {
        $hooks = [
            'paymentOptions',
            'paymentReturn',
        ];

        $defaultConfiguration = $this->getDefaultConfiguration();

        foreach ($defaultConfiguration as $name => $value) {
            Configuration::updateValue($name, $value);
        }

        return parent::install() && $this->registerHook($hooks) && $this->installOrderState();
    }

    /**
     * Uninstall module
     *
     * @return bool
     */
    public function uninstall()
    {
        $defaultConfiguration = $this->getDefaultConfiguration();

        foreach (array_keys($defaultConfiguration) as $name) {
            Configuration::deleteByName($name);
        }

        return $this->uninstallOrderState() && parent::uninstall();
    }

    /**
     * Module tabs
     *
     * @return array
     */
    public function getTabs()
    {
        $tabs = [
            [
                'name' => $this->l('Paysera'),
                'class_name' => 'AdminPayseraConfiguration',
                'ParentClassName' => 'AdminParentPayment',
            ],
        ];

        return $tabs;
    }

    /**
     * Get module payment options
     *
     * @return array|PaymentOption[]
     */
    public function hookPaymentOptions()
    {
        $payseraOption = new PaymentOption();
        $payseraOption->setCallToActionText($this->l('Pay by Paysera'));
        $payseraOption->setAction($this->context->link->getModuleLink($this->name, 'redirect'));

        $displayPaymentList = (bool) Configuration::get('PAYSERA_DISPLAY_PAYMENT_LIST');
        if ($displayPaymentList) {
            $projectID   = Configuration::get('PAYSERA_PROJECT_ID');
            $currencyISO = $this->context->currency->iso_code;
            $amount      = $this->context->cart->getOrderTotal() * 100;
            $langISO     = strtolower($this->context->language->iso_code);
            $langISO     = in_array($langISO, ['lt', 'en', 'ru', 'lv', 'ee', 'et', 'pl', 'bg']) ? $langISO : 'en';

            $methods = WebToPay::getPaymentMethodList($projectID, $currencyISO)
                ->filterForAmount($amount, $currencyISO)
                ->setDefaultLanguage($langISO)
                ->getCountries();

            $this->context->smarty->assign([
                'defaultCountry' => Configuration::get('PAYSERA_DEFAULT_COUNTRY'),
                'payMethods' => $methods,
            ]);

            $additionalInfo = $this->context->smarty->fetch('module:paysera/views/templates/hook/payment-options.tpl');
            $payseraOption->setAdditionalInformation($additionalInfo);
        }

        return [$payseraOption];
    }

    public function hookPaymentReturn(array $params)
    {
        //@todo: implement
    }

    /**
     * Check if module support cart currency
     *
     * @return bool
     */
    public function checkCurrency()
    {
        $idCurrency = $this->context->cart->id_currency;

        $currency = new Currency($idCurrency);
        $moduleCurrencies = $this->getCurrency($idCurrency);

        if (is_array($moduleCurrencies)) {
            foreach ($moduleCurrencies as $moduleCurrency) {
                if ($currency->id == $moduleCurrency['id_currency']) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Module default configuration
     *
     * @return array
     */
    protected function getDefaultConfiguration()
    {
        return [
            'PAYSERA_PROJECT_ID' => '',
            'PAYSERA_PROJECT_PASSWORD' => '',
            'PAYSERA_TESTING_MODE' => 1,
            'PAYSERA_DISPLAY_PAYMENT_LIST' => 1,
            'PAYSERA_DEFAULT_COUNTRY' => 'lt',
        ];
    }

    /**
     * Install paysera order state
     *
     * @return bool
     */
    protected function installOrderState()
    {
        $orderState = new OrderState();
        $orderState->color = '#206f9f';
        $orderState->module_name = $this->name;
        $orderState->unremovable = 0;

        foreach (Language::getLanguages(true, false, true) as $idLang) {
            $orderState->name[$idLang] = 'Awaiting Paysera payment';
        }

        if (!$orderState->save()) {
            return false;
        }

        Configuration::updateValue('PAYSERA_ORDER_STATE_ID', $orderState->id);

        return true;
    }

    /**
     * Uninstall paysera order state
     *
     * @return bool
     */
    protected function uninstallOrderState()
    {
        $idOrderState = (int) Configuration::get('PAYSERA_ORDER_STATE_ID');
        $orderState = new OrderState($idOrderState);

        if (!Validate::isLoadedObject($orderState)) {
            return true;
        }

        return $orderState->delete();
    }

    /**
     * Require autoloader
     */
    private function autoload()
    {
        require_once __DIR__.'/vendor/autoload.php';
    }
}
