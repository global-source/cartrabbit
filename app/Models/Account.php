<?php
namespace CartRabbit\Models;

use Flycartinc\Order\Model\Order;

class Account
{
    public $user_id;

    public function __construct()
    {
        global $current_user;
        $this->user_id = $current_user->ID;
    }

    public function is_user_loggedIn()
    {
        return $this->user_id != 0;
    }

    public function getMyOrders($user_id = null)
    {
        //TODO: Improve this
        if (is_null($user_id)) $user_id = $this->user_id;
        $orders = [];
        $list = [];
        try {
            if ($user_id != 0) {
                $orders = Order::where('order_user_id', $user_id)->orderBy('created_at', 'desc')->pluck('id');
            }

            foreach ($orders as $index => $order_id) {
                $list[$order_id] = (new Orders())->getOrder($order_id, true)[0];
            }
        } catch (\Exception $e) {
        }
        return $list;
    }

}