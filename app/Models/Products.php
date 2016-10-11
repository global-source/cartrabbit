<?php

namespace CartRabbit\Models;

use CartRabbit\Helper;
use Corcel\Post;
use CartRabbit\Helper\MetaConverter;
use Illuminate\Database\Eloquent\Model as Eloquent;
use CartRabbit\library\Pagination;


/**
 * Class products
 * @package CartRabbit\Models
 */
class Products extends Eloquent
{

    /**
     * Variable for Assign Product ID
     * @var
     */
    private $product_id;

    /**
     * Variable for Assign Pagination Limit
     * @var
     */
    protected $limit;

    /**
     * Variable for Assign Products Per Page
     * @var
     */
    protected $page;

    /**
     * @var string
     */
    protected $taxonomy = 'Brands';

    private $segments;

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param mixed $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * @return mixed
     */
    public function getSegments()
    {
        return $this->segments;
    }

    /**
     * @param mixed $segments
     */
    public function setSegments($segments)
    {
        $this->segments = $segments;
    }

    /**
     * Variable for Process Product Options
     */

    private $options = array();


    /**
     * Variable for Manage Product's link based on the Permalink Configuration
     * @var array
     */
    private $product_link = array();


    /**
     * @var int
     */
    private $product_total = 0;

    /**
     * @var array
     */
    var $list = array();

    /**
     * @var array
     */
    private $product = array();

    /**
     * Products constructor.
     * @param array $product
     */
    public function __construct($product = array())
    {
        /** @var array product store the product's details */
        $this->product = $product;

        $permalink_options = (new Settings())->getPermalinkSettings();
        $this->product_link = $permalink_options;
    }

