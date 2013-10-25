<?php
// sedscode_settings_page() displays the page content for the Test settings submenu


function sedscode_settings_page() {

    //must check that the user has the required capability 
    if (!current_user_can('manage_options'))
    {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }
global $wpdb;
//Import the core Civicrm Files
  include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm.settings.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/CRM/Core/Config.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/civicrm.config.php');

//Show the display
echo '<div id=bpciviopts">';
echo '<div id="bpcivigroupmenu" style="min-width: 475px;float:left">';

    //Defaults
    	$default_apikey = "Enter Rostrum API Key Here";
    	$default_ossiminvalue = 1;
    	$default_ossimaxvalue = 20000;
    	$default_ossicurvalue = 7200;
    //Load Settings for apikey
    	$apikey = get_option('sedscode_rostrumapi');
    	if($apikey == false) {  //First time or if options are cleared
	    	update_option('sedscode_rostrumapi',$default_apikey);
	    	$apikey = $default_apikey;
    	} 
    	$apikeynum = $apikey;
    //Load Settings for Min OSSI NASA
    	$ossiminvalue = get_option('ossisync_minvalue');
    	if($ossiminvalue == false) {  //First time or if options are cleared
	    	update_option('ossisync_minvalue',$default_ossiminvalue);
	    	$ossiminvalue = $default_ossiminvalue;
    	}
    //Load Settings for Max OSSI NASA
    	$ossimaxvalue = get_option('ossisync_maxnvalue');
    	if($ossimaxvalue == false) {  //First time or if options are cleared
	    	update_option('ossisync_maxnvalue',$default_ossimaxvalue);
	    	$ossimaxvalue = $default_ossimaxvalue;
    	}
    //Load Settings for Current OSSI NASA
    	$ossicurvalue = get_option('ossisync_curvalue');
    	if($ossicurvalue == false) {  //First time or if options are cleared
	    	update_option('ossisync_curvalue',$default_ossicurvalue);
	    	$ossicurvalue = $default_ossicurvalue;
    	} 			
    //POST for General Options
    if(isset($_POST['generalopt'])) {
    //Post Response for API Key
    	if(isset($_POST['apikey'])) { //Organization Post Response
    		if( get_option('sedscode_rostrumapi') !== $_POST['apikey']) {
    		//New Organiation
    			update_option('sedscode_rostrumapi',$_POST['apikey']);
    			$apikeynum = get_option('sedscode_rostrumapi');
    		} 
    		if( get_option('ossisync_minvalue') !== $_POST['nasaminvalue']) {
    		//New NASA OSSI Min
    			update_option('ossisync_minvalue',$_POST['nasaminvalue']);
    			$ossiminvalue = get_option('ossisync_minvalue');
    		} 
    		if( get_option('ossisync_curvalue') !== $_POST['nasacurvalue']) {
    		//New NASA OSSI Cur
    			update_option('ossisync_curvalue',$_POST['nasacurvalue']);
    			$ossicurvalue = get_option('ossisync_curvalue');
    		} 
    		if( get_option('ossisync_maxnvalue') !== $_POST['nasamaxvalue']) {
    		//New NASA OSSI Max
    			update_option('ossisync_maxnvalue',$_POST['nasamaxvalue']);
    			$ossimaxvalue = get_option('ossisync_maxnvalue');
    		} 
    		
    	}
    }
 //Form
    	echo "<h3> SEDS Code Options</h3>";
    	echo "<div id='sedscode-adminmenu'>";
    	echo '<form action="" method="post">';
    	echo 'Rostrum API Key: <input type="text" name="apikey" value="' . $apikeynum . '"><br>';
    	echo "<div id='ossiadminmenu'>";
    	echo 'Minimum NASA Value: <input type="number" name="nasaminvalue" value="' . $ossiminvalue . '"><br>';
    	echo 'Current NASA Value: <input type="number" name="nasacurvalue" value="' . $ossicurvalue . '"><br>';
    	echo 'Max NASA Value: <input type="number" name="nasamaxvalue" value="' . $ossimaxvalue . '"><br>';
    	echo "</div>";
    	//Output Refreshtime
    	echo '<input type="submit" name="generalopt" value="Submit">';
    	echo '</form>';
    	echo "</div>";  	
    	
 //Form Option for seds-jobs
 //Post Response
 if(isset($_POST['ossisessionsubmit'])) {
 	for ($j=0; $j<count($_POST['periodname']); $j++) {
 		$postresponse[$_POST['periodid'][$j]] = array(
 			'periodname' => $_POST['periodname'][$j],
 			'periodstartdate' => $_POST['periodstartdate'][$j],
 			'periodenddate' => $_POST['periodenddate'][$j],
 			'deadline' => $_POST['deadline'][$j],
 			'periodstartdisplaydate' => $_POST['periodstartdisplaydate'][$j],
 			'periodenddisplaydate' => $_POST['periodenddisplaydate'][$j],
 			);
 	}
 	$sessionsoptionspost = update_option( "ossi-session-options", $postresponse );	
 	
 }
 
 //Regular options
	$sessions = get_option( "ossi-sessions", array() );
	
	$sessionsoptions = get_option( "ossi-session-options", array() );
 	echo "<h3> SEDS Code Options - SEDS Jobs</h3>";
 	echo "Estimate Dates based on <a href='https://intern.nasa.gov/ossi/web/public/main/index.cfm?solarAction=view&subAction=studentCal'>https://intern.nasa.gov/ossi/web/public/main/index.cfm?solarAction=view&subAction=studentCal</a><br>";
 	echo "Get Values from <a href='https://intern.nasa.gov/ossi/web/public/guest/searchOpps/'>https://intern.nasa.gov/ossi/web/public/guest/searchOpps/</a><br>";
 	echo "Use Date format yyyy-mm-dd" . "<br>";
 	echo '<form action="" method="post">';
 	echo "<table>";
 	echo "<tr><td>ID</td><td>Name</td><td>Start Date</td><td>End Date</td><td>Deadline</td><td>Display Start Date</td><td>Display End Date</td></tr>";
 	for ($i=0; $i<count( $sessions); $i++) {
 		echo "<tr>";
 		$hiddensession = '<input type="hidden" name="periodid[]" value="'.$sessions[$i].'">';
 		if ($sessions[$i] === "") {$hiddensession = '<input type="hidden" name="periodid[]" value="'. 0 .'">'; }
 		echo "<td>" . $sessions[$i]. $hiddensession . "</td>";
 		echo "<td>" . '<input type="text" name="periodname[]" value="'.$sessionsoptions[$sessions[$i]]['periodname'].'">' . "</td>";
 		echo "<td>" . '<input type="datetime" name="periodstartdate[]" value="'.$sessionsoptions[$sessions[$i]]['periodstartdate'].'">' . "</td>";
 		echo "<td>" . '<input type="datetime" name="periodenddate[]" value="'.$sessionsoptions[$sessions[$i]]['periodenddate'].'">' . "</td>";
 		echo "<td>" . '<input type="datetime" name="deadline[]" value="'.$sessionsoptions[$sessions[$i]]['deadline'].'">' . "</td>";
 		echo "<td>" . '<input type="datetime" name="periodstartdisplaydate[]" value="'.$sessionsoptions[$sessions[$i]]['periodstartdisplaydate'].'">' . "</td>";
 		echo "<td>" . '<input type="datetime" name="periodenddisplaydate[]" value="'.$sessionsoptions[$sessions[$i]]['periodenddisplaydate'].'">' . "</td>";
 		echo "</tr>";
 	}
 	echo "</table>";
 	echo '<input type="submit" name="ossisessionsubmit" value="Submit">';
 	echo '</form>';
 	//Insert Explanations here
 	
 	//Post Reponse for levels
 	$current_levels = get_option( "ossi-level", array() );
 	if(isset($_POST['ossilevelsubmit'])) {
 		for ($i=0;$i<count($_POST['levelid']);$i++) {
 			$levelsoptions[$_POST['levelid'][$i]] = $_POST['levelname'][$i];
 		}
 		echo "Post Level<pre>";
 		print_r($levelsoptions);
 		echo "</pre>";
 		update_option("ossi-level-options", $levelsoptions);
 	}
 	if(isset($_POST['ossilevelclear'])) {
 		delete_option("ossi-level-options");
 	}
 	//Field Markup for edu level
 	 	echo '<form action="" method="post">';
 	 	
 	 	$levelsoptions = get_option( "ossi-level-options", array() );
 	echo "<table>";
 	echo "<tr><td>Tag Name</td><td>Display Name</td></tr>";
 	for ($i=0; $i<count( $current_levels); $i++) {
 		echo "<tr>";
 		$hiddenlevels = '<input type="hidden" name="levelid[]" value="'.$current_levels[$i].'">';
 		if ($current_levels[$i] === "") {$hiddenlevels = '<input type="hidden" name="levelid[]" value="'. 0 .'">'; }
 		echo "<td>" . $current_levels[$i]. $hiddenlevels . "</td>";
 		if (strlen($levelsoptions[$current_levels[$i]]) > 2 ) {
	 		echo "<td>" . '<input type="text" name="levelname[]" value="'.$levelsoptions[$current_levels[$i]].'">' . "</td>";
 		} else {
 		//No Data - copy exhisting
 			echo "<td>" . '<input type="text" name="levelname[]" value="'. ucwords(strtolower(str_replace("_"," ",$current_levels[$i]))) .'">' . "</td>";
 		}
 		echo "</tr>";
 	}
 	echo "</table>";
 	echo '<input type="submit" name="ossilevelsubmit" value="Update Field"> ';
 	echo '<input type="submit" name="ossilevelclear" value="Delete">';
 	echo '</form>';
 	
 	
 //Diagnostics

	echo "_POST<pre>";
	print_r( $_POST );
	echo "</pre>";
 	echo "levelsoptions<pre>";
	print_r( $levelsoptions );
	echo "</pre>";
 	echo "current_levels<pre>";
	print_r( $current_levels );
	echo "</pre>";

echo "</div>";
echo "</div>";
}

?>
