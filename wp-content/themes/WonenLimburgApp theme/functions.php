<?php 

function wpbootstrap_scripts_with_jquery()
{
	// Register the script like this for a theme:
	wp_register_script( 'custom-script', get_template_directory_uri() . '/bootstrap/js/bootstrap.js', array( 'jquery' ) );
	// For either a plugin or a theme, you can then enqueue the script:
	wp_enqueue_script( 'custom-script' );
}

add_action( 'wp_enqueue_scripts', 'wpbootstrap_scripts_with_jquery' );

add_filter( 'posts_where', 'wpse18703_posts_where', 10, 2 );
add_filter( 'posts_where', 'wpse18705_posts_where', 10, 2 );

function wpse18703_posts_where( $where, &$wp_query )
{
    global $wpdb;

    if ( $wpse18703_title = $wp_query->get( 'search_post_title' ) ) {
                
      $i = count($wpse18703_title)-1;
      $where .= ' AND (' . $wpdb->posts . '.post_title LIKE \''; 
      
      for ($y = 0; $y <= $i; $y++) {
        
        if($y >=1){
        $where .= ' OR '.$wpdb->posts . '.post_title LIKE \'';}

       $where .= esc_sql( $wpdb->esc_like( $wpse18703_title[$y])). '\' ';
      }
      $where .= ')';
   
    }
    return $where;
}

function wpse18705_posts_where( $where, &$wp_query )
{
    global $wpdb;

    if ( $wpse18703_title = $wp_query->get( 'search_name' ) ) {
                
      $i = count($wpse18703_title)-1;
      $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \''; 
      $where .= '%'.esc_sql( $wpdb->esc_like( $wpse18703_title)). '%\' ';
    }

    return $where;
}

function post_remove () 
{ 
   remove_menu_page('edit.php');
   remove_menu_page('edit-comments.php');
   remove_menu_page('edit.php?post_type=page');
} 

function codex_custom_init() {

$args = array( 
    'label' => 'Home',
    'public' => true,
    'menu_icon'=>'dashicons-admin-home',
    'supports' => array(
      'title',
      'editor',
      'excerpt',
      'thumbnail',
      'revisions',
      ),
    'hierarchical' => true,
    'menu_position' => 5
    );
  register_post_type( 'home', $args );

$args = array( 
    'label' => 'Berichten',
    'public' => true,
    'menu_icon'=>'dashicons-welcome-write-blog',
    'supports' => array(
      'title',
      'editor',
      'excerpt',
      'thumbnail',
      'revisions'
      ),
    'hierarchical' => true,
    'menu_position' => 6
    );
  register_post_type( 'bericht', $args );

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
 		'menu_position' => 7
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
    'menu_position' => 8
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
    'menu_position' => 9
    );
  register_post_type( 'buurtvan', $args );

   $args = array( 
    'label' => 'Jaarverslagen',
    'public' => true,
    'menu_icon'=>'dashicons-chart-bar',
    'supports' => array(
      'title',
      'editor',
      'excerpt',
      'thumbnail',
      'revisions'
      ),
    'hierarchical' => true,
    'menu_position' => 10
    );
  register_post_type( 'jaarverslag', $args );

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
    'menu_position' => 11
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
    'menu_position' => 12
);
  register_post_type( 'meldingen', $args );

   	flush_rewrite_rules( false );
 }

 

 add_action('admin_menu', 'post_remove');
 add_action( 'init', 'codex_custom_init' );
 add_image_size( 'overview-thumb', 400, 300, true );
 add_image_size( 'large-thumb', 705, 440, true );
 add_image_size( 'portrait-thumb', 300, 9999 );
 add_image_size( 'team-landscape-thumb', 500, 9999 );
 add_image_size( 'team-landscape', 1000, 9999 );

  //466 pixels wide (and unlimited height)
      
     
?>
