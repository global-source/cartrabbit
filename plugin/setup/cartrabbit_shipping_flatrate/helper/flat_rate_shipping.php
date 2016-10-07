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
            $config = self::loadConfig();
            return $config['meta']['enableShipping'][0];

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
        global $wpdb;

        $result = $wpdb->get_results("SELECT * FROM wp_posts WHERE post_type = 'crt_sh_fla_conf'");

        $storeConfigId = $result[0]->ID;
        if (!$storeConfigId or empty($storeConfigId) or !isset($storeConfigId)) {
            $storeConfigId = self::createConfig();
        }
        if ($data['shipping']['enabled'] != 'on') {
            $data['shipping']['enabled'] = 'off';
        }

        foreach ($data['shipping'] as $key => $value) {

            $result = get_post_meta($storeConfigId, $key);
            if ($result) {
                update_post_meta($storeConfigId, $key, $value);
            } else {
                add_post_meta($storeConfigId, $key, $value);
            }
        }

        update_post_meta($storeConfigId, 'enableShipping', $data['shipping']['enabled']);

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
        $cost = $data['list'][0]['shipping_cost'][0] + $data['list'][0]['handling_cost'][0];
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
            $config = [];
//            $config['items'] = Manage_free_shipping::load();
//            $config['core'] = Manage_free_shipping::loadConfig();
            $path = __DIR__ . '/../view/flat_rate_shipping.php';
            $result['html'] = self::processView($path, $config);
        }

        return $result;
    }

    static function processView($path, $data)
    {
        ob_start();
        $config = self::load();
        include($path);
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    static function load()
    {
        global $wpdb;
        //TODO: Eliminate this
        $result = $wpdb->get_results("SELECT wp_posts.ID, wp_postmeta.meta_key, wp_postmeta.meta_value FROM wp_posts, wp_postmeta WHERE
                                      wp_posts.post_type = 'crt_sh_fla_conf' AND wp_postmeta.post_id = wp_posts.ID
                                      AND wp_postmeta.meta_key = 'enableShipping'
                                      group by wp_posts.ID");
        $storeConfig = $result[0];

        $status = 'off';
        if ($storeConfig->meta_key == 'enableShipping') {
            $status = $storeConfig->meta_value;
        }

        $ids[] = $wpdb->get_results("SELECT ID from wp_posts WHERE post_type='crt_sh_fla_conf'");

        $meta = array();
        $meta['status'] = $status;
        $i = 0;
        foreach ($ids[0] as $id) {
            $meta['list'][$i] = get_post_meta($id->ID);
            $meta['list'][$i]['id'] = $id->ID;
            $i++;
        }
        return $meta;
    }

}