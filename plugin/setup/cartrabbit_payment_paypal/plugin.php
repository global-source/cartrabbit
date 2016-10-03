<?php

/**
 * @wordpress-plugin
 * Plugin Name:       CartRabbit PayPal Payment
 * Plugin URI:        http://flycart.org/
 * Description:       A Standard PayPal Payment plugin for CartRabbit
 * Version:           0.0.1
 * Author:            Shankar Thiyagaraajan
 * License:           MIT
 */

/** Verify the Parent Plugin */
if (!in_array('cartrabbit/plugin.php', get_option('active_plugins'))) return;

if (!defined('ABSPATH')) {
    die('Access denied.');
}

require_once __DIR__ . '/payment_paypal.php';

/** Register Payment */
add_filter('cartrabbit_payment_plugins', 'Payment_paypal::register_plugin');

add_filter('cartrabbit_payment_option_menu', 'Payment_paypal::register_plugin_menu');

add_filter('cartrabbit_is_available', 'Payment_paypal::is_available');

/** Return Payment Data's */
add_filter('cartrabbit_payment_plugin', 'Payment_paypal::loadPaypalConfigurations');

add_action('cartrabbit_payment_plugin_save', 'Payment_paypal::save_plugin_config');

add_filter('cartrabbit_prepayment_form', 'Payment_paypal::pre_payment_form');

add_action('cartrabbit_post_payment', 'Payment_paypal::payment_process');
