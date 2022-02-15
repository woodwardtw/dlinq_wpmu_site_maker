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


function dlinq_team_added( $form, $entry_id, $original_entry){
   var_dump($original_entry);
}

add_action( 'gform_after_update_entry_4', 'dlinq_team_added', 10, 3 );

//after post creation write the created post ID to the form
add_action( 'gform_after_create_post', 'dlinq_save_post_id', 10, 3 );

function dlinq_save_post_id( $post_id, $entry, $form){
   //23
   rgar( $entry, '23' ) = $post_id;
}

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

function dlinq_make_new_tracking_post($title, $site_id){
   $args = array(
      'post_title' => $title,
      'post_category' => 'Spring 2022',
      'post_content' => 'foo',
   );
   wp_insert_post($args);
}


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

//add custom taxonomies

add_action( 'init', 'create_section_taxonomies', 0 );
function create_section_taxonomies()
{
  // Add new taxonomy, NOT hierarchical (like tags)
  $labels = array(
    'name' => _x( 'Sections', 'taxonomy general name' ),
    'singular_name' => _x( 'section', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Sections' ),
    'popular_items' => __( 'Popular Sections' ),
    'all_items' => __( 'All Sections' ),
    'parent_item' => null,
    'parent_item_colon' => null,
    'edit_item' => __( 'Edit Sections' ),
    'update_item' => __( 'Update section' ),
    'add_new_item' => __( 'Add New section' ),
    'new_item_name' => __( 'New section' ),
    'add_or_remove_items' => __( 'Add or remove Sections' ),
    'choose_from_most_used' => __( 'Choose from the most used Sections' ),
    'menu_name' => __( 'Sections' ),
  );

//registers taxonomy specific post types - default is just post
  register_taxonomy('Sections',array('post'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'update_count_callback' => '_update_post_term_count',
    'query_var' => true,
    'rewrite' => array( 'slug' => 'section' ),
    'show_in_rest'          => true,
    'rest_base'             => 'section',
    'rest_controller_class' => 'WP_REST_Terms_Controller',
    'show_in_nav_menus' => true,    
  ));
}


add_action( 'init', 'create_team_taxonomies', 0 );
function create_team_taxonomies()
{
  // Add new taxonomy, NOT hierarchical (like tags)
  $labels = array(
    'name' => _x( 'Teams', 'taxonomy general name' ),
    'singular_name' => _x( 'team', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Teams' ),
    'popular_items' => __( 'Popular Teams' ),
    'all_items' => __( 'All Teams' ),
    'parent_item' => null,
    'parent_item_colon' => null,
    'edit_item' => __( 'Edit Teams' ),
    'update_item' => __( 'Update team' ),
    'add_new_item' => __( 'Add New team' ),
    'new_item_name' => __( 'New team' ),
    'add_or_remove_items' => __( 'Add or remove Teams' ),
    'choose_from_most_used' => __( 'Choose from the most used Teams' ),
    'menu_name' => __( 'Teams' ),
  );

//registers taxonomy specific post types - default is just post
  register_taxonomy('Teams',array('post'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'update_count_callback' => '_update_post_term_count',
    'query_var' => true,
    'rewrite' => array( 'slug' => 'team' ),
    'show_in_rest'          => true,
    'rest_base'             => 'team',
    'rest_controller_class' => 'WP_REST_Terms_Controller',
    'show_in_nav_menus' => true,    
  ));
}
