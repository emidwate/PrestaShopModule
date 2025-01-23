<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */


if (!defined('_PS_VERSION_')) {
    exit;
}

class Coupon extends Module
{
    public function __construct()
    {
        $this -> name = 'coupon';
        $this -> tab = 'pricing_promotion';
        $this -> version = '1.0';
        $this -> author = 'PrestaShop';
        $this -> need_instance = 0;
        $this -> ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '8.99.99'];
        $this -> bootstrap = true;

        parent::__construct();

        $this -> displayName = $this -> trans('Coupon', [], 'Modules.Coupon.Admin');
        $this -> description = $this -> trans(
            'Design a compelling discount popup to drive sales!', 
            [],
            'Modules.Coupon.Admin'
        );
        
        $this -> confirmUninstall = $this -> trans('Are you sure you want to uninstall ?', [], 'Modules.Coupon.Admin');
    }
    
    public function install()
    {

        return (
            parent::install() 
            && $this -> registerHook('actionFrontControllerSetMedia') 
            && $this -> registerHook('displayAfterBodyOpeningTag')
        );
    } 

    public function uninstall()
    {
        return (
            parent::uninstall() 
            && $this -> unregisterHook('actionFrontControllerSetMedia') 
            && $this -> unregisterHook('displayAfterBodyOpeningTag') 
            && Configuration::deleteByName('COUPON_CUSTOM_MESSAGE') 
            && Configuration::deleteByName('COUPON_BACKGROUND_COLOR') 
            && Configuration::deleteByName('COUPON_CART_RULE_ID') 
            && Configuration::deleteByName('COUPON_DISCOUNT_PERCENT') 
            && Configuration::deleteByName('COUPON_DISCOUNT_AMOUNT')
            && Configuration::deleteByName('COUPON_DISPLAY_DURATION')
            && Configuration::deleteByName('COUPON_INTERVAL_DURATION')
        );
    }

    public function hookDisplayAfterBodyOpeningTag()
    {
        $userSelectedDiscount = $this->getUserSelectedDiscountData();

        $this->context->smarty->assign([
            'customSellerMessage' => (string) Configuration::get('COUPON_CUSTOM_MESSAGE'),
            'couponBgColor' => (string) Configuration::get('COUPON_BACKGROUND_COLOR'),
            'discountCode' => $this->extractDiscountCode($userSelectedDiscount),
            'discountExpirationDate' => $this->extractExpirationDate($userSelectedDiscount),
            'discountAmount' => $this->extractDiscountAmount($userSelectedDiscount),
            'moduleDir' => _MODULE_DIR_ . $this->name . '/',
            'displayDuration' => (string) Configuration::get('COUPON_DISPLAY_DURATION'),
            'intervalDuration' => (string) Configuration::get('COUPON_INTERVAL_DURATION')
        ]);
        return $this->fetch('module:'.$this->name.'/views/templates/hook/coupon.tpl');
    }
    
    public function getDiscountData()
    {
        $sqlQuery = 
                'SELECT 
                    c.id_cart_rule, 
                    c.date_to, 
                    c.code, 
                    c.reduction_percent, 
                    c.reduction_amount
                FROM `' . _DB_PREFIX_ .'cart_rule` c;';
        
        $result = Db::getInstance()->executeS($sqlQuery); 
        if ($result) {
            return $result;
        }
        return null;
    }

    public function getUserSelectedDiscountData()
    {
        $discountData = $this -> getDiscountData();
        $userSelecteCartRuleID = (string) Configuration::get('COUPON_CART_RULE_ID');

        if(!empty($discountData)){
            foreach ($discountData as $selectedDiscount) {
                if ($selectedDiscount['id_cart_rule'] == $userSelecteCartRuleID) {
                    return $selectedDiscount;
                }
            }
        }
    }

    public function extractDiscountCode($data)
    {
        if (!empty($data) && $data['code'] != ""){
            return $data['code'];
        }
        return 'No discount code found!';
    }
    
    public function extractExpirationDate($data)
    {
        if(!empty($data)) {
            return $data['date_to'];
        }
        return 'No expiration date found!';
    }
    
    public function extractDiscountAmount($data)
    {
        if(!empty($data)){
            $reductionPercent = $data['reduction_percent'];  
            $reductionAmount = $data['reduction_amount'];
    
            Configuration::updateValue('COUPON_DISCOUNT_AMOUNT', $reductionAmount);
            Configuration::updateValue('COUPON_DISCOUNT_PERCENT', $reductionPercent);
    
            if(!empty(intval(Configuration::get('COUPON_DISCOUNT_PERCENT')))) {
                return Configuration::get('COUPON_DISCOUNT_PERCENT') . '%';
            } else {
                return number_format(Configuration::get('COUPON_DISCOUNT_AMOUNT'),2) . 'PLN';
            }
        }
    }

    public function hookActionFrontControllerSetMedia($params) 
    { 
        $this->context->controller->registerStylesheet( 
            'module-'.$this->name.'-style', 
            'modules/'.$this->name.'/views/css/coupon.css'
        ); 

        $this->context->controller->registerJavascript(
            'module-'.$this->name.'-js',
            'modules/'.$this->name.'/views/js/coupon.js'
        );
    }
    
    public function isUsingNewTranslationSystem()
    {
        return true;
    }    

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submit' . $this->name)) {
            $customSellerMessage = (string) Tools::getValue('COUPON_CUSTOM_MESSAGE'); 
            $couponBgColor = (string) Tools::getValue('COUPON_BACKGROUND_COLOR'); 
            $couponCartRuleID = (string) Tools::getValue('COUPON_CART_RULE_ID');
            $displayDuration = (string) Tools::getValue('COUPON_DISPLAY_DURATION');
            $intervalDuration = (string) Tools::getValue('COUPON_INTERVAL_DURATION');

            if (
                    !Validate::isGenericName($customSellerMessage)
                    || !Validate::isGenericName($displayDuration) 
                    || !Validate::isGenericName($intervalDuration) 
                    || !Validate::isGenericName($couponBgColor) 
                    || !Validate::isGenericName($couponCartRuleID)
                    || empty($couponCartRuleID) 
                ) {
                    $output = $this->displayError($this->trans('Invalid configuration value.', [], 'Modules.Coupon.Admin'));
            } else {
                Configuration::updateValue('COUPON_CUSTOM_MESSAGE', $customSellerMessage); 
                Configuration::updateValue('COUPON_BACKGROUND_COLOR', $couponBgColor);
                Configuration::updateValue('COUPON_CART_RULE_ID', $couponCartRuleID);
                Configuration::updateValue('COUPON_DISPLAY_DURATION', $displayDuration);
                Configuration::updateValue('COUPON_INTERVAL_DURATION', $intervalDuration);
                $output = $this->displayConfirmation($this->trans('Settings updated.', [], 'Modules.Coupon.Admin'));
            }
        }
        return $output . $this->displayForm();
    }

    public function displayForm()
    {
        $form = [
            'form' => [
                'legend' => [
                    'title' => $this->trans('Settings', [], 'Modules.Coupon.Admin'),
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->trans('Coupon content', [], 'Modules.Coupon.Admin'),
                        'name' => 'COUPON_CUSTOM_MESSAGE',
                        'size' => 20,
                        'required' => false,
                        'placeholder' => $this->trans('Additional message you want to convey to your customers.', [], 'Modules.Coupon.Admin')
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->trans('Cart rule ID', [], 'Modules.Coupon.Admin'),
                        'name' => 'COUPON_CART_RULE_ID',
                        'size' => 20,
                        'required' => true
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->trans('Coupon display duration to customer in seconds.', [], 'Modules.Coupon.Admin'),
                        'name' => 'COUPON_DISPLAY_DURATION',
                        'size' => 20,
                        'required' => false,
                        'placeholder' => $this->trans('Default is 15 seconds.', [], 'Modules.Coupon.Admin')
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->trans('Pause between each coupon display in seconds.', [], 'Modules.Coupon.Admin'),
                        'name' => 'COUPON_INTERVAL_DURATION',
                        'size' => 20,
                        'required' => false,
                        'placeholder' => $this->trans('Default is 35 seconds.', [], 'Modules.Coupon.Admin')
                    ],
                    [
                        'type' => 'color',
                        'label' => $this->trans('Coupon background color.', [], 'Modules.Coupon.Admin'),
                        'name' => 'COUPON_BACKGROUND_COLOR',
                        'size' => 20,
                        'required' => false
                    ]
                ],
                'submit' => [
                    'title' => $this->trans('Save', [], 'Modules.Coupon.Admin'),
                    'class' => 'btn btn-default pull-right',
                ]
            ],
        ];

        $helper = new HelperForm();

        $helper->table = $this->table;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&' . http_build_query(['configure' => $this->name]);
        $helper->submit_action = 'submit' . $this->name;

        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        
        $fieldsNameArray = [
            'COUPON_CUSTOM_MESSAGE',
            'COUPON_BACKGROUND_COLOR',
            'COUPON_CART_RULE_ID',
            'COUPON_DISPLAY_DURATION',
            'COUPON_INTERVAL_DURATION'
        ];

        foreach ($fieldsNameArray as $fieldName) {
            $helper->fields_value[$fieldName] = Tools::getValue($fieldName, Configuration::get($fieldName));
        }

        return $helper->generateForm([$form]);
    }
}