<?php
/**
 * The Template for displaying all single products
 *
 * This template can be overridden by copying it to yourtheme/cartrabbit/cartrabbit-thank-page-template.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see        http://docs.woothemes.com/document/template-structure/
 * @author        WooThemes
 * @package    WooCommerce/Templates
 * @version     1.6.4
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

get_header(); ?>

<section id="primary" class="content-area">
    <div id="content" class="site-content" role="main">


        <?php

        do_action('cartrabbit_thank');

        ?>
    </div>
</section>

<?php get_footer(); ?>
