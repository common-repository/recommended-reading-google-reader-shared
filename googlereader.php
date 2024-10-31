<?php
/**
 Plugin Name: Recommended Reading: Google Reader Shared
 Plugin URI: http://www.get10up.com/plugins/recommended-reading-google-reader-shared-wordpress/
 Description: Pulls shared items from your <strong>Google Reader</strong> account for display in a <strong>sidebar widget</strong> or within pages or posts. Easily customized with friendly interface. <strong>Embed in page or post</strong> via shortcode; can always show newest, or start at post publication date!
 Version: 4.0.4
 Author: Jake Goldman (10up)
 Author URI: http://www.get10up.com

    Plugin: Copyright 2009 10up (email : jake@get10up.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * rr_grs_activation() handles plugin activation
 */
function rr_grs_activation() 
{
	//BACKWARDS COMPATABILITY FROM PRE 4.0
	if (get_option('gr_sid')) 
	{
		//connection conversion
		$grs_conn = array();
		$grs_conn['id'] = get_option('gr_sid');
		delete_option('gr_sid');
	 	$grs_conn['item_count'] = get_option('gr_num');
	 	delete_option('gr_num');
	 	update_option('rr_grs_connection',$grs_conn);
		
		//preference conversion
		$grs_prefs = array();
		$grs_prefs['link_love'] = (get_option('gr_credit')) ? 1 : 0;
		delete_option('gr_credit');
		$grs_prefs['content_show'] = (get_option('gr_content')) ? 1 : 0;
		delete_option('gr_content');
		$grs_prefs['content_truncate'] = intval(get_option('gr_trim'));
		delete_option('gr_trim');
		$grs_prefs['date_format'] = get_option('gr_date');
		delete_option('gr_date');
		$grs_prefs['date_preface'] = get_option('gr_datep');
		delete_option('gr_datep');
		$grs_prefs['source_show'] = (get_option('gr_source')) ? 1 : 0;
		delete_option('gr_source');
		$grs_prefs['source_preface'] = get_option('gr_sourcep');
		delete_option('gr_sourcep');
		$grs_prefs['new_window'] = (get_option('gr_open')) ? 1 : 0;
		delete_option('gr_open');
		$grs_prefs['no_follow'] = (get_option('gr_nofollow')) ? 1 : 0;
		delete_option('gr_nofollow');
		$grs_prefs['feed_link_text'] = get_option('gr_read_more_text');
		delete_option('gr_read_more_text');
		$grs_prefs['feed_link_show'] = (get_option('gr_read_more_feed')) ? 1 : 0;
		delete_option('gr_read_more_feed');
		$grs_prefs['notes_show'] = (get_option('gr_notes')) ? 1 : 0;
		delete_option('gr_notes');
		$grs_prefs['notes_preface'] = get_option('gr_notesp');
		delete_option('gr_notep');
		update_option('rr_grs_prefs',$grs_prefs);
		
		//shortcode conversion
		$grs_shortcode = array();
		$grs_shortcode['as_of_publish_date'] = (get_option('gr_publish_date')) ? 1 : 0;
		delete_option('gr_publish_date');
		$grs_shortcode['up_to_last_post'] = (get_option('gr_publish_upto')) ? 1 : 0;
		delete_option('gr_publish_upto');
		$grs_shortcode['truncate_override'] = (get_option('gr_sc_trim')) ? 1 : 0;
		delete_option('gr_sc_trim');
		$grs_shortcode['html_show'] = (get_option('gr_sc_html')) ? 1 : 0;
		delete_option('gr_sc_html');
		$grs_shortcode['html_simple'] = (get_option('gr_sc_simplehtml')) ? 1 : 0;
		delete_option('gr_sc_simplehtml');
		$grs_shortcode['html_noimg'] = (get_option('gr_sc_strpimg')) ? 1 : 0;
		delete_option('gr_sc_strpimg');
		$grs_shortcode['styles'] = (get_option('gr_sc_styles')) ? 1 : 0;
		delete_option('gr_sc_styles');
		update_option('rr_grs_shortcode',$grs_shortcode);
		
		//widget update
		update_option('rr_grs_widget_options',get_option('setup_widget_grs'));
		delete_option('setup_widget_grs');
		
		//delete other old options
		delete_option('gr_lastupdate');
		delete_option('gr_sc_lastupdate');
		delete_option('gr_skipnote'); //TEMPORARY
		delete_option('gr_sc_cache');
		delete_option('gr_cache');
	}
	
	
	//default settings
	$rr_grs_prefs = get_option('rr_grs_prefs');
	if (!$rr_grs_prefs) {
		$rr_grs_prefs['link_love'] = 1;
		update_option('rr_grs_prefs', $rr_grs_prefs);
		return true;
	}
	rr_grs_flush(); //flush the cache
}
register_activation_hook(__FILE__,'rr_grs_activation');

/**
 * rr_grs_flush() clears all of the caching
 * 
 * @param bool $connection_info indicates whether the widget cache needs to be cleared too
 */
function rr_grs_flush($connection_info = true) 
{
	//may not be necessary to clean up connection info if settings are being updated already
	if ($connection_info) {
		//flush last update timestamps
		$rr_grs_connection = get_option('rr_grs_connection');
		$rr_grs_connection['widget_lastupdate'] = '';
		$rr_grs_connection['widget_cache'] = '';
		update_option('rr_grs_connection', $rr_grs_connection);
	}
	//clear shortcode cache(s)
	global $wpdb; 
	$wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key = '_rr_grs_cache' OR meta_key = '_rr_grs_cache_date';");	
}

/**
 * rr_grs_options_init() initializes plugin options
 * 
 * @return
 */
function rr_grs_options_init() 
{
	register_setting('rr_grs_options','rr_grs_connection','rr_grs_connection_validate'); //array of fundamental options including ID and caching info 
	register_setting('rr_grs_options','rr_grs_prefs','rr_grs_prefs_validate'); //generic preferences array
	register_setting('rr_grs_options','rr_grs_shortcode','rr_grs_shortcode_validate'); //array of shortcode options
}
add_action('admin_init','rr_grs_options_init');

/**
 * rr_grs_connection_validate() handles validation of connection options
 */
function rr_grs_connection_validate($input) 
{
	rr_grs_flush(false); //flush cache; not necessary to flush info updated here too
	$input['id'] = (is_numeric($input['id'])) ? $input['id'] : ''; //ID must be numeric
	$input['item_count'] = intval($input['item_count']); //item count must be integer
	return $input; //pass back to save
}

