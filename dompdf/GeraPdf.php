<?php
session_start();
ini_set('display_errors', 0);
error_reporting(E_ALL ^ E_NOTICE);
require '../dompdf/vendor/autoload.php';
use Dompdf\Dompdf;
$html = $_SESSION['HTMLPDF']; // Session na qual deve ser salo o html
$attachment = $_SESSION['HTMLPDFDownload']; // Session na qual deve ser definido se vai baixar direto(true) ou abrir nova aba(false)
$orientacao = ($_SESSION['HTMLPDFMudaOrientacao'] == true)? "landscape":"portrait"; // Session que define se vai mudar de portaretrato
// Nos servidores windows e Linux tem diferença de barras e isso gera problema
$primeiroCaractere = substr(dirname(__FILE__), 0, 1);
if($primeiroCaractere == "/"){
    $caminho = str_replace("/dompdf", "", dirname(__FILE__));
}else{
    $caminho = str_replace("\dompdf", "", dirname(__FILE__));
}
$dompdf = new DOMPDF();
$dompdf->getOptions()->setChroot([$caminho]);
$dompdf->getOptions()->setIsRemoteEnabled(TRUE);
$dompdf->setPaper("A4", $orientacao);
$dompdf->loadHtml($html);
$dompdf->render();
$dompdf->stream('relatoriopdf.pdf',array("Attachment" => $attachment));
?>