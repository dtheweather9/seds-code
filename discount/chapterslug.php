<?php
function seds_chaptersdiscount( $atts ){
  //Bring in the API and set base values
	/*
	$seds_baseurl = str_replace("/wp-content/plugins/seds-chapterlist","", getcwd());
	include_once($seds_baseurl . '/wp-blog-header.php');
	include_once(ABSPATH  . '/wp-blog-header.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm.settings.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/CRM/Core/Config.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/civicrm.config.php');
	$config = CRM_Core_Config::singleton();
	*/
	//Page
		$regularregistration = 9526;
		$chapterregistration = 11816;
		
	//Postreturn
	
	//What kind of loop
		if($regularregistration == get_queried_object_id() ) { //Regular Registration Page
			$htmlreturn = "";
			//$htmlreturn .= "<br>Registration Page ID: " .  get_queried_object_id();
			$htmlreturn .= "<br>";
			$htmlreturn .= '<form action="'.plugins_url() . "/seds-code/discount/returnpost.php" .'" method="post">';
			$returnurl = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
			$htmlreturn .= wp_nonce_field( 'chapterdiscountcode' );
			$htmlreturn .= '<input type="hidden" name="returnurl" value="'.$returnurl.'">';
			$htmlreturn .= '<input type="hidden" name="regularregistration" value="'.$regularregistration.'">';
			$htmlreturn .= '<input type="hidden" name="chapterregistration" value="'.$chapterregistration.'">';
			$htmlreturn .= 'Chapter Discount Code: <input type="text" name="discountcode">';
			$htmlreturn .= '<input type="submit" value="Apply Discount">';
			$htmlreturn .= '</form>';
			
		}
		if($chapterregistration == get_queried_object_id() ) { //Chapter Registration Page
			if (!($_GET[chapterpost] == 1)) {
				echo '<div style="position:absolute;top:0;left:0;width:100%;height:100%;z-index:1000;"> </div>';
				echo '<meta http-equiv="refresh" content="0; url=' . site_url() . '">';
				//echo $_GET[chapterpost] . "<br>";
			}
		}
	
	//Test
		//$htmlreturn = "<br>Page ID: " .  get_queried_object_id();
		//$htmlreturn .= "<br>";
		
		
	return $htmlreturn;
}
add_shortcode( 'seds-chaptersdiscount', 'seds_chaptersdiscount' );