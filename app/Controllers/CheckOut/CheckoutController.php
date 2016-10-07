<?php

namespace CartRabbit\Controllers\CheckOut;

use CommerceGuys\Intl\Country\CountryRepository;
use Flycartinc\Order\Model\Order;
use CartRabbit\Helper;
use Herbert\Framework\Http;
use CartRabbit\Controllers\BaseController;
use CartRabbit\Models\Checkout;
use CartRabbit\Models\Cart;
use CartRabbit\Models\Customer;
use CartRabbit\Models\Orders;
use CartRabbit\Models\Settings;
use CartRabbit\Models\Shipping;

/**
 * Class BaseController
 * @package CartRabbit\Controllers
 */
class CheckoutController extends BaseController
{
    /**
     * To Initiate the Checkout Page
     *
     * @return \CartRabbit\Controllers\display
     */
    public function init_CheckOut(Http $http)
    {
        parent::__construct();
        $order = new Order();
        $order->initOrder();
        if (!$order->cart_status) {
            return parent::redirectTo('cart', true);
        }

        $general['countries'] = (new CountryRepository())->getList();
        $general['orderSummery'] = $order;
        $general['countries'] = (new CountryRepository())->getList();
        $general['shipping'] = Checkout::loadShippingForm();
        $general['payment'] = Checkout::getPayment();

        $billing_address = Customer::getBillingAddresses();
        $session = Checkout::checkoutSession();

        $currency = new Helper\Currency();

        return parent::view('Site.Checkout.checkOut', compact('general', 'content', 'billing_address', 'session', 'cart_items', 'currency'));
    }

    public function placeOrder(Http $http)
    {
        /** Once the payment process is initiated, then Checkout page is Redirect to Payment Page. */
        $form = $this->preConfirmPayment($http);
        return parent::view('Site.Payment.paymentView', compact('form'));
    }

    public function loadAddressInfo()
    {
        $session = Checkout::checkoutSession();
        return parent::view('Site.Checkout.Panels.AddressInfo', compact('general', 'billing_address', 'session'));
    }

    /**
     * [DEPRECATED]
     * @return \CartRabbit\Controllers\display
     */
    public function getOrderSummery()
    {
        $currency = new Helper\Currency();
        $general['orderSummery'] = Checkout::loadOrderSummery();
        $session = Checkout::checkoutSession();
        $shipping = Checkout::loadShippingForm();
        return parent::view('Site.Checkout.Panels.OrderSummery', compact('general', 'shipping', 'currency', 'session'));
    }

    /**
     * @return mixed
     */
    public function loadCheckoutData(Http $http)
    {
        $currency = new Helper\Currency();
        $general['orderSummery'] = Checkout::loadOrderSummery();
        $session = Checkout::checkoutSession();
        $general['shipping'] = Checkout::loadShippingForm();

        $content = Checkout::getContents();
        $content['countries'] = (new CountryRepository())->getList();
        $general['payment'] = Checkout::getPayment();

        $shipping_form = parent::view('Site.Checkout.Panels.shippingMethod', compact('general', 'session', 'content', 'currency'));

        $summery_form = parent::view('Site.Checkout.Panels.OrderSummery', compact('general', 'currency', 'session', 'content'));

        $response['summery'] = $summery_form->getBody();
        $response['shipping'] = $shipping_form->getBody();
        return $response;
    }

    /**
     *
     */
    public function initPrePayment()
    {
        $order = new Order();
        $order->initOrder();

        if (!$order->cart_status) {
            $error['error_url'] = parent::redirectTo('cart', true);
            return $error;
        }

        $error_log = Checkout::validateCheckout($order);
        if (!empty($error_log)) {
            $error['error_log'] = $error_log;
            return $error;
        }

    }

    /**
     * @param $http
     * @return mixed
     */
    public function preConfirmPayment($http)
    {
        $data['plugin_name'] = Session()->get('customer')['payment_method'];
        $data['result'] = array();
        $data['shipping']['address'] = Session()->get('customer')['shipping_address'];
        $data['plugin']['post'] = $http->all();

        $order = new Order();
        $order->initOrder();
        $orderSummery = $order;

        /** Create/Update Order */
        $id = Orders::saveOrder($orderSummery);

        $data['plugin']['invoice_no'] = Settings::getInvoiceNo();
        $data['plugin']['order_id'] = $id;
        $data['plugin']['order'] = $orderSummery;
        $data['plugin']['currency'] = (new Helper\Currency())->getCurrency()->getCurrencyCode();
        $form = apply_filters('cartrabbit_prepayment_form', $data);
        $form = $form['result'];
        return $form;
    }

