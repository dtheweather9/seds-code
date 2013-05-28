<?php
function seds_chapters( $atts ){
  //Bring in the API and set base values
	$seds_baseurl = str_replace("/wp-content/plugins/seds-chapterlist","", getcwd());
	include_once($seds_baseurl . '/wp-blog-header.php');
	include_once(ABSPATH  . '/wp-blog-header.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm.settings.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/CRM/Core/Config.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/civicrm.config.php');
	$config = CRM_Core_Config::singleton();
	$seds_membershiptype = 1; //Membership type used by seds
//Call Membership list
	$seds_params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
		'membership_type_id' => $seds_membershiptype,);
	$seds_result = civicrm_api('Membership', 'get', $seds_params);
	$seds_resultvalues = $seds_result['values'];
	
	//Set the first loop
	
	//Run for each chapters membership
	for ($i=0;$i<count($seds_resultvalues);$i++) { //Loop through each membership and look up contact data
		if ($seds_resultvalues[$i]['status_id'] == 1 || $seds_resultvalues[$i]['status_id'] == 2 || $seds_resultvalues[$i]['status_id'] == 3) { //Loop through and see if the membership is new, current, or in grace
		//Get General Information
		$seds_chapterparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
			'contact_id' => $seds_resultvalues[$i]['contact_id'],);
		$seds_chapterresult = civicrm_api('Contact', 'get', $seds_chapterparams);
		$seds_chaptervalues = $seds_chapterresult['values'];
		//Get custom Information
		$seds_customparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
			'entity_id' => $seds_resultvalues[$i]['contact_id'],);
		$seds_customresult = civicrm_api('CustomValue', 'get', $seds_customparams);
		$seds_customvalues = $seds_customresult['values'];
		//Get website information
		$seds_webparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
			'contact_id' => $seds_resultvalues[$i]['contact_id'],);
		$seds_webresult = civicrm_api('Website', 'get', $seds_webparams);
		$seds_webresultvalues = $seds_webresult['values'];
		
		//Info to use
			//Set foundedline cleanly
			$seds_titleext = $seds_customvalues[1]['latest'] . "&#10; Founded: ". $seds_customvalues[2]['latest']; //TODO: Make a mormalish file
		$seds_titleline = '<tr title="' . $seds_titleext . '">';
		$seds_row[$i] = $seds_titleline . '<td>' . '<a href = "' . $seds_webresultvalues[0]['url'] . '">' . $seds_chaptervalues[0]['display_name'] . "</a><br>" . $seds_customvalues[0]['latest'] . "</td><td>" . $seds_chaptervalues[0]['email'] . "</td></tr>";
		$seds_chaptername[$i] = $seds_chaptervalues[0]['display_name'];
		} //end if loop
	}
	//Generate Desired Array of Ordered Chapters
	asort($seds_chaptername);
	$seds_sortedarray = array_keys($seds_chaptername);
	//Output return
	$seds_rowout1 = '<table style="width: 100%" border="1">'; //Start the data field
	$seds_rowout2 = $seds_rowout1 . "<tr><td><h4>Chapter</h4></td><td><h4>Contact</h4></td></tr>"; //Output the titles
	$seds_rowinfo = $seds_rowout2;
	for ($i=0;$i<count($seds_sortedarray);$i++) {
		$seds_rowinfo = $seds_rowinfo . $seds_row[$seds_sortedarray[$i]];
	}
	$seds_rowinfo = $seds_rowinfo .  "</table>"; //Add the table end
	return $seds_rowinfo;
}
add_shortcode( 'seds-chapters', 'seds_chapters' );
