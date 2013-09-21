<?php
/*
Plugin Name: SEDS Code Addons
Plugin URI: http://www.seds.org
Description: A plugin to display SEDS Chapters using a shortcode and other misc setup files
Version: 0.02
Author: Dan Pastuf
Author URI: http://www.danpastuf.com
License: GPL2
*/

//Add Profile Edit
require_once(ABSPATH . '/wp-admin/includes/plugin.php');
//Add Chapter Leadershiplist
require_once(ABSPATH . '/wp-content/plugins/seds-code/leaderlist.php');
//Add [seds-chapters]
require_once(ABSPATH . '/wp-content/plugins/seds-code/chapterslug.php');
//Add [seds-map]
require_once(ABSPATH . '/wp-content/plugins/seds-code/sedsmap.php');
//Add [seds-getcard]
require_once(ABSPATH . '/wp-content/plugins/seds-code/cardstore.php');
//require_once(ABSPATH . '/wp-content/plugins/seds-code/cardgen.php');

//Add SEDS General Code
require_once(ABSPATH . '/wp-content/plugins/seds-code/sedscode_genmenu.php');	
add_options_page(__('SEDSCODE General Settings','menu-sedscode'), __('SEDS Options','menu-sedscode'), 'manage_options', 'sedscodesettings', 'sedscode_settings_page');

//Add [seds-jobsearch]
require_once(ABSPATH . '/wp-content/plugins/seds-code/sedsjobsearch.php');
//Add [seds-resume]
require_once(ABSPATH . '/wp-content/plugins/seds-code/sedsresume.php');
//Add [hidecc]
//require_once(ABSPATH . '/wp-content/plugins/seds-code/hidecc.php');  No longer used
//Add Function for SEDS Leaders
require_once(ABSPATH . '/wp-content/plugins/seds-code/chapterleaderssync.php');



function seds_install() { 
wp_schedule_event( time(), 'hourly', 'seds_leaders_hook' );
}

register_activation_hook( __FILE__, 'seds_install' );

function seds_deactivation() {
	wp_clear_scheduled_hook('seds_leaders_hook');
}

register_deactivation_hook(__FILE__, 'seds_deactivation');