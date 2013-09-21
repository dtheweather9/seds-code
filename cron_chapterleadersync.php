#!/usr/bin/env php
<?php


//Add Code for sync to wpcrop and include
$seds_baseurl = str_replace("/wp-content/plugins/seds-code","", getcwd());
include_once($seds_baseurl . '/wp-blog-header.php');

//mail("dmpastuf@seds.org","Test.php Sent","Here's the Message, Test Php works in seds-code");
//Include Files
	include_once(ABSPATH  . '/wp-blog-header.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm.settings.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/CRM/Core/Config.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/civicrm.config.php');
	$config = CRM_Core_Config::singleton();
	global $wpdb;
//Declare variables
	$seds_relationtype = 5; //Sets the employee relationship for the api
//Find BP Organizations who should have leaders i.e. Chapters
	$seds_querytext = 'SELECT * FROM `wp_bpcivi_groupsync`';
	$seds_settinggroups = $wpdb->get_results($seds_querytext);
	//Assign nice array
	for ($x=0;$x<count($seds_settinggroups);$x++) {
		$seds_temparr = get_object_vars($seds_settinggroups[$x]);
		$seds_grouparray[$seds_temparr['orgid']] = $seds_temparr['buddypress_group'];
		$seds_grouparrayvalues[$x] = $seds_temparr['buddypress_group']; //Indexed Buddypress groups
		$seds_grouparrayvkeys[$x] = $seds_temparr['orgid']; //Indexed Civicrm groups
	}
//Find Employees of above groups; find what data should look like
	for ($i=0; $i<count($seds_grouparray); $i++) {
	
	$params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
	  'relationship_type_id' => $seds_relationtype,
	  'contact_id_b' => $seds_grouparrayvkeys[$i], );
	$result = civicrm_api('Relationship', 'get', $params);
	if ($result['count']>0) { //If there are memberships
		$seds_leaders = $result['values'];
		for ($j=0;$j<count($seds_leaders);$j++){  //Loop for each relationship
			//Find the Buddypress Group
				$seds_groupgid = $seds_leaders[$j]['contact_id_b'];
				$seds_groupgidsql = "SELECT * FROM `wp_bpcivi_groupsync` WHERE `orgid`=" . $seds_groupgid . " LIMIT 0, 30 ";
				$seds_groupgidsqlquery = $wpdb->get_results($seds_groupgidsql);
				$seds_groupgidsqlquery = get_object_vars($seds_groupgidsqlquery[0]); //Get first record and use for matching
				$seds_groupgid = $seds_groupgidsqlquery['buddypress_group'];
			//Find the Wordpress ID
				$seds_leaderufmatch = $seds_leaders[$j]['contact_id_a']; //TODO lookup the group id
				$params = array('version' => 3,'page' => 'CiviCRM', 'q' => 'civicrm/ajax/rest','sequential' => 1,
					'contact_id' => $seds_leaderufmatch,);
				$result = civicrm_api('UFMatch', 'get', $params);
				$seds_leaderufmatch = $result['values'][0]['uf_id'];
			if ($seds_leaders[$j]['custom_19'] == 0) {
			//The relationship is active
			  if(isset($seds_leaderufmatch)){
				$seds_baseleaders[$seds_groupgid][$seds_leaderufmatch] = 1;
			  }
			} else {
			//The membership is deleted
			  if(isset($seds_leaderufmatch)){
				$seds_baseleaders[$seds_groupgid][$seds_leaderufmatch] = 0;
			  }
			}
		}  //seds_baseleaders is the result of what the database should look like
	} else {
	//No Membership records exhist in civicrm for group
	}
	
	}
//Now that we have the result of what the array should look like, find what the database state is and change as needed

$n=0;
foreach($seds_baseleaders as $bpgroup => $seds_grouploop) {
	foreach ($seds_grouploop as $wpuser => $seds_groupvalue) {
		//Diagnostics
		  //echo "BP Group: $bpgroup  - User:  $wpuser - $seds_groupvalue<br>";		
	if($seds_groupvalue == 1) {	//User should be admin
		//Find what current status is
		$seds_exhistsql = "SELECT * FROM `wp_bp_groups_members` WHERE `group_id` = $bpgroup AND `user_id` = $wpuser LIMIT 0, 30 ";
		$seds_exhistsqlquery = $wpdb->get_results($seds_exhistsql);
			if (is_null($seds_exhistsqlquery[0])) {
			//No Record	
			//echo "<p> No Record Exhists </p>";
				//TODO Make Record if this happens
			} else {
			$seds_exhist = get_object_vars($seds_exhistsqlquery[0]);
			//elseif admin
			if($seds_exhist['is_admin'] == 1) {
			//Record Exhists and is admin
				//Do Nothing
		
			} else {
			//Record Exhists, is not admin
				//Make Admin
				$seds_makeadminsql = "UPDATE `wp_bp_groups_members` SET `is_admin` = 1 WHERE `wp_bp_groups_members`.`id` = " . $seds_exhist['id'];
				$seds_makeadminquery = $wpdb->get_results($seds_makeadminsql);
			}
		
		}
		
	} else {
		//relationship was deleted, make sure the record in buddypress is also removed from admin
		$seds_exhistsql = "SELECT * FROM `wp_bp_groups_members` WHERE `group_id` = $bpgroup AND `user_id` = $wpuser LIMIT 0, 30 ";
		$seds_exhistsqlquery = $wpdb->get_results($seds_exhistsql);
		if (is_null($seds_exhistsqlquery[0])) {
			//No Record	
			//echo "<p> No Record Exhists </p>";
				//TODO Make Record if this happens
		} else {
			$seds_exhist = get_object_vars($seds_exhistsqlquery[0]);
			//elseif admin
			if($seds_exhist['is_admin'] == 1) {
			//Record Exhists and is admin
				$seds_makeadminsql = "UPDATE `wp_bp_groups_members` SET `is_admin` = 0 WHERE `wp_bp_groups_members`.`id` = " . $seds_exhist['id'];
				$seds_makeadminquery = $wpdb->get_results($seds_makeadminsql);
			} else {
			//Record Exhists, is not admin
				//Do Nothing
				
			}
		
		}
	}
	//TODO - Remove anyone not on the list of employees (i.e. manual adds, manuel deleted, and other).  Make sure to add an exempt rule
	}
}
	
/*

//Diagnostics
	echo "Setting Group: <pre>";
	//print_r($seds_baseleaders);
	echo "<pre>";
	
*/
?>