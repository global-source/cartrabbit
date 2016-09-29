<?php

namespace CartRabbit\Helper;

use Corcel\Post;
use CartRabbit\Models\Price;
use CartRabbit\Models\Settings;

/**
 * For Simple Product
 *
 * Class Simple Product
 * @package CartRabbit\Helper
 */
class SimpleProduct
{
    /**
     * Variable for Assign Product ID
     * @var
     */
    private $product_id;

    /**
     * SimpleProduct constructor.
     */
    public function __construct()
    {

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
     *
     * @return mixed
     */
    public function getProductId()
    {
        return $this->product_id;
    }

    /**
     * To Get Product's Complete Configuration Settings
     *
     * @return Product's Configurations
     */
    public function getProductConfig()
    {
        return Post::find($this->product_id)->meta()->get();
    }

    /**
     * To Get Product's Quantity Restrictions
     *
     * @param $product
     */
    public function getQuantityRestrictions(&$product)
    {
        $store = (new Settings())->getStoreConfigurations();
        if (!is_null($product['max_sale_qty_default']) && $product['max_sale_qty_default'] === 'on') {
            $product['max_sale_qty'] = (float)$store['inventory_max_qty'];
        }
        if (!is_null($product['min_sale_qty_default']) && $product['min_sale_qty_default'] === 'on') {
            $product['min_sale_qty'] = (float)$store['inventory_min_qty'];
        }
        if (!is_null($product['min_qty_notify_default']) && $product['min_qty_notify_default'] === 'on') {
            $product['min_qty_notify_default '] = (float)$store['inventory_notify_qty'];
        }

    }

    /**
     * To Get Manage Stock Status
     *
     * @param $product reference array of Product Instance
     * @return True | False Manage Stock Status
     */
    public function checkManageStockStatus(&$product)
    {
        $stockStatus = true;
        if ($this->manageStock($product) && $this->backOrderAllowed($product) === false) {
            $stockStatus = $this->validateStock($product, $product['qty']);
        }
        return $stockStatus;
    }


    /**
     * To Get Status of Manage Stock
     *
     * @param $product reference array of Product Instance
     * @return True | False Manage Stock Status
     */
    public function manageStock($product)
    {
        return ($product['manage_stock'] == 'yes') ? true : false;
    }

    /**
     * To Get BackOrder Status
     *
     * @param $product reference array of Product Instance
     * @return True | False Back Orders Status
     */
    public function backOrderAllowed($product)
    {
        return ($product['back_orders'] == 'allow') ? true : false;
    }

    /**
     * To Get Quantity Validation Status
     *
     * @param $product reference array of Product Instance
     * @param int $qty
     * @return True | False Quantity Validation Status
     */
    public function validateStock($product, $qty = 1)
    {
        $stockStatus = true;

        //if stock is less than 0
        if ($product['qty'] <= 0) {
            $stockStatus = false;
        }

        //purchase qty validation
        //check stock status
        if ($product['stockStatus'] == 'outOfStock') {
            $stockStatus = false;
        }
        return $stockStatus;
    }

    /**
     * To Process Product's Price
     *
     * @param $product reference array of Product Instance
     * @param int $qty
     * @return bool|float Price of a Product
     */
    public function getPrice($product, $qty = 1, $doNothing = false)
    {
        $currency = new \CartRabbit\Helper\Currency();
        if (!$product) return false;
        $price = new ProductPrice();
        $price->setProductId($this->product_id);

        //if doNothing then set Qty to 1, for getting product's price
        $pro_qty = ($doNothing == true) ? 1 : $qty;

        if($qty == 1){
            $pro_qty = 1;
        }

        //to check/get special price for the product
        $specialPrice = (new Price())->getSpecialPriceByQty($this->product_id, $qty);

        //if no special price then set false
        $specialprice = ($specialPrice) ? (string)$specialPrice[0] : false;
        if (!$specialprice) {
            //to get special price for the products listed in cart page
            $product_price = $product['regular_price'] * $pro_qty;

        } else {
            //to get special price subtotal for the products list
            $product_price = $specialprice * $pro_qty;
        }
        return $product_price;
    }

}
