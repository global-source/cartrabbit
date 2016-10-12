<?php

namespace CartRabbit\Models;

use CartRabbit\Helper\SPCache;
use Flycartinc\Order\Model\Order;
use Flycartinc\Order\Model\OrderItem;
use Flycartinc\Order\Model\OrderMeta;
use Illuminate\Database\Eloquent\Model as Eloquent;
use CartRabbit\Helper\Currency;
use CartRabbit\Helper\Util;

/**
 * Class Orders
 * @package CartRabbit\Models
 */
class Orders extends Eloquent
{

    protected $table = 'cartrabbit_orders';

    protected $fillable = array(
        'order_user_id',
        'order_mail',
        'invoice_no',
        'invoice_prefix',
        'unique_order_id'
    );

    /**
     * To Set Primart Key
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * To Set the timestamp for all records
     * @var bool
     */
    public $timestamps = true;

    protected $with = 'meta';

    /**
     * Order Id
     * @var
     */
    protected $order_id;

    /**
     * @var int
     */
    private $subtotal = 0;

    /**
     * @var int
     */
    private $total_tax = 0;
    /**
     * @var int
     */
    private $total_cost = 0;

    /**
     * @var int
     */
    private $subtotal_ex_tax = 0;

    /**
     * @var array
     */
    private $taxrates = array();

    private $offset;

    private $limit;

    private $page;

    /**
     * Cart constructor.
     */
    public function __construct()
    {

    }

    /**
     * To Set Order Id
     *
     * @param $id
     */
    public function setOrderId($id)
    {
        $this->order_id = $id;
    }

    /**
     * To get Order Id
     *
     * @return mixed Product Id
     */
    public function getOrderId()
    {
        return $this->order_id;
    }

    public function setOffset($offset)
    {
        $this->offset = $offset;
    }

