<?php
//API KEY: AIzaSyBUlUe8--3d3hE0DHs_GpXkRSzSCaFCXaI
function seds_map( $atts ){
  //Bring in the API and set base values
	$seds_baseurl = str_replace("/wp-content/plugins/seds-chapterlist","", getcwd());
	include_once($seds_baseurl . '/wp-blog-header.php');
	include_once(ABSPATH  . '/wp-blog-header.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm.settings.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/CRM/Core/Config.php');
	include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/civicrm.config.php');
	$config = CRM_Core_Config::singleton();
	$seds_membershiptype = 1; //Membership type used by seds chapters
//Call Membership list for SEDS-USA
	//To be used for color change setup
	$seds_params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
		'membership_type_id' => $seds_membershiptype,);
	$seds_result = civicrm_api('Membership', 'get', $seds_params);
	$seds_resultvalues = $seds_result['values'];
	//Make a loop and get the activity result
	for ($z=0;$z<count($seds_resultvalues);$z++){
		$seds_status[$seds_resultvalues[$z]['contact_id']] = $seds_resultvalues[$z]['status_id'];
	}
//Call International Chapters List
	$seds_masterlistparams = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
	'contact_sub_type' => 'SEDS_Chapter',
	'rowCount' => 500, //Note that limited to 500 total historical chapters - must increase if there are more
	);
	$seds_masterlistresult = civicrm_api('Contact', 'get', $seds_masterlistparams);
	$seds_masterlist = $seds_masterlistresult['values'];
//Generate Array for XML Output
	
	$seds_defgeo1 = 0;
	$seds_defgeo2 = 0;
		
	for ($i=0;$i<count($seds_masterlist);$i++) {
		//Run through logic
		$seds_icon[$i] = "red"; //TODO: Add Icon Filter for country, etc
		if (isset($seds_status[$seds_masterlist[$i]['contact_id']])) {
			if ($seds_status[$seds_masterlist[$i]['contact_id']] == 1 || $seds_status[$seds_masterlist[$i]['contact_id']] == 2 || $seds_status[$seds_masterlist[$i]['contact_id']] == 3) {
			//Currently active US Chapter
			$seds_icon[$i] = "usactive";
			} else {
			//Not Active US Chapter
			$seds_icon[$i] = "usinactive";
			}
		} else { //No Membership info exhists i.e. International or no US membership record established
			$seds_icon[$i] = "red";
		}
		
		if (strlen($seds_masterlist[$i]['geo_code_1']) < 2) {
		$seds_geo1[$i] = $seds_defgeo1;
		$seds_defgeo1 = $seds_defgeo1 +.5;
		} else {
		$seds_geo1[$i] = $seds_masterlist[$i]['geo_code_1']; 
		}
		if (strlen($seds_masterlist[$i]['geo_code_1']) < 2) {
		$seds_geo2[$i] = $seds_defgeo2;
		$seds_defgeo2 = $seds_defgeo2 +.5;
		} else {
		$seds_geo2[$i] = $seds_masterlist[$i]['geo_code_2']; 
		}
		
		//Assign
		$seds_chapter[$i]['name'] = $seds_masterlist[$i]['display_name'];
		$seds_chapter[$i]['lat'] = $seds_geo1[$i];
		$seds_chapter[$i]['lgn'] = $seds_geo2[$i];
		$seds_chapter[$i]['icon'] = $seds_icon[$i];
	}
	
	$seds_xmlfileout = ABSPATH . '/wp-content/plugins/seds-code/gmaps/moredata2.xml';
	$seds_fileoutcontent = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<markers>' . "\n";
	for ($x=0;$x<count($seds_chapter);$x++) {
		$seds_fileoutcontent .= '  <marker name="' . str_replace("&"," and ",$seds_chapter[$x]['name']) . '" lat="' . $seds_chapter[$x]['lat'] . '" lng="' . $seds_chapter[$x]['lgn'] . '" icon="' . $seds_chapter[$x]['icon'] . '"/>' . "\n";
	}
	$seds_fileoutcontent .= "</markers>";
	file_put_contents($seds_xmlfileout, $seds_fileoutcontent);
//Google Maps Call
	
	echo '<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>';
	echo '<script type="text/javascript" src="' . get_site_url() . '/wp-content/plugins/seds-code/gmaps/util.js"></script>';
	
	?>
<script type="text/javascript">
  var infowindow;
  var map;
  var iconBase = 'https://maps.google.com/mapfiles/kml/shapes/';
  
  var icons = {
  red: {
    icon: 'http://maps.google.com/mapfiles/kml/shapes/placemark_circle_highlight.png',
    shadow: iconBase + 'parking_lot_maps.shadow.png'
  },
  usactive: {
    icon: 'http://maps.google.com/mapfiles/kml/pal3/icon39.png',
    shadow: iconBase + 'library_maps.shadow.png'
  },
  usinactive: {
    icon: 'http://maps.google.com/mapfiles/kml/pal3/icon47.png',
    shadow: iconBase + 'library_maps.shadow.png'
  }
};

  function initialize() {
    var myLatlng = new google.maps.LatLng(0, 0);
    var myOptions = {
      zoom: 1,
      center: myLatlng,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    }
    map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
    downloadUrl("<?php echo get_site_url() . '/wp-content/plugins/seds-code/gmaps/moredata2.xml'; ?>", function(data) {
      var markers = data.documentElement.getElementsByTagName("marker");
      for (var i = 0; i < markers.length; i++) {
        var latlng = new google.maps.LatLng(parseFloat(markers[i].getAttribute("lat")),
                                    parseFloat(markers[i].getAttribute("lng")));
        var marker = createMarker(markers[i].getAttribute("name"), latlng, markers[i].getAttribute("icon"));
       }
     });
  }

  function createMarker(name, latlng, icon) {
    var marker = new google.maps.Marker({position: latlng, map: map, icon: icons[icon].icon,});
    google.maps.event.addListener(marker, "click", function() {
      if (infowindow) infowindow.close();
      infowindow = new google.maps.InfoWindow({content: name});
      infowindow.open(map, marker);
    });
    return marker;
  }

google.maps.event.addDomListener(window, 'load', initialize);
</script>
	<script onload="initialize()"></script>
	<?php
	
	echo '<div id="map_canvas" style="width:600px; height:500px"></div>';
//Diagnostics
	/*
	echo "XML Output: <pre>";
	//print_r($seds_fileoutcontent);
	echo "</pre>";
	
	echo "SEDS Chapter Membership: <pre>";
	print_r($seds_status);
	echo "</pre>";
	*/
	
}//End of seds_map function

add_shortcode( 'seds-map', 'seds_map' );