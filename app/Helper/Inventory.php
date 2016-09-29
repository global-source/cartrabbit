<?php

namespace CartRabbit\Helper;

use Corcel\Post;
use Flycartinc\Inventory\Model\InventoryUnit;
use Flycartinc\Inventory\Model\InventoryUnitInterface;
use Illuminate\Support\Facades\Session;
use CartRabbit\Models\Cart;
use CartRabbit\Models\Product;
use CartRabbit\Models\Products;
use CartRabbit\Models\Settings;
use CartRabbit\Models\Variant;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Flycartinc\Inventory\Operator\InventoryOperator;
use Flycartinc\Inventory\Checker\AvailabilityChecker;
use Flycartinc\Inventory\Operator\BackordersHandler;
use CartRabbit\Models\Object;
use Illuminate\Database\Eloquent\Collection as ArrayVollection;

/**
 * Class Tax For Performing Tax based Operations
 * @package CartRabbit\Helper
 */
class Inventory
{

    /**
     * @var array
     */
    private $product;

    private $product_post = array();

    private $product_meta = array();

    /**
     * Inventory constructor.
     * @param array $product
     */
    public function __construct($product = array())
    {
        $this->product = $product;
    }

    /**
     * To Process Stock Management of the Product
     * [ACTIVE]
     * @param object $product Of Product Model
     * @param int $qty will be assigned on Cart
     */
    public function validateStock(&$product, $qty = 1, $is_variant = false)
    {
        $product_id = $product->ID;
        $product_name = $product->post_title;

        //default quantity is 1
        $qty_limit = $product->meta->stock->min;

        /** To Get Backorder state from the Product's description */
        $backorder = ($product->meta->backOrders == 'allow') ? true : false;

        $availabilityChecker = new AvailabilityChecker($backorder);

        $item[$product_id]['product'] = $product_name;
        $qtyState = ((int)$product->getOnHand() < (int)$qty_limit) ? '' : ', Available to Buy ' . $product->getOnHand();

        $buy_status = 0;
        $qtyToSale = 0;
        $stock_state = '';
        if (!$product->isProductInManageStock()) {
            $avail_status = true;
            $buy_status = $qty;
            $qtyToSale = 1;
            $stock_state = '';
        } else {
            $avail_status = $availabilityChecker->isStockAvailable($product);
            if ($avail_status) {
                if ($product->getOnHand() > $qty_limit) {
                    /** CASE 1: If Qty Greater then or Equal to Limit and Less than or Equal to Stock */
                    if ($qty >= $qty_limit and $qty <= $product->getOnHand()) {
                        $stock_state = '';
                        $qtyToSale = $qty;
                        $buy_status = $qtyToSale;
//                        unset($item[$item['id']]['product']);
                        /** CASE 2: If Qty Greater then  than Stock */
                    } elseif ($qty > $product->getOnHand()) {
                        $qtyToSale = $product->getOnHand();
                        $buy_status = $qtyToSale;
                        $stock_state = 'Insuffient, Available to Buy ' . $qtyToSale;
                        /** CASE 3: If Qty Less then  than Limit */
                    } elseif ($qty < $qty_limit) {
                        $qtyToSale = $qty_limit;
                        $buy_status = $qtyToSale;
                        $stock_state = 'Minimum to Buy ' . $qtyToSale;
                    }
                    /** CASE 4: If Stock Less then  than Limit */
                } elseif ($product->getOnHand() < $qty_limit) {
                    $qtyToSale = 1;
                    $avail_status = false;
                    $stock_state = 'Not Able to buy, minimum quantity !';
                }
            } else {
                $stock_state = 'Sorry, Not Available';
                $qtyToSale = 0;
            }
        }
        if ($qtyToSale == 0 and $product->isCart) {
            dd('REMOVE');
        }

        if ($is_variant) {
            $avail_status = true;
        }
        $product->meta->visible_on_storefront = $avail_status;
        $product->meta->stock->buy = $buy_status;
        $product->meta->stock->log = $stock_state;
    }


    /**
     * Update Product's Stock Status with DB
     * [Before Order Confirmation]
     */
    public function updateStockStatus()
    {
        $stockItem = $this->product;
        $product = $this->product->getProduct(true);

        $inventory['sold'] = 0;
        $inventory['stock'] = $product['stock'];
        $inventory['return'] = 0;

        //Here, the default qty is 1
        $qty = 1;

        $eventDispatcher = new EventDispatcher();
        $object = new Object();
        $inventoryUnit = new InventoryUnit();
        $inventoryUnits = new ArrayVollection();

        // To Get Backorder state from the Product's description
        $backorder = ($product['back_orders'] == 'allow') ? true : false;

        $availabilityChecker = new AvailabilityChecker($backorder);
        $backordersHandler = new BackordersHandler($object);
        $inventoryOperator = new InventoryOperator($backordersHandler, $availabilityChecker, $eventDispatcher);

        /** To Set the Product to Stockable */
        $inventoryUnit->setStockable($stockItem);
        /** To Add InventoryUnit to Array of Units */
        $inventoryUnits->add($inventoryUnit);
        /** To Set the State of the Operation */
        $inventoryUnit->setInventoryState(InventoryUnitInterface::STATE_SOLD);
        /** To Decrease the OnHand Qty of the Product */
        $inventoryOperator->decrease($inventoryUnits);

        if (!$stockItem->isProductInManageStock()) {
            $avail_status = true;
        } else {
            /** Check the basic stock Availability */
            $avail_status = $availabilityChecker->isStockAvailable($stockItem);
            if ($avail_status) {
                /** Check the stock Availability with buying qty */
                $avail_status = $availabilityChecker->isStockSufficient($stockItem, (int)$qty);
            }
        }

        /** If Purchase is possible, then set [stock | sold | return] */
        if ($avail_status) {
            $inventory['available'] = $stockItem->getOnHand();
            $inventory['stock'] = $stockItem->getOnHand();
            $inventory['sold'] = (int)$product['sold'] + 1;       // here the value 1, is represents the qty of buy
        }
        $post = new Post();
        $post = $post->find($stockItem->getProductId());
        foreach ($inventory as $key => $value) {
            $post->meta
                ->$key = $value;
            $post->save();
        }
    }
}