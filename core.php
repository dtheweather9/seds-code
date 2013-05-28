<?php
/*
Plugin Name: SEDS Chapter List
Plugin URI: http://www.seds.org
Description: A plugin to display SEDS Chapters using a shortcode
Version: 0.01
Author: Dan Pastuf
Author URI: http://www.danpastuf.com
License: GPL2
*/

//Add Profile Edit
require_once(ABSPATH . '/wp-admin/includes/plugin.php');
//Add Chapter Leadershiplist
require_once(ABSPATH . '/wp-content/plugins/seds-chapterlist/leaderlist.php');
//Add [seds-chapters]
require_once(ABSPATH . '/wp-content/plugins/seds-chapterlist/chapterslug.php');
