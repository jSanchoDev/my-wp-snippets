<?php
/*
Different php-snippets, needed for everyday work with Wordpress.
*/

//=========================== REGISTER Custom Post Type

function register_new_cpt() {
register_post_type('new_cpt', array( 
'label' => 'New_CPT',
'description' => '',
'public' => true,
'has_archive' => true,
'show_ui' => true,
'show_in_menu' => true,
'capability_type' => 'post',
'hierarchical' => false,
'rewrite' => array('slug' => 'new_cpt', 'with_front' => 1),
'query_var' => true,
'exclude_from_search' => false,
'supports' => array('title','editor','excerpt','trackbacks','custom-fields','comments','revisions','thumbnail','author','page-attributes','post-formats'),
'labels' => array (
  'name' => 'New_CPT',
  'singular_name' => 'New_CPT',
  'menu_name' => 'New_CPT',
  'add_new' => 'Add New_CPT',
  'add_new_item' => 'Add New_CPT',
  'edit' => 'Edit New_CPT',
  'edit_item' => 'Edit New_CPT',
  'new_item' => 'New New_CPT',
  'view' => 'View New_CPT',
  'view_item' => 'View New_CPT',
  'search_items' => 'Search for New_CPT',
  'not_found' => 'New_CPT not found',
  'not_found_in_trash' => 'New_CPT not found in trash',
  'parent' => 'Parent New_CPT')
) 
); }
add_action('init', 'register_new_cpt');

//================================ Register P2P connection

$a_b_args = array(
    'name' => 'a_2_b', // name of relation
    'from' => 'a_cpt', // first cpt name
    'to'   => 'b_cpt', // second cpt name
    'sortable'   => 'any',
    'reciprocal' => false,
    'admin_box' => array(
        'show' => 'any',
        'context' => 'normal',
        'can_create_post' => true
    ),
    'admin_column' => 'any'
);
    p2p_register_connection_type($a_b_args);
?>