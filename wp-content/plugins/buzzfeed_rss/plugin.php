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
		function import( $settings, $type, $nr_of_feeds, $offset=NULL ) {

			$rssFeed = $settings['feeds'][$type];

			$feedObject = implode(file($rssFeed));
			$xmlObject = simplexml_load_string($feedObject, null, LIBXML_NOCDATA);
			$json = json_encode($xmlObject);
			$rssArray = json_decode($json,TRUE);

			$items = $rssArray['channel']['item'];

			// walk through the rss items
			foreach ($items as $index => $rssItem) {

				$feed_object = new BuzzFeedRss_object();

				$feed_object->set_type($type);
				$feed_object->set_feed_id($rssItem['guid']);
				$feed_object->set_timestamp(strtotime($rssItem['pubDate']));
				$feed_object->set_date(strtotime($rssItem['pubDate']));
				$feed_object->set_title($rssItem['title']);
				$feed_object->set_text($rssItem['description']);
				//$feed_object->set_media($media);
				$feed_object->set_url('https://twitter.com/wonenlimburg/status/'.$source_url);

				$this->feeds[] = $feed_object;

			}

			$this->total = count($items);
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
			$offset = $arguments['offset'] || 0;
			$type = $arguments['source'];
			$nr_of_feeds = 40;

			if ($arguments['offset']) { $offset = $arguments['offset']; }
			if ($arguments['nr_of_feeds']) { $nr_of_feeds = $arguments['nr_of_feeds']; }

		//	echo $type;
          	$this->feeds_collection->feeds = [];

			$this->feeds_collection->import($this->settings, $type, $nr_of_feeds, $offset );
			$this->feeds_collection->sort_feeds();

			$this->feeds_collection->feeds = array_slice($this->feeds_collection->feeds, $offset, $nr_of_feeds);
			$this->feeds_collection->feeds = array_values($this->feeds_collection->feeds);
			//print_r($this->feeds_collection->feeds);
			// Return status and response
			return array("status_code"=>1,"totalamount"=>$this->feeds_collection->total,"response"=>$this->feeds_collection->feeds);



			
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




			// $this->feeds_collection->feeds = array_slice($this->feeds_collection->feeds, $offset, $nr_of_feeds);
			
			// $this->feeds_collection->feeds = array_values($this->feeds_collection->feeds);

			// // Return status and response
			// return array("status_code"=>1,"totalamount"=>$this->feeds_collection->total,"response"=>$this->feeds_collection->feeds);

		}


	}

	new BuzzFeedRss();

}


?>