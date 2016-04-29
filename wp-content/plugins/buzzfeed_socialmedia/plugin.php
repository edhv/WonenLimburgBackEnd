<?php

/*
Plugin Name: Buzzfeed Social Media
Plugin URI: 
Description: Get compilation of social media feeds
Author: Ties Kuypers, global structure by Jeroen Braspenning
Author URI: 
Version: 1.0
Text Domain: 
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/


/* ------------------------------------------------------------------------ */
if( class_exists('BuzzFeed_object') ) {
	
	class BuzzFeedSocialmedia_object extends BuzzFeed_object {

		// Setters overwrite
		

	}

}






/* ------------------------------------------------------------------------ */

if( class_exists('BuzzFeed_collection') ) {


	class BuzzFeedSocialMedia_collection extends BuzzFeed_collection {

		/* GETTERS ------------------ */
	
		/* Get wordpress feed */
		function import($settings, $source, $nr_of_feeds, $lat = NULL, $long = NULL, $radius = 5, $startdate, $enddate,$offset=NULL,$types=NULL, $regio = array(), $afdeling=NULL,$naam=NULL,$sort=NULL ) 
		{	
		
			// Cache the data
			// if($this->has_cache == TRUE)
			// return;

			$cacheHandle = 'buzzapicache/socialmedia/'.$source.$nr_of_feeds;

			if ($this->has_cache($cacheHandle)) {
				$this->fetch_cache($cacheHandle);
				return;
			}

			
			$timestamp = time();

			if($source=='socialmedia'){
				$feed_object = new BuzzFeedFacebook_object();
				$feed_object->set_type('socialmedia');
				$feed_object->set_feed_id('11111');
				$feed_object->set_timestamp($timestamp);
				$feed_object->set_date($timestamp);
				$feed_object->set_title('socialmedia');
				$feed_object->set_text('');
				$page = get_page_by_title('socialmedia','OBJECT','home');
				$photo_url = get_field('img',$page->ID);
				$feed_object->set_media($photo_url['sizes']['large-thumb']);
				$feed_object->set_url('');
					
				$this->feeds[] = $feed_object;

			}

			if($source=='socialmediasub'){
			

			for ($i=0; $i <=3 ; $i++) { 

				switch ($i) {
					
					case '0':
							$feed_object = new BuzzFeedFacebook_object();
							$feed_object->set_type('socialmedia');
							$feed_object->set_feed_id('22222');
							$feed_object->set_timestamp($timestamp);
							$feed_object->set_date($timestamp);
							$feed_object->set_title('twitter');
							$feed_object->set_text('');
							$feed_object->set_media(get_template_directory_uri().'/img/twitter.png');
							$feed_object->set_url('http://mijnwonenlimburg.nl/app/api/feed/twitter/get_feeds/');
								
							$this->feeds[] = $feed_object;
		
						break;
					
					case '1':
							$feed_object = new BuzzFeedFacebook_object();
							$feed_object->set_type('socialmedia');
							$feed_object->set_feed_id('33333');
							$feed_object->set_timestamp($timestamp);
							$feed_object->set_date($timestamp);
							$feed_object->set_title('facebook');
							$feed_object->set_text('');
							$feed_object->set_media(get_template_directory_uri().'/img/facebook.png');
							$feed_object->set_url('http://www.mijnwonenlimburg.nl/app/api/feed/facebook/get_feeds/');
								
							$this->feeds[] = $feed_object;
						break;

					case '2':
							$feed_object = new BuzzFeedFacebook_object();
							$feed_object->set_type('socialmedia');
							$feed_object->set_feed_id('44444 ');
							$feed_object->set_timestamp($timestamp);
							$feed_object->set_date($timestamp);
							$feed_object->set_title('youtube');
							$feed_object->set_text('');
							$feed_object->set_media(get_template_directory_uri().'/img/youtube.png');
							$feed_object->set_url('http://www.mijnwonenlimburg.nl/app/api/feed/youtube/get_feeds/');
								
							$this->feeds[] = $feed_object;
						break;

					default:
						# code...
						break;
				}
			
				}

			}
			
		
			/*$query	=	'http://mijnwonenlimburg.nl/app/api/feed/facebook/get_feeds/?source='.$source.'&nr_of_feeds=40';
			$query	=	json_decode(file_get_contents($query),true);
			
			$this->total= $query['totalamount'];

			foreach ($query['response'] as $post) {
			

				$feed_object = new BuzzFeedFacebook_object();
				
				$feed_object->set_type($post['type']);
				$feed_object->set_feed_id($post['feed_id']);
				$feed_object->set_timestamp($post['timestamp']);
				$feed_object->set_date($post['date']);
				$feed_object->set_title($post['title']);
				$feed_object->set_text($post['text']);
				$feed_object->set_media($post['media']);
				$feed_object->set_url($post['url']);
				
				$this->feeds[] = $feed_object;
			}
			
			$query    = 'http://mijnwonenlimburg.nl/app/api/feed/twitter/get_feeds/?source='.$source.'&nr_of_feeds=40';
			$query	=	json_decode(file_get_contents($query),true);
			$this->total += $query['totalamount'];
			
			foreach ($query['response'] as $post) {
			

				$feed_object = new BuzzFeedFacebook_object();
				
				$feed_object->set_type($post['type']);
				$feed_object->set_feed_id($post['feed_id']);
				$feed_object->set_timestamp($post['timestamp']);
				$feed_object->set_date($post['date']);
				$feed_object->set_title($post['title']);
				$feed_object->set_text($post['text']);
				$feed_object->set_media($post['media']);
				$feed_object->set_url($post['url']);
				
				$this->feeds[] = $feed_object;
			}*/

			
			//Set the cache
			//$this->set_cache();
			$this->set_cache($cacheHandle, 60);



		}

	}
}



/* ------------------------------------------------------------------------ */

if( class_exists('BuzzFeed') ) {


	class BuzzFeedSocialMedia extends BuzzFeed {


		function __construct()
		{


			// Basic
			$this->slug  = "socialmedia";
			$this->label = "Socialmedia";

			// Settings
			$this->settings = array(
				"nr_of_feeds"    => 3,
				"default_source" => "socialmedia",
				"offset" => NULL,
				'socialmedia'        => array(),
				);


			$this->feeds_collection = new BuzzFeedSocialMedia_collection(); 
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
			$offset     = $this->settings['offset'];
			$types		= NULL;
			$regio		= NULL;
			$afdeling	= NULL;
			$naam		= NULL;
			$sort		= NULL;


			// overwrite defaults
			if (isset($arguments['nr_of_feeds'])) { $nr_of_feeds = $arguments['nr_of_feeds']; }
			if (isset($arguments['source'])) { $source = $arguments['source']; }
			if (isset($arguments['offset'])) { $offset = $arguments['offset']; }

			$this->feeds_collection->import($this->settings['socialmedia'],$source, $nr_of_feeds,0,0,5,0,0,$offset,$types=NULL);
	
			$this->feeds_collection->sort_feeds();

			$this->feeds_collection->feeds = array_slice($this->feeds_collection->feeds, $offset, $nr_of_feeds);

			$this->feeds_collection->feeds = array_values($this->feeds_collection->feeds);
			
			// Return status and response
			return array("status_code" => 1,"totalamount" => $this->feeds_collection->total, "response" => $this->feeds_collection->feeds);


		}


	}

	new BuzzFeedSocialMedia();

}


?>