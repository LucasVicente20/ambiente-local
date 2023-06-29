<?php

/**
 * Portal da DGCO
 *
 * PHP version 5.2.5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category  Pitang Novo Layout
 * @package   App
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version   GIT: EMPREL-SAD-PORTAL-COMPRAS-REL-COD-20160705-1013
 *
 *
 * HISTORICO DE ALTERAÇÔES
 * -----------------------------------------------------------------------
 *  Alterado: Pitang Agile TI
 *  Data:     04/07/2016
 *  Objetivo: Requisito 136739: Cartilhas, Guias e Manuais - Nova funcionalidade internet e intranet (#446)
 */

// Acesso ao arquivo de funções #
include "../funcoes.php";

// Executa o controle de segurança #
session_start();
Seguranca();

// Aumenta o tempo de espera do servidor web para término de execução da página #
set_time_limit(3000);

// Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Botao = $_POST['Botao'];
    $Titulo = $_POST['Titulo'];
    $Descricao = $_POST['Descricao'];
} else {
    $Critica = $_GET['Critica'];
    $Mensagem = $_GET['Mensagem'];
    $Mens = $_GET['Mens'];
}

// Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;
$NomePrograma = "ConsDocumento.php";

$Mens = 0;
$Tipo = 0;
$Mensagem .= "";

$db = Conexao();
$sql = "
	SELECT
		CDOCPOCODI, EDOCPOARQU, EDOCPOTITU, EDOCPODESC, CDOCPOUSAL,
		CDOCPOGRAL, TDOCPOULAT, EDOCPOARQS
	FROM
		SFPC.TBDOCUMENTACAOPORTAL
    WHERE FDOCPOTIPO LIKE 'C'
	ORDER BY
		EDOCPOTITU, EDOCPOARQU
";

$resDocs = $db->query($sql);
if (PEAR::isError($resDocs)) {
    EmailErroSQL("Erro de SQL em " . $NomePrograma, __FILE__, __LINE__, "SQL falhou.", $sql, $resDocs);
    exit(0);
}

?>
<html>
<?php
// Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.Rot.Botao.value=valor;
	document.Rot.submit();
}
function janela( pageToLoad, winName, width, height, center) {
	xposition=0;
	yposition=0;
	if ((parseInt(navigator.appVersion) >= 4 ) && (center)){
		xposition = (screen.width - width) / 2;
		yposition = (screen.height - height) / 2;
	}
	args = "width=" + width + ","
	+ "height=" + height + ","
	+ "location=0,"
	+ "menubar=0,"
	+ "resizable=0,"
	+ "scrollbars=0,"
	+ "status=0,"
	+ "titlebar=no,"
	+ "toolbar=0,"
	+ "hotkeys=0,"
	+ "z-lock=1," //Netscape Only
	+ "screenx=" + xposition + "," //Netscape Only
	+ "screeny=" + yposition + "," //Netscape Only
	+ "left=" + xposition + "," //Internet Explore Only
	+ "top=" + yposition; //Internet Explore Only
	window.open( pageToLoad,winName,args );
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form enctype="multipart/form-data" action="RotEnvioDocumento.php"
		method="post" name="Rot">
		<br> <br> <br> <br>
		<table cellpadding="3" border="0" summary="">
			<!-- Caminho -->
			<tr>
				<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="2"><br> <font
					class="titulo2">|</font> <a href="../index.php"><font
						color="#000000">Página Principal</font></a> > Institucional >
					Consultar Cartilhas, Guias e Manuais > Consultar Documentação</td>
			</tr>
			<!-- Fim do Caminho-->

			<!-- Erro -->
	<?php if ($Mens == 1) {
    ?>
	<tr>
				<td width="100"></td>
				<td align="left" colspan="2"><?php ExibeMens($Mensagem, $Tipo, 1);
    ?></td>
			</tr>
	<?php 
} ?>
	<!-- Fim do Erro -->

			<!-- Corpo -->
			<tr>
				<td width="100"></td>
				<td class="textonormal">
					<table border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff"
						summary="">
						<tr>
							<td class="textonormal">
								<table border="1" cellpadding="3" cellspacing="0"
									bordercolor="#75ADE6" summary="" class="textonormal">
									<tr>
										<td align="center" bgcolor="#75ADE6" valign="middle"
											class="titulo3" colspan="4">CONSULTA DE CARTILHAS, GUIAS e
											MANUAIS</td>
									</tr>
								<?php
        if ($resDocs->numRows() > 0) {
            ?>
								<tr>
										<td align="center" class="titulo3" bgcolor="#F7F7F7">ARQUIVO</td>
										<td align="center" class="titulo3" bgcolor="#F7F7F7">TÍTULO</td>
										<td align="center" class="titulo3" bgcolor="#F7F7F7">
											DESCRIÇÃO</td>
										<td align="center" class="titulo3" bgcolor="#F7F7F7">DATA</td>
									</tr>
								<?php
            $linha = 0;
            $itr = 0;
            resetArquivoAcesso();
            while ($linha = $resDocs->fetchRow()) {
                $itr ++;
                $doccod = $linha[0];
                $docarq = $linha[1];
                $doctit = $linha[2];
                $docdesc = $linha[3];
                $docusr = $linha[4];
                $docgrp = $linha[5];
                $docdata = $linha[6];
                $docarqserv = $linha[7];
                $arquivo = 'institucional/' . $docarqserv;
                addArquivoAcesso($arquivo);
                
                ?>
									<tr>
										<td valign="top" bgcolor="#F7F7F7" class="textonormal"><a
											href="../carregarArquivo.php?arq=<?=urlencode($arquivo)?>"
											target="_blank"><?=$docarq?></a></td>
										<td valign="top" bgcolor="#F7F7F7" class="textonormal">
											<?=$doctit?>
										</td>
										<td valign="top" bgcolor="#F7F7F7" class="textonormal">
											<?=$docdesc?>
										</td>
										<td valign="top" bgcolor="#F7F7F7" class="textonormal">
											<?=DataBarra($docdata).' '.Hora($docdata)?>
										</td>
									</tr>
								<?php

            }
        } else {
            ?>
								<tr>
										<td valign="top" bgcolor="#F7F7F7" class="textonormal"
											colspan="4" width="500">Nenhum documento encontrado.</td>
									</tr>
							<?php

        }
        ?>
						</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<!-- Fim do Corpo -->
		</table>
	</form>
</body>
</html>