    /**
     * @param $option
     */
    public function setOptions($option)
    {
        $this->options = $option;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * To Set Product Display Limit
     * @param $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * To Get Product Display Limit
     * @return mixed
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     *To Set Product Page Limit
     */
    public function setPerPage($page)
    {
        $this->page = $page;
    }

    /**
     * To Get Product Page Limit
     *
     * @return mixed
     */
    public function getPerPage()
    {
        return $this->page;
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
     * @return array
     */
    public function allProducts()
    {
        if (empty($this->list)) {
            $query = Product::join('postmeta', 'posts.ID', '=', 'postmeta.post_id')
                ->where('posts.post_type','cartrabbit_product')
                ->where('postmeta.meta_key', 'visible_on_storefront')
                ->where('postmeta.meta_value', 'yes')
                ->where('post_title', '!=', 'Auto Draft')
                ->where('post_status', '!=', 'trash')
                ->pluck('id');
            $this->list = $query;
        } else {
            $query = $this->list;
        }
        return $query;
    }

    /**
     * @return mixed
     */
    public function product_total()
    {
        return $this->allProducts()->count();
    }

    /**
     * To Display the products
     *
     * @return Products as array
     */
    public function get_products($all = false)
    {
        if ($all) {
            $products = $this->allProducts();
        } else {
            $products = $this->allProducts()->forPage($this->page, $this->limit);
        }
        $list = [];
        if (!$products) return array();

        foreach ($products as $key => $product_id) {

            if ($product_id) {
                $product = Product::init($product_id);
                $product->processProduct();
                $list[$product_id] = $product->setRelation('meta', $product->meta->pluck('meta_value', 'meta_key'));
            }
        }
        return $list;
    }

    /**
     * @param mixed $page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }

    /**
     * @param $product_id
     * @return array
     */
    public static function getProduct($product_id)
    {
        /** If "$product_id" is empty, then it return array() [empty] */
        if (!$product_id) return array();
        $variant = new Variant();
        //$product = new Product();
        //$product->setProductId($product_id);
        $product = Product::find($product_id);
//        $product->getProduct(true);
        $product->processProduct();
        $product->setRelation('meta', $product->meta->pluck('meta_value', 'meta_key'));

        if ($product) {
            $variant->processVariant($product, true);
        }
        if (!empty($product)) {
//            $product->meta->brand = get_term($product['meta']['brand'])->name;
//            $product->setRelation('meta', $product->meta->pluck('meta_value', 'meta_key'));
            //should be named as variants or attributes.
            $product['html'] = Variant::getVariantHtml($product['meta']);
        }
        return $product;
    }

    /**
     * @return int
     */
    public function getProductTotal()
    {
        return $this->product_total;
    }

    /**
     * To Get List of Products
     * @return array|Products
     */
    public function getList()
    {
        if (empty($this->list)) {
            $this->list = $this->get_products();
            return $this->list;
        }
        return $this->list;
    }

    /**
     * To Get List of Items by ID
     * @param $id
     * @return mixed
     */
    public function getSingletonFromList($id)
    {
        if (isset($this->list[$id])) {
            return $this->list[$id];
        }
    }


    /**
     * To Save Product's Special Priceson
     *
     * @param $specialPrice Array of Special Price List
     * @return array
     */
    public
    function saveSpecialPrices($specialPrice, $post_id = null)
    {
        if ((!is_array($specialPrice) and !is_object($specialPrice)) or is_null($specialPrice)) return false;

        /** The Special Price's product id is set by array,
         * in the case of storing special price of "Variant Product"
         */

        if (!isset($post_id) or isset($specialPrice['product_id'])) {
            $post_id = $specialPrice['product_id'];
            $specialPrice = $specialPrice['special_price'];
        }

        if (!isset($post_id) and !isset($specialPrice['product_id'])) return array();

        foreach ($specialPrice as $sprice) {
            if (isset($sprice['price_id'])) {
                $price = Price::where('id', (int)$sprice['price_id'])->first();
            } else {
                $price = new Price();
            }
            $price->post_id = $post_id;
            $price->date_from = $sprice['date_from'];
            $price->date_to = $sprice['date_to'];
            $price->qty_from = $sprice['qty_from'];
            $price->price = $sprice['price'];
            $price->save();
        }
    }

    /**
     * To save Product's Meta
     *
     * @param $http Post Instance
     * @param $product_id Post Id
     * @return bool
     */
    public function saveProduct($http, $product_id)
    {

    }

    /**
     * To Verify as the product have any category or not
     *
     * @param $category
     * @param $product_id
     */
    public function checkCategory($category, $product_id)
    {
        if (count($category) == 1) {
            if ((int)array_first($category) == 0) {
                $this->assignDefaultCategoryForProduct($product_id);
            }
        }
    }

    /**
     * To Get Product's Entire Meta
     *
     * @return \CartRabbit\Helper\Converted
     */
    public function getProductAllMeta()
    {
        if (!isset($this->product_id)) return array();

        return Post::find($this->product_id)->meta()->pluck('meta_value', 'meta_key');
    }

    /**
     * Here, "genre" is pre-registered to assign as default product category,
     *
     * Case 1: If no category is assigned for the product means.
     *
     * Case 2: If any one category is assigned, then this default action is not taken to proceed.
     * @param $product_id
     */
    public function assignDefaultCategoryForProduct($product_id)
    {
        wp_set_object_terms($product_id, self::getDefaultCategory(), 'genre');
    }

    /**
     * @return mixed
     */
    public static function getDefaultCategory()
    {
        $terms = get_terms('genre', array('get' => 'all'));
        foreach ($terms as $term) {
            if ($term->slug == 'uncategorized') {
                return $term->term_id;
            }
        }
    }


    /**
     * Available Produce Metrics for Length and Weight
     * @return Product Metrics in Array or JSON
     */
    public
    function getProductMetrics($isJSON = false)
    {
        /** Product's various Length Metrics */
        $metrics['length'] = array(
            'Centimeter' => array(
                'unit_length_id' => 1,
                'name' => 'Centimeter',
                'unit' => 'cm',
                'value' => 1.00000000
            ),
            'Inch' => array(
                'unit_length_id' => 2,
                'name' => 'Inch',
                'unit' => 'inc',
                'value' => 0.39370000
            ),
            'Millimeter' => array(
                'unit_length_id' => 3,
                'name' => 'Millimeter',
                'unit' => 'mm',
                'value' => 10.00000000
            )
        );

        /** Product's various Weight Metrics */
        $metrics['weight'] = array(
            'Kilogram' => array(
                'unit_weight_id' => 1,
                'name' => 'Kilogram',
                'unit' => 'kg',
                'value' => 1.00000000
            ),
            'Gram' => array(
                'unit_weight_id' => 2,
                'name' => 'Gram',
                'unit' => 'g',
                'value' => 1000.00000000
            ),
            'Ounce' => array(
                'unit_weight_id' => 3,
                'name' => 'Ounce',
                'unit' => 'oz',
                'value' => 35.27400000
            ),
            'Pound' => array(
                'unit_weight_id' => 4,
                'name' => 'Pound',
                'unit' => 'lb',
                'value' => 2.20462000
            )
        );

        return ($isJSON) ? json_encode($metrics) : $metrics;
    }

    /**
     * To Get Metric Details by Unit Length Id
     * @param $unit_id Unit Length Id
     * @return Array of Metric Details
     */
    public
    function getProductMetricByUnitLengthId($unit_id, $type = null)
    {
        $metrics = $this->getProductMetrics();
        foreach ($metrics['length'] as $metric) {
            if ($metric['unit_length_id'] == $unit_id) {
                return ($type == 'JSON') ? json_encode($metric) : $metric;
            }
        }
    }

    /**
     * To Get Metric Details by Unit Weight Id
     * @param $unit_id Unit Weight Id
     * @return Array of Metric Details
     */
    public
    function getProductMetricByUnitWeightId($unit_id, $isJSON = false)
    {
        $metrics = $this->getProductMetrics();
        foreach ($metrics['weight'] as $metric) {
            if ($metric['unit_weight_id'] == $unit_id) {
                return ($isJSON) ? json_encode($metric) : $metric;
            }
        }
    }

    public function generatePagination()
    {
        $pagination = new Pagination();
        $total = $this->product_total();

        $pagination->setSegments($this->segments);
        $pagination->setTotal($total);
        $pagination->setLimit($this->limit);
        return $pagination->generatePagination();
    }
}