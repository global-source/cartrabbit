<?php

/**
 * @wordpress-plugin
 * Plugin Name:       CartRabbit
 * Plugin URI:        http://flycart.org/
 * Description:       A Simple and Secure MVC based eCommerce shopping cart plugin.
 * Version:           0.0.1
 * Author:            Shankar Thiyagaraajan
 * License:           MIT
 */

if (!defined('ABSPATH')) {
    die('Access denied.');
}

function verify_geoIP_update()
{
    if (!file_exists(\CartRabbit\Helper::get('site_path') . '/resources/assets/mmdb/GeoLite2-City.mmdb')) {
        if ($_GET['page'] == 'dashboard') {
            return print '<div id="message" class="update-nag geo_ip_download_curl">Your <strong>GeoIP</strong> database is missing, <a href="#">download now</a></div>';
        }
    }
}

/**
 * Base Getherbert Classes
 */
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/vendor/getherbert/framework/bootstrap/autoload.php';

//require_once __DIR__ . '/app/Helper/EventManager.php';
require_once __DIR__ . '/app/Controllers/Admin/AdminController.php';
require_once __DIR__ . '/plugin/imageUploader.php';
require_once __DIR__ . '/plugin/functions.php';

use CartRabbit\Models\Products;

$taxonomy = \CartRabbit\Helper\Util::extractDataFromHTTP('taxonomy', 'post');

$post_type = \CartRabbit\Helper\Util::extractDataFromHTTP('post_type', 'post');

/**
 * To Update or Restore the Persistent Cart.
 * Initial Level Trigger.
 */
function init_Cart()
{
    \Flycartinc\Cart\Cart::initCart();
}

/** Trigger on End of the plugin Loading. */
function initStore()
{
    \CartRabbit\Helper\Store::init();
}

/** This function loads the user's address from DB and load to Session. */
function updateStore($user_login)
{
    $user_obj = get_user_by('login', $user_login);

    \CartRabbit\Models\Customer::updateUserAddress($user_obj->ID);

    /**
     * Function "saveAddressFromSession()" will be used, when the user come from guest account.
     * So, the address of guest account will be moved to user's table
     */

    \CartRabbit\Models\Customer::onUserLogin($user_obj->ID);
}

function trigger_new_order_mail($status)
{
    \CartRabbit\Helper\OrderMail::newOrderMail($status);
}

function trigger_order_complete_mail()
{
    \CartRabbit\Helper\OrderMail::orderCompletedMail();
}

function userOnLogout()
{
    \CartRabbit\Models\Customer::onUserLogout();
}


/**
 * Register meta box(es).
 */
function wpmeta_register_meta_boxes()
{
    $post_type = \CartRabbit\Helper\Util::extractDataFromHTTP('post_type', 'post');

    /** For Remove MetaBox */
    remove_meta_box('tagsdiv-Shipping_class', 'cartrabbit_product', 'side');
    remove_meta_box('tagsdiv-product_attributes', 'cartrabbit_product', 'side');
    remove_meta_box('tagsdiv-product_brands', 'cartrabbit_product', 'side');
    remove_meta_box('pageparentdiv', 'cartrabbit_product', 'side');

    /** For CartRabbit_Product post_type */
    add_meta_box('meta_property', __('Product Properties', 'CartRabbit_Products'), 'new_product_properties', 'cartrabbit_product', 'normal', 'high');
    add_meta_box('postimagediv', __('Product Image'), 'post_thumbnail_meta_box', 'cartrabbit_product', 'side', 'high');
    add_meta_box('desc_testing', 'Product Description', 'meta_short_descr', 'cartrabbit_product', 'normal', 'high');
    add_meta_box('galleryMetabox', 'Product Gallery', 'imageUploader::output', 'cartrabbit_product', 'side', 'high');

    /** For CartRabbit_Order post_type */
    add_meta_box('order_description', 'Order Description', 'meta_order_info', 'cartRabbit_order', 'normal', 'high');
    add_meta_box('orde_items', 'Order Items', 'meta_order_items', 'cartRabbit_order', 'normal', 'high');


}


/**
 * General Order Info Metabox
 */
function meta_order_info()
{
    echo do_shortcode("[CartRabbitProductOrderInfo]");
}

/**
 * General Ordered Item's Metabox
 */
function meta_order_items()
{
    echo do_shortcode("[CartRabbitProductOrderItems]");
}

/**
 * Fill Metabox with Shortcodes
 */
function new_product_properties()
{
    echo do_shortcode("[CartRabbitProductNewMetabox]");
}


/**
 * To Create Meta Description with Wordpress Editor.
 */
function meta_short_descr($post)
{
    $settings = array(
        'textarea_name' => 'excerpt',
        'quicktags' => array('buttons' => 'em,strong,link'),
        'tinymce' => array(
            'theme_advanced_buttons1' => 'bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator',
            'theme_advanced_buttons2' => '',
        ),
        'editor_css' => '<style>#wp-excerpt-editor-container .wp-editor-area{height:175px; width:100%;}</style>'
    );
    wp_editor(htmlspecialchars_decode($post->post_excerpt), 'excerpt', apply_filters('short_description_editor_settings', $settings));
}

