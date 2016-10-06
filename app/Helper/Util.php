<?php

namespace CartRabbit\Helper;

use CartRabbit\Models\Settings;
use CommerceGuys\Addressing\Repository\CountryRepository;
use CommerceGuys\Addressing\Repository\SubdivisionRepository;
use Illuminate\Database\Capsule\Manager as Capsule;
use CartRabbit\Helper;
use CartRabbit\Models\Products;
use Illuminate\Encryption\Encrypter;

/**
 * Class Util
 * @package CartRabbit\Helper
 */
class Util
{

    /**
     * @var
     */
    static $columns;

    /**
     * To check the given array is JSON or NOT
     *
     * @param string $string Suspicious data format variable
     * @return bool JSON or NOT
     */
    function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * To Extract ID from its Value to return ID
     *
     * @param string $item with string prifix
     * @return integer ID
     */
    public static function extractIdFromProductId($item)
    {
        foreach ($item as $key => $value) {
            if (str_contains($key, 'id_')) {
                return $value;
            }
        }
    }

    /**
     * This function return the Default Product Image
     * for Replacing Broken or Missing Images.
     *
     * @return array
     */
    public static function getDefaultConfig()
    {
        return [
            'product_image' => Helper::get('site_url') . 'resources/assets/img/default_product.gif',
            'ajax_spin' => Helper::get('site_url') . 'resources/assets/img/spin.gif'
        ];
    }

    /**
     * To Extract prefix contents from meta contents to retrieve the value
     *
     * @param $cartItems array of items
     * @return array extracted array of items
     */
    public static function extractProductID($cartItems)
    {
        if (!isset($cartItems['items'])) return array();
        $hash = 0;
        foreach ($cartItems['items'] as $key => $value) {
            if (str_contains($key, 'id_')) {
                $items[$hash]['id'] = $value;
            }
            if (str_contains($key, 'qty_')) {
                $items[$hash]['qty'] = $value;
                $hash = $hash + 1;  // Need to Workout to Optimize, now the count depends on qty
            }
        }
        return $items;
    }

    /**
     * To Compare the array of items to find any changes are happened.
     *
     * @param array $source1 Set Of Array on Before Changes
     * @param array $source2 Set Of Array on After Changes
     * @param string $search type of search
     * @return bool True No Difference | False Has Difference
     */
    public static function checkArrayDifference($source1, $source2, $search)
    {
        if (count($source1) == 0 and count($source2) == 0) return true;
        if ($search == 'all') {
            loop:
            if (count($source1) == 0) return true;
            if (count($source1) != count($source2)) return false;
            $last_bfr = (array_last($source1));
            $last_aft = (array_last($source2));
            if (!empty(array_diff($last_bfr, $last_aft))) {
                return false;
            } else {
                array_pop($source1);
                array_pop($source2);
                goto loop;
            }
        }
        return true;
    }

    /**
     * To Generate static liks with static page ID's
     *
     * @return array of generated links
     */
    public function getStaticLinks()
    {
        $link = array();
        $URL = array(
            'cartrabbit_config' =>
                ['meta' =>
                    [
                        'cart' => 'page_to_cart_product',
                        'products' => 'page_to_list_product',
                        'checkout' => 'page_to_checkout',
                        'myAccount' => 'page_to_account',
                        'thank' => 'page_to_thank'
                    ],
                    'except' => 'NoPage'
                ]
        );
        $product = new Products();
        foreach ($URL as $key => $value) {
            $post = \Post::where('post_type', $key)->get()->first();
            $post_meta = self::extractMeta($value, $post);
        }
        foreach ($post_meta as $key => $ID) {
            $product->setProductId($ID);
            $link[$key] = get_permalink($ID);
        }
        return $link;
    }

    /**
     * To Extract meta with specific validation
     *
     * @param array $meta Set Of Meta keys
     * @param array $post Instance of the Post
     * @return array Set of Extracted meta from post
     */
    public static function extractMeta($meta, $post)
    {
        $result = array();
        if (!$post) return $result;
        foreach ($meta['meta'] as $key => $value) {
            $data = $post->meta->$value;
            if ($data != $meta['except']) {
                $result[$key] = $data;
            }
        }
        return $result;
    }

