<?php

/*
Plugin Name: Buzzfeed Facebook
Plugin URI: 
Description: Get facebook feed
Author: Lody Aeckerlin, global structure by Jeroen Braspenning
Author URI: www.edhv.nl
Version: 1.0
Text Domain: 
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/



/* ------------------------------------------------------------------------ */
if( class_exists('BuzzFeed_object') ) {
	
	class BuzzFeedFacebook_object extends BuzzFeed_object {

		// Setters overwrite
		

	}

}






/* ------------------------------------------------------------------------ */

if( class_exists('BuzzFeed_collection') ) {


	class BuzzFeedFacebook_collection extends BuzzFeed_collection {

		/* GETTERS ------------------ */

		/* Get twitter feed */
		function import($settings, $source, $nr_of_feeds, $lat = NULL, $long = NULL, $radius = 5, $startdate, $enddate,$offset=NULL,$regio = array(), $afdeling=NULL,$naam=NULL,$sort=NULL) {


			// Cache the data
			if($this->has_cache == TRUE)
			return;
				

			include_once('library/facebook-php-sdk-master/src/facebook.php');


			$facebook = new Facebook($settings);		

			
			$pagefeed = $facebook->api("/" . $source . "/posts?limit=40");

			//print_r($pagefeed);
			$this->total = count($pagefeed['data']);
			
			
			foreach($pagefeed['data'] as $post) 
				{
					
					
			
					if ($post['type'] == 'status' || $post['type'] == 'link' || $post['type'] == 'photo') 
					{

							$array = array();
							$id = $post['id'];
							$postid = explode('_',$id);
							$postid = $postid[1];
							$array['date'] = strtotime($post['created_time']);
							$array['title'] = '';
							
							if($post['status_type']!='shared_story'){
							if (empty($post['message']) === false) {
								$array['text'] = htmlentities($post['message']);
							} elseif (empty($post['story']) === false) {
								$array['text'] = htmlentities($post['story']);
							} elseif (empty($post['name']) === false) {
								$array['text']=htmlentities($post['name']);
							} else {
								$array['text'] = "";
							}}
							else{
								if (empty($post['description']) === false) {
									$array['text'] = htmlentities($post['description']);
								}
							}
							
							
							$array['source_url'] = 'https://www.facebook.com/'.$source.'/posts/'.$postid;
							if(isset($post['object_id'])){
								$array['media']=$post['object_id'];
								$media_feed = $facebook->api("/".$array['media']);
							if(!empty($media_feed['images']['0']['source'])){
								$array['media']=$media_feed['images']['0']['source'];
								}
								else{$array['media']='';}
								}					
							else{
								$array['media']='';
							}												

							$keywords = '';
							$string = $array['text'];
							$str = 1;
							 preg_match_all('/#(\w+)/',$string,$matches);
							  $i = 0;
							  if ($str) {
							   foreach ($matches[1] as $match) {
							   $count = count($matches[1]);
							   $keywords .= "$match";
							   $i++;
							   if ($count > $i) $keywords .= ", ";
							  }
							 } else {
							   foreach ($matches[1] as $match) {
								$keyword[] = $match;
								   }
								  $keywords = $keyword;
							 }
							
							$array['tags'] = $keywords;
							$array['geo'] = '';
							
											
							// Compose object
							$feed_object = new BuzzFeedFacebook_object();
							$feed_object->set_type($this->type);
							$feed_object->set_feed_id($array['source_url']);
							$feed_object->set_timestamp($array['date']);
							$feed_object->set_date($array['date']);
							$feed_object->set_title($array['title']);
							$feed_object->set_text($array['text']);
							$feed_object->set_media($array['media']);
							$feed_object->set_url($array['source_url']);
				
							$this->feeds[] = $feed_object;
							
						
						
					}
										
				}
			
			

			//Set the cache
			$this->set_cache();
			$this_json = json_encode($this);
		}





	}
}






/* ------------------------------------------------------------------------ */

if( class_exists('BuzzFeed') ) {


	class BuzzFeedFacebook extends BuzzFeed {


		function __construct()
		{


			// Basic
			$this->slug = "facebook";
			$this->label = "Facebook";

			// Settings
			$this->settings = array(
				"nr_of_feeds" => 5,
				"default_source" => "wonenlimburg",
				"offset" => NULL,
				"facebook" => array(
					'appId' => '993364724047662',
					'secret' => 'ec6b48ca33ba27509d139e9c1190e8c1',
					'fileUpload' => false
				)

			);

			$this->feeds_collection = new BuzzFeedFacebook_collection(); 
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
			if (isset($arguments['offset'])) { $offset = $arguments['offset']; }


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
			
			$this->feeds_collection->import($this->settings["facebook"],$source, $nr_of_feeds, $lat, $long, $radius,0,0, $offset,$regio,$afdeling,$naam,$sort);
			$this->feeds_collection->sort_feeds();

			$this->feeds_collection->feeds = array_slice($this->feeds_collection->feeds, $offset, $nr_of_feeds);
			$this->feeds_collection->feeds = array_values($this->feeds_collection->feeds);


	
			// Return status and response
			return array("status_code"=>1,"totalamount"=>$this->feeds_collection->total,"response"=>$this->feeds_collection->feeds);


		}


	}

	new BuzzFeedFacebook();

}


?>