/**
 * Register Taxonomy for Product Tag
 */

function create_product_tag()
{
    $cat_labels = array(
        'name' => _x('Product Tags', 'taxonomy general name'),
        'singular_name' => _x('Tag', 'taxonomy singular name'),
        'search_items' => __('Search Product Tag'),
        'all_items' => __('All Product Tags'),
        'parent_item' => __('Parent Tag'),
        'parent_item_colon' => __('Parent Tags:'),
        'edit_item' => __('Edit Product Tag'),
        'update_item' => __('Update Product Tag'),
        'add_new_item' => __('Add New Product Tag'),
        'new_item_name' => __('New Product Tag Name'),
        'menu_name' => __('Tag'),
        'choose_from_most_used' => __('Choose from the most used Product tags'),
        'separate_items_with_commas' => __('Separate Product Tags with commas'),
    );
    $cat_args = array(
        'labels' => $cat_labels,
        'has_archive' => true,
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => array(
            'slug' => 'cartrabbit_attributes',
            'with_front' => true,
            'hierarchical' => true
        )
    );
    register_taxonomy('product_tags', array('cartrabbit_product'), $cat_args);

    $cat_labels = array(
        'name' => _x('Product Attributes', 'taxonomy general name'),
        'singular_name' => _x('Attributes', 'taxonomy singular name'),
        'search_items' => __('Search Product Attributes'),
        'all_items' => __('All Product Attributes'),
        'parent_item' => __('Parent Attributes'),
        'parent_item_colon' => __('Parent Attributes:'),
        'edit_item' => __('Edit Product Attributes'),
        'update_item' => __('Update Product Attributes'),
        'add_new_item' => __('Add New Product Attributes'),
        'new_item_name' => __('New Product Attributes Name'),
        'menu_name' => __('Attributes'),
        'choose_from_most_used' => __('Choose from the most used Product attributes'),
        'separate_items_with_commas' => __('Separate Product Attributes with commas'),
    );
    $cat_args = array(
        'labels' => $cat_labels,
        'has_archive' => true,
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => array(
            'slug' => 'cartrabbit_attributes',
            'with_front' => true,
            'hierarchical' => true
        )
    );
    register_taxonomy('product_attributes', array('cartrabbit_product'), $cat_args);


    $cat_labels = array(
        'name' => _x('Shipping Classes', 'taxonomy general name'),
        'singular_name' => _x('Shipping Class', 'taxonomy singular name'),
        'search_items' => __('Search Shipping Class'),
        'all_items' => __('All Shipping Classes'),
        'parent_item' => __('Parent Shipping Class'),
        'parent_item_colon' => __('Shipping Class:'),
        'edit_item' => __('Edit Shipping Class'),
        'update_item' => __('Update Shipping Class'),
        'add_new_item' => __('Add New Shipping Class'),
        'new_item_name' => __('New Shipping Class Name'),
        'menu_name' => __('Shipping Class'),
        'choose_from_most_used' => __('Choose from the most used Shipping Classes'),
        'separate_items_with_commas' => __('Separate Shipping Classes with commas'),
    );
    $cat_args = array(
        'labels' => $cat_labels,
        'has_archive' => true,
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => array(
            'slug' => 'cartrabbit_Shipping_class',
            'with_front' => true,
            'hierarchical' => true
        )
    );
    register_taxonomy('Shipping_class', array('cartrabbit_product'), $cat_args);


    $cat_labels = array(
        'name' => _x('Product Brands', 'taxonomy general name'),
        'singular_name' => _x('Brand', 'taxonomy singular name'),
        'search_items' => __('Search Product Brands'),
        'all_items' => __('All Product Brands'),
        'parent_item' => __('Parent Brands'),
        'parent_item_colon' => __('Parent Brands:'),
        'edit_item' => __('Edit Product Brand'),
        'update_item' => __('Update Product Brand'),
        'add_new_item' => __('Add New Product Brand'),
        'new_item_name' => __('New Product Brand Name'),
        'menu_name' => __('Brands'),
        'choose_from_most_used' => __('Choose from the most used Product Brands'),
        'separate_items_with_commas' => __('Separate Product Brand with commas'),
    );
    $cat_args = array(
        'labels' => $cat_labels,
        'has_archive' => true,
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => array(
            'slug' => 'cartrabbit_brands',
            'with_front' => true,
            'hierarchical' => true
        )
    );
    register_taxonomy('product_brands', array('cartrabbit_product'), $cat_args);
}

/**
 * Loop based Taxonomy Registration
 *
 * @param $name
 */
function custom_taxonomy_register($name)
{
    $cat_labels = array(
        'name' => _x('Product ' . $name, 'taxonomy general name')
    );
    $cat_args = array(
        'labels' => $cat_labels,
        'has_archive' => true,
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => array(
            'slug' => 'cartrabbit_attributes',
            'with_front' => true,
            'hierarchical' => true
        )
    );
    register_taxonomy('pr_' . $name, array('cartrabbit_product'), $cat_args);
}


