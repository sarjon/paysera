<section id="payseraAdditionalInformation">

    <div class="form-group row">
        <div class="col-sm-12 form-control-label clearfix">
            <label class="float-left">
                {l s='Select payment country' mod='paysera'}
            </label>
        </div>
        <div class="col-sm-6">
            <select class="form-control form-control-select" title="{l s='Payment country' mod='paysera'}">
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

    <hr>

    {foreach $payMethods as $country}
        <fieldset id="payseraPaymentMethods_{$country->getCode()}" class="form-group row" {if $country->getCode() != $defaultCountry}style="display:none"{/if}>
            {foreach $country->getGroups() as $group}
                <legend class="col-form-legend col-sm-12">{$group->getTitle()}</legend>
                <div class="col-sm-12">
                {foreach $group->getPaymentMethods() as $paymentMethod}
                    <div class="form-check">
                        <label class="form-check-label">
                            <input class="form-check-input"
                                   type="radio"
                                   name="paysera_payment_method"
                                   value="{$paymentMethod->getKey()}"
                                   style="height: 80%;"
                            >
                            <img src="{$paymentMethod->getLogoUrl()}"
                                 title="{$paymentMethod->getTitle()}"
                                 alt="{$paymentMethod->getTitle()}"
                            >
                        </label>
                    </div>
                {/foreach}
                </div>
            {/foreach}
        </fieldset>
    {/foreach}
</section>
