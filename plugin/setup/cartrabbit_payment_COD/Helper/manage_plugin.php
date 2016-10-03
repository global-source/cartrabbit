<?php

class Manage_plugin
{
    static $element = 'cartrabbit_payment_cod';

    static function is_me($element, $strict = false)
    {
        return self::$element === $element;
    }

    static function is_available($element)
    {
        if (self::is_me($element)) {
            return self::load()['enabled'];
        } else {
            return $element;
        }
    }

    static function load()
    {
        $data = get_option(self::$element);
        $out = [];
        if (is_string($data)) {
            $result = json_decode($data, true);
            if (isset($result['payment'])) {
                $out = $result['payment'];
            }
        }
        return $out;
    }

    static function save($data)
    {
        if (!isset($data['payment']['enabled'])) {
            $data['payment']['enabled'] = 'off';
        }

        if (get_option(self::$element)) {
            update_option(self::$element, json_encode($data));
        } else {
            add_option(self::$element, json_encode($data));
        }

        /** Redirect to its Landing Page */
        wp_redirect(get_site_url() . '/wp-admin/admin.php?page=cartrabbit-config&tab=payment&opt=' . self::$element);
    }

    /**
     * @return string
     */

    static function loadShippingConfigurations()
    {
        $config = self::load();
        $path = __DIR__ . '/../View/payment.php';
        $html = self::processView($path, $config);
        return $html;
    }

    function processView($path, $data)
    {
        ob_start();
        $config = $data;
        include($path);
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

}