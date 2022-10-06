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

add_action('wp_enqueue_scripts', 'dlinq_ssr_load_scripts');

function dlinq_ssr_load_scripts() {                           
    $deps = array('jquery');
    $version= '1.0'; 
    $in_footer = true;    
    wp_enqueue_script('dlinq-sitemaker-js', plugin_dir_url( __FILE__) . 'js/dlinq_wpmu_site_maker.js', $deps, $version, $in_footer); 
}

function dlinq_team_added( $form, $entry_id, $original_entry){
   $entry = GFAPI::get_entry( $entry_id );
   //var_dump($entry);
   $user_email = $entry['16'];
   //var_dump($user_email);
   $team = $entry['17'];
   $tag = $entry['19'];//semester
   $section = get_term_by('name', $entry['22'], 'section')->term_id;
   //get_term_by('name', 'a', 'section')
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
   //dlinq_add_team_to_intro($team_slug);
   dlinq_add_team_to_posts($entry, $team);
}


function dlinq_add_featured_img($content){
   if(has_post_thumbnail()){
      $image = get_the_post_thumbnail(get_the_id(),'large');
      return $image . $content;
   }
   return $content;
 
}

add_filter( 'the_content', 'dlinq_add_featured_img', 1 );


// function dlinq_section_assigner( $post_id, $feed, $entry, $form ){
//       $section = $entry['22'];
//       wp_set_post_terms( $post_id, $section, 'section');
// }
// add_action( 'gform_advancedpostcreation_post_after_creation_1', 'dlinq_section_assigner', 10, 4 );
// add_action( 'gform_advancedpostcreation_post_after_creation_4', 'dlinq_section_assigner', 10, 4 );


function dlinq_add_team_to_posts($entry, $team){
   $try = gform_get_meta( $entry['id'], 'gravityformsadvancedpostcreation_post_id' );
   foreach($try as $post){
      $post_id = $post['post_id'];
      wp_set_post_terms( $post_id, $team, 'team');
   }
}

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
      $notification = wp_send_new_user_notifications( $user_id, 'both');
   }
   return $user_id;
}

/**
 * Custom register email
 */
add_filter( 'wp_new_user_notification_email', 'custom_wp_new_user_notification_email', 10, 3 );
function custom_wp_new_user_notification_email( $wp_new_user_notification_email, $user, $blogname ) {
 
   //  $user_login = stripslashes( $user->user_login );
   //  $user_email = stripslashes( $user->user_email );
   //  $login_url  = wp_login_url();
   //  $message  = __( 'Hi there,' ) . "/r/n/r/n";
   //  $message .= sprintf( __( "Welcome to %s! Here's how to log in:" ), get_option('blogname') ) . "/r/n/r/n";
   //  $message .= wp_login_url() . "/r/n";
   //  $message .= sprintf( __('Username: %s'), $user_login ) . "/r/n";
   //  $message .= sprintf( __('Email: %s'), $user_email ) . "/r/n";
   //  $message .= __( 'Password: The one you entered in the registration form. (For security reason, we save encripted password)' ) . "/r/n/r/n";
   //  $message .= sprintf( __('If you have any problems, please contact me at %s.'), get_option('admin_email') ) . "/r/n/r/n";
   //  $message .= __( 'bye!' );
 
    $wp_new_user_notification_email['subject'] = sprintf( '[%s] Your Middlebury TRLM Site Account', $blogname );
    //$wp_new_user_notification_email['headers'] = array('Content-Type: text/html; charset=UTF-8');
    //$wp_new_user_notification_email['message'] = $message;
 
    return $wp_new_user_notification_email;
}

//make the network site 
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
}

//Team posts 

//gets the users from the network site and display in the post body
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
         echo "<a href='{$current_blog_details->siteurl}' class='team-link'>{$current_blog_details->blogname}</a>";

         //users from the other blog
         $users = get_users($args);
         //var_dump($users);
         if($users){
               echo "<h2 class='team-members'>Team Members</h2><ol id='team-list'>";
            foreach($users as $user) {
               //var_dump($user);
               echo "<li>{$user->display_name}</li>";
            }
               echo "</ul>";
         }
   
   }
  
   return $content;
}


