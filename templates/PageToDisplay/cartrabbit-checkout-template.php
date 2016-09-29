<?php
/**
 * The Template for displaying all single posts
 *
 * @package WordPress
 * @subpackage Twenty_Fourteen
 * @since Twenty Fourteen 1.0
 */

get_header(); ?>

    <div id="primary" class="content-area">
        <div id="content" class="site-content" role="main">
            <?php
            //To Display
            echo do_shortcode('[CartRabbitCheckout]');
            ?>
        </div><!-- #content -->
    </div><!-- #primary -->

<?php
get_sidebar('content');
get_sidebar();
get_footer();
