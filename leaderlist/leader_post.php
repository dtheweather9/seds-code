<?php
	
//Include Files	
	$sedsll_baseurl = str_replace("/wp-content/plugins/seds-code/leaderlist","", getcwd());
	include_once($sedsll_baseurl . '/wp-blog-header.php');
	include_once(ABSPATH  . '/wp-blog-header.php');
	//WP NONCE check
		if(wp_verify_nonce( $_POST['_sedsleaderedit'], "sedsleaderedit") == false) { 
			 print 'Sorry, your nonce did not verify.';
   			exit;
		} else {
	//Finish
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm.settings.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/CRM/Core/Config.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/civicrm.config.php');
//Setup Memberships
	//Query order of officers
		$seds_officerparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
		'option_sort'=>"weight",
		'option_group_id' => 95,);
		$seds_officerresult = civicrm_api('OptionValue', 'get', $seds_officerparams);
		$seds_officerlist = $seds_officerresult['values'];
	//Current Buddypress Group
		//$seds_currgroup = $bp->groups->current_group->id;
		$seds_currgroup = $_POST['seds_currgroup'];
	//Run Query on DB
		$seds_querytext = 'SELECT * FROM `wp_bpcivi_groupsync` WHERE `buddypress_group` =' . $seds_currgroup;
		$seds_settinggroups = $wpdb->get_results($seds_querytext);
	//Assign to array from first membership found - oldest set effectively
		$seds_groupsettings = get_object_vars($seds_settinggroups[0]);
		$currentcivigroup = $seds_groupsettings['orgid'];
//Running
	//New Relationship Post Process
		if($_POST['NLeadersubmit'] == "New Leader") {
			$params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
				"contact_id_a" => $_POST['membid'],
				"contact_id_b" => $_POST['nChapterid'],
				"relationship_type_id" => "5",
				"start_date" => $_POST['newstartdate'],
				"end_date" => $_POST['newenddate'],
				"is_active" => 1,
				"is_permission_a_b" => 1,
				"is_permission_b_a" => 0,
				"custom_3" => $_POST['nrole'],
				"custom_4" =>  $_POST['ncustom_4'],
				"custom_5" => "0",
				);
			$seds_changecurrentvalues[] = civicrm_api('Relationship', 'create', $params);
		}
	//Change Relationship Post Process
		/*
		if($_POST['leaderChangesubmit'] == "Change") {
			//Get the old data
				$params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
					'id' => $_POST['Relationedit'],);
				$seds_changecurrentapi = civicrm_api('Relationship', 'get', $params);
				$seds_changeleaderresult[] = $seds_changecurrentapi;
				$seds_changecurrentvalues = array_merge($seds_changecurrentapi['values'][0],array( 'start_date' => $_POST['changebday'], 'end_date' => $_POST['changeendday'],));
			//Merge the new data
			$seds_changeleaderparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,);
			unset($seds_changecurrentvalues['id']);
			$seds_changeleaderparams = array_merge($seds_changeleaderparams,$seds_changecurrentvalues);
			$seds_newcontactdataresult = civicrm_api('Relationship', 'create', $seds_changeleaderparams);
			$seds_changeleaderresult[] = $seds_newcontactdataresult;
			//Delete Old Data
				if($_POST['Relationedit'] !== $seds_newcontactdataresult['id']) {
					$seds_changedeleteleaderparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
					'id' => $_POST['Relationedit'], //relationship id
					);
					$seds_changeleaderresult[] = civicrm_api('Relationship', 'delete', $seds_changedeleteleaderparams);
				}
		}
		*/
		if($_POST['leaderChangesubmit'] == "Change") {
				//echo "<br>Change Post: <pre>";
				//print_r($_POST);
				//echo "</pre>";
				
				$params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
					'id' => $_POST['Relationedit'],
				);
				$result = civicrm_api('Relationship', 'get', $params);
				$oldparams = $result['values'][0];
				$params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
				'id' => $_POST['Relationedit'],
				'start_date' => $_POST['changebday'],
				'end_date' => $_POST['changeendday'],
				'custom_5' => 0,
				);
				$newparams = array_merge($oldparams,$params);
				$delparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
					'id' => $_POST['Relationedit'],
				);
				$seds_changecurrentvalues[] = civicrm_api('Relationship', 'delete', $delparams);				
				unset($newparams['id']);
				$seds_changecurrentvalues[] = civicrm_api('Relationship', 'create', $newparams);
		}
	//Delete Relationship Post Process
		if($_POST['removeleaderDelete'] == "Delete") {
				$params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
					'id' => $_POST['Relationedit'],
				);
				$result = civicrm_api('Relationship', 'get', $params);
				$oldparams = $result['values'][0];
				$params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
				'id' => $_POST['Relationedit'],
				'start_date' => $_POST['changebday'],
				'end_date' => $_POST['changeendday'],
				'custom_5' => 1,
				);
				$newparams = array_merge($oldparams,$params);
				$delparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
					'id' => $_POST['Relationedit'],
				);
				$seds_changecurrentvalues[] = civicrm_api('Relationship', 'delete', $delparams);				
				unset($newparams['id']);
				$seds_changecurrentvalues[] = civicrm_api('Relationship', 'create', $newparams);
		}		
	//Undelete Relationship Post Process
		if($_POST['removeleaderDelete'] == "UnDelete") {
				$params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
					'id' => $_POST['Relationedit'],
				);
				$result = civicrm_api('Relationship', 'get', $params);
				$oldparams = $result['values'][0];
				$params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
				'id' => $_POST['Relationedit'],
				'start_date' => $_POST['changebday'],
				'end_date' => $_POST['changeendday'],
				'custom_5' => 0,
				);
				$newparams = array_merge($oldparams,$params);
				$delparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
					'id' => $_POST['Relationedit'],
				);
				$seds_changecurrentvalues[] = civicrm_api('Relationship', 'delete', $delparams);				
				unset($newparams['id']);
				$seds_changecurrentvalues[] = civicrm_api('Relationship', 'create', $newparams);
		}
	//Hard Delete for errors
		if($_POST['PermDelete'] == "PermDelete") {
			$seds_changeleaderparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
				'id' => $_POST['Relationedit'], //relationship id
			);
		$seds_changeleaderresult[] = civicrm_api('Relationship', 'delete', $seds_changeleaderparams);
		}
