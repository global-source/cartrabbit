<?php namespace CartRabbit;

    /** @var \Herbert\Framework\Router $router */

//this is a test root. Delete it before packaging
$router->get([
    'as' => 'Test',
    'uri' => '/test',
    'uses' => __NAMESPACE__ . '\Controllers\Test\TestController@test'
]);

$router->get([
    'as' => 'Shop',
    'uri' => '/shop/{category}/{slug}',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\AdminController@shop'
]);

/** END TEST FUNCTIONS */

/** To Add Cart item to Customer's Cart table */

$router->post([
    'as' => 'Products',
    'uri' => '/products/addToCart',
    'uses' => __NAMESPACE__ . '\Controllers\Cart\CartController@addCart'
]);

/** To Update Cart item to Customer's Cart table */

$router->post([
    'as' => 'Products',
    'uri' => '/products/updateCart',
    'uses' => __NAMESPACE__ . '\Controllers\Cart\CartController@updateCart'
]);

/** To Remove Cart item from Customer's Cart table */

$router->post([
    'as' => 'Products',
    'uri' => '/products/RemoveItem',
    'uses' => __NAMESPACE__ . '\Controllers\Cart\CartController@removeCart'
]);

/** To get Cart summery */

$router->post([
    'as' => 'Cart Summery',
    'uri' => '/cart/getCartSummery',
    'uses' => __NAMESPACE__ . '\Controllers\Cart\CartController@getCartSummery'
]);

///** To Create Order with the Customer's Cart table */
//
//$router->post([
//    'as'   => 'Checkout',
//    'uri'  => '/checkout',
//    'uses' => __NAMESPACE__.'\Controllers\Order\OrderController@createOrder'
//]);

/** To get states by country code in JSON Format */

$router->post([
    'as' => 'CountryCode',
    'uri' => '/countrycode',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\AdminController@getStates'
]);

/** To Create or Update Cart Configurations */

$router->post([
    'as' => 'Cart Config General',
    'uri' => '/cartConfig/general',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\SettingsController@saveCartGeneralConfig'
]);

/** To Create or Update Store Configurations */

$router->post([
    'as' => 'Store Config General',
    'uri' => '/storeConfig/general',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\SettingsController@saveStoreGeneralConfig'
]);

/** To Create or Update Product's General Configurations */

$router->post([
    'as' => 'Product Config General',
    'uri' => '/productConfig/general',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\SettingsController@saveProductGeneralConfig'
]);

/** To Create or Update Secondary Currencies */

$router->post([
    'as' => 'Cart Secondary Currency',
    'uri' => '/config/secCurrency',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\SettingsController@saveCartSecondaryCurrency'
]);


/** To Remove Secondary Currencies */

$router->post([
    'as' => 'Remove Secondary Currency',
    'uri' => '/config/removeSecondaryCurrency',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\SettingsController@removeSecondaryCurrency'
]);

/** To Create or Update Secondary Currencies */

$router->post([
    'as' => 'Cart Secondary Currency',
    'uri' => '/cartConfig/cart',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\SettingsController@saveCartConfiguration'
]);

/** To Create or Update Tax Configurations */

$router->post([
    'as' => 'Cart Tax Configuration',
    'uri' => '/cartConfig/Tax',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\SettingsController@saveCartTaxConfig'
]);

/** To Create Tax Classes */

$router->post([
    'as' => 'Add Tax Classes',
    'uri' => '/cartConfig/TaxClass',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\SettingsController@saveCartTaxClass'
]);

/** To Remove Tax Classes */

$router->post([
    'as' => 'Remove Tax Classes',
    'uri' => '/cartConfig/RemoveTaxClass',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\SettingsController@removeCartTaxClass'
]);

/** To Load Tax Amounts */

$router->post([
    'as' => 'Config Tax Amounts',
    'uri' => '/cartConfig/getTaxAmounts',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\SettingsController@getTaxAmountConfiguration'
]);

