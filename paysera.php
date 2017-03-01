<?php

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

class Paysera extends PaymentModule
{
    public function __construct()
    {
        $this->name = 'paysera';
        $this->author = 'Šarūnas Jonušas';
        $this->version = '1.0.0';
        $this->compatibility = ['min' => '1.7.0.0', 'max' => _PS_VERSION_];
        $this->controllers = ['redirect', 'callback', 'accept', 'cancel'];

        parent::__construct();

        $this->displayName = $this->l('Paysera');
        $this->description = $this->l('Accept payments by Paysera system');
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

        return parent::install() && $this->registerHook($hooks);
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

        return parent::uninstall();
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
                'icon' => 'payment',
            ],
        ];

        return $tabs;
    }

    /**
     * Get module payment options
     *
     * @param array $params
     *
     * @return array|PaymentOption[]
     */
    public function hookPaymentOptions(array $params)
    {
        $payseraOption = new PaymentOption();
        $payseraOption->setCallToActionText($this->l('Pay by Paysera'));
        $payseraOption->setAction($this->context->link->getModuleLink($this->name, 'redirect'));
        $payseraOption->setAdditionalInformation($this->l('Order process will be faster'));

        return [$payseraOption];
    }

    public function hookPaymentReturn(array $params)
    {

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
        ];
    }
}
