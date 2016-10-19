<?php

namespace CartRabbit\Models;

use CommerceGuys\Addressing\Repository\SubdivisionRepository;
use Corcel\Term;
use Corcel\TermTaxonomy;
use Flycartinc\Order\Model\Order;
use Herbert\Framework\Models\Option;
use Corcel\Post;
use Herbert\Framework\Models\PostMeta;
use CartRabbit\Helper\MetaConverter;
use CartRabbit\Helper;
use Illuminate\Database\Eloquent\Model as Eloquent;


/**
 * Class products
 * @package CartRabbit\Models
 */
class Settings extends Eloquent
{
    /**
     * @var array
     */
    protected static $postTypes = ['cartrabbit_config'];


    /**
     * @var array
     */
    protected static $settings = array();

    /**
     * @var array
     */
    protected static $difference = array();

    protected static $permanant_links = array();

    /**
     * Settings constructor.
     */
    public function __construct()
    {
        if (empty(self::$settings)) {
            self::getAllSettings();
        }
    }

    /**
     * @return mixed|void
     */
    public static function getAllSettingsMenuItems()
    {
        $items = array(
            'General' => '?page=cartrabbit-config&tab=general',
            'Product' => '?page=cartrabbit-config&tab=product',
            'Tax' => '?page=cartrabbit-config&tab=tax&opt=taxoptions',
            'Inventory' => '?page=cartrabbit-config&tab=inventory',
            'Cart' => '?page=cartrabbit-config&tab=cart',
            'Shipping' => '?page=cartrabbit-config&tab=shipping',
            'Payment' => '?page=cartrabbit-config&tab=payment&opt=general',
            'Order' => '?page=cartrabbit-config&tab=order&opt=general'
        );

        $menu_items = apply_filters('cartrabbit_admin_menu_items', $items);
        return $menu_items;
    }

    /**
     * @return mixed|void
     */
    public static function getAllShippingOptionMenu()
    {
        $items = array(
            'General' => '?page=cartrabbit-config&tab=shipping&opt=general'
        );
        $option_menu = apply_filters('storpress_shipping_option_menu', $items);
        return $option_menu;
    }

    public static function getAllPaymentOptionMenu()
    {
        $items = array(
            'General' => '?page=cartrabbit-config&tab=payment&opt=general'
        );
        $option_menu = apply_filters('cartrabbit_payment_option_menu', $items);
        return $option_menu;
    }

    public static function getInvoiceNo()
    {
        $inv_pref = self::get('invoice_prefix', '');
        $inv_no = ((int)Order::max('invoice_no')) + 1;

        return $inv_pref . $inv_no;
    }

    public static function getPaymentConfig()
    {
        $config_id = self::getStoreConfigID();
        $config['data'] = Post::find($config_id)->meta()
            ->where('meta_key', 'like', 'payment_%')
            ->pluck('meta_value', 'meta_key');
        $list = array();
        $config['list'] = apply_filters('cartrabbit_payment_plugins', $list);
        return $config;
    }

    /**
     * To Return all settings of the product
     */
    public static function getAllSettings()
    {
        $post = Post::where('post_type', 'cartrabbit_config')->first();
        $values = array();
        if (count($post) != 0) {
            $values = $post->meta()->pluck('meta_value', 'meta_key')->toArray();
        }
        self::$settings = (object)$values;
        return self::$settings;
    }

    /**
     * @param $action
     * @param $post_id
     * @return mixed
     */
    public static function canUser($action, $post_id)
    {
        return current_user_can($action, $post_id);
    }

    /**
     * Are prices inclusive of tax
     * @return bool
     */

    public static function pricesIncludeTax()
    {
        return self::isTaxEnabled() && self::get('tax_price_includes_tax') === 'yes';
    }

    /**
     * @return mixed
     */
    public static function isTaxEnabled()
    {
        return apply_filters('cartrabbit_tax_enabled', self::get('tax_enable', 'off') == 'on');
    }

    /** For Static Call to get Store Configuration Settings */
    public static function get($key, $default = '')
    {
        if (empty(self::$settings)) {
            self::getAllSettings();
        }

        if (isset(self::$settings->$key)) {
            return self::$settings->$key;
        } else {
            return $default;
        }
    }

    public static function getProductDisplayConfig()
    {
        $configID = Settings::getStoreConfigID();
        $config = Post::find($configID)->meta()
            ->where('meta_key', 'like', 'd_config_%')
            ->pluck('meta_value', 'meta_key')->toArray();
        return ((isset($config) and !empty($config)) ? $config : array());
    }

    /**
     * To Update Store Configurations
     *
     * @param $key
     * @param $value
     */
    public static function set($key, $value)
    {
        $id = self::getStoreConfigID();
        $config = Post::find($id);
        $config->meta->$key = $value;
        $config->save();
    }

    public static function updateConfig($list)
    {
        if ($list and is_array($list)) {
            $id = self::getStoreConfigID();
            $config = Post::find($id);
            if(isset($config) and !is_null($config)){
                foreach ($list as $index => $value) {
                    $config->meta->$index = $value;
                    $config->save();
                }
            }
        }
    }

    public static function loadShippingConfigurations()
    {
        $data['shipping_enable'] = self::get('shipping_enable', 'no');
        $data['shipping_dont_allow_if_no_shipping'] = self::get('shipping_dont_allow_if_no_shipping', 'no');
        return $data;
    }

    /**
     * To Get Product's General Display configurations
     */
    public function productDisplayConfig()
    {
        $config = Settings::getProductDisplayConfig();
        Helper\Util::arrayRemoveString($config, 'index', 'dConfig_');
//        $this->meta->displaySetup = $config;
        return $config;
    }

    /**
     * @return bool
     */
    public static function checkCartClearStatus()
    {
        $config = self::getCartConfig();
        if ($config['clearCart'] == 'onPlaceOrder') {
            return true; // Temporary
//            (new \CartRabbit\Models\Cart())->removeCart();
        } elseif ($config['clearCart'] == 'afterOrderConfirmation') {
            return true; // Temporary
        }
    }

    /**
     * Get Cart Configuration's entire meta
     * @return converted meta format to "Key => Value"
     */
    public static function getCartConfig($onlyInstance = false)
    {
        $post = Post::type('cartrabbit_config')->first();
        if (is_null($post)) return false;

        if (!$onlyInstance) {
            $post = $post->meta()->pluck('meta_value', 'meta_key');
        }
        if (isset($post['logo_id'])) {
            $post['logo'] = wp_get_attachment_image_src($post['logo_id'])[0];
        }
        return $post;
    }