/** To Create or Update Store Inventory Configuartions */

$router->post([
    'as' => 'Store Inventory Configuration',
    'uri' => '/cartConfig/Inventory',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\SettingsController@saveInventoryConfigurations'
]);

/** To Remove Secondary Currencies */

$router->post([
    'as' => 'Remove Secondary Currencies',
    'uri' => '/cartConfig/removeSecondaryCurrency',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\SettingsController@removeSecondaryCurrency'
]);

/** To Set Page to Display Products List */

$router->post([
    'as' => 'Set Page to Display Products',
    'uri' => '/cartConfig/pageToDisplay',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\SettingsController@setPageToDisplayProducts'
]);

/** To Set Products List */

$router->post([
    'as' => 'Display Products',
    'uri' => '/productsList',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\SettingsController@getProductList'
]);

/** To Add Product Brand */

$router->post([
    'as' => 'Add Brand',
    'uri' => '/product/addBrand',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\SettingsController@addBrandTaxonomy'
]);

/** To Remove Product Brand */

$router->post([
    'as' => 'Remove Brand',
    'uri' => '/product/removeBrand',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\SettingsController@removeBrandTaxonomy'
]);

/** To Remove Special Price */

$router->post([
    'as' => 'Remove Special Price',
    'uri' => '/product/removeSpecialPrice',
    'uses' => __NAMESPACE__ . '\Controllers\Products\ProductsController@removeSpecialPrice'
]);

/** To Get Special Price Of a Product By ID */

$router->post([
    'as' => 'Get Special Price',
    'uri' => '/product/getSpecialPrice',
    'uses' => __NAMESPACE__ . '\Controllers\Products\ProductsController@getSpecialPrice'
]);

/** To Save Special Price Of a Product*/

$router->post([
    'as' => 'Save Special Price',
    'uri' => '/product/addSpecialPrice',
    'uses' => __NAMESPACE__ . '\Controllers\Products\ProductsController@addSpecialPrice'
]);

/** To Get Special Price Of a Product By ID */

$router->post([
    'as' => 'Get Special Price',
    'uri' => '/product/getSpecialPriceList',
    'uses' => __NAMESPACE__ . '\Controllers\Products\ProductsController@getSpecialPriceListByID'
]);

/** To Check User Name/ E-Mail for CheckOut Page */

$router->post([
    'as' => 'Check Account',
    'uri' => '/checkout/checkAccount',
    'uses' => __NAMESPACE__ . '\Controllers\CheckOut\CheckoutController@checkAccountIsExist'
]);


/** Place Order */

$router->post([
    'as' => 'Place Order',
    'uri' => '/checkout/place_order',
    'uses' => __NAMESPACE__ . '\Controllers\CheckOut\CheckoutController@placeOrder'
]);

/** To SignIn User's Account */

$router->post([
    'as' => 'Sign In Account',
    'uri' => '/checkout/signin',
    'uses' => __NAMESPACE__ . '\Controllers\CheckOut\CheckoutController@userSignIn'
]);

/** To SignIn User's as Guest */

$router->post([
    'as' => 'Sign as Guest Account',
    'uri' => '/checkout/signInGuest',
    'uses' => __NAMESPACE__ . '\Controllers\CheckOut\CheckoutController@guestSignIn'
]);

/** To SignOut User's Account */

$router->get([
    'as' => 'Sign Out Account',
    'uri' => '/checkout/signout',
    'uses' => __NAMESPACE__ . '\Controllers\CheckOut\CheckoutController@userSignOut'
]);

/** To SignUp User's Account */

$router->post([
    'as' => 'Sign Up Account',
    'uri' => '/checkout/signup',
    'uses' => __NAMESPACE__ . '\Controllers\CheckOut\CheckoutController@userSignUp'
]);

/** To Get Subdivisions based on Country Code */

