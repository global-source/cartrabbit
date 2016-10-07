<?php

namespace CartRabbit\Models;

use Carbon\Carbon;
use CartRabbit\Helper\Currency;
use CartRabbit\Helper\Util;
use Corcel\Post as Post;
use Corcel\User;
use Flycartinc\Order\Model\Order;
use Illuminate\Database\Capsule\Manager as Capsule;
use Logging\Logger;

/**
 * Class products
 * @package CartRabbit\Models
 */
class Dashboard extends Post
{

    /**
     * Cart constructor.
     */
    public function __construct()
    {

    }

    /**
     * To Get Total Buy on the Shop
     *
     * @return float|int
     */
    public function getTotalBuy()
    {
        $total_buy = Capsule::table('order_itemmeta')
            ->where('meta_key', 'LIKE', 'price_%')
            ->sum('meta_value');
        Logger::info("Dashboard/" . __FUNCTION__ . "()", ["total_buy : " => $total_buy]);
        return $total_buy;
    }

    public static function getDashboardSummery()
    {
        $response['orders'] = self::getOrderIndex();

        return $response;
    }

    public static function saveNotes($http)
    {
        $note = $http->get('note', false);
        global $current_user;
        $user = $current_user->ID;
        $note = json_encode($note);

        if ($note and $user) {
            if (get_user_meta($user, 'cart_rabbit_dash_notes')) {
                update_user_meta($user, 'cart_rabbit_dash_notes', $note);
            } else {
                add_user_meta($user, 'cart_rabbit_dash_notes', $note);
            }
        }
    }

    public static function getUserNotes()
    {
        global $current_user;
        $user = $current_user->ID;
        $notes = get_user_meta($user, 'cart_rabbit_dash_notes');

        if (is_string(array_first($notes))) {
            $notes = json_decode(array_first($notes), true);
        }
        return $notes;
    }

    public static function getOrderIndex($count = 5)
    {
        $response = [];
        $orders = Order::limit($count)->orderBy('created_at', 'desc')->get();
        try {
            foreach ($orders as $index => $order) {
                if ($order->order_user_id != 0) {
                    $response[$index]['user'] = User::find($order->order_user_id);

                    $response[$index]['user'] = $response[$index]['user']->setRelation('meta', $response[$index]['user']->meta->pluck('meta_value', 'meta_key'));

                    $response[$index]['info'] = $order;
                }
            }
            return $response;
        } catch (\Exception $e) {
            //
        }
    }

    public static function getDashboardContents()
    {
        $products = new Products();
        $response['products'] = $products->get_products(true);
        if (count($response['products']) > 0) {
            $response['index'] = self::getDashboardSummery();
            $response['total_buyers'] = self::totalBuyers();
            $response['sales'] = self::getSales();
        }

        return $response;
    }

    public static function totalBuyers()
    {
        return Order::groupBy('order_user_id')->get()->count();
    }

    public static function getSales()
    {
        $currency = new Currency();
        $sales = [];
        try {
            $orders = Customer::getCustomerOrderWithStateOf('completed');
            self::getTopSellingProducts($orders, $sales);

            $sales['total'] = 0;
            $sales['today'] = 0;
            $sales['daily'] = 0;
            $sales['monthly'] = 0;
            $sales['graph'] = [];

            foreach ($orders as $index => $order) {
                $month = $order->created_at->format('m');
                $day = $order->created_at->format('d');

                $year = $order->created_at->format('Y');
                if (!isset($sales['graph'][$year])) {
                    $sales['graph'][$year] = [];
                }
                if (!isset($sales['graph'][$year]['month'][$month])) {
                    $sales['graph'][$year]['month'][$month] = 0;
                }
                if (!isset($sales['graph'][$year][$month]['day'][$day])) {
                    $sales['graph'][$year][$month]['day'][$day] = 0;
                }


                $sales['total'] += $order->meta->total;
                if (isset($sales['all'][$day])) {
                    $sales['all'][$day] += $order->meta->total;
                } else {
                    $sales['all'][$day] = $order->meta->total;
                }
                if ($order->created_at->format('Y-m-d') == Carbon::today()->format('Y-m-d')) {
                    $sales['today'] += $order->meta->total;
                }
                if ($order->created_at->format('m') == Carbon::today()->format('m')) {
                    $sales['monthly'] += $order->meta->total;
                }
                if ($order->created_at->format('d') == Carbon::today()->format('d')) {
                    $sales['daily'] += $order->meta->total;
                }

                $sales['graph'][$year]['month'][$month] += $currency->format($order->meta->total, null, $order->meta->exchange_value, null, false)->getAmount();

                $sales['graph'][$year][$month]['day'][$day] += $currency->format($order->meta->total, null, $order->meta->exchange_value, null, false)->getAmount();
            }
            $sales['graph_count'] = count($sales['graph']);
            $sales['graph'] = \CartRabbit\Helper\Dashboard::dailyChart($sales['graph']);
        } catch (\Exception $e) {
            wp_die(__('Unable Process Graph Data !'));
        }
        return $sales;
    }

    public static function getTopSellingProducts($orders, &$sales)
    {
        foreach ($orders as $index => $order) {
            $items = $order->items()->get();
            //TODO: Implement Product get Limit
            foreach ($items as $id => $item) {
                if (!isset($sales['top_selling'][$item->meta->product_id])) {
//                    $sales['top_selling'][$item->meta->product_id] = 0;
                    $sales['top_selling'][$item->meta->product_id]['quantity'] = 0;
                    $sales['top_selling'][$item->meta->product_id]['name'] = $item->meta->product_name;
                    $sales['top_selling'][$item->meta->product_id]['sku'] = $item->meta->sku;
                }

                $sales['top_selling'][$item->meta->product_id]['quantity'] += $item->meta->quantity;
            }
        }
        if (isset($sales['top_selling'])) {
            uasort($sales['top_selling'], array((new Util()), 'qty_sorting'));
        }
    }

    /**
     *To Check Cart's Initial Configuration
     *
     */
    public function isNewBoot()
    {
        $isNew = false;
        $ID = Settings::getStoreConfigID();
        if (!$ID) {
            $isNew = true;
        }
        Logger::info("Dashboard/" . __FUNCTION__ . "()", ["isNew : " => $isNew]);
        return $isNew;
    }
}