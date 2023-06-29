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
 * @package   Institucional
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version   GIT: EMPREL-SAD-PORTAL-COMPRAS-REL-COD-20160705-1051
 *
 *
 * HISTORICO DE ALTERAÇÔES
 * -----------------------------------------------------------------------
 *  Alterado: Pitang Agile TI
 *  Data:     04/07/2016
 *  Objetivo: Requisito 136739: Cartilhas, Guias e Manuais - Nova funcionalidade internet e intranet (#446)
 * -----------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     08/01/2023
 * Objetivo: Tarefa Redmine 277360
 * -----------------------------------------------------------------------
 */

// Acesso ao arquivo de funções #
include "../funcoes.php";

// Executa o controle de segurança #
session_start();
Seguranca();

// Aumenta o tempo de espera do servidor web para término de execução da página #
set_time_limit(3000);

$DocCodSelecionado = null;

// Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Botao = $_POST['Botao'];
    $ExcluirItem = $_POST['ExcluirItem'];
    $NoItens = $_POST['NoItens'];
} else {
    $Critica = $_GET['Critica'];
    $Mensagem = $_GET['Mensagem'];
    $Mens = $_GET['Mens'];
}

// Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;
$NomePrograma = "DeletarCartilhaGuiaManual.php";

$Mens = 0;
$Tipo = 0;
$Mensagem .= "";

if ($Botao == "Excluir") {
    if (count($ExcluirItem) == 0) {
        $Mens = 1;
        $Tipo = 2;
        $Mensagem = "Selecione pelo menos um arquivo para ser excluído";
        $Botao = "";
    }
} elseif ($Botao == "ConfirmarExcluir") {
    $db = Conexao();
    $db->query("BEGIN TRANSACTION");

    $sqlCodItens = " FDOCPOTIPO LIKE 'C' ";
    $mostraSeparador = false;

	for ($itr = 0; $itr < count($ExcluirItem); $itr ++) {
        $sqlCodItens .= " AND CDOCPOCODI = $ExcluirItem[$itr] ";
    }

    $sqlCodItensOr = "";
    $mostraSeparador = false;

	for ($itr = 0; $itr < count($ExcluirItem); $itr ++) {
        if (! $mostraSeparador) {
            $mostraSeparador = true;
        } else {
            $sqlCodItensOr .= " OR ";
        }

		$sqlCodItensOr .= " CDOCPOCODI = $ExcluirItem[$itr] ";
    }

    $sqlDeletarItens = " SELECT CDOCPOCODI, EDOCPOARQU, EDOCPOTITU, EDOCPODESC, CDOCPOUSAL,
								CDOCPOGRAL, TDOCPOULAT, EDOCPOARQS, FDOCPOTIPO
						FROM	SFPC.TBDOCUMENTACAOPORTAL
						WHERE	" . $sqlCodItens . " ";

	$resDelItens = $db->query($sqlDeletarItens);

	if (PEAR::isError($resDelItens)) {
        $db->query("ROLLBACK");
        $db->query("END");
        $db->disconnect();

        EmailErroSQL("Erro de SQL em " . $NomePrograma, __FILE__, __LINE__, "SQL falhou.", $sqlDeletarItens, $resDelItens);
        exit(0);
    }

    $sqlDelete = "DELETE FROM SFPC.TBDOCUMENTACAOPORTAL WHERE " . $sqlCodItensOr . " ";
    
	$resDelete = $db->query($sqlDelete);
    
	if (PEAR::isError($resDelete)) {
        $db->query("ROLLBACK");
        $db->query("END");
        $db->disconnect();
        EmailErroSQL("Erro de SQL em " . $NomePrograma, __FILE__, __LINE__, "SQL falhou.", $sqlDelete, $resDelete);
        exit(0);
    }

    $db->query("COMMIT");
    $db->query("END");
    $db->disconnect();

	while ($linha = $resDelItens->fetchRow()) {
        $nomeArqu = $GLOBALS["CAMINHO_UPLOADS"] . "institucional/" . $linha[7];
        unlink($nomeArqu);
    }

    $Mens = 1;
    $Tipo = 1;
    $Mensagem = "Documento(s) excluido(s) com sucesso";
    $Botao = "";
}