$router->post([
    'as' => 'Get Subdivisions',
    'uri' => '/checkout/getSubdivisions',
    'uses' => __NAMESPACE__ . '\Controllers\CheckOut\CheckoutController@getSubdivisions'
]);

/** To Save User's Address for Checkout */

$router->post([
    'as' => 'Save User Address',
    'uri' => '/checkout/saveAddress',
    'uses' => __NAMESPACE__ . '\Controllers\CheckOut\CheckoutController@saveUserAddress'
]);

/** To Set Customer's Billing Address */

$router->post([
    'as' => 'Set Billing Address',
    'uri' => '/checkout/setCheckoutAddress',
    'uses' => __NAMESPACE__ . '\Controllers\CheckOut\CheckoutController@setAddress'
]);

/** To Remove Customer's Billing Address */

$router->post([
    'as' => 'Remove Billing Address',
    'uri' => '/checkout/removeBillingAddress',
    'uses' => __NAMESPACE__ . '\Controllers\CheckOut\CheckoutController@removeBillingAddress'
]);

/** To Set Customer's Delivery Address */

$router->post([
    'as' => 'Set Delivery Address',
    'uri' => '/checkout/setDeliveryAddress',
    'uses' => __NAMESPACE__ . '\Controllers\CheckOut\CheckoutController@setDeliveryAddress'
]);

/** To Remove Customer's Delivery Address */

$router->post([
    'as' => 'Remove Delivery Address',
    'uri' => '/checkout/removeDeliveryAddress',
    'uses' => __NAMESPACE__ . '\Controllers\CheckOut\CheckoutController@removeDeliveryAddress'
]);

/** To Edit Tax Profile */

$router->post([
    'as' => 'Edit Tax Profile',
    'uri' => '/taxConfig/editTaxProfile',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\SettingsController@editTaxProfile'
]);

/** To Edit Tax Amount */

$router->post([
    'as' => 'Edit Tax Amount',
    'uri' => '/taxConfig/editTaxAmount',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\SettingsController@saveTaxAmount'
]);

/** To Remove Tax Amount */

$router->post([
    'as' => 'Remove Tax Amount',
    'uri' => '/taxConfig/removeTaxAmount',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\SettingsController@removeTaxAmount'
]);

/** To Add Tax Rate */

$router->post([
    'as' => 'Add Tax Rate',
    'uri' => '/taxConfig/addTaxRates',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\SettingsController@saveTaxRate'
]);

/** To Edit Tax Rate */

$router->post([
    'as' => 'Edit Tax Rate',
    'uri' => '/taxConfig/editTaxRates',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\SettingsController@editTaxRate'
]);
/** To Save Product Display Configutation */

$router->post([
    'as' => 'Save Product Display',
    'uri' => '/cartConfig/productDisplay',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\SettingsController@saveProductDisplayConfiguration'
]);

/** To Remove Tax Rate */

$router->post([
    'as' => 'Remove Tax Rate',
    'uri' => '/taxConfig/removeTaxRates',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\SettingsController@removeTaxRate'
]);

/** To Add Zone */

$router->post([
    'as' => 'Add Zone',
    'uri' => '/taxConfig/addZone',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\SettingsController@addZone'
]);

/** To Edit Zone */

$router->post([
    'as' => 'Add Zone',
    'uri' => '/taxConfig/editZone',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\SettingsController@editZone'
]);

/** To Remove Zone */

$router->post([
    'as' => 'Remove Zone',
    'uri' => '/taxConfig/removeZone',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\SettingsController@removeZone'
]);

/** Load Term Members */

$router->post([
    'as' => 'Load Term Members',
    'uri' => '/product/termMeta',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\AdminController@extractAttributes'
]);

/** Save Term Members For Product*/

$router->post([
    'as' => 'Save Product`s Term Members',
    'uri' => '/product/savetermMeta',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\AdminController@saveProductAttributes'
]);

/** Save Variation Products */

$router->post([
    'as' => 'Save Variation Products',
    'uri' => '/product/saveVariationProducts',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\AdminController@saveVariationProducts'
]);