    /**
     * To Get Cart Configuration Status by applying Rules.
     *
     * @return array
     */
    public static function getCartConfigStatus()
    {
        $error = array();

        //Checklist is array of rules to apply and ensure the state of the plugin
        //1. Expected  : Hook to find out the Bug or Issue on Setup
        //2. Msg       : Message will if Hook find any Bug
        //3. URL       : A Path to config the issue.

        $checklist = array(
            'product_Display_' =>
                ['expected' => 'NoPage',
                    'msg' => 'Need to Setup the CartRabbit Pages !',
                    'url' => Helper::get('site_addr') . '/wp-admin/admin.php?page=cartrabbit-config&tab=product&opt=Display'
                ]
        );

        $hash = 0;
        foreach ($checklist as $key => $value) {
            $error[$hash]['log'] = self::getCartConfig(true)->meta()
                ->where('meta_key', 'like', $key . '%')
                ->where('meta_value', $value['expected'])
                ->pluck('meta_key', 'meta_value')->first();
            $error[$hash]['msg'] = $value['msg'];
            $error[$hash]['url'] = $value['url'];
            $hash = $hash + 1;
        }
        return isset($error[0]['log']) ? $error : array();
    }

    /**
     * @param $http
     * @return bool
     */
    public static function removeTermOption($http)
    {
        $slug = $http->has('class_id') ? $http->get('class_id') : false;
        if (!$slug) return false;
        $product_id = Helper\Util::extractDataFromHTTP('post');
        $post = Post::find($product_id);
        $meta = json_decode($post->meta->product_attributes, true);
        unset($meta[$slug]);
        $post->meta->product_attributes = json_encode($meta);
        $post->save();
    }

    /**
     * @param $http
     */
    public static function refreshAttributes($http)
    {
        $action = ($http->has('action') ? $http->get('action') : false);
        $refresh = ($http->has('refresh') ? $http->get('refresh') : false);
        $product_id = Helper\Util::extractDataFromHTTP('post');

        if (isset($product_id) and $refresh == 'true') {
            $attributes = (new Settings())->processVariationsList(Product::init($product_id)->meta->product_attributes, true);

            Settings::generatePostVariant($attributes, $action);
        }
    }

    /**
     * To Process the Variation list of Each Product's Attributes
     *
     * @param string $source JSON Dump of Attributes of the Product
     * @return array List Of variations of the Product
     */
    public function processVariationsList($source, $indexTaxonomy = false)
    {
        $list = array();
        $term_list = '';
        if (!is_array($source)) $source = json_decode($source, true);

        if (empty($source)) return array();

        foreach ($source as $key => $value) {
            if (isset($value['is_used_for_variant'])) {
                $list[$key] = $value['list'];
            }
        }
        foreach ($list as $key => $value) {
            if ($indexTaxonomy) {
                $term_list[$key] = $this->getTermNameByID($value);
            } else {
                $term_list[] = $this->getTermNameByID($value);
            }
        }
        if (is_array($term_list) AND !empty($term_list)) {
            $result = Helper\Util::cartesian($term_list);
        } else {
            $result = array();
        }
//        self::getSlugForVariants($result);
        return $result;
    }

    /**
     * @param $id
     * @return array
     */
    public function getTermNameByID($id)
    {
        if (is_array($id)) {
            foreach ($id as $term_id) {
                $result[] = get_term($term_id)->slug;
            }
        } else {
            $result = get_term($id);
        }
        return $result;
    }

    /**
     * To Generate Bunch of post with the Cartesian Combination
     *
     * @param $list
     * @param $isReset
     * @return array
     */
    public static function generatePostVariant($list, $isReset = false)
    {
        $product_id = Helper\Util::extractDataFromHTTP('post');
        /** Once any one of the option is removed, then All Variant Combinations got Rollback */
        if ($isReset) {
//            Post::where('post_name', 'like', 'product-' . (int)$product_id . '-variation-%')->delete();
        }
        if (empty($list)) return array();
        $variations = Post::where('post_name', 'like', 'product-' . (int)$product_id . '-variation-%')->get();
        $total = count($list);

        foreach ($list as $key => $value) {
            if ($total == 0) {
                die();
            }
            $total = $total - 1;
            $status = true;
            foreach ($variations as $item) {
                if ($item['post_name'] == 'product-' . $product_id . '-variation-' . $key) $status = false;
            }
            if ($status) {
                $post = self::createVariation($product_id, $key, $value);
            } else {
                $post = Post::where('post_name', 'product-' . $product_id . '-variation-' . $key)->get()->first();
            }
            if ($product_id) {
                self::createVariationMeta($post, $value, $product_id);
            }

        }
    }

    /**
     * To Create Variation Product
     *
     * @param $product_id
     * @param $id
     * @return \WP_Post
     */
    public static function createVariation($product_id, $id, $combination)
    {
        /** Generate Variant Products */
        $post_title = Post::find($product_id)->post_title;
        $post = new Post();
        $post->post_name = 'product-' . $product_id . '-variation-' . $id;
        $post->post_title = $post_title . ' | ' . implode(',', $combination);
        $post->post_parent = $product_id;
        $post->post_type = 'cartrabbit_variant';
        $post->save();
        return $post;
    }

    /**
     * To Create meta for Variations
     *
     * @param $post
     * @param $meta
     * @return array
     */
    public static function createVariationMeta($post, $meta, $parent_product_id)
    {
        if (empty($meta) OR !isset($meta) OR !is_array($meta)) return array();
        /** Generate Variant Products Meta */
        foreach ($meta as $index => $name) {
            $key = 'attribute_pro_' . $index;
            $post->meta->$key = $name;
        }
        $post->meta->parent_product = $parent_product_id;
        $post->save();
    }

    /**
     * @param $result
     */
    public static function getSlugForVariants(&$result)
    {
        $terms = Term::get();
        foreach ($result as $index => $item) {
            foreach ($item as $key => $value) {
//                dd($terms->where('slug', $value));
            }
        }
    }

    // Not Yet Implemented Fully !

    /**
     *
     * Reset Variations of a Product, by delete all of its dependencies
     *
     * @param int $product_id Product ID
     * @return bool True | False
     */
    public static function resetVariations($product_id = null)
    {
        if (is_null($product_id)) {
            $product_id = Helper\Util::extractDataFromHTTP('post');
        }

        if (!isset($product_id) or is_null($product_id)) return false;
        $key = 'product-' . $product_id . '-variation-%';

        $products = Post::where('post_name', 'like', $key);
        $ids = $products->pluck('id');

        foreach ($ids as $id) {
            PostMeta::where('post_id', $id)->delete();
        }
        $products->delete();

    }

