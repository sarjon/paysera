{**
* This file is part of the paysera module.
*
* @author    Šarūnas Jonušas, https://github.com/sarjon
* @copyright Copyright (c) Šarūnas Jonušas
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}

<section id="payseraAdditionalInformation">
    <div class="form-group row">
        <div class="col-sm-12 form-control-label clearfix">
            <label class="float-left">
                {l s='Select payment country' mod='paysera'}
            </label>
        </div>
        <div class="col-sm-6">
            <select class="form-control form-control-select js-paysera-payment-country" title="{l s='Payment country' mod='paysera'}">
                {foreach $payMethods as $country}
                    <option value="{$country->getCode()}"
                            {if $country->getCode() == $defaultCountry} selected="selected" {/if}
                    >
                        {$country->getTitle()}
                    </option>
                {/foreach}
            </select>
        </div>
    </div>

    {foreach $payMethods as $country}
        <fieldset id="payseraPaymentMethods_{$country->getCode()}" class="form-group row js-paysera-payment-methods" {if $country->getCode() != $defaultCountry}style="display:none"{/if}>
            {foreach $country->getGroups() as $group}
                <legend class="col-form-legend col-sm-12">{$group->getTitle()}</legend>
                {foreach $group->getPaymentMethods() as $paymentMethod}
                    <div class="col-sm-12 col-md-6">
                        <div class="form-check">
                            <label class="form-check-label">
                                <input class="form-check-input js-paysera-payment-method"
                                       type="radio"
                                       name="paysera_payment_method_input"
                                       value="{$paymentMethod->getKey()}"
                                       style="height: 80%;"
                                >
                                <img src="{$paymentMethod->getLogoUrl()}"
                                     title="{$paymentMethod->getTitle()}"
                                     alt="{$paymentMethod->getTitle()}"
                                >
                            </label>
                        </div>
                    </div>
                {/foreach}
            {/foreach}
        </fieldset>
    {/foreach}
</section>
