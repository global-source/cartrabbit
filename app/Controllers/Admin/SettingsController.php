<?php

namespace CartRabbit\Controllers\Admin;

use Herbert\Framework\Notifier;
use CartRabbit\Controllers\BaseController;
use Herbert\Framework\Http;
use CartRabbit\Helper\Tax;
use CartRabbit\Helper\Util;
use CartRabbit\Models\Admin;
use CartRabbit\Models\Currency;
use Corcel\Post;
use CommerceGuys\Intl\Currency\CurrencyRepository;
use CommerceGuys\Addressing\Repository\CountryRepository;
use CartRabbit\Models\Customer;
use CartRabbit\Models\Settings;
use CartRabbit\Models\Setup;

/**
 * Class Admin
 * @package CartRabbit\Controllers
 */
class SettingsController extends BaseController
{

    /**
     * To Set Current tab, For Temporary Purpose
     * @var
     */
    private $tabType;

    /**
     * @var array
     */
    private $admin_menu = array();

    /**
     * For Store the Cart Config Status
     * @var array
     */
    private $cartConfigStatus = array();

    /**
     * To Display Cart Configuration
     */

    public function __construct()
    {
        parent::__construct();
        if (!empty(Settings::getStoreConfigID())) {
            $this->cartConfigStatus = Settings::getCartConfigStatus();
        }
    }

    /**
     * To Return the Config Status
     * @return array of Config Status
     */
    public function getCartConfigStatus()
    {
        return $this->cartConfigStatus;
    }


    /**
     * To Setup the Configuration of the Cartrabbit
     * @param $http Http instance for access post data
     * @return \CartRabbit\Controllers\display
     */
    public function cartConfiguration(http $http)
    {
        /** @var $tab To get the Current Cart configuration tab type */
        $tab = ($http->has('tab') ? $http->get('tab') : '');

        /** @var $tab To get the Current Cart configuration option tab type */
        $opt = ($http->has('opt') ? $http->get('opt') : '');

        /** @var $tab To get the Current Cart configuration option tab type */
        $item_id = ($http->has('tax_id') ? $http->get('tax_id') : null);

        /** @var $tab To get the Current Cart configuration option tab type */
        $zone = ($http->has('zone_list') ? $http->get('zone_list') : false);

        /** @var $tab To get the Current Cart configuration option tab type */
        $rate = ($http->has('rate_list') ? $http->get('rate_list') : false);

        /** @var $tab To get the Current Cart configuration option tab type */
        $rate_edit = ($http->has('rate_edit') ? $http->get('rate_edit') : null);

        if ($tab === '') $tab = 'general';

        $this->tabType = $tab;

        /** To Retrieve all Admin Menu Items */
        $this->admin_menu = Settings::getAllSettingsMenuItems();

        if ($this->tabType == 'tax') {
            if ($opt == 'taxoptions') {
                $html = $this->loadTaxConfiguration();
            } elseif ($opt == 'taxes') {
                if (is_null($item_id)) {
                    $html = $this->loadTaxesConfiguration();
                } else {
                    if ($zone) {
                        $html = $this->loadTaxesConfigurationListZone($item_id);
                    } elseif ($rate) {
                        if ($rate_edit) {
                            $html = $this->loadTaxesConfigurationListRateAmounts($item_id, $rate_edit);
                        } else {
                            $html = $this->loadTaxesConfigurationListRate($item_id);
                        }
                    } else {
                        $html = $this->loadTaxesConfigurationFor($item_id);
                    }
                }
            } elseif ($opt == 'tax_classes') {
                $html = $this->loadTaxClassesConfiguration();
            }

        } elseif ($this->tabType == 'product') {
            if ($opt !== '') {
                $html = $this->loadProductConfigurationByType($opt);
            } else {
                $html = $this->productGeneralConfiguration();
            }
        } elseif ($this->tabType == 'cart') {
            $html = $this->loadCartConfiguration();
        } elseif ($this->tabType == 'inventory') {
            $html = $this->loadInventoryConfiguration();
        } elseif ($this->tabType == 'shipping') {
            if ($opt == 'general' or $opt == '') {
                $html = $this->loadShippingConfigurations();
            } else {
                $html = $this->loadShipping($opt);
            }
        } elseif ($this->tabType == 'payment') {
            if ($opt == 'general') {
                $html = $this->loadPaymentConfigurations();
            } else {
                $html = $this->loadPayment($opt);
            }
        } elseif ($this->tabType == 'order') {
            if ($opt == 'general') {
                $html = $this->loadOrderConfigurations();
            }
        } else {
            $html = $this->loadGeneralConfiguration();
        }
        return $html;
    }