    /**
     *
     * To Return List of Variations
     *
     * @param int $product_id Product ID
     * @return array List of Variation list
     */
    public static function getVariationsList($product_id = null)
    {

        if (is_null($product_id)) $product_id = Helper\Util::extractDataFromHTTP('post');

        if (is_null($product_id) OR empty($product_id) OR !isset($product_id)) return array();

        $data = Settings::getTermlist(array('term_id', 'slug', 'name'), $product_id);

        if (empty($data)) return array();

        foreach ($data['active'] as $key => $value) {
            $result[$key] = $value['list'];
            unset($data['active'][$key]['list']);
        }
        if (empty($result) OR !isset($result)) return array();

        foreach ($result as $key => $value) {
            foreach ($value as $item) {
                if (isset($data['raw'][$key][$item])) {
                    $output[$item] = $data['raw'][$key][$item];
                }
            }
        }
        if (!$output) return array();
        foreach ($output as &$attribute) {
            $attribute['type'] = TermTaxonomy::find($attribute['term_taxonomy_id'])->getAttributes()['taxonomy'];
            unset($attribute['term_taxonomy_id']);
            unset($attribute['name']);
            $attribute['type'] = str_replace('pro_', '', $attribute['type']);
        }
        $result = '';
        foreach ($output as $key => $val) {
            $type = $val['type'];
            $result[$type][$val['term_id']] = $val['slug'];
        }
        $result['activeSets'] = $result;

        $variations = Post::where('post_name', 'like', 'product-' . $product_id . '-variation%')->get();

        $result_meta = array();
        foreach ($variations as $key => $value) {
            $out[$key] = $value->meta()->select('meta_value', 'meta_key', 'post_id')->get();
            foreach ($out[$key] as $index => $meta) {
                if (str_contains($meta['meta_key'], 'attribute_pro_')) {
                    $result_meta[$key][str_replace('attribute_pro_', '', $meta['meta_key'])] = $meta['meta_value'];
                    $result_meta[$key]['product_id'] = $meta['post_id'];
                    unset($meta['meta_key']);
                    unset($meta['meta_value']);
                    unset($meta['value']);
                }
            }
        }
        $result['cartesian'] = $result_meta;
//        self::extractTermSet();
        return $result;
    }

    /**
     * @param array $index
     * @param null $product_id
     * @return array
     */
    public static function getTermlist($index = array(), $product_id = null)
    {
        if (is_null($product_id)) {
            $product_id = Helper\Util::extractDataFromHTTP('post');
        }
        if (is_null($product_id)) return array();

        $terms['raw'] = self::getProductAttributes();
        foreach ($terms['raw'] as $key => $value) {
            $terms['raw'][$key] = self::getProductAttributes($value['slug'], null, 'term_id');
        }
        $active_terms = Product::init($product_id)->meta->product_attributes;

        if (empty($active_terms)) return array();
        $terms['active'] = json_decode($active_terms, true);

        /** To Verify, Attribute is set to "Use as Variant" or Not */
        foreach ($terms['active'] as $key => $item) {
            if (!isset($item['is_used_for_variant'])) {
                unset($terms['active'][$key]);
            }
        }

        Helper\Util::arrayExtractExcept('list', $terms['active']);

        return $terms;

    }

    /**
     * @param string $attr
     * @param null $index
     * @return array
     */
    public static function getProductAttributes($attr = 'product_attributes', $index = null, $filter_by = 'slug')
    {
        /** Here Prefix "pro_" is assigned to make
         * difference between native and cartrabbit product's taxononmy */
        if ($attr != 'product_attributes') {
            if ($attr != 'product_brands') {
                $attr = 'pro_' . $attr;
            }
        }

        $res = get_terms($attr, array('get' => 'all'));

        Helper\Util::objectToArray($res);
        if (is_null($index)) {
            $index = array(
                'term_id',
                'name',
                'slug',
                'term_taxonomy_id'
            );
        }
        return Helper\Util::extractArray($index, $res, $filter_by);
    }

    /**
     * To Collect the Terms of the Product
     */
    public static function extractTermSet()
    {
        $product_id = Helper\Util::extractDataFromHTTP('post');
        $taxonomy = json_decode(Product::init($product_id)->meta->product_attributes);
        Helper\Util::arrayExtractExcept('list', $taxonomy);
    }

    /**
     * Generate HTML level Variant Combinations
     *
     * @param $variations
     * @return mixed
     */
    public static function generateVariationHtml($variations, $product_id = null)
    {
        $hash = 0;
        $combi_id = 0;
        if (is_null($product_id)) {
            $product_id = Helper\Util::extractDataFromHTTP('post');
        }
        if (!isset($variations['activeSets'])) return array();
        if (!isset($variations['cartesian'])) return array();

        /** This will automatically append the existing attributes to a combinations */
        self::compareVariations($variations, $product_id);

        /** For Collecting Wildcard attributes */
        $wildcard = self::collectWildcardAttributes($variations, $product_id);

        if (!isset($variations['cartesian']) OR empty($variations['cartesian'])) return array();
        foreach ($variations['cartesian'] as $c_key => $c_value) {
            $html['combinations'] = count($c_value) - 1;
            $set = 0;
            foreach ($c_value as $c_id => $c_item) {
                foreach ($variations['activeSets'] as $key => $value) {
                    if ($c_id == $key) {
                        $html['html'][$combi_id][$hash] = '<select name="cartrabbit[variant][attr][' . $c_id . '][' . $c_value['product_id'] . ']" id=term_set_' . $c_value['product_id'] . '_' . $set . ' class="terms term_index_' . $set . ' form-control">';
                        $html['html'][$combi_id][$hash] .= '<option value="any_' . $c_id . '">Any ' . $c_id . '..</option>';
                        foreach ($value as $id => $item) {
                            $active = '';
                            if ($c_item == $item) $active = 'selected = "selected"';
                            $html['html'][$combi_id][$hash] .= '<option ' . $active . ' value="' . $item . '">' . $item . '</option>';
                        }
                        $html['html'][$combi_id][$hash] .= '</select>';
                        $set = $set + 1;
                        $html['html'][$combi_id][$hash] .= '<input type="hidden" name="cartrabbit[variant][' . $c_value['product_id'] . ']" value="' . $c_value['product_id'] . '">';

                        /** To Set the Wildcard Select Box */
                    }
                    if (empty($html['product_id'])) {
                        $html['product_id'][] = $c_value['product_id'];
                    } elseif (isset($c_value['product_id']) AND !in_array($c_value['product_id'], $html['product_id'])) {
                        $html['product_id'][] = $c_value['product_id'];
                    }
                    $hash = $hash + 1;
                }
            }

            //TODO : Bug  Extra Select Option is added, if combination already have the attribute

            /**
             * Creating Wildcard Attributes
             */
            $whash = $hash + 1;
            foreach ($wildcard as $w_key => $w_value) {
                foreach ($w_value as $a_key => $a_value) {
                    $html['html'][$combi_id][$whash] = '<select name="cartrabbit[variant][attr][' . $w_key . '][' . $c_value['product_id'] . ']" id=term_set_' . $c_value['product_id'] . '_' . $set . ' class="terms term_index_' . $set . ' form-control">';
                    $html['html'][$combi_id][$whash] .= '<option value="any_' . $w_key . '">Any ' . $w_key . '..</option>';
                    foreach ($a_value as $id => $item) {
                        $html['html'][$combi_id][$whash] .= '<option value="' . $item . '">' . $item . '</option>';
                    }
                    $html['html'][$combi_id][$whash] .= '</select>';
                    $set = $set + 1;
                    $html['html'][$combi_id][$whash] .= '<input type="hidden" name="cartrabbit[variant][' . $c_value['product_id'] . ']" value="' . $c_value['product_id'] . '">';
                }
                $whash = $whash + 1;
            }
            $combi_id = $combi_id + 1;
            $html['id'][] = $c_value['product_id'];
        }
        if (isset($html)) {
            $html['id'] = json_encode($html['id']);
        }
        foreach ($html['product_id'] as $id) {
            $html['meta'][$id] = Post::find($id)->meta()->get()->pluck('meta_value', 'meta_key');
            $html['meta'][$id]['_img'] = wp_get_attachment_url($html['meta'][$id]['_thumbnail_id']);
            $html['meta'][$id]['_thumbnail_id'] = wp_get_attachment_thumb_url($html['meta'][$id]['_thumbnail_id']);
        }
        $html['meta']['shipping_class'] = self::getShippingClasses();
        $html['meta']['tax_class'] = json_decode(self::get('taxClasses'), true);
        return $html;
    }

