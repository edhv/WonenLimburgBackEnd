<?php 

function wpbootstrap_scripts_with_jquery()
{
	// Register the script like this for a theme:
	wp_register_script( 'custom-script', get_template_directory_uri() . '/bootstrap/js/bootstrap.js', array( 'jquery' ) );
	// For either a plugin or a theme, you can then enqueue the script:
	wp_enqueue_script( 'custom-script' );
}
add_action( 'wp_enqueue_scripts', 'wpbootstrap_scripts_with_jquery' );

function codex_custom_init() {

 	$args = array( 
 		'label' => 'Wie is wie',
 		'public' => true,
    'menu_icon'=>'dashicons-groups',
 		'supports' => array(
 			'title',
 			'editor',
 			'excerpt',
 			'thumbnail',
 			'revisions'
 			),
 		'hierarchical' => true,
 		'menu_position' => 5
 		);
 	register_post_type( 'wieiswie', $args );

    $args = array( 
    'label' => 'Brochures',
    'public' => true,
    'menu_icon'=>'dashicons-format-aside',
    'supports' => array(
      'title',
      'editor',
      'excerpt',
      'thumbnail',
      'revisions'
      ),
    'hierarchical' => true,
    'menu_position' => 5
    );
  register_post_type( 'brochure', $args );


    $args = array( 
    'label' => 'In de Buurt van',
    'public' => true,
    'menu_icon'=>'dashicons-media-default',
    'supports' => array(
      'title',
      'editor',
      'excerpt',
      'thumbnail',
      'revisions'
      ),
    'hierarchical' => true,
    'menu_position' => 5
    );
  register_post_type( 'buurtvan', $args );

    $args = array( 
    'label' => 'Kalender',
    'public' => true,
    'menu_icon'=>'dashicons-calendar-alt',
    'supports' => array(
      'title',
      'editor',
      'excerpt',
      'thumbnail',
      'revisions'
      ),
    'hierarchical' => true,
    'menu_position' => 5
    );
  register_post_type( 'kalender', $args );

    $args = array( 
    'label' => 'Meldingen',
    'public' => true,
    'menu_icon'=>'dashicons-megaphone',
    'supports' => array(
      'title',
      'editor',
      'excerpt',
      'thumbnail',
      'revisions'
      ),
    'hierarchical' => true,
    'menu_position' => 5
    );
  register_post_type( 'meldingen', $args );

   	flush_rewrite_rules( false );
 }

 add_action( 'init', 'codex_custom_init' );
 add_image_size( 'overview-thumb', 400, 300, true );
 add_image_size( 'large-thumb', 705, 440, true );
  //466 pixels wide (and unlimited height)
      
     
?>
