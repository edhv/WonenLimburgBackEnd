<?php

/*
Plugin Name: Buzzfeed Instagram
Plugin URI: 
Description: Get instagram feed
Author: Lody Aeckerlin, global structure by Jeroen Braspenning
Author URI: www.edhv.nl
Version: 1.0
Text Domain: 
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

include_once('library/Instagram.php');
use MetzWeb\Instagram\Instagram;

/* ------------------------------------------------------------------------ */
if( class_exists('BuzzFeed_object') ) {
	
	class BuzzFeedInstagram_object extends BuzzFeed_object {

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


	class BuzzFeedInstagram_collection extends BuzzFeed_collection {

		/* GETTERS ------------------ */


		/* Get instagram feed */
		function import($settings, $source, $nr_of_feeds, $lat = NULL, $long = NULL, $radius = 5,$startdate, $enddate,$offset=NULL,$types=NULL, $regio = array(), $afdeling=NULL,$naam=NULL,$sort=NULL) {


			// Cache the data
			
			//if($this->has_cache == TRUE);
			//return;

			
			$instagram = new Instagram('40a35cfc91144de7bb827d6d47a12f2f');
			
			//Hashtag detection
			$hashtag = FALSE;
			
			if (substr($source, 0, 5) === 'hash/'){$hashtag=TRUE;} else {$hashtag=FALSE;}
			if (substr($source, 0, 3) === 'GEO'){$geo=TRUE;} else {$geo=FALSE;}

			print_r($instagram);
			/*	
			$totalamount	=	$query->found_posts;
			$this->feeds[] = $totalamount;	
			*/



			if(($hashtag == FALSE) && ($geo == FALSE)){
				
			
				$result = $instagram->searchUser($source,1);
				foreach ($result->data as $user) {
					$id = $user->id;
					break;
				}
				
				$result = $instagram->getUserMedia($id, $nr_of_feeds);
				foreach ($result->data as $media) {
					if ($media->type === 'video') {
						// video
						$array = array();
						$array['date'] = $media->created_time;
						$array['title'] = '';
						$array['text'] = $media->caption->text;
						$array['user'] = $media->caption->from->username;
						$array['source_url'] = $media->link;
						//standard_resolution = 640x640;
						$array['media'][] = $media->images->standard_resolution->url;
						$array['media'][] = $media->videos->standard_resolution->url;
						$array['tags'] = $media->tags;
						$array['geo'] = $media->location;


						// Compose object
						$feed_object = new BuzzFeedInstagram_object();
						$feed_object->set_type($this->type." video");
						$feed_object->set_title($array['title']);
						$feed_object->set_feed_id($array['source_url']);
						$feed_object->set_timestamp($array['date']);
						$feed_object->set_date($array['date']);
						$feed_object->set_text($array['text']);
						$feed_object->set_media($array['media']);
						$feed_object->set_url($array['source_url']);

						$this->feeds[] = $feed_object;

					}

					else {
						//image
						$array = array();
						$array['date'] = $media->created_time;
						$array['title'] = '';
						$array['text'] = $media->caption->text;
						$array['user'] = $media->caption->from->username;
						$array['source_url'] = $media->link;
						//standard_resolution = 640x640;
						$array['media'] = $media->images->standard_resolution->url;
						$array['tags'] = $media->tags;
						$array['geo'] = $media->location;

						// Compose object
						$feed_object = new BuzzFeedInstagram_object();
						$feed_object->set_type($this->type." image");
						$feed_object->set_title($array['title']);
						$feed_object->set_feed_id($array['source_url']);
						$feed_object->set_timestamp($array['date']);
						$feed_object->set_date($array['date']);
						$feed_object->set_text($array['text']);
						$feed_object->set_media($array['media']);
						$feed_object->set_url($array['source_url']);
								
						$this->feeds[] = $feed_object;
					}
					
				}
			}

			else if(($hashtag==TRUE) && ($geo==FALSE)){

				$tag = substr($source, 5);
				$result = $instagram->getTagMedia($tag,$nr_of_feeds);
				
				foreach ($result->data as $media) {
					
					if ($media->type === 'video') {
						// video
						$array = array();
						$array['date'] = $media->created_time;
						$array['title'] = '';
						$array['text'] = $media->caption->text;
						$array['user'] = $media->caption->from->username;
						$array['source_url'] = $media->link;
						//standard_resolution = 640x640;
						$array['media'][] = $media->images->standard_resolution->url;
						$array['media'][] = $media->videos->standard_resolution->url;
						$array['tags'] = $media->tags;
						$array['geo'] = $media->location;

						// Compose object
						$feed_object = new BuzzFeedInstagram_object();
						$feed_object->set_type($this->type." video");
						$feed_object->set_title($array['title']);
						$feed_object->set_feed_id($array['source_url']);
						$feed_object->set_timestamp($array['date']);
						$feed_object->set_date($array['date']);
						$feed_object->set_text($array['text']);
						$feed_object->set_media($array['media']);
						$feed_object->set_url($array['source_url']);

						$this->feeds[] = $feed_object;
					}
					
					else {
						//image
					
						$array = array();
						
						$array['date'] = $media->created_time;
						$array['title'] = '';
						
						if(empty($media->caption)){
						$array['text'] = "";
						$array['user'] = $media->user->username;
						}

						else{
						$array['text'] = $media->caption->text;
						$array['user'] = $media->caption->from->username;
						}

						$array['source_url'] = $media->link;
						//standard_resolution = 640x640;
						$array['media'] = $media->images->standard_resolution->url;
						$array['tags'] = $media->tags;
						$array['geo'] = $media->location;

						// Compose object
						$feed_object = new BuzzFeedInstagram_object();
						$feed_object->set_type($this->type." image");
						$feed_object->set_title($array['title']);
						$feed_object->set_feed_id($array['source_url']);
						$feed_object->set_timestamp($array['date']);
						$feed_object->set_date($array['date']);
						$feed_object->set_text($array['text']);
						$feed_object->set_media($array['media']);
						$feed_object->set_url($array['source_url']);

						$this->feeds[] = $feed_object;
					}
				}	
				
			}
			
			if(($hashtag==FALSE) && ($geo==TRUE)){
				$tags = array('ddw14','dutchdesignweek','getthebuzz');
				$latlng = substr($source, 3);
				$latlng = explode('/',$latlng);
				$lat = $latlng[0];
				$lng = $latlng[1];
				$radius = $nr_of_feeds;
				
				$date = date_create('-7 days');
				$mintime = date_format($date, 'U');
				$date = date_create();
				$maxtime = date_format($date, 'U');
				
				$result = $instagram->searchMedia($lat, $lng, $radius, $mintime, $maxtime);
				foreach ($result->data as $media) {
					$tagresult = array_intersect($media->tags,$tags);
					if(!empty($tagresult))
					{
						if ($media->type === 'video') {
							// video
							$array = array();
							$array['date'] = $media->created_time;
							$array['title'] = '';
							$array['text'] = $media->caption->text;
							$array['user'] = $media->caption->from->username;
							$array['source_url'] = $media->link;
							//standard_resolution = 640x640;
							$array['media'][] = $media->images->standard_resolution->url;
							$array['media'][] = $media->videos->standard_resolution->url;
							$array['tags'] = $media->tags;
							$array['geo'] = $media->location;

							// Compose object
							$feed_object = new BuzzFeedInstagram_object();
							$feed_object->set_type($this->type." video");
							$feed_object->set_title($array['title']);
							$feed_object->set_feed_id($array['source_url']);
							$feed_object->set_timestamp($array['date']);
							$feed_object->set_date($array['date']);
							$feed_object->set_text($array['text']);
							$feed_object->set_media($array['media']);
							$feed_object->set_url($array['source_url']);

							$this->feeds[] = $feed_object;
						}
						else {
							//image
							$array = array();
							$array['date'] = $media->created_time;
							$array['title'] = '';
							$array['text'] = $media->caption->text;
							$array['user'] = $media->caption->from->username;
							$array['source_url'] = $media->link;
							//standard_resolution = 640x640;
							$array['media'] = $media->images->standard_resolution->url;
							$array['tags'] = $media->tags;
							$array['geo'] = $media->location;

							// Compose object
							$feed_object = new BuzzFeedInstagram_object();
							$feed_object->set_type($this->type." image");
							$feed_object->set_title($array['title']);
							$feed_object->set_feed_id($array['source_url']);
							$feed_object->set_timestamp($array['date']);
							$feed_object->set_date($array['date']);
							$feed_object->set_text($array['text']);
							$feed_object->set_media($array['media']);
							$feed_object->set_url($array['source_url']);

							$this->feeds[] = $feed_object;
						}
					}
				}
			}
			
			
			
			//Set the cache
			//$this->set_cache();
			
			
			$this_json = json_encode($this);
			
			
			

		}





	}
}






/* ------------------------------------------------------------------------ */

if( class_exists('BuzzFeed') ) {


	class BuzzFeedInstagram extends BuzzFeed {


		function __construct()
		{


			// Basic
			$this->slug = "instagram";
			$this->label = "Instagram";

			// Settings
			$this->settings = array(
				"nr_of_feeds" => 5,
				"default_source" => "oneirocular",
				"lat" => 0,
				"lon" => 0,
				"offset" => "0",
				"instagram" => array(
					'clientId' => '40a35cfc91144de7bb827d6d47a12f2f'
				)

			);

			$this->feeds_collection = new BuzzFeedInstagram_collection(); 
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
			$source = $this->settings['default_source'];
			$offset     = $this->settings['offset'];
			$types		= NULL;
			$regio		= NULL;
			$afdeling	= NULL;
			$naam		= NULL;
			$sort		= NULL;


			// overwrite defaults
			if (isset($arguments['nr_of_feeds'])) { $nr_of_feeds = $arguments['nr_of_feeds']; }
			if (isset($arguments['source'])) { $source = $arguments['source']; }


			
			$this->feeds_collection->import($this->settings["instagram"],$source, $nr_of_feeds, $lat, $long, $radius,0,0, $offset,$types,$regio,$afdeling,$naam,$sort);

			$this->feeds_collection->sort_feeds();

			$this->feeds_collection->feeds = array_slice($this->feeds_collection->feeds, 0, $nr_of_feeds);
			$this->feeds_collection->feeds = array_values($this->feeds_collection->feeds);


			
			// Return status and response
			return array("status_code"=>1,"response"=>$this->feeds_collection->feeds);



		}


	}

	new BuzzFeedInstagram();

}


?>