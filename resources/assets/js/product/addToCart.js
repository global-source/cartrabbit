jQuery.noConflict();
(function ($) {
    var site_addr = $('#site_address').val();
    var variants = $('#variant_combination').val();
    var parent_product = false;

    /** In Multiple Product list View, there is no variants data to display */
    if (variants) {
        var variant_attributes = $('#variant_attributes').val();
        var variant_option_list = $('#option_list').val();
        var variant_sets = $('#variant_sets').val();
        parent_product = $('#txt_product_id').val();

        variant_attributes = JSON.parse(variant_attributes);
        variant_option_list = JSON.parse(variant_option_list);
        variant_sets = JSON.parse(variant_sets);
        variants = $.parseJSON(variants);

        /** Initially Disable the Cart Button, If Product has Variants */
        $('.btn_cart_add').attr('disabled', 'disables');
    }

    var id = 0;
    var value = [];
    var triggered = true;
    var initialSelect = '';
    $('.price-display').hide();

    /**
     * For Managing variant Product with its combinations, the following function is used
     *
     * This Event Handler will handles the variant product option changes with the active attribute
     * sets, to find the match and display its relevant product details.
     *
     */

    $('.select_attribute_option').on('change', function () {
        var current = $(this);
        var active_change = $(this).val();
        var active_option = $(this).attr('name');
        active_option = active_option.replace('attr_', '');
        var availability = [];

        var listItem = {};
        var activeId = '';
        var list = [];


        $.each(variant_attributes, function (index, option) {
            /** To Refresh the List Array to avoid duplicate entry */
            list = [];
            $.each(availability, function (i, data) {
                list.push(data[option]);
                /** To Removing options from possible Attribute's List */
                if (option !== active_option) {
                    $('[name=attr_' + option).html('');
                }
            });
            /** To Remove Duplicate index */
            list = $.unique(list);
            /** To Insert array of Options with corresponding Attributes */
            listItem[option] = list;

        });

        $.each(listItem, function (i, data) {
            $.each(data, function (index, item) {
                if (i !== active_option) {
                    $('[name=attr_' + i).append('<option value="' + item + '">' + item + '</option>');
                }
            });
        });

        value = [];
        $('.select_attribute_option').each(function () {
            /** The value "none" is need to eliminate */
            if ($(this).val() == 'none') {
                //
            } else {
                value.push($(this).val());
            }


        });

        /** "activeSet" holds the current value of the options as Array */
        var activeSet = value;

        /** Loop all the available attribute sets */
        $.each(variant_sets, function (i, data) {
            if (i.indexOf('_') >= 0) {
                i = i.split('_');
                i = i[0];
            }
            /** "id" holds the current id of the attribute set's ID */
            id = i;

            data = $.map(data, function (el) {
                if (id != el) {
                    return el
                }
            });
            if ($(activeSet).not(data).length == 0 || $(data).not(activeSet).length == 0) {
                /** "activeId" holds the ID of the matched attribute set */
                activeId = id;
            }
            id = '';
        });
        //console.log('ACTIVE');
        //console.log(activeSet);
        /**
         * To Show Clear Option
         */
        $('#log').html('<spane class="text text-primary" id="clear_combinations"><a href=javascript:void(0)>Clear</a></spane>');
        $('#log').show();

        if (isCanUpdate()) {
            /** "activeId" is set, then the variant product's price is display */
            if (activeId.length != 0) {
                /** To Show "Clear" Combination Option */
                clearCombinations();

                /** To Set Active Variant ID  */
                $('#txt_variant_id').val(activeId);

                /** To Set Active Variant Combination */
                $('#active_variant_set').val(JSON.stringify(activeSet));

                /**
                 * To show Price
                 */
                $('#lbl_price_' + parent_product).html(123);

                $('.price-display').show();

                /**
                 * Show Sku, Brand, Description
                 */

                /** To show SKU on variant change */

                /** Is Sku available */
                /** Is SKU not empty */
                    //if (variants[activeId]['sku'] != '') {
                $('#product_sku').html('SKU : ' + variants[activeId]['sku']);
                //}

                /** To show Variant Description */
                console.log(variants[activeId]);
                /** Is Variant Description not empty */
                if (variants[activeId]['variantDesc'] != '') {
                    $('.desc-display').html(variants[activeId]['variantDesc']);
                }

                /** Variant Image */

                /** Is Show Product Image not empty */
                    //if (variants[activeId]['displaySetup']['showProductImage'] == 'yes') {
                $('.productImage-display').attr('src', variants[activeId]['raw_image']);
                //}

                /** Set Product Price */

                var price_out = '';
                var price_obj = variants[activeId].pricing;
                if (price_obj.is_discounted) {
                    if (price_obj.special_price < price_obj.base_price && price_obj.special_price > 0) {
                        price_out = '<span class="price-strike"><span class="text-strike"> ' + price_obj.f_base_price + '</span></span> <span>' + price_obj.f_special_price + '</span>';
                    } else {
                        price_out = price_obj.f_price;
                    }
                } else {
                    price_out = price_obj.f_price;
                }

                $('#product_price_' + parent_product).html(price_out);
                /** Set Product Stock */
                if (variants[activeId]['stock']['show'] == true) {
                    $('.stock-availability').html('Available :' + variants[activeId]['stock']['qty']);
                }
                //if (variants[activeId]['regular_price'] != '') {
                $('.productImage-display').attr('src', variants[activeId]['image_thump']);
                //}
                //else {
                //    $('.productImage-display').attr('Not available');
                //}

                /** Variant Min sale Quantity */

                var quantityBox = $('.txt_product_qty');

                /** Is Min Sale Quantity not empty */
                if (variants[activeId]['stock']['show'] == true) {
                    quantityBox.attr('min', variants[activeId]['stock']['min']);
                    quantityBox.val(variants[activeId]['stock']['min']);
                }
                else {
                    quantityBox.attr('min', 0);
                    quantityBox.val(0);
                }

                /** Variant Max sale Quantity */

                /** Is Max Sale Quantity not empty */
                if (variants[activeId]['stock']['show'] == true) {
                    quantityBox.attr('max', variants[activeId]['stock']['max']);
                }
                else {
                    //If there is no Max Sale, then it set as Actual stock of the product
                    quantityBox.attr('max', variants[activeId]['stock']['qty']);
                }

                /**
                 Show Availability Based On variants
                 */

                /** Is Stock Object Show is set to 'true'  */
                if (variants[activeId]['stock']['show']) {

                    /** Is Stock Object Quantity is not empty */
                    if (variants[activeId]['stock']['qty'] == '' || variants[activeId]['stock']['qty'] == 0) {
                        $('.txt_product_qty').attr('disabled', 'disabled');
                        $('.btn_cart_add').attr('value', 'Out Of Stock !');
                        $('.btn_cart_add').attr('disabled', 'disables');
                    } else {
                        $('.txt_product_qty').removeAttr('disabled');
                        $('.btn_cart_add').removeAttr('disabled');
                        //TODO : Set Dynamic Button Text
                        $('.btn_cart_add').val('Add To Cart');
                    }
                }
            } else {
                if (!isCanUpdate()) {
                    $('#log').html('Sorry, this Option not Available ! ' +
                        '<spane class="text text-primary" id="clear_combinations">' +
                        '<a href=javascript:void(0)>clear</a>' +
                        '</spane>');
                }
            }
        }


        /** Loop all the available attribute sets */
        $.each(variant_sets, function (i, data) {
            /** "id" holds the current id of the attribute set's ID */
            id = i;

            /**
             * To get the matched variant combinations with the last changed option
             * to overwrite the option sets with available variant combination.
             * (ex.) blue => blue, small
             *               blue, large
             *      small => grey, small
             *               red,  small
             */
            $.map(data, function (el) {
                if (active_change == el) {
                    /** "availability" holds Available Matched Variant Sets */
                    availability.push(variant_sets[id]);
                }
            });
        });

        /**
         * To get the Select tags other than the Current One
         */

        var options_keys = function (activeTitleName) {
            var list = [];

            /** Getting data from the options_list div */
            for (var k in JSON.parse($('#option_list').val())) {

                /** Is Key 'k' not active Title */
                if (k != activeTitleName) {
                    list.push(k);
                }
            }
            return list;
        };

        /**
         * To remove all the options from Select other than the Current One
         */

        var removeAllOptions = function (tagNames) {

            /** Looping all the inActive <Select> and removing the options */
            tagNames.forEach(function (title) {
                $('[name=attr_' + title + ']')
                    .find('option')
                    .remove()
                    .end()
                ;
            });
        };

        /**
         * Ordering the Valid Combinations for the Current Selected Option
         */

        var getCombinationsOrdered = function (combinationForValue, activeTitleName) {
            var list = {};

            /** Getting the Variant combinations category */
            for (i = 0; i < variant_attributes.length; i++) {
                if (variant_attributes[i] !== activeTitleName) {
                    list[variant_attributes[i]] = [];
                }
            }

            /** Grouping the Variant Combinations based on their Category */
            combinationForValue.forEach(function (target) {
                for (var k in target) {
                    if (typeof target[k] !== 'function' && k != activeTitleName) {
                        list[k].push(target[k]);
                    }
                }
            });
            return list;
        };

        /**
         * Updating the Combinations in the Select option based on the Selected Value
         */

        var updateSelectOptions = function (listOptions) {

            /** ListOptions are grouped Variant Combinations based on their Category */

            /** Looping the ListOptions and appending them to their corresponding Category */
            for (var k in listOptions) {
                var options = '';

                /** Eliminating the Duplicates from ListOptions */
                var data = $.unique(listOptions[k]);
                options = "<option value='none'>Please Choose your " + k + "</option>";
                for (var val in data) {
                    options += "<option value=" + data[val] + "> " + data[val] + " </option>";
                }

                /** Appending them based on their category */
                $('[name=attr_' + k + ']').append(options);
            }
        };

        /**
         * Setting the initial
         */

        if (triggered) {
            initialSelect = $(this).attr('title');
        }

        /**
         * Validating the change of the Select and if Valid change the Select Options
         */

        /** Checking if this is the first */
        if (initialSelect == $(this).attr('title')) {
            var activeTitleName = $(this).attr('title');
            var tags = options_keys(activeTitleName);
            removeAllOptions(tags);
            var listOptions = getCombinationsOrdered(availability, activeTitleName);
            updateSelectOptions(listOptions);
            triggered = false;
        }

        //if(active_change == 'none')

    });

    $('.txt_product_qty').change(function (e) {
        var id = $(this).attr('id');
        id = id.replace('qty_', '');

        var form = $('#cart_add_' + id).serializeArray()
        id = $('#cart_add_' + id + ' input[name=id]').val();
        if (parent_product != false) {
            id = parent_product;
        }
        var option = $('.select_attribute_option').val();

        if (option != 'none') {
            $.ajax(
                {
                    url: site_addr + "/product/getSpecialPrice",
                    type: 'POST',
                    data: form,
                    success: function (result) {
                        updatePrice(result, id);
                    },
                    error: function (request) {
                        alert(request.responseText);
                    }
                });
        }
    });

    /** Add item to cart */

    $(document).on('click', '.btn_cart_add', function () {

        var data = $(this).closest('form').serializeArray();

        var id = $(this).attr('id');
        id = id.replace('btn_add_', '');
        var qty = $('#qty_' + id).val();
        console.log(qty);
        if (qty > 0) {
            var cart_link = $('#btn_my_cart').val();
            $.ajax({
                url: site_addr + '/products/addToCart',
                type: 'POST',
                data: data,
                success: function (res) {

                    if (res.error) {
                        $('#lbl_add_cart_response_' + id).html(res.error);
                    } else {
                        $('#lbl_add_cart_response_' + id).html('Item Added Successfully ! \n Check it on <a href="' + cart_link + '">Cart</a>');
                        updateCartSummery();
                    }
                }
            });
        } else {
            $('#lbl_add_cart_response_' + id).html('This Entry is Restricted !');
        }

    });

    /**
     * To Reset the Option List
     */
    $(document).on('click', '#clear_combinations', function () {
        /** "variant_option_list" holds the list of option sets */
        triggered = true;
        initialSelect = '';
        $('.price-display').hide();
        $.each(variant_option_list, function (i, data) {
            $('[name=attr_' + i).html('');
            /** default index  */
            $('[name=attr_' + i).append('<option value="none"> Choose any ' + i + '</option>');
            $.each(data['list'], function (index, item) {
                $('[name=attr_' + i).append('<option value="' + item + '">' + item + '</option>');
            });
        });
        $('.stock-availability').html('');
        resetProductImage();
        $('#log').hide();
        $('.product_price_display').html('');
        $('#product_sku').html('');
        $('.desc-display').html('');
        $('.txt_product_qty').val(0);
        $('.info_log').html('');
        $('.btn_cart_add').attr('disabled', 'disabled');
    });

    /** To Limit the Qty Value and Avoid Enter Characters */
    $(".txt_product_qty").keypress(function (e) {
        var minVal = $(this).attr('min');
        if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
            return false;
        }
        if (this.value.length == 0 && e.which == 48) {
            return false;
        }
        // 101 Set Limit, Should not Enter More than the Given Min Qty Sale
        // CODE HERE


        $(".txt_product_qty").on('change keyup', function () {
            var minVal = parseInt($(this).attr('min'));
            var maxVal = parseInt($(this).attr('max'));
            var $this = $(this)
            var t = setInterval(
                function () {
                    if (($this.val() < minVal || $this.val() > maxVal) && $this.val().length != 0) {
                        if ($this.val() < minVal) {
                            $this.val(minVal)
                        }
                        if ($this.val() > maxVal) {
                            $this.val(maxVal)
                        }
                    }
                }, 50)
        })
    });

    function isCanUpdate() {
        var canUpdate = true;
        $('.select_attribute_option').each(function () {
            /** The value "none" is need to eliminate */
            if ($(this).val() == 'none') {
                canUpdate = false;
            }
        });
        return canUpdate;
    }

    /** For Updating the Special price of the product "On Qty Change" */
    function updatePrice(result, id) {
        result = JSON.parse(result);

        //If Special price is available, then update the special price and strice the actual price.
        if ((result.special_price > 0) && result.is_discounted == true && (result.special_price != result.base_price)) {
            html = '<span class="price-strike"><span class="text-strike">' + result.f_base_price + '</span></span><span>' + result.f_special_price + '</span>';
            $('#product_price_' + id).html(html);
            $('.price-display').show();
        }
        else {
            //If Special price is not available, then display the actual price.
            if (result.price > 0) {
                html = '<span>' + result.f_price + '</span>';
                $('#product_price_' + id).html(result.f_price);
                $('.price-display').show();
            }
        }
    }

    function updateCartSummery() {
        var out;
        var cart_link = $('#btn_my_cart').val();
        $.ajax({
            url: site_addr + '/cart/getCartSummery',
            type: 'POST',
            success: function (res) {
                out = 'You have ' + res.quantity + ' items | ' + res.total +
                    '<br> <a href="' + cart_link + '">View Cart</a>';
                $('.cart_summery').html(out);
            }
        });
    }

    function clearCombinations() {
        /**
         * To Show Clear Option
         */
        $('#log').html('<spane class="text text-primary" id="clear_combinations"><a href=javascript:void(0)>Clear</a></spane>');
        $('#log').show();
    }

    function resetProductImage() {
        //TODO Reset product Image
    }


})(jQuery);