    public function getOffSet()
    {
        return $this->offset;
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    public function setPage($page)
    {
        $this->page = $page;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Meta data relationship.
     *
     * @return Order Item Meta's Meta Collection
     */
    public function meta()
    {
        return $this->hasMany('CartRabbit\Models\OrderMeta', 'order_id', 'unique_order_id');
    }

    /**
     * Magic method to return the meta data like the post original fields.
     *
     * @param string $key
     *
     * @return string
     */
    public function __get($key)
    {
        if (!isset($this->$key)) {
            if (isset($this->meta->$key)) {
                return $this->meta->$key;
            }
        } elseif (isset($this->$key) and empty($this->$key)) {
            //TODO: Improve this
        }

        return parent::__get($key);
    }

    public static function getOrderSession()
    {
        $customer = new Customer();
        $billing_address = $customer->getBillingAddress();
        $shipping_address = $customer->getShippingAddress();
        $payment = $customer->getPaymentMethod();
        $shipping = $customer->getShippingMethod();

        return array(
            'billing_address' => json_encode($billing_address),
            'shipping_address' => json_encode($shipping_address),
            'payment' => $payment,
            'shipping' => $shipping
        );
    }

    public static function verifyOrderStatus()
    {
        if (is_user_logged_in()) {
            /** Attempt to retrieve the User ID */
            $id = get_current_user_id();

        }
        dd('HOLDED');
    }

    public static function saveOrder($orderContent)
    {
        /** If Order is already exist, then new order will not be created. */
//        if (!self::verifyOrderStatus()) return false;

        $session = self::getOrderSession();

        $session_order = Session()->get('order_id', 0);

        if ($session_order == 0) {
            /** Creating New Order */
            $order = new Order();
            $user = wp_get_current_user();
            $user_id = $user->ID;

            /** Get Order User's Info */
            $traceOrder = self::traceOrder();

            /** To Save Order */
            $user_email = $user->user_email;
            $order->order_user_id = $user_id;
            $order->order_mail = $user_email;
            $order->order_status = 'new';
            $order->save();
        } else {
            /** If Order is already created, */
            $order = Order::find($session_order);
            self::resetOrderTable($order->id);
        }

        $payment_plugin = Checkout::getPaymentPlugin();
        $shipping_plugin = Checkout::getShippingPlugin();

        $invoice_prefix = Settings::get('invoice_prefix');

        $order->unique_order_id = time() . $order->id;
        $order->invoice_prefix = $invoice_prefix;
        $order->invoice_no = (int)$order->max('invoice_no') + 1;
        $order->save();
        $uni_order_id = $order->unique_order_id;

        /** Creating Order's Meta */

        $fill = array(
            'total',
            'subtotal',
            'subtotal_ex_tax',
            'tax_total',
            'taxes',
            'shipping_total',
            'shipping_tax_total',
            'exchange_value',
            'currency_code',
            'order_status',
            'trace_order',
            'billing_address',
            'shipping_address',
            'payment_method',
            'payment_method_raw',
            'shipping_method',
            'shipping_method_raw'
        );
        foreach ($fill as $index) {
            if ($index == 'taxes') {
                $data = json_encode($orderContent->$index);
            } elseif ($index == 'currency_code') {
                $data = Session()->get('currency');

            } elseif ($index == 'exchange_value') {
                $data = Session()->get('exchange_value');

            } elseif ($index == 'order_status') {
                $data = 'new';

            } elseif ($index == 'billing_address') {
                $data = $session['billing_address'];

            } elseif ($index == 'shipping_address') {
                $data = $session['shipping_address'];

            } elseif ($index == 'payment_method') {
                $data = $session['payment'];

            } elseif ($index == 'payment_method_raw') {

                if (isset($session['payment'])) {
                    $data = $payment_plugin['paymentMethods'][$session['payment']];
                } else {
                    $data = '';
                }


            } elseif ($index == 'shipping_method') {
                $data = $session['shipping'];

            } elseif ($index == 'shipping_method_raw') {
                if (isset($session['shipping'])) {
                    $data = $shipping_plugin['shippingMethods'][$session['shipping']];
                } else {
                    $data = '';
                }

            } elseif ($index == 'trace_order') {
                $data = json_encode($traceOrder);

            } else {
                $data = $orderContent->$index;
            }

            if (is_null($data)) {
                $data = '';
            }

            $orderMeta = new OrderMeta();
            $orderMeta->order_id = $order->id;
            $orderMeta->meta_key = $index;
            $orderMeta->meta_value = $data;
            $orderMeta->save();
        }
        self::saveOrderItems($orderContent->cart_contents, $order->id);
        if ($uni_order_id) {

        }
        /** Clearing Cache */
        SPCache::forget('order_list');
        return $uni_order_id;
    }

    public static function traceOrder()
    {
        return array(
            'options' => '',
            'geo_id' => extension_loaded('geoip') ? '' : $_SERVER['REMOTE_ADDR'],
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'params' => '',
            'session' => Session()->getID()
        );
    }

    public static function saveOrderItems($items, $order_id)
    {
        $item_type = 'order_item';

        foreach ($items as $index => $item) {
            /** Create Order Items */
            $order_item = new OrderItems();
            $order_item->order_item_type = $item_type;
            $order_item->order_id = $order_id;
            $order_item->save();

            $sku = $item->product->meta['sku'];
            $product_name = $item->product->post_title;

            // Adding Additional info of a product.
            $item['sku'] = $sku;
            $item['product_name'] = $product_name;

            /** Create Order Item's Meta */
            foreach ($item as $key => $value) {
                if ($key !== 'product') {
                    if ($key == 'line_tax_data') {
                        $value = json_encode($value);
                    }
                    $order_item_meta = new OrderItemMeta();
                    $order_item_meta->order_item_id = $order_item->id;
                    $order_item_meta->meta_key = $key;
                    $order_item_meta->meta_value = $value;
                    $order_item_meta->save();
                }
            }
            $order_item->save();
        }
        /** To Set Order ID */
        Session()->set('order_id', $order_id);

        return $order_id;
    }

    /**
     * @return string
     */
    public function getCurrentUser()
    {
        $firstName = wp_get_current_user()->first_name;
        $lastName = wp_get_current_user()->last_name;
        $display = wp_get_current_user()->display_name;
        if ($firstName == '') {
            return $display;
        } else {
            return $firstName . ' ' . $lastName;
        }
    }

    public static function resetOrderTable($order_id)
    {
        $order_item = OrderItem::where('order_id', $order_id);
        $order_ids = $order_item->pluck('id');
        foreach ($order_ids as $id) {
            OrderItemMeta::where('order_item_id', $id)->delete();
        }
        $order_item->delete();
        OrderMeta::where('order_id', $order_id)->delete();
    }

    public function getOrder($order_id = false, $is_single = false, $withProduct = true)
    {
        $result = array();

        // Now all created orders are going to list [without any constraints]
        if (!$order_id) {
            $orders = Order::all()->forPage($this->page, $this->limit);
        } else {
            $orders[] = Order::find($order_id);
        }

        $i = 0;
        foreach ($orders as $order) {

            $result[$i]['order_id'] = $order['id'];
            $result[$i]['user'] = self::get_user_data($order['order_user_id']);
            $result[$i]['order'] = $order;

            /** Exchange Value is Set to Process */
            $i++;
        }

        if ($is_single) {
            $order = new Order();
            $items = $order->find($order_id);
            foreach ($items->items()->get() as $key => $item) {
                $result[0]['order'] = $items;
                $result[0]['items']['meta'][$key] = $item->meta()->pluck('meta_value', 'meta_key');
            }
        }
        return $result;
    }

    public static function order_count()
    {
        return Order::all()->count();
    }

    public static function get_user_data($user_id = false)
    {
        if ($user_id == false) $user_id = wp_get_current_user()->ID;

        $user = get_user_by('id', $user_id);

        return array(
            'id' => $user_id,
            'nicename' => $user->user_nicename,
            'displayName' => $user->display_name,
            'fname' => $user->first_name,
            'lname' => $user->last_name,
            'email' => $user->user_email
        );
    }

    public static function processOrderObject(&$order)
    {
//        $isIncludeTax = Settings::get('tax_with_price', 'yes');
//
//        foreach ($order['cart_contents'] as $index => &$item) {
//            if ($isIncludeTax == 'yes') {
//                $item['line_price'] = ($item['line_subtotal'] + $item['line_subtotal_tax']) / max(1, $item['quantity']);
//            } else {
//                $item['line_price'] = ($item['line_subtotal'] / max(1, $item['quantity']));
//            }
//        }
    }


    public function showShippingAddress()
    {

        $status = Settings::get('showShippingFields', 'yes') == 'yes' ? true : false;

        if ($status === false) {
            if ((new Shipping)->needsShipping()) return true;
        }
        return false;
    }
}