$db = Conexao();

$sql = "SELECT CDOCPOCODI, EDOCPOARQU, EDOCPOTITU, EDOCPODESC, CDOCPOUSAL, CDOCPOGRAL, TDOCPOULAT, EDOCPOARQS
		FROM SFPC.TBDOCUMENTACAOPORTAL
    	WHERE FDOCPOTIPO LIKE 'C' ";

if (($Botao == "Excluir") and (count($ExcluirItem) > 0)) { // selecionar arquivo a ser excluído
	$sql .= " AND ";

	for ($itr = 1; $itr <= $NoItens; $itr ++) {
        if (! is_null($ExcluirItem[$itr])) {
            if (! $mostraSeparador) {
                $mostraSeparador = true;
            } else {
                $sql .= " OR ";
            }
            $sql .= " CDOCPOCODI = " . $ExcluirItem[$itr] . " ";
        }
    }
}

$sql .= " ORDER BY EDOCPOARQU ";

$resDocs = $db->query($sql);

if (PEAR::isError($resDocs)) {
    EmailErroSQL("Erro de SQL em " . $NomePrograma, __FILE__, __LINE__, "SQL falhou.", $sql, $resDocs);
    exit(0);
}

if ($resDocs->numRows() == 0 and $Botao != "") {
    $Botao = "";
}
?>
<html>
<?php
// Carrega o layout padrão #
layout();
?>
<script type="text/javascript">
<!--
function enviar(valor) {
	document.Rot.Botao.value=valor;
	document.Rot.submit();
}

