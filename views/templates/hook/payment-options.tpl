<table>
    <tr>
        <td style="width:210px">{l s='Select payment country' mod='paysera'}</td>
        <td>
            <select class="payment-country-select">
                {foreach from=$payMethods item=country}
                    <option {if $country->getCode() == $defaultCountry} selected="selected" {/if}
                            value="{$country->getCode()|escape:'quotes'}">{$country->getTitle()}</option>
                {/foreach}
            </select>
        </td>
    </tr>
</table>

<div id="payment-content">
    <div class="payment-methods-wrapper">
        {foreach from=$payMethods item=country}
            <div id="{$country->getCode()|escape:'quotes'}" class="payment-countries"
                 style="display:{if $country->getCode() == $defaultCountry}table{else}none{/if};">
                {foreach from=$country->getGroups() item=group}
                    <div class="payment-group-wrapper">
                        <div class="payment-group-title">{$group->getTitle()|escape:'quotes'}</div>
                        {foreach from=$group->getPaymentMethods() item=paymentMethod}
                            <div class="payment-item">
                                <input type="radio" class="radio" name="payment_method"
                                       value="{$paymentMethod->getKey()|escape:'quotes'}" class="payment-radio"/>
                                <img src="{$paymentMethod->getLogoUrl()|escape:'quotes'}" title="{$paymentMethod->getTitle()}"
                                     alt="{$paymentMethod->getTitle()|escape:'quotes'}" class="payment-logo"/>

                                <div class="clear"></div>
                            </div>
                        {/foreach}
                        <div class="clear"></div>
                    </div>
                {/foreach}
            </div>
        {/foreach}
    </div>
</div>
