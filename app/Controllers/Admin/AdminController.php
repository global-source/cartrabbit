<?php

namespace CartRabbit\Controllers\Admin;

use CommerceGuys\Zone\Repository\ZoneRepository;
use Corcel\Post;
use Herbert\Framework\Http;

use CartRabbit\Helper;
use CartRabbit\Helper\EventManager;
use CartRabbit\Models\Admin;
use CartRabbit\Models\postNew as postNew;
use CartRabbit\Controllers\BaseController;

use CartRabbit\Models\Product;
use CartRabbit\Models\ProductBase;
use CartRabbit\Models\Products;

use CartRabbit\Models\ProductVariable;
use CartRabbit\Models\Settings;
use Illuminate\Database\Capsule\Manager as Capsule;
use CartRabbit\Models\VariableProduct;
use CartRabbit\Models\Variant;

/**
 * Class Admin
 * @package CartRabbit\Controllers
 */
class AdminController extends BaseController
{
//    protected $event;


    /**
     * Admin Controller constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * To Get the Metabox for Add New product
     *
     * @return Admin product metabox Twig
     */
    public function getProductMetabox(Http $http)
    {
        $product = new Products();
        $setting = new Settings();

        /** Get Post id from Model Products, If New Product then return NULL. */
        $post_id = $http->has('post') ? $http->get('post') : null;

        $data['meta']['product_attributes'] = array();
        /** Data Processing is not available for New Product. */
        if (!is_null($post_id)) {
            $data = (new Settings())->processProductContents($post_id, $data);
        }

        $taxClasses = json_decode(Settings::get('tax_classes', '[]'), true);
        if (!is_array($taxClasses) AND empty($taxClasses)) {
            $taxClasses = '';
        }
        $data['meta']['taxClasses_list'] = $taxClasses;
        $data['meta']['shipping_class_list'] = Settings::getShippingClasses();

        $config = $setting->getBasicSetup($http, $data);

        $metrics = $product->getProductMetrics();
        $brands = $setting->getBrandTaxonomy();

        $units_type = array('Nos', 'Kg', 'Ltr', 'Mtr', 'Feet');
        return parent::view('Admin.Products.metabox', compact('units_type', 'brands', 'data', 'metrics', 'config'));
    }

    /**
     * To Generate variant Combinations with given option sets
     *
     * @return array of Cartesian combinations
     */
    public function getVariantCombinations()
    {
        $product_id = Helper\Util::extractDataFromHTTP('post');
        return json_encode((new Settings())->getVariationsList(Product::init($product_id)->meta->product_attributes));
//        return json_encode((new Settings())->getVariationsList(ProductBase::get('product_attributes', '', $product_id)));
    }

    /**
     * @param Http $http
     * @return \CartRabbit\Controllers\display
     */
    public function generateVariationList(Http $http)
    {
        /** To check the need to reset the combination or not */
        Settings::refreshAttributes($http);
        $variations = Settings::getVariationsList();
        $html = Settings::generateVariationHtml($variations);
        return parent::view('Admin.Products.Variations.generate_variation_product', compact('html', 'variations'));
    }

    /**
     * To Add Individual Variation of the Product
     *
     * @param Http $http
     * @return array result
     */
    public function addVariation(Http $http)
    {
        return Settings::addNewVariation($http);
    }

    /**
     * To Remove Individual Variation of the Product
     * @param Http $http
     * @return bool
     */
    public function removeVariation(Http $http)
    {
        if ($http->has('reset') and $http->get('reset') == 'true') {
            Settings::resetVariations();
        } else {
            return Settings::removeVariation($http);
        }
    }

    /**
     * To Save Multiple Variations of a Single Product
     *
     */
    public function saveVariationProducts(Http $http)
    {
        ProductVariable::saveVariants($http);
    }

    /**
     * @param $http Http instance for access post data
     * @return array
     */
    public function extractAttributes(Http $http)
    {
        $term_id = ($http->has('term_id') ? $http->get('term_id') : null);

        if (is_null($term_id)) return array();

        echo json_encode(Settings::getProductAttributes($term_id));
    }

