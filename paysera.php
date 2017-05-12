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
     * Since Paysera accepts all prices in cents,
     * we need to multiply each price by defined value.
     *
     * @var int
     */
    const PRICE_MULTIPLIER = 100;

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
        $this->controllers = ['redirect', 'callback', 'accept', 'cancel', 'validation'];

        parent::__construct();

        $this->autoload();
        $this->compile();

        $this->displayName = $this->l('Paysera');
        $this->description = $this->l('Accept payments by Paysera system.');
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
        if (!$this->areCredentialsNonEmpty()) {
            return [];
        }

        $payseraOption = new PaymentOption();
        $payseraOption->setCallToActionText($this->l('Pay by Paysera'));
        $payseraOption->setAction($this->context->link->getModuleLink($this->name, 'validation'));

        $displayPaymentMethods = (bool) Configuration::get('PAYSERA_DISPLAY_PAYMENT_METHODS');
        if ($displayPaymentMethods) {
            $projectID = Configuration::get('PAYSERA_PROJECT_ID');
            $defaultCountry = Configuration::get('PAYSERA_DEFAULT_COUNTRY');
            $supportedLangs = $this->container->getParameter('supported_languages');

            $currencyISO = $this->context->currency->iso_code;
            $amount = $this->context->cart->getOrderTotal() * self::PRICE_MULTIPLIER;
            $langIso = strtolower($this->context->language->iso_code);
            $langIso = in_array($langIso, $supportedLangs) ? $langIso : 'en';

            $methods = WebToPay::getPaymentMethodList($projectID, $currencyISO)
                ->filterForAmount($amount, $currencyISO)
                ->setDefaultLanguage($langIso)
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
        //@todo: implement or remove
    }

    /**
     * Check if module supports cart currency
     *
     * @return bool
     */
    public function checkCurrency()
    {
        $idCurrentCurrency = $this->context->cart->id_currency;

        $currency = new Currency($idCurrentCurrency);
        $moduleCurrencies = $this->getCurrency($idCurrentCurrency);

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
     * Check if merchant has configured it's credentials
     *
     * @return bool
     */
    protected function areCredentialsNonEmpty()
    {
        $projectID = Configuration::get('PAYSERA_PROJECT_ID');
        $projectPassword = Configuration::get('PAYSERA_PROJECT_PASSWORD');

        return !empty($projectID) && !empty($projectPassword);
    }

    /**
     * Build module service contaienr
     */
    private function compile()
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
        require_once $this->getLocalPath().'vendor/autoload.php';
    }
}
