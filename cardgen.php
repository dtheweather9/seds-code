<?php
//Initialize
//header ('Content-Type: image/png');
function seds_getcard($atts) {
	$cid = $atts['cid'];

$cardgen = str_replace("/wp-content/plugins/seds-code","", getcwd());

	include_once($cardgen . '/wp-blog-header.php');
	include_once(ABSPATH  . '/wp-blog-header.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm.settings.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/CRM/Core/Config.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/civicrm.config.php');
	$config = CRM_Core_Config::singleton();
//ImageDumpLocation
	$img_location = ABSPATH  . 'wp-content/plugins/' . "files/civicrm/custom/userid/";
//Gather Data from civicrm
	//$cid = $_GET["cid"];
	
	$membership_params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
		'contact_id' => $cid,
		'membership_type_id' => 46, //SEDS-USA Membership Data for dates
	);
	$sedsusamembership_result = civicrm_api('Membership', 'get', $membership_params);
	
	$membership_params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
		'contact_id' => $cid,
	);
	$membership_result = civicrm_api('Membership', 'get', $membership_params);
	$membership_result = $membership_result['values'];
	for($i=0;$i<count($membership_result);$i++){
		$findoldest[$i] = strtotime("YY-MM-DD",$membership_result[$i]['start_date']);
	}
	
	asort($findoldest);
	$findoldest = array_keys($findoldest);
	$chaptermembership = $findoldest[0];
	if ($membership_result[$chaptermembership]['membership_type_id'] == 46) {
		$chaptermembership = $findoldest[1];
	}
	$member_record_id = $membership_result[$chaptermembership]['membership_id'];
	$membership_type_id = $membership_result[$chaptermembership]['membership_type_id'];
	$chapter_record_id = $membership_result[$chaptermembership]['membership_id'];
	//Member Type Record
		$membershiptype_params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
			'id' => $membership_type_id,
		);
		$membershiptype_result = civicrm_api('MembershipType', 'get', $membershiptype_params);
		$membership_org = $membershiptype_result['values'][0]['member_of_contact_id'];
	//Contact Organization Get
		$groupcontact_params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
			'contact_id' => $membership_org,
		);
		$groupcontact_result = civicrm_api('Contact', 'get', $groupcontact_params);
	//Contact Get
		$memberinfo_params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
			'contact_id' => $cid,
		);
		$memberinfo_result = civicrm_api('Contact', 'get', $memberinfo_params);
	
//Assign Data
	
	$dataout['cid'] = $cid;
	$dataout['memberstart'] = $sedsusamembership_result['values'][0]['membership_start_date'];
	$dataout['memberend'] = $sedsusamembership_result['values'][0]['membership_end_date'];
	$dataout['org_nickname'] = $groupcontact_result['values'][0]['nick_name']; //Nickname
	$dataout['last_name'] = $memberinfo_result['values'][0]['last_name'];
	$dataout['middle_name'] = $memberinfo_result['values'][0]['middle_name'];
	$dataout['first_name'] = $memberinfo_result['values'][0]['first_name'];
	$dataout['image_URL'] = $memberinfo_result['values'][0]['image_URL'];
