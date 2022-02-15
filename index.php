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

function dlinq_blog_maker(){
   $domain =     $current_network = get_network();
   //var_dump($current_network);
   $team = 'Team Shrimp Heads';
   $path = $current_network->domain . '/' . sanitize_title( $team ) . '/';
   $user_id = 13;
   $sites = get_sites(array( 'fields' => 'ids', 'path' => '/team-shrimp-heads/'))[0];
   var_dump($sites);
   if($sites > 0){
      $blog_id = $sites;
      add_user_to_blog($blog_id, 11, 'administrator');
   } else {
      $args = array(
      'domain' => 'multsitetwo.local',
      'path' => sanitize_title($team),
      // 'network_id' => '',
      // 'registered' => '',
      'user_id' => $user_id,
      'title' => $team,      
   );
   $new_site = wp_insert_site($args);
   return 'foo';
   }
   
}

add_shortcode( 'make-site', 'dlinq_blog_maker' );


/**
 * Retrieves a sites ID given its (subdomain or directory) slug.
 *
 * @since MU
 * @since 4.7.0 Converted to use get_sites().
 *
 * @param string $slug A site's slug.
 * @return int|null The site ID, or null if no site is found for the given slug.
 */
function dlinq_get_id_from_blogname($slug){
    $current_network = get_network();
    $slug = trim($slug, '/');
    if (is_subdomain_install()) {
        $domain = $slug . '.' . preg_replace('|^www\\.|', '', $current_network->domain);
        $path = $current_network->path;
    } else {
        $domain = $current_network->domain;
        $path = $current_network->path . $slug . '/';
    }
    $site_ids = get_sites(array('number' => 1, 'fields' => 'ids', 'domain' => $domain, 'path' => $path));

    if (empty($site_ids)) {
        return null;
    }
    return array_shift($site_ids);
}

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
