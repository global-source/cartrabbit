<?php

namespace CartRabbit\Models;

use CommerceGuys\Addressing\Repository\SubdivisionRepository;
use CommerceGuys\Intl\Country\CountryRepository;
use Flycartinc\Cart\Cart;
use Flycartinc\Order\Model\Order;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Corcel\UserMeta;
use CartRabbit\Helper\Rules;
use CartRabbit\Helper\Util;

/**
 * Class products
 * @package CartRabbit\Models
 */
class Checkout extends Eloquent
{

    /**
     * Cart constructor.
     */
    public function __construct()
    {

    }

    public static function getPayment()
    {
        $order = Checkout::validateOrder();

        if (!$order->cart_status) {
            $error['error_url'] = parent::redirectTo('cart', true);
            return $error;
        }
        $result = self::getPaymentPlugin();
        return $result;
    }

    public static function getPaymentPlugin()
    {
        $available = [];
        $paymentMethods = apply_filters('cartrabbit_payment_plugins', array());
        foreach ($paymentMethods as $key => $payment) {
            $available[$key] = apply_filters('cartrabbit_is_available', $key);
        }
        $result['paymentMethods'] = $paymentMethods;
        $result['available'] = $available;
        return $result;
    }

    public static function getShippingPlugin($quantity = false)
    {
        $shipping['shippingMethods'] = apply_filters('cartrabbit_shipping_plugins', array());
        foreach ($shipping['shippingMethods'] as $key => $shipping_method) {
            
            $shipping['available'][$key] = apply_filters('is_available', $key);
//            $shipping['rate'][$key] = apply_filters('cartrabbit_package_rates', $key);
        }
//        dd($shipping);
        return $shipping;
    }


    /**
     * Return All Guest Session Records
     */
    public static function checkoutSession()
    {
        global $current_user;
        $customer = new Customer();

        $session['billing_address'] = array_get(Session()->get('customer', []), 'billing_address', false);
        $session['shipping_address'] = array_get(Session()->get('customer', []), 'shipping_address', false);

        if (!empty($session['billing_address']) && $session['billing_address']) {
            $session['billing_address']['states'] = json_decode(Util::getStatesByCountryCode(array_get($session['billing_address'], 'country', 'none')), true);
        }
        if (!empty($session['shipping_address']) && $session['shipping_address']) {
            $session['shipping_address']['states'] = json_decode(Util::getStatesByCountryCode(array_get($session['shipping_address'], 'd_country', 'none')), true);
        }

        $session['uaccount'] = Session()->get('uaccount', 'noRecord');
        if ($current_user->ID) {
            $session['uaccount'] = $current_user->ID;
        }
        $session['paymentMethod'] = $customer->getPaymentMethod();
        $session['shippingMethod'] = array_first(Session()->get('chosen_shipping_methods', false));

        $status = 'false';
        if (!empty($session['billing_address']) and !empty($session['shipping_address'])) {
            $status = 'true';
        }
        $session['address_status'] = $status;

        return $session;
    }

    public static function validateCheckout($order)
    {
        $shipping = $order->shipping_info;
//        dd($shipping);
        $session = Session()->all();
        $error_log = [];
        if ($session['uaccount'] != 'noRecord') {
            if (isset($session['customer'])) {
                if (isset($session['customer']['billing_address']) and isset($session['customer']['shipping_address'])) {
                    if (isset($session['chosen_shipping_methods'])) {
                        if (isset($session['customer']['payment_method'])) {
                            //
                        } else {
                            $error_log = 'Please Select Payment Method !';
                        }
                        //
                    } else {
                        if ($shipping['isEnable'] == true) {
                            if ($shipping['needShipping'] == true) {
                                if (Settings::get('shipping_dont_allow_if_no_shipping') == 'yes') {
                                    $error_log = 'Please Select Shipping Method !';
                                }
                            }
                        }
                    }
                    //
                } else {
                    $error_log = 'Please Select Your Address !';
                }
                //
            } else {
                $error_log = 'Please Select Your Address !';
            }
            //
        } else {

            $error_log = 'Account Not identified, Please Check your account !';
        }
        return $error_log;
    }

    public static function validateOrder()
    {
        $order = new Order();
        $order->initOrder();
        $order->vertifyStock();
        return $order;
    }

    public static function verifyCartItems(&$order)
    {
        //TODO: Here, 502 Gateway error is occurred in frequently, verify it.
    }

    public static function getContents()
    {
        $order = new Order();
        $order->initOrder();
        return $order;
    }

    /**
     * To Respond Ajax based Shipping Call
     *
     * @return \CartRabbit\Controllers\display
     */
    public static function loadShippingForm()
    {
        $order = Checkout::validateOrder();
        if (!$order->cart_status) {
            $error['error_url'] = parent::redirectTo('cart', true);
            return $error;
        }

        $shippingMethods = self::getShippingPlugin();
//        $config['shippingEnabled'] = Settings::get('shipping_enable', 'no');
//        $config['productRequireShipping'] = Shipping::productRequireShipping();
//        $config['shipping_dont_allow_if_no_shipping'] = Settings::get('shipping_dont_allow_if_no_shipping', 'no');
        return $shippingMethods;
    }

    /**
     * To Respond Ajax based Order Summery Call
     *
     * @return \CartRabbit\Controllers\display
     */
    public static function loadOrderSummery()
    {
        $order = new Order();
        $order->initOrder();
        $orderSummery = $order;

        return $orderSummery;
    }
}