function janela(pageToLoad, winName, width, height, center) {
	xposition=0;
	yposition=0;

	if ((parseInt(navigator.appVersion) >= 4 ) && (center)) {
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
	<script type="text/javascript" src="../menu.js"></script>
	<script type="text/javascript">Init();</script>
	<form action="DeletarCartilhaGuiaManual.php" method="POST" name="Rot">
		<br> <br> <br> <br>
		<table cellpadding="3" border="0" summary="">
			<!-- Caminho -->
			<tr>
				<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="2"><br> <font
					class="titulo2">|</font> <a href="../index.php"><font
						color="#000000">Página Principal</font></a> > Institucional >
					Cartilhas, Guias e Manuais > Consultar Documentação</td>
			</tr>
			<!-- Fim do Caminho-->
			<!-- Erro -->
			<?php
			if ($Mens == 1) {
				?>
				<tr>
					<td width="100"></td>
					<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
				</tr>
				<?php
			}
			?>
			<!-- Fim do Erro -->
			<!-- Corpo -->
			<tr>
				<td width="100"></td>
				<td class="textonormal">
					<table border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
						<tr>
							<td class="textonormal">
								<?php
    							if ($Botao == "") {
        							?>
									<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
										<tr>
											<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
												LISTA DE DOCUMENTOS
												<input type="hidden" name="NoItens" value="<?=$resDocs->numRows()?>">
											</td>
										</tr>
										<?php
        								if ($resDocs->numRows() > 0) {
            								?>
											<tr>
												<td class="textonormal" colspan="4">
													<p align="justify">Escolha o arquivo desejado para exclusão clicando no botão 'Excluir' ao lado do arquivo.</p>
												</td>
											</tr>
											<tr>
												<td align="center" class="titulo3" bgcolor="#F7F7F7">&nbsp;</td>
												<td align="center" class="titulo3" bgcolor="#F7F7F7">ARQUIVO</td>
												<td align="center" class="titulo3" bgcolor="#F7F7F7">TÍTULO</td>
												<td align="center" class="titulo3" bgcolor="#F7F7F7">DATA</td>
											</tr>
											<?php
            								$linha = 0;
            								$itr = 0;

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
                								?>
												<tr>
													<td valign="top" bgcolor="#F7F7F7" class="textonormal">
														<input type="checkbox" name="ExcluirItem[<?=$itr?>]" value="<?=$doccod?>" class="botao">
													</td>
													<td valign="top" bgcolor="#F7F7F7" class="textonormal">
														<?=$docarq?>
													</td>
													<td valign="top" bgcolor="#F7F7F7" class="textonormal">
														<?=$doctit?>
													</td>
													<td valign="top" bgcolor="#F7F7F7" class="textonormal">
														<?=$docdata?>
													</td>
												</tr>
												<?php
            								}
            								?>
											<tr>
												<td valign="top" align="right" bgcolor="#F7F7F7" class="textonormal" colspan="4"><input type="button" name="Excluir" value="Excluir" class="botao" onclick="javascript:enviar('Excluir');"></td>
											</tr>
											<?php
        								} else {
            								?>
											<td valign="top" bgcolor="#F7F7F7" class="textonormal" colspan="4" width="500">Nenhum documento encontrado.</td>
											<?php
        								}
        								?>
									</table>
									<?php
        							// Confirmação de exclusão
    							} elseif ($Botao == "Excluir") {
            						?>
									<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
										<tr>
											<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">CONFIRMAR EXCLUSÃO</td>
										</tr>
										<tr>
											<td class="textonormal" colspan="4">
												<p align="justify">Confirme o(s) arquivo(s) a ser(em) excluído(s)</p>
											</td>
										</tr>
										<tr>
											<td align="center" class="titulo3" bgcolor="#F7F7F7">ARQUIVO</td>
											<td align="center" class="titulo3" bgcolor="#F7F7F7">TÍTULO</td>
											<td align="center" class="titulo3" bgcolor="#F7F7F7">DESCRIÇÃO</td>
											<td align="center" class="titulo3" bgcolor="#F7F7F7">DATA</td>
										</tr>
										<?php
            							while ($linha = $resDocs->fetchRow()) {
                							$doccod = $linha[0];
                							$docarq = $linha[1];
                							$doctit = $linha[2];
                							$docdesc = $linha[3];
                							$docusr = $linha[4];
                							$docgrp = $linha[5];
                							$docdata = $linha[6];
                							?>
											<tr>
												<td valign="top" bgcolor="#F7F7F7" class="textonormal">
													<?=$docarq?>
												</td>
												<td valign="top" bgcolor="#F7F7F7" class="textonormal">
													<?=$doctit?>
												</td>
												<td valign="top" bgcolor="#F7F7F7" class="textonormal">
													<?=$docdesc?>
												</td>
												<td valign="top" bgcolor="#F7F7F7" class="textonormal">
													<?=$docdata?>
												</td>
											</tr>
											<?php
            							}
            							?>
										<tr>
											<td class="textonormal" align="right" colspan="4">
												<input type="button" value="Confirmar" class="botao" onclick="javascript:enviar('ConfirmarExcluir');">
												<input type="button" value="Cancelar" class="botao" onclick="javascript:enviar('');">
												<input type="hidden" name="NoItens" value="<?=$NoItens?>">
												<?php
            									// guardar codigos dos documentos a serem excluidos
            									$cnt = 0;

            									for ($itr = 1; $itr <= $NoItens; $itr ++) {
                									if (! is_null($ExcluirItem[$itr])) {
                    									?>
														<input type="hidden" name="ExcluirItem[<?=$cnt?>]" value="<?=$ExcluirItem[$itr]?>">
														<?php
                    									$cnt ++;
                									}
            									}
            									?>
											</td>
										</tr>
									</table>
									<?php
        						}
    							?>
							</td>
						</tr>
						<tr>
							<td><input type="hidden" name="Botao" value="" /></td>
						</tr>
					</table>
				</td>
			</tr>
			<!-- Fim do Corpo -->
		</table>
	</form>
</body>
</html>
