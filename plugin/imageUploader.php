<?php

/**
 * Class imageUploader
 * For Managing multiple image uploads
 */
class imageUploader
{
    /**
     * Output the metabox.
     *
     * @param WP_Post $post
     */
    public static function output($post)
    {
        ?>
        <div class="CartRabbit">
        <div id="product_images_container">
            <ul class="product_images">
                <?php
                if (metadata_exists('post', $post->ID, '_product_image_gallery')) {
                    echo $post->ID;
                    $product_image_gallery = get_post_meta($post->ID, '_product_image_gallery', true);
                } else {
                    // Backwards compat
                    $attachment_ids = get_posts('post_parent=' . $post->ID . '&numberposts=-1&post_type=attachment&orderby=menu_order&order=ASC&post_mime_type=image&fields=ids&meta_key=_cartrabbit_exclude_image&meta_value=0');
                    $attachment_ids = array_diff($attachment_ids, array(get_post_thumbnail_id()));
                    $product_image_gallery = implode(',', $attachment_ids);
                }

                $attachments = array_filter(explode(',', $product_image_gallery));

                $update_meta = false;

                if (!empty($attachments)) {
                    foreach ($attachments as $attachment_id) {
                        $attachment = wp_get_attachment_image($attachment_id, 'thumbnail');

                        // if attachment is empty skip
                        if (empty($attachment)) {
                            $update_meta = true;

                            continue;
                        }

                        echo '<li class="image" data-attachment_id="' . esc_attr($attachment_id) . '">
								' . $attachment . '
								<ul class="actions">
									<li class="product-images"><a href="#" class="delete tips" data-tip="' . esc_attr__('Delete image', 'cartrabbit') . '">' . __('Delete', 'cartrabbit') . '</a></li>
								</ul>
							</li>';

                        // rebuild ids to be saved
                        $updated_gallery_ids[] = $attachment_id;
                    }

                    // need to update product meta to set new gallery ids
                    if ($update_meta and $updated_gallery_ids) {
                        update_post_meta($post->ID, '_product_image_gallery', implode(',', $updated_gallery_ids));
                    }
                }
                ?>
            </ul>

            <input type="hidden" id="product_image_gallery" name="product_image_gallery"
                   value="<?php echo esc_attr($product_image_gallery); ?>"/>

        </div>
        <p class="add_product_images hide-if-no-js">
            <a href="#" data-choose="<?php esc_attr_e('Add Images to Product Gallery', 'cartrabbit'); ?>"
               data-update="<?php esc_attr_e('Add to gallery', 'cartrabbit'); ?>"
               data-delete="<?php esc_attr_e('Delete image', 'cartrabbit'); ?>"
               data-text="<?php esc_attr_e('Delete', 'cartrabbit'); ?>"><?php _e('Add product gallery images', 'cartrabbit'); ?></a>
        </p>
        </div>
        <?php
    }

    /**
     * Save meta box data.
     *
     * @param int $post_id
     * @param WP_Post $post
     */
    public static function save($post_id, $post)
    {
        $attachment_ids = isset($_POST['product_image_gallery']) ? array_filter(explode(',', wc_clean($_POST['product_image_gallery']))) : array();

        update_post_meta($post_id, '_product_image_gallery', implode(',', $attachment_ids));
    }
}