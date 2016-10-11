<?php
/**
 * Created by PhpStorm.
 * User: elamathi
 * Date: 23/06/16
 * Time: 8:48 PM
 */

namespace CartRabbit\Models;

use CartRabbit\Helper\GeoIP;
use Corcel\User;
use Illuminate\Support\Facades\Session;
use Flycartinc\Order\Model\Order as Order;
use Corcel\UserMeta;
use CartRabbit\Helper\Rules;
use CartRabbit\Helper\Util;

/**
 * Class Customer
 * @package CartRabbit\Models
 */
class Customer extends User
{
    /**
     * @var array
     */
    protected $_data = array();

    /**
     * @var string
     */
    protected $table = 'users';

    /**
     * @var array
     */
    protected $customer_address = array();

    /**
     * Customer constructor.
     * @param array $attributes
     */
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);

        if (empty($this->_data)) {
            $this->init();
        }
    }

    /**
     *
     */
    public function init()
    {
        $this->_data = array(
            'postcode' => '',
            'city' => '',
            'address_1' => '',
            'address_2' => '',
            'state' => '',
            'country' => '',
            'shipping_postcode' => '',
            'shipping_city' => '',
            'shipping_address_1' => '',
            'shipping_address_2' => '',
            'shipping_state' => '',
            'shipping_country' => '',
            'is_vat_exempt' => false,
            'calculated_shipping' => false
        );

        if (is_user_logged_in() && Orders::get_user_data()) {
            foreach ($this->_data as $key => $value) {
                $meta_value = get_user_meta(get_current_user_id(), (false === strstr($key, 'shipping_') ? 'billing_' : '') . $key, true);
                $this->_data[$key] = $meta_value ? $meta_value : array_get($this->_data, $key, '');
            }
        }

        if (empty($this->_data['country'])) {
            $this->_data['country'] = $this->get_default_country();
        }

        if (empty($this->_data['shipping_country'])) {
            $this->_data['shipping_country'] = array_get($this->_data, 'country', '');
        }

        if (empty($this->_data['state'])) {
            $this->_data['state'] = $this->get_default_state();
        }

        if (empty($this->_data['shipping_state'])) {
            $this->_data['shipping_state'] = array_get($this->_data, 'state', '');
        }

        $this->customer_address = array_get(Session()->get('customer', []), 'billing_address', '');
    }

    /**
     *
     */
    public static function initAddress()
    {
//        if (self::verifyUserSession() == false) {
        self::updateLocation();
//        }
    }

    /**
     *
     */
    public static function updateLocation()
    {
        global $current_user;
        $session = Session()->get('customer', 0);
        if ($session == 0 && $current_user->ID == 0) {
            /** Update User Address. */
            $location = GeoIP::getLocation($_SERVER['REMOTE_ADDR']);
            if (isset($location) && isset($location->country)) {
                /** Location should have country code. */
                if (!is_null($location->country)) {
                    $billing_address['city'] = object_get($location, 'city', '');
                    $billing_address['country'] = object_get($location, 'country', '');
                    $billing_address['postalCode'] = object_get($location, 'postalCode', '');
                    if ($location->state) {
                        $billing_address['zone'] = object_get($location, 'country', '') . '-' . object_get($location, 'state', '');
                    }
                    $customer['billing_address'] = $billing_address;
                    $customer['shipping_address'] = $billing_address;
                    Session()->set('customer', $customer);
                }
            }
        }
    }

    public static function clearOrderSession()
    {
        Session()->remove('chosen_shipping_methods');
        Session()->remove('shipping_method_counts');
        $customer = Session()->get('customer');
        if (isset($customer['payment_method'])) {
            unset($customer['payment_method']);
        }
        Session()->set('customer', $customer);
        Session()->remove('order_id');
    }

    /**
     * @return bool
     */
    public static function verifyUserSession()
    {
        if (is_user_logged_in()) {
            /** Attempt to retrieve the User ID */
            $id = get_current_user_id();
            /** Get Corresponding User's Cart item meta */

            if (!is_null($id)) {
                self::updateUserAddress($id);
                $status = true;
            } else {
                $status = false;
            }
            return $status;
        }
    }

    /**
     *
     */
    public static function updateUserAddress($user_id)
    {
        $billing_address = self::getUserAddress($user_id);
        if (!$billing_address) return false;
//        if (empty(self::getBillingAddress())) {
        $customer['billing_address'] = $billing_address;
        $customer['shipping_address'] = $billing_address;
        Session()->set('customer', $customer);
//        }
    }

    public static function saveAddressFromSession()
    {
        global $current_user;
        $user_id = $current_user->ID;
        $address['billing_address'] = self::getBillingAddress();
        $address['shipping_address'] = self::getShippingAddress();

        foreach ($address as $index => $item) {
            self::createAddress($user_id, $item, $index);
        }

        if ($user_id) {
            /** To Clearing the Guest Session after user Logged In. */
            self::clearGuestSession();
        }

    }

    public static function isCustomerHaveUnCompletedOrders()
    {
        $res = 0;
        try {
            $res = self::getCustomerOrderWithStateOf('new');
        } catch (\Exception $e) {
            //
        }
        return boolval($res);
    }

    public static function getCustomerOrderWithStateOf($state, $user = null)
    {
        if (is_null($user)) {
            if (is_user_logged_in()) {
                /** Attempt to retrieve the User ID */
                $user = get_current_user_id();
            }
        }
        $res = 0;
        try {
            $res = Order::where('order_status', $state)->where('order_user_id', $user)->get();
        } catch (\Exception $e) {
            //
        }
        return $res;
    }

    /**
     * @param $id
     * @return array|mixed
     */
    public static function getUserAddress($id = null)
    {
        if (is_null($id)) {
            if (is_user_logged_in()) {
                /** Attempt to retrieve the User ID */
                $id = get_current_user_id();
            }
        }

        try {
            $user = self::find($id);
            if ($user) {
                $address = [];
                $billing_address = $user
                    ->meta()
                    ->where('meta_key', 'like', 'billing_address_%')
                    ->orderBy('umeta_id', 'desc')
                    ->first()->meta_value;
                if ($billing_address) {
                    $address = json_decode($billing_address, true);
                }
            }
        } catch (\Exception $e) {

        }
        return $address;
    }

    /**
     * __get function.
     *
     * @param string $property
     * @return string
     */
    public function __get($property)
    {
        if ('address' === $property) {
            $property = 'address_1';
        }
        if ('shipping_address' === $property) {
            $property = 'shipping_address_1';
        }
        return isset($this->_data[$property]) ? $this->_data[$property] : '';
    }

    /**
     * Get default country for a customer.
     *
     * @return string
     */
    public function get_default_country()
    {
        $default = Settings::get('store_country');
        return $default;
    }

    /**
     * Get default state for a customer.
     *
     * @return string
     */
    public function get_default_state()
    {
        $default = Settings::get('store_state');
        return $default;
    }

    /**
     * Has calculated shipping?
     *
     * @return bool
     */
    public function has_calculated_shipping()
    {
        return !empty($this->calculated_shipping);
    }

    /**
     * @return mixed
     */
    public function get_shipping_country()
    {
        return array_get($this->customer_address, 'country', '');
    }

    /**
     * @return mixed
     */
    public function get_shipping_state()
    {
        return array_get($this->customer_address, 'zone', '');
    }

    /**
     * @return mixed
     */
    public function get_shipping_postcode()
    {
        return array_get($this->customer_address, 'postalCode', '');
    }

    /**
     * @return mixed
     */
    public function get_shipping_city()
    {
        return array_get($this->customer_address, 'city', '');
    }

    /**
     * @param $default
     * @return array
     */
    public function getBillingAddress($default = false)
    {
        if ($default) {
            return $this->customer_address;
        }
        $customer_address = [];
        try {
            $customer_address = array_get(Session()->get('customer', []), 'billing_address', '');

            if (!$customer_address) {
                if (!empty($this)) {
                    $customer_address = $this->customer_address;
                }
            }
        } catch (\Exception $e) {
            //
        }
        return $customer_address;
    }

    /**
     * @return array
     */
    public function getShippingAddress($default = false)
    {
        if ($default) {
            return $this->customer_address;
        }
        $customer_address = [];
        try {
            $customer_address = array_get(Session()->get('customer', []), 'shipping_address', '');
            if (!$customer_address) {
                if (!empty($this)) {
                    $customer_address = $this->customer_address;
                }
            }
        } catch (\Exception $e) {
            //
        }
        return $customer_address;
    }

    /**
     * @param $field
     * @param array $default
     * @return array
     */
    public static function getField($field, $default = [])
    {
        $customer = Session()->get('customer', false);
        if ($customer != false) {
            if (isset($customer[$field])) {
                return $customer[$field];
            }
        }
        return $default;
    }

    public static function updateMethod($http)
    {
        $shipping = $http->get('shipping_method');
        $payment = $http->get('payment_method');
        if ($shipping) {
            Session()->set('chosen_shipping_methods', array($shipping));
        }
        if ($payment) {
            Customer::setSession('payment_method', $payment);
        }
        return true;
    }

    /**
     *
     */
    public function get_shipping_address()
    {
        return;
    }

    /**
     *
     */
    public function get_shipping_address_2()
    {
        return;
    }

    /**
     * @return bool
     */
    public function is_vat_exempt()
    {
        return false;
    }

    /**
     * Gets the state from the current session.
     *
     * @return string
     */
    public function get_state()
    {
        return $this->state;
    }

    /**
     * Gets the country from the current session.
     *
     * @return string
     */
    public function get_country()
    {
        return $this->country;
    }

    /**
     * Gets the postcode from the current session.
     *
     * @return string
     */
    public function get_postcode()
    {
        return empty($this->postcode) ? '' : wc_format_postcode($this->postcode, $this->get_country());
    }

    /**
     * Get the city from the current session.
     *
     * @return string
     */
    public function get_city()
    {
        return $this->city;
    }

    /**
     * Gets the address from the current session.
     *
     * @return string
     */
    public function get_address()
    {
        return $this->address_1;
    }

    /**
     * Gets the address_2 from the current session.
     *
     * @return string
     */
    public function get_address_2()
    {
        return $this->address_2;
    }


    /**
     * @return mixed
     */
    public function get_taxable_address()
    {
        $tax_based_on = Settings::get('tax_calculate_with');

        // Check shipping method at this point to see if we need special handling
        if (true == apply_filters('sp_apply_base_tax_for_local_pickup', true) && (new Order())->needs_shipping() && sizeof(array_intersect(Session()->get('chosen_shipping_methods', array()), apply_filters('sp_local_pickup_methods', array('local_pickup')))) > 0) {
            $tax_based_on = 'shopBased';
        }

        if ('shopBased' === $tax_based_on) {

            $country = Settings::get('store_country');
            $state = Settings::get('store_state');
            $postcode = Settings::get('store_postalcode');
            $city = Settings::get('store_city');

        } elseif ('customerBilling' === $tax_based_on) {
            $country = $this->get_country();
            $state = $this->get_state();
            $postcode = $this->get_postcode();
            $city = $this->get_city();

        } else {
            $country = $this->get_shipping_country();
            $state = $this->get_shipping_state();
            $postcode = $this->get_shipping_postcode();
            $city = $this->get_shipping_city();
        }

        return apply_filters('sp_customer_taxable_address', array($country, $state, $postcode, $city));
    }

    /**
     * Is customer outside base country (for tax purposes)?
     *
     * @return bool
     */
    public function is_customer_outside_base()
    {
        list($country, $state) = $this->get_taxable_address();

        if ($country) {


            if (Settings::get('store_country') !== $country) {
                return true;
            }

            if (Settings::get('store_state') && Settings::get('store_country') !== $state) {
                return true;
            }

        }

        return false;
    }

    /**
     * @return null
     */
    public static function getBillingAddresses()
    {
        global $current_user;
        $user = $current_user->ID;

        $billing_address = null;
        if ($user) {
            $addresses = UserMeta::where('user_id', $user)
                ->where('meta_key', 'LIKE', '%_address_%')
                ->where('meta_value', '!=', '[]')
                ->pluck('meta_value', 'meta_key');

            foreach ($addresses as $key => $address) {
                $billing_address[$key] = (array)json_decode($address);
            }
        }
        $billing_address['active'] = array_get(Session()->get('customer', []), 'billing_address', false);
        return $billing_address;
    }

    /**
     * To Remove entire Guest Session
     */
    public static function clearGuestSession()
    {
        Session()->remove('guest');
        Session()->remove('guestMail');
    }

    /**
     *
     */
    public static function clearTempSession()
    {
        self::removeMethod();
        self::removeAddress();
    }

    /**
     * @param null $type
     */
    public static function removeAddress($type = null)
    {
        $customer = Session()->get('customer', 0);
        if ($customer) {
            if (!is_null($type)) {
                switch ($type) {
                    case 'billing':
                        if (isset($customer['billing_address'])) unset($customer['billing_address']);
                        break;
                    case 'shipping':
                        if (isset($customer['shipping_address'])) unset($customer['shipping_address']);
                        break;
                }
            } else {
                if (isset($customer['shipping_address'])) unset($customer['shipping_address']);
                if (isset($customer['billing_address'])) unset($customer['billing_address']);
            }
            Session()->set('customer', $customer);
        }
    }

    /**
     * @param null $type
     */
    public static function removeMethod($type = null)
    {
        $customer = Session()->get('customer', 0);
        if ($customer) {
            if (!is_null($type)) {
                switch ($type) {
                    case 'shipping':
                        if (isset($customer['shipping_method'])) unset($customer['shipping_method']);
                        break;
                    case 'payment':
                        if (isset($customer['payment_method'])) unset($customer['payment_method']);
                        break;
                }
            } else {
                if (isset($customer['shipping_method'])) unset($customer['shipping_method']);
                if (isset($customer['payment_method'])) unset($customer['payment_method']);
            }
            Session()->set('customer', $customer);
        }
    }

    /**
     * @return bool
     */
    public static function getShippingMethod()
    {
        $shipping = Session()->get('chosen_shipping_methods', false);
        if ($shipping) {
            $shipping = $shipping[0];
        }
        return $shipping;
    }

    /**
     * @return bool
     */
    public static function getPaymentMethod()
    {
        $customer = Session()->get('customer', 0);
        if ($customer) {
            if (isset($customer['payment_method'])) {
                return $customer['payment_method'];
            }
        }
        return false;
    }

    /**
     * @param $address_id
     * @return array|null
     */
    public static function getAddressByID($address_id)
    {
        $billing_address = null;
        global $current_user;

        if ($address_id) {
            $address = \User::find($current_user->ID)
                ->meta()
                ->where('meta_key', trim($address_id))
                ->pluck('meta_value')->first();
            if ($address) {
                $billing_address = (array)json_decode($address);
            }
        }

        return $billing_address;
    }

    /**
     * To Remove the Setted Billing Address from Session
     */
    public function removeBillingAddress()
    {
        $session = Session()->get('customer', []);
        if (!empty($session)) {
            if (isset($session['billing_address'])) {
                unset($session['billing_address']);
                Session()->set('customer', $session);
            }
        }
    }

    /**
     * To Set the Address to Process Billing
     * @param $address_id
     */
    public function setDeliveryAddress($address_id)
    {
        $address = self::getAddressByID($address_id);
        $address['id'] = $address_id;
        self::setSession('shipping_address', $address);
    }

    /**
     * To Remove the Setted Billing Address from Session
     */
    public function removeDeliveryAddress()
    {
        $session = Session()->get('customer', []);
        if (!empty($session)) {
            if (isset($session['shipping_address'])) {
                unset($session['shipping_address']);
                Session()->set('customer', $session);
            }
        }
    }

    public static function onUserLogin($user_id)
    {
        if (Session()->get('uaccount', 'nope') == 'guest') {
            Customer::saveAddressFromSession();
        }

        Session()->set('uaccount', 'login');

        Session()->set('user_id', $user_id);
        Session()->remove('guest');
        Session()->remove('guestMail');
        Session()->remove('chosen_shipping_methods');
    }

    public static function onUserLogout()
    {
        Session()->set('uaccount', 'noRecord');
        Session()->remove('customer');
        Session()->remove('chosen_shipping_methods');
        Session()->remove('user_id');
    }

    /**
     * @param $field
     * @param $value
     */
    public static function setSession($field, $value)
    {
        $customer = Session()->get('customer', 0);
        if (!$customer) {
            $customer = [];
        }
        $customer[$field] = $value;

        Session()->set('customer', $customer);
    }

    /**
     * To Save User's Billing Address
     *
     * @param object $http Instance to Access Post Form Data's
     * @return array $validation result of errors
     */
    public function saveUserAddress($http)
    {
        global $current_user;
        /** Type Of Address */
        $address_type = $http->get('addr_type', 'shipping_address');

        $force_set = $http->get('force_set', false);

        if ($address_type == 'billing_address') {
            /** For New Billing Address Validation */
            $error = Util::validate($http, Rules::checkout_customer_billing_address());
        } else {
            /** For New Delivery Address Validation */
            $error = Util::validate($http, Rules::checkout_customer_shipping_address());
        }

        $validation['success'] = 1;

        if (!empty($error)) {
            $validation['success'] = 0;
            $validation['error'] = $error;
            return (array)$validation;
        }

        $data = $http->all();

        $user = $current_user->ID;
        $uaccount = Session()->get('uaccount', 'guest');

        /** "Guest" is Identified by Session of Guest Identifier "is_guest" and User Login Status with Wordpress */
        if ($uaccount == 'guest' && $user == 0) {

            self::setSession($address_type, $data);
            self::setSession($address_type . '_verified', boolval($validation['success']));

            /** Implement "Force Set" to assign address billing address as delivery address also. */
            if ($force_set == 'true') {
                $address_type = ($address_type == 'billing_address') ? 'shipping_address' : 'billing_address';
                self::setSession($address_type, $data);
                self::setSession($address_type . '_verified', boolval($validation['success']));
            }
        } else {
            $data = $http->all();
            self::createAddress($user, $data, $address_type);
        }
        return $validation;
    }

    public static function createAddress($user_id, $data, $address_type)
    {
        if ($user_id == 0) {
            global $current_user;
            $user_id = $current_user->ID;
        }
        //TODO: Verify this
        if (!empty($data) and $data != '[]' and is_array($data) and $user_id) {
            $user = User::find($user_id);
            $count = $user->meta()->where('meta_key', 'like', '%_address_%')->get()->count();
            $key = $address_type . '_' . $count;
            $data = json_encode($data);

            $user->meta->$key = $data;
            $user->save();
        }
    }

    /**
     * To Check, Account is Exist or Not
     * @param $http Instance for Access Form data
     * @return string Status of the Account
     */
    public function checkAccount($http)
    {
        $account = $http->get('account', null);
        $res = 'DONT-ALLOW';

        if (!is_null($account)) {
            if (!filter_var($account, FILTER_VALIDATE_EMAIL)) {
                if (username_exists($account)) {
                    $res = 'EXIST';
                } else {
                    $res = 'DONT-ALLOW';
                }
            } else {
                if (email_exists($account)) {
                    $res = 'EXIST';
                } else {
                    $res = 'ALLOW';
                }
            }
        }

        return $res;
    }

    /**
     * To Sign In the User's Account
     *
     * @param $userName UserName of the User to Be Logged In
     * @param $password Password of the User's Account
     * @return string
     */
    public static function userSignIn($userName, $password)
    {
        try {
            $credentials = array(
                'user_login' => $userName,
                'user_password' => $password,
                'rememember' => true
            );
            self::clearGuestSession();
            return wp_signon($credentials, false);

        } catch (\Exception $e) {
            //
        }
    }

    public static function isValidUser($userName, $password)
    {
        $user = get_user_by('login', $userName);
        if ($user && wp_check_password($password, $user->data->user_pass, $user->ID))
            return true;
        else
            return false;
    }

    /**
     * @param $user
     * @param $status
     */
    public static function guestSignIn($user, $status)
    {
        /** Guest User [email] Validation */
        Session()->set('guest', $user);
        if (filter_var($user, FILTER_VALIDATE_EMAIL)) {
            $userName = explode('@', $user);

            Session()->set('guestMail', $user);
            Session()->set('guest', ucfirst($userName[0]));
        } else {
            Session()->set('guestMail', '__unknown__');
            Session()->set('guest', '__unknown__');
        }

        Session()->set('uaccount', 'guest');
    }

    /**
     * To Register New User
     *
     * @param $email for register the User Account
     * @param $password Password of the Account
     * @return True or False [Created | Not Created]
     */
    public function userRegistration($email, $password)
    {
        try {
            if (validate_username($email) and Util::validateEmail($email) and self::isUserExist($email)) {
                if (wp_create_user($email, $password, $email)) {
                    if (self::userSignIn($email, $password)) {
                        Session()->set('uaccount', 'register');
                    }
                    self::clearGuestSession();
                    return true;
                } else {
                    return false;
                }
            }
        } catch (\Exception $e) {
            //
        }
    }

    public static function isUserExist($uname)
    {
        return (is_object(get_user_by('login', $uname)));
    }
}