    /**
     * @param Http $http
     * @return mixed
     */
    public function validateVariationList(Http $http)
    {
        $list = ($http->has('list') ? $http->get('list') : array());
        $variants = ($http->has('variants') ? $http->get('variants') : array());
        return Settings::validateVariations($list, $variants);
    }

    /**
     * @param $http Http instance for access post data
     */
    public function saveProductAttributes(Http $http)
    {
        (new Settings())->saveAttributes($http);
    }

    /**
     * @param $http Http instance for access post data
     */
    public function removeProductAttrOption(Http $http)
    {
        Settings::removeTermOption($http);
        Settings::refreshAttributes($http);
    }

    /**
     * To Get the JSON formated states list in belongs to the given Country
     *
     * @param $http Http instance for access post data
     * @return JSON formated States list
     */
    public function getStates(Http $http)
    {
        $country = ($http->has('country') ? $http->get('country') : '');
        $response = Helper\Util::getStatesByCountryCode($country);
        return $response;
    }

    public function validateEmail(Http $http)
    {
        $email = $http->has('email') ? $http->get('email') : false;
        if ($email) {
            echo Helper\Util::validateEmail($email);
        } else {
            echo 'false';
        }
    }

    /**
     * Get Current Cart Configuration metas
     *
     * @return converted meta format
     */
    public function getCartConfig()
    {
        $admin = new Admin();
        return $admin->getCartConfiguration();
    }

    /**
     * Initial Level CartRabbit Checker
     * If Root Configuration Data's are Not Exist,
     * Then Create New Configuration Data's
     */
    public function bootLoader()
    {
        if (!Settings::getStoreConfigID()) {
            /** Create Dummy Congiguration Data */

        }
    }


    /**************************************************************************************
     * Temporary Testing Function
     **************************************************************************************/
    public function test2()
    {


    }


    /** Category Based Search */

    public function shop($category, $slug)
    {
        $id = $this->getIdBySlug($slug);
        $terms = $this->getCategory($category);
        $taxonomy_id = array_search($terms["term_id"], $terms["taxonomy_id"]);
        if ($terms) {
            $post_id = $this->checkProduct($taxonomy_id, $id);
        }
        $post_id = $post_id[array_keys($post_id)[0]][0]->object_id;
        $this->getProduct($post_id);

    }

    /**
     * To Get Product ID by It's Slug
     *
     * @param $slug
     * @return mixed
     */
    public function getIdBySlug($slug)
    {
        return Post::where('post_name', (string)$slug)->get()
            ->pluck('ID')->toArray();
    }

    /**
     * To Get Available Categories from the Registered Taxonomies
     *
     * @param string $category with Specific Category
     * @return bool
     */
    public function getCategory($category)
    {
        $cartRabbit_Category = 'genre';

        $option = Capsule::table('term_taxonomy')->where('taxonomy', $cartRabbit_Category)
            ->pluck('term_id', 'term_taxonomy_id')->toArray();

        $categories['category_name'] = Capsule::table('terms')->where('slug', $category)->pluck('term_id');

        if (in_array($categories['category_name'][0], $option)) {
            $res['term_id'] = $categories['category_name'][0];
            $res['taxonomy_id'] = $option;
        } else {
            $res = false;
        }
        return $res;
    }

    /**
     * To Check the product is belongs to the category or not
     *
     * @param inte $taxonomy_id Category ID
     * @param integer $id Product ID
     * @return array product
     */
    public function checkProduct($taxonomy_id, $id)
    {
        foreach ($id as $post_id) {
            $posts[] = Capsule::table('term_relationships')
                ->where('term_taxonomy_id', '=', $taxonomy_id)
                ->where('object_id', '=', $post_id)
                ->get()->toArray();
        }
        foreach ($posts as $key => $val) {
            if (empty($val) or is_null($val)) {
                unset($posts[$key]);
            }
        }
        return $posts;
    }

    /**
     * To Get product by its ID
     *
     * @param $id Post ID
     * @return mixed Product
     */
    public function getProduct($id)
    {
        $product = Post::where('ID', $id)->get();
        return $product;
    }
}