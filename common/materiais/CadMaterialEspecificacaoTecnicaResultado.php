<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadMaterialEspecificacaoTecnicaResultado.php
# Autor:    Carlos Abreu
# Data:     19/06/2007
# Objetivo: Programa para Apresentação de Arquivos com Especificação Técnica para Download
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data:     15/01/2019
# Objetivo: Tarefa Redmine 77809
# ------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     07/02/2019
# Objetivo: Tarefa Redmine 210602
# ------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

AddMenuAcesso( '/materiais/CadMaterialEspecificacaoTecnicaConsultar.php' );

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Critica          = $_POST['Critica'];
	$Botao            = $_POST['Botao'];
	$Arquivos         = $_POST['Arquivos'];
	$QuantArquivos    = $_POST['QuantArquivos'];
	$GrupoCodigo      = $_POST['GrupoCodigo'];
	$ClasseCodigo     = $_POST['ClasseCodigo'];
	$ArquivoDescricao = $_POST['ArquivoDescricao'];
    $ItemCodigo       = $_POST['ItemCodigo'];
    $TipoItem         = $_POST['TipoItem'];
} else {
	$GrupoCodigo  = $_GET['GrupoCodigo'];
	$ClasseCodigo = $_GET['ClasseCodigo'];
    $ItemCodigo   = $_GET['ItemCodigo'];
}

resetArquivoAcesso();

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadMaterialEspecificacaoTecnicaResultado.php";

if ($Botao == "Voltar") {
	header("location: CadMaterialEspecificacaoTecnicaConsultar.php");
	exit();
}

$db = Conexao();

$sql = "SELECT	EGRUMSDESC
		FROM	SFPC.TBGRUPOMATERIALSERVICO
		WHERE	CGRUMSCODI = $GrupoCodigo";

$result = $db->query($sql);

if (PEAR::isError($result)) {
	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
} else {
	$Linha = $result->fetchRow();
	$GrupoDescricao = $Linha[0];
}

$sql = "SELECT	ECLAMSDESC
		FROM	SFPC.TBCLASSEMATERIALSERVICO
		WHERE	CGRUMSCODI = $GrupoCodigo
				AND CCLAMSCODI = $ClasseCodigo";

$result = $db->query($sql);

if (PEAR::isError($result)) {
	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
} else {
	$Linha = $result->fetchRow();
	$ClasseDescricao = $Linha[0];
}

