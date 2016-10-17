<?php
/**
 * @wordpress-plugin
 * Plugin Name:       CartRabbit COD Payment
 * Plugin URI:        http://flycart.org/
 * Description:       A Simple COD Plugin for CartRabbit
 * Version:           0.0.1
 * Author:            Shankar Thiyagaraajan
 * License:           MIT
 */

/** Verify the Parent Plugin */
if (!in_array('cartrabbit/plugin.php', get_option('active_plugins'))) return;

if (!defined('ABSPATH')) {
    die('Access denied.');
}

require_once 'Helper/manage_plugin.php';

require_once WP_PLUGIN_DIR . '/cartrabbit/vendor/flycartinc/order/src/Model/Order.php';
use Flycartinc\Order\Model\Order;


function register_plugin($list)
{
    $data = Manage_plugin::load();
    if (!isset($data)) {
        $list['cartrabbit_payment_cod'] = 'Cash On Delivery';
    } else {
        $list['cartrabbit_payment_cod'] = $data['cod_payment_title'];
    }
    return $list;
}

function plugin_info($element)
{
    if (Manage_plugin::is_me($element)) {
        return 'Cash On Delivery';
    }
}

function register_plugin_menu($items)
{
    $items['COD'] = '?page=cartrabbit-config&tab=payment&opt=cartrabbit_payment_cod';

    return $items;
}

function load_plugin_data($element)
{
    if (Manage_plugin::is_me($element)) {
        return Manage_plugin::loadShippingConfigurations();
    } else {
        return $element;
    }
}

function save_plugin_config($data)
{
    if (Manage_plugin::is_me($data['payment']['plugin'])) {
        Manage_plugin::save($data);
    } else {
        return $data;
    }
}

function payment_process($data)
{
    if (Manage_plugin::is_me($data['payment_name'], true)) {

        /** For COD */
        $plugin_data = Manage_plugin::load();

        $order_id = $data['order_id'];

        $order = new Order();
        $order->setOrderId($order_id);
        $order->paymentComplete($plugin_data['cod_default_payment_status']);
        Order::emptyCart();
        $data['result'] = 'display';
    }
    return $data;
}

function pre_payment_form($data)
{
    if (Manage_plugin::is_me($data['plugin_name'])) {
        $site_url = get_site_url();
        $data['result'] = ' <form method="post" action="' . $site_url . '/confirmPayment">
                                <div class="place-order-button">
                                    <input type="submit" class="fc-btn fc-btn-success" value="Place Order"
                                           id="btn_place_order">
                                           <input type="hidden" name="order_id" value="' . $data['plugin']['order_id'] . '">
                                           <input type="hidden" name="payment_name" value="' . $data['plugin_name'] . '">
                                </div>
                            </form>';
    }
    return $data;
}

/** Register Payment */
add_filter('cartrabbit_payment_plugins', 'register_plugin');

/** Get Plugin info */
add_filter('cartrabbit_payment_info', 'plugin_info');

/** Return Payment Data's */
add_filter('cartrabbit_payment_plugin', 'load_plugin_data');

add_filter('cartrabbit_payment_option_menu', 'register_plugin_menu');

add_filter('cartrabbit_is_available', 'Manage_plugin::is_available');

/** Event State Representations */
add_filter('cartrabbit_payment_plugins_on_select', 'state_on_select');
add_filter('cartrabbit_payment_plugins_on_before', 'state_on_before');
add_filter('cartrabbit_payment_plugins_on_after', 'state_on_after');

add_filter('cartrabbit_prepayment_form', 'pre_payment_form');

add_action('cartrabbit_post_payment', 'payment_process');

add_action('cartrabbit_payment_plugin_save', 'save_plugin_config');
