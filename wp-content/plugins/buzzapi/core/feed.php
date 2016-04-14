<?php 


class BuzzFeed {



	public $settings;
	public $label;
	public $slug;
	public $feed_object;
	public $feed_collection;


	function __construct()
	{


		add_action("buzz/register_feed", array($this,"register_feed"),10,1);

		$this->init();

	}


	function init() {
		$this->register_filters();
	}


	function register_filters() {

		// Register the filters needed for data retrieval
		// ....
	}


	function register_feed() {

		// Get the api
		$api = buzz_api();

		// Register this feed
		$api->feeds[$this->slug] = $this;

	}


	function get_feeds($arguments) {

	}




}



?>