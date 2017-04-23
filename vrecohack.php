<html>
<head>
	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
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
	echo '<h3>Those who live within 5-mile radius from you (' . $details->city . ', ' . $details->region . ', ' . $details->country . ') made the following choices:</h3>';
	foreach ($stats as $step => $choices) {
		// echo '<li>';
		ksort($choices);
		#echo "Step #$step";
		echo "<div id='chart_div_$step' style='display: inline-block;'></div>";
		echo '<script type="text/javascript">';
		echo "var options_$step = {'title':'Step $step', 'width':300, 'height':200, 'legend':'bottom', 'is3D':true};";
		echo "google.charts.load('current', {'packages':['corechart']});";
		echo "google.charts.setOnLoadCallback(drawChart_$step);";
		echo "function drawChart_$step() {";
		echo 'var data = new google.visualization.DataTable();';
        echo "data.addColumn('string', 'Topping');";
        echo "data.addColumn('number', 'Slices');";
        echo "data.addRows([";
		foreach ($choices as $choice => $value) {
			echo "['$choice', $value],";
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