//fix title if the network site changes the title after initial team creation
function dlinq_team_title_adjust( $title, $id ) {
   global $post;
   if($post){
         if ( in_category('team', $id ) ) {
         $slug = get_post_field( 'post_name', get_post($id) );
         if(get_sites(array( 'fields' => 'ids', 'path' => '/'. $slug . '/'))){
             $site_id = get_sites(array( 'fields' => 'ids', 'path' => '/'. $slug . '/'))[0];
             $args = array(
               'blog_id' => $site_id,
            );
               //site information
               $current_blog_details = get_blog_details( array( 'blog_id' => $site_id ) );
               $new_title = $current_blog_details->blogname;
               return $new_title;    
         }
        
      }
 
    return $title;

   }
   return $title;

}
add_filter( 'the_title', 'dlinq_team_title_adjust', 10, 2 );



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

// Register Custom Taxonomy
function dlinq_section_taxonomy() {

	$labels = array(
		'name'                       => _x( 'Sections', 'Taxonomy General Name', 'text_domain' ),
		'singular_name'              => _x( 'Section', 'Taxonomy Singular Name', 'text_domain' ),
		'menu_name'                  => __( 'Section', 'text_domain' ),
		'all_items'                  => __( 'All sections', 'text_domain' ),
		'parent_item'                => __( '', 'text_domain' ),
		'parent_item_colon'          => __( '', 'text_domain' ),
		'new_item_name'              => __( 'New Section', 'text_domain' ),
		'add_new_item'               => __( 'Add New Section', 'text_domain' ),
		'edit_item'                  => __( 'Edit Section', 'text_domain' ),
		'update_item'                => __( 'Update Section', 'text_domain' ),
		'view_item'                  => __( 'View Section', 'text_domain' ),
		'separate_items_with_commas' => __( 'Separate sections with commas', 'text_domain' ),
		'add_or_remove_items'        => __( 'Add or remove sections', 'text_domain' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'text_domain' ),
		'popular_items'              => __( 'Popular Sections', 'text_domain' ),
		'search_items'               => __( 'Search Sections', 'text_domain' ),
		'not_found'                  => __( 'Not Found', 'text_domain' ),
		'no_terms'                   => __( 'No sectioins', 'text_domain' ),
		'items_list'                 => __( 'Items list', 'text_domain' ),
		'items_list_navigation'      => __( 'Items list navigation', 'text_domain' ),
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => false,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
		'show_in_rest'               => true,
	);
	register_taxonomy( 'section', array( 'post' ), $args );

}
add_action( 'init', 'dlinq_section_taxonomy', 0 );

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
  register_taxonomy('team',array('post'), array(
    'hierarchical' => false,
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


add_action( 'init', 'create_modality_taxonomies', 0 );
function create_modality_taxonomies()
{
  // Add new taxonomy, NOT hierarchical (like tags)
  $labels = array(
    'name' => _x( 'Modalities', 'taxonomy general name' ),
    'singular_name' => _x( 'Modality', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Modalities' ),
    'popular_items' => __( 'Popular Modalities' ),
    'all_items' => __( 'All Modalities' ),
    'parent_item' => null,
    'parent_item_colon' => null,
    'edit_item' => __( 'Edit Modality' ),
    'update_item' => __( 'Update modality' ),
    'add_new_item' => __( 'Add New modality' ),
    'new_item_name' => __( 'New modality' ),
    'add_or_remove_items' => __( 'Add or remove Modalities' ),
    'choose_from_most_used' => __( 'Choose from the most used Modalities' ),
    'menu_name' => __( 'Modalities' ),
  );

//registers taxonomy specific post types - default is just post
  register_taxonomy('modality',array('post'), array(
    'hierarchical' => false,
    'labels' => $labels,
    'show_ui' => true,
    'update_count_callback' => '_update_post_term_count',
    'query_var' => true,
    'rewrite' => array( 'slug' => 'modality' ),
    'show_in_rest'          => true,
    'rest_base'             => 'modality',
    'rest_controller_class' => 'WP_REST_Terms_Controller',
    'show_in_nav_menus' => true,    
  ));
}

//let in the iframes 

function allow_iframes_for_editor( $allowed_tags ){
    
   $allowed_tags['iframe'] = array(
      'align' => true,
      'allow' => true,
      'allowfullscreen' => true,
      'class' => true,
      'frameborder' => true,
      'height' => true,
      'id' => true,
      'marginheight' => true,
      'marginwidth' => true,
      'name' => true,
      'scrolling' => true,
      'src' => true,
      'style' => true,
      'width' => true,
      'allowFullScreen' => true,
      'class' => true,
      'frameborder' => true,
      'height' => true,
      'mozallowfullscreen' => true,
      'src' => true,
      'title' => true,
      'webkitAllowFullScreen' => true,
      'width' => true
   );
    
    if ( current_user_can('editor') ) {
      return $allowed_tags;  
    }
}

add_filter( 'wp_kses_allowed_html', allow_iframes_for_editor, 1 );
