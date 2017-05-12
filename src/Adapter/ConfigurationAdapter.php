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

namespace Sarjon\Paysera\Adapter;

/**
 * Class ConfigurationAdapter
 *
 * @package Sarjon\Adapter
 */
class ConfigurationAdapter
{
    /**
     * Set configuration value
     *
     * @param string $key
     * @param string|int $value
     *
     * @return bool
     */
    public function set($key, $value)
    {
        return \Configuration::updateValue($key, $value);
    }

    /**
     * Get configuration value
     *
     * @param string $key
     *
     * @return string
     */
    public function get($key)
    {
        return \Configuration::get($key);
    }

    /**
     * Delete configuration by name
     *
     * @param string $key
     *
     * @return bool
     */
    public function remove($key)
    {
        return \Configuration::deleteByName($key);
    }
}
