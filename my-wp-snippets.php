<?php
/*
Different php-snippets, needed for everyday work with Wordpress.
*/

//========================================================================
//=========================== Get Posts
//========================================================================

$args = array(
	'posts_per_page'   => 5,
	'offset'           => 0,
	'category'         => '',
	'orderby'          => 'post_date', /* none|ID|author|title|name|date|modified|parent|rand|comment_count|meta_value|meta_key=keyname|meta_value_num|post__in */
	'order'            => 'DESC', // ASC|DESC
	'include'          => '',
	'exclude'          => '',
	'meta_key'         => '',
	'meta_value'       => '',
	'post_type'        => 'post',
	'post_mime_type'   => '',
	'post_parent'      => '',
	'post_status'      => 'publish',
	'suppress_filters' => true );
	
//========================================================================	
//=========================== Custom loop WP_Query
//========================================================================

// The Query
$the_query = new WP_Query( $args );

// The Loop
while ( $the_query->have_posts() ) {
	$the_query->the_post();
	echo '<li>' . get_the_title() . '</li>';
}
wp_reset_postdata();

//========================================================================
//=========================== Custom Loop get_posts
//========================================================================

global $post; // required
$args = array('category' => -9); // exclude category 9
$custom_posts = get_posts($args);
foreach($custom_posts as $post) : setup_postdata($post);
	//...
endforeach;

//========================================================================
//=========================== Add custom JS/CSS
//========================================================================

wp_register_script('customjs', get_template_directory_uri() . '/js/custom.js', 'jquery' /*deps*/, '1.0', true/*put in footer*/);
wp_enqueue_script('customjs');

wp_register_style("customcss", get_template_directory_uri() . "/css/custom.css", 'maincss'/*deps*/, '1.0', 'print');
wp_enqueue_style('customcss');

//========================================================================
//=========================== Add custom image sizes
//========================================================================

add_theme_support('post-thumbnails');
add_image_size('custom-size', 720, 480, true);
add_image_size('slide-image', 900, 350, true);

//========================================================================
//=========================== Configure excerpt and more
//========================================================================

function custom_excerpt($length) {
    return 30;
}
add_filter('excerpt_length', 'custom_excerpt', 999);

function custom_more($more){
    return '...';
}
add_filter('excerpt_more', 'custom_more');

//========================================================================
//=========================== REGISTER Custom Post Type
//========================================================================

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

//========================================================================
//================================ Register P2P connection
//========================================================================

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

//========================================================================	
//================================ Get P2P related posts
//========================================================================
	
$posts = get_posts( array(
                'connected_type' => self::getConnectionType($sender_class, $receiver_class),
                'connected_items' => $post, // Instead of get_queried_object(). Works on archives too.
                'nopaging' => true,
                'suppress_filters' => false
            ) );
			
//========================================================================	
//================================ AJAX (multiple forms on different pages)
//========================================================================	

//------------------------------- First, in HTML
?>	
<form id="SomeMailForm"> <!-- simple form, with one user field and necessary hidden felds -->
    <label for="name">Your name: </label>
    <input id="name" name="name" value = "" type="text" /><br>
    <input id="formID" name="formID" value = "join" type="hidden" /> <!-- form ID for processing function -->
    <input name="action" type="hidden" value="my_ajax_hook" /> <!-- this puts the action my_ajax_hook into the serialized form -->
    <input id="submit_button" value = "Join us!!!" type="button" onClick="submit_join();" /> <!-- assign our JS function to button click -->
</form>
<div id="response_join" class="response_area"></div> <!-- response will be here, should be display:none by default -->
<?php
//------------------------------- ... then, in js/ajax.js ...	
function submit_join(){
    jQuery.post(the_ajax_script.ajaxurl, jQuery("#SomeMailForm").serialize() // the_ajax_script.ajaxurl == admin-ajax.php, variable set by wp_localize_script(), see below
        ,
        function(my_response){
            jQuery("#response_join").html(my_response);
            jQuery("#response_join").fadeIn('800'); // or simply .show() it
        }
    );
}
//------------------------------- ... meanwhile in processing function (function.php/plugin)...
function ajax_process(){
        switch($_POST['formID']){
            case 'join': // request from 1st form
                echo "1st!";
                break;
            case 'another_form_id': // request from 2nd form
                echo "2nd";
                break;
        }
    die();// wordpress may print out a spurious zero without this - can be particularly bad if using json
}
//------------------------------- finally, necessary scripts init (function.php/plugin)			
wp_register_script('my-ajax-handle', plugin_dir_url(dirname( __FILE__ )) . 'js/ajax.js'); //
wp_enqueue_script( 'my-ajax-handle' );
wp_localize_script( 'my-ajax-handle', 'the_ajax_script', array('ajaxurl' => admin_url( 'admin-ajax.php'))); // pass a variable to our script
add_action( 'wp_ajax_my_ajax_hook', 'ajax_process');
add_action( 'wp_ajax_nopriv_my_ajax_hook', 'ajax_process');

//========================================================================	
//================================ Sidebar register
//========================================================================			
//-------------------- In functions.php/plugin
if (function_exists('register_sidebar')) {
    register_sidebar(array(
        'name' => 'New sidebar',
        'id' => 'newsidebar',
        'description' => '',
        'class' => '', // CSS class
        'before_widget' => '', // HTML
        'after_widget' => '', // HTML
        'before_title' => '', // HTML
        'after_title' => '' // HTML
    ));
}

