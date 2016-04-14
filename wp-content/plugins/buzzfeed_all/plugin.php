<?php

/*
Plugin Name: Buzzfeed All
Plugin URI: 
Description: Get all feeds
Author: Ties Kuypers, global structure by Jeroen Braspenning
Author URI: 
Version: 1.0
Text Domain: 
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/


/* ------------------------------------------------------------------------ */
if( class_exists('BuzzFeed_object') ) {
	
	class BuzzFeedAll_object extends BuzzFeed_object {

		// Setters overwrite
		

	}

}






/* ------------------------------------------------------------------------ */

if( class_exists('BuzzFeed_collection') ) {


	class BuzzFeedAll_collection extends BuzzFeed_collection {

		/* GETTERS ------------------ */

		/* DDW14 ------------------ */

		private $feed_array = array
		(

	
			//array('youtube', 'user', 'UCoP_KWmDZo8I4xeotYYGAmg', '1'),
			array('socialmedia', '', '', '1'),
			array('wordpress', 'tag', 'bericht', '1'),
			array('wordpress', 'tag', 'wieiswie', '1'),
	        array('wordpress', 'tag', 'kalender', '6'),
	        array('wordpress', 'tag', 'brochure', '1'),
	        array('wordpress', 'tag', 'buurtvan', '1'),
            array('wordpress', 'tag', 'jaarverslag', '1'),
            array('wordpress', 'tag', 'koopenwoon', '1'),
            array('wordpress', 'tag', 'werkenbij', '1'),
            array('wordpress', 'tag', 'huurdersraad', '1'),
            array('wordpress', 'tag', 'boekenkast', '1'),
            array('wordpress', 'tag', 'meldingen', '1'),
          
		);




		function get_source_url($sourcetype, $type_of_data, $source, $number_of_items, $type = 'home', $lat = NULL, $long = NULL, $radius = 5) 
		{	


		
			if($type_of_data == '-') 
			{
				$type_of_data = '';
			}
		
			if($source == '-')
			{
				$source = '';
			}
		
	      
			// Basic url
			$has_geo = false;
			$url     = 'http://www.mijnwonenlimburg.nl/app/api/feed/';
			
			$param = '';
			
			// Social Media
			if($sourcetype == 'socialmedia')
			{
				if($type_of_data == 'user') {
					$param = 'source='.$source.'';	
				} 
			}



			// Facebook
			if($sourcetype == 'facebook')
			{
				if($type_of_data == 'user') {
					$param = 'source='.$source.'';	
				} else if($type_of_data == 'event') {
					$has_geo = true;
					$param  = 'source=event/'.$source.'';
				}
			}
			
			// Instagram @todo: geo?
			if($sourcetype == 'instagram')
			{
				if($type_of_data == 'user') {
					$param = 'source='.$source.'';	
				} else if($type_of_data == 'tag') {
					$param  = 'source=hash/'.$source.'';
				}
			}
			
			// Twitter
			if($sourcetype == 'twitter')
			{
				if($type_of_data == 'user') {
					$param = 'source='.$source.'';	
				} else if($type_of_data == 'tag') {
					$has_geo = true;
					$param  = 'source=hash/'.$source.'';
				}
			}
			
			// Twitter
			if($sourcetype == 'twitter')
			{
				if($type_of_data == 'user') {
					$param = 'source='.$source.'';
				} else if($type_of_data == 'tag') {
					$param = 'source=hash/'.$source.'';
				}
			}

			
			
			// Wordpress
			if($sourcetype == 'wordpress')
			{
				if($source != '')
				{
					$param = 'source='.$source.'';	
				}
			}
			
			
			
			// Youtube
			if($sourcetype == 'youtube')
			{
				if($type_of_data == 'user') {
					$param = 'source='.$source.'';	
				}
				$has_geo = true;
			}
			
		
		
			
			
			// Set the url
			$url .= $sourcetype.'/get_feeds/?'.$param.'&nr_of_feeds='.$number_of_items.''; 
			
			// Add the geo
			if($has_geo == true && $lat != NULL && $long != NULL)
			{
				$url .= '&geo='.$lat.','.$long;
				
				if($radius != NULL)
				{
					$url .= '&radius='.$radius;	
				}
			}
			
			return $url;
		}
		
		
		
		
	

		/* Get wordpress feed */
		function get_all_feeds($types=array(),$nr_of_feeds='')
		{	
		    
			// Cache the data
			$use_cache = (isset($_GET['use_cache'])) ? TRUE : FALSE;
			
            if($use_cache == TRUE)
			{
				echo "AAA";
				$this->fetch_cache('all_feeds');
				return;	
			}
				
			//Loop trough the feeds\
			$feeds = array();


			$args     = array(

			  	    'post_type' =>	'home',
					'post_status' => 'publish',
					'orderby'	=>	'menu_order',
					'posts_per_page'=> -1,
					'order'		=>	'ASC',
					'search_post_title' => $types
									  
					  	);
			$query    = new WP_Query($args);
			
			$source_array = $this->feed_array;
			$feeds[] = $source_array[11];
			array_unshift($types,'meldingen');
			
			foreach($query->posts as $feedurl){	
				
				switch ($feedurl->post_title) {
					
					
					case 'socialmedia':
						$feeds[] = $source_array[0];
						break;

					case 'bericht':
						$feeds[] = $source_array[1];
						break;		

					case 'wieiswie':
						$feeds[] = $source_array[2];
						break;

					case 'kalender':
						$feeds[] = $source_array[3];
						break;

					case 'brochure':
						$feeds[] = $source_array[4];
						break;

					case 'buurtvan':
						$feeds[] = $source_array[5];
						break;

					case 'jaarverslag':
						$feeds[] = $source_array[6];
						break;
					
					case 'koopenwoon':
						$feeds[] = $source_array[7];
						break;

					case 'werkenbij':
						$feeds[] = $source_array[8];
						break;

					case 'huurdersraad':
						$feeds[] = $source_array[9];
						break;

					case 'boekenkast':
						$feeds[] = $source_array[10];
						break;
	
					default:
						# code...
						break;
				}

			}

			
			$this->total = count($types)-2;


	
			foreach($feeds as $items)
			{	
			
			
				if($items[0]=='wordpress'){
			
					
					

					if(in_array($items[2],$types)){

						/*
						$url = call_user_func_array('self::get_source_url', $items); //$this->get_source_url($sourcetype, $type_of_data, $source, $number_of_items, $type, $lat, $long, $radius);
											
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($ch, CURLOPT_URL, $url);
						$result = json_decode(curl_exec($ch));
						curl_close($ch);*/


						// replaced the expensive curl with the call of a simple hook
						$result = apply_filters("wordpress/get_feeds", array(
							"source"=>$items[2],
							"nr_of_feeds"=>$items[3]
						));

						//Add the result
						$this->feeds[] = $result;

					}


				}

				else {

					if(in_array($items[0],$types)){


						/*
						$url = call_user_func_array('self::get_source_url', $items); //$this->get_source_url($sourcetype, $type_of_data, $source, $number_of_items, $type, $lat, $long, $radius);

						$ch = curl_init();
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($ch, CURLOPT_URL, $url);
						$result = json_decode(curl_exec($ch));
						curl_close($ch);
						
						//Add the result
						$this->feeds[] = $result;
						*/

						//replaced the expensive curl with the call of a simple hook
						$result = apply_filters($items[0]."/get_feeds", array(
							"nr_of_feeds"=>$items[3]
						));
				
						//Add the result
						$this->feeds[] = $result;

					}
				}
				
			}
			
			//Set the cache
			$this->set_cache('all_feeds', 30);
		}

	}
}






