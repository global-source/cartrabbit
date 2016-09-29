<?php

namespace CartRabbit\Helper;

use CartRabbit\Models\Price;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * For Product Price Manager
 *
 * Class Product Price
 * @package CartRabbit\Helper
 */
class ProductPrice
{

    /**
     * Variable for Assign Product ID
     * @var
     */
    private $product_id;

    /**
     * ProductPrice constructor.
     */
    public function __construct($product_id = null)
    {
        if(!is_null($product_id)) $this->product_id = $product_id;
    }

    /**
     * To Set Product ID
     * @param $product_id
     */
    public function setProductId($product_id)
    {
        $this->product_id = $product_id;
    }

    /**
     * To Get product ID
     * @return mixed
     */
    public function getProductId()
    {
        return $this->product_id;
    }

    /**
     * To Get All Special Prices
     * @return mixed
     */
    public function getAllSpecialPrice()
    {
        $specialprice = Price::where('post_id', $this->product_id)
            ->where('date_from', '>=', date('d-m-Y'))->pluck('price');
        return $specialprice;
    }

    /**
     * To Check Has Special Price Or Not
     * @return bool
     */
    public function hasSpecialPrice()
    {
        $specialprice = Price::where('post_id', $this->product_id)->get()->count();
        return ($specialprice == 0) ? false : true;
    }

    /**
     * To Get Special Price By Product Id and Qty
     * @param int $qty
     * @return bool|float
     */
    public function getSpecialPriceByQty($qty = 1, $format = false)
    {
        $currency = new \CartRabbit\Helper\Currency();
        if (!is_numeric($qty)) return false;
        if (!$this->hasSpecialPrice()) return false;
        if ($qty == '') $qty = 1;

        $specialprice = Price::where('post_id', $this->product_id)
            ->where('qty_from', '<=', (int)$qty)
            ->where('date_from', '<=', date('d-m-Y'))
            ->where('date_to', '>=', date('d-m-Y'))
            ->pluck('price');

        if ($format) {
            foreach ($specialprice as $price) {
                $specialprice = $currency->format((string)$price);
            }
        }
        return (count($specialprice) == 0) ? false : $specialprice;
    }

}