/**
 * Create Generic Categories  for "product" Post type.
 */
function create_product_category()
{
    register_taxonomy('genre', 'cartrabbit_product', array(
        // Hierarchical taxonomy (like categories)
        'hierarchical' => true,
        // This array of options controls the labels displayed in the WordPress Admin UI
        'labels' => array(
            'name' => _x('Product Category', 'taxonomy general name'),
            'singular_name' => _x('Product Category', 'taxonomy singular name'),
            'search_items' => __('Search Product Categories'),
            'all_items' => __('All Product Categories'),
            'parent_item' => __('Parent Product Category'),
            'parent_item_colon' => __('Parent Product Category:'),
            'edit_item' => __('Edit Product Category'),
            'update_item' => __('Update Product Category'),
            'add_new_item' => __('Add New Product Category'),
            'new_item_name' => __('New Product Category Name'),
            'menu_name' => __('Product Categories'),
        ),

        // Control the slugs used for this taxonomy
        'rewrite' => array(
            'slug' => 'genre', // This controls the base slug that will display before each term
            'with_front' => false, // Don't display the category base before "/locations/"
            'hierarchical' => true // This will allow URL's like "/locations/boston/cambridge/"
        )
    ));
}

/**
 * To Create Taxonomy as "Brand"
 */
function create_brand_taxonomy()
{
    $args = array(
        'hierarchical' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'genre'),
    );

    register_taxonomy('Brands', array('brands'), $args);


    /** For Initiate Product Attributes, to Register the Stored Attributes */


    $options = json_decode(get_option('cartrabbit_attributes'), true);
//    dd($options);
    if (empty($options) OR is_null($options)) $options = array();
    foreach ($options as $term) {
        $name = str_replace('pro_', '', $term['slug']);
        $name = $name . ' Attributes';
        registerTaxonomyDirect($name, $term);
    }
    $options = json_decode(get_option('cartrabbit_shipping_class'), true);
    if (empty($options) OR is_null($options)) $options = array();
    foreach ($options as $term) {
        $name = str_replace('sclass_', '', $term['slug']);
        $name = $name . ' Classes';
        registerTaxonomyDirect($name, $term);
    }
}

/**
 * This function is used for registering the taxonomy of stored contents. *
 *
 * @param string $name Name of the Taxonomy
 * @param array $term Term set of Taxonomy Info
 */
function registerTaxonomyDirect($name, $term)
{

    $cat_labels = array(
        'name' => _x($name, 'taxonomy general name'),
        'singular_name' => _x($name, 'taxonomy singular name'),
        'search_items' => __('Search ' . $name),
        'all_items' => __('All ' . $name),
        'parent_item' => __('Parent ' . $name),
        'parent_item_colon' => __('Parent ' . $name . ' :'),
        'edit_item' => __('Edit ' . $name),
        'update_item' => __('Update ' . $name),
        'add_new_item' => __('Add New ' . $name),
        'new_item_name' => __('New ' . $name . ' Name'),
        'menu_name' => __('Attributes'),
        'choose_from_most_used' => __('Choose from the most used ' . $name),
        'separate_items_with_commas' => __('Separate ' . $name . ' with commas'),
    );
    $args = array(
        'labels' => $cat_labels,
        'hierarchical' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'genre'),
    );
    if (isset($term['slug'])) {
        register_taxonomy($term['slug'], array('brands'), $args);
    }
}

/**
 * To Save Taxonomy to "wp_Option" table
 *
 * @param integer $term_id Term ID to save Taxonomy
 * @return bool Status
 */
function addAttributes($term_id)
{
    $post_type = \CartRabbit\Helper\Util::extractDataFromHTTP('post_type', 'post');
    $taxonomy = \CartRabbit\Helper\Util::extractDataFromHTTP('taxonomy', 'post');

    if ($post_type != 'cartrabbit_product') return false;

    /** Creating Product Attributes Taxonomy */
    if ($taxonomy == 'product_attributes') {
        saveTaxonomy($term_id, array('name' => 'cartrabbit_attributes', 'slug' => 'pro_'), $_POST);
        /** Creating Shipping Class Taxonomy */
    } elseif (str_contains($taxonomy, 'pro_')) {
        saveTaxonomy($term_id, array('name' => 'cartrabbit_attributes', 'slug' => 'pro_'), $_POST);
    } elseif ($taxonomy == 'Shipping_class') {
        saveTaxonomy($term_id, array('name' => 'cartrabbit_shipping_class', 'slug' => 'sclass_'));
    }
}

/**
 *
 * For Creating Taxonomy, Update options of the taxonomy
 *
 * @param integer $term_id
 * @param array $set
 * @param null $meta
 */