    /**
     * To Loads general configuration settings
     * @return \CartRabbit\Controllers\display
     */
    public function loadGeneralConfiguration()
    {
        $tabType = $this->tabType;
        $admin_menu = $this->admin_menu;
        $config = Settings::getCartConfig();

        $secondaryCurrency = (new Currency())->getSecondaryCurrencyMeta();
        $currency = (new CurrencyRepository())->getList();
        $countries = (new CountryRepository())->getList();
        $config['countries'] = json_decode($config['countries']);
        $config['store_states'] = (array)json_decode(Util::getStatesByCountryCode($config['store_country']));
        $configStatus = $this->cartConfigStatus;
        if (!empty($configStatus)) {
            Notifier::error('Please setup the Display Page !');
        }
        $config['pages'] = Settings::getDisplaySetup();

        return parent::view('Admin.Configuration.generalConfig', compact('tabType', 'admin_menu', 'countries', 'currency', 'config', 'secondaryCurrency', 'configStatus'));
    }

    /**
     * @return \CartRabbit\Controllers\display
     */
    public function loadTaxClassesConfiguration()
    {
        $tabType = $this->tabType;
        $admin_menu = $this->admin_menu;
        $taxClasses = (new Settings())->getTaxClasses();
        return parent::view('Admin.Configuration.Tax.TaxClasses', compact('tabType', 'admin_menu', 'taxClasses'));
    }

    /**
     * To Loads Tax configuration settings
     * @return \CartRabbit\Controllers\display
     */
    public function loadTaxConfiguration()
    {
        $tabType = $this->tabType;
        $admin_menu = $this->admin_menu;
        $settings = new Settings();
        $taxOptions = $settings->getTaxConfigurationDatas();
        $configStatus = $this->cartConfigStatus;
        return parent::view('Admin.Configuration.Tax.TaxOptions', compact('tabType', 'taxOptions', 'admin_menu', 'configStatus'));
    }

    /**
     * To Load Tax List of Configurations
     * @return \CartRabbit\Controllers\display
     */
    public function loadTaxesConfiguration()
    {
        $tabType = $this->tabType;
        $admin_menu = $this->admin_menu;
        $taxes = (new Tax())->getTaxes();
        $configStatus = $this->cartConfigStatus;
        return parent::view('Admin.Configuration.Tax.Taxes', compact('tabType', 'admin_menu', 'taxes', 'configStatus'));
    }

    /**
     * @return \CartRabbit\Controllers\display
     */
    public function loadCartConfiguration()
    {
        $tabType = $this->tabType;
        $admin_menu = $this->admin_menu;
        $configStatus = $this->cartConfigStatus;
        $cartConfig = Settings::getCartConfig();
        return parent::view('Admin.Configuration.Cart.cartConfiguration', compact('tabType', 'admin_menu', 'configStatus', 'cartConfig'));
    }

