<?php
$ossirun_baseurl = str_replace("/wp-content/plugins/seds-code/sedsjobs","", getcwd());
include_once($ossirun_baseurl . '/wp-blog-header.php');
include('simple_html_dom.php');
global $wpdb;
//$html = file_get_html("http://intern.nasa.gov/ossi/web/public/guest/searchOpps/index.cfm?solarAction=view&id=7200");
//foreach($html->find('input') as $e) 
//    echo $e->name . ' | ' . $e->value . '<br>';

//delete_option( "ossi-sessions" );

function ossijob($jobid) {
$agent= 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
$tuCurl  = curl_init(); 
curl_setopt($tuCurl, CURLOPT_URL, "https://intern.nasa.gov/ossi/web/public/guest/searchOpps/index.cfm?solarAction=view&id=" . $jobid);
curl_setopt($tuCurl , CURLOPT_RETURNTRANSFER, true);
curl_setopt($tuCurl, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($tuCurl, CURLOPT_POST, false);
curl_setopt($tuCurl, CURLOPT_VERBOSE, true);
curl_setopt($tuCurl, CURLOPT_SSLVERSION,3); 
curl_setopt($tuCurl, CURLOPT_USERAGENT, $agent);
$respooutnse = curl_exec($tuCurl);
file_put_contents("tempfile.html",$respooutnse);
//Create Tempfile
$html = file_get_html("tempfile.html");
//Pull all hidden data fields - contains info
foreach($html->find('input') as $e) {
    $inputfields[$e->name] = $e->value;
}
foreach($html->find('input[name="eduLevel"]') as $e) {
    //$inputfields['eduLevel'] = $e->value;
    $inputfields['eduLevel'] .= $e->value . "|";
}
foreach($html->find('input[name="sessions"]') as $e) {
    //$inputfields['eduLevel'] = $e->value;
    $inputfields['Sessionss'] .= $e->value . "|";
}

foreach($html->find('td[nowrap]') as $major) {
    $inputfields["majors"] = $major->innertext;
}

$inputfields["eduLevel"] = str_replace("_LEVEL","_LEVEL|",$inputfields["eduLevel"]);
$inputfields["eduLevel"] = str_replace("||","|",$inputfields["eduLevel"]);
$inputfields["eduLevelarr"] = explode("|",$inputfields["eduLevel"]);
$inputfields["sessionsarr"] = explode("|",$inputfields["Sessionss"]);
$inputfields["majorsarr"] = explode("<br>",$inputfields["majors"]);
$inputfields["majors"] = str_replace("<br>","|",$inputfields["majors"]);
	if($inputfields['solarAction'] == "print") {
		return $inputfields; //Return array
	}

}   //End of ossijob function

function ossimajorcheck( $querymajors ) {
	if(is_array($querymajors)) {
	$datatypesfield = 8;
	$current_majors = get_option( "ossi-majors" );
	if($current_majors==false) {
		$current_majors = array("");
	} elseif(!is_array($current_majors)) {
		$current_majors = array("");
		delete_option( "ossi-majors" );
	}
	$testquery = array_merge($current_majors, $querymajors);
	$newmajors = array_unique($testquery);
	sort($newmajors);
	update_option( "ossi-majors", $newmajors );
	$jobman_options = get_option( "jobman_options", array("") );
	$exhistingmajorfields = str_replace("\n","|",$jobman_options['job_fields'][$datatypesfield]['data']);
	$exhistingmajorfieldsarr = explode("|",$exhistingmajorfields);
	$testquery = array_merge($exhistingmajorfieldsarr,$newmajors );
	//$testquery = $newmajors;
	$newmajors = array_unique($testquery);
	sort($newmajors);
	if($newmajors[0] == "") { unset($newmajors[0]); sort($newmajors); }
	$jobman_options['job_fields'][$datatypesfield]['data'] = implode("\n",$newmajors);
	$jobman_options['job_fields'][$datatypesfield]['data'] = implode("\n", array_unique(preg_replace("/[^a-zA-Z0-9_ ]+/", "", explode("\n",$jobman_options['job_fields'][$datatypesfield]['data']))));
	$jobman_options['job_fields'][$datatypesfield]['type'] = "checkbox";
	update_option( "jobman_options", $jobman_options );
	}
}

function ossilevelcheck( $querylevel ) {
	if(is_array($querylevel)) {
	$datatypesfield = 10;
	$current_levels = get_option( "ossi-level" );
	if($current_levels==false) {
		$current_levels = array("");
	} elseif(!is_array($current_levels)) {
		$current_levels = array("");
		delete_option( "ossi-level" );
	}
	$testquery = array_merge($current_levels, $querylevel);
	$newlevels = array_unique($testquery);
	sort($newlevels);
	if($newlevels[0] == "") { unset($newlevels[0]); sort($newlevels); }
	for($k=0;$k<count($newlevels);$k++) {
		$newlevelsa[$k] = str_replace("_"," ",$newlevels[$k]);
		$newlevelsa[$k] = ucwords(strtolower($newlevelsa[$k]));
	}
	update_option( "ossi-level", $newlevels );
	update_option( "ossi-alevel", $newlevelsa );
	$jobman_options = get_option( "jobman_options", array("") );
	$exhistingtypesfields = str_replace("\n","|",$jobman_options['job_fields'][$datatypesfield]['data']);
	$exhistingtypesfieldsarr = explode("|",$exhistingtypesfields);
	$testquery = array_merge($exhistingtypesfieldsarr,$newlevels );
	$newlevels = array_unique($testquery);
	sort($newlevels);
	if($newlevels[0] == "") { unset($newlevels[0]); sort($newlevels); }
	$jobman_options['job_fields'][$datatypesfield]['data'] = implode("\n",$newlevels);
	$jobman_options['job_fields'][$datatypesfield]['data'] = implode("\n", array_unique(preg_replace("/[^a-zA-Z0-9_]+/", "", explode("\n",$jobman_options['job_fields'][$datatypesfield]['data']))));
	$jobman_options['job_fields'][$datatypesfield]['type'] = "checkbox";
	update_option( "jobman_options", $jobman_options );
	}
}

function ossitypecheck( $querytype ) {
	if(is_array($querytype)) {
	$datatypesfield = 9;
	$current_types = get_option( "ossi-types" );
	if($current_types==false) {
		$current_types = array("");
	} elseif(!is_array($current_types)) {
		$current_types = array("");
		delete_option( "ossi-types" );
	}
	$testquery = array_merge($current_types, array($querytype));
	$newtypes = array_unique($testquery);
	sort($newtypes);
	if($newtypes[0] == "") { unset($newtypes[0]); sort($newtypes); }
	update_option( "ossi-types", $newtypes );
	//Update Jobman Options
	$jobman_options = get_option( "jobman_options", array("") );
	$exhistingjobfields = str_replace("\n","|",$jobman_options['job_fields'][$datatypesfield]['data']);
	$exhistingjobfieldsarr = explode("|",$exhistingjobfields);
	$stdopts = array("Project Leader", "Assistant", "Other", "Leadership");
	$testquery = array_merge($exhistingjobfieldsarr, $newtypes, $stdopts);
	$newtypes = array_unique($testquery);
	sort($newtypes);
	if($newtypes[0] == "") { unset($newtypes[0]); sort($newtypes); }
	$jobman_options['job_fields'][$datatypesfield]['data'] = implode("\n",$newtypes);
	$jobman_options['job_fields'][$datatypesfield]['data'] = implode("\n", array_unique(preg_replace("/[^a-zA-Z0-9_ ]+/", "", explode("\n",$jobman_options['job_fields'][$datatypesfield]['data']))));
	update_option( "jobman_options", $jobman_options );
	}
}

function ossisessioncheck( $querysession ) {
	
	$current_sessions = get_option( "ossi-sessions" );
	if($current_sessions==false) {
		$current_sessions = array("");
	} elseif(!is_array($current_sessions)) {
		$current_sessions = array("");
		delete_option( "ossi-sessions" );
	}
	$testquery = array_merge($current_sessions, $querysession);
	$newsessions = array_unique($testquery);
	sort($newsessions);
	if($newsessions[0] == "") { unset($newsessions[0]); sort($newsessions); }
	update_option( "ossi-sessions", $newsessions );
}

function newoldjobpagecheck ($nasaid ) {
	//SELECT * FROM `wp_postmeta` WHERE `meta_key`="data12" AND `meta_value`=7220	
	global $wpdb;
	$myrows = $wpdb->get_results( "SELECT * FROM `wp_postmeta` WHERE `meta_key`=\"data12\" AND `meta_value`=" . $nasaid , "ARRAY_A");
	if (count($myrows) < 1) {
		return false;	
	} else {
		return $myrows[0]['post_id']; //Post ID
	}
}
//End Functions declare

//Load 'global' variables
	$sessionsoptions = get_option( "ossi-session-options", array() );	
	$ossiminvalue = get_option('ossisync_minvalue', 1);
	$ossimaxvalue = get_option('ossisync_maxnvalue',25000 );
	$ossicurvalue = get_option('ossisync_curvalue', 1);
	if ($ossicurvalue > $ossimaxvalue) {
		$ossicurvalue = $ossiminvalue;
	} elseif($ossicurvalue < $ossiminvalue) {
		$ossicurvalue = $ossiminvalue;
	}
	$ossicurvalue++;
	update_option('ossisync_curvalue',$ossicurvalue);
//Begin cycle through
for ($i=$ossicurvalue;$i<($ossicurvalue+1);$i++) { //TODO: Allow for variable number ran here

//Here $i is the value of the id for the query
	$fields[$i] = ossijob($i);
if(count($fields[$i]) > 1) { //Fields Exhist
//Take data and add to core
	for ($j=0;$j<count($fields[$i]['majorsarr']); $j++) {
		if(strlen($fields[$i]['majorsarr'][$j]) > 2) {
			$mastermajors[] = $fields[$i]['majorsarr'][$j];
		}
	} //Each Major
	$mastermajors = array_unique($mastermajors);
	sort($mastermajors);
	//Check and load each type of major into options table
		ossimajorcheck( $mastermajors );
	for ($j=0;$j<count($fields[$i]['eduLevelarr']); $j++) {
		if(strlen($fields[$i]['eduLevelarr'][$j]) > 2) {
			$masterlevel[] = $fields[$i]['eduLevelarr'][$j];
		}
	} //Each Level
	$masterlevel = array_unique($masterlevel);
	sort($masterlevel);
	//Check and load each type of level into options table
		ossilevelcheck($masterlevel);
	//Check and Load each type into options table
		ossitypecheck( $fields[$i]['type'] );
	for ($j=0;$j<count($fields[$i]['sessionsarr']); $j++) {
		$mastersession[] = $fields[$i]['sessionsarr'][$j];
	} //Each Session
	$mastersession = array_unique($mastersession);
	sort($mastersession);
	//Check and load each type of session into options table
		ossisessioncheck($mastersession);
//Add datetime to each array
	//Add Time and Dates Field
	$fields[$i]['start_date'] = $sessionsoptions[$fields[$i]['sessions']]['periodstartdate'];
	$fields[$i]['end_date'] = $sessionsoptions[$fields[$i]['sessions']]['periodenddate'];
	$fields[$i]['deadline'] = $sessionsoptions[$fields[$i]['sessions']]['deadline'];
	$fields[$i]['periodstartdisplaydate'] = $sessionsoptions[$fields[$i]['sessions']]['periodstartdisplaydate'];
	$fields[$i]['periodenddisplaydate'] = $sessionsoptions[$fields[$i]['sessions']]['periodenddisplaydate'];
	//NOTE: Post date is start date
	$postcheck[$i] = newoldjobpagecheck($fields[$i]['id'] );
	//Update Post
	if (is_numeric($postcheck[$i])) { //Update
		$postid = $postcheck[$i]; //Post
		$postupdate = array(
    		'ID'           => $postid,
      		'post_date' => $fields[$i]['start_date'] . ' 00:00:00',
    	);
    	wp_update_post( $postupdate );
	} else { //New post
		//Date Check
			$fielddate = $fields[$i]['periodstartdisplaydate'];
			if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$fielddate)) {
				//echo "valid date<br>";
			}else{
				$fielddate = date("Y-m-d");
			 }
		$post = array(
			'comment_status' => 'closed', // 'closed' means no comments.
			'ping_status'    => 'closed',// 'closed' means pingbacks or trackbacks turned off
			'post_author'    => 2, //The user ID number of the author.
			'post_date'      => $fielddate . " 00:00:00", //The time post was made.
			'post_name'      => strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $fields[$i]['title']))), // The name (slug) for your post
			'post_parent'    => 11208,
			'post_status'    => 'publish', //Set the status of the new post.
			'post_title'     => $fields[$i]['title'], //The title of your post.
			'post_type'      => 'jobman_job', //You may want to insert a regular post, page, link, a menu item or some custom post type
		);  
		//$postid = wp_insert_post( $post ); 
		//Diagnostics
		/*
		$postid = wp_insert_post( $post,true ); 
                echo "<pre>";
                print_r($post);
                echo "</pre>";
		echo "<pre>";
		print_r($postid);
		echo "</pre>";
		*/

	}
