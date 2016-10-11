<?php
/**
 * @wordpress-plugin
 * Plugin Name:       CartRabbit Quantity Based Shipping
 * Plugin URI:        http://flycart.org/
 * Description:       A Simple Quantity Based Shipping Plugin for CartRabbit.
 * Version:           0.0.1
 * Author:            Shankar Thiyagaraajan
 * License:           MIT
 */

if (!defined('ABSPATH')) {
    die('Access denied.');
}

/** Verify the Parent Plugin */
if (!in_array('cartrabbit/plugin.php', get_option('active_plugins'))) return;

require_once __DIR__ . '/Manage_qty_shipping.php';

global $element;

$element = 'cartrabbit_shipping_qty_based';

/**
 * Returns the Array of menu items
 *
 * @param $items
 * @return mixed
 */
function getAdminMenu($items)
{
//    $items['Shipping'] = '?page=cartrabbit-config&tab=shipping';
    return $items;
}

/**
 * @param array $params
 * @return array
 */
function init($params = array())
{
    $params[] = array('id' => 'cartrabbit_shipping_qty_based');
    return $params;
}

/**
 * @param $element
 * @return bool
 */
function is_available($element)
{
    if (is_me($element)) {
        $config = Manage_qty_shipping::load();
        if (isset($config['enabled'])) {
            return $config['enabled'];
        }
    } else {
        return $element;
    }
}

function add_option_menu_item($items)
{
    $items['Quantity Based Shipping'] = '?page=cartrabbit-config&tab=shipping&opt=cartrabbit_shipping_qty_based';
    return $items;
}

/**
 * @param $element
 * @return bool
 */
function is_me($element)
{
    return 'cartrabbit_shipping_qty_based' == $element;
}

/**
 * @param $data
 */
function save_configuration($data)
{
    if (is_me($data['shipping']['plugin'])) {
        Manage_qty_shipping::save($data);
    }
}

/**
 * @return string
 */
function loadShippingConfigurations($result)
{
    if (is_me($result['type'])) {
        $config['items'] = Manage_qty_shipping::load();
        $path = __DIR__ . '/view/Shipping.php';
        $result['html'] = Manage_qty_shipping::processView($path, $config);
    }
    return $result;
}

/**
 * @return array
 */
function loadShippingConfigurationsList()
{
    return Manage_qty_shipping::load();
}

/**
 * @param $package
 * @return mixed
 */
function loadShippingRates($package)
{
    return Manage_qty_shipping::processRates($package);
}

/**
 * @param $package
 */
function calculate($package)
{
    //
}

/**
 *
 */
function init_source()
{
    $url = plugin_dir_url(__FILE__);
    wp_enqueue_script('CartRabbit jQuery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js', false);
    wp_enqueue_script('CartRabbit jQuery 2', $url . '/assets/js/shipping.js', false);
}

/**
 * @param $data
 */
function removeShippingRate($data)
{
    if (is_me($data['id'])) {
        Manage_qty_shipping::remove($data['row']);
    }
}


/** Register Payment */
add_filter('cartrabbit_shipping_plugins', 'Manage_qty_shipping::register_plugin');

add_filter('cartrabbit_shipping_methods', 'init');
add_filter('is_available', 'is_available', 10, 2);

/** To Process the admin_menu_items */
add_filter('cartrabbit_admin_menu_items', 'getAdminMenu');

/** To Load Shipping Configurations */
add_filter('cartrabbit_shipping_config_list', 'loadShippingConfigurationsList');
add_filter('cartrabbit_shipping_config', 'loadShippingConfigurations');

/** Load All Shipping Rates */
add_filter('cartrabbit_package_rates', 'loadShippingRates');

/** To Calculate Shipping */
add_filter('cartrabbit_calculate_shipping', 'calculate');

/** To Save the shipping configurations */
add_action('cartrabbit_save_shipping_config', 'save_configuration');

/** To Remove Shipping Rate */
add_action('cartrabbit_remove_shipping_rate', 'removeShippingRate');

add_filter('storpress_shipping_option_menu', 'add_option_menu_item');

add_action('init', 'init_source');