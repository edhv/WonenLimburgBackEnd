<?php

/*
Plugin Name: Buzzfeed Pages
Plugin URI:
Description: Get wordpress pages as object
Author: global structure by Jeroen Braspenning
Author URI:
Version: 1.0.1
Text Domain:
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/


/* ------------------------------------------------------------------------ */
if( class_exists('BuzzFeed_object') ) {

	class BuzzFeedPages_object extends BuzzFeed_object {

		// Setters overwrite
		function set_media_large($value = false) {
			$this->media_large = $value;
		}

	}

}






/* ------------------------------------------------------------------------ */

if( class_exists('BuzzFeed_collection') ) {


	class BuzzFeedPages_collection extends BuzzFeed_collection {



		function __construct()
		{

			add_action( 'save_post', array($this,"on_post_save"),10,2);

			// Construct the parent()
	    	//parent::__construct();

		}



		// setup a hook that on a post save the cache is emptied
		function on_post_save( $post_id, $post ) {

			global $wpdb; // you may not need this part. Try with and without it

			if ( $post->post_type != 'page') {
			     return;
			 }

			// if the saved post is found inside the post types array, reset the cache
			delete_transient('buzzfeedcache/pages');

			
		}

		

	}
}






/* ------------------------------------------------------------------------ */

if( class_exists('BuzzFeed') ) {


	class BuzzFeedPages extends BuzzFeed {


		function __construct()
		{


			// Basic
			$this->slug  = "pages";
			$this->label = "pages";
			$currenttime = strtotime('today');

			// Settings
			$this->settings = array(
				"nr_of_feeds"    => 20,
				"default_source" => "buurtvan",
				"startdate" => $currenttime,
				"enddate" => 32503680000,
				"offset" => 0,
				"types" => NULL,
				"regio" => null,
				"afdeling" => null,
				"naam" => NULL,
				"sort" => "ASC",
				'wordpress'        => array(),
				);


			$this->feeds_collection = new BuzzFeedPages_collection();
			$this->feeds_collection->set_type($this->slug);

			// Construct the parent()
			parent::__construct();

		}



		function register_filters() {

			// Add filter here to connect the api to channel functions
			// ie. add_filter($this->slug."/get_feeds", array($this,"import_user_feed"), 1,2);
			add_filter($this->slug."/get_pages", array($this,"get_pages"), 1,2);
		}


		/*
		Give a post object and the function returns the right information
		 */
		function get_page_content($page) {

			$content = array();

			$content['title'] = $page->post_title;

			switch ($page->post_name) {

				case 'contact':
					$content['column_one'] = get_field('contact_column_1', $page->ID);
					$content['column_two'] = get_field('contact_column_2', $page->ID);
					break;
				
				default:
					$content['body'] = apply_filters('the_content', $page->post_content);
					break;
			}

			return $content;
		}


		function get_pages($arguments) {



			$pagesObject = array();

			$cache = get_transient( 'buzzfeedcache/pages' );

			if (!$cache) {

				$pagesObject = array();

				$pages = get_pages(); 
				
				// get the page content
				foreach ( $pages as $page ) {
					$pagesObject[$page->post_name] = $this->get_page_content($page);
				}


				// set the transient
				set_transient('buzzfeedcache/pages', $pagesObject);

			} else {
				$pagesObject = $cache;
			}


			// Return status and response
			return array("status_code" => 1,"pages" => $pagesObject);


		}


	}

	new BuzzFeedPages();

}


?>
