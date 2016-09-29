/**
 * This Plugin is used for validating the variant products
 *
 */

var existing_option;

jQuery.noConflict();
(function ($) {
    $(document).on('focus', '.terms', function () {
        existing_option = this.value;
    });

    $(document).on('keydown', '.terms', function (e) {

        var keyCode = e.keyCode || e.which;

        if (keyCode == 9) {
            e.preventDefault();
        }
    });

    //$(document).on('change', '.terms', function () {
    //    var current = $(this);
    //    var select = $('.terms').serializeArray();
    //    var site_addr = $('#site_addr').val();
    //    var combination = $('#variant_combinations').val();
    //    $.ajax({
    //        url: site_addr + '/product/validateVariationList',
    //        type: 'POST',
    //        data: {list: select, variants: combination},
    //        success: function (res) {
    //            if (res['status'] == 'CONFLICT') {
    //                current.val(existing_option);
    //            }
    //        }
    //    });
    //});

})(jQuery);