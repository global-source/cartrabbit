<?php

/**
 * Created by PhpStorm.
 * User: flycart
 * Date: 21/7/16
 * Time: 12:34 PM
 */
class Manage_qty_shipping
{
    static function register_plugin($list)
    {
        $list['cartrabbit_shipping_qty_based'] = 'Quantity Based Shipping';
        return $list;
    }

    static function save($data)
    {
        $status = $data['shipping']['enabled'];
        unset($data['shipping']['plugin']);
        unset($data['shipping']['enabled']);

        global $wpdb;
        foreach ($data['shipping'] as $key => $value) {
            if (!isset($value['update'])) {
                $name = preg_replace('/\s+/', '', $value['shippingName']);
                $id = wp_insert_post(array(
                    'post_name' => strtolower(str_replace(' ', '_', $name)),
                    'post_title' => 'CartRabbit ' . $name . ' Shipping Rate',
                    'post_type' => 'crt_sh_qty'
                ));
            }
            foreach ($value as $index => $item) {
                $index = preg_replace('/\s+/', '', $index);
                $item = preg_replace('/\s+/', '', $item);
                if (isset($value['update'])) {
                    update_post_meta($value['id'], $index, $item);
                } else {
                    add_post_meta($id, $index, $item);
                }
            }
        }

        $result = $wpdb->get_results("SELECT * FROM wp_posts WHERE post_type = 'crt_sh_qty_conf'");

        $storeConfigId = $result[0]->ID;
        if (!$storeConfigId or empty($storeConfigId) or !isset($storeConfigId)) {
            $storeConfigId = self::createConfig();
        }
        if ($status != 'on') {
            $status = 'off';
        }

        update_post_meta($storeConfigId, 'enableShipping', $status);

        /** Redirect to its Landing Page */
        wp_redirect(get_site_url() . '/wp-admin/admin.php?page=cartrabbit-config&tab=shipping&opt=cartrabbit_shipping_qty_based');
    }

    static function createConfig()
    {
        return wp_insert_post(array(
            'post_name' => 'cartrabbit_shipping_qty_configurations',
            'post_title' => 'CartRabbit Qty Based Shipping Configurations',
            'post_type' => 'crt_sh_qty_conf'
        ));
    }

    static function load()
    {
        global $wpdb;
        //TODO: Eliminate this
        $result = $wpdb->get_results("SELECT wp_posts.ID, wp_postmeta.meta_key, wp_postmeta.meta_value FROM wp_posts, wp_postmeta WHERE
                                      wp_posts.post_type = 'crt_sh_qty_conf' AND wp_postmeta.post_id = wp_posts.ID
                                      AND wp_postmeta.meta_key = 'enableShipping'
                                      group by wp_posts.ID");
        $storeConfig = $result[0];

        $status = 'off';

        if ($storeConfig->meta_key == 'enableShipping') {
            $status = $storeConfig->meta_value;
        }

        $ids[] = $wpdb->get_results("SELECT ID from wp_posts WHERE post_type='crt_sh_qty'");

        $meta = array();
        $meta['enableShipping'] = $status;

        $i = 0;
        foreach ($ids[0] as $id) {
            $meta['list'][$i] = get_post_meta($id->ID);
            $meta['list'][$i]['id'] = $id->ID;
            $i++;
        }
        return $meta;
    }

    static function loadConfig()
    {
        global $wpdb;
        $result['post'] = $wpdb->get_results("SELECT * FROM wp_posts WHERE post_type = 'crt_sh_qty_conf'");
        $result['meta'] = get_post_meta($result['post'][0]->ID);
        return $result;
    }

    static function parserRates($rates, $package)
    {
        //TODO: Make it Dynamic
        $includeTax = false;

        $qty_total = 0;
        foreach ($package['package']['contents'] as $key => $item) {
            $qty_total += $item['quantity'];
        }
        $element = 'cartrabbit_shipping_qty_based';
        $rateList = array();
        if (!empty($rates)) {
            foreach ($rates as $key => $value) {
                $id = $value['id'][0];
                if ($qty_total >= $value['minQty'][0]) {
                    $rateList['element'] = $element;
                    $rateList['id'] = $value['id'][0];
                    $rateList['name'] = $value['shippingName'][0];
                    $rateList['rate'] = (float)$value['rate'][0];
                    $rateList['extra'] = (float)$value['extra'][0];

                    $rateList['tax'] = 0;
                    if (!$includeTax) {
                        $rateList['total'] = (float)$value['extra'][0] + (float)$value['rate'][0];
                    } else {
                        $rateList['total'] = (float)$value['extra'][0] + (float)$value['rate'][0] + $rateList[$id]['tax'];
                    }
                }
            }
        }
        return $rateList;
    }

    static function remove($row)
    {
        wp_delete_post(trim($row));
    }

    static function processRates($package)
    {
        $package['rates']['cartrabbit_shipping_qty_based'] = self::parserRates(self::load()['list'], $package);
        return $package;
    }

    function processTaxRates($cost)
    {
        $rates = self::taxes();

        $total['sum'] = 0;
        $list = array();
        foreach ($rates as $id => $value) {
            $list[$id] = $cost / (100 + $value);
            $total['sum'] += $cost / (100 + $value);
        }
        $total['list'] = $list;
        return $total;
    }

    function taxes()
    {
        return array(
            'standard' => '5'
        );
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
}