/**
 * rr_grs_prefs_validate() handles validation of general preferences
 */
function rr_grs_prefs_validate($input) 
{
	$input['link_love'] = ($input['link_love'] == 1) ? 1 : 0;
	$input['content_show'] = ($input['content_show'] == 1) ? 1 : 0;
	$input['content_truncate'] = intval($input['content_truncate']);
	$input['source_show'] = ($input['source_show'] == 1) ? 1 : 0;
	$input['new_window'] = ($input['new_window'] == 1) ? 1 : 0;
	$input['no_follow'] = ($input['no_follow'] == 1) ? 1 : 0;
	$input['feed_link_show'] = ($input['feed_link_show'] == 1) ? 1 : 0; 
	$input['notes_show'] = ($input['notes_show'] == 1) ? 1 : 0;
	// $input['notes_skip'] = ($input['notes_skip'] == 1) ? 1 : 0;
	// no sanitization: date_format, date_preface, source_preface, feed_link_text, notes_preface
	return $input;
}

/**
 * rr_grs_shortcode_validate() handles sanitization of shortcode options
 */
function rr_grs_shortcode_validate($input) 
{
	$input['as_of_publish_date'] = ($input['as_of_publish_date'] == 1) ? 1 : 0;
	$input['up_to_last_post'] = ($input['up_to_last_post'] == 1) ? 1 : 0;
	$input['truncate_override'] =  ($input['truncate_override'] == 1) ? 1 : 0;
	$input['html_show'] =  ($input['html_show'] == 1) ? 1 : 0;
	$input['html_simple'] =  ($input['html_simple'] == 1) ? 1 : 0;
	$input['html_noimg'] =  ($input['html_noimg'] == 1) ? 1 : 0;
	$input['styles'] =  ($input['styles'] == 1) ? 1 : 0;
	return $input;
}

/**
 * cache handling function... hooks feed transient time and returns 15 secs for aggressive users 
 */
function rr_grs_cache_override($age) { return 15; }

/**
 * rr_grs_fetch_items() is the core function that handles processing of the feed
 * 
 * @param bool $shortcode determines whether this should be handled as a shortcode entry
 * @param integer $maxcnt is used when a count parameter is passed via shortcode tag
 * @return is the output ready for display
 */