    /**
     * To Load Tax Configuration for given tax ID
     *
     * @param integer $tax_id to get Particular Tax profile
     * @return \CartRabbit\Controllers\display
     */
    public function loadTaxesConfigurationFor($tax_id)
    {
        $tax = new Tax();
        $setting = new Settings();
        $tabType = $this->tabType;
        $admin_menu = $this->admin_menu;
        $content = $tax->getTaxesByID($tax_id);
        $zone = $tax->getTaxesByID($tax_id, true);
        $countries = (new CountryRepository())->getList();
        $configStatus = $this->cartConfigStatus;
        $taxClasses = $setting->getTaxClasses();
        return parent::view('Admin.Configuration.Tax.Taxes_Items.TaxItem', compact('content', 'tabType', 'admin_menu', 'zone', 'tax_id', 'configStatus', 'taxClasses', 'countries'));
    }

    /**
     * To Get Tax Profile's Zones
     *
     * @param integer $tax_id to get Particular Tax profile
     * @return \CartRabbit\Controllers\display
     */
    public function loadTaxesConfigurationListZone($tax_id)
    {
        $tabType = $this->tabType;
        $admin_menu = $this->admin_menu;
        $content = (new Tax())->getTaxesByID($tax_id, true);
        $configStatus = $this->cartConfigStatus;
        return parent::view('Admin.Configuration.Tax.Taxes_Items.ListZone', compact('content', 'tabType', 'admin_menu', 'configStatus'));
    }

    /**
     * To Get Tax Profile's Rates
     *
     * @param integer $tax_id to get Particular Tax profile
     * @return \CartRabbit\Controllers\display
     */
    public function loadTaxesConfigurationListRate($tax_id)
    {
        $tabType = $this->tabType;
        $admin_menu = $this->admin_menu;
        $content = (new Tax())->getTaxesByID($tax_id);
        $configStatus = $this->cartConfigStatus;
        return parent::view('Admin.Configuration.Tax.Taxes_Items.ListRate', compact('content', 'tabType', 'admin_menu', 'tax_id', 'configStatus'));
    }

    /**
     * @param $http Http instance for access post data
     * @return \CartRabbit\Controllers\display
     */
    public function getTaxAmountConfiguration(Http $http)
    {
        $tabType = $this->tabType;
        $admin_menu = $this->admin_menu;
        $tax_id = $http->has('zone') ? $http->get('zone') : null;
        $rate_id = $http->has('rate') ? $http->get('rate') : null;
        $tax = new Tax();
        $content = $tax->getTaxesByID($tax_id);
        $status = $tax->checkAvailablity($content, $rate_id);
        $configStatus = $this->cartConfigStatus;
        return parent::view('Admin.Configuration.Tax.Taxes_Items.ConfigRate', compact('content', 'tabType', 'admin_menu', 'tax_id', 'rate_id', 'status', 'configStatus'));
    }

    /**
     * To Show List Of Rate's by Tax Profile
     *
     * @param integer $tax_id of a Tax Profile
     * @param integer $rate_id of a Rate Profile under Given Tax
     * @return \CartRabbit\Controllers\display
     */
    public function loadTaxesConfigurationListRateAmounts($tax_id, $rate_id)
    {
        $tabType = $this->tabType;
        $admin_menu = $this->admin_menu;
        $tax = new Tax();
        $content = $tax->getTaxesByID($tax_id);
        $status = $tax->checkAvailablity($content, $rate_id);
        $configStatus = $this->cartConfigStatus;
        return parent::view('Admin.Configuration.Tax.Taxes_Items.ConfigRate', compact('content', 'tabType', 'admin_menu', 'tax_id', 'rate_id', 'status', 'configStatus'));
    }

    /**
     * @param $http Http instance for access post data
     */
    public function removeCartTaxClass(Http $http)
    {
        (new Settings())->removeTaxClass($http);
    }

    /**
     * @param $http Http instance for access post data
     * @return \Herbert\Framework\Response
     */
    public function saveCartTaxClass(Http $http)
    {
        $setting = new Settings();
        $setting->saveTaxClasses($http);

        wp_redirect(get_site_url() . '/wp-admin/admin.php?page=cartrabbit-config&tab=tax&opt=tax_classes');
    }