function saveTaxonomy($term_id, $set, $meta = null)
{
    $terms = get_option($set['name']);

    if (!isset($terms) or empty($terms)) $terms = array();

    if ($terms) {
        $options = json_decode($terms, true);
    } else {
        $options = array();
    }
    if (empty($options) OR is_null($options)) $options = array();

    if (!is_null($meta) and $set['name'] = 'cartrabbit_attributes') {

        /** For Storing Product Attributes */
        $list_type = $_POST['list_type'];
        $color_list = $_POST['color_pallet'];
        if (!empty($list_type)) {
            add_term_meta($term_id, 'list_type', $list_type);
        } elseif (!empty($color_list)) {
            add_term_meta($term_id, 'color_pallets', $color_list);
        }
    }
    /** Still, Shipping Class taxonomy have no "Meta" */
    //

    $slug = $_POST['slug'];
    if (empty($slug)) $slug = str_replace(' ', '_', strtolower($_POST['tag-name']));

    $item = array(
        'term_id' => $term_id,
        'tag_name' => $_POST['tag-name'],
        'slug' => $set['slug'] . $slug
    );
    array_push($options, $item);

    update_option($set['name'], json_encode($options));
}

/**
 * @param $term_id
 * @return bool
 */
function editAttributes($term_id)
{
    if (!is_admin()) return false;

    $post_type = \CartRabbit\Helper\Util::extractDataFromHTTP('post_type', 'post');
    $taxonomy = \CartRabbit\Helper\Util::extractDataFromHTTP('taxonomy', 'post');

    if ($post_type != 'cartrabbit_product') return false;
    if ($taxonomy == 'post') return false;
    if ($taxonomy == 'Shipping_class' or str_contains($taxonomy, 'sclass_')) {
        $index = 'sclass_';
        $name = 'cartrabbit_shipping_class';
    } else {
        $index = 'pro_';
        $name = 'cartrabbit_attributes';
    }

    $terms = get_option($name);
    $options = json_decode($terms, true);

    /** It will remove the array index, if having the value as "Null" */
    \CartRabbit\Helper\Util::eliminateArrayIf($options, 'tag_name', null);

    if (empty($options) OR is_null($options)) $options = array();
    $list_type = $_POST['list_type'];
    $color_list = $_POST['color_pallet'];
    if (!empty($list_type) AND $taxonomy == 'product_attributes') {
        /** For Update Product Attribute's */
        update_term_meta($term_id, 'list_type', $list_type);
    } elseif (!empty($color_list)) {
        /** For Update Product Attribute's Option */
        update_term_meta($term_id, 'color_pallets', $color_list);
    } elseif ($taxonomy == 'Shipping_class') {

        /** Still, No more Operation in Shipping Class */
        //
    }

    $item = array(
        'tag_name' => $_POST['tag-name'],
        'slug' => $index . $_POST['slug']
    );
    array_push($options, $item);
    update_option($name, json_encode($options));
}


/**
 * @param $term_id
 */
function create_attributes($term_id)
{
    add_term_meta($term_id, 'color_code', $_POST['color_pallet']);
}

/**
 * @param $term_id
 */
function edited_attributes($term_id)
{
    update_term_meta($term_id, 'color_code', $_POST['color_pallet']);
}

/**
 * @param $taxonomy
 */
function additional_attributes_fields($taxonomy)
{
    if ($taxonomy == 'product_attributes') {
        echo '<lable>List Type :</lable>';
        echo '<select name="list_type">
             <option value="select">Select</option>
             <option value="radio">Radio</option>
             <option value="colorlist">Colorlist</option>
             <option value="text">Text</option>
          </select><br><br>';
    } elseif (str_contains($taxonomy, 'pro_')) {
        if (checkTermMeta('list_type', 'colorlist', $taxonomy)) {
            echo '<label>Select Color : </label><input type="color" name="color_pallet"><br><br>';
        }
    }
}

/**
 * @param $tag
 */
function additional_edit_attributes_fields($tag)
{
    $taxonomy = $_GET['taxonomy'];
    if ($taxonomy == 'product_attributes') {
        $list_type = get_term_meta($tag->term_id, 'list_type')[0];
        $types = array(
            'select' => 'Select',
            'radio' => 'Radio',
            'colorlist' => 'Colorlist',
            'text' => 'Text'
        );
        ?>
        <lable>List Type :</lable>
        <select name="list_type">
            <?php foreach ($types as $key => $value) {
                $res = (($key == $list_type) ? 'selected=selected' : '');
                ?>
                <option <?php echo $res . '>' . $value ?> </option>
            <?php } ?>
        </select>
        <?php
    } elseif (str_contains($taxonomy, 'pro_')) {
        if (checkTermMeta('list_type', 'colorlist', $taxonomy)) {
            $color = get_term_meta($tag->term_id, 'color_pallets')[0];

            /** If Color is not set, "#000000" [Black] is set as default */
            if (is_null($color)) $color = '#000000';

            echo '<tr class="form-field">';
            echo '<lable>Select Color : <br></lable>';
            echo '<input type="color" value=' . $color . ' name="color_pallet">';
            echo '</tr>';
        }
    }
}

