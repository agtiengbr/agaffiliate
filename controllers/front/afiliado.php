<?php
class AgAffiliateAfiliadoModuleFrontController extends ModuleFrontController
{
    public  $ssl = true;
    private $id;
    private $configuration;
    
    public function initContent()
    {
        parent::initContent();

        $this->id = (int) $this->context->cookie->id_customer;
        $this->configuration = "AGAFFILIATE_CONTROL_ID_{$this->id}";

        if(Tools::getIsSet('token')){
            $this->module->generateCookie(Tools::getValue('token'), true);
        }

        if(Tools::getIsSet('validation') && Tools::getValue('validation') == 1){
            $this->generateToken();
        }

        if(Tools::getIsSet('agaffiliate_id') && Configuration::hasKey($this->configuration)){
            $this->isAffiliate();
        }else{
            Tools::redirect($this->context->link->getPageLink('my-account', true));
        }
    }

    protected function generateToken()
    {
        $token = substr(md5(sha1($this->id . uniqid())), -8);

        Configuration::updateValue('AGAFFILIATE_TOKEN_' . $token, $this->id);
        Configuration::updateValue($this->configuration, $token);
        Tools::redirect($this->context->link->getModuleLink('agaffiliate', 'afiliado', ['agaffiliate_id' => $this->id]));
    }

    protected function isAffiliate()
    {   
        $this->context->smarty->assign([
            'affiliate_link' => $this->context->link->getModuleLink('agaffiliate', 'afiliado', ['token' => Configuration::get($this->configuration)])
        ]);

        $this->setTemplate('module:agaffiliate/views/templates/front/account.tpl');
    }
}