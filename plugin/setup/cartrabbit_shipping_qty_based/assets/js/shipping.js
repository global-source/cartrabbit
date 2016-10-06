/**
 * Created by flycart on 21/7/16.
 */

jQuery.noConflict();
(function ($) {
    $(document).on('click', '.btn_add_shipping_rate', function () {
            console.log('CLICKED');
        var table_body = $('.shipping_table');
        var count = $('.shipping_table tr').length;
        var row = '<tr>' +
            '<td>' + count + '</td>' +
            '<td><input type="text" class="form-control" name="cartrabbit[shipping][' + count + '][minQty]"> </td> ' +
            '<td><input type="text" class="form-control" name="cartrabbit[shipping][' + count + '][maxQty]"> </td> ' +
            '<td><input type="text" class="form-control" name="cartrabbit[shipping][' + count + '][rate]"> </td> ' +
            '<td><input type="text" class="form-control" name="cartrabbit[shipping][' + count + '][extra]"> </td> ' +
            '<td><i class="glyphicon glyphicon-remove text-danger btn_remove_shipping_rate"></td>' +
            '<input type="hidden" name="cartrabbit[shipping][' + count + '][id]" value="' + count + '">' +
            '</tr>';
        table_body.append(row);
    });

    $(document).on('click', '.btn_remove_shipping_rate', function () {
        var id = $(this).attr('id');
        var isOK = confirm('Are You Sure to Delete ?');
        if (isOK) {
            $.ajax({
                url: '/config/removeShipping',
                type: 'POST',
                data: {id: id},
                success: function () {

                }
            });
            $(this).closest('tr').remove();
        }
    });
})(jQuery);