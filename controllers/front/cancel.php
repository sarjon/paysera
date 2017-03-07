<?php

class PayseraCancelModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        Tools::redirect($this->context->link->getPageLink('order'));
    }
}
