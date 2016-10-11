<?php

namespace CartRabbit\Controllers\Products;

use Corcel\PostMeta;
use Herbert\Framework\Models\Post;
use Herbert\Framework\Http;

use CartRabbit\Helper;
use CartRabbit\Controllers\BaseController;
use CartRabbit\library\Pagination;
use CartRabbit\Models\Cart;
use CartRabbit\Models\Currency;
use CartRabbit\Models\Price;
use CartRabbit\Models\Product;
use CartRabbit\Models\ProductBase;
use CartRabbit\Models\Products;
use CartRabbit\Models\Admin;
use CartRabbit\Models\Settings;
use CartRabbit\Models\Variant;

/**
 * Class Products
 * @package CartRabbit\Controllers
 */
class ProductsController extends BaseController
{

    /**
     * To set the current currency type
     * @param $http Http instance for access post data
     */
    public function __construct(Http $http)
    {
        parent::__construct();
        if ($http->has('currency')) Session()->set('currency', $http->get('currency'));
    }

    /**
     * To Display all products with its details
     * @param $http Http instance for access post data
     * @return view of Available Products
     */
    public function getProducts(Http $http)
    {
        $currency = new Helper\Currency();
        $setting = new Settings();
        $limit = ($http->has('limit') ? $http->get('limit') : 5);
        $Page = ($http->has('ppage') ? $http->get('ppage') : 1);
        $segments = $http->segments();
        $pagination = new Pagination();

        $productsModel = new Products();

        $productsModel->setLimit($limit);
        $productsModel->setPerPage($Page);
        $productsModel->setSegments($segments);
        $products = $productsModel->get_products();
        $pagination = $productsModel->generatePagination();

        $currency->setSegments($segments);
        $setting = Settings::getProductDisplayConfig();

        return parent::view('Site.Product.showProducts', compact('products', 'pagination', 'setting'));
    }

    public function secondaryCurrency()
    {
        $currency = new Helper\Currency();
        $secondaryCurrency = $currency->getSecondaryCurrencies();
        return parent::view('Site.Product.showSecondaryCurrency', compact('secondaryCurrency'));
    }

    /**
     * To Get Complete Product Details
     * @param $post_id
     * @return \CartRabbit\Controllers\display
     */
    public function viewProduct($post_id)
    {
        $product_id = $post_id['postId'];

        if (!isset($product_id) or empty($product_id)) {
            $out = [];
        } else {
            $product = Product::init($product_id);
            $product->processProduct();

            $product->setRelation('meta', $product->meta->pluck('meta_value', 'meta_key'));
//            $out['meta'] = $product->meta->pluck('meta_value', 'meta_key');
//            $out['html'] = Variant::getVariantHtml($product['meta']);
        }
//        $product = $out;
        $currency = new Helper\Currency();
        $setting = Settings::getProductDisplayConfig();

        return parent::view('Site.Product.viewProduct', compact('product', 'setting', 'currency'));
    }

    /**
     * To Display Product Cart alone
     * @param $post_id
     * @return \CartRabbit\Controllers\display
     */
    public function viewProductTitle($post_id)
    {
        $product = new Products();
        $product->setProductId($post_id['postId']);
        $title = $product->getSingleProductTitle();
        return parent::view('Site.Product.viewTitle', compact('title'));
    }

    /**
     * To Display Product Image alone
     * @param $post_id
     * @return \CartRabbit\Controllers\display
     */
    public function viewProductImage($post_id)
    {
        $product = new Products();
        $product->setProductId($post_id['postId']);
        $image = $product->getSingleProductImage();
        return parent::view('Site.Product.viewImage', compact('image'));
    }

    /**
     * To Display Product Price alone
     * @param $post_id
     * @return \CartRabbit\Controllers\display
     */
    public function viewProductPrice($post_id)
    {
        $simpleProduct = new Helper\SimpleProduct();
        $simpleProduct->setProductId((int)$post_id['postId']);
        $product = Product::where('ID', $post_id)
            ->first()
            ->meta()
            ->get()
            ->pluck('meta_value', 'meta_key');
        $price = $simpleProduct->getPrice($product);
        return parent::view('Site.Product.viewPrice', compact('price'));
    }

    /**
     * To Display Product description alone
     * @param $post_id
     * @return \CartRabbit\Controllers\display
     */
    public function viewProductDescription($post_id)
    {
        $product = new Products();
        $product->setProductId($post_id);
        $description = $product->getSingleProductDescription();
        return parent::view('Site.Product.viewDescription', compact('description'));
    }

    /**
     * To Display Product Cart alone
     * @param $post_id
     * @return \CartRabbit\Controllers\display
     */
    public function viewProductCart($post_id)
    {
        $post_id = $post_id['postId'];
        $product = new Products();
        $product->setProductId($post_id);
        $products['meta'][0] = Post::find($post_id)->meta()->pluck('meta_value', 'meta_key');
        $product->processProduct($products['meta'][0]);
        return parent::view('Site.Product.viewCart', compact('products'));
    }

    /**
     * To Display Product Gallery alone
     * @param $post_id
     * @return \CartRabbit\Controllers\display
     */
    public function viewProductGallery($post_id)
    {
        $images = Product::find($post_id['postId'])->getProductGallery();
        return parent::view('Site.Product.viewGallery', compact('post_id', 'images'));
    }


    /**
     * To Remove Special Price
     * @param Http $http
     */
    public function removeSpecialPrice(Http $http)
    {
        $id = $http->has('price_id') ? $http->get('price_id') : null;
        $admin = new Admin();
        $admin->removeSpecialPriceByID($id);
    }

    /**
     * To Get Special Price of a Product
     *
     * @param Http $http Instance for Access Post Datas
     * @return price
     */
    public function getSpecialPrice(Http $http)
    {
        $id = $http->get('id', 0);
        $qty = $http->get('txt_product_qty', 1);

        $pricing = "{}";

        if ($id) {
            $product = Product::init($id);
            $pricing = $product->formattedPrice($qty);
        }
        return json_encode($pricing);
    }

    public function addSpecialPrice(Http $http)
    {
        if (!$http->has('cartRabbit')) return false;
        $data['data'] = $http->all();

        Helper\Util::convertSerialToArray($data);

        $post_id = Helper\Util::extractDataFromHTTP('post');

        $specialPrice = $data['data'];

        (new Products())->saveSpecialPrices($specialPrice['cartRabbit']);
        return parent::redirect($_SERVER['HTTP_REFERER'], true);
    }

    public function getSpecialPriceListByID(Http $http)
    {
        $product_id = $http->has('product_id') ? $http->get('product_id') : null;
        $specialPrice = array();
        if (!is_null($product_id)) {
            $specialPrice = Price::where('post_id', $product_id)->get();
//                \CartRabbit\Models\Product::getSpecialPriceListByID($product_id);
        }
        return parent::view('Admin.Products.Variations.special_price', compact('specialPrice', 'product_id'));
    }

    //TODO: Improve this Process
    public function productPanel()
    {
        return '';
    }

    /**
     * 103 Change Product Type
     */

    public function resetProductType(Http $http)
    {
        $product_id = Helper\Util::extractDataFromHTTP('post');
        $product_type = $http->has('product_type') ? $http->get('product_type') : null;

        ProductBase::productReset($product_id, $product_type);
        return 'true';
    }
}