    /**
     * @param Http $http
     * @return \Herbert\Framework\Response
     */
    public function ConfirmPayment(Http $http)
    {
        $data = $http->all();
        $data['plugin_name'] = $http->get('customer')['payment_method'];
        $data['result'] = array();

        /** Clear Order Session */
        Customer::clearOrderSession();

        if ($data) {
            do_action('cartrabbit_post_payment', $data);
        }

        $page = (new Settings())->getConfigurationMeta('page_to_thank');

        $page = apply_filters('cartrabbit_thankyou_page', $page);

        return parent::redirect(get_permalink($page[0]), true);
    }

    /**
     * To Get Subdivides by Country Code [AJAX]
     *
     * @param Http $http Instance to Access Post Form Data's
     * @return array of Subdivides
     */
    public function getSubdivisions(Http $http)
    {
        $country = ($http->has('country')) ? $http->get('country') : null;
        return Settings::getSubdivisions($country);
    }

    /**
     * To Save User Address
     *
     * @param Http $http Instance to Access Post Form Data's
     * @return Array of result
     */
    public function saveUserAddress(Http $http)
    {
        return (new Customer())->saveUserAddress($http);
    }

    /**
     * To Check Whether Account is Exist or Not
     *
     * @param Http $http Instance to Access Post Form Data's
     * @return string Account Status
     */
    public function checkAccountIsExist(Http $http)
    {
        return (new Customer())->checkAccount($http);
    }

    public function setShippingMethod(Http $http)
    {
        return Customer::updateMethod($http);
    }

    public function setPaymentMethod(Http $http)
    {
        return Customer::updateMethod($http);
    }

    public static function getOrder()
    {

    }

    /**
     * To Sign In the User Account
     *
     * @param Http $http Instance to Access Post Form Data's
     */
    public function userSignIn(Http $http)
    {
        $userName = ($http->has('uname')) ? $http->get('uname') : null;
        $password = ($http->has('pass')) ? $http->get('pass') : null;

        if (Customer::isValidUser($userName, $password)) {
            Customer::userSignIn($userName, $password);
            Session()->set('uaccount', 'login');
        } else {
            $response['error'] = 'Invalid User !';
            return $response;
        }
    }

    /**
     * To Sign as Guest Account.
     *
     * NOTE : Here, Guest with name of "__unknown__" is Reserved, so don't allow.
     *
     * @param Http $http Instance to Access Post Form Data's
     */

    public function guestSignIn(Http $http)
    {
        $status = $http->has('status') ? $http->get('status') : false;
        $user = $http->has('user') ? $http->get('user') : 'noUser';
        Session()->set('uaccount', 'guest');
        Customer::guestSignIn($user, $status);
    }

    /**
     * To Sign Out the User's Account by Calling Wordpress "Logout()" Function
     *
     */
    public function userSignOut()
    {
        wp_logout();
    }

    /**
     * To Register the User's Account
     *
     * @param Http $http Instance to Access Post Form Data's
     * @return True or False [Status of User Registration]
     */
    public function userSignUp(Http $http)
    {
        $email = $http->has('uname') ? $http->get('uname') : null;
        $password = $http->has('pass') ? $http->get('pass') : null;
        $re_password = $http->has('pass') ? $http->get('re_pass') : null;
        if ($password === $re_password and Helper\Util::validateEmail($email)) {
            $user = new Customer();
            $user->userRegistration($email, $password);
        } else {
            $response['error'] = 'Invalid User';
            return $response;
        }
    }

    /**
     * To Set Billing address for Shipping Products
     *
     * @param Http $http Instance to Access Post Form Data's
     */
    public function setAddress(Http $http)
    {
        $billing = trim($http->get('billing_address', false));
        $shipping = trim($http->get('shipping_address', false));
        $same_as_shipping = trim($http->get('diff_shipping_address', false));

        $list = [];
        if ($same_as_shipping == 'yes') {
            $list['billing_address'] = Customer::getAddressByID($billing);
            $list['shipping_address'] = Customer::getAddressByID($shipping);
        } else {
            if ($billing) {
                $list['billing_address'] = Customer::getAddressByID($billing);
                $list['shipping_address'] = Customer::getAddressByID($billing);
            }
        }

        $customer = Session()->get('customer');
        foreach ($list as $index => $address) {
            $customer[$index] = $list[$index];
        }
        Session()->set('customer', $customer);
    }

    /**
     *To Removed Assigned Billing Address
     */
    public function removeBillingAddress()
    {
        (new Customer())->removeBillingAddress();
    }

    /**
     * To Set Billing address for Shipping Products
     *
     * @param Http $http Instance to Access Post Form Data's
     */
    public function setDeliveryAddress(Http $http)
    {
        if ($http->has('addr_id')) {
            (new Customer())->setDeliveryAddress($http->get('addr_id'));
        }
    }

    /**
     *To Removed Assigned Billing Address
     */
    public function removeDeliveryAddress()
    {
        (new Customer())->removeDeliveryAddress();
    }
}