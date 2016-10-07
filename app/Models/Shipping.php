<?php
/**
 * Created by PhpStorm.
 * User: elamathi
 * Date: 23/06/16
 * Time: 8:52 PM
 */


namespace CartRabbit\Models;

use Flycartinc\Order\Model\Order;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Herbert\Framework\Session as Session;

/**
 * Class Shipping
 * @package CartRabbit\Models
 */
class Shipping extends Eloquent
{


    /** @var bool True if shipping is enabled. */
    public $enabled = false;

    /** @var array Stores methods loaded into cartrabbit. */
    public $shipping_methods = array();

    /** @var float Stores the cost of shipping */
    public $shipping_total = 0;

    /**  @var array Stores an array of shipping taxes. */
    public $shipping_taxes = array();

    /** @var array Stores the shipping classes. */
    public $shipping_classes = array();

    /** @var array Stores packages to ship and to get quotes for. */
    public $packages = array();

    public $shipping_rates = array();

    /**
     * Shipping constructor.
     * @param array $attributes
     */
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);

        $this->init();
    }

    /**
     *
     */
    public function init()
    {
        do_action('cartrabbit_shipping_init');
        $this->enabled = (Settings::get('shipping_enable') == 'off') ? false : true;
    }


    /**
     * @param array $packages
     */
    public function calculateShipping($packages = array())
    {
        $this->shipping_total = null;
        $this->shipping_taxes = array();
        $this->packages = array();

        if (!$this->enabled || empty($packages)) {
            return;
        }

        // Calculate costs for passed packages
        $package_keys = array_keys($packages);

        $package_keys_size = sizeof($package_keys);
        for ($i = 0; $i < $package_keys_size; $i++) {
            $this->packages[$package_keys[$i]] = $this->calculate_shipping_for_package($packages[$package_keys[$i]]
            );
        }

        // Get all chosen methods
        $chosen_methods = Session()->get('chosen_shipping_methods');
        $method_counts = Session()->get('shipping_method_counts');

        // Get chosen methods for each package
        foreach ($this->packages as $i => $package) {
            $chosen_method = false;
            $method_count = false;

            if (!empty($chosen_methods[$i])) {
                $chosen_method = $chosen_methods[$i];
            }

            if (!empty($method_counts[$i])) {
                $method_count = $method_counts[$i];
            }
            // Get available methods for package
            $available_methods = $package['rates']['rates'];

            if (sizeof($available_methods) > 0) {
                // If not set, not available, or available methods have changed, set to the DEFAULT option
                if ($chosen_method !== false || $method_count != sizeof($available_methods)) {
                    //TODO: Verify its need.

                    $chosen_method = apply_filters('cartrabbit_shipping_chosen_method', $this->get_default_method($available_methods, $chosen_method), $available_methods);

                    /** If Shipping has "Chosen Method" */
//                    $chosen_method = Customer::getField('shipping_method');
//
//                    $chosen_methods[$i] = $chosen_method;
//                    $method_counts[$i] = sizeof($available_methods);

                    do_action('cartrabbit_shipping_method_chosen', $chosen_method);
                }

                // Store total costs
                if ($chosen_method) {
                    $rate = $available_methods[$chosen_method];
                    // Merge cost and taxes - label and ID will be the same
                    $this->shipping_total += $rate['total'];

                    if (!empty($rate->taxes) && is_array($rate->taxes)) {
//                        foreach (array_keys($this->shipping_taxes + $rate->taxes) as $key) {
//                            $this->shipping_taxes[$key] = (isset($rate->taxes[$key]) ? $rate->taxes[$key] : 0) + (isset($this->shipping_taxes[$key]) ? $this->shipping_taxes[$key] : 0);
                        $this->shipping_taxes = $rate->taxes;
                    }
                }
            }
        }

        // Save all chosen methods (array)
//        Session()->set('chosen_shipping_methods', $chosen_methods);
//        Session()->set('shipping_method_counts', $method_counts);

    }

    /**
     * @param $available_methods
     * @param $chosen_method
     * @return mixed
     */
    public function get_default_method($available_methods, $chosen_method)
    {
        $list = array();
        foreach ($available_methods as $index => $value) {
            if (isset($value['element'])) {
                $list[$value['element']] = array_get($value, 'total', 0);
            }
        }

        /** Sort Array with its shipping charges */
        asort($list);

        //TODO: Verify this default assignment is required or not.
        /** Take the least(First) amount as default shipping charge */
//        $rate_id = array_first(array_keys($list));

        /** Return the first(Min) shipping as Default rate */
//        $method = $available_methods[$rate_id]['element'];

        /** Verify the active session's shipping method with Available shipping methods. */
        if (Session()->has('chosen_shipping_methods') or isset($available_methods[Session()->get('chosen_shipping_methods')[0]])) {
            $method = Session()->get('chosen_shipping_methods')[0];
        } else {
            $method = false;
        }

        /** Sanity Check */
        $plugin_status = apply_filters('is_available', $method);

        if ($plugin_status == 'off' or $plugin_status == false) {
            Session()->remove('chosen_shipping_methods');
            $method = false;
        }

        return $method;
    }

    /**
     * Calculate shipping rates for a package,
     *
     * Calculates each shipping methods cost. Rates are stored in the session based on the package hash to avoid re-calculation every page load.
     *
     * @param array $package cart items
     * @return array
     */

    public function calculate_shipping_for_package($package = array())
    {
        if (!$this->enabled || !$package) {
            return false;
        }
        //initialise session

        $package_hash = 'cartrabbit_ship_' . md5(json_encode($package));
        $stored_rates = Session()->get('shipping_for_package');

        if (!is_array($stored_rates) || $package_hash !== $stored_rates['package_hash'] || empty($stored_rates['rates'])) {
            // Calculate shipping method rates
            $package['rates'] = array();
            $shipping_methods = $this->load_shipping_methods($package);

            if ((is_array($shipping_methods) || is_object($shipping_methods)) and !is_null($shipping_methods)) {

//                if (is_object($shipping_methods)) {
//                    $shipping_methods = (array)$shipping_methods;
//                }

                foreach ($shipping_methods as $shipping_method) {

                    if (apply_filters('is_available', $shipping_method->id)) {
                        $rates = apply_filters('cartrabbit_calculate_shipping', array($shipping_method, $package));
                        // Place rates in package array
                        if (!empty($rates) && is_array($rates)) {

                            foreach ($rates as $rate) {
                                $package['rates'][$rate['id']] = $rate;
                            }
                        }
                    }
                }
            }

            // Filter the calculated rates
            $package['rates'] = apply_filters('cartrabbit_package_rates', array('rates' => $package['rates'], 'package' => $package));
        } else {
            $package['rates'] = $stored_rates['rate'];
        }
        $this->shipping_rates = $package['rates']['rates'];

        return $package;
    }


    /**
     * @param array $package
     * @return array|null
     */
    public function load_shipping_methods($package = array())
    {
        $this->unregister_shipping_methods();

        // Methods can register themselves through this hook
        do_action('cartrabbit_load_shipping_methods', $package);

        // Register methods through a filter

        $shipping_methods_to_load[] = apply_filters('cartrabbit_shipping_methods', array());

        if (!empty(array_first($shipping_methods_to_load))) {
            foreach (array_first($shipping_methods_to_load) as $method) {
                $this->register_shipping_method($method);
            }
            $this->sort_shipping_methods();

            return $this->shipping_methods;
        } else {
            return null;
        }
    }

    /**
     * Register a shipping method for use in calculations.
     *
     * @param object|string $method Either the name of the method's class, or an instance of the method's class
     */
    public function register_shipping_method($method)
    {
        if (is_array($method)) {
            $id = $method['id'];
            $this->shipping_methods[$id] = $method;
        }
    }

    /**
     * Unregister shipping methods.
     */
    public function unregister_shipping_methods()
    {
        $this->shipping_methods = array();
    }

    /**
     * Sort shipping methods.
     *
     * Sorts shipping methods into the user defined order.
     *
     * @return array
     */
    public function sort_shipping_methods()
    {
        $sorted_shipping_methods = array();

        // Get order option
        $ordering = (array)get_option('cartrabbit_shipping_method_order');

        /** TESTING */
        $ordering['cartrabbit_shipping'] = 0;
        /** ********************/

        $order_end = 999;

        //TODO: Implement Sorting
        // Load shipping methods in order
        foreach ($this->shipping_methods as $method) {
            if (isset($ordering[$method['id']]) && is_numeric($ordering[$method['id']])) {
                // Add in position
                $sorted_shipping_methods[$ordering[$method['id']]][] = $method;
            } else {
                // Add to end of the array
                $sorted_shipping_methods[$order_end][] = $method;
            }
        }

        ksort($sorted_shipping_methods);

        $this->shipping_methods = array();

        foreach ($sorted_shipping_methods as $methods) {
            foreach ($methods as $method) {
                $method = (object)$method;

                $id = empty($method->instance_id) ? $method->id : $method->instance_id;
                $this->shipping_methods[$id] = $method;
            }
        }
        return $this->shipping_methods;
    }

    /**
     * For Especially Reset the Shipping Process
     */
    public function resetShipping()
    {
        $session = new Session();
        $session->remove('chosen_shipping_methods');
        $this->shipping_total = null;
        $this->shipping_taxes = array();
        $this->packages = array();
    }

    /**
     * To Find the Required Shipping State with All list of Products in Cart
     *
     * @return bool
     */
    public static function productRequireShipping()
    {
        $order = new Order();
        $order_items = $order->getCart();
        foreach ($order_items as $item) {
            if ($item['product'] instanceof ProductInterface && $item['product']->requiresShipping()) {
                return true;
                break;
            }
        }
        return false;
    }

    public function needsShipping()
    {

//        $order = new Order();
//        $order = $order->initOrder();
//        $order->getItems();

//        foreach ($items as $item) {
//            if ($item['product']->requireShipping()) {
//                return true;
//                break;
//            }
//        }

//        $items =

    }

}