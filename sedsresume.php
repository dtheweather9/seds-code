<?php



function seds_userresume( ){
	//Bring in the API and set base values
		$seds_baseurl = str_replace("/wp-content/plugins/seds-chapterlist","", getcwd());
		include_once($seds_baseurl . '/wp-blog-header.php');
		include_once(ABSPATH  . '/wp-blog-header.php');
		include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm.settings.php');
		include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/CRM/Core/Config.php');
		include_once(ABSPATH  . 'wp-content/plugins/civicrm/civicrm/civicrm.config.php');
		$config = CRM_Core_Config::singleton();
		global $wpdb;
		
	//Load Files
		$skills = file(ABSPATH  . '/wp-content/plugins/seds-code/resume/skillstart.txt');
		$proficency = file(ABSPATH  . '/wp-content/plugins/seds-code/resume/skillslevel.txt');
	//Load Pre functions
	?>
	<script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
	<link href="http://www.test2.seds.org/wp-content/plugins/seds-code/select2/select2.css" rel="stylesheet"/>
	<script src="http://www.test2.seds.org/wp-content/plugins/seds-code/select2/select2.js"></script>
	<script>
		$(document).ready(function() { 
		$("#sedsresume-skills0").select2(); 
		});
	</script>	
	<script>
		$(document).ready(function() { 
		$("#sedsresume-company").select2({
			placeholder: "Select Company",
		}); 
		});
	</script>
	<script>
		$(document).ready(function() { 
		console.log("Page is Loaded");
		});
	</script>
	<script>
	$("#sedsresume-company")
		.on("select2-selecting", function() { console.log("Selection Changed") 
		}); 	
	</script>
		<?php
		echo '<form action="" method="post">';
		echo "This is the resume location<br>";
		
		echo '<div id="sedsresume-hiddencompanies">';
		
		$companylist_params = array('version' => 3,'page' => 'CiviCRM','q' => 'civicrm/ajax/rest','sequential' => 1,
  			'contact_type' => 'Organization',
  			'return' => 'display_name',
  			'options' => array('limit' => '5000'), 'sort' => 'display_name ASC',
			);
		$companylist_result = civicrm_api('Contact', 'get', $companylist_params);
		
		echo '<select name="company[]" id="sedsresume-company" style="width: 500px;">';
		echo '<option></option>';
		for($i=0;$i<$companylist_result['count'];$i++) {
			//$companyarray[$companylist_result['values'][$i]['contact_id']] = $companylist_result['values'][$i]['display_name'];
			echo '<option value="' . $companylist_result['values'][$i]['contact_id'] . '">' . $companylist_result['values'][$i]['display_name'] . '</option>';
		}
		echo '</select><br> ';
		
		echo "<pre>";
		print_r($companyarray);
		echo "</pre>";
		
		echo "</div>";
		
		echo '<div id="sedsresume-skillsdiv">';
		echo '<h2>Skills</h2>';
		  for($j=0;$j<1;$j++) {
			echo '<select multiple name="skill[]" id="sedsresume-skills'. $j . '" style="width: 500px;">';
			for ($i=0;$i<count($skills);$i++) {
			echo '<option value="' . trim($skills[$i]) . '">' . trim($skills[$i]) . '</option>';
			}
			echo '</select><br> ';
		  }	
		echo "</div>";
		echo '<br><input type="submit" value="Submit">';
		echo "</form>";
		//Diagnostics
			echo "<pre>";
			print_r($_POST);
			echo "</pre>";
}//End of shortcode block

add_shortcode( 'seds-userresume', 'seds_userresume' );
/*
add_action('bp_init', 'sedscode_addresume');

function sedscode_addresume() {
//Add Array
  global $bp;
	$bpcivi_photonavparent_url = trailingslashit( $bp->displayed_user->domain . 'profile' );
	$bpcivi_photonavdefaults = array(
		'name'            => 'Resume', // Display name for the nav item
		'slug'            => 'sedsresume', // URL slug for the nav item
		'parent_slug'     => 'profile', // URL slug of the parent nav item
		'parent_url'      => $bpcivi_photonavparent_url, // URL of the parent item
		'item_css_id'     => bpcivi_imagecss, // The CSS ID to apply to the HTML of the nav item
		'user_has_access' => true,  // Can the logged in user see this nav item?
		'position'        => 90,    // Index of where this nav item should be positioned
		'screen_function' => sedscoderesume_image_page, // The name of the function to run when clicked
	);

bp_core_new_subnav_item($bpcivi_photonavdefaults);
add_action('bp_template_content', 'sedscode_image_page_content');
}

function sedscoderesume_image_page() {
	bp_core_load_template( 'members/single/plugins' ); //Loads general members/single/plugins template
}

function sedscode_image_page_content() {
	global $bp;
	if ($bp->current_action == 'sedsresume' ) {  //If the Action 
		seds_userresume();  
	}
}

*/

/*

<script>
	function add() {
    	var item = $('#sedsresume-skillsdiv2').clone();
    	item.attr({'style': ''});
    	$('#sedsresume-addlocation').append(item);
		}
	</script>
	
echo '<button class="sedsaddskill" onclick="add()" type="button">Add more</button>';
*/
