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

/**
 * Class AdminPayseraConfigurationController
 */
class AdminPayseraConfigurationController extends ModuleAdminController
{
    /**
     * @var bool Use bootstrap in admin page
     */
    public $bootstrap = true;

    /**
     * @var Paysera
     */
    public $module;

    /**
     * Initialize controller with options
     */
    public function init()
    {
        $this->initOptions();

        parent::init();
    }

    /**
     * Add custom content
     */
    public function initContent()
    {
        $moduleCurrencies = Currency::checkPaymentCurrencies($this->module->id);
        if (!count($moduleCurrencies)) {
            $this->warnings[] = $this->l('No currencies configured for this module.');
        }

        $testingMode = (bool) Configuration::get('PAYSERA_TEST_MODE');
        if ($testingMode) {
            $this->warnings[] = $this->l('Module is in testing mode.');
        }

        parent::initContent();
    }

    /**
     * Define configuration options
     */
    protected function initOptions()
    {
        $this->fields_options = [
            'paysera_configuration' => [
                'title' => $this->l('Paysera configuration'),
                'fields' => [
                    'PAYSERA_PROJECT_ID' => [
                        'title' => $this->l('Paysera Project ID'),
                        'type' => 'text',
                        'validation' => 'isUnsignedInt',
                        'class' => 'fixed-width-xxl',
                    ],
                    'PAYSERA_PROJECT_PASSWORD' => [
                        'title' => $this->l('Paysera Project password'),
                        'type' => 'text',
                        'validation' => 'isString',
                        'class' => 'fixed-width-xxl',
                    ],
                    'PAYSERA_DISPLAY_PAYMENT_METHODS' => [
                        'title' => $this->l('Display payment methods'),
                        'validation' => 'isBool',
                        'type' => 'bool',
                        'cast' => 'intval',
                        'class' => 'fixed-width-xxl',
                    ],
                    'PAYSERA_DEFAULT_COUNTRY' => [
                        'title' => $this->l('Default payment country'),
                        'type' => 'select',
                        'class' => 'fixed-width-xxl',
                        'list' => $this->getCountries(),
                        'identifier' => 'id',
                    ],
                    'PAYSERA_TEST_MODE' => [
                        'title' => $this->l('Testing mode'),
                        'validation' => 'isBool',
                        'type' => 'bool',
                        'cast' => 'intval',
                        'class' => 'fixed-width-xxl',
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
            'paysera_verification_configuration' => [
                'title' => $this->l('Website ownership verification'),
                'fields' => [
                    'PAYSERA_INCLUDE_VERIFICATION' => [
                        'title' => $this->l('Include verification content'),
                        'validation' => 'isBool',
                        'type' => 'bool',
                        'cast' => 'intval',
                        'class' => 'fixed-width-xxl',
                    ],
                    'PAYSERA_VERIFICATION_CODE' => [
                        'title' => $this->l('Verification code'),
                        'hint' => $this->l('Your website verification code'),
                        'desc' => sprintf($this->l('Example code: %s'), '00adfe1398d874f6f945aee15501d0f1'),
                        'type' => 'text',
                        'class' => 'fixed-width-xxl',
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
            'paysera_additional_configuration' => [
                'title' => $this->l('Paysera additional configuration'),
                'fields' => [
                    'PAYSERA_DISPLAY_WIDGET' => [
                        'title' => $this->l('Display Paysera sign of quality'),
                        'hint' => $this->l('Display Paysera sign of quality in Front Office pages.'),
                        'validation' => 'isBool',
                        'type' => 'bool',
                        'cast' => 'intval',
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    private function getCountries()
    {
        $countries = [];
        $projectID = (string) Configuration::get('PAYSERA_PROJECT_ID');

        if (!$projectID) {
            return $countries;
        }

        $methods = WebToPay::getPaymentMethodList($projectID)
            ->setDefaultLanguage($this->context->language->iso_code)
            ->getCountries();

        foreach ($methods as $method) {
            $countries[] = [
                'id' => $method->getCode(),
                'name' => $method->getTitle(),
            ];
        }

        return $countries;
    }
}
