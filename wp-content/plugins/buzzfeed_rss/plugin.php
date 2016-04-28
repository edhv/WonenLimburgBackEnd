<?php

/*
Plugin Name: Buzzfeed Rss
Plugin URI: 
Description: Get an RSS feed
Author: Jeroen Braspenning
Author URI: www.edhv.nl
Version: 1.0.1
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
		function import($settings, $source, $nr_of_feeds, $lat = NULL, $long = NULL, $radius = 5, $startdate, $enddate,$offset=NULL,$types=NULL, $regio = array(), $afdeling=NULL,$naam=NULL,$sort=NULL ) {


			// retrieve rss data
			$rssFeed = $settings['feeds'][$source];

			$feedObject = implode(file($rssFeed));
			$xmlObject = simplexml_load_string($feedObject, null, LIBXML_NOCDATA);
			$json = json_encode($xmlObject);
			$rssArray = json_decode($json,TRUE);

			$items = $rssArray['channel']['item'];

			// walk through the rss items
			foreach ($items as $index => $rssItem) {

				$feed_object = new BuzzFeedRss_object();

				$feed_object->set_type($source);
				$feed_object->set_feed_id($rssItem['guid']);
				$feed_object->set_timestamp(strtotime($rssItem['pubDate']));
				$feed_object->set_date(strtotime($rssItem['pubDate']));
				$feed_object->set_title($rssItem['title']);
				$feed_object->set_text($rssItem['description']);
				//$feed_object->set_media($media);

				$this->feeds[] = $feed_object;

			}

			$this->total = count($items);

			//Set the cache
			$this->set_cache();

			$this_json = json_encode($this);

		
			
			

		}



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

				"feeds" => array(
					"nieuws" => "https://www.wonenlimburg.nl/rss.jsp?objectid=49d13a26-e6ff-4b9b-b3aa-7e32b8fefb76",
				)

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

			// // defaults
			$offset = 0;
			$type = 'nieuws';
			$nr_of_feeds = 40;

			if (isset($arguments['offset'])) { $offset = $arguments['offset']; }
			if (isset($arguments['nr_of_feeds'])) { $nr_of_feeds = $arguments['nr_of_feeds']; }
			if (isset($arguments['type'])) { $type = $arguments['source']; }

			// if the system is cached, don't re import
          	if(!$this->feeds_collection->has_cache) {
          		$this->feeds_collection->import($this->settings, $type, $nr_of_feeds, NULL, NULL, 0, 0,0);
          	}
          	
			$this->feeds_collection->sort_feeds();
			$this->feeds_collection->feeds = array_slice($this->feeds_collection->feeds, $offset, $nr_of_feeds);
			$this->feeds_collection->feeds = array_values($this->feeds_collection->feeds);

			// Return status and response
			return array("status_code"=>1,"totalamount"=>$this->feeds_collection->total,"response"=>$this->feeds_collection->feeds);




		}


	}

	new BuzzFeedRss();

}


?>