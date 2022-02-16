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
   $entry = GFAPI::get_entry( $entry_id );
   //var_dump($entry);
   $user_email = $entry['16'];
   //var_dump($user_email);
   $team = $entry['17'];
   $tag = $entry['19'];//semester
   $section = get_term_by('name', $entry['22'], 'sections');
   $team_slug = sanitize_title($team );   
   $args = array(
      'name'        => $team_slug,
      'post_type'   => 'post',
      'post_status' => 'publish',
      'numberposts' => 1
      );
   $project = get_posts($args);
   $team_cat_id = get_category_by_slug('team')->term_id;
   //var_dump($project);
   if(!$project){
      //create the index post
      $args = array(
         'post_title' => $team,
         'post_category' => array($team_cat_id),
         'post_status' => 'publish',
         'tags_input' => array($tag),
         'tax_input' => array(
            'section' => array($section),
         ),
      );
      wp_insert_post($args);
   }
   $user_id = dlinq_add_user($user_email);
   dlinq_blog_creation($team_slug, $user_id, $team);
}
var_dump(get_term_by('slug', 'a', 'Sections'));
var_dump(get_term_by('slug', 'a', 'Section'));
var_dump(get_term_by('slug', 'a', 'sections'));
var_dump(get_term_by('slug', 'a'));
var_dump(get_term_by( 'slug', 'uncategorized'));

add_action( 'gform_after_update_entry_1', 'dlinq_team_added', 10, 3 );
add_action( 'gform_after_update_entry_4', 'dlinq_team_added', 10, 3 );//DELETE THIS***********

function dlinq_add_user($email){
   if (get_user_by('email', $email)){
      $user = get_user_by('email', $email);
      $user_id = $user->ID;      
   } else {
      $chop = strpos($email,'@', 0);
      $username = substr($email, 0, $chop);
      $pw = wp_generate_password();
      //var_dump($username);
      $user_id = wp_create_user($username, $pw, $email);
   }
  
   return $user_id;
}


function dlinq_blog_creation($slug, $user_id, $team){
   if(get_sites(array( 'fields' => 'ids', 'path' => '/'. $slug . '/'))){
      //var_dump('existing site');
      $sites = get_sites(array( 'fields' => 'ids', 'path' => '/'. $slug . '/'))[0];
      $blog_id = $sites;
      $admin = add_user_to_blog($blog_id, $user_id, 'administrator');
      //var_dump($admin);
   } else {
      $current_network = get_network();
      $domain = $current_network->domain;
      $args = array(
      'domain' => $domain,
      'path' => sanitize_title($team),
      // 'network_id' => '',
      // 'registered' => '',
      'user_id' => $user_id,
      'title' => $team,      
      );
      $new_site = wp_insert_site($args);
   }
   
   return $new_site;
}

//Team posts 
add_filter( 'the_content', 'dlinq_associate_users', 1 );

function dlinq_associate_users($content){
   global $post;
   if(in_category('team', $post->ID)){
         $slug = $post->post_name;
         $site_id = get_sites(array( 'fields' => 'ids', 'path' => '/'. $slug . '/'))[0];
         $args = array(
               'blog_id' => $site_id,
            );
         //site information
         $current_blog_details = get_blog_details( array( 'blog_id' => $site_id ) );
         echo "<h2>Team Name</h2><a href='{$current_blog_details->siteurl}' class='team-link'>{$current_blog_details->blogname}</a>";

         //users from the other blog
         $users = get_users($args);
         //var_dump($users);
         if($users){
               echo "<h2>Team Members</h2><ol>";
            foreach($users as $user) {
               //var_dump($user);
               echo "<li>{$user->display_name}</li>";
            }
               echo "</ul>";
         }
   
   }
  
   return $content;
}

function dlinq_team_title_adjust( $title, $id ) {
   global $post;
    if ( in_category('team', $id ) ) {
         $slug = $post->post_name;
         $site_id = get_sites(array( 'fields' => 'ids', 'path' => '/'. $slug . '/'))[0];
         $args = array(
               'blog_id' => $site_id,
            );
         //site information
         $current_blog_details = get_blog_details( array( 'blog_id' => $site_id ) );
         $new_title = $current_blog_details->blogname;
        return $new_title; 
    }
 
    return $title;
}
add_filter( 'the_title', 'dlinq_team_title_adjust', 10, 2 );




//probably all not needed but might be useful somewhere else
//
//
//
//
//
//
//after post creation write the created post ID to the form
add_action( 'gform_advancedpostcreation_post_after_creation', 'dlinq_save_post_id', 10, 4 );

function dlinq_save_post_id( $post_id, $feed, $entry, $form){
    $entry['23'] = $post_id;
    // Save the update
    $updated = GFAPI::update_entry( $entry );

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
