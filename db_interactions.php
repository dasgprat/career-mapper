<?php
	
	include_once('dbconnect.php');
	
	function getStates($mysqli) {
		if (!($res = $mysqli->query("SELECT DISTINCT `l_state` FROM `cm_location` ORDER BY `l_state`"))) {
			echo "Failed Select";
		}
		
		$data = [];
		while ($row = $res->fetch_assoc()) {
			array_push($data, $row["l_state"]);
		}
		
		return $data;
	}
	
	function getCities($mysqli, $stateAbbreviation) {
		if (!($res = $mysqli->query("SELECT `l_city` FROM `cm_location` WHERE `l_state`='$stateAbbreviation'"))) {
			echo "Select Failed";
		}
		
		$data = [];
		while ($row = $res->fetch_assoc()) {
			array_push($data, $row["l_city"]);
		}
		
		return $data;
	}
	
	function getIndeces($mysqli, $city, $stateAbbreviation) {
		if (!($res = $mysqli->query("select p.p_name,count(j.jid),avg(j.j_salary_max),avg(j.j_salary_min) ,max(i.i_quality_of_life)  
	        from cm_job j left join cm_company c ON j.j_company = c.cid 
	    	left join cm_profession p ON j.j_profession = p.pid
	    	left join cm_location l ON j.j_location = l.lid 
	    	left join cm_index i on l.lid = i.i_location  
	    	where l.l_state = '$stateAbbreviation' and l.l_city = '$city'
	    	group by l.l_state,p.p_name"))) {
				echo "Failed Select";
		}
	
		$data = [];
		while ($row = $res->fetch_assoc()) {
			// reformat data
			$r = [];
			$r['name'] = $row['p_name'];
			$r['count'] = $row['count(j.jid)'];
			$r['salary_max'] = $row['avg(j.j_salary_max)'];
			$r['salary_min'] = $row['avg(j.j_salary_min)'];
			$r['quality_of_life'] = $row['max(i.i_quality_of_life)'];
			array_push($data, $r);	
		}
		
		return $data;
	}
	
	function getStateIndeces($mysqli, $stateAbbreviation) {
		if (!$res = $mysqli->query("select l.l_city,l.l_state,i.i_crime,i.i_groceries,i.i_health,i.i_location,i.i_pollution,i.i_quality_of_life,i.i_rent,i.i_safety,i.i_traffic 
			from cm_index i left join  cm_location l on i.iid = l.lid 
			where l.l_state = '$stateAbbreviation'")) {
				echo "Failed Select";
		}
		
		$data = [];
		while ($row = $res->fetch_assoc()) {
			$r = [];
			$r['city'] = $row['l_city'];
			$r['crime'] = $row['i_crime'];
			$r['groceries'] = $row['i_groceries'];
			$r['health'] = $row['i_health'];
			$r['pollution'] = $row['i_pollution'];
			$r['quality_of_life'] = $row['i_quality_of_life'];
			$r['rent'] = $row['i_rent'];
			$r['safety'] = $row['i_safety'];
			$r['traffic'] = $row['i_traffic'];
			array_push($data, $r);
		}
		
		return $data;
	}
	
	function getIndex($mysqli, $index) {
		$sql = "";
		switch ($index) {
			case 'quality':
				$sql = "select l.l_state, avg(i.i_quality_of_life) as avg_quality from cm_index i left join cm_location l on i.i_location = l.lid group by l.l_state order by i.i_quality_of_life desc";
				break;
			case 'crime':
				$sql= "select l.l_state, avg(i.i_crime) as avg_crime from cm_index i left join cm_location l on i.i_location = l.lid group by l.l_state order by i.i_crime desc";
				break;
			case 'groceries':
				$sql= "select l.l_state, avg(i.i_groceries) as avg_groceries from cm_index i left join cm_location l on i.i_location = l.lid group by l.l_state order by i.i_groceries desc";
				break;
			case 'health':
				$sql= "select l.l_state, avg(i.i_health) as avg_health from cm_index i left join cm_location l on i.i_location = l.lid group by l.l_state order by i.i_health desc";
				break;
			case 'pollution':
				$sql= "select l.l_state, avg(i.i_pollution) as avg_pollution from cm_index i left join cm_location l on i.i_location = l.lid group by l.l_state order by i.i_pollution desc";
				break;
			case 'rent':
				$sql= "select l.l_state, avg(i.i_rent) as avg_rent from cm_index i left join cm_location l on i.i_location = l.lid group by l.l_state order by i.i_rent desc";
				break;
			case 'safety':
				$sql= "select l.l_state, avg(i.i_safety) as avg_safety from cm_index i left join cm_location l on i.i_location = l.lid group by l.l_state order by i.i_safety desc";
				break;
			case 'traffic':
				$sql= "select l.l_state, avg(i.i_traffic) as avg_traffic from cm_index i left join cm_location l on i.i_location = l.lid group by l.l_state order by i.i_traffic desc";
				break;
			default: break;
		}
		
		if (!($res = $mysqli->query($sql))) {
			echo "Failed Select";
		}
		
		$data = [];
		while ($row = $res->fetch_assoc()) {
			$r = [];
			$r['state'] = $row['l_state'];
			$r['index'] = $row['avg_'.$index];
			array_push($data, $r);
		}
		
		return $data;
	}
?>