jQuery(function (a) {

      /**
     * To Remove Image
     */
    a(".product_images").on("click", "a.delete", function () {
        a(this).closest("li.image").remove();
        var b = "";
        return a("#product_images_container").find("ul li.image").css("cursor", "default").each(function () {
            var a = jQuery(this).attr("data-attachment_id");
            b = b + a + ","
        }), h.val(b), a("#tiptip_holder").removeAttr("style"), a("#tiptip_arrow").removeAttr("style"), !1
    });
  

    /**
     * To Create Product Gallery
     */
    var g, h = a("#product_image_gallery"),
        i = a("#product_images_container").find("ul.product_images");
    jQuery(".add_product_images").on("click", "a", function (b) {
        var c = a(this);
        return b.preventDefault(), g ? void g.open() : (g = wp.media.frames.product_gallery = wp.media({
            title: c.data("choose"),
            button: {
                text: c.data("update")
            },
            states: [new wp.media.controller.Library({
                title: c.data("choose"),
                filterable: "all",
                multiple: !0
            })]
        }), g.on("select", function () {
            var a = g.state().get("selection"),
                b = h.val();
            a.map(function (a) {
                if (a = a.toJSON(), a.id) {
                    b = b ? b + "," + a.id : a.id;
                    var d = a.sizes && a.sizes.thumbnail ? a.sizes.thumbnail.url : a.url;
                    i.append('<li class="image" data-attachment_ids="' + a.id + '">' +
                        '<img src="' + d + '" />' +
                        '<ul class="actions"><li><a href="#" class="delete" title="' + c.data("delete") + '">' +
                        c.data("text") + "</a></li></ul></li>")
                }
            }), h.val(b)
        }), void g.open())
    }), i.sortable({
        items: "li.image",
        cursor: "move",
        scrollSensitivity: 40,
        forcePlaceholderSize: !0,
        forceHelperSize: !1,
        helper: "clone",
        opacity: .65,
        placeholder: "wc-metabox-sortable-placeholder",
        start: function (a, b) {
            b.item.css("background-color", "#f6f6f6")
        },
        stop: function (a, b) {
            b.item.removeAttr("style")
        },
        update: function () {
            var b = "";
            a("#product_images_container").find("ul li.image").css("cursor", "default").each(function () {
                var a = jQuery(this).attr("data-attachment_id");
                b = b + a + ","
            }), h.val(b)
        }
    })
});

