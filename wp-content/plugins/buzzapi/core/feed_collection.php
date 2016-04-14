<?php


class BuzzFeed_collection {

	public $feeds;
	public $type;
	public $total;
	public $has_cache = FALSE;

	function __construct()
	{
		$this->feeds = array();
		
		//Fetch the cache
		$this->fetch_cache();
	}


	/* SETTERS ------------------ */
	function set_type($value) {
		$this->type = $value;
	}


	/* GETTERS ------------------ */
	function import($settings, $source, $nr_of_feeds, $lat = NULL, $long = NULL, $radius = 5, $startdate, $enddate, $offset=NULL, $regio = array(), $afdeling=NULL,$naam=NULL,$sort=NULL ) {
	}



	/* GLOBAL HELPERS ------------------ */



	private $v = 1;

	/* Cache the data */
	function fetch_cache($name =  '')
	{
		//Get the cache name
		$cache_name = ($name != '') ? $name : sha1(strtolower($_SERVER['REQUEST_URI'])).'-'.$this->v;
		
		//Return the transient
		$cache = get_transient($cache_name);
		$cache_data = $cache;


		if($cache !== FALSE)
		{
			$this->has_cache = TRUE;
			$this->feeds     = $cache_data[0];
			$this->total     = $cache_data[1];
		}
	}


	
	function set_cache($name = '', $time = 60)
	{
		$cache_name = ($name != '') ? $name : sha1(strtolower($_SERVER['REQUEST_URI'])).'-'.$this->v;

		//Serialize the data
		
		$data[0] = $this->feeds;
		$data[1] = $this->total;
		
		$data_encoded = $data;
		
		//Set the cache
		set_transient($cache_name, $data_encoded, ($time*60));
		
						
	}





	/* Sort feeds */ 
	function sort_feeds() {
		usort( $this->feeds, array($this, 'fn_feed_sort'));
	}


	/* Sorting function */
	function fn_feed_sort($a, $b) {

		// To sort this function needs to return 0, -1 or 1
		if(is_numeric($a) || is_numeric($b))
		{
			return 1;
		}

		// IF timestamps are equal
		if ($a->timestamp == $b->timestamp) {
			return 0;
		}

		// Sorts the oldest first
		return ( $a->timestamp < $b->timestamp) ? 1 : -1;
			
	}


}


?>