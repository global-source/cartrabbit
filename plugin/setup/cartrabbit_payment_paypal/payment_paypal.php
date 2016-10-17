<?php

require_once WP_PLUGIN_DIR . '/cartrabbit/vendor/flycartinc/order/src/Model/Order.php';
use Flycartinc\Order\Model\Order;

/** This Class is used for managing basic plugin interaction */
class Payment_paypal
{

    static $element = 'cartrabbit_payment_paypal';

    static function is_me($element)
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

    static function register_plugin($list)
    {
//        $data = Manage_plugin::load();
        if (!isset($data)) {
            $list[self::$element] = 'PayPal';
        }
//        else {
//            $list['cartrabbit_payment_paypal'] = $data['cod_payment_title'];
//        }
        return $list;
    }


    static function register_plugin_menu($items)
    {
        $items['PayPal'] = '?page=cartrabbit-config&tab=payment&opt=' . self::$element;

        return $items;
    }

    static function load_plugin_data($element)
    {
        if (self::is_me($element)) {
            return self::loadPaypalConfigurations($element);
        } else {
            return $element;
        }
    }

    /**
     * @return string
     */

    static function loadPaypalConfigurations($element)
    {
        if (self::is_me($element)) {
            $config = self::load();
            $path = __DIR__ . '/view/payment.php';
            $html = self::processView($path, $config);
            return $html;
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

    function save_plugin_config($data)
    {
        if (self::is_me($data['payment']['plugin'])) {
            self::save($data);
        } else {
            return $data;
        }
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

    static function get_post_by($field, $value, $output = OBJECT)
    {
        global $wpdb;
        $post = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE " . $field . " = %s AND post_type='cartrabbit_payment'", $value));
        if ($post)
            return get_post($post, $output);

        return null;
    }

    static function pre_payment_form($data)
    {
        $element = $data['plugin_name'];
        if (self::is_me($element)) {
            $data['config'] = self::load();

            // Switch Between Sandbox and Main Account for Payment.
            if ($data['config']['payment_paypal_sandbox'] == 'yes') {
                $data['config']['payment_paypal_merchant_id'] = $data['config']['payment_paypal_sandbox_merchant_merchant_id'];
            }

            $path = __DIR__ . '/view/payment_form.php';
            $data['result'] = self::processView($path, $data);
        }
        return $data;
    }

    static function payment_process($data)
    {
        $element = $data['payment_name'];
        if (self::is_me($element)) {
            $html = '';
            switch ($data['paction']) {
                case 'display':
                    $html = "on after text from plugin data";
                    break;
                case 'cancel':
                    $html = "on cancel text from plugin data";
                    break;
                case 'process':
                    self::_process($data);
                    break;
                default:
                    break;
            }
        } else {
            return $data;
        }
    }

    static function _process($data)
    {
        if (empty($data)) {
            return $data;
        }
        $errors = array();
        $config = self::load();

        /** For Paypal */
        $status = $data['payment_status'];

        $order_id = $data['custom'];

        $order = Order::where('unique_order_id', $order_id)->get()->first();

        $order->setOrderId($order_id);

        self::verifyOrder($order, $config, $data);
        if ($config['payment_paypal_validate_ipn'] == 'yes') {
//            if($data['paction'] == 'process')
            $ipn_error = self::_validateIPN($data);
        }

        $order->setTransactionId($data['txn_id']);
        $order->setTransactionData(json_encode($data));

        if ($ipn_error == 'INVALID') {
            //TODO: Send Error Mail
            $order->updateOrderStatus('failed');
        } elseif (strtoupper($status) == 'COMPLETED') {
            Order::emptyCart();
            $order->paymentComplete('completed');
        } elseif (strtoupper($status) == 'PENDING') {
            $order->updateOrderStatus('pendingPayment');
        } elseif (strtoupper($status) == 'CANCEL') {
            $order->updateOrderStatus('Cancel');
        } else {
            //TODO: Send Error Mail
            $order->updateOrderStatus('failed');
        }
        return 'display';
    }

    /**
     * Validates the IPN data
     *
     * @param array $data
     * @return string Empty string if data is valid and an error message otherwise
     * @access protected
     */
    static function _validateIPN($data)
    {
        $paypal_url = self::_getPostUrl(true);

        $request = 'cmd=_notify-validate';

        foreach ($data as $key => $value) {
            if (in_array($key, array('paction', 'payment_name', 'plugin_name'))) {
                continue;
            }
//            if (is_string($value)) {
            $request .= '&' . $key . '=' . urlencode(html_entity_decode((string)$value, ENT_QUOTES, 'UTF-8'));
//            }
        }

        $curl = curl_init($paypal_url);

        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl);

        $res = '';
        if ((strcmp($response, 'VERIFIED') == 0 || strcmp($response, 'UNVERIFIED') == 0)) {
            $res = 'VALID';
        } elseif (strcmp($response, 'INVALID') == 0) {
            $res = 'INVALID';
        }

        return $res;
    }

    static function verifyOrder($order, $config, $data)
    {
        //Order ID Empty and Matched
        if (!empty($order->unique_order_id) && $order->unique_order_id > 0 && $order->unique_order_id == $data['custom']) {
            $errors[] = 'Invalid Order Id';
        }
        if ($config['payment_paypal_merchant_id'] != $data['receiver_id']) {
            $errors[] = 'Merchant ID Mismatch';
        }
        //TODO: Validate mc_gross [Order Total]


    }

    /**
     * Gets the Paypal gateway URL
     *
     * @param boolean $full
     * @return string
     * @access protected
     */
    static function _getPostUrl($full = true)
    {
//        $url = $this->params->get('sandbox') ? 'www.sandbox.paypal.com' : 'www.paypal.com';
        $url = 'www.sandbox.paypal.com';
        if ($full) {
            $url = 'https://' . $url . '/cgi-bin/webscr';
        }

        return $url;
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
