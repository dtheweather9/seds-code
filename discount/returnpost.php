<?php

function objectToArrayreturnpost($d) {
		if (is_object($d)) {
			// Gets the properties of the given object
			// with get_object_vars function
			$d = get_object_vars($d);
		}
 
		if (is_array($d)) {
			/*
			* Return array converted to object
			* Using __FUNCTION__ (Magic constant)
			* for recursive call
			*/
			return array_map(__FUNCTION__, $d);
		}
		else {
			// Return array
			return $d;
		}
	}

	$seds_baseurl = str_replace("/wp-content/plugins/seds-code/discount/","", getcwd());
	$seds_baseurl = str_replace("/wp-content/plugins/seds-code/discount","", $seds_baseurl);
	$seds_baseurl = str_replace("\wp-content\plugins\seds-code\discount","", $seds_baseurl);
	
	include_once($seds_baseurl . '/wp-blog-header.php');
	include_once(ABSPATH  . '/wp-blog-header.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm.settings.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/CRM/Core/Config.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/civicrm.config.php');
		
	global $wpdb;
if(count($_POST) > 0) {
		if (wp_verify_nonce( $_POST['_wpnonce'], 'chapterdiscountcode' ) == 1) {
			
		$sql = "SELECT `id`,`code` FROM `cividiscount_item` WHERE `is_active` = 1";
		//Make Array from DSN
			$dsndecode = str_replace( "mysql://","",CIVICRM_DSN); $dsndecode = str_replace( "?new_link=true","",$dsndecode);
			$dsndecode = str_replace( ":","|",$dsndecode); $dsndecode = str_replace( "@","|",$dsndecode);
			$dsndecode = str_replace( "/","|",$dsndecode); $dsndecodearr = explode("|",$dsndecode);
		//Connect to new DSN
			$db2 = new wpdb($dsndecodearr[0],$dsndecodearr[1],$dsndecodearr[3],$dsndecodearr[2]);
			$myrows2 = $db2->get_results( $sql );
		$myrows2 = objectToArrayreturnpost($myrows2);
		$donotpass = 0;
		for ($i=0; $i<count($myrows2); $i++) {
			if ($myrows2[$i]['code'] == $_POST['discountcode']) {
				//Discount Passes
				$donotpass = $donotpass + 1;
				}
		}
		$permalink = get_permalink( $_POST['chapterregistration'] );
		if ($donotpass > 0 ) {
			$permalink = $permalink . "?chapterpost=1&returnurl=" . $_POST[returnurl] ;
		} else {
			$permalink = $permalink . "?chapterpost=0&returnurl=" . $_POST[returnurl] ;
		}
		//echo $permalink . "<br>";
		echo '<meta http-equiv="refresh" content="0; url=' . $permalink . '">';		
		}
		/*
		echo "_POST<pre>";
		print_r($_POST);
		echo "</pre>";
		echo "myrows2<pre>";
		print_r($myrows2);
		echo "</pre>";
		*/
		
	}
	