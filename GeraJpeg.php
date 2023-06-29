<?php
#-------------------------------------------------------------------------
# Programa: GeraJpeg.php
# Autor   : Luciano Mauro
# Data    : 16/09/2003
# Objetivo: Gera uma Imagem em Formato Jpeg
#-------------------------------------------------------------------------

session_start();
# Seleciona Letras Aleatoriamente e Registra em VariÃ¡vel de SessÃ£o #
$Letras = array("A","B","C","D","E","F","G","H","I","J","K","L","M",
                "N","P","Q","R","S","T","U","V","W","X","Y","Z",2,3,4,5,6,7,8,9);
srand((float)microtime()*1000000);
shuffle($Letras); $_Combinacao_ = "";
for( $C = 0; $C < 5; $C++ ) { $_Combinacao_ .= $Letras[$C]; }
if( session_is_registered('_Combinacao_') ) {
 		$_SESSION['_Combinacao_'] = $_Combinacao_;
}else{
		session_register('_Combinacao_'); $_SESSION['_Combinacao_'] = $_Combinacao_;
}

// Acrescenta espeço em branco.
$SpacedChars = "";
for ($i = 0; $i < strlen($_SESSION["_Combinacao_"]); $i++ )
	$SpacedChars .= substr($_SESSION["_Combinacao_"], $i, 1) . " ";

// Configura documento para imagem.
header("Content-Type: image/jpeg");

// Objeto de imagem.
$Image  = imagecreate(90,18);

// Cores da fundo da imagem.
$Azul       = imagecolorallocate($Image, 0,   88,  204);
$Laranja    = imagecolorallocate($Image, 255, 102, 0);
$Verde      = imagecolorallocate($Image, 59,  160, 120);
$Vermelho   = imagecolorallocate($Image, 228, 0,   68);
$RoxoEscuro = imagecolorallocate($Image, 116, 0,   204);
$RoxoClaro  = imagecolorallocate($Image, 219, 80,  214);
// Cores da letra da imagem.
$Branco     = imagecolorallocate($Image, 255, 255, 255);
$Preto      = imagecolorallocate($Image, 0,   0,   0);

$Colors = array($Azul,$Laranja,$Verde,$Vermelho,$RoxoEscuro,$RoxoClaro);

// Randomiza cores.
srand((float)microtime()*1000000);
shuffle($Colors);
// Altura da linha colorida.
$Height = 3;
// Gera linha horizontal.
$Line = 0;
for ($i = 0; $i < 5; $i++)
{
	for ($l = $Line; $l < ($Line + $Height); $l++)
		imageline($Image,0,$l,100,$l,$Colors[$i]);
	$Line += $Height;
}

// Randomiza cores.
srand((float)microtime()*1000000);
shuffle($Colors);
// Largura da linha colorida.
$Width = 3;
// Gera linha vertical.
$Line = 0;
for ($i = 0; $i < 15; $i++)
{
	for ($l = $Line; $l < ($Line + $Width); $l++)
		imageline($Image,$l,0,$l,100,$Colors[$i]);
	$Line += $Width * 2;
}

imagestring($Image,5,4,0,$SpacedChars,$Preto);
imagestring($Image,5,6,0,$SpacedChars,$Branco);
imagejpeg($Image);
?>
