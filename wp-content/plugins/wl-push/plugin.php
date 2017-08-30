<?php
/**
 * Plugin Name: Wonen Limburg News Push
 * Plugin URI:
 * Description:
 * Version: 0.1
 * Author: Jeroen Braspenning
 * Author URI:
 * License:
 */



if (!class_exists('WL_Push'))
{

	/**
	*
	*/
	class WL_Push
	{


		/**
		 * 	get_instance
		 *
		 * 	returns the singleton instance of this class
		 *  @return Singleton instance
		 *
		 */
	    public static function get_instance()
	    {
	        static $instance = null;
	        if (null === $instance) {
	            $instance = new static();
	        }

	        return $instance;
	    }


		/**
		 * 	constructor
		 *
		 */

		function __construct()
		{

			// settings
			$this->settings = array(
				'onesignal_url'=>'https://onesignal.com/api/v1/notifications',
				'onesignal_key'=>'ZGYwMzBhMzUtMWE4OC00NzU2LWIxYzItOTc4ZjJmYTQ2ODdl',
				'onesignal_id'=>'a6e3f7ad-4ec6-42d3-a9f9-699bbabfee65',
				'segments'=>array('All'),
				'test_users'=>array(
					"5adb310a-27a0-4953-bb07-d01f6874dce1", // jeroen ipad
					"3f614866-9e50-4cb5-a57c-25a994235b6d", // jeroen android emulator
          "b183e905-0b59-4fa8-a9ca-9e2b2c96101b",  // EDHV iPad
          "68a58235-609f-44aa-9387-bf3acea4206d"// EDHV iPad
				)
			);

			// actions
			add_action('init', array($this, 'init'), 1);
			add_action( 'add_meta_boxes', array($this, 'wpdocs_register_meta_boxes'));
			add_action( 'admin_enqueue_scripts', array($this, 'register_plugin_scripts') );
			add_action( 'wp_ajax_send_push', array($this, 'send_push_message'), 1 );

		}


		/**
		 * 	init
		 *
		 * 	Initialize the main parts of the projects plugin
		 *
		 */

		function init()
		{
		}

		function send_push_message($data) {

			global $post;

			$return = array();

			$titel = $_POST['title'];
			$message = $_POST['message'];
			// onesignal paramters
			$parameters = array(
					'app_id' => $this->settings['onesignal_id'],
					'data' => array('id'=>$_POST['post_id']),
					);

				// define the target
			if ($_POST['target'] == 'all') {
				$parameters['included_segments'] = $this->settings['segments'];
				$parameters['headings'] = array('en'=>$_POST['title']);
				$parameters['contents'] = array('en'=>$_POST['message']);
			} else if ($_POST['target'] == 'test') {
				$parameters['include_player_ids'] = $this->settings['test_users'];
				$parameters['headings'] = array('en'=>'[ test ] '.$_POST['title']);
				$parameters['contents'] = array('en'=>'[ test ] '.$_POST['message']);
			} else {
				// if no target is given return error
				$return = array(
					'status'=>0
				);
				echo json_encode($return);
				wp_die();
			}

			// prepare the data string
			$data_string = json_encode($parameters);

	    // setup curl
			$ch = curl_init($this->settings['onesignal_url']);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			    'Content-Type: application/json',
			    'authorization: Basic '.$this->settings['onesignal_key'],
			    'cache-control: no-cache',
			    'Content-Length: ' . strlen($data_string))
			);

			$result = curl_exec($ch);
			$err = curl_error($ch);
			curl_close($ch);

			if ($err) {
				$return = array('status'=>0);
			} else {
				$return = array('status'=>1);
			}

			echo json_encode($return);

			wp_die();

		}


		/**
		 * Register meta box(es).
		 */
		function wpdocs_register_meta_boxes() {
		    add_meta_box( 'meta-box-id', 'Nieuws Push Bericht', array($this, 'wpdocs_my_display_callback'), 'bericht','side' );
		}

		/**
		 * Meta box display callback.
		 *
		 * @param WP_Post $post Current post object.
		 */
		function wpdocs_my_display_callback( $post ) {


			$html = '<div class="js-push-feedback"></div><br/><div class="js-push-fields">';
			$html .= '
			<div class="acf-field acf-field-select " >
				<div class="acf-label">
					<label for="">Versturen aan</label>
				</div>
				<div class="acf-input">
					<select class="js-push-target">
						<option value="test" selected="selected">Test gebruikers</option>
						<option value="all">Alle gebruikers</option>
					</select>
				</div>
			</div>
			';
			$html .= '<div class="acf-field">
				<div class="acf-label">
					<label for="">Titel</label>
				</div>
				<div class="acf-input">
					<div class="acf-input-wrap"><input type="text" class="js-push-title" placeholder="Titel" value="'.$post->post_title.'"></div>
				</div>
			</div>';

			$html .= '<div class="acf-field">
				<div class="acf-label">
					<label for="">Bericht</label>
				</div>
				<div class="acf-input">
					<div class="acf-input-wrap"><textarea rows="5" class="js-push-message" placeholder="Bericht"></textarea>
				</div>
			</div><br/>';
			$html .= '<a class="button button-primary button-large js-send-push">Bericht verzenden</a><span class="spinner js-push-spinner"></span>';
			$html .= '</div>';


			echo $html;

			return $html;
		}


		function register_plugin_scripts($hook) {
			global $post;


			if ( 'post.php' != $hook ) {
				return;
			}

			$this->settings['post_id'] = $post->ID;
			$this->settings['post_title'] = $post->post_title;

			wp_enqueue_script( 'push_script',  plugins_url('wl-push') . '/js/plugin.js' );
			wp_localize_script( 'push_script', 'wl_push_settings', $this->settings);
		}


	}


	/**
	 * 	wl_push
	 *
	 * 	Main function which ensures that wl_push is initiated only once
	 *  Also this allows to call wl_push(); like a global variable everywhere
	 *
	 *  @return Object
	 */

	function wl_push()
	{
		global $wl_push;

		if(!isset($wl_push))
		{
			$wl_push = WL_Push::get_instance();
		}

		return $wl_push;
	}


	// initialize
	wl_push();


} // class_exists check

?>