/** Remove Product's term Option*/

$router->post([
    'as' => 'Remove Term From Product`s Members',
    'uri' => '/product/removeTermOption',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\AdminController@removeProductAttrOption'
]);

///** Generate Product's Attributes Combinations */
//
//$router->post([
//    'as' => 'Generate product attribute combinations',
//    'uri' => '/product/generateCombinations',
//    'uses' => __NAMESPACE__ . '\Controllers\Admin\AdminController@getVariantCombinations'
//]);

/** Generate Product's Attributes View */

$router->post([
    'as' => 'Generate product attribute combinations View',
    'uri' => '/product/generateVariationList',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\AdminController@generateVariationList'
]);

/** Validate Product Variant Combinations  */

$router->post([
    'as' => 'Validate Variant',
    'uri' => '/product/validateVariationList',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\AdminController@validateVariationList'
]);

/** Add New Product Variant Combinations  */

$router->post([
    'as' => 'New Validate Variant',
    'uri' => '/product/newVariation',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\AdminController@addVariation'
]);

/** Remove Product Variant Combinations  */

$router->post([
    'as' => 'Remove Validate Variant',
    'uri' => '/product/removeVariation',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\AdminController@removeVariation'
]);


/** ****************************MANAGE SHIPPING [plugin]******************************** */


/** To Manage Shipping Configuration */

$router->post([
    'as' => 'Shipping Config',
    'uri' => '/config/shippingConfig',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\SettingsController@manageShippingConfig'
]);

/** To Manage Shipping */

$router->post([
    'as' => 'Shipping',
    'uri' => '/config/shipping',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\SettingsController@manageShipping'
]);

/** To Remove Shipping */

$router->post([
    'as' => 'Remove Shipping',
    'uri' => '/config/removeShipping',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\SettingsController@removeShipping'
]);

/** To Set Shipping Method to Calculate */

$router->post([
    'as' => 'set Shipping',
    'uri' => '/config/setShippingMethod',
    'uses' => __NAMESPACE__ . '\Controllers\CheckOut\CheckoutController@setShippingMethod'
]);

/** ****************************PAYMENT******************************** */

/** Init Pre Payment Session */

$router->post([
    'as' => 'Init Pre Payment Session',
    'uri' => '/checkout/initPrePayment',
    'uses' => __NAMESPACE__ . '\Controllers\CheckOut\CheckoutController@initPrePayment'
]);

/** To Set Payment Method to Calculate */

$router->post([
    'as' => 'set Payment Method',
    'uri' => '/config/setPaymentMethod',
    'uses' => __NAMESPACE__ . '\Controllers\CheckOut\CheckoutController@setPaymentMethod'
]);

/** To Set Payment Method to Calculate */

$router->post([
    'as' => 'set Payment Config',
    'uri' => '/config/setPaymentConfig',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\SettingsController@setPaymentConfig'
]);

/** To Set Payment Plugin's Config */

$router->post([
    'as' => 'set Payment Plugin Config',
    'uri' => '/config/setPaymentPluginConfig',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\SettingsController@setPaymentPluginConfig'
]);

/** For Pre Confirm Payment */

$router->post([
    'as' => 'pre confirm payment',
    'uri' => '/checkout/',
    'uses' => __NAMESPACE__ . '\Controllers\CheckOut\CheckoutController@init_CheckOut'
]);

/** To Confirm Payment */
$router->get([
    'as' => 'confirm payment',
    'uri' => '/confirmPayment',
    'uses' => __NAMESPACE__ . '\Controllers\CheckOut\CheckoutController@ConfirmPayment'
]);
$router->post([
    'as' => 'confirm payment',
    'uri' => '/confirmPayment',
    'uses' => __NAMESPACE__ . '\Controllers\CheckOut\CheckoutController@ConfirmPayment'
]);

/** ****************************ORDER******************************** */

/** To Store Order Configurations */

