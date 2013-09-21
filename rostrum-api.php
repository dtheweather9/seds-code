<?php

//Fields
/*
	id_first_name - First Name
	id_last_name - Last Name
	id_chapter - Chapter id; passes contact info
	id_organization - organization
	id_email - email
	id_email_confirm - email confirm
	id_phone - phone number
	id_membershipid - member id
	id_heard_about - heard about reasons for attending
	regtype - id_regtype_[0-3]; registration types
	id_events_0 - Registration for banquet
	registration_total - total amount for ticket
	
	
*/

//Define Location of header files
$seds_baseurl = str_replace("/wp-content/plugins/seds-code","", getcwd());
include_once($seds_baseurl . '/wp-blog-header.php');
if ($_POST['key'] !== get_option('sedscode_rostrumapi')) {
die("Incorrect Key - Contact System Admin");
}
//Test ourput
//mail("dmpastuf@seds.org","Test.php Sent","Here's the Message, Test Php works in seds-code");

//Include Files
	include_once(ABSPATH  . '/wp-blog-header.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm.settings.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/CRM/Core/Config.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/civicrm.config.php');
	$config = CRM_Core_Config::singleton();
	global $wpdb;
//Define Variables
	$spacevision_id = 2;
	$banquet_id = 3;

//Assign variables	
	//$SEDSPOST = $_GET; //Note also change the key check
	$SEDSPOST = $_POST; 
	$id_first_name = $SEDSPOST['id_first_name'];
	$id_last_name = $SEDSPOST['id_last_name'];
	$id_organization = $SEDSPOST['id_organization'];
	$id_email = $SEDSPOST['id_email'];
	$id_phone = $SEDSPOST['id_phone'];
	$registration_total = $SEDSPOST['registration_total'];
	$id_membershipid = $SEDSPOST['id_membershipid'];
	$id_heard_about = $SEDSPOST['id_heard_about'];
	$regtype = $SEDSPOST['regtype'];
	$roleid = $SEDSPOST['roleid'];
	$id_events = $SEDSPOST['id_events'];
	$banquet_veg = $SEDSPOST['banquet_veg'];
	$order_id = $SEDSPOST['order_id'];
	$payment_type = $SEDSPOST['payment_type'];
	$authorize_transactionid = $SEDSPOST['authorize_transactionid'];
	$seds_istest = $SEDSPOST['istest'];
