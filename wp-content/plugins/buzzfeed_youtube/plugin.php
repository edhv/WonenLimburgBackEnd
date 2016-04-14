<?php

/*
Plugin Name: Buzzfeed Youtube
Plugin URI: 
Description: Get youtube feed
Author: Ties Kuypers, global structure by Jeroen Braspenning
Author URI:
Version: 1.0
Text Domain: 
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/



/* ------------------------------------------------------------------------ */
if( class_exists('BuzzFeed_object') ) {
	
	class BuzzFeedYoutube_object extends BuzzFeed_object {

		// Setters overwrite
		/*function set_date($value = false) {
			$this->date = $this->normalize_date(strtotime($value));
		}

		function set_timestamp($value = false) {
			$this->timestamp = strtotime($value);
			
		}*/

	}

}






/* ------------------------------------------------------------------------ */

if( class_exists('BuzzFeed_collection') ) {


	class BuzzFeedYoutube_collection extends BuzzFeed_collection {


		/* GETTERS ------------------ */
	

		/* Get twitter feed */
		function import($settings, $source, $nr_of_feeds, $lat = NULL, $long = NULL, $radius = 5, $startdate, $enddate,$offset=NULL,$regio = array(), $afdeling=NULL,$naam=NULL,$sort=NULL) {

			// cache the data
				if($this->has_cache == TRUE)
				return;
				
				if ($nr_of_feeds>40){$nr_of_feeds=40;}

				$query	=	'https://www.googleapis.com/youtube/v3/search?key=AIzaSyDajzZ1VV9AlLOI0t34cn2jOJl7ovl7uCM&channelId='.$source.'&part=snippet,id&order=date&maxResults=40';
				$query	=	json_decode(file_get_contents($query),true);

				$this->total = $query['pageInfo']['resultsPerPage'];

				// Add each result to the appropriate list, and then display the lists of
				// matching videos, channels, and playlists.
				foreach ($query['items'] as $post) 
				{
				
				// Compose object
				if(isset($post['id']['videoId'])){

					$feed_object = new BuzzFeedYoutube_object();
					
					$feed_object->set_type('youtube');
					$feed_object->set_timestamp(strtotime($post['snippet']['publishedAt']));
					$feed_object->set_date(strtotime($post['snippet']['publishedAt']));
					$feed_object->set_title($post['snippet']['title']);
					$feed_object->set_text($post['snippet']['description']);
					$feed_object->set_feed_id($post['id']['videoId']);
					$feed_object->set_media($post['snippet']['thumbnails']['high']['url']);
					$feed_object->set_url($post['id']['videoId']);
				}
				
				else{					
				//do nothing
					}
				
				
			

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

	class BuzzFeedYoutube extends BuzzFeed {

		function __construct()
		{


			// Basic
			$this->slug  = "youtube";
			$this->label = "Youtube";

			// Settings
			$this->settings = array
			(
				'youtube' => array
				(
					'developer_key'    => 'AIzaSyDajzZ1VV9AlLOI0t34cn2jOJl7ovl7uCM'
				)
			);

			$this->feeds_collection = new BuzzFeedYoutube_collection(); 
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

			// defaults
			$nr_of_feeds = 40;
			$source      = "UCoP_KWmDZo8I4xeotYYGAmg";
			$lat         = NULL;
			$long        = NULL;
			$radius      = 5;
			$offset		= NULL;
			$types		= NULL;
			$regio		= NULL;
			$afdeling	= NULL;
			$naam		= NULL;
			$sort		= NULL;

			// overwrite defaults
			if (isset($arguments['nr_of_feeds'])) { $nr_of_feeds = $arguments['nr_of_feeds']; }
			if (isset($arguments['source'])) { $source = $arguments['source']; }
			if (isset($arguments['offset'])) { $offset = $arguments['offset']; }
			
			// overwrite geo defaults
			if (isset($arguments['geo']))
			{
				$geo  = explode(',', $arguments['geo']);
				if(count($geo) >= 2)
				{
					$lat  = $geo[0];
					$long = $geo[1];	
				}
			}
			if (isset($arguments['radius'])) { $radius = $arguments['radius']; }


			$this->feeds_collection->import($this->settings['youtube'], $source, $nr_of_feeds , $lat, $long, $radius,0,0,$offset,$regio,$afdeling,$naam,$sort);

			//$this->feeds_collection->sort_feeds();

			$this->feeds_collection->feeds = array_slice($this->feeds_collection->feeds, $offset, $nr_of_feeds);
			$this->feeds_collection->feeds = array_values($this->feeds_collection->feeds);


			// Return status and response
			return array("status_code"=>1, "totalamount" => $this->feeds_collection->total, "response"=>$this->feeds_collection->feeds);

		}


	}

	new BuzzFeedYoutube();

}


?>