$router->post([
    'as' => 'To Save Order Config',
    'uri' => '/config/config',
    'uses' => __NAMESPACE__ . '\Controllers\Order\OrderController@saveOrderConfig'
]);
/** To Get Order Summery */

$router->post([
    'as' => 'Get Order Summery',
    'uri' => '/config/getOrderSummery',
    'uses' => __NAMESPACE__ . '\Controllers\CheckOut\CheckoutController@getOrderSummery'
]);

/** To update Order Status */

$router->post([
    'as' => 'Update Order Status',
    'uri' => '/order/updateOrderStatus',
    'uses' => __NAMESPACE__ . '\Controllers\Order\OrderController@updateOrderStatus'
]);

/** To Load Payment Options */

$router->post([
    'as' => 'Get Payment Form',
    'uri' => '/checkout/loadPayment',
    'uses' => __NAMESPACE__ . '\Controllers\CheckOut\CheckoutController@loadCheckoutData'
]);

/** To Load Shipping Options */

$router->post([
    'as' => 'Get Billing Form',
    'uri' => '/checkout/loadAddressInfo',
    'uses' => __NAMESPACE__ . '\Controllers\CheckOut\CheckoutController@loadAddressInfo'
]);

/** To Load Shipping Options */

$router->post([
    'as' => 'Get Shipping Form',
    'uri' => '/checkout/loadShipping',
    'uses' => __NAMESPACE__ . '\Controllers\CheckOut\CheckoutController@loadShippingForm'
]);

/** To Load Order Summery */

$router->post([
    'as' => 'Get Order Summery',
    'uri' => '/checkout/loadOrderSummery',
    'uses' => __NAMESPACE__ . '\Controllers\CheckOut\CheckoutController@getOrderSummery'
]);

/** To Place the Order */

$router->post([
    'as' => 'Create Order',
    'uri' => '/order/createOrder',
    'uses' => __NAMESPACE__ . '\Controllers\Order\OrderController@createOrder'
]);

/** To Place the Order */

$router->post([
    'as' => 'get Order',
    'uri' => '/order/getOrder',
    'uses' => __NAMESPACE__ . '\Controllers\Order\OrderController@getOrder'
]);

/** To View the Order */

$router->post([
    'as' => 'Show Order',
    'uri' => '/order/showOrder',
    'uses' => __NAMESPACE__ . '\Controllers\Order\OrderController@showOrder'
]);

/** Order Pagination */

$router->post([
    'as' => 'Paginate Order List',
    'uri' => '/order/paginateOrderList',
    'uses' => __NAMESPACE__ . '\Controllers\Order\OrderController@getOrdersPagination'
]);

/** ******************************** My Account *********************************** */

/** Get My Order */

$router->post([
    'as' => 'Get Single Order',
    'uri' => '/order/getMyOrder',
    'uses' => __NAMESPACE__ . '\Controllers\Account\AccountController@getMyOrder'
]);

/** ******************************** Change Product Type  *********************************** */

/**
 * Change product type
 */

$router->post([
    'as' => 'Change Product Type',
    'uri' => '/product/changeType',
    'uses' => __NAMESPACE__ . '\Controllers\Products\ProductsController@resetProductType'
]);

/** ******************************** Utility *********************************** */

/**
 * Change product type
 */

$router->post([
    'as' => 'Email Validation',
    'uri' => '/util/validateEmail',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\AdminController@validateEmail'
]);

/** ******************************* DASHBOARD ******************************************** */

/**
 * Change product type
 */

$router->post([
    'as' => 'Dashboard Notes',
    'uri' => '/dashboard/saveNotes',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\DashboardController@saveNotes'
]);

/**
 * Download Geo IP via CURL
 */

$router->post([
    'as' => 'Download Geo IP',
    'uri' => '/dashboard/downloadGeoIP',
    'uses' => __NAMESPACE__ . '\Controllers\Admin\DashboardController@downloadGeoIP'
]);