/* ------------------------------------------------------------------------ */

if( class_exists('BuzzFeed') ) {


	class BuzzFeedAll extends BuzzFeed {


		function __construct()
		{


			// Basic
			$this->slug  = "all";
			$this->label = "all";

			// Settings
			$this->settings = array(
            "types" => ["home","socialmedia","boekenkast","huurdersraad","koopenwoon","werkenbij","bericht","jaarverslag","brochure","kalender","wieiswie","buurtvan"]
            );

			$this->feeds_collection = new BuzzFeedAll_collection(); 
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

	      $types     = $this->settings['types'];
		  $nr_of_feeds = 12; 
		  $offset = 0;

          if (isset($arguments['types'])) { $types = $arguments['types']; $types = explode(",",$types);	array_unshift($types,'home'); }

          if (isset($arguments['nr_of_feeds'])) { $nr_of_feeds = $arguments['nr_of_feeds']+1; }
          if (isset($arguments['offset'])) { $offset = $arguments['offset']; }

          $this->feeds_collection->feeds = [];

		  $this->feeds_collection->get_all_feeds($types,$nr_of_feeds);
		  $this->feeds_collection->feeds = array_slice($this->feeds_collection->feeds, $offset, $nr_of_feeds);
		  $this->feeds_collection->feeds = array_values($this->feeds_collection->feeds);
			//print_r($this->feeds_collection->feeds);
			// Return status and response
		return array("status_code" => 1,"totalamount"=>$this->feeds_collection->total,"response" => $this->feeds_collection->feeds);


		}


	}

	new BuzzFeedAll();

}


?>