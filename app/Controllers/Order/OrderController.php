<?php
namespace CartRabbit\Controllers\Order;

use Flycartinc\Order\Model\Order;
use CartRabbit\Controllers\BaseController;
use CartRabbit\Helper;

use Herbert\Framework\Http;
use Corcel\Post;

use CartRabbit\library\Pagination;
use CartRabbit\Models\Orders;
use CartRabbit\Models\Settings;


/**
 * Class Cart
 *
 * @package CartRabbit\Controllers
 */
class OrderController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * To Get Registered Orders
     */
    public function getOrder()
    {
        //
    }

    public function saveOrderConfig(Http $http)
    {
        if ($http->has('invoice_prefix')) {
            Settings::set('invoice_prefix', $http->get('invoice_prefix'));
        }
        return parent::redirect('/wp-admin/admin.php?page=cartrabbit-config&tab=order&opt=general');
    }

    /**
     * Redirect To Order List Page
     * @return \Herbert\Framework\Response
     */
    public function getOrderList(Http $http)
    {
        /** @var $order_id To get the Order Details of the given ID */
        $order_id = $http->has('order_id') ? $http->get('order_id') : null;

        if (!is_null($order_id)) {
            if (Helper\SPCache::has('order_single_' . $order_id)) {
                $html = json_decode(Helper\SPCache::get('order_single_' . $order_id), true);
            } else {
                $html = $this->getOrderByID($order_id);
                Helper\SPCache::add('order_single_' . $order_id, json_encode($html->getBody()), 24 * 3600);
            }

        } else {
            if (Helper\SPCache::has('order_list')) {
                $html = json_decode(Helper\SPCache::get('order_list'), true);
            } else {
                $html = $this->getAllOrders($http);
                Helper\SPCache::add('order_list', json_encode($html->getBody()), 24 * 3600);
            }
        }
        return $html;
    }

    public function getOrderByID($order_id)
    {
        $order_status = array(
            'pending' => 'Pending payment',
            'shipped' => 'Shipped',
            'new' => 'New',
            'onHold' => 'On Hold',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
            'failed' => 'Failed'
        );
        $orderInfo = (new Orders())->getOrder($order_id, true)[0];

        $currency = new Helper\Currency();
        $util = new Helper\Util();
        $address = new Helper\Address();
        return parent::view('Admin.Orders.OrderInfo', compact('orderInfo', 'order_status', 'currency', 'util', 'address'));
    }

    public function getAllOrders($http)
    {
        $limit = ($http->has('limit') ? $http->get('limit') : 10);
        $Page = ($http->has('ppage') ? $http->get('ppage') : 1);

        $order = new Orders();
//        $orders_data['orders'] = $order->getOrder();

        $pagination = new Pagination();
        $segment = 'page=cartrabbit_order';
        $order->setLimit($limit);
        $order->setPage($Page);
        $orders = $order->getOrder();

        $products_count = Orders::order_count();

        $pagination->setSegments($segment);
        $pagination->setTotal($products_count);
        $pagination->setLimit($limit);
        $pagination = $pagination->generatePagination();
        $currency = new Helper\Currency();
        $util = new Helper\Util();
        return parent::view('Admin.Orders.OrderList', compact('orders', 'currency', 'pagination', 'util'));
    }

    public function updateOrderStatus(Http $http)
    {
        $order_id = Helper\Util::extractDataFromHTTP('order_id');

        $status = $http->get('order_status', false);
        if ($status and isset($order_id)) {
            $order = new Order();
            $order->setOrderId($order_id);
            $order->updateOrderStatus($status, true);
        }
        $return = get_site_url() . '/wp-admin/admin.php?page=cartrabbit_order&order_id=' . $order_id;
        if (!$order_id) {
            $return = $_SERVER['HTTP_REFERER'];
        }
        wp_redirect($return);
    }

    public function showOrder(Http $http)
    {
        //
    }

    /**
     * To Create Order
     *
     * @param Http $http
     */
    public function createOrder(Http $http)
    {
        //TODO: Update Order on enter Order Summery
        //TODO: Bug On Meta Relationships
        $order = new Order();
        $order->initOrder();
        $orderSummery = $order;
        Orders::saveOrder($orderSummery);
    }

    /**
     * To Display Order information's
     *
     * @param Http $http For Access Post Contents
     * @return Order Information Twig
     */

    public function getOrderInfo(Http $http)
    {
        /** @var  $post_id Get the currenct post ID from URI */
        $post_id = ($http->has('post') ? $http->get('post') : '');
        if ($post_id == '') die('New Order Not Yet Available !');
        $meta = Post::find($post_id)->meta()->pluck('meta_value', 'meta_key');

        $order_status = array(
            'Processing',
            'Pending payment',
            'Processing',
            'On Hold',
            'Completed',
            'Cancelled',
            'Refunded',
            'Failed'
        );
        $data = array(
            'order_id' => $post_id,
            'meta' => $meta
        );

        return parent::view('Admin.Orders.OrderInfo', compact('data', 'order_status'));
    }

    public function prePayment(Http $http)
    {

    }

    /**
     * To Display Ordered Item details
     *
     * @return Ordered items Twig
     */

    public function getOrderedItems(Http $http)
    {
        /** @var  $post_id Get the currenct post ID from URI */
        $post_id = ($http->has('post') ? $http->get('post') : '');
        $order = new Orders();
        $order->setOrderId($post_id);
        $order = $order->getOrderItem();
        return parent::view('Admin.Orders.OrderItems', compact('order'));
    }
}