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

namespace Sarjon\Paysera\Install;

use OrderState;
use Paysera;
use Sarjon\Paysera\Adapter\ConfigurationAdapter;
use Sarjon\Paysera\Adapter\LanguageAdapter;
use Sarjon\Paysera\Exception\PayseraException;

/**
 * Class Installer
 *
 * @package Sarjon\Paysera\Install
 */
class Installer
{
    /**
     * @var Paysera
     */
    private $module;

    /**
     * @var array
     */
    private $moduleSettings;

    /**
     * @var ConfigurationAdapter
     */
    private $configurationAdapter;

    /**
     * @var LanguageAdapter
     */
    private $languageAdapter;

    /**
     * Installer constructor.
     *
     * @param Paysera $module
     * @param ConfigurationAdapter $configurationAdapter
     * @param array $moduleSettings
     */
    public function __construct(Paysera $module, ConfigurationAdapter $configurationAdapter, LanguageAdapter $languageAdapter, array $moduleSettings)
    {
        $this->module = $module;
        $this->moduleSettings = $moduleSettings;
        $this->configurationAdapter = $configurationAdapter;
        $this->languageAdapter = $languageAdapter;
    }

    /**
     * Install module
     *
     * @return bool
     */
    public function installl()
    {
        if (!$this->registerHooks()) {
            throw new PayseraException('Failed to register Paysera module hooks.');
        }

        if (!$this->installConfiguration()) {
            throw new PayseraException('Failed to install Paysera module configuration.');
        }

        if (!$this->installOrderStates()) {
            throw new PayseraException('Failed to install Paysera order states.');
        }

        return true;
    }

    /**
     * Uninstall module
     *
     * @return bool
     */
    public function uninstall()
    {
        if (!$this->uninstallOrderStates()) {
            throw new PayseraException('Failed to uninstall Paysera order states.');
        }

        if (!$this->uninstallConfiguration()) {
            throw new PayseraException('Failed to uninstall Paysera module configuration.');
        }

        return true;
    }

    /**
     * Get module tabs to be installed
     *
     * @return array
     */
    public function getTabs()
    {
        return $this->moduleSettings['tabs'];
    }

    /**
     * Register module hooks
     *
     * @return bool
     */
    protected function registerHooks()
    {
        return $this->module->registerHook($this->moduleSettings['hooks']);
    }

    /**
     * Install default module configuration
     *
     * @return bool
     */
    protected function installConfiguration()
    {
        $defaultConfiguration = $this->moduleSettings['configuration'];

        foreach ($defaultConfiguration as $name => $value) {
            // skip order state configuration
            // since those will be saved after order states are created
            if (strpos($name, 'ORDER_STATE') !== false) {
                continue;
            }

            if (!$this->configurationAdapter->set($name, $value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Uninstall module configuration
     *
     * @return bool
     */
    protected function uninstallConfiguration()
    {
        $configurationNames = array_keys($this->moduleSettings['configuration']);

        foreach ($configurationNames as $name) {
            if (!$this->configurationAdapter->remove($name)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Install Paysera order states
     *
     * @return bool
     */
    protected function installOrderStates()
    {
        $orderStates = $this->moduleSettings['order_states'];
        $idLangs = $this->languageAdapter->getIDs();

        foreach ($orderStates as $state) {
            $orderState = new OrderState();
            $orderState->color = $state['color'];
            $orderState->paid = $state['paid'];
            $orderState->invoice = $state['paid'];
            $orderState->module_name = $this->module->name;
            $orderState->unremovable = 0;

            foreach ($idLangs as $idLang) {
                $orderState->name[$idLang] = $state['name'];
            }

            if (!$orderState->save()) {
                return false;
            }

            $this->configurationAdapter->set($state['config'], $orderState->id);
        }

        return true;
    }

    /**
     * Uninstall order states
     *
     * @return bool
     */
    protected function uninstallOrderStates()
    {
        $orderStates = $this->moduleSettings['order_states'];

        foreach ($orderStates as $state) {
            $idOrderState = $this->configurationAdapter->get($state['config']);
            $orderState = new OrderState($idOrderState);
            $orderState->deleted = 1;

            if (!$orderState->save()) {
                return false;
            }
        }

        return true;
    }
}
