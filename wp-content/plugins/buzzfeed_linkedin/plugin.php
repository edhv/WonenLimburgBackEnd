<?php

/*
Plugin Name: Buzzfeed Linkedin
Plugin URI: 
Description: Get linkedin feed
Author: Ties Kuypers, global structure by Jeroen Braspenning
Author URI: 
Version: 1.0
Text Domain: 
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/


/* ------------------------------------------------------------------------ */
if( class_exists('BuzzFeed_object') ) {
	
	class BuzzFeedLinkedin_object extends BuzzFeed_object {

		// Setters overwrite
		

	}

}






/* ------------------------------------------------------------------------ */

if( class_exists('BuzzFeed_collection') ) {


	class BuzzFeedLinkedin_collection extends BuzzFeed_collection {

		/* GETTERS ------------------ */


		/* Get linkedin feed */
		function import($settings, $source, $nr_of_feeds, $lat = NULL, $long = NULL, $radius = 5, $startdate, $enddate,$offset=NULL, $types = array(), $regio = array(), $afdeling=NULL,$naam=NULL,$sort=NULL ) 
		{	
			
			include_once('library/LinkedIn.php');
	
			$li = new LinkedIn(
			  array(
			    'api_key' => '77m5pz5y47ph7x', 
			    'api_secret' => 'PLDrGmEcfe4Oz6J1', 
			    'callback_url' => 'https://www.mijnwonenlimburg.nl/app/api'
			  )
			);

		

				foreach($query->posts as $post)
				{	
				
				    
					$this->feeds[] = $feed_object;

				}

			
			
			
			//Set the cache
			$this->set_cache();
			
			$this_json = json_encode($this);

		

	}
}
}






/* ------------------------------------------------------------------------ */

if( class_exists('BuzzFeed') ) {


	class BuzzFeedLinkedin extends BuzzFeed {


		function __construct()
		{


			// Basic
			$this->slug  = "linkedin";
			$this->label = "linkedin";

			// Settings
			$this->settings = array(
				"nr_of_feeds"    => 20,
				'linkedin'        => array(),
				"offset" => NULL,
				"default_source" => "wonenlimburg"
			);


			$this->feeds_collection = new BuzzFeedLinkedin_collection(); 
			$this->feeds_collection->set_type($this->slug);

			// Construct the parent()
	    	parent::__construct();

		}



		function register_filters() {

			// Add filter here to connect the api to channel functions
			// ie. add_filter($this->slug."/get_feeds", array($this,"import_user_feed"), 1,2);
			add_filter($this->slug."/get_feeds", array($this,"get_feeds"), 1,2);
		}



		function get_feeds($arguments) {
		

			// // defaults
			$nr_of_feeds = $this->settings['nr_of_feeds'];
			$source      = $this->settings['default_source'];
			$lat         = NULL;
			$long        = NULL;
			$radius      = 5;
			$offset     = $this->settings['offset'];
			$types		= NULL;
			$regio		= NULL;
			$afdeling	= NULL;
			$naam		= NULL;
			$sort		= NULL;
		

			// overwrite defaults
			if (isset($arguments['nr_of_feeds'])) { $nr_of_feeds = $arguments['nr_of_feeds']; }
			if (isset($arguments['source'])) { $source = $arguments['source']; }



			$this->feeds_collection->import($this->settings['linkedin'],$source, $nr_of_feeds,0,0,5,0,0,$offset,$types, $regio, $afdeling,$naam,$sort);

			//$this->feeds_collection->sort_feeds();

			$this->feeds_collection->feeds = array_slice($this->feeds_collection->feeds, $offset, $nr_of_feeds);
			$this->feeds_collection->feeds = array_values($this->feeds_collection->feeds);
			
			// Return status and response
			return array("status_code" => 1,"totalamount" => $this->feeds_collection->total , "response" => $this->feeds_collection->feeds);


		}


	}

	new BuzzFeedLinkedin();

}


?>