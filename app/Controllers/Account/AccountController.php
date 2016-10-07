<?php

namespace CartRabbit\Controllers\Account;

use Herbert\Framework\Http;
use CartRabbit\Controllers\BaseController;
use CartRabbit\Helper;
use CartRabbit\Models\Account;
use CartRabbit\Models\Orders;

/**
 * Class BaseController
 * @package CartRabbit\Controllers
 */
class AccountController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function myAccount()
    {
        $orders = (new Account())->getMyOrders();
        $currency = new Helper\Currency();
        return parent::view('Site.Account.myAccount', compact('orders', 'currency'));
    }

    public function getMyOrder(Http $http)
    {
        $order_id = $http->get('order_id', false);
        $orderInfo = [];
        if ($order_id) {
            $orderInfo = (new Orders())->getOrder($order_id, true, false)[0];
        }
        $currency = new Helper\Currency();
        $util = new Helper\Util();

        //TODO: Improve the formation to eliminate "printing" issue.
        $address = new Helper\Address();
        return parent::view('Site.Account.OrderInfo', compact('orderInfo', 'currency', 'util', 'address'));
    }

}