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
    //Load Settings for OrgID
    	$apikey = get_option('sedscode_rostrumapi');
    	if($apikey == false) {  //First time or if options are cleared
	    	update_option('sedscode_rostrumapi',$default_apikey);
	    	$apikey = $default_apikey;
    	} 
    //Post Response for API Key
    	if(isset($_POST['apikey'])) { //Organization Post Response
    		if( get_option('sedscode_rostrumapi') !== $_POST['apikey']) {
    		//New Organiation
    			update_option('sedscode_rostrumapi',$_POST['apikey']);
    			$apikeynum = get_option('sedscode_rostrumapi');
    		} 
    		
    	}
 //Form
    	echo "<h3> SEDS Code Options</h3>";
    	echo '<form action="" method="post">';
    	echo 'Rostrum API Key: <input type="text" name="apikey" value="' . $apikeynum . '"><br>';
    	//Output Refreshtime
    	echo '<input type="submit" value="Submit">';
    	echo '</form>';
echo "</div>";
echo "</div>";
 
}

?>
