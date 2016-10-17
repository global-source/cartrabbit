<?php

/**
 * Created by PhpStorm.
 * User: flycartlaravel
 * Date: 15/9/16
 * Time: 4:55 PM
 */

$element = 'cartrabbit_shipping_flat_rate';

class Flat_rate_shipping
{
    static function register_plugin($list)
    {
        $list['cartrabbit_shipping_flat_rate'] = 'Flat Rate Shipping';
        return $list;
    }

    /**
     * @param $element
     * @return bool
     */
    static function is_me($element)
    {
        return 'cartrabbit_shipping_flat_rate' == $element;
    }

    /**
     * @param array $params
     * @return array
     */
    static function init($params = array())
    {
        $params[] = array('id' => 'cartrabbit_shipping_flat_rate');
        return $params;
    }

    static function is_available($element)
    {
        if (self::is_me($element)) {
            $config = self::load();
            if (isset($config['list']['enabled'])) {
                return $config['list']['enabled'];
            }

        } else {
            return $element;
        }
    }

    static function loadConfig()
    {
        global $wpdb;
        $result['post'] = $wpdb->get_results("SELECT * FROM wp_posts WHERE post_type = 'crt_sh_fla_conf'");
        $result['meta'] = [];
        if (isset($result['post'])) {
            if (isset($result['post']->ID)) {
                $result['meta'] = get_post_meta(array_first($result['post'])->ID);
            }
        }
        return $result;
    }

    static function add_option_menu_item($items)
    {
        $items['Flat Rate Shipping'] = '?page=cartrabbit-config&tab=shipping&opt=cartrabbit_shipping_flat_rate';
        return $items;
    }

    /**
     * @param $data
     */
    static function save_configuration($data)
    {
        if (self::is_me($data['shipping']['plugin'])) {
            self::save($data);
        }
    }


    static function save($data)
    {
        if (!isset($data['shipping'])) return false;

        if ($data['shipping']['enabled'] != 'on') {
            $data['shipping']['enabled'] = 'off';
        }

        if (get_option('crt_sh_fla_conf')) {
            update_option('crt_sh_fla_conf', json_encode($data));
        } else {
            add_option('crt_sh_fla_conf', json_encode($data));
        }

//        update_post_meta($storeConfigId, 'enableShipping', $data['shipping']['enabled']);

        /** Redirect to its Landing Page */
        wp_redirect(get_site_url() . '/wp-admin/admin.php?page=cartrabbit-config&tab=shipping&opt=cartrabbit_shipping_flat_rate');
    }

    static function createConfig()
    {
        return wp_insert_post(array(
            'post_name' => 'cartrabbit_flat_shipping_configurations',
            'post_title' => 'CartRabbit Flat Shipping Configurations',
            'post_type' => 'crt_sh_fla_conf'
        ));
    }

    static function calculateRates($package)
    {
        $data = self::load();
        $cost = array_get($data['list'], 'shipping_cost', 0) + array_get($data['list'], 'handling_cost', 0);
        $package['rates']['cartrabbit_shipping_flat_rate'] = array(
            'element' => 'cartrabbit_shipping_flat_rate',
            'total' => $cost
        );
        return $package;
    }

    /**
     * @return string
     */
    static function loadShippingConfigurations($result)
    {
        if (self::is_me($result['type'])) {
            $config = self::load();
            $path = __DIR__ . '/../view/flat_rate_shipping.php';
            $result['html'] = self::processView($path, $config);
        }

        return $result;
    }

    static function processView($path, $data)
    {
        ob_start();
        $config = $data;
        include($path);
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    static function load()
    {
        $meta = array();

        $meta['list'] = json_decode(get_option('crt_sh_fla_conf'), true)['shipping'];
        return $meta;
    }

}