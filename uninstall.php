<?php
//uninstall script

//protection
if(!defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN')) exit();

//clear shortcode cache(s)
global $wpdb; 
$wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key = '_rr_grs_cache' OR meta_key = '_rr_grs_cache_date';");

//remove options
delete_option('rr_grs_connection');
delete_option('rr_grs_prefs');
delete_option('rr_grs_shortcode');
delete_option('rr_grs_widget_options');
?>