    /**
     * @param $http Http instance for access post data
     * @return \Herbert\Framework\Response
     */
    public function editTaxProfile(Http $http)
    {
        $tax_id = ($http->has('tax_profile_id') ? $http->get('tax_profile_id') : null);
        if ($tax_id) {
            (new Tax())->editTaxProfile($tax_id, $http->all());
        }
        wp_redirect(get_site_url() . '/wp-admin/admin.php?page=cartrabbit-config&tab=tax&opt=taxes&tax_id=' . $tax_id);
    }

    /**
     * To Save Tax Rates
     *
     * @param $http Http instance for access post data
     * @return  mixed redirect to Tax Profile
     */
    public function saveTaxRate(Http $http)
    {
        $tax_id = ($http->has('tax_id') ? $http->get('tax_id') : null);
        if ($tax_id) {
            (new Tax())->saveTaxRate($tax_id, $http->all());
        }
        wp_redirect(get_site_url() . '/wp-admin/admin.php?page=cartrabbit-config&tab=tax&opt=taxes&tax_id=' . $tax_id);
    }

    /**
     * To Save Tax Rates
     *
     * @param $http Http instance for access post data
     * @return  mixed redirect to Tax Profile
     */
    public function editTaxRate(Http $http)
    {
        $tax_id = ($http->has('tax_id') ? $http->get('tax_id') : null);
        if ($tax_id) {
            (new Tax())->updateDefaultStatus($tax_id, $http->all());
        }
        wp_redirect(get_site_url() . '/wp-admin/admin.php?page=cartrabbit-config&tab=tax&opt=taxes&tax_id=' . $http->get('tax_id'));
    }

    /**
     * @param $http Http instance for access post data
     */
    public function removeTaxRate(Http $http)
    {
        $tax_id = ($http->has('tax_id') ? $http->get('tax_id') : null);
        if ($tax_id) {
            (new Tax())->removeTaxRates($tax_id, $http->all());
        }
    }

    /**
     * @param $http Http instance for access post data
     * @return  Tax Rate's page
     */
    public function addZone(Http $http)
    {
        $tax_id = ($http->has('tax_id') ? $http->get('tax_id') : null);
        if ($tax_id) {
            (new Tax())->saveTaxZone($tax_id, $http->all());
        }
        wp_redirect(get_site_url() . '/wp-admin/admin.php?page=cartrabbit-config&tab=tax&opt=taxes&tax_id=' . $http->get('tax_id'));
    }

    /**
     * @param $http Http instance for access post data
     * @return \Herbert\Framework\Response
     */
    public function editZone(Http $http)
    {
        $tax_id = ($http->has('tax_id') ? $http->get('tax_id') : null);
        if ($tax_id) {
            (new Tax())->editTaxZone($tax_id, $http->all());
        }
        wp_redirect(get_site_url() . '/wp-admin/admin.php?page=cartrabbit-config&tab=tax&opt=taxes&tax_id=' . $http->get('tax_id'));
    }

    /**
     * @param $http Http instance for access post data
     */
    public function removeZone(Http $http)
    {
        $tax_id = ($http->has('tax_id') ? $http->get('tax_id') : null);
        if ($tax_id) {
            (new Tax())->removeTaxZone($tax_id, $http->all());
        }
    }

    /**
     * To Save Tax Rates
     *
     * @param $http Http instance for access post data
     * @return  mixed redirect to Amount Profile Page
     */
    public function saveTaxAmount(Http $http)
    {
        (new Tax())->saveTaxAmount($http->all());
        wp_redirect(get_site_url() . $_SERVER['HTTP_REFERER'], true);
    }

    /**
     * To Remove Tax's Rate Profile by Amount ID
     *
     * @param $http Http instance for access post data
     */
    public function removeTaxAmount(Http $http)
    {
        (new Tax())->removeTaxAmount($http->all());
    }

