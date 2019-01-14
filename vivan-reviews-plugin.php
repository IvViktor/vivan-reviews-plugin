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
	if (!empty($post_data['_vivan-reviews-visitor-review']) && $post_data['_vivan-reviews-visitor-review'] == '_new-review'){
		if (isset($post_data['vivan_reviews_visitor_name']) && !empty($post_data['vivan_reviews_visitor_name'])){
			$visitor_name = $post_data['vivan_reviews_visitor_name'];
		} else return;
		if (isset($post_data['vivan_reviews_visitor_email']) && !empty($post_data['vivan_reviews_visitor_email'])){
			if (is_email($post_data['vivan_reviews_visitor_email'])) $visitor_email = $post_data['vivan_reviews_visitor_email'];
			else return;
		} else return;
		if (isset($post_data['vivan_reviews_visitor_review']) && !empty($post_data['vivan_reviews_visitor_review'])){
			$visitor_review = $post_data['vivan_reviews_visitor_review'];
		} else return;
		if (isset($post_data['g-recaptcha-response']) && !empty($post_data['g-recaptcha-response'])){
			$grecaptcha_data = array(
				'body' => array(
					'secret' => '6LfJI4kUAAAAAFgZh3RrP64-W8mV1Dpr6CNhrk5J',
					'response' => $post_data['g-recaptcha-response']
				)
			);
			$grecaptcha_response = wp_remote_retrieve_body(wp_remote_post('https://www.google.com/recaptcha/api/siteverify', $grecaptcha_data));
			$grecaptcha_response_data = json_decode($grecaptcha_response, true);
			if ($grecaptcha_response_data['success'] == false) return;
		} else return;
		$review_data = array(
			'post_title' => __("Review by ").$visitor_name ,
			'post_content' => $visitor_review,
			'post_type' => 'vivan_visitor_review',
			'post_status' => 'publish',
			'meta_input' => array(
				'visitor_name' => $visitor_name,
				'visitor_email' => $visitor_email
			)
		);
		wp_insert_post($review_data);
	}
}
