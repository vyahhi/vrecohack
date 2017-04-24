<html>
<head>
	<meta charset="UTF-8">
	<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Lato" />
	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
	<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
	<style>
/*		@font-face {
		 font-family: roboto;
	 	 src: url(Lato-Regular.ttf);
		}
*/		body, svg, text {
	 		font-family: Lato;
	 		color: #fff;
	 		background-color: #222;
	 		font-weight: normal;
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
		3 => array('name' => 'Grocery Store', 0 => 'Buy Plastic Bags', 1 => 'Go Home'),
		4 => array('name' => 'Child Birthday', 0 => 'Action Figure', 1 => 'Crayons'),
		5 => array('name' => 'Trip', 0 => 'Plane', 1 => 'Drive'),
	);

	echo '<div style="float: right; height: 400px; width: 400px; padding-bottom: 20px;" id="map"></div>';
	echo "	<script>
	  function initMap() {
		var uluru = {lat: $details->latitude, lng: $details->longitude};
		var map = new google.maps.Map(document.getElementById('map'), {
		  zoom: 13,
		  center: uluru,
		  styles: [
			{
				featureType: 'road',
				elementType: 'labels',
				stylers: [
					{ visibility: 'off' }
				]
			},
			{
				featureType: 'poi',
				elementType: 'labels',
				stylers: [
					{ visibility: 'off' }
				]
			},
			{elementType: 'geometry', stylers: [{color: '#222222'}]},
			{elementType: 'labels.text.stroke', stylers: [{color: '#222222'}]},
			{elementType: 'labels.text.fill', stylers: [{color: '#999999'}]},
			{
			  featureType: 'administrative.locality',
			  elementType: 'labels.text.fill',
			  stylers: [{color: '#999999'}]
			},
			{
			  featureType: 'poi.park',
			  elementType: 'geometry',
			  stylers: [{color: '#226622'}]
			},
			{
			  featureType: 'road',
			  elementType: 'geometry',
			  stylers: [{color: '#333333'}]
			},
			{
			  featureType: 'road',
			  elementType: 'geometry.stroke',
			  stylers: [{color: '#000000'}]
			},
			{
			  featureType: 'road.highway',
			  elementType: 'geometry',
			  stylers: [{color: '#666666'}]
			},
			{
			  featureType: 'road.highway',
			  elementType: 'geometry.stroke',
			  stylers: [{color: '#000000'}]
			},
			{
			  featureType: 'transit',
			  elementType: 'geometry',
			  stylers: [{color: '#333333'}]
			},
			{
			  featureType: 'water',
			  elementType: 'geometry',
			  stylers: [{color: '#222266'}]
			},
			{
			  featureType: 'water',
			  elementType: 'labels.text.fill',
			  stylers: [{color: '#999999'}]
			},
			{
			  featureType: 'water',
			  elementType: 'labels.text.stroke',
			  stylers: [{color: '#222222'}]
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
	echo '<h1 style="text-align: center">FUTUREVIEWAR</h1>';
	echo '<center><img src="logo-white.png" height="350"></center>';
	echo '<h2 style="padding: 30px; clear: both;">Those who live within 1-mile radius from you (' . $details->city . ', ' . $details->region . ', ' . $details->country . ') made the following choices:</h2>';
	echo '</div>';
	foreach ($stats as $step => $choices) {
		ksort($choices);
		if (!isset($names[$step])) continue;
		$step_text = '#' . ($step + 1) . ' ' . $names[$step]['name'] . ':';
		//echo "<h3>$step_text</h3>";
		echo "<div id='chart_div_$step'></div>";
		echo '<script type="text/javascript">';
		echo "var options_$step = {'title':'$step_text', 'width':'100%', 'height':400, 'is3D':true, 'backgroundColor': '#222', slices: {
			0: { color: '#6bc5ab' },
			1: { color: '#8b6bc5' }
		  },
		  //titlePosition: 'none', 
		  'titleTextStyle': {'color': '#fff', 'fontSize': '20'},
		  'pieSliceTextStyle': {'color': '#fff', 'fontSize': '20'},
		  'legend': {'position': 'labeled', 'textStyle': {'color': '#fff', 'fontSize': '20'}},
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