    /**
     * To Loads Tax configuration settings
     *
     * @return \CartRabbit\Controllers\display
     */
    public function loadInventoryConfiguration()
    {
        $setting = new Settings();
        $this->tabType = 'inventory';
        $tabType = $this->tabType;
        $admin_menu = $this->admin_menu;
        $inventory = $setting->getInventoryConfig();
        $configStatus = $this->cartConfigStatus;
        return parent::view('Admin.Configuration.Inventory.Inventory', compact('tabType', 'admin_menu', 'inventory', 'configStatus'));
    }

    /** ****************************MANAGE SHIPPING******************************** */

    /**
     * @return \CartRabbit\Controllers\display
     */
    public function loadShippingConfigurations()
    {
        $tabType = 'shipping';
        $option_menu = Settings::getAllShippingOptionMenu();
        $config = Settings::loadShippingConfigurations();
        $admin_menu = $this->admin_menu;
        return parent::view('Admin.Shippings.general', compact('content', 'admin_menu', 'config', 'tabType', 'option_menu'));
    }

    public function loadShipping($plugin)
    {
        $tabType = 'shipping';
        $result = array();

        // Set the Type of Plugin to Init
        $result['type'] = $plugin;
        $content = apply_filters('cartrabbit_shipping_config', $result)['html'];

        $option_menu = Settings::getAllShippingOptionMenu();
        $admin_menu = $this->admin_menu;
        $plugin_type = $plugin;
        return parent::view('Admin.Shippings.shipping', compact('content', 'admin_menu', 'config', 'tabType', 'option_menu', 'plugin_type'));
    }

    public function loadPaymentConfigurations()
    {
        $tabType = 'payment';
        $admin_menu = $this->admin_menu;
        $option_menu = Settings::getAllPaymentOptionMenu();
        $config = Settings::getPaymentConfig();
        return parent::view('Admin.Payment.general', compact('admin_menu', 'config', 'tabType', 'option_menu'));
    }

    public function loadPayment($plugin)
    {
        $tabType = 'payment';
        $admin_menu = $this->admin_menu;
        $option_menu = Settings::getAllPaymentOptionMenu();
        $content = apply_filters('cartrabbit_payment_plugin', $plugin);
        $plugin_type = $plugin;
        return parent::view('Admin.Payment.payment', compact('admin_menu', 'content', 'tabType', 'option_menu', 'plugin_type'));
    }

    /** ****************************MANAGE ORDER******************************** */

    public function loadOrderConfigurations()
    {
        $tabType = 'order';
        $admin_menu = $this->admin_menu;
        $config['invoice_prefix'] = Settings::get('invoice_prefix', '');
        return parent::view('Admin.Configuration.Order.general', compact('admin_menu', 'config', 'tabType'));

    }

    public function setPaymentConfig(Http $http)
    {
        $default = $http->has('payment_default') ? $http->get('payment_default') : '';
        Settings::set('payment_default', $default);
        wp_redirect(get_site_url() . '/wp-admin/admin.php?page=cartrabbit-config&tab=payment&opt=general');
    }

    public function setPaymentPluginConfig(Http $http)
    {
        $data = $http->has('cartrabbit') ? $http->get('cartrabbit') : null;
        if (!is_null($data)) {
            do_action('cartrabbit_payment_plugin_save', $data);
        }
    }

    /**
     * @param Http $http
     * @return array
     */
    public function manageShipping(Http $http)
    {
        $data = $http->has('cartrabbit') ? $http->get('cartrabbit') : false;
        if (!$data) return array();
        do_action('cartrabbit_save_shipping_config', $data);
    }

