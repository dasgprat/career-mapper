<!DOCTYPE html>
<html>
	<head>
		<title>Career Mapper</title>
		<style>
			#map {
				height: 500px;
				width: 100%;
			}
			
			.title {
				text-align: center;
			}
		</style>
	</head>
	<body>
		<h1 class="title">Career Mapper</h1>
		<div id="map"></div>
		
		<script>
			
			var map;
			
			function initMap() {
				var usa = {lat: 40, lng: -100};
				
				map = new google.maps.Map(document.getElementById('map'), { 
					zoom: 4, 
					center: usa,
					styles: mapStyle
				});
				
				loadMapShapes();
				
				var mapStyle = [{
					'featureType': 'all',
					'elementType': 'all',
					'stylers': [{'visibility': 'off'}]
				}, {
					'featureType': 'landscape',
					'elementType': 'geometry',
					'stylers': [{'visibility': 'on'}, {'color': '#fcfcfc'}]
				}, {
					'featureType': 'water',
					'elementType': 'labels',
					'stylers': [{'visibility': 'off'}]
				}, {
					'featureType': 'water',
					'elementType': 'geometry',
					'stylers': [{'visibility': 'on'}, {'hue': '#5f94ff'}, {'lightness': 60}]
				}];
			}
			
			function loadMapShapes() {
				map.data.loadGeoJson('https://storage.googleapis.com/mapsdevsite/json/states.js', { idPropertyName: 'STATE' });
			}
		</script>
		<script async defer
			src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAS8p97oXW9Fbwg2ly4-zHxkmYZvag0MZc&callback=initMap">
    </script>
	</body>
</html>