/**
 * @param $key
 * @param $val
 * @param $taxonomy
 * @return bool
 */
function checkTermMeta($key, $val, $taxonomy)
{
    $taxonomy = str_replace('pro_', '', $taxonomy);
    $term_id = \Herbert\Framework\Models\Term::where('slug', $taxonomy)
        ->get()
        ->pluck('term_id')
        ->first();
    return (get_term_meta($term_id, $key)[0] == $val);
}

/**
 * @param $taxonomy
 */
function add_attributes_fields($taxonomy)
{
    if (checkTermMeta('list_type', 'colorlist', $taxonomy)) {
        echo '<label>Select Color : </label><input type="color" name="color_pallet"><br><br>';
    }
}


/**
 * @param $columns
 * @return array
 */
function manage_my_category_columns($columns)
{
    $taxonomy = \CartRabbit\Helper\Util::extractDataFromHTTP('taxonomy');

    if ($taxonomy == 'product_attributes') {
        return array_merge($columns,
            array(
                'type' => __('Type'),
                'manage' => __('Manage')
            ));
    } elseif (checkTermMeta('list_type', 'colorlist', $taxonomy)) {
        return array_merge($columns,
            array(
                'color' => __('Color Code')
            ));
    } elseif ($taxonomy == 'Shipping_class') {
        return array_merge($columns,
            array(
                'manage' => __('Manage')
            ));
    } else {
        return $columns;
    }
}


/**
 * @param $out
 * @param $column_name
 * @param $term_id
 * @return string
 */
function manage_product_attribute_data($out, $column_name, $term_id)
{
    $term = get_term_by('id', $term_id, 'category');
    $taxonomy = \CartRabbit\Helper\Util::extractDataFromHTTP('taxonomy');

    /** For Manage Columns in "Product Attributes" Taxonomy */
    if ($taxonomy == 'product_attributes') {
        $term_type = get_term_meta($term_id, 'list_type')[0];
        switch ($column_name) {
            case 'manage':
                if ($term_type !== 'text') {
                    $out = '<a href="' . get_site_url() . '/wp-admin/edit-tags.php?taxonomy=pro_' . $term->slug . '&post_type=cartrabbit_product"><span class="dashicons dashicons-networking"></span></a>';
                } else {
                    $out = '';
                }
                break;
            case 'type':
                $out = '<b>' . $term_type . '</b>';
                break;
            default:
                break;
        }
        /** For Manage Columns in "Product Attribute's Options" Taxonomy */
    } elseif (str_contains($taxonomy, 'pro_')) {
        $color = get_term_meta($term_id, 'color_pallets')[0];
        $out = '<span class="dashicons dashicons-format-image" style="color:' . $color . '"></span> [' . $color . ']';

        /** For Manage Columns in "Shopping Class" Taxonomy */
    } elseif ($taxonomy == 'Shipping_class') {
        $out = '<a href="' . get_site_url() . '/wp-admin/edit-tags.php?taxonomy=sclass_' . $term->slug . '&post_type=cartrabbit_product"><span class="dashicons dashicons-cart"></span></a>';
    }
    return $out;
}

/**
 * To Append Form "enctype"
 */
function post_edit_form_tag()
{
    echo ' enctype="multipart/form-data"';
}

/** To Save meta datas to corresponding post.
 * @param $post_id Integer Post Id.
 * @return bool
 */
function metabox_save_value($post_id)
{
    global $post;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    //To Set Current user permission to edit this post.
    if (!current_user_can('edit_post')) return;

    if ($post->post_type == 'cartrabbit_order') {
        $order = new \CartRabbit\Models\Orders();
        $status = (!is_null($_POST['status'])) ? $_POST['status'] : null;
        if ($status !== null) {
//            $order->setOrderId($post_id);
//            $order->saveOrderStatus($status);
        }
    }
    $product = new Products();

    if (!isset($post_id)) return false;

    /** Add Sepecial Price in Separate Table */
    $specialPrice = !isset($_POST['cartRabbit']['specialPrice']) ?: $_POST['cartRabbit']['specialPrice'];

    $product->checkCategory($_POST['tax_input']['genre'], $_POST['ID']);

    $product->saveSpecialPrices($specialPrice, $post_id);

    /** Assign Brand to Product */
    $brand = $_POST['brand'];
    wp_set_object_terms($post_id, $brand, 'Brands');

    if ($_POST['product_type'] == 'simpleProduct') {
        (new \CartRabbit\Models\ProductSimple())->onBeforeSave($_POST);
    } elseif ($_POST['product_type'] == 'variableProduct') {
        (new \CartRabbit\Models\ProductVariable())->onBeforeSave($_POST);
    }
}

/** Display custom column */
function display_posts_stickiness($column, $post_id)
{
    if ($column == 'Ship To') {
        echo get_post_meta($post_id, 'address')[0];
    } elseif ($column == 'Action') {
        echo '<button class="dashicons-before dashicons-format-status" title="Comment"></button>';
        echo '<button class="dashicons-before dashicons-visibility" ti></button>';
        echo '<button class="dashicons-before dashicons-welcome-write-blog"></button>';
    }
}

