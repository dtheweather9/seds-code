<?php
$sedschapt_baseurl = str_replace("/wp-content/plugins/seds-code","", getcwd());
	include_once($sedschapt_baseurl . '/wp-blog-header.php');
	include_once(ABSPATH  . '/wp-blog-header.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm.settings.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/CRM/Core/Config.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/civicrm.config.php');
	$config = CRM_Core_Config::singleton();
	$sedschapt_membershiptype = 1; //Membership type used by seds
//Call Membership list
	$sedschapt_params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
		'membership_type_id' => $sedschapt_membershiptype,);
	$sedschapt_result = civicrm_api('Membership', 'get', $sedschapt_params);
	$sedschapt_resultvalues = $sedschapt_result['values'];
	
	//Run for each chapters membership
	for ($i=0;$i<count($sedschapt_resultvalues);$i++) { //Loop through each membership and look up contact data
	$sedschapt_params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
		'contact_id' => $sedschapt_resultvalues[$i]['contact_id'],);
	$sedschapt_contactresult = civicrm_api('Contact', 'get', $sedschapt_params);
	
	//Attach values
	//TODO: Add if statements for chapter type
		$sedschap_output[$sedschapt_contactresult['values'][0]['contact_id']] = $sedschapt_contactresult['values'][0]['display_name'];
	
	}
	
	$sedschap_jsonoutput = json_encode($sedschap_output);
	echo $sedschap_jsonoutput;