    /**
     *
     * To Compare the Cartesian and Activesets to re-generate the activesets
     *
     * @param array $variations Variation Sets
     * @param int $product_id Product ID
     */
    public static function compareVariations(&$variations, $product_id)
    {
        foreach ($variations['cartesian'] as $key => $value) {
            foreach ($value as $c_key => &$c_value) {
                if (!isset($variations[$c_key]) and $c_key != 'product_id') {
                    $variations['activeSets'][$c_key] = self::generateAttributes($c_key, $product_id);
                }
            }
        }
    }

    /**
     * @param $name
     * @return array
     */
    public static function generateAttributes($name)
    {
        $key = 'attribute_pro_' . $name . '%';
        $meta = PostMeta::where('meta_key', 'like', $key)->pluck('meta_value', 'meta_key');
        $result = array();
        foreach ($meta as $key => $value) {
            $term = get_term_by('slug', $value, 'pro_' . $name);
            if (!is_null($term) and !empty($term)) {
                $id = $term->term_id;
                $result[$id] = $value;
            }
        }
        return $result;
    }

    /**
     * To Collect the Terms that are not exist in Cartesian, but exist on Product
     *
     * @param array $variations Variation Sets
     * @param int $product_id Product ID
     * @return array Wildcard Sets
     */
    public static function collectWildcardAttributes($variations, $product_id)
    {
        $toAppend = array();
        $attributes = json_decode(Product::init($product_id)->meta->product_attributes);
        Helper\Util::arrayExtractExcept('list', $attributes);
        $toAdd = array();
        $hash = 0;
        foreach ($attributes as $a_key => $a_value) {
            foreach ($variations['cartesian'] as $key => $value) {
                if (!isset($value[$a_key])) {
                    $toAdd[$hash] = $a_key;
                }
            }
            $hash = $hash + 1;
        }
        foreach ($toAdd as $key => $value) {
            $toAppend[$value]['list'] = $variations['activeSets'][$value];
        }
        return $toAppend;
    }

    /**
     * @return array
     */
    public static function getShippingClasses()
    {
        $shipping_classes = get_terms('Shipping_class', array('get' => 'all'));
        $result = array();
        foreach ($shipping_classes as $value) {
            $result[$value->slug] = $value->name;
        }
        return $result;
    }

    /**
     * @param $termList
     */
    public static function reorderTermList($termList)
    {

    }

    /**
     * @param $http
     * @return array
     */
    public static function addNewVariation($http)
    {
        $data = $http->all();
        if (empty($data) or !isset($data)) return false;
        $result = array();
//        $result = self::checkVariantWithRules($data);
//        if (!empty($result['stop'])) return $result;

        if (!$http->has('options')) return array();

        $options = $http->get('options');
        $meta = array();
        foreach ($options as $key => $value) {
            $exp = explode('_', $value[0]);
            $meta[$exp[0]] = $exp[1];
        }
        $id = self::getVariation(true)->count();
        $product_id = Helper\Util::extractDataFromHTTP('post');
        $post = self::createVariation($product_id, $id, $meta);
        self::createVariationMeta($post, $meta);
        return $result;
    }

    /**
     * @param $data
     * @return array
     */
    public static function checkVariantWithRules($data)
    {
        $product_parent = Helper\Util::extractDataFromHTTP('post');
        $newVariant = array();
        $wildcards = array();
        $error = array();
        foreach ($data['options'] as $key => $value) {
            $ext = explode('_', $value[0]);
            $newVariant[$ext[0]] = $ext[1];
            if ($ext[1] == 'any') {
                $wildcards[$ext[0]] = $ext[0];
            }
        }

        /** If there is no more wildcard fields, then return "True" */
        if (empty($wildcards)) return array();

        $variants = Variant::hasVariants($product_parent);
        $variants_list = Variant::extractVariants($variants);

        foreach ($variants_list as $key => $variant) {
            foreach ($variant as $index => $item) {
                if (isset($wildcards[$index])) {
                    if ($item == 'any') {
                        $error['alert'][$index] = $index;
                    } else {
                        $error['stop'][$index] = $index;
                    }
                }
            }
        }
        return $error;
    }


    /**
     * @param bool $byPost
     * @return mixed
     */
    public static function getVariation($byPost = false)
    {
        if ($byPost) {
            $product_id = Helper\Util::extractDataFromHTTP('post');
            $key = 'product-' . $product_id . '-%';
            $variations = Post::where('post_name', 'like', $key)->get();
        } else {
            $variations = Post::where('post_name', 'like', 'product-%')->get();
        }
        return $variations;
    }

    /**
     * @param $http
     * @return bool
     */
    public static function removeVariation($http)
    {
        if (!$http->has('id')) return false;
        $id = $http->get('id');
        Post::where('id', $id)->delete();
        PostMeta::where('post_id', $id)->delete();
    }

    /**
     * @param $list
     * @param $variants
     * @return mixed
     */
    public static function validateVariations($list, $variants)
    {
        foreach ($list as $key => $value) {
            $id = str_replace(']', '', str_replace('cartrabbit[variant][', '', $value['name']));
            $set[$id][] = $value['value'];
        }
        $error['status'] = 'NO CONFLICT';
        foreach ($set as $key => $value) {
            $active_set = $value;
            $active_set_id = $key;
            foreach ($set as $id => $item) {
                if (empty(array_diff($item, $active_set)) AND $active_set_id !== $id) {
                    $error['status'] = 'CONFLICT';
                    $error['from'] = $active_set_id;
                    $error['to'] = $id;
                }
            }
        }
        return $error;
    }

    /**
     *
     */
    public static function removeAttributeCombinations()
    {

    }

