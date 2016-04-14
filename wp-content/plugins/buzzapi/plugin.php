<?php

/**
* Plugin Name: Buzzfeeds API
* Plugin URI: 
* Description: 
* Version: 0.1
* Author: Jeroen Braspenning, Edhv
* Author URI: http://www.jeroenbraspenning.nl
* License: 
* Copyright: Jeroen Braspenning, Edhv
*/
 header("Access-Control-Allow-Origin:*");



if (!class_exists('BuzzApi')) 
{

	/**
	* 
	*/
	class BuzzApi
	{
		

		/**
		 * 	get_instance
		 *  
		 * 	returns the singleton instance of this class
		 *  @return Singleton instance
		 * 
		 */
	    public static function get_instance()
	    {
	        static $instance = null;
	        if (null === $instance) {
	            $instance = new static();
	        }

	        return $instance;
	    }


		/**
		 * 	constructor
		 * 	
		 */
		
		function __construct()
		{

			// vars
			$this->settings = array(
				'namespace' => 'buzz_api',
				'api' => array(
					'api_prefix' => 'api',
					'topic_prefix' => 'feed',
					'rewrite_tags' => array(
						'feed_channel' => '([^&/]+)',
						'feed_action' => '([^&/]+)',
					)
				)
			);


			$this->feeds = array();

			// hooks
			//register_activation_hook( __FILE__, array($this, 'activate_plugin'), 1 );
			register_activation_hook( __FILE__, array($this, 'plugin_activation'), 1 );

			// actions
			add_action('init', array($this, 'init'), 1);

			// catch the template_redirect in case of an api call
			add_action( 'pre_get_posts', array($this, 'catch_api_call') );

			// filters

			// includes
			$this->include_before_theme();
			add_action('plugins_loaded', array($this, 'initialize_feeds'), 1);

			//add_action('plugins_loaded', array($this, 'initialize_feeds'), 1);



		}


		/**
		 * 	init
		 *  
		 * 	Initialize the main parts of the projects plugin
		 * 	
		 */
		
		function init() 
		{


			// rewrite configuration
			global $wp, $wp_rewrite;

			$this->set_rewrite_tags();

			$this->set_rewrite_rules();

			// Admin only
			//$apiResult = apply_filters('projects/getAll', array(), 2);
			//print_r($apiResult);
		}



		/**
		 * 	plugin_activation
		 *
		 * 	this function gets called after activation and registers the api rewrite
		 * 
		 */
		function plugin_activation() {

			// rewrite configuration
			global $wp, $wp_rewrite;

			$this->set_rewrite_tags();

			$this->set_rewrite_rules();

			// Resets the rewrite rules
			flush_rewrite_rules();

		}


		/* 	SETTERS  */

		/**
		 * 	set_rewrite_tags
		 *
		 * 	sets the rewrite tags so the api url can be parsed correctly into query vars
		 * 	
		 */
		function set_rewrite_tags() {
			foreach ($this->settings['api']['rewrite_tags'] as $tagName => $tagRegex) {
				add_rewrite_tag('%'.$tagName.'%', $tagRegex);
			}
		}


		/**
		 * 	set_rewrite_rules
		 *
		 * 	sets the rewrite tags so the api url can be parsed correctly
		 * 	
		 */
		function set_rewrite_rules() {

			// compose rewrite regex
			// i.e. api/feeds/([^&/]+)?
			$rewrite_regex = $this->settings['api']['api_prefix'] . '/' . $this->settings['api']['topic_prefix'];
			// compose redirect
			// i.e. index.php?api_action=$matches[1]
			$rewrite_redirect = 'index.php?';

			$index = 1;

			foreach ($this->settings['api']['rewrite_tags'] as $tagName => $tagRegex) {
				$rewrite_regex .= '/'.$tagRegex;
				$rewrite_redirect .= $tagName.'=$matches['.$index.']&';

				$index++;
			}

			// finish regex
			$rewrite_regex .= '?$';
			// finish redirect, remove last &
			$rewrite_redirect = rtrim($rewrite_redirect, "&");

			// set the rewrite rule
			add_rewrite_rule($rewrite_regex, $rewrite_redirect, 'top');

		}



		/**
		 * 	include_before_them
		 *  
		 *  Will include the core files
		 * 
		 */
		function include_before_theme() {

		
			// include necessary classes and functions
			include_once('core/feed_collection.php');
			include_once('core/feed_object.php');
			include_once('core/feed.php');
			
			// admin only includes
			if( is_admin() ) { }

		}


		function initialize_feeds() {
			// Request all the plugin feeds to register
			do_action('buzz/register_feed');

		}





		/**
		 *  catch_api_call
		 *
		 * 	this function tries to catch the api call by hooking into the pre_get_posts
		 * 
		 */
		function catch_api_call($query) {
			global $wp, $wp_rewrite;
	
			// Check if this might be an api request
			// Only fire on main query calls
			if ( $query->is_main_query() && isset($wp->query_vars['feed_channel'])) {
				$query = false;

				$this->process_api_call();
			}

		}



		/**
		 *  process_api_call
		 *
		 * 	processes the API call
		 * 
		 */
		function process_api_call() {
			global $wp, $wp_rewrite;
			
			$params = $_GET;	


			// perform the actual api call
			$api_result = apply_filters($wp->query_vars['feed_channel'] . '/' . $wp->query_vars['feed_action'], $params, 2);
		
			// get the result status, if no results the status will be 404
			$result_status = isset($api_result['status_code']) ? $api_result['status_code'] : 404;
			//$result_status = 1;

			// enable gzip compression for the json result
			//ob_start('ob_gzhandler');

			// check the api result
			if ($result_status === 1) {

				echo json_encode($api_result);

			} else {
			
				// check what kind of error is returned
				switch ($result_status) {
					case 0:
						$response = $api_result['error_description'];
						break;
					case 404:
						$response = "method doesn't exist";
						break;
				}
		
			 	echo json_encode(array("status_code"=>$result_status, "response"=>$response));
			 }

			die();

		}



	}


	/**
	 * 	buzzfeeds
	 *
	 * 	Main function which ensures that buzzfeeds is initiated only once
	 *  Also this allows to cal buzzfeeds(); like a global variable everywhere
	 * 	
	 *  @return Object
	 */

	function buzz_api()
	{
		global $BuzzApi;
		
		if(!isset($BuzzApi))
		{
			$BuzzApi = BuzzApi::get_instance();
		}
		
		return $BuzzApi;
	}


	// initialize
	buzz_api();


} // class_exists check




?>