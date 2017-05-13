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
 * Class LanguageAdapter
 *
 * @package Sarjon\Paysera\Adapter
 */
class LanguageAdapter
{
    /**
     * Get array of language IDs
     *
     * @param bool $active
     * @param int|bool $idShop
     *
     * @return array|int[]
     */
    public function getIDs($active = true, $idShop = false)
    {
        return \Language::getIDs($active, $idShop);
    }
}
