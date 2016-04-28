<?php

/*
Plugin Name: Buzzfeed Rss
Plugin URI: 
Description: Get an RSS feed
Author: Jeroen Braspenning
Author URI: www.edhv.nl
Version: 1.0
Text Domain: 
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/



/* ------------------------------------------------------------------------ */
if( class_exists('BuzzFeed_object') ) {
	
	class BuzzFeedRss_object extends BuzzFeed_object {


	}

}





/* ------------------------------------------------------------------------ */

if( class_exists('BuzzFeed_collection') ) {


	class BuzzFeedRss_collection extends BuzzFeed_collection {


		/* GETTERS ------------------ */



	}
}






/* ------------------------------------------------------------------------ */

if( class_exists('BuzzFeed') ) {

	class BuzzFeedRss extends BuzzFeed {


		function __construct()
		{


			// Basic
			$this->slug = "rss";
			$this->label = "Rss";

			// Settings
			$this->settings = array(

			);

			$this->feeds_collection = new BuzzFeedRss_collection(); 
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

			echo "get feeds rss";
			die();

			// // defaults
			// $nr_of_feeds = 40;
			// $source      = "wonenlimburg";
			// $lat         = NULL;
			// $long        = NULL;
			// $radius      = 5;
			// $offset     = NULL;
			// $types		= NULL;
			// $regio		= NULL;
			// $afdeling	= NULL;
			// $naam		= NULL;
			// $sort		= NULL;
			// // overwrite defaults
			// if (isset($arguments['nr_of_feeds'])) { $nr_of_feeds = $arguments['nr_of_feeds']; }
			// if (isset($arguments['source'])) { $source = $arguments['source']; }
			// if (isset($arguments['offset'])) { $offset = $arguments['offset']; }

			
			// // overwrite geo defaults
			// if (isset($arguments['geo']))
			// {
			// 	$geo  = explode(',', $arguments['geo']);
			// 	if(count($geo) >= 2)
			// 	{
			// 		$lat  = $geo[0];
			// 		$long = $geo[1];	
			// 	}
			// }
			// if (isset($arguments['radius'])) { $radius = $arguments['radius']; }


			// $this->feeds_collection->import($this->settings["twitter"], $source, $nr_of_feeds , $lat, $long, $radius,0,0,$offset,$types,$regio,$afdeling,$naam,$sort);


			// $this->feeds_collection->feeds = array_slice($this->feeds_collection->feeds, $offset, $nr_of_feeds);
			
			// $this->feeds_collection->feeds = array_values($this->feeds_collection->feeds);

			// // Return status and response
			// return array("status_code"=>1,"totalamount"=>$this->feeds_collection->total,"response"=>$this->feeds_collection->feeds);

		}


	}

	new BuzzFeedRss();

}


?>