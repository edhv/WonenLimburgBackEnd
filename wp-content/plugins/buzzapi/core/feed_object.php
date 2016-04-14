<?php


class BuzzFeed_object {

	public $type;
	public $feed_id;
	public $timestamp;
	public $date;
	public $title;
	public $text;
	public $media;
	public $url;
	
	public $lat;
	public $long;
	
	public $tags;
	
	public $starttimedate;
	public $endtimedate;
	
	public $locatie;
	public $contact_mail;
	public $contact_tel;
	public $contact_url;
	public $wieiswie_mail;
	public $wieiswie_tel;
	public $wieiswie_regio ;
	public $wieiswie_afdeling;
	public $pdf_link;
	public $quote;
	public $colour_code;
	
	

	function __construct() 
	{

	}


	// Setters
	function set_type($value = false) {
		$this->type = $value;
	}

	function set_feed_id($value = false) {
		$this->feed_id = $value;
	}

	function set_timestamp($value = false) {
		$this->timestamp = $value;
	}

	function set_date($value = false) {
		$this->date = $value;
	}

	function set_title($value = false) {
		$this->title = $value;	
	}

	function set_text($value = false) {
		$this->text = $value;
		$this->fetch_tags();
	}

	function set_media($value = false) {
		$this->media = $value;
	}

	function set_url ($value = false) {
		$this->url = $value;
	}
	
	
	function set_lat ($value = false) {
		$this->lat = $value;	
	}
	
	function set_long ($value = false) {
		$this->long = $value;	
	}
	
	
	function set_tags($value = false) {
		$this->tags = $value;	
	}

	function set_starttimedate($value = false) {
		$this->starttimedate = $value;
	}

	function set_endtimedate($value = false) {
		$this->endtimedate = $value;
	}

	function set_locatie($value = false) {
		$this->locatie = $value;
	}

	function set_contact_mail($value = false) {
		$this->contact_mail = $value;
	}

	function set_contact_tel($value = false) {
		$this->contact_tel = $value;
	}

	function set_contact_url($value = false) {
		$this->contact_url = $value;
	}
	
	function set_wieiswie_mail($value = false) {
		$this->wieiswie_mail = $value;
	}

	function set_wieiswie_tel($value = false) {
		$this->wieiswie_tel = $value;
	}

	function set_wieiswie_regio($value = false) {
		$this->wieiswie_regio = $value;
	}

	function set_wieiswie_afdeling($value = false) {
		$this->wieiswie_afdeling = $value;
	}

	function set_quote($value = false) {
		$this->quote = $value;
	}

	function set_pdf_link($value = false) {
		$this->pdf_link = $value;
	}

	function set_colour_code($value = false) {
		$this->colour_code = $value;
	}
	


	// Helpers
	function fetch_tags()
	{
		$text = $this->text;	
		$tags = array();
		
		$has_tags = preg_match_all("/(#\w+)/", $text, $tags);
		if($has_tags)
		{
			$tags = $tags[0];
			$this->set_tags($tags);
		}
	}
	
	
	
	
	
	function normalize_date($timestamp) {
		return strftime("%d %B, %H:%M", $timestamp);
	}

}


?>