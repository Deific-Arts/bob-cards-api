<?php
/**
 * @package WordPress
 * @subpackage Bob
 * @since 1.0
 */


// includes
include_once('inc/post-types/business.php');
include_once('inc/taxonomies/states.php');


// disable wp-embed
function disable_wp_embed(){
    wp_deregister_script( 'wp-embed' );
}
add_action( 'wp_footer', 'disable_wp_embed' );


// disable admin bar
add_filter('show_admin_bar', '__return_false');


// enqueue styles and scripts
function enqueue_styles() {
  wp_enqueue_style('admin-css', get_theme_file_uri('/admin.css'));
}
add_action('wp_head', 'enqueue_styles');


// allows CORS
function add_cors_http_header(){
  header("Access-Control-Allow-Origin: *");
}
add_action('init','add_cors_http_header');

// not having this causes a redirect loop on prod for some reason
// remove_filter('template_redirect', 'redirect_canonical');