function rr_grs_fetch_items($shortcode = false, $maxcnt = 0) 
{
	if ($shortcode) 
	{
 		$shortcode = get_option('rr_grs_shortcode');	//get shortcode options
		
		//if using "as of publish date" feature
		if ($shortcode['as_of_publish_date']) 
		{
			//since new items can't really be added to the feed in the past, we can assume we're done if there's a cache (rarely untrue, solved by cache flush)
			$grs_cache = get_post_meta(get_the_ID(),"_rr_grs_cache",true);	//cache store in post meta for shortcode
			if ($grs_cache) return $grs_cache; 
			
			$post_date = get_the_time('U');	//fetch post date in pure unix time
			
			//get the date of the last shortcode post if we only want to display up to previous post using shortcode
			if($shortcode['up_to_last_post']) {
				global $wpdb;
				$last_post = $wpdb->get_var("SELECT post_date FROM $wpdb->posts WHERE post_date < '".date("Y-m-d H:i:s", $post_date)."' AND (post_type = 'post' OR post_type = 'page') AND post_content LIKE '%[recreading%]%' ORDER BY post_date DESC LIMIT 1;");
				$last_post = (is_null($last_post)) ? false : strtotime($last_post); //convert to false or friendly date format 
			}		
		} 
	}
	
	//retrieve connection info and stop this if core connection id has not been set
	$grs_conn = get_option('rr_grs_connection');  
	if (!$grs_conn['id']) return '<a href="http://www.get10up.com/plugins/recommended-reading-google-reader-shared-wordpress/" target="_blank">Recommended Reading: Google Reader Shared plug-in</a> has not been configured.';
	
	//if we are not using shortcode with "as of publish date feature", we can limit the feed to the number of items we want to show; otherwise need all (note post_date only set here under shortcode as of pub date condition)
	$item_count = 0;
	if ($maxcnt && !isset($post_date)) $item_count = $maxcnt; //if a count provided by shortcode, and not using as of publish date feature
	elseif (!isset($post_date)) $item_count = $grs_conn['item_count']; //otherwise use configured counter, unless using as of publish date feature
	
	//tentatively reduce cache to 15 seconds for aggressive users... will be an option in the future
	add_filter('wp_feed_cache_transient_lifetime', 'rr_grs_cache_override');
	
	//url for shared items feed
	$url = "http://www.google.com/reader/public/atom/user%2F".$grs_conn['id']."%2Fstate%2Fcom.google%2Fbroadcast?n=".$item_count;
	if (isset($last_post) && $last_post) $url .= "&ot=".$last_post; //only return items shared since last post EXPERIMENTAL
	
	//now using wordpress built in fetch feed feature
	include_once(ABSPATH. WPINC.'/feed.php');
	$grs_feed = fetch_feed($url);
	if(is_wp_error($grs_feed)) return "No Google Reader feed was found with the provided ID. Please validate the ID in plug-in configuration.";	//error catch
	
	//remove tentative filter
	remove_filter('wp_feed_cache_transient_lifetime','rr_grs_cache_override');
	
	$grs_entries = $grs_feed->get_feed_tags('http://www.w3.org/2005/Atom','entry');	//get the array of items
	if (!$grs_entries || count($grs_entries) <= 0) return "There are no items in your shared Google Reader feed.";	//if the feed is empty, lets stop while we're ahead
	
	//basic caching handling
	$feedupdate = $grs_feed->get_feed_tags('http://www.w3.org/2005/Atom','updated');
	$feedupdate = $feedupdate[0]['data'];
	$lastupdate = ($shortcode) ? get_post_meta(get_the_ID(),"_rr_grs_cache_date",true) : $grs_conn['widget_lastupdate'];
	
	//conditions under which we use the cache
	if($lastupdate && $lastupdate == $feedupdate) {
		$gr_cache = ($shortcode) ? get_post_meta(get_the_ID(),"_rr_grs_cache",true) : $grs_conn['widget_cache'];
		return $gr_cache;
	}
	
	//otherwise we need to generate everything
		
	$grs_prefs = get_option('rr_grs_prefs'); //time to get the full preferences
	
	$grs_new_window = ($grs_prefs['new_window']) ? ' target="_blank"' : ' ';	//HTML for opening link in new window
	$grs_no_follow = ($grs_prefs['no_follow']) ? ' rel="nofollow"' : ' ';	//HTML for nofollow rel
	$content_truncate = ($shortcode && $shortcode['truncate_override']) ? "" : $grs_prefs['content_truncate'];
	// if($grs_prefs['notes_skip']) $skipped_notes = 0; //need to keep track of stand alone notes in case we need more items later  
	
	//gr_cache variable will hold all the output
	$gr_cache = '<ul class="gReader-list">';
	
	$entries_shown = 0; //counter of shown entries... necessary if using as of publish date feature and unique ids for li tags
	
	//loop through each item... will break out if using entries shown
	foreach($grs_entries as $entry) 
	{	
		//we need to get the date the item was shared if we're using shortcode with as of publish date so we know whether to skip it (can use post_date, only set under this condition)
		if (isset($post_date)) 
		{
			//retrieve date the item was shared
			$date_shared = round(floatval($entry['attribs']['http://www.google.com/schemas/reader/atom/']['crawl-timestamp-msec'])/1000);	//need to deal with millisecond to second conversion
			$date_shared = strtotime(get_date_from_gmt(date("Y-m-d H:i:s",$date_shared))); 	//need to account for current timezone for google GMT; thankfully wordpress has a function! 
		
			//if date item is shared is newer than post date, continue to next item
			if ($date_shared > $post_date) continue;
			
			//if using 'up till last post' option and date of item is older than last post, time to stop!
			if (isset($last_post) && $date_shared <= $last_post) {
				if (!$entries_shown) $gr_cache .= '<li>No items have been shared since the last post to feature shared items.</li>'; //if no items since last post, show a message
				break;
			}	
		}
		
		$entryContent = $entry['child']['http://www.w3.org/2005/Atom']; //more specific pointer
		
		$item_url = $entryContent['link'][0]['attribs']['']['href'];
		//check if its a standalone shared note - NEEDS MORE HELP LATER
		/*
		if (isset($skipped_notes) && strstr($item_url,"google.com/reader/item")) {
			$skipped_notes++;
			continue;
		}
		*/
		
		$entries_shown++; //increment entries shown			
		$gr_cache .= '<li class="gReader-item gReader-item-'.$entries_shown.'">';
		
		$title = $entryContent['title'][0]['data'];
		$gr_cache .= '<a href="'.$item_url.'" title="link to post"'.$grs_new_window.$grs_no_follow.' class="gReader-title">'.$title.'</a>';
		
		//handle date output
		if ($grs_prefs['date_format']) {
			$rawDatePublished = $entryContent['published'][0]['data'];
			$date_published = strtotime(strval($rawDatePublished)); //convert stored publish date to timestamp
			if (!$date_published) $date_published = strtotime(substr($rawDatePublished,0,10)); //bug fix for older php
			$date_published = strtotime(get_date_from_gmt(date("Y-m-d H:i:s",$date_published))); //convert to a timestamp with timezone applied
			
			$gr_cache .= '<div class="gReader-date">';
			if($grs_prefs['date_preface']) $gr_cache .= '<span class="preface">'.$grs_prefs['date_preface'].' </span>';
			$gr_cache .= date($grs_prefs['date_format'],$date_published).'</div>';
		}
		
		//handle source output
		if ($grs_prefs['source_show']) {
			$postsource = $entryContent['source'][0]['child']['http://www.w3.org/2005/Atom'];
			$postsource_title = $postsource['title'][0]['data']; 
			$postsource_link = $postsource['link'][0]['attribs']['']['href'];
			
			if ($postsource_title == "(title unknown)") $postsource = "My Shared Notes";  //for stand alone shared note
			
			$gr_cache .= '<div class="gReader-source">';
			if($grs_prefs['source_preface']) $gr_cache .= '<span class="preface">'.$grs_prefs['source_preface'].' </span>';
			$gr_cache .= '<a href="'.$postsource_link.'"'.$grs_new_window.$grs_no_follow.' title="source blog">'.$postsource_title.'</a></div>';	
		}
		
		//notes handling
		if ($grs_prefs['notes_show'] && isset($entry['child']['http://www.google.com/schemas/reader/atom/']['annotation'])) {				
			$annotation = $entry['child']['http://www.google.com/schemas/reader/atom/']['annotation'][0]['child']['http://www.w3.org/2005/Atom']['content'][0]['data'];
			$notep = ($grs_prefs['notes_preface']) ? '<span class="preface">'.$grs_prefs['notes_preface'].' </span>' : '';
			$gr_cache .= '<div class="gReader-notes">'.$notep.wp_kses($annotation,array()).'</div>';
		}
		
		//content handling
		if ($grs_prefs['content_show']) 
		{
			$desc = isset($entryContent['content']) ? $entryContent['content'][0]['data'] : $entryContent['summary'][0]['data']; //content may be stored in content tag or summary tag depending on blog
			
			if (substr($desc,0,12) == "<blockquote>") $desc = substr($desc, strpos($desc,"</blockquote>")+13); //remove inline shared item note
			
		 	if ($shortcode && $shortcode['truncate_override'] && $shortcode['html_show']) {
		 		if ($shortcode['html_simple']) $desc = wp_kses($desc,array('a' => array('href' => array(),'title' => array()),'br' => array(),'em' => array(),'strong' => array(),'blockquote'=>array(),'<p>'=>array()));
 				elseif ($shortcode['html_noimg']) $desc = preg_replace('#</?img[^>]*>#is','',$desc);
		 	} else {
 				$desc = preg_replace('/&[^;]+;/','',wp_kses($desc,array()));
 				if($content_truncate && strlen($desc) > $content_truncate) $desc = trim(substr($desc,0,$content_truncate))."...";	//shorten content if shorter than defined max length
			}
		 	 
			$gr_cache .= '<div class="gReader-desc">'.$desc.'</div>';
		}
		
		$gr_cache .= '</li>';
		
		if ($item_count && $entries_shown >= $item_count) break; //necessary for shortcode and possibly for hiding notes later
	}
	
	//handling of read more link
	if ($grs_prefs['feed_link_text'] && (!isset($last_post))) {
		$gr_cache .= '<li class="grMore"><a href="';
		$gr_cache .= ($grs_prefs['feed_link_show']) ? $url : 'http://www.google.com/reader/shared/'.$grs_conn['id'];
		$gr_cache .= '"'.$grs_new_window.$grs_no_follow.'>'.$grs_prefs['feed_link_text'].'</a></li>';
	}
	if ($grs_prefs['link_love']) $gr_cache .= '<li class="grCredit"><small>Plugin by <a href="http://www.get10up.com/plugins/recommended-reading-google-reader-shared-wordpress/" title="Expert WordPress Developers"'.$grs_new_window.'>10up</a></small></li>';
	
	$gr_cache .= "</ul>";
	
	//cache storing for shortcode
	if($shortcode) {
		update_post_meta(get_the_ID(),"_rr_grs_cache",$gr_cache);
		update_post_meta(get_the_ID(),"_rr_grs_cache_date",$feedupdate);
	} 
	//cache storing for widget
	else {
		$grs_conn['widget_cache'] = $gr_cache;
		$grs_conn['widget_lastupdate'] = $feedupdate;
		update_option('rr_grs_connection',$grs_conn);
	}
	
	return $gr_cache;
}