    public function manageShippingConfig(Http $http)
    {
        $status = $http->has('shipping_enable') ? $http->get('shipping_enable') : null;
        $shippingRestrict = $http->has('shipping_dont_allow_if_no_shipping') ? $http->get('shipping_dont_allow_if_no_shipping') : null;

        if (!is_null($status)) {
            $status = 'yes';
        } else {
            $status = 'no';
        }
        if (!is_null($shippingRestrict)) {
            $shippingRestrict = 'yes';
        } else {
            $shippingRestrict = 'no';
        }

        Settings::set('shipping_enable', $status);
        Settings::set('shipping_dont_allow_if_no_shipping', $shippingRestrict);
        Customer::removeMethod('shipping');

        wp_redirect(get_site_url() . '/wp-admin/admin.php?page=cartrabbit-config&tab=shipping');
    }

    /**
     * @param Http $http
     * @return bool
     */
    public function removeShipping(Http $http)
    {
        $plugin = Util::extractDataFromHTTP('opt');
        $data['row'] = $http->get('id');
        $data['id'] = $plugin;
        do_action('cartrabbit_remove_shipping_rate', $data);
    }

    /** ************************************PRODUCT CONFIG*********************************************** */

    /**
     * To Loads Product configuration settings
     *
     * @param string $option getting from the URL
     * @return \CartRabbit\Controllers\display
     */
    public function loadProductConfigurationByType($option)
    {
        $tabType = 'product';
        $this->tabType = $tabType;
        if ($option === 'general') {
            $html = $this->productGeneralConfiguration();
        } elseif ($option === 'display_product') {
            $html = $this->productDisplayProductConfiguration();
        } elseif ($option === 'downloadable_products') {
            $html = $this->productDownloadableProductsConfiguration();
        } elseif ($option === 'brands') {
            $html = $this->productBrandsConfiguration();
        } else {
            $html = $this->productGeneralConfiguration();
        }
        return $html;
    }

    /**
     * To Save Inventory Configurations
     *
     * @param $http Http instance for access post data
     * @return \Herbert\Framework\Response
     */
    public function saveInventoryConfigurations(Http $http)
    {
        $setting = new Settings();
        $data = $http->all();
        $setting->saveInventoryConfig($data);
        wp_redirect(get_site_url() . '/wp-admin/admin.php?page=cartrabbit-config&tab=inventory');
    }

    /**
     * To Display the Product's General Configuration Settings
     *
     * @return \CartRabbit\Controllers\display
     */
    public function productGeneralConfiguration()
    {
        $tabType = $this->tabType;
        $admin_menu = $this->admin_menu;
        $generalConfig = (new Settings())->getProductGeneralConfig();
        $configStatus = $this->cartConfigStatus;
        return parent::view('Admin.Configuration.Products.General', compact('tabType', 'admin_menu', 'generalConfig', 'configStatus'));
    }

    /**
     * * To Display the Product's Display Configuration Settings
     *
     * @return \CartRabbit\Controllers\display
     */
    public function productDisplayConfiguration()
    {
        $tabType = $this->tabType;
        $admin_menu = $this->admin_menu;
        $pages = Settings::getDisplaySetup();
        $pages = (!$pages) ? array() : $pages;
        $configStatus = $this->cartConfigStatus;
        return parent::view('Admin.Configuration.Products.Display', compact('tabType', 'admin_menu', 'pages', 'current_page', 'configStatus'));
    }

    /**
     * @return \CartRabbit\Controllers\display
     */
    public function productDisplayProductConfiguration()
    {
        $settings = new Settings();
        $tabType = $this->tabType;
        $admin_menu = $this->admin_menu;
        $productConfig = $settings->getProductDisplaySetup();
        return parent::view('Admin.Configuration.Products.DisplayProduct', compact('tabType', 'admin_menu', 'productConfig'));
    }

    /**
     * @param Http $http
     * @return \Herbert\Framework\Response
     */
    public function saveProductDisplayConfiguration(Http $http)
    {
        $settings = new Settings();
        $settings->saveProductDisplayConfigurations($http);
        wp_redirect(get_site_url() . '/wp-admin/admin.php?page=cartrabbit-config&tab=product&opt=display_product');
    }