    /**
     * To Perform migration to ensure the table and its properties
     * are up-to-date
     *
     */
    public static function migration()
    {
        /** Return all Defined Table Configs */
        $tables = Migration::cartrabbit_tables();

        foreach ($tables as $key => $value) {
            loop:
            /** To Verify the Table is Exist or Not */
            if (Capsule::Schema()->hasTable($key)) {
                foreach ($value as $column => $datatype) {
                    /** To Add Column if Not Exist */
                    if (!Capsule::Schema()->hasColumn($key, $column)) {

                        //Assign the Name and Datatype for temporary access
                        self::$columns['name'] = $column;
                        self::$columns['datatype'] = $datatype;

                        Capsule::Schema()->table($key, function ($table) {
                            $column = self::$columns['name'];
                            $datatype = self::$columns['datatype'];
                            $table->$datatype($column)->after('id');
                        });
                    }
                }
            } else {
                /** To Create Table if Not Exist, then Send back to the Loop for Adding Columns */
                Capsule::schema()->create($key, function ($table) {
                    /** Setup basic columns */
                    $table->increments('id');
                    $table->timestamps();
                });
                goto loop;
            }
        }
    }

    /**
     * To Validates the input with the given rules
     *
     * @param object $http of post data
     * @param array $rules set of to check with the data
     * @return array of result of validation
     */
    public static function validate($http, $rules)
    {
        $error = array();
        foreach ($rules as $key => $rule) {
            if ($rule == 'Required') {
                if (!$http->has($key)) {
                    $error[$key] = $rule;
                }
            }
        }
        return $error;
    }

    /**
     * To Encrypt the given data with Encryption package by Secret Key
     *
     * @param string $string Raw Data
     * @return string Encoded Data
     */
    public static function encrypt($string)
    {
        $encoder = new Encrypter(Helper::get('enc_key'));
        if (!$string) return array();
        return $encoder->encrypt($string);
    }

    /**
     * To Decrypt the given Crypt data by Secret Key
     *
     * @param string $coded_string Encoded Data
     * @return string Raw Data
     */
    public static function decrypt($coded_string)
    {
        $decode = new Encrypter(Helper::get('enc_key'));
        if (!$coded_string) return array();
        return $decode->decrypt($coded_string);
    }


    /**
     * NOT YET USED
     * @param $session_name
     * @param $data
     * @return array|mixed
     */
    public static function initSession($session_name, $data)
    {
        if (empty($data)) $data = array();
        if (empty($session_name)) return array();
        Session()->set($session_name, $data);
        return Session()->get($session_name);
    }

    /**
     * To Check the given data is exixt in source
     *
     * @param array $source Collection of Data
     * @param string $data needle
     * @return bool exist Or Not
     */
    public static function checkExist($source, $data)
    {
        return array_search($data, $source);
    }

    /**
     * This Function is used to perform the Set Operation to generate all
     * possible combinations with the given array
     *
     * @param array $input collection of array
     * @return array collection of Possible sets
     */
    public static function cartesian($input)
    {
        // filter out empty values
        $input = array_filter($input);

        $result = array(array());

        foreach ($input as $key => $values) {
            $append = array();

            foreach ($result as $product) {
                foreach ($values as $item) {
                    $product[$key] = $item;
                    $append[] = $product;
                }
            }

            $result = $append;
        }

        return $result;
    }

