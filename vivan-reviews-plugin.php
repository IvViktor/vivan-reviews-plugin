<?php

/**
 
 * @since             1.0.0
 * @package           vivan_reviews_plugin
 *
 * @wordpress-plugin
 * Plugin Name:       Vivan reviews plugin
 * Description:       Simple plugin for leaving visitors reviews on your site.
 * Version:           1.0.0
 * Author:            Viktor Ivanchenko
 * Author URI:        viittyok1992@gmail.com
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * The code that runs during plugin activation.
 */
function activate_vivan_reviews_plugin() {
	 register_vivan_reviews_plugin_custom_post_type();
	 flush_rewrite_rules();
}

function register_vivan_reviews_plugin_custom_post_type() {
	register_post_type('vivan_visitor_review',
                       array(
                           'label'       => "Отзывы" ,
                           'description' => "Отзывы оставленные посетителями сайта",
                           'public'      => true,
                       )
    );
}
add_action('init','register_vivan_reviews_plugin_custom_post_type');
/**
 * The code that runs during plugin deactivation.
 */
function deactivate_vivan_reviews_plugin() {
	unregister_vivan_reviews_plugin_custom_post_type();
	flush_rewrite_rules();
}

function unregister_vivan_reviews_plugin_custom_post_type() {
    unregister_post_type('vivan_visitor_review');
}

register_activation_hook( __FILE__, 'activate_vivan_reviews_plugin' );
register_deactivation_hook( __FILE__, 'deactivate_vivan_reviews_plugin' );

function vivan_reviews_add_review($post_data){
	
}
