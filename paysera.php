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

        $this->displayName = $this->trans('Paysera', [], 'Modules.Paysera.Admin');
    }
}
