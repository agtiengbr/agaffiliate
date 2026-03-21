{if ($agaffiliate_config == false)}
    <a class="col-lg-4 col-md-6 col-sm-6 col-xs-12" title="{l s='Become an Affiliate' mod='agaffiliate'}" href="{$agaffiliate_link|escape:'html':'UTF-8'}">
        <span class="link-item">
            <i class="material-icons">share</i>
            {l s='Become an Affiliate' mod='agaffiliate'}
        </span>
    </a>
{/if}

{if ($agaffiliate_config == true)}
    <a class="col-lg-4 col-md-6 col-sm-6 col-xs-12" title="{l s='Affiliate Area' mod='agaffiliate'}" href="{$agaffiliate_link|escape:'html':'UTF-8'}">
        <span class="link-item">
            <i class="material-icons">share</i>
            {l s='Affiliate Area' mod='agaffiliate'}
        </span>
    </a>
{/if}