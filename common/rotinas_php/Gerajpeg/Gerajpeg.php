<?php 
###############################################################
# Programa: GeraJpeg.php
# Autor   : Luciano Mauro
# Data    : 16/09/2003
# Objetivo: Gera uma Imagem em Formato Jpeg
###############################################################
# Alterado Por: Luciano Mauro
# Data        : 13/11/2013
# Alteração   : Definir o caminho para salvar os dados de sessão 
###############################################################
 ini_set('display_errors', 0);
        error_reporting(E_ALL ^ E_NOTICE);
# Define o caminho para salvar os dados de sessão
$sessionPath = !empty($_GET['sessionPath']) ? $_GET['sessionPath'] : "";
if ($sessionPath != "") { ini_set("session.save_path","$sessionPath"); }
# Define o caminho para salvar os dados de sessão

session_start();
# Seleciona Letras Aleatoriamente e Registra em Variavel de Sessao #
$Letras = array("A","B","C","D","E","F","G","H","I","J","K","L","M",
"N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
srand((float)microtime()*1000000);
shuffle($Letras); $_Combinacao_ = "";
for ( $C = 0; $C < 5; $C++ ) { $_Combinacao_ .= $Letras[$C]; }
$_SESSION['_Combinacao_'] = $_Combinacao_;
# Seleciona Letras Aleatoriamente e Registra em Variavel de Sessao #
# Gera Imagem #
header("Expires: 0");
header("Cache-Control: private");
header("Content-Type: image/jpeg");
$Id = imagecreate(100,25);
$Branco = imagecolorallocate($Id, 250, 250, 250); 	
$Preto = imagecolorallocate($Id, 10, 10, 10);
imagefill($Id,0,0,$Branco);
$Valor = localtime(time(),1);
if ( substr($Valor["tm_sec"],1,1)  > 3 ) { $Fonte = imageloadfont("GeraJpegFontes/comen.gdf"); }
else { $Fonte = imageloadfont("GeraJpegFontes/comep.gdf"); }
imagestring($Id,$Fonte,3,0,$_Combinacao_,$Preto);
imagejpeg($Id);   
# Gera Imagem #
?>