//Check MemberID and email
	$seds_emailcheckparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
		'contact_id' => $id_membershipid,
		'email' => $id_email, );
	$seds_emailcheckresult = civicrm_api('Email', 'get', $seds_emailcheckparams);
	if ( $seds_emailcheckresult['count'] > 0) {
	//Valid MemberID and Email
	} else {
		//No MemberID or invalid match - Create Contact
		$seds_newcontactparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
			"contact_type" => "Individual",
			"first_name" =>$id_first_name,
			"last_name" => $id_last_name,
			"job_title" => "",
			"current_employer" =>$id_organization,
			"phone_type_id"=>"1",
			"phone"=>$id_phone,
			"email"=>$id_email,
		);
		$seds_newcontactresult = civicrm_api('Contact', 'create', $seds_newcontactparams);
		$id_membershipid = $seds_newcontactresult['id'];
	}
	
	//Fee Level / Map
		switch($regtype) {
			case 1:
				$mappedregtype = '[Exhibitor]';
				break;
			case 2:
				$mappedregtype = '[Professional (Late)]';
				break;
			case 3:
				$mappedregtype = '[Professional (Onsite)]';
				break;
			case 4:
				$mappedregtype = '[Professional (Regular)]';
				break;
			case 5:
				$mappedregtype = '[Professional (Regular)(NSS Discount)]';
				break;
			case 6:
				$mappedregtype = '[Professional (Regular)(SFF Discount)]';
				break;
			case 7:
				$mappedregtype = '[Professional (Regular)(TPS Discount)]';
				break;
			case 8:
				$mappedregtype = '[Speaker]';
				break;
			case 9:
				$mappedregtype = '[Sponsor]';
				break;	
			case 10:
				$mappedregtype = '[Student (Late)]';
				break;
			case 11:
				$mappedregtype = '[Student (Onsite)]';
				break;
			case 12:
				$mappedregtype = '[Student (Regular)]';
				break;
			case 13:
				$mappedregtype = '[Young Professional (Late)]';
				break;
			case 14:
				$mappedregtype = '[Young Professional (Onsite)]';
				break;
			case 15:
				$mappedregtype = '[Student (Regular)]';
				break;
			case 16:
				$mappedregtype = '[No Registration]';
				break;
		}
	
	//Register Event Attendance //Log Event Registration
		$event_registerparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
			'contact_id' => $id_membershipid,
			'event_id' => $spacevision_id, //2 is SpaceVision 2013
			'participant_fee_level' => $mappedregtype,
			'participant_fee_amount' => $registration_total,
			'participant_fee_currency' => 'USD',
			'participant_status_id' => 1,
			'participant_role_id' => $roleid,
			'participant_source' => "rostrum-" . $order_id,
			'participant_is_pay_later' => $payment_type,
			'participant_is_test' => $seds_istest,
			);
		$event_registerresults = civicrm_api('Participant', 'create', $event_registerparams);
	//Log Sub Event Registration
	  for($i=0;$i<count($id_events);$i++) {
		
		if ($id_events[$i]==3) { //Banquet is Selected
			if ($banquet_veg == 0) {
			$mealtype = "Banquet Registration - Chicken";
			} else {
			$mealtype = "Banquet Registration - Vegetable";
			}
			$event_registerparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
				'contact_id' => $id_membershipid,
				'event_id' => $banquet_id, //3 is SpaceVision 2013 Banquet
				'participant_fee_level' => $mealtype,
				'participant_fee_amount' => $registration_total,
				'participant_fee_currency' => 'USD',
				'participant_status_id' => 1,
				'participant_role_id' => 1, //All will register as attendees
				'participant_source' => "rostrum-" . $order_id,
				'participant_is_pay_later' => $payment_type,
				'participant_is_test' => $seds_istest,//Make 0 when complete with testing
				);
			$event_registerresultsbanquet = civicrm_api('Participant', 'create', $event_registerparams);
		}
		//Add Additional Events here	
		
	  } //End Sub Event Log Loop
	//Log Contributions	
	$params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
		'contact_id' => $id_membershipid,
		'currency' => "USD",
		'non_deductible_amount' => $registration_total,
		'total_amount' => $registration_total,
		'is_test' => $seds_istest, //change later
		'is_pay_later' => $payment_type,
		'financial_type_id' => 4, //Event Fee
		'contribution_status_id' => 1, //2 = cancelled, 3 = refunded
		'instrument_id'=> 79, //79 - Credit Card; 80 - Debt Card, 81 - Cash, 82 - Check, 83 - EFT
		'financial_account_id' =>4, //Event Fee Account - TBD
		'receive_date' => date("Y-m-d H:i:s"),
		'trxn_id' => $authorize_transactionid,
		);
	$seds_eventcontresult = civicrm_api('Contribution', 'create', $params);
	$params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
		'participant_id' => $event_registerresults['id'], 
		'contribution_id' => $seds_eventcontresult['id'],
		);
	$svmaincontributionresult = civicrm_api('ParticipantPayment', 'create', $params);
	if(isset($event_registerresultsbanquet['id'])) {
	$params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
		'participant_id' => $event_registerresultsbanquet['id'], 
		'contribution_id' => $seds_eventcontresult['id'],
		);
	$svbauqetcontributionresult = civicrm_api('ParticipantPayment', 'create', $params);
	}
	
//Encode output
echo json_encode(array("memberid" => $id_membershipid));

		
//Diagnostics
/*
	echo "GET: <pre>";
	print_r($_GET);
	echo "</pre><hr>";
	echo "seds_newcontactresult: <pre>";
	print_r($seds_newcontactresult);
	echo "</pre><hr>";
	echo "event_registerresults: <pre>";
	print_r($event_registerresults);
	echo "</pre><hr>";
	echo "event_registerresultsbanquet<pre>";
	print_r($event_registerresultsbanquet);
	echo "</pre><hr>";
	echo "seds_eventcontresult<pre>";
	print_r($seds_eventcontresult);
	echo "</pre><hr>";
	echo "svmaincontributionresult<pre>";
	print_r($svmaincontributionresult);
	echo "</pre><hr>";
	echo "svbauqetcontributionresult<pre>";
	print_r($svbauqetcontributionresult);
	echo "</pre><hr>";
*/	
	
	
?>