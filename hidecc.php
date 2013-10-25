<?php
function hidecc( $atts ){
	$str = <<<MY_MARKER
	<script type="text/javascript">
	var int=self.setInterval(function(){myFunction()},1000);
	function myFunction()
	{
	if (document.getElementsByTagName("div")["pricevalue"].textContent=="$ 0.00") {
	document.getElementById('payment_information').style.display = "none";
	} else {
	document.getElementById('payment_information').style.display = "inherit";
	}
	}
	</script>
MY_MARKER;
	echo $str;
}
add_shortcode( 'hidecc', 'hidecc' );