    /**
     * @return bool
     */
    public static function isCheckoutPage()
    {

        //TODO check if we are on a checkout page
        return true;
    }

    /**
     * @return bool
     */
    public static function isCartPage()
    {
        //TODO check if we are on a cart page
        return true;
    }

    /** For Dynamic call to get Store Configuration Settings */
    public function __get($key)
    {
        if (isset(self::$settings->$key)) {
            return self::$settings->$key;
        } else {
            return $key;
        }
    }

    /**
     * To Get Sub divides by Country Code's
     *
     * @param $country Country Code
     * @return array of State Lists
     */
    public static function getSubdivisions($country)
    {
        $subdivisions = '';
        if ($country) {
            $zone = new SubdivisionRepository();
            $subDivides = $zone->getAll($country);
            foreach ($subDivides as $subDivid) {
                $subdivisions['Country'][$subDivid->getID()] = $subDivid->getName();
            }
        }
        return $subdivisions;
    }

    /**
     * To Create New Configuration Settings if Not Exist
     * @param $http
     * @return \Herbert\Framework\Response
     */
    public function createGeneralConfiguration($http)
    {
        /** @var $tab To get the selected Countries List */
        $countries = ($http->has('countries') ? $http->get('countries') : '');

        /** @var $tab To get the selected currency */
        $currency = ($http->has('currency') ? $http->get('currency') : 'INR');

        /** @var $tab To get Logo */
        $logo = ($http->hasFile('logo') ? $http->file('logo') : '');

        $fields = array(
            'store_name',
            'address1',
            'address2',
            'city',
            'postal',
            'terms',
            'store_country',
            'store_state',
            'sell_location',
            'position',
            'separator',
            'dec_separator',
            'no_dec_value',
            'currency',
            'logo_id',
            /** For Product Display Config. */
            'page_to_list_product',
            'page_to_cart_product',
            'page_to_account',
            'page_to_checkout',
            'page_to_thank'
        );

        foreach ($fields as $field) {
            $data[$field] = $http->get($field);
        }

        $data['logo_path'] = Helper::get('upload_dir');
        if ($data['sell_location'] != 'sell_to_all') {
            $data['countries'] = json_encode($countries);
        } else {
            $data['countries'] = '*';
        }
        $data['currency'] = $currency;

        //TODO : Need to validate image to '.PNG'

        /**
         * 103 storing image by WordPress default image upload
         */

        if ($logo !== '') {
            $_thumbnail_id = Post::type('attachment')->where('guid', $logo)->pluck('ID');
            $data['logo_id'] = $_thumbnail_id[0];
        }

        $post = new Post();
        $post->post_title = "CartRabbit Cart Configuration";
        $post->post_type = "cartrabbit_config";

        $post->save();
        foreach ($data as $key => $val) {
            $post->meta->$key = $val;
        }
        $post->save();
    }

    /**
     * To Update Cart if already exist
     *
     * @param $http Http instance for access post data
     *
     * @return status of updated cart
     */
    public function updateGeneralConfig($http)
    {
        /** @var $tab To get the selected Countries List */
        $countries = ($http->has('countries') ? $http->get('countries') : '');
        /** @var $tab To get Logo */
//        $logo = ($http->hasFile('logo') ? $http->file('logo') : '');
        $logo = ($http->has('logo') ? $http->get('logo') : '');
        $post = Post::where('post_type', 'cartrabbit_config')->first();
//        $post = Post::find($id['ID']);

        $fields = array(
            'store_name',
            'address1',
            'address2',
            'city',
            'postal',
            'terms',
            'store_country',
            'store_state',
            'sell_location',
            'position',
            'separator',
            'dec_separator',
            'no_dec_value',
            'currency',
            'logo_id',
            /** For Product Display Config. */
            'page_to_list_product',
            'page_to_cart_product',
            'page_to_account',
            'page_to_checkout',
            'page_to_thank'
        );

        foreach ($fields as $field) {
            $data[$field] = $http->get($field);
        }

        $data['logo_path'] = Helper::get('upload_dir');
        if ($data['sell_location'] != 'sell_to_all') {
            $data['countries'] = json_encode($countries);
        } else {
            $data['countries'] = '*';
        }

        //TODO : Need to validate as image is '.PNG' or Not

        /**
         * 103 storing image by WordPress default image upload
         */

        if ($logo !== '') {
            $_thumbnail_id = Post::type('attachment')->where('guid', $logo)->pluck('ID');
            $data['logo_id'] = $_thumbnail_id->first();
        }

        foreach ($data as $key => $val) {
            $post->meta->$key = $val;
        }

        $post->save();
    }

