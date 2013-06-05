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