echo '<meta http-equiv="refresh" content="0;URL= '. $_POST['leaderredirect_url'] . '">';		
}
//Diagnistics
	/*
	echo plugins_url() . "/seds-code/leaderlist/leader_post.php";
	echo "<br>seds_changecurrentvalues: <pre>";
	print_r($seds_changecurrentvalues);
	echo "</pre>";	
	
	echo "<br>seds_changeleaderparams: <pre>";
	print_r($seds_changeleaderparams);
	echo "</pre>";	
	
	echo "<br>newparams: <pre>";
	print_r($newparams);
	echo "</pre>";		
	echo "<br>Post: <pre>";
	print_r($_POST);
	echo "</pre>";
	*/
//Return to Directed website	
	//header( 'Location: '. $_POST['leaderredirect_url'] ) ;
	//TODO a different method


/*
//POST result
		//Post is called for edit user
		  if(isset($_POST['Edituser'])) {
		  	echo "Post: <pre>";
			//print_r($_POST);
			echo "</pre>";
			echo '<table id="seds-leadertable">';
			echo '<form action="'.$actionurl.'" method="post">';
			echo '<input type="hidden" name="leaderredirect_url" value="' . get_site_url() . $_SERVER["REQUEST_URI"] . '">';
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
		  
		//New Relationship / leadership role
		if(isset($_POST['NLeadersubmit'])) {
			$seds_newleaderparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
			'relationship_type_id' => 5,
			'contact_id_a' => $_POST['membid'],
			'contact_id_b' => $_POST['nChapterid'],
			'start_date' => $_POST['newstartdate'],
			'end_date' => $_POST['newenddate'],
			'custom_3' => $_POST['nrole'],
			//'custom_18' => $_POST['nchapterrep'],
			);
			$seds_newleaderresult = civicrm_api('Relationship', 'create', $seds_newleaderparams);
		}
		//Delete Relationship
		if(isset($_POST['removeleaderDelete'])) {
			$seds_deleteleaderparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
			'entity_id' => $_POST['Relationedit'],
			'custom_5' => 1,
			);
			$seds_deleteleaderresult = civicrm_api('CustomValue', 'delete', $seds_deleteleaderparams);
		}
		//Undelete Relationship
		if(isset($_POST['Undeleteuser1'])) {
			$seds_undeleteleaderparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
			'entity_id' => $_POST['Relationundel'],
			'custom_5' => 0,
			);
			$seds_undeleteleaderresult = civicrm_api('CustomValue', 'create', $seds_undeleteleaderparams);
		}
*/


?>