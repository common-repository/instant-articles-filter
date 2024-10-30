<?php
/*
 * Plugin Name: Instant Articles Filter
 * Version: 0.1
 * Plugin URI: http://www.thepennyhoarder.com/
 * Description: Filter which posts get included in the Facebook Instant Article (FIA) feed.
 * Author: The Penny Hoarder, Branndon Coelho
 * Author URI: http://www.thepennyhoarder.com/
 * Requires at least: 4.0
 * Tested up to: 4.5
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( is_admin() ) {


	############################################
	# Checkbox in Post Edit Page
	############################################
		function tph_add_custom_meta_boxes_fia_filter() {
		 
			$settings = get_option('tph_fia_filter_settings'); 

			if( !empty($settings['allowed_tag']) || !empty($settings['denied_tag']) || !empty($settings['allowed_cat']) || !empty($settings['denied_cat'])) {
				return;
			}

			if ($settings['post_type'] != NULL) {
				foreach ($settings['post_type'] as $type => $allowed) {
					// Define the custom attachment for posts
				    add_meta_box(
				        'wp_fia_filter',
				        'Facebook Instant Article Filter',
				        'wp_fia_filter',
				        $type,
				        'side'
				    );
				}
			} else {
				// Define the custom attachment for posts
			    add_meta_box(
			        'wp_fia_filter',
			        'Facebook Instant Article Filter',
			        'wp_fia_filter',
			        'post',
			        'side'
			    );
			}
		    
		     
		 
		} // end tph_add_custom_meta_boxes_fia_filter
		add_action('add_meta_boxes', 'tph_add_custom_meta_boxes_fia_filter');

		function wp_fia_filter() {
		 
		    wp_nonce_field(plugin_basename(__FILE__), 'wp_fia_filter_nonce');

			$postID = get_the_ID();
			$showOnFIA = get_post_meta( $postID, 'wp_fia_filter', true );

			$settings = get_option('tph_fia_filter_settings'); 
			if (!isset($_GET['post']) && !isset($_GET['action']) && isset($settings['checked_by_default']) && $settings['checked_by_default'] == '1') {
				$showOnFIA = '1';
			}

		    $html = '<p class="description">';
		        $html .= '<input type="checkbox" id="wp_fia_filter" name="wp_fia_filter" value="1" '.checked($showOnFIA, 1, 0).' /> Show this in Facebook Instant Articles?';
		    $html .= '</p>';
		     
		    echo $html;
		 
		} // end wp_fia_filter


		function save_custom_meta_fia_filter($id) {
		    /* --- security verification --- */
		    if(!wp_verify_nonce($_POST['wp_fia_filter_nonce'], plugin_basename(__FILE__))) {
		      return $id;
		    } // end if
		       
		    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		      return $id;
		    } // end if
		    
			if(!current_user_can('edit_page', $id)) {
			    return $id;
			} // end if
		    /* - end security verification - */

		   	if ( sanitize_text_field( $_POST['wp_fia_filter'] ) == '1') {
		   		$useCheckboxes = '1';
		   	} else {
		   		$useCheckboxes = '';
		   	}
		    
		    update_post_meta($id, 'wp_fia_filter', sanitize_text_field('1'));     
		     
		} // end save_custom_meta_fia_filter
		add_action('save_post', 'save_custom_meta_fia_filter');



	############################################
	# Admin Reporting and Menus
	############################################

	
		# MENU

			function tph_fia_filter_menu() {
				$menuTitle = 'Instant Articles Filter';
				$menuSlug = 'fia-filter';
				$menuFunction = 'tph_fia_filter_options';
				$icon = plugins_url( 'icon.png', __FILE__ );
				add_menu_page( $menuTitle, $menuTitle, 'manage_options', $menuSlug, $menuFunction, $icon);
			}

		# OPTIONS PAGE

			function tph_fia_filter_options() {
				if (!current_user_can('manage_options')) 
					wp_die( __('You do not have sufficient permissions to access this page. '.__line__ ));
		    	
		    	wp_enqueue_script( 'suggest' );

				include(dirname(__file__).'/options.php');
				return;
			}

		# SETTINGS

			function tph_fia_filter_register_settings() {
				register_setting('tph_fia_filter_settings', 'tph_fia_filter_settings');
			}

		add_action( 'admin_menu', 'tph_fia_filter_menu' );
		add_action( 'admin_init', 'tph_fia_filter_register_settings');

		
} else {
	
	############################################
	# FB INSTANT ARTICLE STUFF
	############################################
		// https://codex.wordpress.org/Class_Reference/WP_Query
		function tph_fia_filter( $query ) {
			if ( $query->is_main_query() && $query->is_feed( INSTANT_ARTICLES_SLUG ) ) {

				// $query->set( 'posts_per_page', 1000 );
				// $query->set( 'posts_per_rss', 1000 );
				// $query->set( 'nopaging', true );

				$settings = get_option('tph_fia_filter_settings'); 

				$allowed_tags = $denied_tags = $allowed_cats = $denied_cats = '';
				
				$checkbox = true;
				if( !empty($settings['allowed_tag'])) {
					// $query->set( 'tax_query', array(array( 'tag' => $settings['allowed_tag'] )));
					$checkbox = false;
					$allowed_tags = array(
										'taxonomy' => 'post_tag',
										'field'    => 'name',
										'terms'    => explode(",", $settings['allowed_tag']),
					);


				}
				if( !empty($settings['denied_tag'])) {
					$checkbox = false;
					$denied_tags = array(
										'taxonomy' => 'post_tag',
										'field'    => 'name',
										'terms'    => explode(",", $settings['denied_tag']),
										'operator' => 'NOT IN',
					);

				}
				if( !empty($settings['allowed_cat'])) {
					$checkbox = false;
					$allowed_cats = array(
										'taxonomy' => 'category',
										'field'    => 'name',
										'terms'    => explode(",", $settings['allowed_cat']),
					);


				}
				if( !empty($settings['denied_cat'])) {
					$checkbox = false;
					$denied_cats = array(
										'taxonomy' => 'category',
										'field'    => 'name',
										'terms'    => explode(",", $settings['denied_cat']),
					);


				}

				if ($checkbox && $settings['checkbox_filter'] == '1') {
					$query->set( 'meta_query', 	array(array('key' => 'wp_fia_filter','value' => '1','compare' => '=')));
				} else if ($checkbox == false) {
					//  $query->set( 'tax_query', array(array( 'tag' => $settings['allowed_tag'] )));
						$query->set( 'tax_query', 	
												array(
													'relation' => 'AND',
													$allowed_tags,
													// $denied_tags,
													// $allowed_cats,
													// $denied_cats,
												));

				}

			}
		}
		add_action( 'pre_get_posts', 'tph_fia_filter', 11, 1 );

	############################################
	# DISABLES THE CATEGORY ON OUTPUT!
	############################################
		$tph_temp_settings = get_option('tph_fia_filter_settings'); 
		if( !empty($tph_temp_settings['remove_category']) && $tph_temp_settings['remove_category'] == '1') {
			function tph_fia_disable_cat_output($content){
				return false;
			}
			add_filter( 'instant_articles_cover_kicker', 'tph_fia_disable_cat_output' );
		}





}

		