//--------------------- In theme

if (is_active_sidebar('newsidebar')) 
dynamic_sidebar('newsidebar'); 

//========================================================================	
//================================ JS Google Map template
//========================================================================

<script>
function initialize() {

	var myLatlng = new google.maps.LatLng(51.56789, 31.45678); // Coords
  
	var mapOptions = {
		scrollwheel: false,
		navigationControl: false,
		mapTypeControl: false,
		scaleControl: false,
		draggable: true,
		zoom: 14,
		center: myLatlng,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	}
	myMap = new google.maps.Map(document.getElementById("map-canvas"), mapOptions); // Init map
	
	var myImage = "http://www.domain.com/custom_ico.png"; // Custom image

	var myMarker = new google.maps.Marker({
      position: myLatlng,
      map: myMap,
	  animation: google.maps.Animation.DROP,
      title: 'Just a test map',
	  draggable: false,
	  icon: myImage 
	});
	
	var myInfoWindow = new google.maps.InfoWindow({
      content: 'Popup text goes here',
      maxWidth: 200
	});
  
	google.maps.event.addListener(myMarker, "click", function() {
    myInfoWindow.open(myMap, myMarker);
	});
	}
	
google.maps.event.addDomListener(window, "load", initialize);
</script>

<div id="map-canvas"></div>

//========================================================================
//==================================== jQuery scripts in WordPress
//========================================================================

jquery							/wp-includes/js/jquery/jquery.js (v1.7.2 as of WP 3.3, v1.8.3 as of WP 3.5)
jquery-ui-core					/wp-includes/js/jquery/ui/jquery.ui.core.min.js
jquery-effects-core				/wp-includes/js/jquery/ui/jquery.effects.core.min.js
jquery-effects-blind			/wp-includes/js/jquery/ui/jquery.effects.blind.min.js
jquery-effects-bounce			/wp-includes/js/jquery/ui/jquery.effects.bounce.min.js
jquery-effects-clip				/wp-includes/js/jquery/ui/jquery.effects.clip.min.js
jquery-effects-drop				/wp-includes/js/jquery/ui/jquery.effects.drop.min.js
jquery-effects-explode			/wp-includes/js/jquery/ui/jquery.effects.explode.min.js
jquery-effects-fade				/wp-includes/js/jquery/ui/jquery.effects.fade.min.js
jquery-effects-fold				/wp-includes/js/jquery/ui/jquery.effects.fold.min.js
jquery-effects-highlight		/wp-includes/js/jquery/ui/jquery.effects.highlight.min.js
jquery-effects-pulsate			/wp-includes/js/jquery/ui/jquery.effects.pulsate.min.js
jquery-effects-scale			/wp-includes/js/jquery/ui/jquery.effects.scale.min.js
jquery-effects-shake			/wp-includes/js/jquery/ui/jquery.effects.shake.min.js
jquery-effects-slide			/wp-includes/js/jquery/ui/jquery.effects.slide.min.js
jquery-effects-transfer			/wp-includes/js/jquery/ui/jquery.effects.transfer.min.js
jquery-ui-accordion				/wp-includes/js/jquery/ui/jquery.ui.accordion.min.js
jquery-ui-autocomplete			/wp-includes/js/jquery/ui/jquery.ui.autocomplete.min.js
jquery-ui-button				/wp-includes/js/jquery/ui/jquery.ui.button.min.js
jquery-ui-datepicker			/wp-includes/js/jquery/ui/jquery.ui.datepicker.min.js
jquery-ui-dialog				/wp-includes/js/jquery/ui/jquery.ui.dialog.min.js
jquery-ui-draggable				/wp-includes/js/jquery/ui/jquery.ui.draggable.min.js
jquery-ui-droppable				/wp-includes/js/jquery/ui/jquery.ui.droppable.min.js
jquery-ui-mouse					/wp-includes/js/jquery/ui/jquery.ui.mouse.min.js
jquery-ui-position				/wp-includes/js/jquery/ui/jquery.ui.position.min.js
jquery-ui-progressbar			/wp-includes/js/jquery/ui/jquery.ui.progressbar.min.js
jquery-ui-resizable				/wp-includes/js/jquery/ui/jquery.ui.resizable.min.js
jquery-ui-selectable			/wp-includes/js/jquery/ui/jquery.ui.selectable.min.js
jquery-ui-slider				/wp-includes/js/jquery/ui/jquery.ui.slider.min.js
jquery-ui-sortable				/wp-includes/js/jquery/ui/jquery.ui.sortable.min.js
jquery-ui-tabs					/wp-includes/js/jquery/ui/jquery.ui.tabs.min.js
jquery-ui-widget				/wp-includes/js/jquery/ui/jquery.ui.widget.min.js
jquery-form						/wp-includes/js/jquery/jquery.form.js
jquery-color					/wp-includes/js/jquery/jquery.color.js
jquery-query					/wp-includes/js/jquery/jquery.query.js
jquery-serialize-object			/wp-includes/js/jquery/jquery.serialize-object.js
jquery-hotkeys					/wp-includes/js/jquery/jquery.hotkeys.js
jquery-table-hotkeys			/wp-includes/js/jquery/jquery.table-hotkeys.js
suggest							/wp-includes/js/jquery/suggest.js
schedule						/wp-includes/js/jquery/jquery.schedule.js

//========================================================================
//========================================================================

?>