    /**
     * @param $image
     * @return bool
     */
    public function validateImage($image)
    {

        $maxImageSizeAllowed = 500;
        $fileTypeAllowed = 'image/png';

        $size = round($image->getSize() / 1024);
        $fileType = $image->getMimeType();

        if ($size < $maxImageSizeAllowed && $fileTypeAllowed == $fileType) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * To Upload an Image
     * @param $logo File Instance
     * @param string $name Name of Image to be Set
     * @return string Status
     */
    public static function uploadImage($logo, $name)
    {
        $path = Helper::get('upload_dir');

        if ($logo->move($path, $name)) {
            return $name;
        } else {
            return 'NoImage';
        }
    }

    /**
     * To Create Tax Configuration if Not Exist
     * @param $http Http instance for access post data
     * @return  bool
     */

    public function createTaxConfigurations($http)
    {
        $data = $http->all();
        if (!data or empty($data)) return false;
        $post = new Post();
        $post->post_title = 'CartRabbit Cart Tax Configurations';
        $post->post_type = 'cartrabbit_config';
        $post->save();
        if ($data['tax_enable_tax'] == '') {
            $data['tax_enable_tax'] = 'off';
        }
        foreach ($data as $key => $val) {
            $post->meta->$key = $val;
        }
        $post->save();
    }

    /**
     * To Update Tax Configuration if Exist
     *
     * @param $http Http instance for access post data
     * @return  bool
     */

    public function updateTaxConfigurations($http)
    {
        $post = Post::where('post_type', 'cartrabbit_config')->get()->first();

        $data = $http->all();
        if (!data or empty($data)) return false;
        if ($data['tax_enable_tax'] == '') {
            $data['tax_enable_tax'] = 'off';
        }
        foreach ($data as $key => $val) {
            $post->meta->$key = $val;
        }
        $post->save();
    }

    /**
     * To Get Formatted Store Address
     *
     * @return array of Store Address
     */
    public function getStoreAddress()
    {
        $store = $this->getConfigurationMetas();
        if (!empty($store)) {
            $address['countryCode'] = $store['store_country'];
            $address['administrativeArea'] = $store['store_state'];
            $address['locale'] = $store['city'];
            $address['address1'] = $store['address1'];
            $address['address2'] = $store['address2'];
            $address['organization'] = $store['store_name'];
            $address['postalCode'] = $store['postal'];
        }
        return (isset($address['countryCode'])) ? $address : null;
    }

    /**
     * Get Cart Configuration's All Meta
     * @return mixed Meta Value
     */
    public function getConfigurationMetas()
    {
        $post = Post::where('post_type', 'cartrabbit_config')->first();
        $value = '';
        if (count($post) != 0) {
            $meta = Post::find($post['ID']);
            $value = $meta->meta()->pluck('meta_value', 'meta_key');
        }
        return $value;
    }

    /**
     * To Get Formatted Customer Address
     *
     * @return array of Customer Address
     */
    public function getCustomerAddress()
    {
        $data = (new Customer())->getBillingAddress();
        if (!is_null($data)) {
            $customerAddress = array(
                'countryCode' => $data['country'],
                'administrativeArea' => $data['zone'],
                'locale' => $data['city'],
                'address1' => $data['address1'],
                'address2' => $data['address2'],
                'organization' => $data['company'],
                'postalCode' => $data['postalCode'],
            );
        }

        return (!empty($customerAddress)) ? $customerAddress : null;
    }

    /**
     * To Add Secondary Currency
     * @param $http Instance for access Post Data's
     */

    public function createSecondaryCurrency($http)
    {
        $datas = $http->all();
        $datas = $datas['cartRabbit']['secCorrency'];

        $post = new Post();
        $post->post_title = "Secondary Currencies ";
        $post->post_type = "cartrabbit_currency";
        $post->save();
        foreach ($datas as $data) {
            $key = $data['currency'];
            $val = json_encode($data);
            $post->meta->$key = $val;
        }
        $post->save();
    }

    /**
     * To Update Secondary Currencies
     * @param $http Instance for access Post Data's
     */

    public function updateSecondaryCurrency($http)
    {
        $datas = $http->all();
        $datas = $datas['cartRabbit']['secCorrency'];
        $id = Post::where('post_type', 'cartrabbit_currency')->first();
        $post = Post::find($id['ID']);
        foreach ($datas as $data) {
            $key = $data['currency'];
            $val = json_encode($data);
            $post->meta->$key = $val;
        }
        $post->save();
    }

    /**
     * To Retrieve the Tax Configuration Form Data's
     * @return Converted Array Format
     */

    public function getTaxConfigurationDatas()
    {
        $post_id = Post::where('post_type', 'cartrabbit_config')->pluck('ID')->first();
        if (is_null($post_id)) return false;
        $posts = Post::find($post_id)->meta()->select('meta_key', 'meta_value')->get();
        return (new MetaConverter())->keyValConverter($posts);
    }

    /**
     * To Remove Secondary Currency
     * @param $currency
     * @return bool
     */
    public function removeCurrency($currency)
    {
        if ($currency !== null) {
            $id = Post::where('post_type', 'cartrabbit_currency')->first();
            delete_post_meta($id['ID'], $currency);
            return true;
        }
        return false;
    }

    /**
     * To Set the Page to Display the Products
     * @param $page Post ID
     */
    public function setPageToDisplay($page)
    {
        $config = Post::where('post_type', 'cartrabbit_config')->first();
        $display = array(
            'page_to_list_product',
            'page_to_cart_product',
            'page_to_account',
            'page_to_checkout',
            'page_to_thank'
        );
        foreach ($display as $toPage) {
            $config->meta->$toPage = $page[$toPage];
        }
        $config->save();
    }

    /**
     * @param $http
     */
    public function saveCartConfig($http)
    {
        $id = self::getStoreConfigID();
        $data = $http->all();
        if (!is_null($data) and !empty($data)) {
            $cart = Post::find($id);
            foreach ($data as $index => $item) {
                $cart->meta->$index = $item;
            }
            $cart->save();
        }
    }

    /**
     * @param $http
     * @return bool
     */
    public function saveProductDisplayConfigurations($http)
    {
        $id = self::getStoreConfigID();
        $data = $http->all();
        if (empty($data) or !isset($data)) return false;
        $configuration = Post::find($id);
        foreach ($data as $key => $value) {
            $configuration->meta->$key = $value;
        }
        $configuration->save();

    }

    /**
     * To Get Default Store Configuration ID
     *
     * @return Post ID
     */
    public static function getStoreConfigID()
    {
        $ID = Post::where('post_type', 'cartrabbit_config')->pluck('ID')->first();
        return ($ID) ? $ID : false;
    }

    /**
     * To Get Display Setup Config's
     *
     * @param bool $isDashboard
     * @return mixed
     */
    public static function getDisplaySetup($isDashboard = false)
    {
        $store_id = self::getStoreConfigID();
        if (!$store_id) return [];
        $list = [];
        if ($isDashboard == false) {
            $list['page'] = Post::where('post_type', 'page')->pluck('post_name', 'ID');
        }
        $pages = Post::find($store_id)
            ->meta()
            ->where('meta_key', 'like', 'page_to_%')
            ->pluck('meta_value', 'meta_key');

        foreach ($pages as $key => $page) {
            $key = str_replace('page_to_', '', $key);
            $list[$key] = $page;
        }
        return $list;
    }

    /**
     * @return array
     */
    public function getProductDisplaySetup()
    {
        $id = self::getStoreConfigID();
        if (!$id or empty($id)) return array();
        $config = Post::find($id)->meta()
            ->where('meta_key', 'like', 'd_config_%')
            ->pluck('meta_value', 'meta_key');
        return $config;
    }

    /**
     * Get Cart Configuration's specific Meta
     * @param $meta_key Cartrabbit Configuration Meta Key
     * @return mixed Meta Value
     */
    public function getConfigurationMeta($meta_key)
    {
        $post = Post::where('post_type', 'cartrabbit_config')->first();
        if (count($post) != 0) {
            $value = $post->meta()->where('meta_key', $meta_key)->pluck('meta_value');
            $return = $value;
        } else {
            $return = '';
        }
        return $return;

    }

    /**
     * To Get Store Configuration Data's
     *
     * @return array of Store Configuration Data's
     */
    public function getStoreConfigurations()
    {
        $id = self::getStoreConfigID();
        $config = array();
        if ($id) {
            $config = Post::find($id)->meta()->pluck('meta_value', 'meta_key');
        }
        return $config;
    }

    /**
     * To Add Brands for Products
     */
    public function addBrandTaxonomy($taxonomy)
    {
        if (!is_null($taxonomy)) {
            foreach ($taxonomy as $term) {
                wp_insert_term($term, 'Brands');
            }
        }
    }

    /**
     * To Get Brand Taxonomies
     */
    public function getBrandTaxonomy()
    {
        return self::getProductAttributes('product_brands');
    }

    /**
     * To Remove Term by Id
     * @param $term_id integer id Of Category
     */
    public function removeBrandTaxonomybyID($term_id)
    {
        if (!is_null($term_id)) {
            wp_delete_term($term_id, 'Brands');
        }
    }

    /**
     * Return the Processed Products Content for MetaBox.
     * @param $post_id
     * @param $data
     * @return mixed
     */
    public function processProductContents($post_id, $data)
    {
        $product = Product::getInstance($post_id);
        $data['meta'] = $product->find($post_id)->meta()->pluck('meta_value', 'meta_key')->toArray();
        $data['meta']['up_sells'] = json_decode(array_get($data['meta'], 'up_sells', '[]'));
        $data['meta']['cross_sells'] = json_decode(array_get($data['meta'], 'cross_sells', '[]'));
        $data['special_price'] = Price::where('post_id', $post_id)->get();
        $this->processAttributes($data);

        return $data;
    }

    /**
     * Return the Product's attributes.
     * @param $data
     */
    public function processAttributes(&$data)
    {
        $setting = new Settings();
        $data['meta']['attributes'] = Settings::getProductAttributes();
        $data['meta']['product_attributes'] = (!isset($data['meta']['product_attributes']) ? array() : json_decode($data['meta']['product_attributes'], true));

        if (!empty($data['meta']['product_attributes'])) {
            foreach ($data['meta']['product_attributes'] as $key => $option) {
                unset($data['meta']['attributes'][$key]);
                $data['meta']['attribute_list'][$key] = Settings::getProductAttributes($key);
            }
            $data['variations'] = $setting->processVariationsList($data['meta']['product_attributes']);
        }
    }

    /**
     * To Save Inventory Configurations
     *
     * @param $config array of Inventory Configurations
     * @return bool
     */
    public function saveInventoryConfig($config)
    {
        $id = self::getStoreConfigID();
        if (!$id) return false;
        $inventory = Post::find($id);
        foreach ($config as $key => $val) {
            $inventory->meta->$key = $val;
        }
        $inventory->save();
    }

    /**
     * To Save Products General Configurations
     *
     * @param $config array of Configuration data from FORM
     * @return bool
     */
    public function saveProductGeneralConfig($config)
    {
        $id = self::getStoreConfigID();
        if ($id) return false;
        $productGeneral = Post::find($id);
        foreach ($config as $key => $val) {
            $productGeneral->meta->$key = $val;
        }
        $productGeneral->save();
    }

    /**
     * TO Get Product's General Configuration Settings, based on its ID
     * @return array of Configuration Details
     */
    public function getProductGeneralConfig()
    {
        $id = self::getStoreConfigID();
        $general = array();
        if ($id) {
            $general = Post::find($id)
                ->meta()
                ->where('meta_key', 'LIKE', 'pro_gen_%')
                ->pluck('meta_value', 'meta_key');
        }
        return $general;
    }

    /**
     * To Get inventory Configurations
     *
     * @return array of Inventory Details
     */
    public function getInventoryConfig()
    {
        $id = self::getStoreConfigID();
        $inventory = array();
        if ($id) {
            $inventory = Post::find($id)
                ->meta()
                ->where('meta_key', 'LIKE', 'inventory_%')
                ->pluck('meta_value', 'meta_key');
        }
        return $inventory;
    }

    /**
     * @param $http instance For Access Post Datas
     * @return string JSON encoded Product List
     */
    public function getProductsbyName($http)
    {
        $search = ($http->has('search') ? $http->get('search') : '');
        // Reject Search, if length is too low.
        if (strlen($search) <= 1) return '';

//        $post = Post::where('post_type', 'cartrabbit_product')
//            ->orWhere('post_type', 'cartrabbit_variant')
//            ->where('post_name', '!=', 'AUTO DRAFT')
//            ->where('post_name', '!=', '')
//            ->where('post_name', 'LIKE', '%' . $search . '%')
//            ->pluck('post_title','');

        $posts = ProductBase::join('postmeta', 'posts.ID', '=', 'postmeta.post_id')
            ->where('posts.post_name', 'LIKE', '%' . $search . '%')
            ->where('posts.post_name', '!=', 'AUTO DRAFT')
            ->where('posts.post_name', '!=', '')
            ->where('postmeta.meta_key', 'visible_on_storefront')
            ->where('postmeta.meta_value', 'yes')
            ->orWhere('posts.post_parent', '!=', 0)
            ->where('posts.post_title', '!=', 'Auto Draft')->get();

        $out = [];
        foreach ($posts as $index => $post) {
            if ($post->meta->sku != '') {
                $out[$post->post_title] = $post->meta->sku;
            }
        }
        return json_encode($out);
    }

    /**
     * To Return Samples for CartRabbit Permalink Options Page
     *
     * @return mixed array of Sample Links
     */
    public function getPermalinkSamples()
    {
        $shop_page_id = get_page_by_title('products');
        $base_slug = urldecode(($shop_page_id > 0 && get_post($shop_page_id)) ? get_page_uri($shop_page_id) : _x('shop', 'default-slug', 'cartrabbit'));

        $product = Post::where('post_type', 'cartrabbit_product')
            ->where('post_name', '!=', '')
            ->limit(1)->pluck('ID', 'post_name')->toArray();
        $product_name = key($product);
        $link['default'] = get_permalink($product[$product_name]);
        $link['shop'] = Helper::get('site_addr') . '/product/' . $product_name;
        $link['shopWithCat'] = Helper::get('site_addr') . '/' . $base_slug . '/product-category/' . $product_name;
        return $link;
    }

    /**
     * @param $http
     */
    public function saveAttributes($http)
    {
        $product_id = Helper\Util::extractDataFromHTTP('post');
        //TODO: Optimize this
        $rawTaxonomy['before'] = Product::init($product_id)->meta->product_attributes;

        if (!empty($rawTaxonomy['before'])) $rawTaxonomy['before'] = json_decode($rawTaxonomy['before']);

        Helper\Util::arrayExtractExcept('list', $rawTaxonomy['before']);

        /** This one save the terms of the product */
        (new Settings())->savePostTermMeta($http);

        /** Collects the Updated terms JSON */
        $rawTaxonomy['after'] = json_decode(Product::init($product_id)->meta->product_attributes);

        Helper\Util::arrayExtractExcept('list', $rawTaxonomy['after']);
    }

    /**
     * @param $http
     */
    public function savePostTermMeta($http)
    {
        $data = $http;

        $product_id = Helper\Util::extractDataFromHTTP('post');
        $option = array();

        foreach ($data->get('items') as $key => $value) {
            $value['id'] = $value['value'];
            if (str_contains($value['name'], '_option')) {
                unset($value[$key]['value']);
                unset($value[$key]['name']);
                $option_name = str_replace('_option', '', $value['name']);
                $option[$option_name]['list'][] = str_replace('_option', '', $value['id']);
            } elseif (str_contains($value['name'], '_used_for_variant')) {
                $option[$option_name]['is_used_for_variant'] = $value['id'];
            } elseif (str_contains($value['name'], '_visable_on_produt')) {
                $option[$option_name]['isVisible'] = $value['id'];
            }
        }

        foreach ($option as $key => $item) {
            $option_name = $key;
            foreach ($item['list'] as $key => $value) {
                $term_ids[] = $value;
            }

            /** Here the assigned terms are flushed */
            wp_set_post_terms($product_id, '', 'pro_' . $option_name);
            /** Here the terms are going to assigned newly */
            wp_set_post_terms($product_id, $term_ids, 'pro_' . $option_name);
        }

        $post = Post::find($product_id);
        $post->meta->product_attributes = json_encode($option);
        $post->save();
    }

    /**
     * To Save Permalink Settings for manage User Friendly URL's
     *
     * @param $post Instance for Access Form Data's
     */
    public function savePermalinkSettings($post)
    {
        $required = array(
            'product_category_base',
            'product_tag_base',
            'permalink'
        );

        // Var $type is used to manage the Permalink Type.
        $type = '';

        // If Permalink Structure is Selected as Plain, then Use get_permalink() to Process link
        if (!isset($post['permalink']) or $post['selection'] == '') {
            $post['permalink'] = 'plain';
        } elseif ($post['permalink'] == 'custom') {
            $type = 'custom';
            $post['permalink'] = $post['permalink_custom'];
        }
        $data['type'] = $type;
        foreach ($required as $require) {
            $data[$require] = $post[$require];
        }

//        if (get_option('cartrabbit_permalink')) {
//            update_option('cartrabbit_permalink', json_encode($data));
//        } else {
//            add_option('cartrabbit_permalink', json_encode($data));
//        }

        $options = new Option();
        $option = $options->where('option_name', 'cartrabbit_permalink')->get();
        if ($option->count() == 0) {
            // Create Permalink Option, if Not Exist
            $option = new Option();
            $option->option_name = 'cartrabbit_permalink';
            $option->option_value = json_encode($data);
            $option->save();
        } else {
            //Update Permalink Option, if Exist
            $option = $option->first();
            $option->option_value = json_encode($data);
            $option->save();
        }

    }

    /**
     *To Get Permalink Configuration Data's
     *
     * @return array of Permalink Configuration data's
     */
    public function getPermalinkSettings()
    {
        if (empty(self::$permanant_links)) {
            $option = Option::where('option_name', 'cartrabbit_permalink')->first();
            $data = isset($option->option_value) ? json_decode($option->option_value, true) : array();

            //initialise the basic settings.
            if (!isset($data['product_category_base'])) $data['product_category_base'] = 'category';
            self::$permanant_links = $data;
        }
        return self::$permanant_links;

    }

    /**
     * To Save Tax classes
     *
     * @param object $http instance of Post data
     * @return array|bool
     */
    public function saveTaxClasses($http)
    {
        $taxClass = $http->has('tax_class') ? $http->get('tax_class') : false;

        if (!$taxClass) return false;
        $taxClass = strtolower($taxClass);
        $taxClass = str_replace(' ', '_', $taxClass);
        $taxConfiguration = $this->getTaxConfiguration();
        $configTaxClass = json_decode($this->getTaxConfigurationMeta('tax_classes'), true);

        if (is_null($configTaxClass)) $configTaxClass = array();

        if (Helper\Util::checkExist($configTaxClass, $taxClass) !== false) return false;

        if (is_null($taxConfiguration)) return array();

        if ($taxClass) {
            array_push($configTaxClass, $taxClass);
            $taxConfiguration->meta->tax_classes = json_encode($configTaxClass);
            $taxConfiguration->save();
        }
    }

    /**
     * @return mixed
     */
    public function getTaxConfiguration()
    {
        return Post::where('post_type', 'cartrabbit_config')->get()->first();
    }

    /**
     * @param $meta_key
     * @return null
     */
    public function getTaxConfigurationMeta($meta_key)
    {
        $post = Post::where('post_type', 'cartrabbit_config')->get()->first();
        if (empty($post)) return array();
        $meta = $post->meta()->get()->where('meta_key', $meta_key)->pluck('meta_value', 'meta_key')->first();
        return (!empty($meta)) ? $meta : null;
    }

    /**
     * To Return stored tax classes
     *
     * @return array|mixed
     */
    public function getTaxClasses()
    {
        $taxClass = $this->getTaxConfigurationMeta('tax_classes');
        return (!is_null($taxClass) ? json_decode($taxClass, true) : array());
    }

    /**
     * To Remove stored tax class
     *
     * @param object $http instance of Post data
     * @return bool
     */
    public function removeTaxClass($http)
    {
        $configTaxClass = json_decode($this->getTaxConfigurationMeta('taxClasses'), true);
        $class_id = $http->has('class_id') ? $http->get('class_id') : false;
        $taxConfiguration = $this->getTaxConfiguration();

        $index = array_search($class_id, $configTaxClass);
        unset($configTaxClass[$index]);
        $taxConfiguration->meta->taxClasses = json_encode($configTaxClass);
        $taxConfiguration->save();
        return true;
    }

    /**
     * To Get Basic Setup for the Product Create Properties
     *
     * @param object $http instance of Post data
     * @param array $data Collection of Configuration data
     * @return mixed Modified setup data
     */
    public function getBasicSetup($http, $data)
    {
        /** If the Product in Edit State */
        if ($http->has('action') AND $http->get('action') == 'edit') {
            $config['isEdit'] = true;
            if (isset($data['meta']['product_type'])) {
                if ($data['meta']['product_type'] == 'variableProduct') {
                    $config['isVariant'] = true;
                } else {
                    $config['isVariant'] = false;
                }
            }
        } else {
            $config['isEdit'] = false;
        }
        return $config;
    }

    /**
     * To Get Term Metas
     *
     * @param object $http instance of Post data
     */
    public function getTermMembers($http)
    {
        $term_id = $http->get('term_id');
        $term = get_term($term_id);
//        dd(get_term_children($term_id, 'pro_' . $term->slug));
//        dd(get_term_children($term_id, 'pro_' . $term->slug));
    }

    /*
     *  103 Get Shipping Classes
     */

    /**
     * @return string
     */
    public function defaultAddressforTax()
    {
        return self::get('tax_start_up_address', 'storeAddress');
    }

    public static function baseLocation()
    {
        $default = apply_filters('sp_get_base_location', self::get('store_country'));

        return $default;
    }

    public static function taxOrVat()
    {

        $return = in_array(self::baseLocation(), self::getEUCountries('eu_vat')) ? __('VAT', 'cartrabbit') : __('Tax', 'cartrabbit');

        return apply_filters('sp_countries_tax_or_vat', $return);

    }

    public static function getEUCountries($type = '')
    {
        $countries = array('AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GB', 'GR', 'HU', 'HR', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK');

        if ('eu_vat' === $type) {
            $countries[] = 'MC';
            $countries[] = 'IM';
        }

        return $countries;
    }

}