    /**
     * To Eliminate the unwanted string in Index or Value of an Array,
     * this function only operate on single dimension array.
     *
     * @param array $source Collection of array
     * @param string $type "index|value|all"
     * @param string $search string to eliminate
     * @param string $replace to replace the searched element
     */
    public static function arrayRemoveString(&$source, $type, $search, $replace = '')
    {
        if (isset($source) or !empty($source)) {
            foreach ($source as $key => $value) {
                /** To Search and Replace in Index of an Array */
                if ($type == 'index') {
                    if (str_contains($key, $search)) {
                        $source[str_replace($search, $replace, $key)] = $value;
                        unset($source[$key]);
                    }
                    /** To Search and Replace in Value of an Array */
                } elseif ($type == 'value') {
                    if (str_contains($value, $search)) {
                        $source[$key] = str_replace($search, $replace, $value);
                    }
                    /** To Search and Replace in Index & Value of an Array */
                } elseif ($type == 'all') {
                    if (str_contains($key, $search)) {
                        $source[str_replace($search, $replace, $key)] = $value;
                        unset($source[$key]);
                    }
                    if (str_contains($value, $search)) {
                        $source[$key] = str_replace($search, $replace, $value);
                    }
                }
            }
        }
    }

    /**
     * To Eliminate the array item by the given key
     *
     * @param array $data Collection of Array
     * @param string $field index on given array
     * @param string $needle content to check and eliminate the array index
     */
    public static function eliminateArrayIf(&$data, $field, $needle)
    {
        foreach ($data as $key => $value) {
            if ($value[$field] == $needle) unset($data[$key]);
        }
    }

    /**
     * To Convert the object to array
     *
     * @param object $objects
     * @return array Converted Array content
     */
    public static function objectToArray(&$objects)
    {
        if (is_array($objects) OR is_object($objects)) {
            if (is_array($objects)) {
                if (count($objects) <= 0) return array();

                /** Single Stage Object Convert */
                foreach ($objects as $key => $object) {
                    $objects[$key] = (array)$object;
                }
            }
        }
    }

    /**
     * To Convert the Array to Object
     *
     * @param array $array Array of Data
     * @return array Object Data
     */
    public static function arrayToObject(&$array)
    {
        if (is_array($array) OR is_object($array)) {
            if (is_array($array)) {
                if (count($array) <= 0) return array();

                /** Single Stage Object Convert */
                foreach ($array as $key => $value) {
                    $array[$key] = (object)$value;
                }
            }
        }
    }

    /**
     * To Extract Array sets from the collection of array
     *
     * @param array $indexes array index
     * @param array $source Collection of Array
     * @param string $primary To make the Primary Index
     * @return array Extracted Array Sets
     */
    public static function extractArray($indexes, $source, $primary = null)
    {
        $result = array();
        if (is_array($source)) {
            $hash = 0;
            foreach ($source as $key => $value) {
                foreach ($indexes as $id => $index) {
                    if (in_array($index, $value)) {
                        if (!is_null($primary)) {
                            $result[$value[$primary]][$index] = $value[$indexes[$id]];
                        } else {
                            $result[$hash][$index] = $value[$indexes[$id]];
                        }
                    }
                }
                $hash = $hash + 1;
            }
        }
        return $result;
    }

    /**
     * To extract the data from the "HTTP_REFERER"
     *
     * @param string $field Field to extract data
     * @return mixed|string extracted data
     */
    public static function extractDataFromHTTP($field, $default = null)
    {
        $result = '';
        try {
            if ($_SERVER['QUERY_STRING'] != '') {
                /** Manually Building URL Resource */
                $source = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
            } else {
                if (isset($_SERVER['HTTP_REFERER'])) {
                    $source = $_SERVER['HTTP_REFERER'];
                } else {
                    $source = null;
                }
            }

            /** If $source URL is Null, then return false */
            if (is_null($source)) return false;

            /** If $source not having '?' means, there is not more Query Strings to Parse */
            if (!str_contains($source, '?')) {
                if ($_SERVER['QUERY_STRING'] != '') {
                    $source = $source . $_SERVER['QUERY_STRING'];
                }
            }

            $parts = parse_url($source);

            if (!isset($parts['query'])) return false;

            parse_str($parts['query'], $query);
            if (isset($query[$field])) {
                $result = $query[$field];
            } else {
                return $default;
            }

            if (empty($result) or !isset($result)) {
                $result = $default;
            }
        } catch (\Exception $e) {
            //
        }
        return $result;
    }

    /**
     * @param $field
     * @param null $default
     * @return mixed|null
     */
    public static function extractDataFromLiveURL($field, $default = null)
    {
        $data = (filter_input(INPUT_GET, 'taxonomy', FILTER_SANITIZE_URL));
        if (!isset($data)) $data = $default;
        return $data;
    }