//Add NASA Category
	wp_set_post_terms( $postid, array(55) );
//Add Meta Terms
	//echo "PostID: " . $postid;
	update_post_meta($postid, 'highlighted', 0 );
	update_post_meta($postid, 'email', "info@seds.org" );
	update_post_meta($postid, 'iconid', "" );
	update_post_meta($postid, 'displayenddate', $fields[$i]['periodenddisplaydate']);
	update_post_meta($postid, 'data11', "https://intern.nasa.gov/ossi/web/public/guest/searchOpps/index.cfm?solarAction=view&id=" . $fields[$i]['id'] );
	update_post_meta($postid, 'data10', implode(", ",$fields[$i]['eduLevelarr']) );
	update_post_meta($postid, 'data9', $fields[$i]['type'] );
	update_post_meta($postid, 'data8', implode(", ",$fields[$i]['majorsarr']) );
	update_post_meta($postid, 'data7', $fields[$i]['city'] .", " . $fields[$i]['state'] );
	update_post_meta($postid, 'data6', $fields[$i]['deadline'] );
	update_post_meta($postid, '_cc_post_template_avatar', 0 );
	update_post_meta($postid, '_cc_post_template_date', 0 );
	update_post_meta($postid, '_cc_post_template_tags', 0 );
	update_post_meta($postid, '_cc_post_template_comments_info', 0 );
	update_post_meta($postid, 'data2', $fields[$i]['start_date'] );
	update_post_meta($postid, 'data3', $fields[$i]['end_date'] );
	update_post_meta($postid, 'data4', $fields[$i]['mailingAddress'] );
	update_post_meta($postid, 'data5', "<p><strong>Description</strong> - " . $fields[$i]['description'] . "</p><p><strong>Outcome</strong> - " . $fields[$i]['projectOutcome'] ."</p><p><strong>Skills</strong> - ". $fields[$i]['skills'] );
	update_post_meta($postid, '_cc_post_template_on', 0 );
	update_post_meta($postid, '_cc_page_template_cat', "a:0:{}" );
	update_post_meta($postid, '_cc_page_slider_caption', 0 );
	update_post_meta($postid, '_cc_page_template_on', 0 );
	update_post_meta($postid, '_cc_page_slider_cat', "a:0:{}" );
	update_post_meta($postid, '_cc_page_slider_on', 0 );
	//update_post_meta($postid, 'meta_key', //$postmeta[$i]['data1'] ); //Salary
	update_post_meta($postid, 'data12', $fields[$i]['id'] );

	}
}	
/*
echo "jobman_options: <pre>";
print_r($jobman_options['job_fields']);
echo "</pre>";

echo "fields: <pre>";
print_r($fields);
echo "</pre>";

echo "postcheck: <pre>";
print_r($postcheck);
echo "</pre>";
*/
/*
$post = get_post( '11725', "ARRAY_A");
echo "post: <pre>";
print_r($post);
echo "</pre>";

$meta_values = get_post_meta('11725');
echo "meta_values: <pre>";
print_r($meta_values);
echo "</pre>";
*/
