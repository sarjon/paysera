<?php

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
        ];
    }
}
