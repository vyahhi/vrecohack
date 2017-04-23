<html>
<head>
	<meta charset="UTF-8">
	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
	<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
	<style>
		@font-face {
		 font-family: roboto;
	 	 src: url(RobotoCondensed-Bold.ttf);
		}
		body, svg, text {
	 		font-family: roboto;
	 		color: #fff;
	 		background-color: #222;
		}
	</style>
</head>
<body>
<?php
	// Active assert and make it quiet

	# file format:
	# line 1: start mark
	# line 2: request
	# line 3: server
	# line 4: cookie
	# line 5: post
	# line 6: get
	# line 7: end mark
	####

	# dumps current HTTP headers including request params
	function dump_request() {
		$data = array($_REQUEST, $_SERVER, $_COOKIE, $_POST, $_GET);
		$fp = fopen('vrecohack.log', 'a');
		fwrite($fp, "===START===\n");
		foreach ($data as $item) {
			$line = serialize($item) . "\n";
			// print_r($line);
			fwrite($fp, $line);
		}
		fwrite($fp, "===END===\n");
		fclose($fp);
	}
	dump_request();

	# shows all previous data
	function get_data() {
		$stats = array();
		$fp = fopen('vrecohack.log', 'r');
		while (($line = fgets($fp)) !== false) {
			// print_r($line);
			assert(strpos($line, '===START===') !== false);
			$request = unserialize(fgets($fp));
			# skip what we don't need yet
			for ($i = 0; $i < 6; $i++) {
				fgets($fp);
				// $line = fgets($fp);
				// if (isset($line['REMOTE_ADDR'])) {
				// 	$ip = $line['REMOTE_ADDR'];
				// }
			}
			if (isset($request['action'])) {
				$step = $request['step'];
				$choice = $request['choice'];
				$stats[$step][$choice] += 1;
			}
		}
		fclose($fp);
		return $stats;
	}

	$stats = get_data();
	ksort($stats);
	$ip = $_SERVER['REMOTE_ADDR'];
	$details = json_decode(file_get_contents("https://ipapi.co/{$ip}/json"));
	$names = array(
		0 => array('name' => 'Trip to Work', 0 => 'Car', 1 => 'Bike'),
		1 => array('name' => 'Coffee', 0 => 'Iced', 1 => 'Hot'),
		2 => array('name' => 'Reading', 0 => 'Kindle', 1 => 'Books'),
		3 => array('name' => 'Grocery Store', 0 => 'Plastic Bags', 1 => 'Go Home'),
		4 => array('name' => 'Child Birthday', 0 => 'Action Figure', 1 => 'Crayons'),
		5 => array('name' => 'Trip', 0 => 'Plane', 1 => 'Drive'),
	);

	echo '<div style="float: right; height: 400px; width: 400px;" id="map"></div>';
	echo "	<script>
	  function initMap() {
		var uluru = {lat: $details->latitude, lng: $details->longitude};
		var map = new google.maps.Map(document.getElementById('map'), {
		  zoom: 13,
		  center: uluru,
		  styles: [
			{elementType: 'geometry', stylers: [{color: '#242f3e'}]},
			{elementType: 'labels.text.stroke', stylers: [{color: '#242f3e'}]},
			{elementType: 'labels.text.fill', stylers: [{color: '#746855'}]},
			{
			  featureType: 'administrative.locality',
			  elementType: 'labels.text.fill',
			  stylers: [{color: '#d59563'}]
			},
			{
			  featureType: 'poi',
			  elementType: 'labels.text.fill',
			  stylers: [{color: '#d59563'}]
			},
			{
			  featureType: 'poi.park',
			  elementType: 'geometry',
			  stylers: [{color: '#263c3f'}]
			},
			{
			  featureType: 'poi.park',
			  elementType: 'labels.text.fill',
			  stylers: [{color: '#6b9a76'}]
			},
			{
			  featureType: 'road',
			  elementType: 'geometry',
			  stylers: [{color: '#38414e'}]
			},
			{
			  featureType: 'road',
			  elementType: 'geometry.stroke',
			  stylers: [{color: '#212a37'}]
			},
			{
			  featureType: 'road',
			  elementType: 'labels.text.fill',
			  stylers: [{color: '#9ca5b3'}]
			},
			{
			  featureType: 'road.highway',
			  elementType: 'geometry',
			  stylers: [{color: '#746855'}]
			},
			{
			  featureType: 'road.highway',
			  elementType: 'geometry.stroke',
			  stylers: [{color: '#1f2835'}]
			},
			{
			  featureType: 'road.highway',
			  elementType: 'labels.text.fill',
			  stylers: [{color: '#f3d19c'}]
			},
			{
			  featureType: 'transit',
			  elementType: 'geometry',
			  stylers: [{color: '#2f3948'}]
			},
			{
			  featureType: 'transit.station',
			  elementType: 'labels.text.fill',
			  stylers: [{color: '#d59563'}]
			},
			{
			  featureType: 'water',
			  elementType: 'geometry',
			  stylers: [{color: '#17263c'}]
			},
			{
			  featureType: 'water',
			  elementType: 'labels.text.fill',
			  stylers: [{color: '#515c6d'}]
			},
			{
			  featureType: 'water',
			  elementType: 'labels.text.stroke',
			  stylers: [{color: '#17263c'}]
			}
		  ]
		});
		var marker = new google.maps.Circle({
		  center: uluru,
		  map: map,
		  radius: 1609,
		  fillColor: '#6bc5ab',
		  fillOpacity: 0.35,
		});
	  }
	</script>";
	echo '<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDEhXQpJ1SOaG9sgpfp1SjwQ94goauC298&callback=initMap"></script>';
	//echo '<div style="float: right"><iframe width="400" height="400" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?q=' . $details->latitude . ',' . $details->longitude . '&hl=es&z=13&output=embed"></iframe></div>';
	echo '<div>';
	echo '<h1 style="text-align: center">FUTUREVIEWAR<br>🚮♻️🌎🌟🎉</h1>';
	echo '<h2>Those who live within 1-mile radius from you (' . $details->city . ', ' . $details->region . ', ' . $details->country . ') made the following choices:</h2>';
	echo '</div>';
	foreach ($stats as $step => $choices) {
		ksort($choices);
		$step_text = $names[$step]['name'];
		echo "<div id='chart_div_$step' style='display: inline-block;'></div>";
		echo '<script type="text/javascript">';
		echo "var options_$step = {'title':'$step_text', 'width':400, 'height':400, 'is3D':true, 'backgroundColor': '#222', slices: {
			0: { color: '#6bc5ab' },
			1: { color: '#8b6bc5' }
		  },
		  'titleTextStyle': {'color': '#fff', 'fontSize': '20'},
		  'pieSliceTextStyle': {'color': '#fff', 'fontSize': '20'},
		  'legend': {'position': 'bottom', 'textStyle': {'color': '#fff', 'fontSize': '20'}},
		 };";
		echo "google.charts.load('current', {'packages':['corechart']});";
		echo "google.charts.setOnLoadCallback(drawChart_$step);";
		echo "function drawChart_$step() {";
		echo 'var data = new google.visualization.DataTable();';
		echo "data.addColumn('string', 'Topping');";
		echo "data.addColumn('number', 'Slices');";
		echo "data.addRows([";
		foreach ($choices as $choice => $value) {
			$choice_text = $names[$step][$choice];
			echo "['$choice_text', $value],";
		}
		echo ']);'; 
		echo "var chart = new google.visualization.PieChart(document.getElementById('chart_div_$step'));";
		echo "chart.draw(data, options_$step);";
		echo '}';
		echo '</script>';
	}
?>
</body>
</html>