/** Add custom column to post list */
function add_sticky_column($columns)
{
    return array_merge($columns,
        array('Ship To' => __('Ship To', 'your_text_domain'),
            'Purchased' => __('Purchased'),
            'Action' => __('Actions')

        ));
}

/**
 * To Initialize the tasks in after login
 */
function init_after_login()
{
    //
}


/**
 * Filter "the_conter" hook and apply additional contents
 * @param $content
 * @return  Original Contents
 */

function filter_single_product_display($content)
{
    global $post;
    if ($post->post_type == 'cartrabbit_product') {
        echo do_shortcode("[CartRabbitSingleProduct post_id=" . $post->ID . "]");
        return;
    }
    return $content;
}


/**
 * To Display Product Title alone
 * @param $content
 */
function filter_single_product_title($content)
{
    global $post;
    if ($post->post_type == 'cartrabbit_product') {
        echo do_shortcode("[CartRabbitSingleProductTitle post_id=" . $post->ID . "]");
        return;
    }
    return $content;
}

/**
 * To Display Product Image alone
 * @param $content
 */
function filter_single_product_image($content)
{
    global $post;
    if ($post->post_type == 'cartrabbit_product') {
        echo do_shortcode("[CartRabbitSingleProductImage post_id=" . $post->ID . "]");
        return;
    }
    return $content;
}

/**
 * To Display Product Image alone
 * @param $content
 */
function filter_single_product_price($content)
{
    global $post;
    if ($post->post_type == 'cartrabbit_product') {
        echo do_shortcode("[CartRabbitSingleProductPrice post_id=" . $post->ID . "]");
        return;
    }
    return $content;
}

/**
 * To Display Product Image alone
 * @param $content
 */
function filter_single_product_description($content)
{
    global $post;
    if ($post->post_type == 'cartrabbit_product') {
        echo do_shortcode("[CartRabbitSingleProductDescription post_id=" . $post->ID . "]");
        return;
    }
    return $content;
}

/**
 * To Display Product Image alone
 * @param $content
 */
function filter_single_product_cart($content)
{
    global $post;
    $url = plugin_dir_url(__FILE__);
    if ($post->post_type == 'cartrabbit_product') {
        echo do_shortcode("[CartRabbitSingleProductCart post_id=" . $post->ID . "]");
        wp_enqueue_script('CartRabbit jQuery', $url . '/resources/assets/js/script.js', false);
        return;
    }
    return $content;
}

/**
 * @param $content
 */
function filter_single_product_gallery($content)
{
    global $post;
    if ($post->post_type == 'cartrabbit_product') {
        echo do_shortcode("[CartRabbitSingleProductGallery post_id=" . $post->ID . "]");
        return;
    }
    return $content;
}


/**
 * To Overwrite Single post template
 * @param $single_template instance
 * @return Single Template
 */
function cartrabbit_single_product_template($single_template)
{
    global $post;
    if ($post->post_type == 'cartrabbit_product') {
        $single_template = plugin_dir_path(__FILE__) . 'templates/cartrabbit-single-product-template.php';
    }
    return $single_template;
}

/**
 * @param $content
 * @return mixed
 */
function testContent($content)
{
    return $content;
}

/**
 * To Overwrite Page Template
 * @param $page_template
 * @return Page Template
 */
function cartrabbit_product_list_template($page_template)
{
    if (is_page()) {
        $page_id = get_the_ID();
        $html = $page_template;
        $post_to_display = pageToDisplayProducts();
        foreach ($post_to_display as $key => $post) {
            if ($page_id == $post) {
                if ($key == 'page_to_list_product') {
                    $page_template = plugin_dir_path(__FILE__) . 'templates/PageToDisplay/cartrabbit-products-list-template.php';
                    $html = $page_template;
                } elseif ($key == 'page_to_cart_product') {
                    $page_template = plugin_dir_path(__FILE__) . 'templates/PageToDisplay/cartrabbit-cart-product-template.php';
                    $html = $page_template;
                } elseif ($key == 'page_to_account') {
                    $page_template = plugin_dir_path(__FILE__) . 'templates/PageToDisplay/cartrabbit-myaccount-template.php';
                    $html = $page_template;
                } elseif ($key == 'page_to_checkout') {
                    $page_template = plugin_dir_path(__FILE__) . 'templates/PageToDisplay/cartrabbit-checkout-template.php';
                    $html = $page_template;
                } elseif ($key == 'page_to_thank') {
                    $page_template = plugin_dir_path(__FILE__) . 'templates/PageToDisplay/cartrabbit-thank-page-template.php';
                    $html = $page_template;
                }
            }
        }
    }
    return $html;
}

/**
 * To Get Assigned Page for Display Products
 * @return Post Id
 */
