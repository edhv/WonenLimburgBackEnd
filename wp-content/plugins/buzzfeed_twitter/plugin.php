<?php

/*
Plugin Name: Buzzfeed Twitter
Plugin URI: 
Description: Get twitter feed
Author: Lody Aeckerlin, global structure by Jeroen Braspenning
Author URI: www.edhv.nl
Version: 1.0
Text Domain: 
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/



/* ------------------------------------------------------------------------ */
if( class_exists('BuzzFeed_object') ) {
	
	class BuzzFeedTwitter_object extends BuzzFeed_object {

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


	class BuzzFeedTwitter_collection extends BuzzFeed_collection {


		/* GETTERS ------------------ */

		/* Get twitter feed */
		function import($twitter_settings, $twitter_account, $nr_of_feeds, $lat = NULL, $long = NULL, $radius = 5, $startdate, $enddate,$offset=NULL,$types=NULL, $regio = array(), $afdeling=NULL,$naam=NULL,$sort=NULL) {

			// Cache the data
			if($this->has_cache == TRUE)
			return;
			
			// Retrieve latest tweets
			include_once('library/twitteroauth/twitteroauth.php');

			
			$twitteroauth = new TwitterOAuth($twitter_settings['consumer_key'], $twitter_settings['consumer_secret'], $twitter_settings['acces_token_key'],$twitter_settings['acces_token_secret']);
			
			//Hashtag detection
			$hashtag = (substr($twitter_account, 0, 5) === 'hash/') ? TRUE : FALSE;

			
			if($hashtag == TRUE)
			{
				$tag     = substr($twitter_account, 5);
				$geocode = '';
				if($lat != NULL && $long != NULL)
				{
					$geocode = '&geocode='.$lat.','.$long.','.$radius.'km';	
				}
				
				
				$tweets = $twitteroauth->get("https://api.twitter.com/1.1/search/tweets.json?q=".$tag."%20-RT&count=".$nr_of_feeds.$geocode);
				
			}
			else
			{	
				$tweets = $twitteroauth->get("https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=".$twitter_account."&exclude_replies=true&count=40");
			}
		
			$this->total	=	count($tweets);
										
			if($hashtag == TRUE)
			{
				$items = $tweets->statuses;
				
				foreach($items as $tweet) 
				{
				
						$date = strtotime($tweet->created_at);
						$text = $tweet->text;
						$user = $tweet->user->name;
						$source_url = $tweet->id_str;
						
						
						$img   = (isset($tweet->entities->media[0]->media_url)) ? $tweet->entities->media[0]->media_url : '';
						$video = $this->fetch_video($tweet);
						$media = ($video == FALSE) ? $img : $video;
						
						$type = ($video == FALSE) ? 'twitter' : 'twitter video';
						
						$tweet_lat  = (!empty($tweet->coordinates)) ? $tweet->coordinates->coordinates[1] : $lat;
						$tweet_long = (!empty($tweet->coordinates)) ? $tweet->coordinates->coordinates[0] : $long;
						
						// Compose object
						$feed_object = new BuzzFeedTwitter_object();
						$feed_object->set_type($type);
						$feed_object->set_feed_id($source_url);
						$feed_object->set_timestamp($date);
						$feed_object->set_date($date);
						$feed_object->set_text($text);
						$feed_object->set_media($media);
						$feed_object->set_url('https://twitter.com/wonenlimburg/status/'.$source_url);
												
						$feed_object->set_lat($tweet_lat);
						$feed_object->set_long($tweet_long);
						
						$this->feeds[] = $feed_object;	
				}
				
			}
			else if($hashtag == FALSE)
			{
				foreach ($tweets as $key => $tweet) 
				{
					$date = strtotime($tweet->created_at);
					$text = $tweet->text;
					$user = $tweet->user->name;
					$source_url = $tweet ->id_str;
					
					$img   = (isset($tweet->entities->media[0]->media_url)) ? $tweet->entities->media[0]->media_url : '';
					$video = $this->fetch_video($tweet);
					$media = ($video == FALSE) ? $img : $video;
					
					$type = ($video == FALSE) ? 'twitter' : 'twitter video';
					
					// Compose object
					$feed_object = new BuzzFeedTwitter_object();
					$feed_object->set_type($type);
					$feed_object->set_feed_id($source_url);
					$feed_object->set_timestamp($date);
					$feed_object->set_date($date);
					$feed_object->set_text($text);
					$feed_object->set_media($media);
					$feed_object->set_url('https://twitter.com/wonenlimburg/status/'.$source_url);
		
					$this->feeds[] = $feed_object;
				}
			}

			
			
			//Set the cache
			$this->set_cache();
			
			$this_json = json_encode($this);

		}
		
		
		
		function fetch_video($tweet)
		{
			//Check if we have urls
			if(isset($tweet->entities->urls))
			{
				$urls = $tweet->entities->urls;
				foreach($urls as $url)
				{
					$display_url = $url->display_url;
					if(stripos($display_url, 'vine.co') !== FALSE)
					{
						return 'http://'.$display_url.'/card';
					}
				}
			}
			
			return FALSE;
		}





	}
}






/* ------------------------------------------------------------------------ */

if( class_exists('BuzzFeed') ) {

	class BuzzFeedTwitter extends BuzzFeed {


		function __construct()
		{


			// Basic
			$this->slug = "twitter";
			$this->label = "Twitter";

			// Settings
			$this->settings = array(

				"twitter" => array(
					"user" => "",
					"consumer_key" => "vgsqUJo2oKft6KTFdPLsw",
					"consumer_secret" => "xb3lnLCuKEre6Pes64YExKNF7cszyIsQf6ghcz4ak",
					"acces_token_key" => "62522673-z6m2TLTQXwBnuxiUewopLY4MFRNWeNQ2QUHn9tdW1",
					"acces_token_secret" => "E0nVEoGbCEIe0KQZzc4gzmx0SDDwQ16wQjRYj9nUrZ4Lc"
				)

			);

			$this->feeds_collection = new BuzzFeedTwitter_collection(); 
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
			$source      = "wonenlimburg";
			$lat         = NULL;
			$long        = NULL;
			$radius      = 5;
			$offset     = NULL;
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


			$this->feeds_collection->import($this->settings["twitter"], $source, $nr_of_feeds , $lat, $long, $radius,0,0,$offset,$types,$regio,$afdeling,$naam,$sort);


			$this->feeds_collection->feeds = array_slice($this->feeds_collection->feeds, $offset, $nr_of_feeds);
			
			$this->feeds_collection->feeds = array_values($this->feeds_collection->feeds);

			// Return status and response
			return array("status_code"=>1,"totalamount"=>$this->feeds_collection->total,"response"=>$this->feeds_collection->feeds);

		}


	}

	new BuzzFeedTwitter();

}


?>