/**
 * google_reader_shared() echos the content of the google reader shared
 * 
 * @param bool $is_shortcode determines whether to use shortcode type output
 * @return is true upon success (content itself is echoed)... use rr_grs_fetch_items to store output and not echo
 */
function google_reader_shared($is_shortcode = false) {
	echo rr_grs_fetch_items($is_shortcode);
	return true;
}

/**
 * rr_grs_widget_control() initializes the widget for admin
 */
function rr_grs_widget_control() {
	// We need to grab any preset options
	$options = get_option("rr_grs_widget_options");
	if (!is_array($options)) $options = array('title' => 'Recommended Reading'); // No options? No problem! We set them here.

	if (isset($_POST['rr_grs_widget_submit'])) {
		$options['title'] = htmlspecialchars($_POST['rr_grs_widget_title']);
		update_option("rr_grs_widget_options", $options); // And we also update the options in the Wordpress Database
	}
	
	echo '
		<label for="rr_grs_widget_title">Widget Title:</label>
		<input type="text" id="rr_grs_widget_title" name="rr_grs_widget_title" value="'.$options['title'].'" />
		<input type="hidden" id="rr_grs_widget_submit" name="rr_grs_widget_submit" value="1" />
	';
} 

/**
 * rr_grs_widget_output() handles widget output
 */
function rr_grs_widget_output($args) 
{
	extract($args);  
	$options = get_option("rr_grs_widget_options");  
  	if (!is_array($options)) $options = array('title' => 'Recommended Reading');
	if (!$options['title']) $options['title'] = "Recommended Reading"; 
  	
	echo $before_widget;
	echo $before_title.$options['title'].$after_title;  
	echo rr_grs_fetch_items();
	echo $after_widget;  
}

/**
 * rr_grs_widget_init() registers the widget
 */
function rr_grs_widget_init() 
{
  register_sidebar_widget('Rec. Reading', 'rr_grs_widget_output');
  register_widget_control('Rec Reading', 'rr_grs_widget_control');
}

add_action("plugins_loaded", "rr_grs_widget_init");

/**
 * rr_grs_shortcode() processes the [recreading] shortcode
 */
function rr_grs_shortcode($atts) 
{
	extract(shortcode_atts(array('items' => ''),$atts));
	return rr_grs_fetch_items(true, $atts['items']);
}
add_shortcode('recreading', 'rr_grs_shortcode');

/**
 * clear_rr_grs_shortcode_cache() clears the shortcode cache for the current page when updated or saved
 */
function clear_rr_grs_shortcode_cache($post_ID) 
{
	delete_post_meta($post_ID,"_rr_grs_cache");
	delete_post_meta($post_ID,"_rr_grs_cache_date");
}
add_action('save_post','clear_rr_grs_shortcode_cache');

/**
 * rr_grs_stylize() adds the prefabbed / generic stylesheet for shortcode output if desired to possible templates 
 */
