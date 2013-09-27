<?php
function seds_jobsearch( $atts ){
	//Load Pre functions
		global $wpdb;
	
	//Get Majors from NASA List	
		$nasaoption = get_option( "ossi-majors" );
		$nasayears = get_option( "ossi-level" );
		$nasayearsoption = get_option( "ossi-level-options" );
	//Get Companies
		$sedsjobs_taxonomies = get_terms('jobman_category');
		for ($j=0;$j<count($sedsjobs_taxonomies);$j++) {
			$sedsjobs_taxonomiesarr[$j] = get_object_vars($sedsjobs_taxonomies[$j]);
		}
	//Get types 
		$jobmanopts = get_option( "ossi-types" );
	
	//Search Form
		?> 
			<script type="text/javascript">
	function selectAll(selectBox,selectAll) { 
    // have we been passed an ID 
    // Source: http://www.qodo.co.uk/blog/javascript-select-all-options-for-a-select-box/
    	if (typeof selectBox == "string") { 
        	selectBox = document.getElementById(selectBox);
    	} 
    	// is the select box a multiple select box? 
    	if (selectBox.type == "select-multiple") { 
        	for (var i = 0; i < selectBox.options.length; i++) { 
            selectBox.options[i].selected = selectAll; 
        	} 
    	}
	}
			</script> 
			
		<?php	
		
		echo '<div id="sedsjoblist-search">';
		echo '<form action="" method="post">';
			echo '<div id="sedsjoblist-company">';
			echo '<h3>Company / Category: </h3><select multiple name="category[]" id="seds-categorybox">';
				for ($i=0;$i<count($sedsjobs_taxonomiesarr);$i++) {
					echo '<option value="' . $sedsjobs_taxonomiesarr[$i]['term_taxonomy_id'] . '" selected>' . $sedsjobs_taxonomiesarr[$i]['name'] . '<br>';
				}
			echo '</select>';
			echo '</div>';
			echo '<div id="sedsjoblist-typebox">';
			echo '<h3>Job Type: </h3>';
				for ($i=0;$i<count($jobmanopts);$i++) {
					echo '<input type="checkbox" name="type[]" value="' . $i . '">';
					echo $jobmanopts[$i] . '<br>';
				}
			echo '</select>';
			echo '</div>';
			echo '<div id="sedsjoblist-majorsbox">';
			echo '<h3>Major / Degree: </h3> <br><select multiple name="major[]" class="select2-select" id="seds-majorsbox">';
				//echo '<option value="ALL" selected> - - ALL - - <br>';
				for ($i=0;$i<count($nasaoption);$i++) {
					echo '<option value="' . $i . '">' . $nasaoption[$i] . '<br>';
				}
			echo '</select><br>';
			echo '<input type="button" name="Button" value="Select All" onclick="selectAll(\'seds-majorsbox\',true)" />';
			echo '<input type="button" name="Button" value="Select None" onclick="selectAll(document.getElementById(\'seds-majorsbox\'),false)" />';
			echo '</div>';
			echo '<div id="sedsjoblist-yearsbox">';
			echo '<h3>Year / Education: </h3>If Applicable<br><select multiple name="education[]" id="seds-yearsbox">';
				//echo '<option value="ALL" selected> - - ALL - - <br>';
				for ($i=0;$i<count($nasayears);$i++) {
					echo '<option value="' . $nasayears[$i] . '">' . $nasayearsoption[$nasayears[$i]] . '<br>';
				}
			echo '</select><br>';
			echo '<input type="button" name="Button" value="Select All" onclick="selectAll(\'seds-yearsbox\',true)" />';
			echo '<input type="button" name="Button" value="Select None" onclick="selectAll(document.getElementById(\'seds-yearsbox\'),false)" />';
			echo '</div>';
			echo '<div id="sedsjoblist-keywords"><h3>Keywords</h3><br>';
			echo '<input type="text" name="keywords">';
			echo '<br>Use "," to split keywords';
			echo '</div>';
			echo '<div id="sedsjoblist-submit">';
			echo '<input id="sedsjoblist-submitbox" type="submit" value="Search">';
			echo '</div>';
			echo '</form>';
		echo '</div>';//End of Job Search Box
	//POST Assignment
		if(isset($_POST['major']) || isset($_POST['category']) || isset($_POST['education']) || strlen($_POST['keywords'])>0 || isset($_POST['type'])) {
			$seds_catvalid = array("");
			for ($j=0;$j<count($_POST['category']);$j++) {
				$seds_catpostssql = 'SELECT `object_id` FROM `wp_term_relationships` WHERE `term_taxonomy_id`=' . $_POST['category'][$j];
				$seds_catposts = $wpdb->get_results($seds_catpostssql);
					for ($i=0;$i<count($seds_catposts);$i++) {
						$seds_catpostsarrtmp = get_object_vars($seds_catposts[$i]);
						$seds_catpostsarr[$j][$i] = $seds_catpostsarrtmp['object_id'];
					}
				
				//Reduce to one set of posts
				$seds_catvalid = array_merge($seds_catvalid, $seds_catpostsarr[$j]);
				}
				array_unique($seds_catvalid);
				unset($seds_catvalid[0]);
				sort($seds_catvalid); //Finishes running cat valid; returns all valid category posts
				
				//Begin Looping through for set meta items
					
					if(strlen($_POST['keywords'])>0) {
							//data5 query
							for ($i=0;$i<count($seds_catvalid);$i++) { //Each valid catagory post is i
								$data5_meta = get_post_meta($seds_catvalid[$i],"data5");
								$sedsjob_keywords = explode("," ,$_POST['keywords']);
								for ($j=0;$j<count($sedsjob_keywords);$j++) {
									if (strpos($data5_meta[0],$sedsjob_keywords[$j]) !== false) {
									//String found
										$data5_metatrue[$seds_catvalid[$i]] = 1;
									} elseif(isset($data5_metatrue[$seds_catvalid[$i]])) {
									//Do nothing, already true
									} else {
										$data5_metatrue[$seds_catvalid[$i]] = 0;	
									}
							  	}
							}
					} //Returns $data5_metatrue which array of the keywords if the year is found
					
					if(isset($_POST['major'])) {
						//data7 query
							for ($i=0;$i<count($seds_catvalid);$i++) { //Each valid catagory post is i
								$data7_meta = get_post_meta($seds_catvalid[$i],"data7");
								for ($j=0;$j<count($_POST['major']);$j++) {
									$seds_majorsearchstring = $nasaoption[$_POST['major'][$j]];
										if ($seds_majorsearchstring == "Engineering-Aerospace Eng.") {
											$seds_majorsearchstring = "Aerospace";
										}
									if (strpos($data7_meta[0],$seds_majorsearchstring) !== false) {
									//String found
										$data7_metatrue[$seds_catvalid[$i]] = 1;
									} elseif(isset($data7_metatrue[$seds_catvalid[$i]])) {
									//Do nothing, already true
									} else {
										$data7_metatrue[$seds_catvalid[$i]] = 0;	
									}
							  	}
							}
					} //Returns $data7_metatrue which array of 1 if the major is found
					
					if(isset($_POST['education'])) {
						//data8 query
							for ($i=0;$i<count($seds_catvalid);$i++) { //Each valid catagory post is i
								$data8_meta = get_post_meta($seds_catvalid[$i],"data8");
								for ($j=0;$j<count($_POST['education']);$j++) {
									if (strpos($data8_meta[0],$nasayears[$_POST['education'][$j]]) !== false) {
									//String found
										$data8_metatrue[$seds_catvalid[$i]] = 1;
									} elseif(isset($data8_metatrue[$seds_catvalid[$i]])) {
									//Do nothing, already true
									} else {
										$data8_metatrue[$seds_catvalid[$i]] = 0;	
									}
							  	}
							}
					} //Returns $data8_metatrue which array of 1 if the year is found
					
					if(isset($_POST['type'])) {
						//type data6 query
							for ($i=0;$i<count($seds_catvalid);$i++) { //Each valid catagory post is i
								$data6_meta = get_post_meta($seds_catvalid[$i],"data6");
								for ($j=0;$j<count($_POST['type']);$j++) {
									$seds_typesearchstring = $jobmanopts[$_POST['type'][$j]];
									if (strpos($data6_meta[0],$seds_typesearchstring) !== false) {
										//String found
										$data6_metatrue[$seds_catvalid[$i]] = 1;
									} elseif(isset($data6_metatrue[$seds_catvalid[$i]])) {
									//Do nothing, already true
									} else {
										$data6_metatrue[$seds_catvalid[$i]] = 0;	
									}
							  	}
							}
					} //Returns $data6_metatrue which array of 1 if the year is found
					
					
				$j=0;
				for ($i=0;$i<count($seds_catvalid);$i++) { //Each valid catagory post is i
					$meta_true = $data8_metatrue[$seds_catvalid[$i]]+$data7_metatrue[$seds_catvalid[$i]]+$data5_metatrue[$seds_catvalid[$i]]+$data6_metatrue[$seds_catvalid[$i]];
					$meta_truearr[$i] = $meta_true;
					if ($meta_true > 0) {
					 //At least one value is true
					 $seds_jobdisplay[$j] = $seds_catvalid[$i];
					 $j++;
					} else {
					 //No values true; do nothing	
					}
					
					if (!isset($_POST['major']) && !isset($_POST['education']) && !isset($_POST['major']) && !strlen($_POST['keywords'] && !isset($_POST['type']))>0) {
						$seds_jobdisplay[$j] = $seds_catvalid[$i];
					}
				} 
		$seds_jobsarr = $seds_jobdisplay;
		for ($k=0;$k<count($seds_jobsarr);$k++) {
		$seds_jobpostarr[$k] = get_the_title($seds_jobsarr[$k]);
		}
		//Display each record queried
		
		
		echo '<table id="sedsjoblist-container" border="1">';
		for ($i=0;$i<count($seds_jobpostarr);$i++) {
				echo '<tr id="sedsjoblist-upperrow">';
					echo '<td id="sedsjoblist-lefthead">';
					echo 'Title: ';
					echo '</td>';
					
					echo '<td id="sedsjoblist-left">';
					echo '<a href="'. get_permalink($seds_jobsarr[$i]) . '">' . $seds_jobpostarr[$i] . "</a>";
					echo '</td>';
				
					echo '<td id="sedsjoblist-locationhead">';
					echo 'Location: ';
					echo '</td>';
					
					echo '<td id="sedsjoblist-right">';
						$seds_location = get_post_meta($seds_jobsarr[$i],"data9");
					echo $seds_location[0];
					echo '</td>';
				echo '</tr>';//sedsjoblist-lefthead end
				
				echo '<tr id="sedsjoblist-lowerrow">';
					echo '<td id="sedsjoblist-lefthead">';
					echo 'Company / Category: ';
					echo '</td>';
					
					echo '<td id="sedsjoblist-bottommiddle" >';
					$seds_catnames = get_the_term_list($seds_jobsarr[$i],"jobman_category","",",");
					echo $seds_catnames;
					echo '</td>';
					
					echo '<td id="sedsjoblist-bottomrighthead" >';
					echo "Type: ";
					echo '</td>';
					echo '<td id="sedsjoblist-bottomright" >';
					$seds_type = get_post_meta($seds_jobsarr[$i],"data6");
					echo $seds_type[0];
					echo '</td>';
				echo '</tr>';//bottom
				echo '<tr id="sedsjoblist-spacerrow">';
				echo '</tr>';//bottom
		}
		echo "</table>";
	
		}//End ISSET POST
		
	//Diagnostics
	/*
	echo "SQL String: " . $seds_jobsql . "<br>";
	echo "<br>POST: <pre>";
	print_r($_POST);
	echo "</pre><br>";	
	*/
	echo "jobmanopts: <pre>";
	print_r($jobmanopts);
	echo "</pre>";
	echo "POST<pre>";
	print_r($_POST);
	echo "</pre>";	
	/*
	echo "<br>POST: <pre>";
	print_r($nasaoption[7]);
	echo "</pre><br>";
	*/
	
}//End of shortcode block

add_shortcode( 'seds-jobsearch', 'seds_jobsearch' );

/* //Working code for dropdown boxes

<script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
			<link href="http://www.test2.seds.org/wp-content/plugins/seds-code/select2/select2.css" rel="stylesheet"/>
			<script src="http://www.test2.seds.org/wp-content/plugins/seds-code/select2/select2.js"></script>
			<script>
			$(document).ready(function() { 
			$("#seds-majorsbox").select2(); 
			});
			</script>	

*/
			
			
/*

			<script>
			$(document).ready(function() { 
			$("#seds-majorsbox").select2(); 
			});
			</script>	
*/