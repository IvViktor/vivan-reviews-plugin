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

//adding reCAPTHCA script loader for specifig page template
add_filter( 'template_include', 'vivan_reviews_add_recaptcha_api', 1000);
function vivan_reviews_add_recaptcha_api(){
	if is_page_template(get_template_directory_uri() . '/page-vivan-reviews.php') {
		wp_enqueue_script('recaptcha_loader', 'https://www.google.com/recaptcha/api.js?hl=ru');
	}
}
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

/*
 *function for check neccessary parameters and add new review as a post
 *use this function with renderer helper function to support informational messages in template file
 */
function vivan_reviews_add_review($post_data, $callback){
	if (!empty($post_data['_vivan-reviews-visitor-review']) && $post_data['_vivan-reviews-visitor-review'] == '_new-review'){
		if (!vivan_reviews_recatcha_is_valid($post_data)){
			$callback(true,__('reCAPTCHA challenge is not passed.'));
			return;
		}
		if (isset($post_data['vivan_reviews_visitor_name']) && !empty($post_data['vivan_reviews_visitor_name'])){
			$visitor_name = $post_data['vivan_reviews_visitor_name'];
		} else {
			$callback(true, __('Name is not specified.'));
			return;
		}
		if (isset($post_data['vivan_reviews_visitor_email']) && !empty($post_data['vivan_reviews_visitor_email'])){
			if (is_email($post_data['vivan_reviews_visitor_email'])) $visitor_email = $post_data['vivan_reviews_visitor_email'];
			else {
				$callback(true, __('Email address is not valid.'));
				return;
			}
		} else {
			$callback(true, __('Email address is not specified.'));
			return;
		}
		if (isset($post_data['vivan_reviews_visitor_review']) && !empty($post_data['vivan_reviews_visitor_review'])){
			$visitor_review = $post_data['vivan_reviews_visitor_review'];
		} else {
			$callback(true, __('Review text is not entered.'));
			return;
		}
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
		$review_post_id = wp_insert_post($review_data, true);
		if (is_wp_error($reviw_post_id){
			$callback(true, __('Internal server error was found during posting your review.'));
			return;
		}
		$callback(false, __('Your review was successfully added.'));
	}
}
/*
 *below is example of renderer helper function
function renderer_helper($is_error, $message_text){
	if ($is_error){
		$inform_message_class_name = 'error-message';
		$inform_message_text = $message_text . __('Please try again.');
	} else {
		$inform_message_class_name = 'success-message';
		$inform_message_text = $message_text;
	}
}
 */
//function for check reCAPTHCA user response with Google reCAPTCHA API
function vivan_reviews_recatcha_is_valid($post_data){
	if (isset($post_data['g-recaptcha-response']) && !empty($post_data['g-recaptcha-response'])){
		$grecaptcha_request = array(
			'body' => array(
				'secret' => '6LfJI4kUAAAAAFgZh3RrP64-W8mV1Dpr6CNhrk5J',
				'response' => $post_data['g-recaptcha-response']
			)
		);
		$grecaptcha_response = (wp_remote_post('https://www.google.com/recaptcha/api/siteverify', $grecaptcha_request));
		if (is_wp_error($grecaptcha_response)) return false;
		$grecaptcha_response_data = json_decode(wp_remote_retrieve_body($grecaptcha_response), true);
		return $grecaptcha_response_data['success'];
	} else return false;
}


