<?php
session_start();
header("Content-Disposition: attachment; filename=" . urlencode($_GET['nome']));
header("Content-Type: application/pdf");
header("Content-Type: application/download");
// header("Content-Transfer-Encoding: Binary");
header("Content-Description: File Transfer");            
echo base64_decode($_SESSION['arquivo_download'][$_GET['arquivo']]);
?>