function rr_grs_stylize() 
{
	$shortcode = get_option('rr_grs_shortcode');
	if($shortcode['styles'] && (is_singular() || is_home() || is_archive())) 
		wp_enqueue_style('rr_grs_styles',WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/css/recreading.css');
}
add_action('get_header', 'rr_grs_stylize');


/**
 * rr_grs_plugin_actlinks() adds the settings link to the plugin page
 */
function rr_grs_plugin_actlinks( $links ) { 
 // Add a link to this plugin's settings page
 $plugin = plugin_basename(__FILE__);
 $settings_link = sprintf( '<a href="options-general.php?page=%s">%s</a>', $plugin, __('Settings') ); 
 array_unshift( $links, $settings_link ); 
 return $links; 
}
if(is_admin()) add_filter("plugin_action_links_".$plugin, 'rr_grs_plugin_actlinks' );

/**
 * rr_grs_admin_menu() sets up the menu link in the admin
 */ 
function rr_grs_admin_menu() 
{
	$plugin_page = add_options_page('Recommended Reading: Google Reader Shared Configuration', 'Rec. Reading', 8, __FILE__, 'rr_grs_options_page');
	add_action('admin_head-'.$plugin_page,'rr_grs_header');
}

function rr_grs_header()
{
	add_filter('contextual_help','rr_grs_context_help');
}

function rr_grs_context_help() 
{
	echo '
		<h5>Recommended Reading: Google Reader Shared</h5>
			<p>Recommended Reading: Google Reader Shared gets the shared items from your Google Reader account.</p>
			<p>To learn more about any of the options below, simply click the question mark next to the field label.</p>
		<h5>Frequently Asked Questions</h5>
			<p><em>Q: Why won\'t my ID validate, even though I\'m absolutely positive that it is right or it was looked up?</em></p>
			<p>A: The most common reason for ID validation faillure is a bad ID! If you are certain it is valid, make certain that your shared items are set for public sharing (not private) in your settings.</p>
			<p><em>Q: How do I share posts?</em></p>
			<p>A: Click the "Share" button at the bottom of any post from within Google Reader.</p>
			<p><em>Q: How do I make posts show only the items I shared before I published my post?</em></p>
			<p>A: You can embed your shared items in pages or posts by using the shortcode: just type in "[recreading]" (sans quotes) where you want it to appear. In the "Display on page / post (shortcode)" options at the bottom of this settings page, check the "As of publish date" option to only show items shared before you published your post.</p>
			<p><em>Q: I want to regularly share my latest items in my posts. Can Recommended Reading automatically show all the shared posts between the publication date of the current post and the last post that showed my shared items?</em></p>
			<p>A: Yes! First, check off the "As of publish date" option (discussed in the previous question). You can check the next option, "up to last post" to do just that!</p>
			<p><em>Q: Do I need to be at my computer to update my recommended reading list?</em></p>
			<p>A: Nope! Since the plug in gets posts from the Google Reader shared feed, you can update your feed from any Google Reader interface that supports sharing items. You can even recommend items from your mobile phone using Google\'s free mobile version or third party clients (I personally recommend Byline and MobileRSS for the iPhone).</p>
			<p><em>Q: I don\'t want to include my shared notes. Can I hide those?</em></p>
			<p>A: Yes... mostly. Uncheck the "show notes" option to hide the notes attached to shared items. Before version 4.0, you could also skip "standalone" notes. Google Reader treated standalone notes (a rarely used feature) as shared items, not annotations. Skipping them would dramatically reduce the performance benefits introduce in 4.0. Although we suspect this feature was rarely used, if there is interested, please let us know by leaving a comment on the plug-in support page.</p>
		<h5>Support</h5>
		<div class="metabox-prefs">
			<p><a href="http://www.get10up.com/plugins/recommended-reading-google-reader-shared-wordpress/" target="_blank">Recommended Reading: Google Reader Shared support</a></p>
			<p>This plug-in was developed by <a href="http://www.get10up.com" target="_blank">10up</a>, Web Development &amp; Strategy Experts located in Providence, Rhode Island in the United States. We develop plug-ins because we love working with systems like WordPress, and to generate interest in our business. If you like our plug-in, and know someone who needs web development work, be in touch!</p>
		</div>
	';	
}

add_action('admin_menu', 'rr_grs_admin_menu');


/**
 * rr_grs_options_page() displays the administrative configuration page
 */
function rr_grs_options_page() { 
?>
	<script type="text/javascript" language="javascript">
		function isValidEmail(str) {
			return (str.lastIndexOf(".") > 2) && (str.indexOf("@") > 0) && (str.lastIndexOf(".") > (str.indexOf("@")+1)) && (str.indexOf("@") == str.lastIndexOf("@"));
		} 
		
		function pulseMessage() {
			jQuery('#message').css("background","#FFFFE0 url(<?php echo WP_PLUGIN_URL; ?>/<?php echo basename(dirname(__FILE__)); ?>/images/loading.gif) 0 0 repeat-x");
	   	}
		
		function makeRequest(idCheck) {
			//default value for idCheck
			if (typeof idCheck == "undefined") idCheck=false;
			     	
			if (idCheck) {
			  	var googleID = jQuery('#gr_sid').val();
			  	if(googleID.length != 20 || isNaN(googleID)) {
			  		jQuery('#message').html("<p><strong>Not a valid Google ID: should be a 20 digit number.</strong></p>").fadeIn('slow');
					jQuery('#gr_sid').animate( { backgroundColor: '#FFCCCC' }, 'slow').focus();
			  		return false;
			  	}
			  	
				jQuery('#message').html("<p><strong>Please wait... confirming valid Google Reader ID.</strong></p>").fadeIn("slow", function() { pulseMessage(); });
				jQuery('#gr_sid').animate( { backgroundColor: '#FFFBCC' }, "slow" );
			} else {
				//confirm an email has been entered
	    		var gEmail = jQuery('#gr_un').val();
				if(!isValidEmail(gEmail)) {
					jQuery('#message').html("<p><strong>Enter a valid email address used for your Google account.</strong></p>").fadeIn('slow');
					jQuery('#gr_un').focus();
					return false;
				}
		  		
		  		//confirm a password was entered
				var gPasswd = jQuery('#gr_pw').val();
		  		if(gPasswd == "") {
		  			jQuery('#message').html("<p><strong>Please enter your account password.</strong></p>").fadeIn('slow');
					jQuery('#gr_pw').focus();
					return false;
				}
		  		
		  		//let the user know we're loading it
		  		jQuery('#message').html("<p><strong>Please wait... attempting to find your Google Reader ID.</strong></p>").fadeIn("slow", function() { pulseMessage(); });
				jQuery('#gr_sid').animate( { backgroundColor: '#FFFBCC' }, "slow" );
			}
	   	
			var url = "<?php echo WP_PLUGIN_URL; ?>/<?php echo basename(dirname(__FILE__)); ?>/getsid.php";
			if(idCheck) jQuery.get(url, { gID: googleID }, function(data) { handle_sid(data); });	
			else jQuery.get(url, { Email: gEmail, Passwd: gPasswd }, function(data) { handle_sid(data); });
		}
	
		function handle_sid(sidResponse) {
			jQuery('#message').css("background-image","none");
			if(sidResponse == "g0") {
				jQuery('#message').html("<p><strong>Google Reader ID was not valid: you have no items in your feed or do not have a public feed.</strong></p>");
  				jQuery('#gr_sid').animate( {backgroundColor: '#FFCCCC'}, 'slow');
            } else if(sidResponse == "g1") {
           		jQuery('#message').html("<p>Successfully validated ID!</p>");
  				jQuery('#gr_sid').animate( {backgroundColor: '#deffc5'}, 'slow');
            } else if(sidResponse == "0") {
				jQuery('#message').html("<p><strong>Google Reader account was not found, or there was an error trying to get user account information.</strong></p>");
  				jQuery('#gr_sid').animate( {backgroundColor: '#FFCCCC'}, 'slow').val("");
	   		} else {
	   			jQuery('#message').html("<p>Google Reader ID look up was successful!</p>");
  				jQuery('#gr_sid').animate( {backgroundColor: '#deffc5'}, 'slow').val(sidResponse);            
      		}
	   	}
	   	
	   	function secondaryOption(field) {
	   		var theField = jQuery(field);
	   		var nextOption = theField.parent().parent().next();
	   		
	   		//if value is checked or has content we want to show the field
	   		if (!theField.is(":checked") || jQuery.trim(theField.val()) == '') {
	   			nextOption.fadeOut("medium");
	   			theField.focus();
	   		} else nextOption.fadeIn("medium");
	   	}
	   
	   	function setMode(theMode) {
	   		if (typeof theMode == "undefined") theMode = 1;
  		  
			if (theMode == 1) {
  		  		jQuery('#gr_num').val("5");
				jQuery('#gr_content').attr('checked', false);
	        	jQuery('#gr_trim').val("").parent().parent().fadeOut("medium");
	        	jQuery('#gr_date').val("");
	        	jQuery('#gr_datep').val("").parent().parent().fadeOut("medium");
	        	jQuery('#gr_source').attr('checked', false);
	        	jQuery('#gr_sourcep').val("").parent().parent().fadeOut("medium");
	        	jQuery('#gr_open').attr('checked', true);
	        	jQuery('#gr_notes').attr('checked', false);
	        	jQuery('#gr_notep').val("").parent().parent().fadeOut("medium");
	        	jQuery('#gr_nofollow').attr('checked', true);
	        	jQuery('#gr_read_more_text').val("");
	        	jQuery('#gr_read_more_feed').attr('checked', false).parent().parent().fadeOut("medium");
	        } else {
  		  		jQuery('#gr_num').val("3");
		        jQuery('#gr_content').attr('checked', true);
		        jQuery('#gr_trim').val("210").parent().parent().fadeIn("medium");
		        jQuery('#gr_date').val("F j, Y");
		        jQuery('#gr_datep').val("Published:").parent().parent().fadeIn("medium");
		        jQuery('#gr_source').attr('checked', true);
		        jQuery('#gr_sourcep').val("Source:").parent().parent().fadeIn("medium");
		        jQuery('#gr_open').attr('checked', true);
		        jQuery('#gr_notes').attr('checked', true);
		        jQuery('#gr_notep').val("My Note: ").parent().parent().fadeIn("medium");
		        jQuery('#gr_nofollow').attr('checked', true);
		        jQuery('#gr_read_more_text').val("See all shared items");
	        	jQuery('#gr_read_more_feed').attr('checked', false).parent().parent().fadeIn("medium");
			}
	  	}
	</script>


	<div class="wrap">
		<div class="icon32" style="background: transparent url(<?php echo WP_PLUGIN_URL; ?>/<?php echo basename(dirname(__FILE__)); ?>/images/gr_icon.png) 3px 3px no-repeat;"><br /></div>
		<h2>Recommended Reading: Google Reader Shared Configuration</h2>
	
		<div class="updated" id="message" style="display: none;"></div>

		<div id="poststuff" style="margin-top: 20px;">
	
		<div class="postbox" style="width: 215px; min-width: 215px; float: right;">
			<h3 class="hndle">Support us</h3>
			<div class="inside">
				<p>Help support continued development of Recommended Reading and other plugins.</p>
				<p>The best thing you can do is refer someone looking for web development or strategy work <a href="http://www.get10up.com">to our company</a>.</p>
				<p>Short of that, here are other ways you can help:</p>
				<form method="post" action="https://www.paypal.com/cgi-bin/webscr" style="text-align: left;">
				<input type="hidden" value="_s-xclick" name="cmd"/>
				<input type="hidden" value="3377715" name="hosted_button_id"/>
				<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" name="submit" alt="PayPal - The safer, easier way to pay online!"/> <img height="1" border="0" width="1" alt="" src="https://www.paypal.com/en_US/i/scr/pixel.gif"/><br/>
				</form>
				<form method="post" action="options.php">
				<?php 
					settings_fields('rr_grs_options');
					$grs_conn = get_option('rr_grs_connection');
					$grs_prefs = get_option('rr_grs_prefs');
					$grs_shortcode  = get_option('rr_grs_shortcode');
				?>
				<p><input type="checkbox" value="1" name="rr_grs_prefs[link_love]" id="link_love"<?php if ($grs_prefs['link_love']) { echo ' checked="true"'; } ?> /> Include link to us [<a href="#" onclick="alert('Inserts a small link back to our site at the bottom of the output'); return false;" style="cursor: help;" title="Inserts a small link back to our site at the bottom of the output">?</a>]</p>
				<p><strong><a href="http://www.get10up.com/plugins/recommended-reading-google-reader-shared-wordpress/">Help &amp; support</a></strong></p>
			</div>
		</div>

		<div class="postbox" style="width: 350px;">
			<h3 class="hndle">My Google Reader ID</h3>
			<div class="inside">
				<table class="form-table" style="clear: none;" >
					<tr valign="top">
						<th scope="row">Google Reader ID [<a href="#" onclick="alert('Google Reader assigns a unique, 20 digit numeric ID to each user. To manually find the ID, open Google Reader, and click the Shared Items option, below Your Stuff, in the left navigation. Focus on the URL of the page: the numeric ID at the end, just after %2F, is your unique ID.'); return false;" style="cursor: help;">?</a>]</th>
						<td style="padding-bottom: 2px;"><input type="text" name="rr_grs_connection[id]" id="gr_sid" value="<?php echo $grs_conn['id']; ?>"  /></td>
					</tr>
					<tr valign="top">
						<th scope="row"></th>
						<td><input type="button" value="Validate ID" onclick="makeRequest(1)" class="button" /></td>
					</tr>
					
					<tr valign="top">
						<th scope="row" valign="top" colspan="2" style="border-top: 1px dashed #DFDFDF; padding-top: 15px;"><strong>Look up my Google Reader ID</strong></th>
					</tr>
					
					<?php if (function_exists(curl_init)) { ?>
					<tr valign="top">
						<th scope="row">Google Username</th>
						<td style="padding-bottom: 2px;"><input type="text" name="gr_un" id="gr_un" value="" autocomplete="off" /></td>
					</tr>
					<tr valign="top">
						<th scope="row">Google Password</th>
						<td style="padding-bottom: 2px;"><input type="password" name="gr_pw" id="gr_pw" value="" autocomplete="off" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"></th>
						<td><input type="button" value="Find my ID" onclick="makeRequest()" class="button" /></td>
					</tr>
					<?php } else { ?>
					<tr valign="top">
						<td colspan="2">Your server does not have <a href="http://www.php.net/manual/en/intro.curl.php" target="_blank">cURL</a> enabled. cURL is a widely supported PHP module used to load and process external web pages behind the scenes. While the WordPress API will let this plugin perform most operations without cURL, cURL is needed for the more complicated process of looking up your ID. You may still manually find and enter your ID. Click the question mark link above for instructions.</td>
					</tr>
					<?php } ?>
				</table>
			</div>
		</div>
	
	<div class="postbox" style="width: 350px;">
		<h3 class="hndle">Display Settings</h3>
		<div class="inside">
			<table class="form-table">
				<tr valign="top">
					<th scope="row" valign="top">Quick Set</th>
					<td>
						<input type="button" value="Simple" onclick="setMode(1)" class="button" />
						<input type="button" value="Detailed" onclick="setMode(2)" class="button" />
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row" valign="top" style="padding-top: 15px; border-top: 1px dashed #DFDFDF;">Number of Posts [<a href="#" onclick="alert('Leave this field blank or set it to 0 to show all items. Keep in mind that, depending on the number of items shared, this may have performance implications.'); return false;" style="cursor: help;">?</a>]</th>
					<td style="padding-top: 10px; border-top: 1px dashed #DFDFDF;"><input type="text" name="rr_grs_connection[item_count]" id="gr_num" value="<?php echo $grs_conn['item_count']; ?>" /></td>
				</tr>
				
				<tr valign="top">
					<th scope="row" valign="top" style="border-top: 1px dashed #DFDFDF;">Show Post Content [<a href="#" onclick="alert('Show the actual body of the shared item.'); return false;" style="cursor: help;">?</a>]</th>
					<td style="padding-top: 10px; border-top: 1px dashed #DFDFDF;"><input type="checkbox" value="1" name="rr_grs_prefs[content_show]" id="gr_content"<?php if ($grs_prefs['content_show']) { echo ' checked="true"'; } ?> onchange="secondaryOption(this);" /></td>
				</tr>
				<tr valign="top"<?php if(!$grs_prefs['content_show']) echo ' style="display: none;"'?>>
					<th scope="row" valign="top">Truncate Content [<a href="#" onclick="alert('Enter the number of characters you want to show from the post content / body. Leave it blank or set to 0 to show the entire post.'); return false;" style="cursor: help;">?</a>]</th>
					<td><input type="text" name="rr_grs_prefs[content_truncate]" id="gr_trim" value="<?php echo $grs_prefs['content_truncate']; ?>" /></td>
				</tr>
				
				<tr valign="top">
					<th scope="row" valign="top" style="border-top: 1px dashed #DFDFDF;">Show Date [<a href="#" onclick="alert('Show and format date of post. Leave it blank to hide the date. If used, must be in PHP date format.'); return false;" style="cursor: help;">?</a>]</th>
					<td valign="top" style="border-top: 1px dashed #DFDFDF;">
						<input type="text" name="rr_grs_prefs[date_format]" id="gr_date" value="<?php echo $grs_prefs['date_format']; ?>" style="width: 80px;" />	<input type="button" value="Reset" onclick="jQuery('#gr_date').val('F j, Y');" class="button" /><br />
						<span class="howto">Use <a href="http://php.net/date" rel="help" target="_blank">PHP date format</a>.</span>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" valign="top">Preface Date [<a href="#" onclick="alert('Optional text to show before the date (eg. Published On)'); return false;" style="cursor: help;">?</a>]</th>
					<td><input type="text" name="rr_grs_prefs[date_preface]" id="gr_datep" value="<?php echo $grs_prefs['date_preface']; ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row" valign="top" style="border-top: 1px dashed #DFDFDF;">Show Blog Source [<a href="#" onclick="alert('Show the name of the blog or feed this came from, with a link.'); return false;" style="cursor: help;">?</a>]</th>
					<td style="padding-top: 10px; border-top: 1px dashed #DFDFDF;"><input type="checkbox" value="1" name="rr_grs_prefs[source_show]" id="gr_source"<?php if ($grs_prefs['source_show']) { echo ' checked="true"'; } ?> onchange="secondaryOption(this);" /></td>
				</tr>
				<tr valign="top"<?php if(!$grs_prefs['source_show']) echo ' style="display: none;"'?>>
					<th scope="row" valign="top">Preface Source [<a href="#" onclick="alert('Optional text to show before the source (i.e. Source)'); return false;" style="cursor: help;" title="Optional text to show before the source (i.e. Source)">?</a>]</th>
					<td><input type="text" name="rr_grs_prefs[source_preface]" id="gr_sourcep" value="<?php echo $grs_prefs['source_preface']; ?>" /></td>
				</tr>
				
				<tr valign="top">
					<th scope="row" valign="top" style="border-top: 1px dashed #DFDFDF;">Open Links in New Window</th>
					<td style="padding-top: 10px; border-top: 1px dashed #DFDFDF;"><input type="checkbox" value="1" name="rr_grs_prefs[new_window]" id="gr_open"<?php if ($grs_prefs['new_window']) { echo ' checked="true"'; } ?> /></td>
				</tr>
				<tr valign="top">
					<th scope="row" valign="top">rel="nofollow" [<a href="#" onclick="alert('Adds this attribute and value to links; instructs some search engines not to influence ranking of the target in search engine index.'); return false;" style="cursor: help;">?</a>]</th>
					<td style="padding-top: 10px;"><input type="checkbox" value="1" name="rr_grs_prefs[no_follow]" id="gr_nofollow"<?php if ($grs_prefs['no_follow']) { echo ' checked="true"'; } ?> /></td>
				</tr>
				
				<tr valign="top">
					<th scope="row" valign="top" style="padding-bottom: 0; border-top: 1px dashed #DFDFDF;">Read More Link [<a href="#" onclick="alert('If you would like a read more link back to your shared item page at Google, enter the text you want to use for the link, ie Read More. Leave this field blank if you do not want to include the link.'); return false;" style="cursor: help;">?</a>]</th>
					<td style="padding-bottom: 0; border-top: 1px dashed #DFDFDF;"><input type="text" name="rr_grs_prefs[feed_link_text]" id="gr_read_more_text" value="<?php echo $grs_prefs['feed_link_text']; ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row" valign="top">Link to Feed [<a href="#" onclick="alert('By default, the read more link sends users to the formatted Google shared items page. If you would prefer to link directly to the Atom feed of shared items, check this option.'); return false;" style="cursor: help;">?</a>]</th>
					<td style="padding-top: 10px;"><input type="checkbox" value="1" name="rr_grs_prefs[feed_link_show]" id="gr_read_more_feed"<?php if ($grs_prefs['feed_link_show']) { echo ' checked="true"'; } ?> /></td>
				</tr>
			</table>
		</div>
	</div>
	
	<div class="postbox" style="width: 350px;">
		<h3 class="hndle">Shared Item Notes</h3>
		<div class="inside">
			<table class="form-table">
				<tr valign="top">
					<th scope="row" valign="top">Show Notes [<a href="#" onclick="alert('Google Reader lets you attach notes to shared items. This lets you display those notes. In the current version, this will not affect stand alone notes in your feed.'); return false;" style="cursor: help;">?</a>]</th>
					<td style="padding-top: 10px;"><input type="checkbox" value="1" name="rr_grs_prefs[notes_show]" id="gr_notes"<?php if ($grs_prefs['notes_show']) { echo ' checked="true"'; } ?> onchange="secondaryOption(this);" /></td>
				</tr>
				<tr valign="top"<?php if(!$grs_prefs['notes_show']) echo ' style="display: none;"'?>>
					<th scope="row" valign="top">Preface Note [<a href="#" onclick="alert('Optional text to show before the note (i.e. My Notes)'); return false;" style="cursor: help;">?</a>]</th>
					<td><input type="text" name="rr_grs_prefs[notes_preface]" id="gr_notep" value="<?php echo $grs_prefs['notes_preface']; ?>" style="width: 80px;" /></td>
				</tr>
				<tr valign="top">
					<td colspan="2"><p>Note: "stand alone" notes (notes not attached to posts) placed in your feed are handled by Google as shared items, not annotations, and will still appear.</p></td>
				</tr>
				
				<?php /*
				<tr valign="top">
					<th scope="row" valign="top">Skip Standalone Notes [<a href="#" onclick="alert('Google Reader lets you add standalone notes to your shared items feed that are not attached to any feed item. Checking this box allows you to skip these items in the output.'); return false;" style="cursor: help;">?</a>]</th>
					<td style="padding-top: 10px;"><input type="checkbox" name="rr_grs_prefs[notes_skip]" id="gr_skipnote"<?php if (get_option('gr_skipnote')) { echo ' checked="true"'; } ?> /></td>
				</tr> */ ?>
			</table>
		</div>
	</div>
	
	<div class="postbox" style="width: 350px;">
		<h3 class="hndle">Display on page / post (shortcode)</h3>
		<div class="inside">
			<p>You can display shared items on a page or post by simply typing <strong>[recreading]</strong> in the content (this is called "shortcode"). An optional attribute, 'items', sets the number of entries to display; for example, <strong>[recreading items=5]</strong>. Shortcode output uses the settings above, in addition the options below.</p>  
			
			<table class="form-table">
				
				<tr valign="top">
					<th scope="row" valign="top"><strong>As of publish date</strong> [<a href="#" onclick="alert('By default, the output will always be based on the latest shared items. This is ideal if you have a dedicated page for your shared items feed. However, if you will be regularly creating a post to highlight recent shared items, you will probably want to show the latest items from the date the post was published. Check this box to apply that logic. Note that this plugin caches the shared items for performance reasons. If you update your notes or the posts themselves are updated, you will need to update the post or page with the shortcode to see the changes. Setting changes made here will automatically flush the cache.'); return false;" style="cursor: help;">?</a>]</th>
					<td style="padding-top: 10px;"><input type="checkbox" value="1" name="rr_grs_shortcode[as_of_publish_date]" id="gr_publish_date"<?php if ($grs_shortcode['as_of_publish_date']) { echo ' checked="true"'; } ?> onchange="secondaryOption(this);" /></td>
				</tr>
				<tr valign="top"<?php if(!$grs_shortcode['as_of_publish_date']) echo ' style="display: none;"'?>>
					<th scope="row" valign="top" style="padding-top: 0;">... <em>up to last post</em> [<a href="#" onclick="alert('If you regularly share your items in a post, you may want to automatically show all shared items since the last post to use [recreading]. Check this option to show all shared items up until the publish date of the last post that has the [recreading] shortcode. Note that this will always override a specified number of posts, with the exception of the very first post to use the shortcode. Note that this option disables the read more link, even if set above, with the exception of the very first post to use the shortcode.'); return false;" style="cursor: help;">?</a>]</th>
					<td style="padding-top: 0;"><input type="checkbox" value="1" name="rr_grs_shortcode[up_to_last_post]" id="gr_publish_upto"<?php if ($grs_shortcode['up_to_last_post']) { echo ' checked="true"'; } ?> /></td>
				</tr>
			
				<tr valign="top">
					<th scope="row" valign="top">Don't truncate content [<a href="#" onclick="alert('You can override the trim setting above just for shortcode content.'); return false;" style="cursor: help;">?</a>]</th>
					<td style="padding-top: 10px;"><input type="checkbox" value="1" name="rr_grs_shortcode[truncate_override]" id="gr_sc_trim"<?php if ($grs_shortcode['truncate_override']) { echo ' checked="true"'; } ?> /></td>
				</tr>
				<tr valign="top">
					<th scope="row" valign="top" style="padding-top: 0;">... <em>and</em> show HTML [<a href="#" onclick="alert('The widget will strip all HTML out of the shared item content, including line and paragraph breaks. You may choose to allow HTML in shortcode output by checking this box. Note that you must also check the Don't Trim box above.'); return false;" style="cursor: help;">?</a>]</th>
					<td style="padding-top: 0;"><input type="checkbox" value="1" name="rr_grs_shortcode[html_show]" id="gr_sc_html"<?php if ($grs_shortcode['html_show']) { echo ' checked="true"'; } ?> /></td>
				</tr>
				<tr valign="top">
					<th scope="row" valign="top" style="padding-top: 0;">... ... <em>but</em> only simple tags [<a href="#" onclick="alert('Only allow very basic HTML: line breaks, paragraph breaks, bold tags, italic tags, links, and blockquotes.'); return false;" style="cursor: help;">?</a>]</th>
					<td style="padding-top: 0;"><input type="checkbox" value="1" name="rr_grs_shortcode[html_simple]" id="gr_sc_simplehtml"<?php if ($grs_shortcode['html_simple']) { echo ' checked="true"'; } ?> /></td>
				</tr>
				<tr valign="top">
					<th scope="row" valign="top" style="padding-top: 0;">... ... <em>and</em> hide images [<a href="#" onclick="alert('Many websites use styles to position their images in ways that will not come over with the feed content, resulting in odd image placement. Check this box to allow full HTML with the exception of images in the item content.'); return false;" style="cursor: help;">?</a>]</th>
					<td style="padding-top: 0;"><input type="checkbox" value="1" name="rr_grs_shortcode[html_noimg]" id="gr_sc_strpimg"<?php if ($grs_shortcode['html_noimg']) { echo ' checked="true"'; } ?> /></td>
				</tr>
				<tr valign="top">
					<th scope="row" valign="top">Use special styles [<a href="#" onclick="alert('For best visual results, we recommend you consult a stylesheet developer to modify your theme and give the output a professional look. Our company (10up) can provide such services. If you do not want to invest time or money, you can try our included stylesheet, but your mileage may vary.'); return false;" style="cursor: help;">?</a>]</th>
					<td><input type="checkbox" value="1" name="rr_grs_shortcode[styles]" id="gr_sc_styles"<?php if ($grs_shortcode['styles']) { echo ' checked="true"'; } ?> /></td>
				</tr>
			</table>
		</div>
	</div>
	
	</div>
	
	<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
	
	</form>
	</div>
  
<?php 
	} 
?>