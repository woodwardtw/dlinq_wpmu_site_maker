<?php 
/*
Plugin Name: DLINQ WPMU Site Maker
Plugin URI:  https://github.com/
Description: Builds sites based on a set of things in Gravity Forms
Version:     1.0
Author:      DLINQ
Author URI:  https://dlinq.middcreate.net
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: my-toolset

*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


// add_action('wp_enqueue_scripts', 'prefix_load_scripts');

// function prefix_load_scripts() {                           
//     $deps = array('jquery');
//     $version= '1.0'; 
//     $in_footer = true;    
//     wp_enqueue_script('prefix-main-js', plugin_dir_url( __FILE__) . 'js/prefix-main.js', $deps, $version, $in_footer); 
//     wp_enqueue_style( 'prefix-main-css', plugin_dir_url( __FILE__) . 'css/prefix-main.css');
// }

function blog_maker(){
   $domain = 'multisitetwo.local/';
   $path = 'bard';
   $title = 'The Bard is set';
   $user_id = 12;
   $options = array( 'public' => 1 );
   wpmu_create_blog($domain, $path, $title, $user_id , $options);
}

add_shortcode( 'make-site', 'blog_maker' );

//blog_maker();

//LOGGER -- like frogger but more useful

if ( ! function_exists('write_log')) {
   function write_log ( $log )  {
      if ( is_array( $log ) || is_object( $log ) ) {
         error_log( print_r( $log, true ) );
      } else {
         error_log( $log );
      }
   }
}

  //print("<pre>".print_r($a,true)."</pre>");