function pageToDisplayProducts()
{
    global $post;
    $arg = array(
        'post_type' => 'cartrabbit_config'
    );
    $result = new WP_Query($arg);
    if ($result->have_posts()) {
        $result->the_post();
        $page['page_to_list_product'] = get_post_meta($post->ID, 'page_to_list_product', true);
        $page['page_to_cart_product'] = get_post_meta($post->ID, 'page_to_cart_product', true);
        $page['page_to_account'] = get_post_meta($post->ID, 'page_to_account', true);
        $page['page_to_checkout'] = get_post_meta($post->ID, 'page_to_checkout', true);
        $page['page_to_thank'] = get_post_meta($post->ID, 'page_to_thank', true);
    }
    return $page;
}

/**
 * To Overwrite Archive Template
 * @param $page_template
 * @return Page Template
 */
function cartrabbit_product_archive_template($page_template)
{
    $page_template = plugin_dir_path(__FILE__) . 'templates/cartrabbit-products-archive-template.php';
    return $page_template;
}

/**
 * To Display Single Product Details
 */
function cartrabbit_single_product_display()
{
    echo do_shortcode('[CartRabbitSingleProduct]');
}

/**
 * To Trigger Product List Shortcode
 */
function filter_product_list()
{
    echo do_shortcode('[CartRabbitProducts]');
}

/**
 * Triggered After Plugin Activated
 */
function InitalConfig()
{

    /** Basic Setup
     *
     * 1. Set the Initial Option Value :
     */
    add_option('cartrabbit_attributes', '');


    // This Redirection happened, Only When Plugin Activated Newly !
    // If Plugin already Installed, then this function not Triggered
    if ((new \CartRabbit\Models\Setup())->isNewBoot() != false) {
        $URL = admin_url('admin.php?page=dashboard');
        wp_redirect($URL);
        exit;
    }
}


/**
 * To Init ReWrite Rules to Make User Friendly URL's
 */

function cartrabbit_shop_rewrite()
{
    $option = (array)json_decode(get_option('cartrabbit_permalink'));
    $tag = (isset($option['product_tag_base']) ? $option['product_tag_base'] : null);
    $category = (isset($option['product_category_base']) ? $option['product_category_base'] : null);
    $permalink = (isset($option['permalink']) ? $option['permalink'] : null);
    $type = (isset($option['type']) ? $option['type'] : null);

    $default = '';

    if ($type == 'custom') {
        $default = $permalink;
    }

    //add to our plugin init function
    global $wp_rewrite;

    //to flush existing rewrite rules to avoid collations
    flush_rewrite_rules();

    //Single Product View
    if ($type == 'custom') {
        $modified = str_replace('%product-category%', '%category%', $default);
        $cartrabbit_structure = $modified . '/product/%product%';
    } else {
        $modified = str_replace('/%product-category%/', '', $default);
        $cartrabbit_structure = $modified . '/product/%product%';
    }
    $wp_rewrite->add_rewrite_tag("%product%", '([^/]+)', "cartrabbit_product=");
    $wp_rewrite->add_permastruct('cartrabbit_single_product', $cartrabbit_structure, false);

    //Tag Based View
    $cartrabbit_structure = $default . '/' . (($tag) ? $tag : 'tag') . '/%tag%';
    $wp_rewrite->add_rewrite_tag("%tag%", '([^/]+)', "product_tags=");
    $wp_rewrite->add_permastruct('cartrabbit_tags', $cartrabbit_structure, false);

    //Category Based View
    $cartrabbit_structure = $default . '/' . (($category) ? $category : 'cat') . '/%category%';
    $wp_rewrite->add_rewrite_tag("%category%", '([^/]+)', "genre=");
    $wp_rewrite->add_permastruct('cartrabbit_category', $cartrabbit_structure, false);

    //Category Based Single Product View
    $cartrabbit_structure = $default . '/' . (($category) ? $category : 'cat') . '/%category%/%page%';
    $wp_rewrite->add_rewrite_tag("%category%", '([^/]+)', "genre=");
    $wp_rewrite->add_rewrite_tag("%page%", '([^/]+)', "cartrabbit_product=");
    $wp_rewrite->add_permastruct('cartrabbit_category_product', $cartrabbit_structure, false);
}

/**
 * To Init Permalink Settings
 */
function settings_init()
{
    // Add a section to the permalinks page
    add_settings_section('cartrabbit-permalink', 'CartRabbit Permalink', 'cartrabbit_permalink', 'permalink');
}

/**
 * To Save Permalink Options data's
 */
function saveAdminOptions()
{
    if (isset($_POST['_wp_http_referer'])) {
        //To Filter the Permalink Option Page
        if ($_POST['_wp_http_referer'] === '/wp-admin/options-permalink.php' or $_POST['_wp_http_referer'] === '/wp-admin/options-permalink.php?settings-updated=true') {
            (new \CartRabbit\Models\Settings())->savePermalinkSettings($_POST);
        }
    }
}

/**
 * To Display Permalink Form on Permalink Setting's Page
 */
function cartrabbit_permalink()
{
    echo do_shortcode('[CartRabbitPermalink]');
}


/*
 *  103 Removing Attributes
 */
