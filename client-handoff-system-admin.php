<?php
/**
 * Plugin Name: Client Handoff System Admin
 * Plugin URI: http://www.cafevagrant.com/client-handoff-system-admin/
 * Description: This plugin is meant to provide easy handoff to clients by creating another permission group and category only accesible by the new permission group.
 * Version: 1.0.0
 * Author: Tanner Chung
 * Author URI: http://www.cafevagrant.com
 * License: GPL2
 */


/* Initializing Plugin, registering new role */

register_activation_hook( __FILE__, 'initial_activation' );
function initial_activation() {
    $admin = get_role( 'administrator' );
    $system_admin_caps = $admin->capabilities;
    $role = add_role( 'system_admin', 'System Admin', $system_admin_caps );
    $role->add_cap( 'be_system_admin');
}

register_deactivation_hook( __FILE__, 'final_deactivation' );
function final_deactivation() {
    remove_role( 'system_admin' );
}

/* Creating New Category */
add_action( 'init', 'ny_page_category' );
function ny_page_category() {

    $show_ui = ( current_user_can( 'be_system_admin' ) ) ? true : false;
    $labels = array(
        'name'              => 'Page Categories',
        'singular_name'     => 'Page Category',
        'search_items'      => 'Search Page Categories',
        'all_items'         => 'All Page Categories',
        'parent_item'       => 'Parent Page Category',
        'parent_item_colon' => 'Parent Page Category:',
        'edit_item'         => 'Edit Page Category',
        'update_item'       => 'Update Page Category',
        'add_new_item'      => 'Add New Page Category',
        'new_item_name'     => 'New Page Category Name',
        'menu_name'         => 'Page Categories',
    );
    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => $show_ui,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'page_category' ),
    );
    register_taxonomy( 'page_category', array( 'page' ), $args );
}

/* Hiding page category system-page */

add_action( 'pre_get_posts', 'my_hide_system_pages' );
function my_hide_system_pages( $query ) {
   if( is_admin() && !empty( $_GET['post_type'] ) && $_GET['post_type'] == 'page' && $query->query['post_type'] == 'page' && !current_user_can( 'be_system_admin' ) ) {
       $query->set( 'tax_query', array(array(
           'taxonomy' => 'page_category',
           'field' => 'slug',
           'terms' => array( 'system-page' ),
           'operator' => 'NOT IN'
       )));
   }
}

/**
 * Remove unneccessary roles
 */

add_action('init', 'removing_roles');

function removing_roles() {
  if (!current_user_can('be_system_admin')) {
    if ( get_role('contributor')) {
      remove_role ('contributor');
    }

    if ( get_role('subscriber')) {
        remove_role ('subscriber');
    }

    if ( get_role('author')) {
        remove_role ('author');
    }
  }
}

add_action('init', 'hide_admin_bar');
function hide_admin_bar() {
  if (!current_user_can('be_system_admin')) {
      add_filter('show_admin_bar', '__return_false');
  }
}