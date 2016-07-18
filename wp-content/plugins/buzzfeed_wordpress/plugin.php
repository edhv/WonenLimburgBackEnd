<?php

/*
Plugin Name: Buzzfeed Wordpress
Plugin URI:
Description: Get wordpress feed
Author: Ties Kuypers, global structure by Jeroen Braspenning
Author URI:
Version: 1.0.1
Text Domain:
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/


/* ------------------------------------------------------------------------ */
if( class_exists('BuzzFeed_object') ) {

	class BuzzFeedWordpress_object extends BuzzFeed_object {

		// Setters overwrite
		function set_media_large($value = false) {
			$this->media_large = $value;
		}

	}

}






/* ------------------------------------------------------------------------ */

if( class_exists('BuzzFeed_collection') ) {


	class BuzzFeedWordpress_collection extends BuzzFeed_collection {



		function __construct()
		{

			add_action( 'save_post', array($this,"on_post_save"),10,1);

			// Construct the parent()
	    	//parent::__construct();

		}



		// setup a hook that on a post save the cache is emptied
		function on_post_save( $post_id ) {

			global $wpdb; // you may not need this part. Try with and without it

			// delete transients that match the post type
			$sql = "DELETE FROM ".$wpdb->prefix."options WHERE `option_name` LIKE ('%_bzz/".get_post_type($post_id)."%')";

			$wpdb->query($sql );

			// if the saved post is found inside the post types array, reset the cache
			//delete_transient('buzzapicache/'.get_post_type($post_id));

			
		}

		/* GETTERS ------------------ */

		/* Get wordpress feed */
		function import($settings, $source, $nr_of_feeds, $lat = NULL, $long = NULL, $radius = 5, $startdate, $enddate,$offset=NULL, $types = array(), $regio = array(), $afdeling=NULL,$naam=NULL,$sort=NULL )
		{	

			// create a cache handle which contains the arguments send to the function
			//$cacheHandle = 'buzzapicache/'.$source.'/'.sha1(strtolower( serialize(func_get_args()) ));
			$cacheHandle = 'bzz/'.$source.'/'.substr(sha1(var_export(func_get_args(), true)),0,15);

			// // check if there is a cache, but also ignore the cache when the 'no_cache' param is set
			if ($this->has_cache($cacheHandle) && !isset($_GET['no_cache'])) {
				$this->fetch_cache($cacheHandle);
				return;
			}

			if ($source=='kalender'){

				$args     = array(

					'post_type' =>	$source,
					'meta_key'	=>	'begin_tijd',
					'orderby'	=>	'meta_value',
					'order'		=>	'ASC',

					'meta_query' => array(
						array(
							'key' => 'begin_tijd',
							'compare' => '>=',
							'value'   => $startdate,
							),

						array(
							'key'     => 'eind_tijd',
							'compare' => '<=',
							'value'   => $enddate,
							),
						),
					);

				// the used acf plugin was faulty, replaced with the default datepicker
				// therefore the date needed to be converted from timestamp to yymmdd
				
				// $args     = array(

				// 	'post_type' =>	$source,
				// 	'meta_key'	=>	'begin_tijd',
				// 	'orderby'	=>	'meta_value',
				// 	'order'		=>	'ASC',

				// 	'meta_query' => array(
				// 		array(
				// 			'key' => 'begin_tijd',
				// 			'compare' => '>=',
				// 			'value'   => strftime("%Y%m%d",$startdate),
				// 			),

				// 		array(
				// 			'key'     => 'eind_tijd',
				// 			'compare' => '<=',
				// 			'value'   => strftime("%Y%m%d",$enddate),
				// 			),
				// 		),
				// 	);
			}

			else if($source=='jaarverslagdetail'){
				$args     = array(

					'post_type' =>	'jaarverslag',
					'meta_key'	=>	'edition',
					'orderby'	=>	'meta_value',
					'order'		=>	'ASC',
					'meta_query' => array(
						'key' => 'edition',
						'compare' => 'LIKE',
						'value'   => $startdate
						),
					);
			}

			else if($source=='wieiswie'){
				$args     = array(

					'post_type' =>	$source,
					'orderby'	=>	'post_title',
					'order'		=>	$sort
					);

				if(!empty($afdeling)){
					$add = array(
						'meta_key' => 'afdeling',
						'meta_query' => array(
							'key' => 'afdeling',
							'compare' => 'LIKE',
							'value'   => $afdeling,
							),);

					$args = array_merge($args,$add);

				}

				if(!empty($naam)){
					$add = array(
						'search_name' => $naam
						);

					$args = array_merge($args,$add);

				}

				if(!empty($regio)){
					$add = array(
						'meta_key' => 'regio',
						'meta_query' => array(
							'key' => 'regio',
							'compare' => 'IN',
							'value'   => $regio,
							),);

					$args = array_merge($args,$add);

				}

			}

			else if($source=='intro' || $source=='boekenkast' || $source=='huurdersraad' || $source=='koopenwoon' || $source=='werkenbij' )
			{
				$args =  array(
					'post_type' =>	'home'

					);

				$this->total = 1;
			}

			else{

				$args     = array(
					'post_type' =>	$source,
					'orderby' => 'menu_order',
					'order' => 'ASC'
					);
			}

			// set the posts per page
			$args['posts_per_page'] = -1; // set an infinite nr of posts per page

			$query = new WP_Query($args);
			//print_r($args);

			$this->total = (int)$query->found_posts;

			// loop trough the posts

			if( $query->have_posts() )
			{



				if ($source=='boekenkast' || $source=='huurdersraad' || $source=='koopenwoon' || $source=='werkenbij' ){

					$feed_object = new BuzzFeedWordpress_object();

					switch ($source) {
						case 'boekenkast':

						$page = get_page_by_title('boekenkast','OBJECT','home');
						$photo_url = get_field('img',$page->ID);
						$feed_object->set_media($photo_url['sizes']['large-thumb']);
						$feed_object->set_url('http://maatschappelijkekaart.wonenlimburg.nl/project/het-bruishuis/');//http://wonenlimburg.nl/');
						$feed_object->set_type('boekenkast');
						$feed_object->set_feed_id('1111');
						$feed_object->set_timestamp(time());
						$feed_object->set_date(time());
						$feed_object->set_title('boekenkast');

						$feed_object->set_text('boekenkast');

						$this->feeds[] = $feed_object;
						break;

						case 'huurdersraad':

						$page = get_page_by_title('huurdersraad','OBJECT','home');
						$photo_url = get_field('img',$page->ID);
						$feed_object->set_media($photo_url['sizes']['large-thumb']);
						$feed_object->set_url('http://huurdersraad-wl.nl/kj3/');//http://www.hv-nl.nl/');
						$feed_object->set_type('huurdersraad');
						$feed_object->set_feed_id('2222');
						$feed_object->set_timestamp(time());
						$feed_object->set_date(time());
						$feed_object->set_title('huurdersraad');

						$feed_object->set_text('huurdersraad');

						$this->feeds[] = $feed_object;
						break;

						case 'koopenwoon':

						$page = get_page_by_title('koopenwoon','OBJECT','home');
						$photo_url = get_field('img',$page->ID);
						$feed_object->set_media($photo_url['sizes']['large-thumb']);
						$feed_object->set_url('http://www.koop-en-woon.nl/');
						$feed_object->set_type('koopenwoon');
						$feed_object->set_feed_id('3333');
						$feed_object->set_timestamp(time());
						$feed_object->set_date(time());
						$feed_object->set_title('koopenwoon');

						$feed_object->set_text('koopenwoon');

						$this->feeds[] = $feed_object;
						break;

						case 'werkenbij':

						$page = get_page_by_title('werkenbij','OBJECT','home');
						$photo_url = get_field('img',$page->ID);
						$feed_object->set_media($photo_url['sizes']['large-thumb']);
						$feed_object->set_url('http://werkenbijwonenlimburg.nl/');
						$feed_object->set_type('werkenbij');
						$feed_object->set_feed_id('4444');
						$feed_object->set_timestamp(time());
						$feed_object->set_date(time());
						$feed_object->set_title('werkenbij');

						$feed_object->set_text('werkenbij');

						$this->feeds[] = $feed_object;
						break;

						default:
						# code...
						break;
					}

				} else if( $source=='intro' || $source=='kalender' || $source=='meldingen' || $source=='wieiswie' || $source=='brochure' || $source=='buurtvan' || $source=='bericht' || $source=='jaarverslag' ){
					foreach($query->posts as $post)
					{
						//print_r($post);
				    // get the post data
						$post_id    = $post->ID;

						$post_url   = get_field('url', $post_id);

					// Compose object
						$feed_object = new BuzzFeedWordpress_object();
						$post_date = strtotime($post->post_date);

						$photo_url = wp_get_attachment_url(get_post_meta($post_id, 'photo', true));

						$feed_object->set_url($post_url);
						$feed_object->set_type($post->post_type);

						$the_content = apply_filters('the_content', $post->post_content);


						if($source=="kalender" || $source=="meldingen"){

							$post_date = get_field('begin_tijd', $post_id, false);
							$feed_object->set_starttimedate(get_field('begin_tijd', $post_id, false));
							$feed_object->set_endtimedate(get_field('eind_tijd', $post_id, false));

							if($source=="kalender"){

								$feed_object->set_contact_mail(get_post_meta($post_id, 'contact_email', true));
								$feed_object->set_contact_tel(get_post_meta($post_id, 'contact_tel', true));
								$feed_object->set_contact_url(str_replace('http://www.','',get_post_meta($post_id, 'contact_website', true)));

								$location = get_post_meta($post_id, 'locatie_map', true);
								$feed_object->set_locatie($location['address']);
								$feed_object->set_lat($location['lat']);
								$feed_object->set_long($location['lng']);
							}

							else if($source=="meldingen"){

								$post->post_type='melding';
								$post->post_content = get_field('beschrijving',$post_id);
								$feed_object->set_colour_code(get_post_meta($post_id, 'status', true));
							}
						}


						else if($source=="wieiswie"){

							$formatted_telnr = get_post_meta($post_id, 'persoon_telnr', true);
							$length = strlen($formatted_telnr);

							if($length == 10) {
								$formatted_telnr = preg_replace("/^1?(\d{3})(\d{4})(\d{3})$/", "$1 - $2 $3", $formatted_telnr);
							}

							$feed_object->set_wieiswie_mail(get_post_meta($post_id, 'persoon_email', true));
						//$feed_object->set_wieiswie_tel(get_post_meta($post_id, 'persoon_telnr', true));
							$feed_object->set_wieiswie_tel($formatted_telnr);
							$feed_object->set_wieiswie_regio(get_post_meta($post_id, 'regio', true));
							$feed_object->set_wieiswie_afdeling(get_post_meta($post_id, 'afdeling', true));

							// changed the system to use thumbs
							$media = get_field('photo',$post_id);
							$photo_url = false;
							$photo_url_large = false;
							
							if (isset($media['sizes'])) {
								$photo_url = $media['sizes']['team-landscape-thumb'];
								$photo_url_large = $media['sizes']['team-landscape'];
							}
							
							$feed_object->set_media_large($photo_url_large);
						}



						else if($source=="brochure" || $source=="buurtvan"){
							$photo_url = wp_get_attachment_url(get_post_meta($post_id, 'cover_img', true));
							$pdf_link = wp_get_attachment_url(get_post_meta($post_id, 'pdf_link', true));
							$feed_object->set_pdf_link($pdf_link);

							if ($source=="buurtvan"){
								$feed_object->set_colour_code(get_post_meta($post_id, 'colourcode', true));
								$feed_object->set_quote(get_post_meta($post_id, 'quote', true));

							}
						}

						else if ($source=="jaarverslag"){

							$post->post_type='jaarverslag';
							$page = get_page_by_title('jaarverslag','OBJECT','home');
							$feed_object->set_starttimedate(get_post_meta($post_id, 'edition', true));
							$photo_url = get_field('img',$page->ID);
							$photo_url = $photo_url['sizes']['large-thumb'];
						}

						else if($source=="bericht")
						{

							$photo_url = wp_get_attachment_url(get_post_meta($post_id, 'bericht_img', true));
							$post->post_content = get_field('bericht_text',$post_id);
							$post->post_type = get_field('bericht_type',$post_id)."bericht";

						}

						else if($source=="intro")
						{
							$feed_object->set_type('intro');


						}
						$feed_object->set_media($photo_url);

						$feed_object->set_feed_id($post_id);
						$feed_object->set_timestamp($post_date);
						$feed_object->set_date($post_date);
						$feed_object->set_title($post->post_title);

						$feed_object->set_text($the_content);

						$this->feeds[] = $feed_object;
					//	print_r($feed_object);
					}
				}

				elseif($source=='jaarverslagdetail'){

					$post = $query->posts[0];
					$post_id = $post->ID;

					$post_date = strtotime($post->post_date);

					$paginas = get_field('layout',$post_id);

					$this->total	=	count($paginas);

					foreach($paginas as $pagina){

						$feed_object = new BuzzFeedWordpress_object();

						if($pagina['acf_fc_layout']=='layout_text'){

							$post_type = 'text';
							$photo_url='';
							$post_text= $pagina['text'];
						}

						elseif($pagina['acf_fc_layout']=='layout_img'){

							$post_type = 'img';
							$photo_url= $pagina['img']['sizes']['large'];
							$post_text= '';
						}

						$feed_object->set_media($photo_url);

						$feed_object->set_type($post_type);
						$feed_object->set_feed_id($post_id);
						$feed_object->set_timestamp($post_date);
						$feed_object->set_date($post_date);
						$feed_object->set_title($post->post_title);

						$feed_object->set_text($post_text);

						$this->feeds[] = $feed_object;

					}

				}

			}

			//Set the cache
			$this->set_cache($cacheHandle, 60*60);

	

			$this_json = json_encode($this);

		}

	}
}






