<?php
	
		include_once('db_interactions.php');
		
		// Example: career-mapper/api.php?index=quality
		if (isset($_GET['index'])) {
			$index = $_GET['index'];
			$data = getIndex($mysqli, $index);
			$json = json_encode($data);
			echo $json;
		}
		
		// Example: career-mapper/api.php?city=Fremont&state=CA
		else if (isset($_GET['city']) && isset($_GET['state'])) {
			$city = $_GET['city'];
			$state = $_GET['state'];
			
			$data = getIndeces($mysqli, $city, $state);
			$json = json_encode($data);
			echo $json;
		} 
		
		// Example: career-mapper/api.php?state=CA
		else if (isset($_GET['state'])) {
			$state = $_GET['state'];
			$data = getCities($mysqli, $state);
			$json = json_encode($data);
			echo $json;
		} 
		
		// Example: career-mapper/api.php
		else {
			$data = getStates($mysqli);
			$json = json_encode($data);
			echo $json;
		}
	
?>