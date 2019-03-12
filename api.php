<?php
	
		include_once('db_interactions.php');
		
		// Example: career-mapper/api.php?city=Fremont&state=CA
		if (isset($_GET['city']) && isset($_GET['state'])) {
			$city = $_GET['city'];
			$state = $_GET['state'];
			
			$data = getIndeces($mysqli, $city, $state);
			$json = json_encode($data);
			echo $json;
		}
	
?>