//Create Image
	// 675 x 425 @ 200 DPI = 3.375” x 2.125
	//$membercard = imagecreatetruecolor(675,425);
	//Imagesset
		$scale_factor = 1.25;
		$whitebackalpha = .40;
	//Create Images	
		$membercard = imagecreatefrompng($img_location . "SEDSCardBackground.png");
		$sedslogo = imagecreatefrompng($img_location . "SEDS-Logo-main.png");
		
		$photo_imagesize = getimagesize($memberinfo_result['values'][0]['image_URL']);
		
		switch( $photo_imagesize['mime']) {
			case 'image/png': $memberimage = imagecreatefrompng($memberinfo_result['values'][0]['image_URL']);
			break;
			case 'image/jpeg': $memberimage = imagecreatefromjpeg($memberinfo_result['values'][0]['image_URL']);
			break;
			case 'image/pjpeg': $memberimage = imagecreatefromjpeg($memberinfo_result['values'][0]['image_URL']);
			break;
			case 'image/gif': $memberimage = imagecreatefromgif($memberinfo_result['values'][0]['image_URL']);
			break;
			//default: die();  
			}
		
	//Define Colors
		$red = imagecolorallocate($membercard, 255, 0, 0);
		$white = imagecolorallocate($membercard, 255, 255, 255);
		$black = imagecolorallocatealpha($membercard, 0, 0, 0,50);
		
		$whiteback = imagecolorallocatealpha($membercard, 255, 255, 255,127*$whitebackalpha );
		$grayback = imagecolorallocatealpha($membercard, 119, 119, 119,127*$whitebackalpha );
		$whiteback2 = imagecolorallocatealpha($membercard, 255, 255, 255,127*.1 );
	//Put SEDS Logo In background
		$sedslogo_x = 850;
		$sedslogo_y = 100;
		imagecopyresized($membercard,$sedslogo, $sedslogo_x,$sedslogo_y,0,0,$scale_factor*640,$scale_factor*217,640,217);
	//Put User Image on Left
		$photo_width = 400;
		$photo_height = $photo_width*4/3;
		$photo_x = 200;
		$photo_y = 450;
		$photo_borderspacing = 10;
		imagefilledrectangle($membercard, $photo_x-$photo_borderspacing, $photo_y-$photo_borderspacing, $photo_x+$photo_width+$photo_borderspacing,$photo_y+$photo_height+$photo_borderspacing, $whiteback);
		imagecopyresized($membercard,$memberimage, $photo_x,$photo_y,0,0,$photo_width,$photo_height,$photo_imagesize[0],$photo_imagesize[1]);	
	//Location Boxes
		$TextBox_Width = 1000;
		$TextBox_Height = 125;
		$TextBox_Y_Space = 175;
		$TextBox_X_Start = 700;
		$TextBox_Y_Start = 425;
	
	//Add Background Transparencies
		imagefilledrectangle($membercard, $TextBox_X_Start, $TextBox_Y_Start, $TextBox_X_Start+$TextBox_Width, $TextBox_Y_Start+$TextBox_Height, $grayback);
		imagefilledrectangle($membercard, $TextBox_X_Start, $TextBox_Y_Start+1*$TextBox_Y_Space, $TextBox_X_Start+$TextBox_Width, $TextBox_Y_Start+$TextBox_Height+1*$TextBox_Y_Space, $grayback);
		imagefilledrectangle($membercard, $TextBox_X_Start, $TextBox_Y_Start+2*$TextBox_Y_Space, $TextBox_X_Start+$TextBox_Width, $TextBox_Y_Start+$TextBox_Height+2*$TextBox_Y_Space, $grayback);
		imagefilledrectangle($membercard, $TextBox_X_Start, $TextBox_Y_Start+3*$TextBox_Y_Space, $TextBox_X_Start+$TextBox_Width, $TextBox_Y_Start+$TextBox_Height+3*$TextBox_Y_Space, $grayback);
	//Add General Text
		$font_detail = ABSPATH . "wp-content/plugins/seds-code/font/swissek.ttf";
		$gen_textsize = 25;
		imagettftext ( $membercard , $gen_textsize ,0 , $TextBox_X_Start , $TextBox_Y_Start-5 , $white , $font_detail , "Member Name" );
		imagettftext ( $membercard , $gen_textsize ,0 , $TextBox_X_Start , $TextBox_Y_Start-5+1*$TextBox_Y_Space , $white , $font_detail , "Chapter" );
		imagettftext ( $membercard , $gen_textsize ,0 , $TextBox_X_Start , $TextBox_Y_Start-5+2*$TextBox_Y_Space , $white , $font_detail , "Membership Expires" );
		imagettftext ( $membercard , $gen_textsize ,0 , $TextBox_X_Start , $TextBox_Y_Start-5+3*$TextBox_Y_Space , $white , $font_detail , "Membership I.D. Number" );
		//General SEDS
			$SEDS = "Students for the Exploration and Development of Space";
			$SEDS_imagesize = imagettfbbox($gen_textsize,0,$font_detail,$SEDS);
			imagefilledrectangle($membercard, 0, 1100, 1995, 1305, $grayback);
			imagettftext ( $membercard , 30 ,0 , 234/2 , 1200 , $white , $font_detail , $SEDS );
	//Add USER Info

		$textcolor = imagecolorallocate($membercard, 255, 255, 255);
		//Size Control Loop
		$useroutput  = array( 
					$dataout['first_name'] . " " . $dataout['middle_name'] . " " . $dataout['last_name'],
					$dataout['org_nickname'],
					$dataout['memberend'],
					$dataout['cid'],
					);
		for($i=0;$i<count($useroutput);$i++) {
			$user_textsize = 40;
			$user_leftspacing = 40;
			for($j = false;$j !== true; $j) {
				$user_imagesize = imagettfbbox($user_textsize,0,$font_detail,$useroutput[$i]);
				$user_textwidthcalc = $user_imagesize[2] - $user_imagesize[0];
				$user_textheightcalc = abs($user_imagesize[1]) - $user_imagesize[5];
				
				if ( $user_textwidthcalc > ($TextBox_Width-$user_leftspacing) || $user_textheightcalc > $TextBox_Height ) {
					$j = false;
					$user_textsize = $user_textsize - 1;
				} else {
					imagettftext ( $membercard , $user_textsize ,0 , $TextBox_X_Start+$user_leftspacing , $TextBox_Y_Start-5+1*$TextBox_Y_Space/2+$TextBox_Y_Space*$i , $textcolor , $font_detail ,  $useroutput[$i] );
					$j = true;
				}
			}
		}	
	//Add Barcode
		$barcodeimage = imagecreatetruecolor(100,375);
		$blackground = imagecolorallocate($barcodeimage, 0, 0, 0);
		
		$redcolor = imagecolorallocate($barcodeimage, 255, 0, 0);
		$barcodefont_detail = ABSPATH . "wp-content/plugins/seds-code/font/FRE3OF9X.TTF";
		imagecolortransparent($barcodeimage, $blackground);
		$blackbarcode = imagecolorallocate($barcodeimage, 1, 0, 0);
		
		imagettftext( $barcodeimage, 100,90,100, 375, $blackbarcode , $barcodefont_detail , "*" . str_pad($cid, 5, '0', STR_PAD_LEFT) . "*" );
		imagepng($barcodeimage,$img_location . "barcode_$cid.png");
		imagedestroy($barcodeimage);
		//Load Barcode
		imagefilledrectangle($membercard, 1995-200, 0, 1995, 1269, $whiteback2);
		$barcode_img = imagecreatefrompng($img_location . "barcode_$cid.png");
		//1269/2-375/2
		imagecopyresized($membercard,$barcode_img, 1995-200,1262/2-375/2-125,0,0,200,750,100,375);
	//Save Image
	
	imagepng($membercard,$img_location . "card_$cid.png");
	//Get Attributes
	$IMG_Attributes = getimagesize($img_location . "card_$cid.png");
	imagedestroy($membercard);
	
	
//Diagnostics
	//echo "membership_org:" . $membership_org . "<br>";
	//echo $memberinfo_result['values'][0]['image_URL'] . "<br>";
		//	echo "photo_imagesize<pre>";
		//	print_r($photo_imagesize['mime']);
		//	echo "</pre>";
			
		//	echo "SEDS_imagesize<pre>";
		//	print_r($SEDS_imagesize);
		//	echo "</pre>";
		return '<img src="' . plugins_url() . "/files/civicrm/custom/userid/" . "card_$cid.png" . '" width="350px">';
}
add_shortcode( 'seds-getcard', 'seds_getcard' );
//echo $cid;
//seds_getcard($_GET[$cid]);