<?php 
session_start();
?>
<html>
	<head>
		<title>Insert title here</title>
	    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
		<script type="text/javascript">
			google.charts.load("current", {packages:["corechart"]});
			google.charts.setOnLoadCallback(drawChart);
			function drawChart() {
				var data = google.visualization.arrayToDataTable([
								['Task', 'Hours per Day'],
								['Work',     11],
								['Eat',      2],
								['Commute',  2],
								['Watch TV', 2],
								['Sleep',    7]
							]);
			var options = {
							title: 'My Daily Activities',
							is3D: true,
						  };
			 var chart_div = document.getElementById('chart_div');
			  var chart = new google.visualization.ColumnChart(chart_div);

			  // Wait for the chart to finish drawing before calling the getImageURI() method.
			  google.visualization.events.addListener(chart, 'ready', function () {
				chart_div.innerHTML =  chart.getImageURI();
				console.log(chart_div.innerHTML);
			  });

			  chart.draw(data, options);

			}
</script>
</head>
<body>
	<div id='chart_div'></div>
</body>
</html>