/* ------------------------------------------------------------------------ */

if( class_exists('BuzzFeed') ) {


	class BuzzFeedWordpress extends BuzzFeed {


		function __construct()
		{


			// Basic
			$this->slug  = "wordpress";
			$this->label = "wordpress";
			$currenttime = strtotime('today');

			// Settings
			$this->settings = array(
				"nr_of_feeds"    => 20,
				"default_source" => "buurtvan",
				"startdate" => $currenttime,
				"enddate" => 32503680000,
				"offset" => 0,
				"types" => NULL,
				"regio" => null,
				"afdeling" => null,
				"naam" => NULL,
				"sort" => "ASC",
				'wordpress'        => array(),
				);


			$this->feeds_collection = new BuzzFeedWordpress_collection();
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

			//echo "-----------------------------------------------------------------------";
			$nr_of_feeds = $this->settings['nr_of_feeds'];
			$source      = $this->settings['default_source'];
			$startdate   = $this->settings['startdate'];
			$enddate     = $this->settings['enddate'];
			$offset     = $this->settings['offset'];
			$types		= $this->settings['types'];
			$regio		= $this->settings['regio'];
			$afdeling	= $this->settings['afdeling'];
			$naam		= $this->settings['naam'];
			$sort		= $this->settings['sort'];


			// overwrite defaults
			if (isset($arguments['nr_of_feeds'])) { $nr_of_feeds = $arguments['nr_of_feeds']; }
			if (isset($arguments['source'])) { $source = $arguments['source']; }
			if (isset($arguments['startdate'])) { $startdate = $arguments['startdate']; }
			if (isset($arguments['enddate'])) { $enddate = $arguments['enddate']; }
			if (isset($arguments['offset'])) { $offset = $arguments['offset']; }
			if (isset($arguments['types'])) { $types = $arguments['types']; $types = explode(",",$types); }
			if (isset($arguments['regio'])) { $regio = $arguments['regio']; $regio = explode(",",$regio); }
			if (isset($arguments['afdeling'])) { $afdeling = $arguments['afdeling']; }
			if (isset($arguments['naam'])) { $naam = $arguments['naam']; }
			if (isset($arguments['sort'])) { $sort = $arguments['sort']; }
			
			// reset the feed before import to clean up the content of the feeds array.
			// somehow this was a problem resulting in many 'melding' type of posts in the feed
			$this->feeds_collection->feeds = []; 

			$this->feeds_collection->import($this->settings['wordpress'],$source, $nr_of_feeds,0,0,5,$startdate,$enddate,$offset,$types, $regio, $afdeling,$naam,$sort);

			$this->feeds_collection->feeds = array_slice($this->feeds_collection->feeds, $offset, $nr_of_feeds);
			$this->feeds_collection->feeds = array_values($this->feeds_collection->feeds);

			// Return status and response
			return array("status_code" => 1,"totalamount" => $this->feeds_collection->total , "response" => $this->feeds_collection->feeds);


		}


	}

	new BuzzFeedWordpress();

}


?>
