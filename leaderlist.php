<?php

add_action('bp_init', 'seds_chapterleaders1');

function seds_chapterleaders1() {
if ( class_exists( 'BP_Group_Extension' ) ) { // Recommended, to prevent problems during upgrade or when Groups are disabled
 
    class SEDSLeadersedit extends BP_Group_Extension {
   	 function __construct() {
        	$this->name = 'Edit Chapter Leaders';
            $this->slug = 'seds-leaders';
            $this->create_step_position = 2;
            $this->nav_item_position = 2;
            $this->visibility = 'private';
            $this->enable_nav_item = false;
		}
        /**
         * The content of the My Group Extension tab of the group admin
         */
	function edit_screen() {
	    if ( !bp_is_group_admin_screen( $this->slug ) )
			return false;
    //Include Files
	include_once(ABSPATH  . '/wp-blog-header.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm.settings.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/CRM/Core/Config.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/civicrm.config.php');
	$config = CRM_Core_Config::singleton();
	global $wpdb;
	global $bp;
	//Current Buddypress Group
		$seds_currgroup = $bp->groups->current_group->id;
	//Run Query on DB
		$seds_querytext = 'SELECT * FROM `wp_bpcivi_groupsync` WHERE `buddypress_group` =' . $seds_currgroup;
		$seds_settinggroups = $wpdb->get_results($seds_querytext);
	//Assign to array from first membership found - oldest set effectively
		$seds_groupsettings = get_object_vars($seds_settinggroups[0]);
	//Perform Query of Civicrm API
		$seds_leaderparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
			'contact_id_b' => $seds_groupsettings['orgid'],'relationship_type_id' => 5,);
		$seds_leaderresult = civicrm_api('Relationship', 'get', $seds_leaderparams);
		
	//Diagnistics
		echo "Current Group information: <br><pre>";
		print_r($seds_leaderresult);
		echo "</pre>";
}
}
bp_register_group_extension( 'SEDSLeadersedit' );
}
}
