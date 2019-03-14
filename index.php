<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
		<title>Career Mapper</title>
		<link rel="stylesheet" href="styles.css">
	</head>
	<body>
		<h1 id="title">Career Mapper</h1>
		<div id="controls" class="nicebox">
			<div>
				<select id="census-variable">
					<option value="quality">Quality of Life</option>
<!--
					<option value=""></option>
					<option value=""></option>
					<option value=""></option>
-->
				</select>
			</div>
			<div id="legend">
				<div id="census-min">min</div>
				<div class="color-key"><span id="data-caret">&#x25c6;</span></div>
				<div id="census-max">max</div>
			</div>
		</div>
		<div id="data-box" class="nicebox">
			<label id="data-label" for="data-value"></label>
			<span id="data-value"></span>
		</div>
		<div id="map"></div>
		<div id="options">
			State: 
			<select id="state">
				<option value="init">--- Select State ---</option>
			</select>
			City: 
			<select id="city">
				<option value="init">--- Select City ---</option>
			</select>
			Data to Display:
			<select id="table">
				<option value="indexes">Index Data</option>
				<option value="jobs">Job Data</option>
			</select>
		</div>
		<div id="data-boxes">
			<div id="city-data">
				<h3 id="city-name">SELECT STATE</h3>
				<table id="city-table">
					<tr>
						<th>City</th>
						<th>Quality of Life</th>
						<th>Crime</th>
						<th>Groceries</th>
						<th>Health</th>
						<th>Pollution</th>
						<th>Rent</th>
						<th>Safety</th>
						<th>Traffic</th>
					</tr>
				</table>
			</div>
			<div id="job-data">
				<h3>Jobs</h3>
				<table id="job-table">
					<tr>
						<th>Name</th>
						<th>Number of Jobs</th>
						<th>Average Salary Min</th>
						<th>Average Salary Max</th>
					</tr>
				</table>
			</div>
		</div>
	<script>
		var mapStyle = [{
			'stylers': [{'visibility': 'off'}]
			}, {
			'featureType': 'landscape',
			'elementType': 'geometry',
			'stylers': [{'visibility': 'on'}, {'color': '#fcfcfc'}]
			}, {
			'featureType': 'water',
			'elementType': 'geometry',
			'stylers': [{'visibility': 'on'}, {'color': '#bfd4ff'}]
		}];
		
		var map;
		var data;
		var indexes;
		var censusMin = Number.MAX_VALUE, censusMax = -Number.MAX_VALUE;

		function initMap() {
		
			// load the map
			map = new google.maps.Map(document.getElementById('map'), {
				center: {lat: 40, lng: -100},
				zoom: 4,
				styles: mapStyle,
				streetViewControl: false,
				mapTypeControl: false,
				fullscreenControl: false
			});
			
			
			// set up the style rules and events for google.maps.Data
			map.data.setStyle(styleFeature);
			map.data.addListener('mouseover', mouseInToRegion);
			map.data.addListener('mouseout', mouseOutOfRegion);
			
			// wire up the button
			var selectBox = document.getElementById('census-variable');
			google.maps.event.addDomListener(selectBox, 'change', function() {
				let index = getSelected(selectBox);
				clearCensusData();
				loadIndex(index);
			});
			
			// state polygons only need to be loaded once, do them now
			loadMapShapes();
			
			// load career-mapper data
			loadStates();
			
			// load cities when new state selected
			let stateSelector = document.getElementById("state");
			stateSelector.addEventListener('change', function() {
				// load cities
				let state = getSelected(stateSelector);
				clearSelect('city');
				clearTable(document.getElementById("city-table"));
				loadCities(state);
				loadStateIndexes(state);
			});
			
			// load jobs when new city selected
			let citySelector = document.getElementById("city");
			citySelector.addEventListener('change', function() {
				// load job data of city selected
				let city = getSelected(citySelector);
				let state = getSelected(stateSelector);
				showCityIndex(city);
				loadJobs(city, state);
			});
			
			// show table depending on selection
			let tableSelector = document.getElementById("table");
			tableSelector.addEventListener('change', function() {
				// hide both tables
				$("#data-boxes div").css("display", "none");
				let selected = getSelected(tableSelector);
				if (selected == "indexes") {
					$("#city-data").css("display", "inline-block");
				} else {
					$("#job-data").css("display", "inline-block");
				}
			});
		}
		
		function getSelected(selector) {
			return selector.options[selector.selectedIndex].value;
		}

		/** Loads the state boundary polygons from a GeoJSON source. */
		function loadMapShapes() {
			// load US state outline polygons from a GeoJson file
			map.data.loadGeoJson('https://storage.googleapis.com/mapsdevsite/json/states.js',{ idPropertyName: 'NAME' });
			
			// wait for the request to complete by listening for the first feature to be
			// added
			google.maps.event.addListenerOnce(map.data, 'addfeature', function() {
				google.maps.event.trigger(document.getElementById('census-variable'),'change');
			});
		}
		
		function loadIndex(index) {
			$.ajax({
				url: "api.php",
				type: "GET",
				data: "index=" + index,
				dataType: 'json',
				success: function(json) {
					$.each(json, function(i, value) {
						let state = toStateName(value['state']);
						var indexValue = parseFloat(value[index]);
						
						if (indexValue < censusMin) {
							censusMin = indexValue;
						}
						if (indexValue > censusMax) {
							censusMax = indexValue;
						}
						
						if (state != undefined && indexValue != NaN) {
							map.data.getFeatureById(state).setProperty('census_variable', indexValue);	
						}
					});
					
					// update and display the legend
					document.getElementById('census-min').textContent = censusMin.toLocaleString();
					document.getElementById('census-max').textContent = censusMax.toLocaleString();
				}
			});
		}
		
		function loadStates() {
			$.ajax({
				url: "api.php",
				type: "GET",
				dataType: 'json',
				success: function(json) {
					$.each(json, function(i, value) {
						$("#state").append($('<option>').text(value).attr('value', value));
					});				
				}
			});
		}
		
		function loadStateIndexes(state) {
			$.ajax({
				url: "api.php",
				type: "GET",
				data: "index=all&state=" + state,
				dataType: 'json',
				success: function(json) {
					indexes = json;
					$.each(json, function(i, value) {
						addCity(value);
					});
				}
			});
			$("#city-name").text(toStateName(state));
		}
		
		function loadCities(state) {
			$.ajax({
				url: "api.php",
				type: "GET",
				data: "state=" + state,
				dataType: 'json',
				success: function(json) {
					$.each(json, function(i, value) {
						$("#city").append($('<option>').text(value).attr('value', value));
					});
				}
			});
		}
		
		function loadJobs(city, state) {
			$.ajax({
				url: "api.php",
				type: "GET",
				data: "city=" + city + "&state=" + state,
				dataType: 'json',
				success: function(json) {
					data = json;
					setData(city, state);
				}
			});
		}
		
		function setData(city, state) {
			// clear old data
			let jobTable = document.getElementById("job-table");
			clearTable(jobTable);
			
			// load city data
			$("#city-name").text(capitalize(city) + ", " + state);
			
			// load job data
			let jobs = data;
			$.each(jobs, function(i, value) {
				addJob(value);
			});		
		}
		
		function addJob(job) {
			$("#job-table").append("<tr class='job-data-row' id='" + job["name"] + "'>" + 
										"<td>" + job["name"] + "</td>" + 
										"<td>" + job["count"] + "</td>" + 
										"<td>" + formatter.format(job["salary_min"]) + "</td>" + 
										"<td>" + formatter.format(job["salary_max"]) + "</td>" + 
									"</tr>");
		}
		
		function addCity(city) {
			$("#city-table").append("<tr class='city-data-row' id='" + city["city"] + "'>" +
										"<td>" + city["city"] + "</td>" +
										"<td>" + city["quality_of_life"] + "</td>" +
										"<td>" + city["crime"] + "</td>" +
										"<td>" + city["groceries"] + "</td>" +
										"<td>" + city["health"] + "</td>" +
										"<td>" + city["pollution"] + "</td>" +
										"<td>" + city["rent"] + "</td>" +
										"<td>" + city["safety"] + "</td>" +
										"<td>" + city["traffic"] + "</td>" +
									"</tr>");
		}
		
		function capitalize(string) {
			return string.charAt(0).toUpperCase() + string.slice(1);
		}
		
		// remove old elements of selectors
		function clearSelect(id) {
			let e = document.getElementById(id);
			let n = e.options.length;
			for (var i = n; i > 0; i--) {
				e.options.remove(i);
			}
		}
		
		// removes data from data boxes
		function clearData() {
			let cityTable = document.getElementById("city-table");
			let jobsTable = document.getElementById("job-table");
			clearTable(cityTable);
			clearTable(jobsTable);
		}
		
		function clearTable(e) {
			let n = e.rows.length;
			for (var i = n - 1; i > 0; i--) {
				e.deleteRow(i);
			}
		}
		
		function showJob(name) {
			let jobTable = document.getElementById("job-table");
			clearTable(jobTable);
			$.each(data, function(i, value) {
				if (value["name"] == name) {
					addJob(value);
				}
			});
		}
		
		function showCityIndex(city) {
			let cityTable = document.getElementById("city-table");
			clearTable(cityTable);
			$.each(indexes, function(i, value) {
				if (value["city"] == city) {
					addCity(value);
				}
			});
		}

		/** Removes census data from each shape on the map and resets the UI. */
		function clearCensusData() {
			censusMin = Number.MAX_VALUE;
			censusMax = -Number.MAX_VALUE;
			map.data.forEach(function(row) {
				row.setProperty('census_variable', undefined);
			});
			document.getElementById('data-box').style.visibility = 'hidden';
			document.getElementById('data-caret').style.display = 'none';
		}

		/**
		* Applies a gradient style based on the 'census_variable' column.
		* This is the callback passed to data.setStyle() and is called for each row in
		* the data set.  Check out the docs for Data.StylingFunction.
		*
		* @param {google.maps.Data.Feature} feature
		*/
		function styleFeature(feature) {
			var low = [5, 69, 54];  // color of smallest datum
			var high = [151, 83, 34];   // color of largest datum
			
			// delta represents where the value sits between the min and max
			var delta = (feature.getProperty('census_variable') - censusMin) / (censusMax - censusMin);
			
			var color = [];
			for (var i = 0; i < 3; i++) {
				// calculate an integer color based on the delta
				color[i] = (high[i] - low[i]) * delta + low[i];
			}
			
			// determine whether to show this shape or not
			var showRow = true;
			if (feature.getProperty('census_variable') == null ||
				isNaN(feature.getProperty('census_variable'))) {
					showRow = false;
			}
			
			var outlineWeight = 0.5, zIndex = 1;
			if (feature.getProperty('state') === 'hover') {
				outlineWeight = zIndex = 2;
			}
			
			return {
				strokeWeight: outlineWeight,
				strokeColor: '#fff',
				zIndex: zIndex,
				fillColor: 'hsl(' + color[0] + ',' + color[1] + '%,' + color[2] + '%)',
				fillOpacity: 0.75,
				visible: showRow
			};
		}

		/**
		* Responds to the mouse-in event on a map shape (state).
		*
		* @param {?google.maps.MouseEvent} e
		*/
		function mouseInToRegion(e) {
			// set the hover state so the setStyle function can change the border
			e.feature.setProperty('state', 'hover');
			
			var percent = (e.feature.getProperty('census_variable') - censusMin) /
			(censusMax - censusMin) * 100;
			
			// update the label
			document.getElementById('data-label').textContent = e.feature.getProperty('NAME');
			document.getElementById('data-value').textContent = e.feature.getProperty('census_variable').toLocaleString();
			document.getElementById('data-box').style.display = 'block';
			document.getElementById('data-box').style.visibility = 'visible';
			document.getElementById('data-caret').style.display = 'block';
			document.getElementById('data-caret').style.paddingLeft = percent + '%';
		}
			
		/**
		* Responds to the mouse-out event on a map shape (state).
		*
		* @param {?google.maps.MouseEvent} e
		*/
		function mouseOutOfRegion(e) {
			// reset the hover state, returning the border to normal
			e.feature.setProperty('state', 'normal');
		}

		let formatter = new Intl.NumberFormat('en-US', {
			style: 'currency',
			currency: 'USD',
		});
		
		function toStateName(state) {
			let states = { 
				AZ: 'Arizona',
				AL: 'Alabama',
				AK: 'Alaska',
				AR: 'Arkansas',
				CA: 'California',
				CO: 'Colorado',
				CT: 'Connecticut',
				DC: 'District of Columbia',
				DE: 'Delaware',
				FL: 'Florida',
				GA: 'Georgia',
				HI: 'Hawaii',
				ID: 'Idaho',
				IL: 'Illinois',
				IN: 'Indiana',
				IA: 'Iowa',
				KS: 'Kansas',
				KY: 'Kentucky',
				LA: 'Louisiana',
				ME: 'Maine',
				MD: 'Maryland',
				MA: 'Massachusetts',
				MI: 'Michigan',
				MN: 'Minnesota',
				MS: 'Mississippi',
				MO: 'Missouri',
				MT: 'Montana',
				NE: 'Nebraska',
				NV: 'Nevada',
				NH: 'New Hampshire',
				NJ: 'New Jersey',
				NM: 'New Mexico',
				NY: 'New York',
				NC: 'North Carolina',
				ND: 'North Dakota',
				OH: 'Ohio',
				OK: 'Oklahoma',
				OR: 'Oregon',
				PA: 'Pennsylvania',
				RI: 'Rhode Island',
				SC: 'South Carolina',
				SD: 'South Dakota',
				TN: 'Tennessee',
				TX: 'Texas',
				UT: 'Utah',
				VT: 'Vermont',
				VA: 'Virginia',
				WA: 'Washington',
				WV: 'West Virginia',
				WI: 'Wisconsin',
				WY: 'Wyoming' 
			};
			
			return states[state];
		}

	</script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script async defer
		src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAS8p97oXW9Fbwg2ly4-zHxkmYZvag0MZc&callback=initMap">
	</script>
	</body>
</html>