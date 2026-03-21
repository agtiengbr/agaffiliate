<?php
require_once _PS_MODULE_DIR_ . 'agcliente/lib/AgModule.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

class BaseAgAffiliate extends AgModule
{
    protected $config_form = false;
    protected $hooks = [
        "header",
        "backOfficeHeader",
        "actionValidateOrder",
        "displayCustomerAccount"
    ];

    public function __construct()
    {
        $this->name = 'agaffiliate';
        $this->tab = 'advertising_marketing';
        $this->version = '1.0.0';
        $this->author = 'AGTI';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('AgAffiliate');
        $this->description = $this->l('Allow your customers to become affiliate of your shop, increasing your sales.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];

        if(Tools::getIsSet('affiliate_token')){
            $token = explode('/', Tools::getValue('affiliate_token'));
            $this->generateCookie($token[0]);
        }
    }

    public function install()
    {
        Configuration::updateValue('AGAFFILIATE_COOKIE', 10);
        Db::getInstance()->execute('ALTER TABLE ' ._DB_PREFIX_.'orders ADD COLUMN id_agaffiliate BOOLEAN DEFAULT 0 AFTER id_customer');

        return parent::install();
    }

    public function uninstall()
    {
        Configuration::deleteByName('AGAFFILIATE_COOKIE');
        
        foreach (AgAffiliate::getCustomers() as $row){
            if ($token = Configuration::get('AGAFFILIATE_CONTROL_ID_' . $row['id'])) {
                Configuration::deleteByName('AGAFFILIATE_CONTROL_ID_' . $row['id']);
                Configuration::deleteByName('AGAFFILIATE_TOKEN_' . $token);
            }
        }
        Db::getInstance()->execute('ALTER TABLE ' ._DB_PREFIX_.'orders DROP COLUMN id_agaffiliate');

        return parent::uninstall();
    }

    public function getContent()
    {
        if (((bool)Tools::isSubmit('submitAgAffiliateModule')) == true &&
            !empty(Tools::getValue('AGAFFILIATE_COOKIE'))
        ) {
            $this->postProcess();
        }

        $this->context->smarty->assign(_PS_MODULE_DIR_, $this->_path);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $this->renderForm();
    }

    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitAgAffiliateModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getConfigForm()]);
    }

    protected function getConfigForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('Specify the duration of the cookie (in hours).'),
                        'name' => 'AGAFFILIATE_COOKIE',
                        'label' => $this->l('Cookie duration'),
                        'required' => true,
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    protected function getConfigFormValues()
    {
        return [
            'AGAFFILIATE_COOKIE' => Configuration::get('AGAFFILIATE_COOKIE'),
        ];
    }

    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    public function generateCookie($token, $redirect = false)
    {
        $key_token = 'AGAFFILIATE_TOKEN_' . $token;
        $validate_has_key = (Configuration::hasKey($key_token)) ? true : false;
        $get_time_cookie  = (time() + (Configuration::get('AGAFFILIATE_COOKIE') * 3600));

        if($validate_has_key){
            setcookie('agaffiliate_id', Configuration::get($key_token), $get_time_cookie, '/');
        }
        if($redirect == true){
            Tools::redirect('index.php');
        }
    }
    
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

    public function hookHeader()
    {
        $id = (int) $this->context->cookie->id_customer;
        $control_id = (bool) Configuration::hasKey("AGAFFILIATE_CONTROL_ID_{$id}");

        $translate = [
            'info'    => $this->l('You can share this link in your social media, blog or other channel. Every time this link is clicked and an order is finished you will earn a comission.'),
            'error'   => $this->l('There was an error copying the link!'),
            'success' => $this->l('Link copied successfully!'),
            'label'   => $this->l('label'),
            'button'  => $this->l('Copy Link')
        ];

        Media::addJsDef([
            'agaffiliate_config'=> $control_id ? true : false,
            'affiliate_token'   => ($control_id) ? Configuration::get("AGAFFILIATE_CONTROL_ID_{$id}") : 0,
            'agaffiliate_url'   => $this->context->shop->getBaseURL(true),
            'agaffiliate_id'    => (Tools::getIsSet('agaffiliate_id')) ? true : false,
            'agaffiliate_translate' => $translate
        ]);

        $this->context->controller->addJquery();
        $this->context->controller->addJS($this->_path . 'views/js/front.js');
        $this->context->controller->addCSS($this->_path . 'views/css/front.css');
    }

    public function hookActionValidateOrder($params)
    {
        $cart = $params['cart'];
        $order_status = $params['orderStatus'];
        $order = $params['order'];

        if(Validate::isLoadedObject($order)){
            $agaffiliate_id = (isset($_COOKIE['agaffiliate_id'])) ? (int) $_COOKIE['agaffiliate_id'] : 0;
            $order_id = (int) $order->id;
            
            Db::getInstance()->execute('UPDATE ' ._DB_PREFIX_."orders SET id_agaffiliate={$agaffiliate_id} WHERE id_order={$order_id}");
        }
    }

    public function hookDisplayCustomerAccount($params)
    {
        $id = (int) $this->context->cookie->id_customer;
        $control_id = (bool) Configuration::hasKey("AGAFFILIATE_CONTROL_ID_{$id}");
        $link = $control_id ? ['agaffiliate_id' => $id] : ['validation' => 1];
        
        $this->context->smarty->assign([
            'agaffiliate_config'=> $control_id ? true : false,
            'agaffiliate_link'  => $this->context->link->getModuleLink('agaffiliate', 'afiliado', $link),
            'agaffiliate_id'    => $id
        ]);

        return $this->display(__FILE__, 'customer-account.tpl');
    }

    protected static function getCustomers()
    {
        return Db::getInstance()->ExecuteS(
            "SELECT c.id_customer as id FROM ". _DB_PREFIX_ . "orders AS o
             INNER JOIN ". _DB_PREFIX_ ."customer AS c ON o.id_customer = c.id_customer
             GROUP BY c.id_customer"
        );
    }
}