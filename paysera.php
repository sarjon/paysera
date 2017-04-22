<?php
/**
 * This file is part of the paysera module.
 *
 * @author    Sarunas Jonusas, https://github.com/sarjon
 * @copyright Copyright (c) Sarunas Jonusas
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class Paysera
 */
class Paysera extends PaymentModule
{
    /**
     * Paysera payment constants
     */
    const PAYMENT_NOT_EXECUTED = 0;
    const PAYMENT_ACCEPTED = 1;
    const PAYMENT_ACCEPTED_NOT_EXECUTED = 2;

    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * Paysera constructor.
     */
    public function __construct()
    {
        $this->name = 'paysera';
        $this->author = 'Šarūnas Jonušas';
        $this->version = '1.0.0';
        $this->tab = 'payments_gateways';
        $this->compatibility = ['min' => '1.7.1.0', 'max' => _PS_VERSION_];
        $this->controllers = ['redirect', 'callback', 'accept', 'cancel'];

        parent::__construct();

        $this->displayName = $this->l('Paysera');
        $this->description = $this->l('Accept payments by Paysera system.');

        $this->autoload();
        $this->buildContainer();
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
        $installer = $this->container->get('paysera.installer');

        return parent::install() && $installer->installl();
    }

    /**
     * Uninstall module
     *
     * @return bool
     */
    public function uninstall()
    {
        $installer = $this->container->get('paysera.installer');

        return $installer->uninstall() && parent::uninstall();
    }

    /**
     * Module tabs
     *
     * @return array
     */
    public function getTabs()
    {
        $installer = $this->container->get('paysera.installer');

        return $installer->getTabs();
    }

    /**
     * Add JS & CSS to front controller
     */
    public function hookActionFrontControllerSetMedia()
    {
        $controller = $this->context->controller->php_self;

        if ('order' == $controller) {
            $displayPaymentMethods = (bool) Configuration::get('PAYSERA_DISPLAY_PAYMENT_METHODS');
            if ($displayPaymentMethods) {
                $this->context->controller->registerJavascript(
                    sha1('modules-paysera-order'),
                    'modules/paysera/views/js/front/payment-methods.js',
                    ['media' => 'all']
                );
            }
        }
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

        $displayPaymentMethods = (bool) Configuration::get('PAYSERA_DISPLAY_PAYMENT_METHODS');
        if ($displayPaymentMethods) {
            $projectID        = Configuration::get('PAYSERA_PROJECT_ID');
            $defaultCountry   = Configuration::get('PAYSERA_DEFAULT_COUNTRY');

            $currencyISO = $this->context->currency->iso_code;
            $amount      = $this->context->cart->getOrderTotal() * 100;
            $langISO     = strtolower($this->context->language->iso_code);
            $langISO     = in_array($langISO, ['lt', 'en', 'ru', 'lv', 'ee', 'et', 'pl', 'bg']) ? $langISO : 'en';

            $methods = WebToPay::getPaymentMethodList($projectID, $currencyISO)
                ->filterForAmount($amount, $currencyISO)
                ->setDefaultLanguage($langISO)
                ->getCountries();

            $this->context->smarty->assign([
                'defaultCountry' => $defaultCountry,
                'payMethods' => $methods,
            ]);

            $additionalInfo = $this->context->smarty->fetch('module:paysera/views/templates/hook/payment-options.tpl');
            $payseraOption->setAdditionalInformation($additionalInfo);
            $payseraOption->setInputs([
                'paysera_payment_method' => [
                    'name' => 'paysera_payment_method',
                    'type' => 'hidden',
                    'value' => '',
                ],
            ]);
        }

        return [$payseraOption];
    }

    public function hookPaymentReturn(array $params)
    {
        //@todo: implement
    }

    /**
     * Check if module supports cart currency
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
     * Build module service contaienr
     */
    private function buildContainer()
    {
        $this->container = new ContainerBuilder();
        $this->container->addCompilerPass(new LegacyCompilerPass());
        $this->container->set('paysera.module', $this);

        $locator = new FileLocator($this->getLocalPath().'config');
        $loader  = new YamlFileLoader($this->container, $locator);

        $loader->load('config.yml');

        $this->container->compile();
    }

    /**
     * Require autoloader
     */
    private function autoload()
    {
        require_once __DIR__.'/vendor/autoload.php';
    }
}