    /**
     * * To Display the Product's Brands Settings
     *
     * @return \CartRabbit\Controllers\display
     */
    public function productBrandsConfiguration()
    {
        $setting = new Settings();
        $brands = $setting->getBrandTaxonomy();
        $tabType = $this->tabType;
        $admin_menu = $this->admin_menu;
        $configStatus = $this->cartConfigStatus;
        return parent::view('Admin.Configuration.Products.Brands', compact('tabType', 'admin_menu', 'brands', 'configStatus'));
    }

    /**
     * To Display the Product's Downloadable Configuration Settings
     *
     * @return \CartRabbit\Controllers\display
     */
    public function productDownloadableProductsConfiguration()
    {
        $tabType = $this->tabType;
        $admin_menu = $this->admin_menu;
        $configStatus = $this->cartConfigStatus;
        return parent::view('Admin.Configuration.Products.downloadableproducts', compact('tabType', 'admin_menu', 'configStatus'));
    }


    /**
     * To Save Product's General Configurations
     *
     * @param $http Http instance for access post data
     * @return \Herbert\Framework\Response
     */
    public function saveProductGeneralConfig(http $http)
    {
        (new Settings())->saveProductGeneralConfig($http->all());
        wp_redirect(get_site_url() . '/wp-admin/admin.php?page=cartrabbit-config&tab=product&opt=general');
    }

    /**
     * @param Http $http
     * @return \Herbert\Framework\Response
     */
    public function saveCartConfiguration(Http $http)
    {
        (new Settings())->saveCartConfig($http);
        wp_redirect(get_site_url() . '/wp-admin/admin.php?page=cartrabbit-config&tab=cart');
    }

    /**
     * To Create or Update Secondary Currency
     *
     * @param $http Http instance for access post data
     * @return \Herbert\Framework\Response
     */
    public function saveCartSecondaryCurrency(Http $http)
    {
        $settings = new Settings();
        $exist = count(Post::where('post_type', 'cartrabbit_currency')->get());
        if ($exist) {
            $settings->updateSecondaryCurrency($http);
        } else {
            $settings->createSecondaryCurrency($http);
        }
        return redirect_response(panel_url('CartRabbit::cartConfig'));
    }

    /**
     * To Create or Update the general cart configuration settings
     *
     * @param $http Http instance for access post data
     * @return \Herbert\Framework\Response
     */
    public function saveCartGeneralConfig(http $http)
    {
        $settings = new Settings();
        $exist = count(Post::where('post_type', 'cartrabbit_config')->get());
        if ($exist) {
            $settings->updateGeneralConfig($http);
        } else {
            $settings->createGeneralConfiguration($http);
        }
        return redirect_response(panel_url('CartRabbit::cartConfig'));
    }

    /**
     * To Create or Update the general Store configuration settings,
     * This will executed on "First Time Activation"
     *
     * @param $http Http instance for access post data
     * @return \Herbert\Framework\Response
     */
    public function saveStoreGeneralConfig(http $http)
    {
        $settings = new Settings();
        $exist = count(Post::where('post_type', 'cartrabbit_config')->get());
        if ($exist) {
            $settings->updateGeneralConfig($http);
        } else {
            $settings->createGeneralConfiguration($http);
            (new Setup())->initBasicPages();
        }

        /** Install Additional Plugins */
        Setup::preConfigurations();
        wp_redirect(get_site_url() . '/wp-admin/admin.php?page=cartrabbit-config');
    }

    /**
     * To Save Cart Tax Configurations
     *
     * @param $http Http instance for access post data
     * @return \Herbert\Framework\Response
     */
    public function saveCartTaxConfig(http $http)
    {
        $settings = new Settings();
        $exist = count(Post::where('post_type', 'cartrabbit_config')->get());
        if ($exist) {
            $settings->updateTaxConfigurations($http);
        } else {
            $settings->createTaxConfigurations($http);
        }

        wp_redirect(get_site_url() . '/wp-admin/admin.php?page=cartrabbit-config&tab=tax&opt=taxoptions');
    }

