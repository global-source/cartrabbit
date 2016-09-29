<?php
/**
 * The Template for displaying all single posts
 *
 * @package WordPress
 * @subpackage Twenty_Fourteen
 * @since Twenty Fourteen 1.0
 */

get_header(); ?>
    <section id="primary" class="content-area">
        <div id="content" class="site-content" role="main">

            <?php
//            if (have_posts()) :
//                while (have_posts()) : the_post();
            echo do_shortcode('[CartRabbitCart]');
//                    add_action('the_content', do_action('cartrabbit_single_product'));
//                endwhile;
//            endif;
            ?>

        </div>
    </section>

<?php
get_footer();
