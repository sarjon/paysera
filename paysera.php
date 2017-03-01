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
            'hookPaymentOptions',
            'hookPaymentReturn',
        ];

        return parent::install() && $this->registerHook($hooks);
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
                'class_name' => 'AdminPayseConfiguration',
                'icon' => 'payment',
            ],
        ];

        return $tabs;
    }
}