if (!empty($ItemCodigo)) {
    $sql_grupo = "SELECT FGRUMSTIPO FROM SFPC.TBGRUPOMATERIALSERVICO WHERE CGRUMSCODI = $GrupoCodigo";
	
	$result_grupo = $db->query($sql_grupo);
    $result_grupo = resultValorUnico($result_grupo);
	
	$tipoTexto = ($result_grupo) == 'M' ? 'Material' : 'Serviço';

    if ($result_grupo == 'M') {
        $sql = "SELECT MP.EMATEPDESC FROM SFPC.TBMATERIALPORTAL MP WHERE MP.CMATEPSEQU = $ItemCodigo ";
    } else {
        $sql = "SELECT ESERVPDESC FROM SFPC.TBSERVICOPORTAL WHERE CSERVPSEQU = $ItemCodigo";
    }

    $result = $db->query($sql);
	
	$item = resultValorUnico($result);
}
$db->disconnect();
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
	<!--
	function enviar(valor){
		document.MaterialPrecoEspecificacaoTecnica.Botao.value=valor;
		document.MaterialPrecoEspecificacaoTecnica.submit();
	}
	<?php MenuAcesso(); ?>
	//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form enctype="multipart/form-data" action="CadMaterialEspecificacaoTecnicaResultado.php" method="post" name="MaterialPrecoEspecificacaoTecnica">
		<br><br><br><br><br>
		<table cellpadding="3" border="0" summary="">
			<!-- Caminho -->
			<tr>
				<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="2">
					<font class="titulo2">|</font>
					<a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais > Especificação Técnica
				</td>
			</tr>
			<!-- Fim do Caminho-->
			<!-- Erro -->
			<?php if ( $Mens == 1 ) {?>
				<tr>
  					<td width="150"></td>
					<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
				</tr>
			<?php } ?>
			<!-- Fim do Erro -->
			<!-- Corpo -->
			<tr>
				<td width="150"></td>
				<td class="textonormal"><br>
					<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
        				<tr>
							<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	       						CONSULTAR - ESPECIFICAÇÃO TÉCNICA
							</td>
						</tr>
        				<tr>
							<td class="textonormal">
								<p align="justify">
									Para visualizar os documentos da Especificação Técnica, clique no link do documento desejado.
								</p>
							</td>
						</tr>
						<tr>
							<td>
								<table border="0" summary="">
									<tr>
										<td class="textonormal" bgcolor="#DCEDF7" height="20">Grupo </td>
										<td class="textonormal"><?php echo $GrupoDescricao; ?></td>
	            					</tr>
									<tr>
										<td class="textonormal" bgcolor="#DCEDF7" height="20">Classe </td>
										<td class="textonormal"><?php echo $ClasseDescricao; ?></td>
	            					</tr>
                					<?php if(!empty($ItemCodigo)) { ?>
                						<tr>
                    						<td class="textonormal" bgcolor="#DCEDF7" height="20"><?php echo $tipoTexto; ?> </td>
                    						<td class="textonormal"><?php echo $item; ?></td>
                						</tr>
                					<?php } ?>
									<tr>
										<td class="textonormal" bgcolor="#DCEDF7" colspan="2">
											<table border="1" width="100%" summary="" valign="top" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6">
												<?php
												$db = Conexao();
												
												$sql  = "SELECT CESPTMCODI, EESPTMOBSE, TO_CHAR(TESPTMULAT,'DD/MM/YYYY'), EESPTMNOME ";
												$sql .= "  FROM SFPC.TBESPECIFICACAOTECNICA ";
												$sql .= " WHERE CGRUMSCODI = $GrupoCodigo AND CCLAMSCODI = $ClasseCodigo ";
                    								if (!empty($ItemCodigo)) {
                        								if ($result_grupo == 'M') {
                            								$sql .= "AND CMATEPSEQU = $ItemCodigo";
                        								} else {
                            								$sql .= "AND CSERVPSEQU = $ItemCodigo";
                        								}
													}

												$result = $db->query($sql);
							
												if (PEAR::isError($result)) {
	  												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												} else {
													$Rows = $result->numRows();
													
													if ($Rows > 0) {
														echo "<tr><td align=\"center\" bgcolor=\"#75ADE6\" valign=\"middle\" class=\"titulo3\"><b>ESPECIFICAÇÕES TÉCNICAS</td><td align=\"center\" bgcolor=\"#75ADE6\" valign=\"middle\" class=\"titulo3\"><b>ARQUIVO</td><td align=\"center\" bgcolor=\"#75ADE6\" valign=\"middle\" class=\"titulo3\"><b>DATA</td></tr>\n";
														
														while ($Linha = $result->fetchRow()) {
															$cont++;
															$row  = $cont-1;

															echo "<tr>\n";
															$ArqUpload = "materiais/ESPECIFICACAO_".$GrupoCodigo."_".$ClasseCodigo."_".$Linha[0];
															$Arq = $GLOBALS["CAMINHO_UPLOADS"].$ArqUpload;
												
															if (file_exists($Arq)) {
																addArquivoAcesso($ArqUpload);
																echo "	<td class=\"textonormal\">";
																$Url = "CadMaterialEspecificacaoTecnicaDownloadDoc.php?GrupoCodigo=$GrupoCodigo&ClasseCodigo=$ClasseCodigo&DocCodigo=$Linha[0]";
													
																if (!in_array($Url,$_SESSION['GetUrl'])) {
																	$_SESSION['GetUrl'][] = $Url;
																}
														
																echo "<a href=\"$Url\" target=\"_blank\" class=\"textonormal\"><u>$Linha[1]</u></a> ";
																echo "</td>";
																echo "	<td class=\"textonormal\">";
																echo "<a href=\"$Url\" target=\"_blank\" class=\"textonormal\"><img src=\"../midia/disquete.gif\" border=\"0\" align=\"absmiddle\"> <u>$Linha[3]</u></a> ";
																echo "</td>";
															} else {
																echo "	<td class=\"textonormal\">";
																echo "$Linha[1]";
																echo "</td>";
																echo "	<td class=\"textonormal\">";
																echo "<img src=\"../midia/disquete.gif\" border=\"0\" align=\"absmiddle\"> $Linha[3] - <b>Arquivo não armazenado</b>";
																echo "</td>";
															}
															
															echo "  <td class=\"textonormal\" valign=\"middle\">$Linha[2]<br> </td> \n";
															echo "</tr>\n";
														}
													} else {
														echo "<tr>\n";
														echo "	<td class=\"textonormal\" height=\"20\">\n";
														echo "		Nenhuma Especificação Cadastrada!\n";
														echo "	</td>\n";
														echo "</tr>\n";
													}
												}
												$db->disconnect();
												?>
											</table>
										</td>
									</tr>
								</table>
								<input type="hidden" name="QuantArquivos" value="<?echo $Rows?>">
							</td>
						</tr>
						<tr>
							<td class="textonormal" align="right">
								<input type="hidden" name="GrupoCodigo" value="<?echo $GrupoCodigo?>">
								<input type="hidden" name="ClasseCodigo" value="<?echo $ClasseCodigo?>">
								<input type="hidden" name="Critica" value="1">
            					<input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
            					<input type="hidden" name="Botao" value="">
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