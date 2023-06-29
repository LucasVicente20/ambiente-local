<?php
#----------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadMaterialPrecoConsultar.php
# Objetivo: Programa de Consulta de Preços de Material
# Autor:    Carlos Abreu
# Data:     15/06/2007
# Autor:    Rossana Lira
# Data:     03/07/2007 - Troca de chamada do programa CadMaterialPrecoHistorico
#                        por CadMaterialPrecoConsultarDetalhe
# OBS.:     Tabulação 2 espaços
#----------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança	   #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/materiais/CadMaterialPrecoConsultarDetalhe.php' );

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadMaterialPrecoConsultar.php" method="post" name="CadMaterialPrecoConsultar">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais > Preço > Consultar
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
		<td width="100"></td>
		<td align="left" colspan="2">
			<?php if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,1); } ?>
		</td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
			<table border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
				<tr>
					<td class="textonormal">
						<table border="0" cellspacing="0" cellpadding="0" summary="">
							<tr>
								<td class="textonormal">
									<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#bfdaf2">
										<tr>
											<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="5">
												TABELA REFERENCIAL DE PREÇOS DE MATERIAIS - TRP-REC
											</td>
										</tr>
				  	      	<tr>
				    	      	<td class="textonormal" colspan="5">
				          	   	<p align="justify">
													Os Preços Estimados acima referem-se  a coleta de preços realizada em Processos
													Licitatórios promovidos pela Administração Municipal e em Atas de Registros de Preços
													de outros Órgãos Públicos. Quaisquer dúvidas sobre os preços estimados, deverá ser
													consultada a Gerência de Relações Comerciais, através do telefone 3232-8229.
				          	   	</p>
											</td>
										</tr>
										<tr>
										<?
										$db   = Conexao();
										$sql  = "SELECT GRUM.FGRUMSTIPM, MAT.EMATEPDESC, PREC.CMATEPSEQU, UNID.EUNIDMSIGL, PREC.VPRECMPREC, PREC.DPRECMCADA ";
										$sql .= "  FROM SFPC.TBPRECOMATERIAL PREC ";
										$sql .= " INNER JOIN SFPC.TBMATERIALPORTAL MAT ";
										$sql .= "    ON PREC.CMATEPSEQU = MAT.CMATEPSEQU ";
										$sql .= " INNER JOIN SFPC.TBUNIDADEDEMEDIDA UNID ";
										$sql .= "    ON MAT.CUNIDMCODI = UNID.CUNIDMCODI ";
										$sql .= " INNER JOIN SFPC.TBSUBCLASSEMATERIAL SUBM ";
										$sql .= "    ON MAT.CSUBCLSEQU = SUBM.CSUBCLSEQU ";
										$sql .= " INNER JOIN SFPC.TBGRUPOMATERIALSERVICO GRUM ";
										$sql .= "    ON SUBM.CGRUMSCODI = GRUM.CGRUMSCODI ";
										$sql .= " WHERE PREC.DPRECMCADA = (SELECT MAX(DPRECMCADA) FROM SFPC.TBPRECOMATERIAL WHERE CMATEPSEQU = PREC.CMATEPSEQU) ";
										$sql .= " ORDER BY GRUM.FGRUMSTIPM, MAT.EMATEPDESC ";
										$result = $db->query($sql);
										if (PEAR::isError($result)) {
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												if ($result->numRows()>0){
														$TotalItens = 0;
														$TotalGeral = 0;
														while ($Linha = $result->fetchRow()){
															if ($TipoMaterial != $Linha[0]){
																$TipoMaterial = $Linha[0];
																if ($TotalItens>0){
																	echo "<tr><td align=\"right\" bgcolor=\"#75ADE6\" valign=\"middle\" class=\"titulo3\">TOTAL DE ITENS</td><td align=\"center\" bgcolor=\"#75ADE6\" valign=\"middle\" class=\"titulo3\" colspan=\"4\">$TotalItens</td></tr>";
																	$TotalItens = 0;
																}
																switch ($TipoMaterial){
																	case 'C':
																		echo "<tr><td align=\"center\" bgcolor=\"#75ADE6\" valign=\"middle\" class=\"titulo3\" colspan=\"5\">MATERIAL DE CONSUMO</td></tr>";
																		echo "<tr><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" >DESCRIÇÃO</td><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" >COD.RED.</td><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" >UNID.</td><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" >VALOR REFERENCIAL(R$)</td><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" >DATA</td></tr>\n";
																		break;
																	case 'P':
																		echo "<tr><td align=\"center\" bgcolor=\"#75ADE6\" valign=\"middle\" class=\"titulo3\" colspan=\"5\">MATERIAL DE PERMANENTE</td></tr>";
																		echo "<tr><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" >DESCRIÇÃO</td><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" >COD.RED.</td><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" >UNID.</td><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" >VALOR REFERENCIAL(R$)</td><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" >DATA</td></tr>\n";
																		break;
																}
															}
															$Valor = str_replace(".",",",$Linha[4]);
															$Data = explode("-",$Linha[5]);
															$Data = $Data[2]."/".$Data[1]."/".$Data[0];
															$Url = "CadMaterialPrecoConsultarDetalhe.php?Material=".$Linha[2];
															if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
															echo "<tr><td><a href=\"$Url\" class=\"textonormal\"><u>".$Linha[1]."</u></a></td><td align=\"right\">".$Linha[2]."</td><td align=\"right\">".$Linha[3]."</td><td align=\"right\">".$Valor."</td><td align=\"right\">".$Data."</td></tr>\n";
															$TotalItens++;
															$TotalGeral++;
														}
														if ($TotalItens>0){
															echo "<tr><td align=\"right\" bgcolor=\"#75ADE6\" valign=\"middle\" class=\"titulo3\">TOTAL DE ITENS</td><td align=\"center\" bgcolor=\"#75ADE6\" valign=\"middle\" class=\"titulo3\" colspan=\"4\">$TotalItens</td></tr>";
															echo "<tr><td align=\"right\" bgcolor=\"#75ADE6\" valign=\"middle\" class=\"titulo3\">TOTAL GERAL</td><td align=\"center\" bgcolor=\"#75ADE6\" valign=\"middle\" class=\"titulo3\" colspan=\"4\">$TotalGeral</td></tr>";
															$TotalItens = 0;
														}
												} else {
													echo "<tr><td colspan=\"5\">Nenhum Preço Armazenado</td></tr>";
												}
										}
										$db->disconnect();
										?>
										</tr>
									</table>
								</td>
							</tr>
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
