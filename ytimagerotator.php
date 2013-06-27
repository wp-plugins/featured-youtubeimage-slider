<?php
/*
Plugin Name: Featured YouTube/Image Slider
Plugin URI: http://wordpress.org/plugins/featured-youtubeimage-slider/
Description: A YouTube/Image Rotator for WordPress
Version: 1.1
Author: Alexander C. Block
Author URI: http://pizzli.com
License: GPLv2
*/
/*  Copyright 2013 Alexander C. Block  (email : ablock@pizzli.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function AddScripts(){
wp_register_style('ytirstylesheet', plugins_url('/css/style.css',__FILE__));
wp_enqueue_script('jquery');
wp_enqueue_script('jquery-ui-core');
wp_enqueue_script('jquery-ui-tabs');
wp_enqueue_style('ytirstylesheet');
}
add_action( 'wp_enqueue_scripts', 'AddScripts' );

//Create Custom Post Type for YITR
function my_custom_post_YTIR() {
	$labels = array(
		'name'               => _x( 'YTIRs', 'post type general name' ),
		'singular_name'      => _x( 'YTIR', 'post type singular name' ),
		'add_new'            => _x( 'Add New', 'YTIR' ),
		'add_new_item'       => __( 'Add New YTIR' ),
		'edit_item'          => __( 'Edit YTIR' ),
		'new_item'           => __( 'New YTIR' ),
		'all_items'          => __( 'All YTIR' ),
		'view_item'          => __( 'View YTIR' ),
		'search_items'       => __( 'Search YTIR' ),
		'not_found'          => __( 'No YTIRs found' ),
		'not_found_in_trash' => __( 'No YTIRs found in the Trash' ), 
		'parent_item_colon'  => '',
		'menu_name'          => 'YTIRs'
	);
	$args = array(
		'labels'        => $labels,
		'description'   => 'Stores YTIRs',
		'public'        => true,
		'menu_position' => 5,
		'supports'      => array( 'title', 'editor', 'thumbnail'),
		'has_archive'   => true,
	);
	register_post_type( 'YTIR', $args );	
}
add_action( 'init', 'my_custom_post_YTIR' );
add_action('admin_menu', 'YTIR');
add_action('save_post', 'saveYTIR');

function YTIR() { add_meta_box('YTIR', 'YouTube ID', 'YTIR_input_function', 'YTIR', 'normal', 'high');}

function YTIR_input_function() {
global $post;
echo '<input type="text" name="YTIR_input" id="YTIR_input" style="width:100%;" value="'.get_post_meta($post->ID,'_YTIR',true).'" />';
}

function saveYTIR($post_id) {
if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;
$YTIR = $_POST['YTIR_input'];
update_post_meta($post_id, '_YTIR', $YTIR);
}

function OutputSlider(){
echo '
<script>
var j = jQuery.noConflict();
j(document).ready(function(){  
;(function(j){
	j.extend( j.ui.tabs.prototype, {
		rotation: null,
		rotationDelay: null,
		continuing: null,
		rotate: function( ms, continuing ) {
			var self = this,
				o = this.options;

			if((ms > 1 || self.rotationDelay === null) && ms !== undefined){//only set rotationDelay if this is the first time through or if not immediately moving on from an unpause
				self.rotationDelay = ms;
			}

			if(continuing !== undefined){
				self.continuing = continuing;
			}

			var rotate = self._rotate || ( self._rotate = function( e ) {
				clearTimeout( self.rotation );
				self.rotation = setTimeout(function() {
					var t = o.selected;
					self.select( ++t < self.anchors.length ? t : 0 );
				}, ms );

				if ( e ) {
					e.stopPropagation();
				}
			});

			var stop = self._unrotate || ( self._unrotate = !continuing
				? function(e) {
					if (e.clientX) { // in case of a true click
						self.rotate(null);
					}
				}
				: function( e ) {
					t = o.selected;
					rotate();
				});

			// start rotation
			if ( ms ) {
				this.element.bind( "tabsshow", rotate );
				this.anchors.bind( o.event + ".tabs", stop );
				rotate();
			// stop rotation
			} else {
				clearTimeout( self.rotation );
				this.element.unbind( "tabsshow", rotate );
				this.anchors.unbind( o.event + ".tabs", stop );
				delete this._rotate;
				delete this._unrotate;
			}

			//rotate immediately and then have normal rotation delay
			if(ms === 1){
				//set ms back to what it was originally set to
				ms = self.rotationDelay;
			}

			return this;
		},
		pause: function() {
			var self = this,
				o = this.options;

			self.rotate(0);
		},
		unpause: function(){
			var self = this,
				o = this.options;

			self.rotate(1, self.continuing);
		}
	});
})(jQuery);
});
j(document).ready(function(){  
j("#featured").tabs({fx:{opacity: "toggle"}}).tabs("rotate", 5000, true);  
j("#featured").hover(  
function() {  
j("#featured").tabs("rotate",0,true);  
},  
function() {  
j("#featured").tabs("rotate",5000,true);  
}  
);  
});
</script>';
echo '<div id="featured" ><ul class="ui-tabs-nav">';
$x=0;
query_posts('showposts=6&post_type=YTIR');
while (have_posts()) : the_post();
$x++;
echo '<li class="ui-tabs-nav-item" id="nav-fragment-'.$x.'"><a href="#fragment-'.$x.'">';
the_post_thumbnail(array(80,50));
echo '<span>'.get_the_title().'</span></a></li>';
endwhile;
echo '</ul>';

wp_reset_query();
$x=0;
query_posts('showposts=6&post_type=YTIR');
while (have_posts()) : the_post();
$x++;
$large_feat_image = get_the_post_thumbnail($post->ID, array(400,250) );
$YouTubeID = get_post_meta(get_the_ID(),'_YTIR', true);
echo '<div id="fragment-'.$x.'" class="ui-tabs-panel" style="">';
if ($YouTubeID !== '')
{
echo '<iframe width="400" height="250" src="http://www.youtube.com/embed/'.$YouTubeID.'" frameborder="0" allowfullscreen></iframe>';
}
if ($YouTubeID == '')
{
the_post_thumbnail(array(400,250) );
}

echo '<div class="info" >';
echo'<h2><a href="';
the_permalink();
echo '" >'.get_the_title().'</a></h2>';
echo'<p>';
echo substr(get_the_excerpt(), 0,100);
echo '<a href="';
echo the_permalink();
echo '" > read more</a></p>
			 </div>
	    </div>';
endwhile;
wp_reset_query();
echo '</div>';
}
// Dummy up theme support.
//add_theme_support( 'post-thumbnails' );

// Add the posts and pages columns filter. They can both use the same function.
add_image_size( 'admin-list-thumb', 100, 100, false );
add_filter('manage_posts_columns', 'tcb_add_post_thumbnail_column', 5);
add_filter('manage_pages_columns', 'tcb_add_post_thumbnail_column', 5);

// Add the column
function tcb_add_post_thumbnail_column($cols){
  $cols['tcb_post_thumb'] = __('Featured');
  return $cols;
}

// Hook into the posts an pages column managing. Sharing function callback again.
add_action('manage_posts_custom_column', 'tcb_display_post_thumbnail_column', 5, 2);
add_action('manage_pages_custom_column', 'tcb_display_post_thumbnail_column', 5, 2);

// Grab featured-thumbnail size post thumbnail and display it.
function tcb_display_post_thumbnail_column($col, $id){
  switch($col){
    case 'tcb_post_thumb':
      if( function_exists('the_post_thumbnail') )
        echo the_post_thumbnail( 'admin-list-thumb' );
      else
        echo 'Not supported in theme';
      break;
  }
}


add_shortcode('ytimageslider', 'OutputSlider');
?>