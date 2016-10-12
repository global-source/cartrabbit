<?php

namespace CartRabbit\Models;

use CartRabbit\Models\ProductInterface;
use CommerceGuys\Tax\TaxableInterface;
use Flycartinc\Inventory\Model\StockableInterface;
use Carbon\Carbon;
use Corcel\Post;
use CartRabbit\Helper;

/**
 * Class ProductBase
 * @package CartRabbit\Models
 */
class ProductBase extends Post implements ProductInterface, StockableInterface, TaxableInterface
{
    /**
     * @var array
     */

    /**
     * Holding the ID of this Product
     * @var
     */
    protected $product_id;

    public $permalink;

    /**
     * ProductBase constructor.
     * @param array $attributes
     */
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);

        //To Adding the Permalink changes to Object Relation
        $permalink_options = (new Settings())->getPermalinkSettings();
        //  $this->setRelation('permalink', (object)$permalink_options);
        $this->permalink = empty($permalink_options) ? new \stdClass() : (object)$permalink_options;
    }

    /**
     * Meta data relationship.
     *
     * @return PostMetaCollection
     */
    public function meta()
    {
        return $this->hasMany('Corcel\PostMeta', 'post_id');
    }

    /**
     * Method to get the ID of the product
     * @return integer The ID of the product
     */
    public function getId()
    {
        return $this->ID;
    }

    /**
     * To Set the product id to get product.
     * @return mixed
     */
    public function getProductId()
    {
        return $this->product_id;
    }

    /**
     * To Retrieve the assigned product id.
     *
     * @param mixed $product_id
     */
    public function setProductId($product_id)
    {
        $this->product_id = $product_id;
    }

    /**
     * To Return the product's status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->post_status;
    }

    /**
     * To Return Product's UpSell.
     *
     * @return mixed
     */
    public function getUpSells()
    {
        return $this->meta->up_sells;
    }

    /**
     * To Return Product's CrossSell.
     *
     * @return mixed
     */
    public function getCrossSells()
    {
        return $this->meta->crosssells;
    }

    /**
     * To Return Product's Add To Cart Text.
     *
     * @return mixed
     */
    public function addToCartText()
    {
        return $this->meta->add_cart_text;
    }

    /**
     * To Return Product's Brand.
     *
     * @return mixed
     */
    public function getBrand()
    {
        return $this->meta->brand;
    }

    /**
     * To Return this product's shipping is enabled or not.
     *
     * @return mixed
     */
    public function isEnableShipping()
    {
        return $this->meta->shipping_enable;
    }

    /**
     * To Return this product's visibility on store front is enabled or not.
     *
     * @return mixed
     */
    public function isVisibleOnStoreFront()
    {
        return $this->meta->visble_on_storefront;
    }


    /********************************************** Relational Models ****************************************/

    /**
     * Method to get the price of a product
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function price()
    {
        return $this->hasMany('CartRabbit\Models\Price', 'post_id');
    }

    /********************************************** Stockable Interface ****************************************/

    /**
     * Get stock keeping unit.
     *
     * @return string SKU
     */
    public function getSku()
    {
        return $this->meta->sku;
    }

    /**
     * Get inventory displayed name.
     *
     * @return string Inventory Name or empty
     */
    public function getInventoryName()
    {
        $inventory = $this->meta->inventory_name;
        return (!empty($inventory)) ? $inventory : '';
    }

    /**
     * Simply checks if there any stock available.
     * It should also return true for items available on demand.
     *
     * @return Boolean Available | Not
     */
    public function isInStock()
    {
        if (!$this->isProductInManageStock()) return true;

        return $this->isProductInAvailable();
    }

    /**
     * Is stockable available on demand?
     *
     * @return Boolean Can Buy | Not
     */
    public function isAvailableOnDemand()
    {
        return $this->isProductAvailableOnDemand();
    }

    /**
     * Get stock's in hold.
     *
     * @return integer Hold item qty
     */
    public function getOnHold()
    {
        if (empty($this->meta->onHold)) {
            $this->meta->onHold = 0;
        }
        return (int)$this->meta->on_hold;
    }

    /**
     * Set stock's Item to Hold.
     *
     * @param integer $onHold to set on Hold
     */
    public function setOnHold($onHold)
    {
        $this->meta->on_hold = (int)$onHold;
    }

    /**
     * Get stock's qty on hand.
     *
     * @return integer On Hand qty
     */
    public function getOnHand()
    {
        //TODO: Verify the Concept, as Hand represents the rest of Hold or Not
        if (empty($this->meta->onHand) or is_null($this->meta->onHand)) {
            if (!empty($this)) {
                $this->meta->onHand = $this->meta->stock->qty;
            } else {
                return 0;
            }
        }

        return (int)$this->meta->on_hand;
    }

    /**
     * Set stock's qty to on hand.
     *
     * @param integer $onHand to set as On Hand
     */
    public function setOnHand($onHand)
    {
        $this->meta->on_hand = (int)$onHand;
    }

    /*** INVENTORY MANAGEMENT ***/

    /**
     * To check this Product's stock management is enabled or not.
     *
     * @return bool
     */
    public function isProductInManageStock()
    {
        if (!$this->meta->manage_stock) return false;

        if ($this->meta->manage_stock == 'yes') {
            $status = true;
        } else {
            $status = false;
        }
        return $status;
    }

    /**
     * To check this product's is in available stage
     *
     * @return bool Available | Not Available
     */
    public function isProductInAvailable()
    {
        if (!$this->meta->stock) return false;

        $stock = (int)$this->meta->stock;

        return ($stock > 0) ? true : false;
    }

    /**
     * To check this product's is able to buy on demand or not
     *
     * @return bool Can Buy | Not
     */
    public function isProductAvailableOnDemand()
    {
        if (!isset($this->meta->backOrders)) return false;

        $order = $this->meta->backOrders;

        return ($order == 'allow') ? true : false;
    }

    /********************************************** Taxable Interface ****************************************/

    /**
     * To Check this product's is a Physical product or not.
     *
     * @return bool
     */
    public function isPhysical()
    {
        // TODO: Implement isPhysical() method.
        return true;
    }

    /********************************************** Mutators ****************************************/

    //TODO: Type To Make Alternative for this !
    /**
     * @return array|object
     */
