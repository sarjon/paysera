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

class PayseraCancelModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        Tools::redirect($this->context->link->getPageLink('order'));
    }
}