<?php
/*
Plugin Name: jQuery Cycle Slideshow for Simplest Gallery
Version: 1.8
Plugin URI: http://www.simplestgallery.com/add-ons/jquery-cycle-slideshow-gallery-style-plugin/
Description: Display your Wordpress galleries as a jQuery Slideshow. Requires the "Simplest Gallery" plugin (adds a new gallery style to it).
Author: Cristiano Leoni
Author URI: http://www.linkedin.com/pub/cristiano-leoni/2/b53/34

# This file is UTF-8 - These are accented Italian letters àèìòù

*/

/*

    History
   + 1.8 2019-03-22 Made it responsive - optimized for bootstrap (img-responsive class defined) and replaced jquery lite library with a newer version. Tested on WP 5.1.1
   + 1.7 2019-01-14 tested on WP 5.0.3, suppressed annoying message if Simplest Gallery main plugin is not present or not active (in this case the plugin will do nothing) 
   + 1.6 2019-01-08 Works with HTTPS, tested on WP 5.02
   + 1.5 2017-06-09	Added support for width,height and border attributes in shortcode
   + 1.4 2013-09-18	Fix on jQuery library version (gallery did not work on some themes)
   + 1.3 2013-09-12	Fixed rare bug in startup. Support for multiple galleries in the same page (with Simplest Gallery version 2.5 or higher)
   + 1.2 2013-09-01	Bug fix
   + 1.1 2013-09-01	Bug fixes for compatibility issues with WP 3.6
   + 1.0 2013-04-29	First working version
*/


add_action('init', 'sgac_init');

// Init tasks: adds a new gallery format to the Simplest Gallery plugin via an API call
function sgac_init() {
	$urlpath = WP_PLUGIN_URL . '/' . basename(dirname(__FILE__));

	// Check if Simplest Gallery Plugin is installed - it might display a reminder line
	if (!function_exists('sga_register_gallery_type') && !($_REQUEST['plugin']=='simplest-gallery/simplest-gallery.php' && $_REQUEST['action']=='activate')) {
		//echo "jQuery Cycle Slideshow requires Simplest Gallery plugin to work.";
		return;
	} else {
		if (is_callable('sga_register_gallery_type')) {
   
		// Adds new gallery type to the Simplest Gallery Plugin
		$result = sga_register_gallery_type(
							'cycle', 		/* this is the gallery type's unique ID */
							'jQuery Cycle Slideshow', /* this is the gallery type name (what the user will see in the settings page) */
							'sgac_render',		/* Function to be called for the gallery rendering */
							'sgac_header',		/* Function to be called on header() */
							array(			/* Array of scripts to be included, possibly empty */
								'jquery'=>array('//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js', false, '1.11.3'),
								'jquery-jjcycle'=>array($urlpath . '/script/jquery.cycle.lite.js', array('jquery'), '1.6'),
							      ),
							array()			/* Array of CSS to be included, possibly empty */
						);
		}
	}

}

// Sample header custom function. If we need to make special things in the header of pages for our gallery format, we will do so here
function sgac_header() {
	return "<!-- jQuery Cycle Slideshow module for Simplest Gallery -->\n";
}

// This is a the gallery-rendering function. We don't need to care about gathering images because the Simplest Gallery plugin does this for us.
// First parameter is an array of images data (images of the gallery to be rendered), second parameter is an array of thumbs data (unused here)
// data here means that each image/thumb is represented by an array. Each position holds a specific thing:
// 0=URL,1=width,2=height,3=unused,4=ID,5=Label
function sgac_render($images,$thumbs,$post_id=NULL,$gall_id=NULL,$attrs,$gallery_type='cycle') {

	if (is_array($attrs)) { // Are parameters specified in the shortcode?

		if (isset($attrs['border'])) { 
			$border = $attrs['border'];
		}

		if (isset($attrs['width'])) {
			$width = $attrs['width'];
		}

		if (isset($attrs['height'])) {
			$height = $attrs['height'];
		}
	}    
    if ($post_id) {
	    if (!$width) { // Deprecated
	    	$width=get_post_meta($post_id, 'gall_width', true);
	    }
	    if (!$height) { // Deprecated
	    	$height=get_post_meta($post_id, 'gall_height', true);
	    }
    }
	if ($width) $width.='px';
	if ($height) $height.='px';
    
    $html_id='cycle_lite_'.$gall_id;
    
    $output = '';

	$style_outer .= "width:100%;height:0;".($width?"max-width:$width;":'')."overflow:hidden;";
	$style_inner .= "width:100%;".($height?"max-height:$height;":'')."overflow:hidden;";
      
	//$style_outer .= 'margin-left:auto;margin-right:auto;';
      
	if($border) $style_outer .= "border:$border;";
      
	$style_outer_tag = " style=\"" . $style_outer . "\"";
	$style_inner_tag = " style=\"" . $style_inner . "\"";

	$output .= "\n<div id=\"" . $html_id . "_container\" class=\"cycle_lite_container\"" . $style_outer_tag . " >\n";
	$output .= "\n  <div id=\"" . $html_id . "\"" . $style_inner_tag . ">\n";
	$image_alt = null;
	$image_description = null;
	$firstim=true;
	foreach($images as $image) {
	$image_alt = 'image';
	$image_description = $image[5];

	if($image_description != '')
	{
	  $image_description = "alt=\"" . esc_attr($image_description) . "\" title=\"" . esc_attr($image_description) . "\" ";
	}
	else
	{
	  $image_description = '';
	}

	$output .= '<img '.($firstim?('id="'.$html_id.'_first" '):'').' class="img-responsive appr" src="' . $image[0] . "\" " . $image_description . " style=\"border:none;position:relative;top:0px;left:0px;\" />\n";

	$firstim=false;
	}
	$output .= "\n  </div>";
	$output .= "\n</div>";

    // Cycle Lite arguments
    $javascript_args = array();
	$javascript_args[] = "height: ".'(($("#'.$html_id.'_first").height())?($("#'.$html_id.'_first").height()):600)';

    if($timeout != "") { $javascript_args[] = "timeout: " . $timeout; }
    if($speed != "") { $javascript_args[] = "speed: " . $speed; }
    if($sync != "") { $javascript_args[] = "sync: " . $sync; }
    if($fit != "") { $javascript_args[] = "fit: " . $fit; }
    if($pause != "") { $javascript_args[] = "pause: " . $pause; }
    if($delay != "") { $javascript_args[] = "delay: " . $delay; }

    // Add javascript
    $output .= "\n<script type=\"text/javascript\">\n";
	$output .= "$(document).ready(function() {\n";
	$output .= '$("#'.$html_id.'_first").one("load", function() {'."\n";
	$output .= '$("#'.$html_id.'_container").css( "height",$("#'.$html_id.'_first").height() );'."\n";
	$output .= '$("#'.$html_id.'").css( "height",$("#'.$html_id.'_first").height() );'."\n";
	$output .= "\n  $('#" . $html_id . "').cycle(";
	if(count($javascript_args) > 0)
	{
	  $output .= "{" . implode(",", $javascript_args) . "}";
	} 
	$output .= ");";
	$output .= "\n}).each(function() {
  if(this.complete) { $(this).trigger('load');}
});\n"; // End first image load function
	$output .= "\n});\n"; // End document ready function
    $output .= "\n</script>\n";

	return $output;


}


?>