    /**
     * To Remove Secondary Currencies
     *
     * @param $http Http instance for access post data
     */
    public function removeSecondaryCurrency(http $http)
    {
        $currency = ($http->has('currency') ? $http->get('currency') : null);
        $settings = new Settings();
        $settings->removeCurrency($currency);
    }

    /**
     * To Set Page to List Products in Site View
     *
     * @param $http Http instance for access post data
     * @return \Herbert\Framework\Response
     */
    public function setPageToDisplayProducts(Http $http)
    {
        if (($http->has('page_to_list_product') and $http->get('page_to_list_product') != 'NoPage')) {
            $page['page_to_list_product'] = $http->get('page_to_list_product');
        } else {
            $page['page_to_list_product'] = 'NoPage';
        }
        if (($http->has('page_to_cart_product') and $http->get('page_to_cart_product') != 'NoPage')) {
            $page['page_to_cart_product'] = $http->get('page_to_cart_product');
        } else {
            $page['page_to_cart_product'] = 'NoPage';
        }
        if (($http->has('page_to_account') and $http->get('page_to_account') != 'NoPage')) {
            $page['page_to_account'] = $http->get('page_to_account');
        } else {
            $page['page_to_account'] = 'NoPage';
        }
        if (($http->has('page_to_checkout') and $http->get('page_to_checkout') != 'NoPage')) {
            $page['page_to_checkout'] = $http->get('page_to_checkout');
        } else {
            $page['page_to_checkout'] = 'NoPage';
        }
        if (($http->has('page_to_thank') and $http->get('page_to_thank') != 'NoPage')) {
            $page['page_to_thank'] = $http->get('page_to_thank');
        } else {
            $page['page_to_thank'] = 'NoPage';
        }

        $setting = new Settings();
        $setting->setPageToDisplay($page);
        wp_redirect(get_site_url() . '/wp-admin/admin.php?page=cartrabbit-config&tab=product&opt=display');
    }

    /**
     * To Display Products on Drop Box For UpSells & CrossSells
     *
     * @param $http Http instance for access post data
     * @return mixed|string|void
     */
    public function getProductList(Http $http)
    {
        return (new Settings())->getProductsbyName($http);
    }

    /**
     * To Add New Brand Taxonomy for Products
     *
     * @param $http Http instance for access post data
     * @return mixed view of product brand page
     */
    public function addBrandTaxonomy(Http $http)
    {
        $taxonomy = $http->has('brandTaxonomy') ? $http->get('brandTaxonomy') : null;
        (new Settings())->addBrandTaxonomy($taxonomy);
        wp_redirect(get_site_url() . '/wp-admin/admin.php?page=cartrabbit-config&tab=product&opt=brands');
    }

    /**
     * To Remove Brand Taxonomy
     *
     * @param $http Http instance for access post data
     * @return bool
     */
    public function removeBrandTaxonomy(Http $http)
    {
        $term_id = $http->has('termId') ? $http->get('termId') : null;
        if ($term_id == null) return false;
        (new Settings())->removeBrandTaxonomybyID($term_id);
    }

    /**
     * To Return Store's Configuration
     *
     * @return array
     */
    public function storeConfigurations()
    {
        return (new Settings())->getStoreConfigurations();
    }

    /**
     * To Get Dynamic Link Samples for Reliable Permalink Configurations
     *
     * @return \CartRabbit\Controllers\display
     */
    public function configPermalink()
    {
        $settings = new Settings();
        $permalink = $settings->getPermalinkSamples();
        $config = $settings->getPermalinkSettings();
        $configStatus = $this->cartConfigStatus;
        return parent::view('Admin.Settings.permalink', compact('permalink', 'config', 'configStatus'));
    }


}