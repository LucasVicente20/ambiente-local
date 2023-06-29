<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: erro.php
# Autor   : Roberta Costa
# Data    : 30/09/2005
# Alterado: Álvaro Faria
# Data    : 03/07/2006
# Objetivo: Exibir Página de Erro (Obsoleto. Usar a função EmailErroDB). mantido por compatibilidade.
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "funcoes.php";

# Executa o controle de segurança #
session_start();
?>
<html>
<head>
<title>Portal de Compras - Prefeitura do Recife</title>
<script language="javascript" type="">
<!--
<?php MenuAcesso(); ?>
//-->
</script>
<style type="text/css">
	#Titulo {position: absolute; z-index: 1; visibility: visible; left: -1; top: 15;}
	#BgMenu{position: absolute; z-index: 0; visibility: visible; left: -12; top: -4;}
</style>
<link rel="Stylesheet" type="Text/Css" href="estilo.css">
</head>
<div id="Titulo">
  <img src="midia/titulo.jpg" border="0" alt="">
</div>
<div id="BgMenu">
  <img src="midia/bg_menu.gif" border="0" alt="">
</div>
<body background="midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="menu.js"></script>
<script language="JavaScript">Init();</script>
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="midia/linha.gif" alt=""></td>
		<td>
			<table border="0" summary="">
				<tr>
					<td align="left" class="textonormal">
						<font class="titulo2">|</font>
						<a href="index.php"><font color="#000000">P&aacute;gina Principal</font></a>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<tr>
		<td width="150"></td>
		<td align="left" colspan="2"> <?php if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,$Mod); } ?> </td>
	</tr>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="150"></td>
		<td>
			<table border="0" cellpadding="5" summary="">
				<tr>
					<td class="textonormal" >
						<p align="justify">
						Prezado usuário,<br><br>Ocorreu um erro. O sistema enviou um email para o analista responsável informando o ocorrido.
						Tente novamente mais tarde.<br><br>Obrigado.
						</p>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<!-- Fim do Corpo -->
</table>
</body>
</html>
