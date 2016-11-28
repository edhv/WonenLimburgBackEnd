<?php

/*
Plugin Name: Buzzfeed Rss
Plugin URI:
Description: Get an RSS feed
Author: Jeroen Braspenning
Author URI: www.edhv.nl
Version: 1.0.2
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

			$items = array($rssArray['channel']['item']);

			// walk through the rss items
			foreach ($items as $index => $rssItem) {

				$feed_object = new BuzzFeedRss_object();

				$feed_object->set_type($source);
				$feed_object->set_feed_id($rssItem['guid']);
				$feed_object->set_timestamp(strtotime($rssItem['pubDate']));
				$feed_object->set_date(strtotime($rssItem['pubDate']));
				$feed_object->set_title($rssItem['title']);
				$feed_object->set_text($rssItem['description']);
				$feed_object->set_media("");





				//
				$fullContent = $this->scrapeUrlContents($rssItem['guid'], strtotime($rssItem['pubDate']));

				if ($fullContent['text']) {
					$feed_object->set_text($fullContent['text']);
				}

				if ($fullContent['img']) {
					$feed_object->set_media($fullContent['img']);
				}

				$this->feeds[] = $feed_object;

			}

			$this->total = count($items);

			//Set the cache
			$this->set_cache();

			$this_json = json_encode($this);

		}




		/**
		 * @param  [type] $url      url to scrape
		 * @param  [type] $feedDate last edited date if this changes from original date refetch
		 */
		function scrapeUrlContents($url, $pubDate) {

			$cacheHandle = "wlp/".sha1(strtolower($url.$pubDate));

			// check if this page has been scraped before
			$cache = get_transient($cacheHandle);
			if ($cache) {
				return $cache;
			}


			// start the scraping
			include_once('library/simplehtmldom/simple_html_dom.php');

			$returnObject = array(
				"img"=>false,
				"text"=>false
			);

			$articleImage = false;
			$articleText = false;

			// check if we already have a cached version of this url

			//$content = file_get_contents($url);
			$html = file_get_html($url);

			// image
			$domImages = $html->find('#contentblock img');
			if (isset($domImages[0])) {
				if (isset($domImages[0]->src)) {
					$articleImage = "http://www.wonenlimburg.nl".$domImages[0]->src;
				}
			}


			// remove all images
			foreach($html->find('#contentblock .document img') as $item) {
			    $item->outertext = '';
			}

			// simplify links
			foreach($html->find('#contentblock .document a') as $item) {
			    $item->outertext = $item->innertext;
			}

			$html->save();



			// filter the paragraphs and h3 components
			$domContent = $html->find('#contentblock .document p, #contentblock .document h5, #contentblock .document h4, #contentblock .document h3, #contentblock .document h2, ');

			foreach($domContent as $e) {

				// ignore if an element has only a spce
				if ($e->innertext === "&nbsp;" || $e->innertext === "") {
					continue;
				}

				// ignore if an element has the class intranet
				if (strpos($e->class, "intranet") !== FALSE) {
					continue;
				}

				$articleText .= "<".$e->tag.">".$e->innertext."</".$e->tag.">";


			}

			$returnObject = array(
				"img"=>$articleImage,
				"text"=>$articleText
			);

			// set cache for a year
			set_transient($cacheHandle, $returnObject, 60 * 60 * 24 * 365);


			return $returnObject;
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
