jQuery.noConflict();
(function ($) {
    $(document).ready(function () {
        /** For Cart View Button */
        $('.btn_addToCart').on('click', function (a) {
            a.preventDefault();
            var id = $(this).attr('id');
            id = id.replace('btn_add_', '');
            var qty = $(this, '.product_cart').siblings(".txt_product_qty").val();
            var pid = $(this, '.product_cart').siblings(".hidden_product_id").val();
            //102 var item = $(this, '.product_cart').siblings(".hidden_product_name").val();
            var site_addr = $('[name="site_address"]').val();
            var status = $(this, '.product_cart').siblings(".lbl_status_msg");
            var type = 'new';
            $.ajax({
                url: site_addr + '/products/addToCart',
                type: 'POST',
                data: {id: pid, qty: qty, type: type},
                success: function () {
                    var report = 'Item added to cart <br><a href="' + site_addr + '/cart/">View cart</a>';
                    status.html(report);
                }
            });
        });

        /** For Getting Special Price On Qty Change */
    });
    /** ---------------------------------------------------CHECKOUT------------------------------------------------------------ */
    /** For Reloading the order summery. */
    function loadOrderSummery() {
        var site_addr = $('#site_addr').val();
        $.ajax({
            url: site_addr + '/checkout/loadOrderSummery',
            type: 'POST',
            success: function (res) {
                /** If error is hit, then redirect */
                if (res.error_url) {
                    location.reload(res.error_url);
                }
                $('#summery_content').html(res);
                $('.orderSummaryContainer').css('display', 'block');
            }
        });
    }
})(jQuery);