    /**
     * This Function can be used for One and Two Dimension Array,
     * Note : Can index only one element
     *
     * @param $index
     * @param $source
     */
    public static function arrayExtractExcept($index, &$source)
    {
        $data = $source;
        if (is_array($data)) {
            foreach ($data as $key => &$item) {
                if (is_array($item)) {
                    foreach ($item as $id => $value) {
                        if ($id !== $index) {
                            unset($source[$key][$id]);
                        }
                    }
                } else {
                    if ($key !== $index) {
                        unset($source[$key]);
                    }
                }
            }
        }
    }

    /**
     * @param $data
     */
    //TODO: Need more optimization to implement jQuery Serialize array Breaker
    /**
     * @param $data
     */
    public static function convertSerialToArray(&$data)
    {
        $result = array();
        foreach ($data['data'] as $key => $value) {
            if (str_contains($value['name'], '[')) {
                $index = explode('[', $value['name']);
                $i = count($index);
                loop:
                foreach ($index as $i_key => $i_value) {
                    if (isset($result[$i_value])) {
                        $result[$i_value][$i_key] = $i_value;
                    } else {
                        $result[$i_value] = next($index);
                    }
                    $i = $i - 1;
                }
                if ($i > 0) {
                    goto loop;
                }

            } else {
                $result[$value['name']] = $value['value'];
            }
        }
    }

    /**
     * To Return array of Image url from array of attachment ID's
     *
     * @param $ids
     * @return array
     */
    public static function attachmentToImageURL($ids)
    {
        $url = [];
        if (is_string($ids)) {
            if (str_contains($ids, ',')) {
                $ids = explode(',', $ids);
            }
        }
        if (is_array($ids)) {
            foreach ($ids as $id) {
                $url[] = wp_get_attachment_thumb_url($id);
            }
        }
        return $url;
    }

    /**
     * To Eliminate the Duplicate array.
     *
     * @param $set
     * @return array
     */
    public static function eliminateDuplicateArray($set)
    {
        //TODO : FIX the Issue On String Sorting
        krsort($set);

        $active = array_pop($set);
        $eliminate = array();
        loop:
        foreach ($set as $index => $item) {
            if ($active === $item and count($active) == count($item)) {
                $eliminate[] = $index;
            }
        }

        if (count($set) > 1) {
            $active = array_pop($set);
            GOTO loop;
        }
        return $eliminate;
    }

    /**
     * This Function will set the Index of root by its child.
     * [Only Used for One Root and One Child]
     *
     * @param $array
     */
    public static function redefinedArrayIndex(&$array)
    {
        foreach ($array as $index => $item) {
            foreach ($item as $key => $value) {
                $array[$key] = $item;
                unset($array[$index]);
            }
        }
    }

