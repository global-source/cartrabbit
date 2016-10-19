<?php

namespace CartRabbit\Models;

use CartRabbit\Helper\Util;
use Corcel\Post as Post;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Class products
 * @package CartRabbit\Models
 */
class Setup extends Post
{

    /**
     * Cart constructor.
     */
    public function __construct()
    {

    }

    /**
     *To Check Cart's Initial Configuration
     *
     */
    public function isNewBoot()
    {
        $isNew = false;
        $ID = Settings::getStoreConfigID();
        if (!$ID) {
            $isNew = true;
        }
        return $isNew;
    }

    /**
     * To Initialize and Configure the Default CartRabbit pages
     */
    public function initBasicPages()
    {
        /** Create Default Product category */
        wp_insert_term('uncategorized', 'genre');

        /** Create Default Pages */
        $pages = array(
            'products' => array(
                'title' => 'Products',
                'name' => 'products',
                'type' => 'page',
                'content' => '[CartRabbitProducts]'
            ),
            'cart' => array(
                'title' => 'Cart',
                'name' => 'cart',
                'type' => 'page',
                'content' => '[CartRabbitCart]'
            ),
            'checkout' => array(
                'title' => 'Checkout',
                'name' => 'checkout',
                'type' => 'page',
                'content' => '[CartRabbitCheckout]'
            ),
            'account' => array(
                'title' => 'Account',
                'name' => 'account',
                'type' => 'page',
                'content' => '[CartRabbitAccount]'
            ),
//            'prePayment' => array(
//                'title' => 'Pre Payment',
//                'name' => 'prePayment',
//                'type' => 'page',
//                'content' => '[CartRabbitPrePayment]'
//            ),
            'thankYou' => array(
                'title' => 'ThankYou',
                'name' => 'thankYou',
                'type' => 'page',
                'content' => 'Thanks For Using !'
            )
        );

        $page_index = array();
        foreach ($pages as $index => $item) {
            $page = Post::type('page')->where('post_title', $index);
            if ($page->count() == 0) {
                $post = new Post();
                foreach ($item as $key => $value) {
                    $key = 'post_' . $key;
                    $post->$key = $value;
                }
                $post->save();
                $page_index[$index] = $post->ID;
            } else {
                $page_index[$index] = $page->pluck('ID')->first();
            }
        }
        /** Create Prodcuts Page for List Products */
        $product_id = $page_index['products'];

        /** Create Cart Page for List Cart Products */
        $cart_id = $page_index['cart'];

        /** Create Checkout Page for Shop Checkout */
        $checkout_id = $page_index['checkout'];

        /** Create Account Page for Manage Orders */
        $account_id = $page_index['account'];

        /** Create Thank You Page for Display on Payment Completion */
        $thankYou_id = $page_index['thankYou'];

        /** Create Default Page for Display  */
        $display = array(
            'page_to_list_product' => $product_id,
            'page_to_cart_product' => $cart_id,
            'page_to_account' => $account_id,
            'page_to_checkout' => $checkout_id,
            'page_to_thank' => $thankYou_id
        );
        (new Settings())->setPageToDisplay($display);
    }

    /**
     *  To Trigger the Installation of Supportive plugins.
     */
    public static function installAdditionalPlugins()
    {
        $oldfolderpath = WP_PLUGIN_DIR. '/cartrabbit/plugin/setup/';
        $newfolderpath = WP_PLUGIN_DIR;
        if (Util::full_copy($oldfolderpath, $newfolderpath)) {

        }
        self::activatePlugins();
//        self::preConfigurations();
    }

    /**
     * To Return List of Supportive plugins.
     * @return array
     */
    public static function getPluginList()
    {
        return [
            'cartrabbit_payment_cod' => 'cartrabbit_payment_COD/plugin.php',
            'cartrabbit_payment_paypal' => 'cartrabbit_payment_paypal/plugin.php',
            'cartrabbit_shipping_flatrate' => 'cartrabbit_shipping_flatrate/plugin.php',
            'cartrabbit_shipping_qty_based' => 'cartrabbit_shipping_qty_based/plugin.php'
        ];
    }

    /**
     * To Verify the Existence of File.
     *
     * @param $folders
     */
    public static function verifyExistence(&$folders)
    {
        $path = WP_PLUGIN_DIR;
        foreach ($folders as $index => $folder) {
            if (!file_exists($path . $folder)) {
                unset($folders[$index]);
            }
        }
    }

    /**
     * To Activate supportive plugins by updating the wordpress option.
     */
    public static function activatePlugins()
    {
        $wordpress_plugin = get_option('active_plugins');
        $corePlugins = self::getPluginList();
        self::verifyExistence($corePlugins);
        foreach ($corePlugins as $index => $plugin) {
            if (!in_array($plugin, $wordpress_plugin)) {
                $wordpress_plugin[] = $plugin;
            }
        }
        update_option('active_plugins', $wordpress_plugin);
    }

    /**
     * Basic Config data setup.
     */
    public static function preConfigurations()
    {
        $tax_classes = ['standard', 'reduced'];

        $config =
            [
                // Setup Default Tax Classes
                'tax_classes' => json_encode($tax_classes),

                // Setup Product Display Config.
                'd_config_catalog_model' => 'no',
                'd_config_show_sku' => 'yes',
                'd_config_show_brand' => 'yes',
                'd_config_show_desc' => 'yes',
                'd_config_quantity_field' => 'yes',
                'd_config_show_price' => 'yes',
                'd_config_show_price_range' => 'yes',
                'd_config_show_product_image' => 'yes',
                'd_config_show_product_thumbnail' => 'yes',
                'd_config_show_product_gallery' => 'yes',
            ];

        Settings::updateConfig($config);
    }

}