//    public function getMetaAttribute()
//    {
//        $meta = $this->meta();
//        if (!$meta) return array();
//        return (object)$meta->pluck('meta_value', 'meta_key')->toArray();
//
//    }

    /********************************************** General Functions ****************************************/

    /**
     * To Retrun the Tax Class of the Product
     *
     * @return string
     */
    public function getTaxClass()
    {
        if ($this->post_parent > 0) {
            $this->meta->tax_profile = self::find($this->post_parent)->meta->tax_profile;
        }
        return $this->meta->tax_profile;
    }

    /**
     * Every Object should have it's "Unique ID" to operate.
     * if "No ID" then, that's not a product or not exist.
     *
     * @return bool
     */
    public function exists()
    {
        return empty($this->ID) ? false : true;
    }

    /**
     * To check the product is in "Purchasable" state or not.
     *
     * @return mixed|void
     */
    public function isPurchasable()
    {
        $purchasable = true;

        // Products must exist of course
        if (!$this->exists()) {
            $purchasable = false;

            // Other products types need a price to be set
        } elseif ($this->getPrice() === '' || $this->getPrice() === false) {
            $purchasable = false;

            // Check the product is published
        } elseif ($this->post_status !== 'publish' && !Settings::canUser('edit_post', $this->getId())) {
            $purchasable = false;
        }

        return apply_filters('cartrabbit_is_purchasable', $purchasable, $this);
    }

    public function isVisible()
    {
        if (!$this->ID) {
            $visible = false;

            // Published/private
        } elseif ($this->post_status !== 'publish' && !Settings::canUser('edit_post', $this->getId())) {
            $visible = false;

            // visibility setting
        } elseif ('no' === $this->visible_on_storefront) {
            $visible = false;
        } elseif ('yes' === $this->visible_on_storefront) {
            $visible = true;
        }

        return apply_filters('cartrabbit_product_is_visible', $visible, $this->id);
    }

    /**
     * To Verify this product under taxable or not
     *
     * @return mixed|void
     */
    public function isTaxable()
    {
        $taxable = $this->getTaxStatus() === true && Settings::isTaxEnabled() ? true : false;
        return apply_filters('cartrabbit_product_is_taxable', $taxable, $this);
    }

    /**
     * Returns whether or not the product is stock managed.
     *
     * @return bool
     */
    public function managing_stock()
    {
        return (!isset($this->manage_stock) || $this->manage_stock == 'no' || Settings::get('inventory_manage_stock') !== 'yes') ? false : true;
    }

    /**
     * Returns whether or not the product can be backordered.
     *
     * @return bool
     */
    public function backorders_allowed()
    {
        return apply_filters('cartrabbit_product_backorders_allowed', $this->back_orders === 'allow' || $this->back_orders === 'notify' ? true : false, $this->id);
    }


    /**
     * Return Status of Tax
     *
     * @return string
     */
    public function getTaxStatus()
    {
        return $this->meta->enable_tax === 'on' ? true : false;
    }

    /**
     * To Get All Created Special Price for the Given Product ID
     * [For External Call]
     * @return mixed Special Price a Product
     */
    public static function getSpecialPriceListByID($id)
    {
        return Price::where('post_id', $id)->get();
    }

    /**
     * To Get All Created Special Price for this product
     * @return mixed Special Price a Product
     */
    public function getSpecialPriceList()
    {
        return $this->price()->get();
    }

    /**
     * To Store Multiple Images Ids for Product
     *
     * @return array
     */
    public function getProductGallery()
    {
        if (!isset($this->ID) or empty($this->ID)) return array();

        $this->meta->gallery = json_decode($this->meta->_product_image_gallery_raw);
    }

    /**
     * Processing the Product.
     * @param $complete bool to represent, product in complete or abstract process.
     * @return object
     */
    public function processProduct($complete = true)
    {
        $this->initBasic();
        $this->initStock();
        if ($complete) {
            $this->initPrice();
        }
        $this->initProductLink();
    }

    /**
     * Apply the basic setup of the product.
     */
    public function initBasic()
    {
        // Default Image
        $default_image = Helper\Util::getDefaultConfig()['product_image'];

        $this->meta->is_variant = false;
        $this->meta->has_variant = false;
        $this->getBrand();
        $this->meta->gallery = json_decode($this->meta->_product_image_gallery_raw);
        $this->meta->raw_image = ($this->meta->image == '' || empty($this->meta->image)) ? $default_image : $this->meta->image;
    }

    /**
     * Apply the stock configurations.
     */
    public function initStock()
    {
        $this->prepareStock();
//        $this->performInventory();
    }

    /**
     * Apply the basic price setup.
     */
    public function initPrice()
    {
        $qty = $this->meta->stock->min;
        $pricing = $this->getPrice($qty);

        $this->meta->pricing = $this->formattedPrice($qty, $pricing);
    }

    /**
     * Perform Inventory operation to validate the availability of the product.
     */
    public function performInventory()
    {
        $inventory = new Helper\Inventory();
        $qty = $this->meta->stock->min;
        $inventory->validateStock($this, $qty);
    }

    /**
     * To Process stock with store's configuration.
     */
    public function prepareStock()
    {
        $stock = new \stdClass();
        $stockDisplayStatus = Settings::get('inventory_stock_display_type');

        if ($this->meta->minQtyNotifyDefault == 'on') {
            $stockToNotify = Settings::get('inventory_notify_qty', 0);
        } else {
            $stockToNotify = $this->meta->minQtyToNotify;
        }
        /** If Stock Configuration is not Done, then set default status... */
        if ($stockDisplayStatus == null) $stockDisplayStatus = 'always';

        switch ($stockDisplayStatus) {
            /** Case 1: If Config 'always', Display Stock Always */
            case 'always':
                $stock->show = true;
                $stock->qty = $this->meta->stock;
                break;
            /** Case 2: If Config 'low', Display Stock Only the stock Threshold limit is reached */
            case 'low':
                if ((int)$this->meta->stock <= (int)$stockToNotify) {
                    $stock->show = true;
                    $stock->qty = $this->meta->stock;
                } else {
                    $stock->show = false;
                    $stock->qty = $this->meta->stock;
                }
                break;
            /** Case 3: If Config 'never', Never Display Stock */
            case 'never':
                $stock->show = false;
                $stock->qty = $this->meta->stock;
                break;
        }

        /** Process with Min & Max Qty Management. */
        $min = $this->meta->min_sale_qty;
        if ($this->meta->min_sale_qty_default == 'on') {
            $min = Settings::get('inventory_min_qty', 1);
        }
        if ($min == 0) {
            $min = 1;
        }
        $stock->min = $min;

        $max = $this->meta->max_sale_qty;
        if ($this->meta->max_sale_qty_default == 'on') {
            $max = Settings::get('inventory_max_qty', 0);
        }
        /** If Max. Restriction is higher than Stock, then Stock is set as max restriction. */
        if ($max > $stock->qty or $max == 0) {
            $max = $stock->qty;
        }
        $stock->max = $max;
        //Assigning the Stock Object.
        $this->meta->stock = $stock;
    }


    /**
     * To Generate this Product's link based on the store's permalink configurations.
     *
     * @return array|false|string
     */
    public function initProductLink()
    {
        if (!isset($this->ID)) return array();

        /** Default Link */
        $link = get_permalink($this->ID);

        $site_addr = Helper::get('site_addr');

        $slug = $this->post_name;

        /** To Return the Category */
        $category = wp_get_object_terms($this->ID, 'genre');

        /** Retrieve the Category name */
        $category = object_get(array_first($category), 'slug', 'none');

        /** To Set the Category base */

        $category_base = $this->permalink->product_category_base;

        if (isset($this->permalink->permalink)) {
            /** To Set Product Link in Based on the Permalink Type */
            if ($this->permalink->permalink == 'plain' or $this->permalink->permalink == 'default_permalink') {

                /** If permalink type is "Plain" or "Default", */
                $link = get_permalink($this->ID);

            } elseif ($this->permalink->permalink == 'product/') {

                /** If permalink type is "Shop Based", */
                $link = $site_addr . '/product/' . $slug;

            } elseif ($this->permalink->permalink == 'product/%product-category%' and $this->permalink->type != 'custom') {

                /** If permalink type is "Category Base", */
                $link = $site_addr . '/' . (($category_base) ? $category_base : 'cat') . '/' . $category . '/' . $slug;

            } elseif ($this->permalink->type == 'custom') {

                /** If permalink type is "Custom", */
                $link = $site_addr . '/' . str_replace('%product-category%', $category, $this->permalink->permalink) . '/' . $slug;
            }
        }
        $this->meta->link = $link;
    }

    /**
     * To Get Product's Quantity Restrictions
     *
     */
    public function getQuantityRestrictions()
    {
        $store = (new Settings())->getStoreConfigurations();
        if (!is_null($this->meta->maxSaleQtyDefault) && $this->meta->maxSaleQtyDefault === 'on') {
            $this->meta->maxSaleQty = (float)$store['inventory_max_qty'];
        }
        if (!is_null($this->meta->minSaleQtyDefault) && $this->meta->minSaleQtyDefault === 'on') {
            $this->meta->minSaleQty = (float)$store['inventory_min_qty'];
        }
        if (!is_null($this->meta->minQtyNotifyDefault) && $this->meta->minQtyNotifyDefault === 'on') {
            $this->meta->minQtyNotifyDefault = (float)$store['inventory_notify_qty'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPrice($qty = 1, $date_from = null, $date_to = null)
    {
        //we might have a special price. So check
        if ($qty <= 1) {
            $qty = 1;
        }

        static $sets;
        if (!is_array($sets)) {
            $sets = array();
        }
        if (!isset($sets[$this->getId()][$qty][$date_from][$date_to])) {
            //get the regular price
            $base_price = $price = $this->getBasePrice();

            //get the special price, if set
            $special_price = $this->getSpecialPrice($qty, $date_from, $date_to);

            //re-assign the price because we have a special price
            if ($special_price > 0) {
                $price = $special_price;
            }

            //create a price object and return
            $priceObject = new \stdClass();
            $priceObject->price = (float)$price;
            $priceObject->special_price = $special_price;
            $priceObject->base_price = $base_price;
            $priceObject->is_discounted = (($special_price < $base_price) && ($special_price !== 0));

            $sets[$this->getId()][$qty][$date_from][$date_to] = $priceObject;

        }
        return $sets[$this->getId()][$qty][$date_from][$date_to];
    }

    /**
     * @return mixed
     */
    public function getBasePrice()
    {
        return $this->meta->regular_price;
    }


    /**
     * @param int $qty Quantity
     * @param null $date_from Date from
     * @param null $date_to Date to
     * @return float special price
     */

    public function getSpecialPrice($qty = 1, $date_from = null, $date_to = null)
    {
        if (is_null($date_from)) $date_from = Carbon::today()->format('Y-m-d');
        if (is_null($date_to)) $date_to = Carbon::today()->format('Y-m-d');

        $special_price = $this->price()
            //date from
            ->where(function ($query) use ($date_from) {
                $query->where('date_from', '<=', $date_from)
                    ->orWhere('date_from', '=', '');
            })
            //date to
            ->where(function ($query) use ($date_to) {
                $query->where('date_to', '>=', $date_to)
                    ->orWhere('date_to', '=', '');
            })
            //quantity
            ->where('qty_from', '<=', $qty)
            ->orderBy('qty_from', 'desc')
            ->limit(1)
            ->pluck('price')->first();

        //check if the special price is set.
        $special_price = (!is_null($special_price)) ? $special_price : 0;

        return $special_price;
    }


    /**
     * {@inheritdoc}
     *
     */
    public function get_price_including_tax($qty = 1, $price = '', $single_product = false)
    {
        //if price is empty
        if ($price == '') {
            $price = $this->getPrice($qty)->price;
        }

        /** If its single product, then reset quantity to '1' [After calculating the price] */
        if ($single_product) {
            $qty = 1;
        }

        if ($this->isTaxable()) {
            $tax_model = new Tax();

            if (Settings::pricesIncludeTax() === false) {
                //price does not include tax

                //get the item tax rates
                $tax_rates = $tax_model->getItemRates($this);
                $taxes = $tax_model->calculateTax($price * $qty, $tax_rates, false);
                $tax_amount = $tax_model->getTaxTotal($taxes);
                $price = $price * $qty + $tax_amount;
            } else {

                //price includes tax. So first calculate the base price

                //get the item tax rates
                $tax_rates = $tax_model->getItemRates($this);
                //get the shop's base tax rates
                $base_tax_rates = $tax_model->getBaseTaxRates($this);

                $customer = new Customer();
                if ($customer->is_vat_exempt()) {
                    //customer is a vat exempted. So deduct the tax from the price to get the actual price.
                    $base_taxes = $tax_model->calculateTax($price * $qty, $base_tax_rates, true);
                    $base_tax_amount = $tax_model->getTaxTotal($base_taxes);
                    $price = $price * $qty - $base_tax_amount;


                } elseif ($tax_rates !== $base_tax_rates && apply_filters('sp_adjust_non_base_location_prices', true)) {

                    $base_taxes = $tax_model->calculateTax($price * $qty, $base_tax_rates, true);
                    $modded_taxes = $tax_model->calculateTax(($price * $qty) - $tax_model->getTaxTotal($base_taxes), $tax_rates, false);
                    $price = ($price * $qty) - $tax_model->getTaxTotal($base_taxes) + $tax_model->getTaxTotal($modded_taxes);

                } else {
                    $price = $price * $qty;
                }

            }

        } else {
            $price = $price * $qty;
        }

        return apply_filters('sp_get_price_including_tax', $price, $qty, $this);
    }


    /**
     * {@inheritdoc}
     */

    public function get_price_excluding_tax($qty = 1, $price = '', $single_product = false)
    {
        if ($price === '') {
            $price = $this->getPrice($qty)->price;
        }

        /** If its single product, then reset quantity to '1'  [After calculating the price] */
        if ($single_product) {
            $qty = 1;
        }

        if ($this->isTaxable() && Settings::pricesIncludeTax() === true) {

            $tax_model = new Tax();
            $tax_rates = $tax_model->getBaseTaxRates($this);

            $taxes = $tax_model->calculateTax($price * $qty, $tax_rates, true);
            $price = $price * $qty - $tax_model->getTaxTotal($taxes);

        } else {
            $price = $price * $qty;
        }

        return apply_filters('sp_get_price_excluding_tax', $price, $qty, $this);
    }


    /**
     * Returns the price including or excluding tax, based on the tax display setting setting.
     *
     * @param  string $price to calculate, left blank to just use get_price()
     * @param  integer $qty passed on to get_price_including_tax() or get_price_excluding_tax()
     * @return string
     */
    public function displayPrice($price = '', $qty = 1)
    {

        if ($price === '') {
            $price = $this->getPrice($qty)->price;
        }

        $tax_display_mode = Settings::get('tax_display_price');
        $display_price = (($tax_display_mode == 'includeTax') ? $this->get_price_including_tax($qty, $price) : $this->get_price_excluding_tax($qty, $price));

        return $display_price;
    }


    public function formattedPrice($qty = 1, $pricing = null)
    {
        if ($pricing === null) {
            $pricing = $this->getPrice($qty);
        }

        $currency = new Helper\Currency();
        $object = new \stdClass();

        $object->price = $this->displayPrice($pricing->price, $qty);
        $object->f_price = $currency->format($object->price);

        //base price
        $object->base_price = $this->displayPrice($pricing->base_price, $qty);
        $object->f_base_price = $currency->format($object->base_price);

        if ($pricing->special_price > 0) {
            $object->special_price = $this->displayPrice($pricing->special_price, $qty);
            $object->f_special_price = $currency->format($object->special_price);
        }
        $object->is_discounted = $pricing->is_discounted;

        return $object;
    }

    /**
     * Method to format price
     * @param $value integer | float Price or any value that needs currency formatting
     *
     * @return string Formatted price
     */

    public function formatPrice($value)
    {
        $currency = new Currency();
        return $currency->format($value);
    }

    /**
     * To Getting the Sum of Rate list.
     *
     * @param $rates
     * @return int
     */
    public static function getAmountTotal($rates)
    {
        $total = 0;
        foreach ($rates as $rate) {
            $total += $rate->getAmount();
        }
        return $total;
    }

    /**
     * To Return this product's shipping is required or not.
     *
     * @return mixed
     */
    public function requiresShipping()
    {
        return apply_filters('cartrabbit_product_needs_shipping', ($this->meta->shipping_enable == 'yes' ? true : false), $this);
    }


    /********************************************** Backend Functions ****************************************/

    /**
     * Product reset while changing the type of Product.
     * This will triggered on changing the product's type.
     */
    public static function productReset($product_id, $product_type)
    {
        //Should have product_id to reset.
        if (!isset($product_id) or empty($product_id)) return false;
        //Should have product_type to update.
        if (!isset($product_type) or empty($product_type)) return false;

        $post = self::find($product_id);

        //If given product type is same as existing, then return false.
        if ($post->meta->post_type == $product_type) return false;

        //Removing Product's Meta.
        $post->meta()->delete();
        $post->save();

        $post = self::find($product_id);
        //Update Product Type.
        $post->meta->product_type = $product_type;
        $post->save();

        //Getting Variants for Variant Product.
        $variants = self::where('post_parent', $product_id)->pluck('ID');
        foreach ($variants as $id) {
            $variant = self::find($id);
            if ($variant) {
                //Remove Variant Meta.
                $variant->meta()->delete();
                //Remove Variant.
                $variant->delete();
            }
        }
    }

    /**
     * Method to display the quantity input box
     */
    public function displayQuantity()
    {
        $html = '';
        $stock = $this->meta['stock'];
        if (Settings::get('d_config_quantity_field') == 'yes') {
            $html .= "<input type='number' name='txt_product_qty' style='width: 30%;' id='qty_{$this->getId()}'
                        class='form-control txt_product_qty fc-input'
                        value='{$stock->min}'
                         min='{$stock->min}'
                         />
                        ";
        } else {
            $html .= "<input type='hidden' name='txt_product_qty' style='width: 30%;' id='qty_{$this->getId()}'
                        class='form-control txt_product_qty fc-input'
                        value='{$stock->min}'
                         min='{$stock->min}'
                         />
                        ";
        }
        return apply_filters('cartrabbit_display_quantity', $html, $this);
    }
}