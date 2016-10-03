<?php
/**
 * @wordpress-plugin
 * Plugin Name:       CartRabbit Flat Rate Shipping
 * Plugin URI:        http://flycart.org/
 * Description:       A Simple Flat Rate Shipping Plugin for CartRabbit
 * Version:           0.0.1
 * Author:            Shankar Thiyagaraajan
 * License:           MIT
 */

/** Verify the Parent Plugin */
if (!in_array('cartrabbit/plugin.php', get_option('active_plugins'))) return;

if (!defined('ABSPATH')) {
    die('Access denied.');
}

require_once 'helper/flat_rate_shipping.php';

/** Register Payment */
add_filter('cartrabbit_shipping_plugins', 'Flat_rate_shipping::register_plugin');

add_filter('cartrabbit_shipping_methods', 'Flat_rate_shipping::init');

add_filter('is_available', 'Flat_rate_shipping::is_available', 10, 2);

add_filter('storpress_shipping_option_menu', 'Flat_rate_shipping::add_option_menu_item');

add_filter('cartrabbit_shipping_config', 'Flat_rate_shipping::loadShippingConfigurations');

/** To Save the shipping configurations */
add_action('cartrabbit_save_shipping_config', 'Flat_rate_shipping::save_configuration');

add_action('cartrabbit_package_rates', 'Flat_rate_shipping::calculateRates');