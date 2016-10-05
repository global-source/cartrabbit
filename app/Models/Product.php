<?php

namespace CartRabbit\Models;

use Corcel\Post;
use CartRabbit\Helper;

/**
 * Class Product
 * @package CartRabbit\Models
 */
class Product extends Post
{

    /**
     * @var string
     */
    protected $table = 'posts';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $with = ['meta'];

    /**
     * Product constructor.
     */
    public function __construct($isCart = false, $product_id = false)
    {
        parent::__construct($this->attributes);

        $this->isCart = $isCart;

        $permalink_options = (new Settings())->getPermalinkSettings();
        $this->setRelation('permalink', (object)$permalink_options);

        if ($product_id != false) $this->ID = $product_id;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function price()
    {
        return $this->hasMany('CartRabbit\Models\Price', 'post_id');
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
     * @return mixed
     */
    public function variants()
    {
        return $this->hasMany('CartRabbit\Models\Variant', 'post_parent')->where('post_type', 'cartrabbit_variant');
    }

    /**
     * @param bool $product_id
     * @param array $options
     * @return bool
     */
    public static function getInstance($product_id = false, $options = array())
    {

        //get the product object
        $product = self::loadProduct($product_id);

        if (!$product) return false;

        //get the right product class
        $product_class = self::loadProductClass($product, $options = array());

        /** @var $product_class string is overwrite with it's corresponding namespace. */
        $product_class = 'CartRabbit\Models\\' . $product_class;

        if (!class_exists($product_class)) {
            $product_class = 'ProductSimple';
        }

        return new $product_class();
    }

    /**
     * @param $product_id
     * @return mixed
     */
    public static function loadProduct($product_id)
    {
        $product = false;

        if ($product_id === false) {
            //load the post from the globals
            $product = $GLOBALS['post'];
        } elseif (is_numeric($product_id)) {
            $product = Post::find($product_id);
        } elseif ($product_id instanceof ProductBase) {
            $product = Post::find($product_id->id);
        }
        return apply_filters('sp_load_product_object', $product);
    }

    /**
     * It Returns the Corresponding Product Model
     *
     * @param $product
     * @param $options
     * @return mixed
     */
    public static function loadProductClass($product, $options)
    {
        $product_id = $product->ID;
        $post_type = $product->post_type;

        $type = 'simple';

        if (!isset($options['product_type'])) {

            $product_types = self::getRegisteredProductType();

            /** List Of Registered Product Types */
            foreach ($product_types as $index => $pro_type) {
                if ($product->meta->product_type == $index) {
                    $type = $pro_type;
                }
            }
        }

        if ($post_type === 'cartrabbit_product') {
            //its a simple product by default.
            if (isset($options['product_type'])) {
                $product_type = $options['product_type'];
                //If $option is not given, then use its own content to find it's product type.
            } elseif (!isset($options['product_type'])) {
                $product_type = $type;
            }
        } elseif ($post_type == 'cartrabbit_variant') {
            $product_type = 'variable';
        } else {
            $product_type = false;
        }

        $classname = self::getClassnameFromProductType($product_type);

        // Filter classname so that the class can be overridden if extended.
        return apply_filters('sp_product_class', $classname, $product_type, $post_type, $product_id);

    }

    /**
     * @param $type
     * @return string
     */
    public static function getClassnameFromProductType($type)
    {
        return 'Product' . ucfirst($type);
    }

    /**
     * Get List of Registered Product Types
     * @return array
     */
    public static function getRegisteredProductType()
    {
        return [
            'simpleProduct' => 'simple',
            'variableProduct' => 'variable'
        ];
    }

    public static function init($product_id)
    {
        try {
            if ($product_id) {
                $instance = self::getInstance($product_id);
                if ($instance) {
                    return $instance->find($product_id);
                }
            }
        } catch (\Exception $e) {
            //
        }
    }

}