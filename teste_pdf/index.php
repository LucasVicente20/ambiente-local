<?php
require_once "classQrCode.php";


$objQrcode = new QrCode();
$objQrcode->TEXT("Eliakim Ramos");
$objQrcode->URL("https://eliakimramos.com.br/portifolio/");
$objQrcode->GeraQRCODE(400,"teste.png");
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
var chart = new google.visualization.PieChart(document.getElementById('piechart_3d'));
chart.draw(data, options);
}
</script>
</head>
<body>
<table style="table-layout: fixed; width: 350px;" border="1">
<tbody>
<tr>
<td>Reallyyyyyyyyyy Looooooong cell conteeeent</td>
<td>short</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>short</td>
<td>ReallyyyyyyyyyyLooooooong cell conteeeent</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>Reallyyyyyyyyyy Looooooong cell conteeeent</td>
<td>short</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>short</td>
<td>ReallyyyyyyyyyyLooooooong cell conteeeent</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>Reallyyyyyyyyyy Looooooong cell conteeeent</td>
<td>short</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>short</td>
<td>Eliakim Ramos conteeeent</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>Reallyyyyyyyyyy Looooooong cell conteeeent</td>
<td>short</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>short</td>
<td>ReallyyyyyyyyyyLooooooong cell conteeeent</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>Reallyyyyyyyyyy Looooooong cell conteeeent</td>
<td>short</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>short</td>
<td>ReallyyyyyyyyyyLooooooong cell conteeeent</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>Reallyyyyyyyyyy Looooooong cell conteeeent</td>
<td>short</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>short</td>
<td>ReallyyyyyyyyyyLooooooong cell conteeeent</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>Reallyyyyyyyyyy Looooooong cell conteeeent</td>
<td>short</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>short</td>
<td>ReallyyyyyyyyyyLooooooong cell conteeeent</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>Reallyyyyyyyyyy Looooooong cell conteeeent</td>
<td>short</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>short</td>
<td>ReallyyyyyyyyyyLooooooong cell conteeeent</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>Reallyyyyyyyyyy Looooooong cell conteeeent</td>
<td>short</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>short</td>
<td>ReallyyyyyyyyyyLooooooong cell conteeeent</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>Reallyyyyyyyyyy Looooooong cell conteeeent</td>
<td>short</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>short</td>
<td>ReallyyyyyyyyyyLooooooong cell conteeeent</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>Reallyyyyyyyyyy Looooooong cell conteeeent</td>
<td>short</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>short</td>
<td>ReallyyyyyyyyyyLooooooong cell conteeeent</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>Reallyyyyyyyyyy Looooooong cell conteeeent</td>
<td>short</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>short</td>
<td>ReallyyyyyyyyyyLooooooong cell conteeeent</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>Reallyyyyyyyyyy Looooooong cell conteeeent</td>
<td>short</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>short</td>
<td>ReallyyyyyyyyyyLooooooong cell conteeeent</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>Reallyyyyyyyyyy Looooooong cell conteeeent</td>
<td>short</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>short</td>
<td>ReallyyyyyyyyyyLooooooong cell conteeeent</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>Reallyyyyyyyyyy Looooooong cell conteeeent</td>
<td>short</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>short</td>
<td>ReallyyyyyyyyyyLooooooong cell conteeeent</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>Reallyyyyyyyyyy Looooooong cell conteeeent</td>
<td>short</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>short</td>
<td>ReallyyyyyyyyyyLooooooong cell conteeeent</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>Reallyyyyyyyyyy Looooooong cell conteeeent</td>
<td>short</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>short</td>
<td>ReallyyyyyyyyyyLooooooong cell conteeeent</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>Reallyyyyyyyyyy Looooooong cell conteeeent</td>
<td>short</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>short</td>
<td>ReallyyyyyyyyyyLooooooong cell conteeeent</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>Reallyyyyyyyyyy Looooooong cell conteeeent</td>
<td>short</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>short</td>
<td>ReallyyyyyyyyyyLooooooong cell conteeeent</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>Reallyyyyyyyyyy Looooooong cell conteeeent</td>
<td>short</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>short</td>
<td>ReallyyyyyyyyyyLooooooong cell conteeeent</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>Reallyyyyyyyyyy Looooooong cell conteeeent</td>
<td>short</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>short</td>
<td>ReallyyyyyyyyyyLooooooong cell conteeeent</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>Reallyyyyyyyyyy Looooooong cell conteeeent</td>
<td>short</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>short</td>
<td>ReallyyyyyyyyyyLooooooong cell conteeeent</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>Reallyyyyyyyyyy Looooooong cell conteeeent</td>
<td>short</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>short</td>
<td>ReallyyyyyyyyyyLooooooong cell conteeeent</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>Reallyyyyyyyyyy Looooooong cell conteeeent</td>
<td>short</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>short</td>
<td>ReallyyyyyyyyyyLooooooong cell conteeeent</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>Reallyyyyyyyyyy Looooooong cell conteeeent</td>
<td>short</td>
<td>Normal cell content</td>
</tr>
<tr>
<td>short</td>
<td>ReallyyyyyyyyyyLooooooong cell conteeeent</td>
<td>Normal cell content</td>
</tr>
</tbody>
</table>
<div>
<img src="teste.png">
<br>
<img src="https://tse2.mm.bing.net/th?id=OIP.ANBtqtE3FBxuRPVc1n5qhAHaJ4&pid=Api&P=0" alt="">
</div>
<div id="piechart_3d" style="width: 900px; height: 500px;"></div>
<a href="GeraPdf.php" target="_blank" rel="noopener noreferrer">Print Relatorio</a>
</body>
</html>