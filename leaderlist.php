<?php

add_action('bp_init', 'seds_chapterleaders1');

function seds_chapterleaders1() {
if ( class_exists( 'BP_Group_Extension' ) ) { // Recommended, to prevent problems during upgrade or when Groups are disabled
 //Run to find out if a chapter
 	global $wpdb;
	global $bp;
 	$seds_ck_currgroup = $bp->groups->current_group->id;
 	if(is_numeric($seds_ck_currgroup)) { //Check if the group is set
 		$seds_ck_querytext = 'SELECT * FROM `wp_bpcivi_groupsync` WHERE `buddypress_group` =' . $seds_ck_currgroup;
		$seds_ck_settinggroups = $wpdb->get_results($seds_ck_querytext);
 	}
	
 //Function class call
    class SEDSLeadersedit extends BP_Group_Extension {
   	 function __construct() {
        	$this->name = 'Chapter Leadership';
            $this->slug = 'seds-leaders';
            $this->nav_item_position = 25;
            $this->visibility = 'public';
            //$this->enable_nav_item = false;
            $this->enable_create_step = false;
		}

	function display() {
    //Include Files
	include_once(ABSPATH  . '/wp-blog-header.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm.settings.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/CRM/Core/Config.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/civicrm.config.php');
	$config = CRM_Core_Config::singleton();
	global $wpdb;
	global $bp;
	//Query order of officers
		$seds_officerparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
		'option_sort'=>"weight",
		'option_group_id' => 94,);
		$seds_officerresult = civicrm_api('OptionValue', 'get', $seds_officerparams);
		$seds_officerlist = $seds_officerresult['values'];
	//Current Buddypress Group
		$seds_currgroup = $bp->groups->current_group->id;
	//Run Query on DB
		$seds_querytext = 'SELECT * FROM `wp_bpcivi_groupsync` WHERE `buddypress_group` =' . $seds_currgroup;
		$seds_settinggroups = $wpdb->get_results($seds_querytext);
	//Assign to array from first membership found - oldest set effectively
		$seds_groupsettings = get_object_vars($seds_settinggroups[0]);
	//Perform Query of Civicrm API
		$seds_leaderparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
			'contact_id_b' => $seds_groupsettings['orgid'],'relationship_type_id' => 5, //Employee Relationship
			'option_sort'=>"end_date",);
		$seds_leaderresult = civicrm_api('Relationship', 'get', $seds_leaderparams);
	//Cycle Through relationships and pull data from api call, assign data
	for ($i=0;$i<count($seds_leaderresult['values']);$i++) {
		$seds_leaderid[$i] = $seds_leaderresult['values'][$i]['contact_id_a'];
		$seds_leader_startdate[$i] = $seds_leaderresult['values'][$i]['start_date'];
		$seds_leader_enddate[$i] = $seds_leaderresult['values'][$i]['end_date'];
		$seds_leader_active[$i] = $seds_leaderresult['values'][$i]['is_active'];
		$seds_leader_title[$i] = $seds_leaderresult['values'][$i]['custom_17'];
		$seds_leader_titlel[$i] = $seds_leaderresult['values'][$i]['custom_17_1'];
		$seds_leader_chapterrep[$i] = $seds_leaderresult['values'][$i]['custom_18'];
		$seds_leader_chapterrepl[$i] = $seds_leaderresult['values'][$i]['custom_18_1'];
		//Pull Contact Data
			$seds_leadsparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
				'contact_id' => $seds_leaderid[$i],);
			$seds_leadsresult = civicrm_api('Contact', 'get', $seds_leadsparams);
		//Assign contact id info
		$seds_leader_displayname[$i] = $seds_leadsresult['values'][0]['display_name'];
		$seds_leader_addressid[$i] = $seds_leadsresult['values'][0]['address_id'];
		$seds_leader_emailid[$i] = $seds_leadsresult['values'][0]['email_id'];
		$seds_leader_email[$i] = $seds_leadsresult['values'][0]['email'];
	}
	//Find Years Covered
	$seds_mindate = date("Y",strtotime(min($seds_leader_startdate)));
	$seds_maxdate = date("Y",strtotime(max($seds_leader_enddate)));
	echo '<table id="seds-leadertable">';
	echo '<tr id="seds-leadertabletitlerow"><td id="seds-leadertabletitlerowlefttext">' . " Name </td><td> Role </td><td> Start Date </td><td> End Date</td><td> Email </td> </tr>";
	for ($j=0;$j<($seds_maxdate-$seds_mindate+1);$j++) { //Each Year
		$seds_leaderyear = $seds_mindate + $j;
		$seds_startyeardate = $seds_leaderyear . "-01-01";
		$seds_endyeardate = $seds_leaderyear . "-12-31";
		echo '<tr><td colspan="6" id="seds-leaderyear">' . $seds_leaderyear . "</td></tr>";
		for ($i=0;$i<count($seds_leaderresult['values']);$i++) {
			if( $seds_leader_startdate[$i] <=  $seds_endyeardate && $seds_leader_enddate[$i] >= $seds_startyeardate ) {
			if($seds_leaderresult['values'][$i]['custom_19'] == 0) {
				echo '<tr><td id="seds-leaderdisplayname">' . $seds_leader_displayname[$i] . "</td><td>";
				echo ucwords($seds_leader_title[$i]);
				echo "</td><td>";
				if($seds_leaderyear < date("Y",strtotime($seds_leader_enddate[$i]))) {
					echo $seds_leader_startdate[$i];
				}
				echo "</td><td>";
				if($seds_leaderyear > date("Y",strtotime($seds_leader_startdate[$i]))) {	
					echo $seds_leader_enddate[$i]; 
				}
				echo "</td><td>";
				echo $seds_leader_email[$i];
				echo "</td></tr>";
			}
			}
		}
	}
	echo "</table><br>";
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
	//Query order of officers
		$seds_officerparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
		'option_sort'=>"weight",
		'option_group_id' => 94,);
		$seds_officerresult = civicrm_api('OptionValue', 'get', $seds_officerparams);
		$seds_officerlist = $seds_officerresult['values'];
	//Current Buddypress Group
		$seds_currgroup = $bp->groups->current_group->id;
	//Run Query on DB
		$seds_querytext = 'SELECT * FROM `wp_bpcivi_groupsync` WHERE `buddypress_group` =' . $seds_currgroup;
		$seds_settinggroups = $wpdb->get_results($seds_querytext);
	//Assign to array from first membership found - oldest set effectively
		$seds_groupsettings = get_object_vars($seds_settinggroups[0]);
	//POST result
		//Post is called for edit user
		  if(isset($_POST['Edituser'])) {
		  	echo "Post: <pre>";
			print_r($_POST);
			echo "</pre>";
			echo '<table id="seds-leadertable">';
			echo '<form action="" method="post">';
			echo '<tr id="seds-leadertabletitlerow">';
			echo "<tr><td> Name </td><td> Role </td><td> Start Date </td><td> End Date </td><td> </td>";
			echo "</tr>";
			echo "<tr><td>";
			echo $_POST['leaderdname'];
			echo "</td><td>";
			echo ucwords($_POST['leadertitle']);
			echo "</td><td>";
			echo '<input type="date" name="changebday" value="' . $_POST['leaderstartdate'] . '">';
			echo "</td><td>";
			echo '<input type="date" name="changeendday" value="' . $_POST['leaderenddate'] . '">';
			echo "</td><td>";
			echo '<input type="hidden" name="Changeuser" value="' . $_POST['Edituser'] . '">';
			echo '<input type="hidden" name="Relationedit" value="' . $_POST['Relationedit'] . '">';
			echo '<input type="submit" name="' . "leaderChangesubmit" . '" value="' . "Change" . '">';
			echo '<input type="submit" name="' . "removeleaderDelete" . '" value="' . "Delete" . '">';
			echo "</td></tr>";
			echo '</form>';
			echo "</table>";
		  }
		//Change in Leadership info
		  if(isset($_POST['leaderChangesubmit'])) {
			$seds_changeleaderparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
			'start_date' => $_POST['changebday'],
			'end_date' => $_POST['changeendday'],
			'id' => $_POST['Relationedit'], );
			$seds_changeleaderresult = civicrm_api('Relationship', 'create', $seds_changeleaderparams);
		  	}
		//New Relationship / leadership role
		if(isset($_POST['NLeadersubmit'])) {
			$seds_newleaderparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
			'relationship_type_id' => 5,
			'contact_id_a' => $_POST['membid'],
			'contact_id_b' => $_POST['nChapterid'],
			'start_date' => $_POST['newstartdate'],
			'end_date' => $_POST['newenddate'],
			'custom_17' => $_POST['nrole'],
			'custom_18' => $_POST['nchapterrep'],
			);
			$seds_newleaderresult = civicrm_api('Relationship', 'create', $seds_newleaderparams);
		}
		//Delete Relationship
		if(isset($_POST['removeleaderDelete'])) {
			$seds_deleteleaderparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
			'entity_id' => $_POST['Relationedit'],
			'custom_19' => 1,);
			$seds_deleteleaderresult = civicrm_api('CustomValue', 'create', $seds_deleteleaderparams);
		}
		//Undelete Relationship
		if(isset($_POST['Undeleteuser1'])) {
			$seds_undeleteleaderparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
			'entity_id' => $_POST['Relationundel'],
			'custom_19' => 0,);
			$seds_undeleteleaderresult = civicrm_api('CustomValue', 'create', $seds_undeleteleaderparams);
		}
		
	//Perform Query of Civicrm API
		$seds_leaderparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
			'contact_id_b' => $seds_groupsettings['orgid'],'relationship_type_id' => 5,
			'option_sort'=>"end_date",);
		$seds_leaderresult = civicrm_api('Relationship', 'get', $seds_leaderparams);
	//Cycle Through relationships and pull data from api call, assign data
	for ($i=0;$i<count($seds_leaderresult['values']);$i++) {
		$seds_relationid[$i] = $seds_leaderresult['values'][$i]['id'];
		$seds_leaderid[$i] = $seds_leaderresult['values'][$i]['contact_id_a'];
		$seds_leader_startdate[$i] = $seds_leaderresult['values'][$i]['start_date'];
		$seds_leader_enddate[$i] = $seds_leaderresult['values'][$i]['end_date'];
		$seds_leader_active[$i] = $seds_leaderresult['values'][$i]['is_active'];
		$seds_leader_title[$i] = $seds_leaderresult['values'][$i]['custom_17'];
		$seds_leader_chapterrep[$i] = $seds_leaderresult['values'][$i]['custom_18'];
		//Pull Contact Data
			$seds_leadsparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
				'contact_id' => $seds_leaderid[$i],);
			$seds_leadsresult = civicrm_api('Contact', 'get', $seds_leadsparams);
		//Assign contact id info
		$seds_leader_displayname[$i] = $seds_leadsresult['values'][0]['display_name'];
		$seds_leader_addressid[$i] = $seds_leadsresult['values'][0]['address_id'];
		$seds_leader_emailid[$i] = $seds_leadsresult['values'][0]['email_id'];
		$seds_leader_email[$i] = $seds_leadsresult['values'][0]['email'];
	}
	//Find Years Covered
	$seds_mindate = date("Y",strtotime(min($seds_leader_startdate)));
	$seds_maxdate = date("Y",strtotime(max($seds_leader_enddate)));
	echo '<table id="seds-leadertable">';
	echo '<tr id="seds-leadertabletitlerow"><td id="seds-leadertabletitlerowlefttext">' . " Name </td><td> Role </td><td> ID </td><td> Start Date </td><td> End Date</td><td> Email </td><td> Edit / Delete </td></tr>";
	for ($j=0;$j<($seds_maxdate-$seds_mindate+1);$j++) { //Each Year
		$seds_leaderyear = $seds_mindate + $j;
		$seds_startyeardate = $seds_leaderyear . "-01-01";
		$seds_endyeardate = $seds_leaderyear . "-12-31";
		echo '<tr><td colspan="7" id="seds-leaderyear">' . $seds_leaderyear . "</td></tr>";
		for ($i=0;$i<count($seds_leaderresult['values']);$i++) { //Each Relationship
			if( $seds_leader_startdate[$i] <=  $seds_endyeardate && $seds_leader_enddate[$i] >= $seds_startyeardate ) {
			if($seds_leaderresult['values'][$i]['custom_19'] == 0) {
				echo '<tr><td id="seds-leaderdisplayname">' . $seds_leader_displayname[$i] . "</td><td>";
				echo ucwords($seds_leader_title[$i]);
				echo "</td><td>";
				echo $seds_leaderid[$i];
				echo "</td><td>";
				if($seds_leaderyear < date("Y",strtotime($seds_leader_enddate[$i]))) {
					echo $seds_leader_startdate[$i];
				}
				echo "</td><td>";
				if($seds_leaderyear > date("Y",strtotime($seds_leader_startdate[$i]))) {	
					echo $seds_leader_enddate[$i]; 
				}
				echo "</td><td>";
				echo $seds_leader_email[$i];
				echo "</td><td>";
				
				echo '<form action="" method="post">';
					echo '<input type="hidden" name="Edituser" value="' . $seds_leaderid[$i] . '">';
					echo '<input type="hidden" name="Relationedit" value="' . $seds_relationid[$i] . '">';
					echo '<input type="hidden" name="leadertitle" value="' . $seds_leader_title[$i] . '">';
					echo '<input type="hidden" name="leaderdname" value="' . $seds_leader_displayname[$i] . '">';
					echo '<input type="hidden" name="leaderstartdate" value="' . $seds_leader_startdate[$i] . '">';
					echo '<input type="hidden" name="leaderenddate" value="' . $seds_leader_enddate[$i] . '">';
					echo '<input type="submit" name="' . $seds_leaderid[$i] . '" value="' . "Edit" . '">';
				echo "</form>";
				echo "</td></tr>";
			}
			} //End of Deleted Loop
		}
	}
	echo "</table><br></form>";
	//Pull Custom Dropdowns
	$seds_leadershipdropparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
		'option_sort'=>"weight",
		'option_group_id' => 94,);
	$seds_leadershipdropresult = civicrm_api('OptionValue', 'get', $seds_leadershipdropparams);
	$seds_leadershipdrop = $seds_leadershipdropresult['values'];
	//Form for adding relationships / officers
	echo '<form action="" method="post">';
	echo "<table>";
	echo '<tr><td colspan="2"><h3>New Leadership</h3></td></tr>';
	echo '<tr><td> I.D. </td><td><input type="number" name="membid" required> </td></tr>';
	echo '<tr><td> Role </td><td>';
	echo '<select name="nrole" required>';
	for($z=0;$z<count($seds_leadershipdrop);$z++) {
		if($seds_leadershipdrop[$z]['is_active'] = 1) {
			echo '<option value="' . $seds_leadershipdrop[$z]['value'] . '">' . $seds_leadershipdrop[$z]['label'] . '</option>';
		}
	}
	echo '</select>';
	echo '</td></tr>';
	echo '<tr><td> Chapter Representative </td><td><input type="radio" name="nchapterrep" id="1" value="1"> Yes <br> <input type="radio" name="nchapterrep" id="2" value="0"> No</td></tr>';
	echo '<tr><td> Start Date </td><td><input type="date" name="newstartdate" required></td></tr>';
	echo '<tr><td> End Date </td><td><input type="date" name="newenddate" required></td></tr>';
	echo "</table>";
	echo '<input type="hidden" name="nChapterid" value="' . $seds_groupsettings['orgid'] . '">';
	echo '<input type="submit" name="NLeadersubmit" value="' . "New Leader" . '">';
	echo "</form>";
	
	//Deleted Members Table Button
	echo '<form action="" method="post">';
	echo '<input type="submit" name="undel1" value="' . "Show Deleted" . '">';
	echo "</form>";
	//Deleted Members Table
	if(isset($_POST['undel1'])) {
	echo '<table id="seds-leadertable">';
	echo '<tr id="seds-leadertabletitlerow"><td id="seds-leadertabletitlerowlefttext">' . " Name </td><td> Role </td><td> ID </td><td> Start Date </td><td> End Date</td><td> Email </td><td> Edit / Delete </td></tr>";
	for ($j=0;$j<($seds_maxdate-$seds_mindate+1);$j++) { //Each Year
		$seds_leaderyear = $seds_mindate + $j;
		$seds_startyeardate = $seds_leaderyear . "-01-01";
		$seds_endyeardate = $seds_leaderyear . "-12-31";
		echo '<tr><td colspan="7" id="seds-leaderyear">' . $seds_leaderyear . "</td></tr>";
		for ($i=0;$i<count($seds_leaderresult['values']);$i++) { //Each Relationship
			if( $seds_leader_startdate[$i] <=  $seds_endyeardate && $seds_leader_enddate[$i] >= $seds_startyeardate ) {
			if($seds_leaderresult['values'][$i]['custom_19'] == 1) {
				echo '<tr><td id="seds-leaderdisplayname">' . $seds_leader_displayname[$i] . "</td><td>";
				echo ucwords($seds_leader_title[$i]);
				echo "</td><td>";
				echo $seds_leaderid[$i];
				echo "</td><td>";
				if($seds_leaderyear < date("Y",strtotime($seds_leader_enddate[$i]))) {
					echo $seds_leader_startdate[$i];
				}
				echo "</td><td>";
				if($seds_leaderyear > date("Y",strtotime($seds_leader_startdate[$i]))) {	
					echo $seds_leader_enddate[$i]; 
				}
				echo "</td><td>";
				echo $seds_leader_email[$i];
				echo "</td><td>";
				
				echo '<form action="" method="post">';
					echo '<input type="hidden" name="Undeleteuser1" value="' . $seds_leaderid[$i] . '">';
					echo '<input type="hidden" name="Relationundel" value="' . $seds_relationid[$i] . '">';
					echo '<input type="hidden" name="leadertitle" value="' . $seds_leader_title[$i] . '">';
					echo '<input type="hidden" name="leaderdname" value="' . $seds_leader_displayname[$i] . '">';
					echo '<input type="hidden" name="leaderstartdate" value="' . $seds_leader_startdate[$i] . '">';
					echo '<input type="hidden" name="leaderenddate" value="' . $seds_leader_enddate[$i] . '">';
					echo '<input type="submit" name="' . $seds_leaderid[$i] . '" value="' . "Un-Delete" . '">';
				echo "</form>";
				echo "</td></tr>";
			}
			} //End of Deleted Loop
		}
	}
	echo "</table><br></form>";
	}
	
	/*//Diagnostics
	echo "Post: <pre>";
	print_r($_POST);
	echo "</pre>";
	
	echo "Relationships: <pre>";
	print_r($seds_leaderresult);
	echo "</pre>"; */

} //End of Edit Screen

} //End of Class
	if(count($seds_ck_settinggroups) > 0) { //Make it so that the group exension is only used for chapter
		bp_register_group_extension( 'SEDSLeadersedit' );
	}
}
}