    /**
     * @param $email
     * @return string
     */
    public static function validateEmail($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Eliminate all un-necessary contents in string.
     * @param $string
     */
    public static function makeString(&$string)
    {
        $string = strtolower($string);
        $string = str_replace(" | ", "-", $string);
        $string = str_replace(" ", "-", $string);
        $string = preg_replace('/[^a-zA-Z0-9\']/', '-', $string);
        $string = str_replace("'", '', $string);
    }

    /**
     * @param $code
     * @return null
     */
    public static function getCountryNameByCode($code)
    {
        $list = (new CountryRepository())->getList();
        if ($code) {
            if (isset($list[$code])) {
                return $list[$code];
            }
        }
        return null;
    }

    /**
     * To Convert the State Code to State Name
     *
     * @param $country_code
     * @param $state_code
     * @return string
     */
    public function convertStateCodeToName($country_code, $state_code)
    {
        $code = '';
        if ($country_code !== '') {
            $states = new SubdivisionRepository();
            $response = $states->getlist($country_code);
            /** If State Code is Shortened (ex. IN-TN) */
            if (str_contains($state_code, '-')) {
                $code = $response[$state_code];
                /** If State Code is not Shortened (ex. Tamil Nadu) */
            } else {
                $code = $state_code;
            }
        }
        return $code;
    }

    /**
     * To Get States by Country Code
     *
     * @param $country
     * @return string
     */
    public static function getStatesByCountryCode($country)
    {
        $response = array();
        if ($country !== '') {
            $states = new SubdivisionRepository();
            $response = json_encode($states->getlist($country));
        }
        return $response;
    }

    /**
     * @param $string
     * @param bool $is_object
     * @return mixed
     */
    public function jsonDecode($string, $is_object = true)
    {
        if (is_string($string)) {
            return json_decode($string, $is_object);
        }
    }

    /**
     * To Download file via cURL.
     *
     * @param $file
     * @param $destination
     */
    public static function downloadFile($file, $destination)
    {
        /**
         * Initialize the cURL session
         */
        $ch = curl_init();
        /**
         * Set the URL of the page or file to download.
         */
        curl_setopt($ch, CURLOPT_URL, $file);
        /**
         * Create a new file
         */
        $fp = fopen($destination, 'w');
        /**
         * Ask cURL to write the contents to a file
         */
        curl_setopt($ch, CURLOPT_FILE, $fp);
        /**
         * Execute the cURL session
         */
        curl_exec($ch);
        /**
         * Close cURL session and file
         */
        curl_close($ch);
        fclose($fp);

        self::extractGzFile($destination);
    }

    /**
     * To Extract ".gz" file.
     * @param $file_name
     * @param $out
     */
    public static function extractGzFile($file_name, $out = null)
    {

        /**  Raising this value may increase performance  */
        $buffer_size = 4096; // read 4kb at a time
        $out_file_name = str_replace('.gz', '', $file_name);

        if (!is_null($out)) {
            $out_file_name = $out;
        }

        /** Open our files (in binary mode)  */

        $file = gzopen($file_name, 'rb');
        $out_file = fopen($out_file_name, 'wb');

        /** Keep repeating until the end of the input file */
        while (!gzeof($file)) {
            // Read buffer-size bytes
            // Both fwrite and gzread and binary-safe
            fwrite($out_file, gzread($file, $buffer_size));
        }

        /**  Files are done, close files */
        fclose($out_file);
        if (gzclose($file)) {
            if (file_exists($out_file_name)) {
                unlink($file_name);
            }
        }
    }

    public static function getURLof($page)
    {
        $pages = Settings::getDisplaySetup();
        switch ($page) {
            case 'product':
                $url = get_permalink($pages['list_product']);
                break;
            case 'cart':
                $url = get_permalink($pages['cart_product']);
                break;
            case 'checkout':
                $url = get_permalink($pages['checkout']);
                break;
            case 'account':
                $url = get_permalink($pages['account']);
                break;
            case 'thankYou':
                $url = get_permalink($pages['thank']);
                break;
            default:
                $url = get_permalink($pages['list_product']);
                break;
        }
        return $url;
    }

    public static function full_copy($source, $target)
    {
        try {
            if (is_dir($source)) {
                @mkdir($target);
                $d = dir($source);
                while (FALSE !== ($entry = $d->read())) {
                    if ($entry == '.' || $entry == '..') {
                        continue;
                    }
                    $Entry = $source . '/' . $entry;
                    if (is_dir($Entry)) {
                        self::full_copy($Entry, $target . '/' . $entry);
                        continue;
                    }
                    copy($Entry, $target . '/' . $entry);
                }

                $d->close();
            } else {
                copy($source, $target);
            }
        } catch (\Exception $e) {
            //
        }
        return true;
    }


    /**
     * item_sorting function.
     *
     * @access public
     * @param mixed $a
     * @param mixed $b
     * @return void
     */
    public function qty_sorting($a, $b)
    {
        if ($a['quantity'] == $b['quantity']) {
            if ($a['quantity'] == $b['quantity']) {
                return 0;
            }
            return ($a['quantity'] < $b['quantity']) ? 1 : -1;
        }
        return ($a['quantity'] < $b['quantity']) ? 1 : -1;
    }
}