function removing_attributes($deleted_term)
{
    $term = get_term($deleted_term);
    \Corcel\TermTaxonomy::where('taxonomy', 'pro_' . $term->slug)->delete();
//    Taxonomy::where('')
}

/** ****************************************** */
/**
 * TEST FUNCTIONS
 */


/** ****************************************** */
// 101 Holded
/** To Add Additional Fields to the Product Attribute Page */

/**
 * To Enqueue the Scripts/Styles to Wordpress
 */
function cartrabbit_enqueue_script()
{
    $url = plugin_dir_url(__FILE__);

    // Enqueue for Page
    if (!is_admin()) {
//        wp_enqueue_style('CartRabbit Style', $url . '/resources/assets/css/metaboxProductImages.css', true);
//        wp_enqueue_script('CartRabbit jQuery', $url . '/resources/assets/js/script.js', false);
    }
    if (is_admin()) {
        wp_enqueue_script('CartRabbit jQuery', $url . '/resources/assets/js/CartRabbit.min.js', false);
    }
}

add_action('admin_init', 'cartrabbit_enqueue_script', 10, 1);

/**
 * Hook for Filtering product content and apply additional contents by its post type
 */

/** For Filter Page Template to Inject Product List Template */

add_action('page_template', 'cartrabbit_product_list_template');

add_action('archive_template', 'cartrabbit_product_archive_template');

add_action('cartrabbit_product_list', 'filter_product_list');

/** For Filter Single Post Template */

add_filter('single_template', 'cartrabbit_single_product_template');

add_action('cartrabbit_single_product', 'filter_single_product_display');

add_action('cartrabbit_single_product_image', 'filter_single_product_image');
add_action('cartrabbit_single_product_price', 'filter_single_product_price');
add_action('cartrabbit_single_product_description', 'filter_single_product_description');
add_action('cartrabbit_single_product_cart', 'filter_single_product_cart');
add_action('cartrabbit_single_product_title', 'filter_single_product_title');
add_action('cartrabbit_single_product_gallery', 'filter_single_product_gallery');

/**
 * Hook for Adding columns to Order list View
 */
add_action('manage_cartrabbit_order_posts_custom_column', 'display_posts_stickiness', 5, 2);
add_filter('manage_cartrabbit_order_posts_columns', 'add_sticky_column');

/**
 * Init Trigger for save post meta.
 */

add_action('save_post', 'metabox_save_value');

/**
 *  Init Generic Category
 */
add_action('init', 'create_product_category');

/**
 *  Init Generic Tag
 */
add_action('init', 'create_product_tag');

/**
 *  Init Product Taxonomy
 */
add_action('init', 'create_brand_taxonomy');

/**
 *  Init Cart
 */
add_action('init', 'init_Cart');

/**
 *  Init Metabox for post new page
 */
add_action('add_meta_boxes', 'wpmeta_register_meta_boxes');

/**
 * After login Setup
 */
add_action('wp_login', 'init_after_login');

/**
 * Add Tag to New Product's Form
 */
add_action('post_edit_form_tag', 'post_edit_form_tag');

/**
 * Perform on Plugin Installation
 */

add_action('activated_plugin', 'InitalConfig');

/**
 * To Add or Overwrite Scripts and Styles
 */
//add_action('init', 'cartrabbit_enqueue_script');

/**
 * To Add Rewrite Rules for User-Friendly Access
 */
add_action('init', 'cartrabbit_shop_rewrite');

/**
 * To Add Additional Contents on Admin Pages
 */
add_action('admin_init', 'settings_init');

/**
 * To Save Admin Option's Data
 */
add_action('update_option', 'saveAdminOptions');
/**
 * To Manage Custom Columns for Product Attributes
 */
add_filter('manage_edit-' . $taxonomy . '_columns', 'manage_my_category_columns');

/**
 * To Manage Custom Column's data for Product Attributes
 */

add_action('manage_' . $taxonomy . '_custom_column', 'manage_product_attribute_data', 10, 3);

add_action('create_' . $taxonomy, 'addAttributes', 999);

add_action('edit_' . $taxonomy, 'editAttributes', 999, 3);

add_action($taxonomy . '_add_form_fields', 'additional_attributes_fields');

add_action('edit_tag_form_fields', 'additional_edit_attributes_fields', 10, 2);

/**
 * To Count Post Views [UNDER TESTING]
 */

/*
 *  103 To Unregister Variants on Attribute deletion
 */

// Unregistering Variants on Attribute Deletion
add_action('deleted_term_taxonomy', 'removing_attributes');


/*
 *  To Call WP_media upload
 */

function load_wp_media_files()
{
    wp_enqueue_media();
}

add_action('admin_enqueue_scripts', 'load_wp_media_files');

add_action('wp_loaded', 'initStore');

add_action('wp_login', 'updateStore');

add_action('wp_logout', 'userOnLogout');

add_action('send_confirm_order_mail', 'trigger_new_order_mail');
add_action('send_complete_order_mail', 'trigger_order_complete_mail');
add_action('admin_notices', 'verify_geoIP_update');