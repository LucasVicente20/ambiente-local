<?php
# ----------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelTRPConsultar.php
# Objetivo: Programa de Detalhamento da Consulta de TRP (janela popup)
# Autor:    Ariston Cordeiro
# Data:     19/11/2012
# OBS.:     Tabulação 2 espaços
# ----------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 73662
# ----------------------------------------------------------------------------

require_once("../licitacoes/funcoesComplementaresLicitacao.php");

# Acesso ao arquivo de funções #
require_once "../compras/funcoesCompras.php";

# Executa o controle de segurança  #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/materiais/CadMaterialTRPConsultar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
	/*	$Botao            	= $_POST['Botao'];
		//$Grupo            	= $_POST['Grupo'];
		//$Classe           	= $_POST['Classe'];
		//$Subclasse        	= $_POST['Subclasse'];
		$Material         	= $_POST['Material'];
		$CodCADUM			= $_POST['CodCADUM'];
		$DescSimples	  	= $_POST['DescSimples'];
		$UndMedida     		= $_POST['UndMedida'];
		$DescCompleta    	= $_POST['DescCompleta'];
		$MediaTRP			= $_POST['MediaTRP'];*/

}else{
		//$Grupo            = $_GET['Grupo'];
		//$Classe           = $_GET['Classe'];
		//$Subclasse        = $_GET['Subclasse'];
		$Material = $_GET['Material'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

$db   = Conexao();
$dataMinimaValidaTrp = prazoValidadeTrp($db, TIPO_COMPRA_LICITACAO)->format('Y-m-d');
$dataMinimaValidaPesquisaMercado = prazoValidadePesquisaMercado();


if( $Botao == "" ){
		# Pega os dados do Material de acordo com o código #

		$meses = resultValorUnico(executarSQL($db, "select QPARGEVLI from SFPC.TBPARAMETROSGERAIS"));
		
		$sql = "SELECT	DISTINCT 
							MAT.EMATEPDESC, MAT.EMATEPCOMP, UND.EUNIDMDESC, REFP.CMATEPSEQU
				FROM	
							SFPC.TBUNIDADEDEMEDIDA UND, SFPC.TBMATERIALPORTAL MAT, SFPC.TBTABELAREFERENCIALPRECOS REFP
				WHERE 	
							REFP.CMATEPSEQU = MAT.CMATEPSEQU	
							AND MAT.CUNIDMCODI = UND.CUNIDMCODI 
							AND REFP.CMATEPSEQU = $Material";
		
		$res  = $db->query($sql);

		if( PEAR::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha         		= $res->fetchRow();
				
				$DescSimples	  	= $Linha[0];
				$DescCompleta  		= $Linha[1];
				$UndMedida   		= $Linha[2];
				$CodCADUM			= $Linha[3];
				
				$MediaTRP = calculaValorTrp($Material);
				$MediaTRP = converte_valor_estoques($MediaTRP);
				$obsTRP = "Para esta média só são considerados preços válidos dos últimos ".$meses." dias, e pesquisas de mercado dos últimos 12 meses";
		}
		
}
?>
<html>
<head>
<title>Portal de Compras - Incluir Centro de Custo</title>
<link rel="stylesheet" type="text/css" href="../estilo.css">
</head>
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<!--  script language="JavaScript">Init();</script-->
<form action="CadMaterialTRPConsultarDetalhe.php" method="post" name="CadMaterialTRPConsultarDetalhe">
<table cellpadding="3" border="0" summary="">
	<!-- Corpo -->
	<tr>
		<td class="textonormal">
			<table border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
				<tr>
					<td class="textonormal">
						<table border="0" cellspacing="0" cellpadding="0" summary="">
							<tr>
								<td class="textonormal">
									<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
										<tr>
											<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="9">
												TABELA REFERENCIAL DE PREÇOS DE MATERIAIS - LICITAÇÃO
											</td>
										</tr>
										<tr>
											<td colspan="9">
												<table border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" width="100%" summary="">
													<tr>
														<td colspan="2">
															<table class="textonormal" border="0" width="100%" summary="">
																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" height="20" align="left">Código CADUM</td>
																	<td class="textonormal"><?php echo $CodCADUM; ?></td>
																</tr>
																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" height="20" align="left">Descrição do Material</td>
																	<td class="textonormal"><?php echo $DescSimples;?></td>
																</tr>
																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" height="20" align="left">Unidade de Medida</td>
																	<td class="textonormal"><?php echo $UndMedida;?></td>
																</tr>
																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" height="20" align="left">Descrição Completa</td>
																	<td class="textonormal"><?php echo $DescCompleta;?></td>
																</tr>
																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" height="20" align="left" title="<?=$obsTRP?>">Média TRP (<a href="#">*</a>)</td>
																	<td class="textonormal" title="<?=$obsTRP?>"><?php echo $MediaTRP;?></td>
																</tr>
															</table>
														</td>
													</tr>
												</table>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#75ADE6" align="center" >DATA REFERÊNCIA</td>
											<td class="textonormal" bgcolor="#75ADE6" align="center" >LICITAÇÃO/PESQUISA DE MERCADO</td>
											<td class="textonormal" bgcolor="#75ADE6" align="center" >ÓRGÃO</td>
											<td class="textonormal" bgcolor="#75ADE6" align="center" >LOTE</td>
											<td class="textonormal" bgcolor="#75ADE6" align="center" >ORDEM</td>
											<td class="textonormal" bgcolor="#75ADE6" align="center" >FORNECEDOR</td>
											<td class="textonormal" bgcolor="#75ADE6" align="center" >MARCA</td>
											<td class="textonormal" bgcolor="#75ADE6" align="center" >MODELO</td>
											<td class="textonormal" bgcolor="#75ADE6" align="center" >PREÇO UNITÁRIO</td>
										</tr>
										<tr>
										<?
										
										#pega registros TRP e dados do material
										$sql  = "SELECT 
														REFP.VTRPREVALO, REFP.CPESQMSEQU, REFP.CLICPOPROC, REFP.ALICPOANOP,  
														REFP.CGREMPCODI, REFP.CCOMLICODI, REFP.CORGLICODI, REFP.CITELPSEQU, REFP.CTRPREULAT
												 FROM 
												 		SFPC.TBTABELAREFERENCIALPRECOS REFP
														JOIN SFPC.TBMATERIALPORTAL MAT ON REFP.CMATEPSEQU = MAT.CMATEPSEQU
														JOIN SFPC.TBSUBCLASSEMATERIAL  SUBM ON MAT.CSUBCLSEQU = SUBM.CSUBCLSEQU
														JOIN SFPC.TBUNIDADEDEMEDIDA UNID ON MAT.CUNIDMCODI = UNID.CUNIDMCODI
														JOIN SFPC.TBGRUPOMATERIALSERVICO GRUM ON SUBM.CGRUMSCODI = GRUM.CGRUMSCODI
												 WHERE 	
												 		REFP.CMATEPSEQU = $Material 
														AND(REFP.CLICPOPROC IS NOT NULL 
															OR REFP.CPESQMSEQU IS NOT NULL)
														AND REFP.CTRPREULAT >= '".$dataMinimaValidaTrp."'
														--AND(REFP.FTRPREVALI = 'A' OR REFP.FTRPREVALI IS NULL)";
										//echo $sql;
										

										$result = $db->query($sql);
										
										if (PEAR::isError($result)) {
											ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
											
											$i = 0;
											$itens;
											
											while ($Linha = $result->fetchRow()){

												if($Linha[2] != NULL){ //verificar se é uma licitação (CLICPOPROC <> NULL)
													#pegando dados da licitacao e fornecedor
													$sql = "SELECT 	ILP.EITELPMARC, ILP.EITELPMODE, FORN.AFORCRCCGC, FORN.NFORCRRAZS, 
																	ORGL.EORGLIDESC, ILP.CITELPNUML, ILP.AITELPORDE
															FROM 	SFPC.TBITEMLICITACAOPORTAL ILP, SFPC.TBFORNECEDORCREDENCIADO FORN, 
																	SFPC.TBORGAOLICITANTE ORGL 
															WHERE	ILP.CLICPOPROC = $Linha[2]
																	AND ILP.ALICPOANOP = $Linha[3]
																	AND ILP.CGREMPCODI = $Linha[4]
																	AND ILP.CCOMLICODI = $Linha[5]
																	AND ILP.CORGLICODI = $Linha[6]
																    AND ILP.CITELPSEQU = $Linha[7]
																	AND ILP.AFORCRSEQU  = FORN.AFORCRSEQU
																	AND ORGL.CORGLICODI =  ILP.CORGLICODI
															ORDER BY
																	ILP.CITELPNUML, ILP.AITELPORDE, FORN.AFORCRCCGC, FORN.NFORCRRAZS, ILP.EITELPMARC, ILP.EITELPMODE";
													
													$retorno = $db->query($sql);
													$retorno = $retorno->fetchRow();
													
													$Data = explode("-",$Linha[8]);
													$Data1 = explode(" ",$Data[2]);
													$Data[2] = $Data1[0];
													
													$DataC = "$Data[0]"."$Data[1]"."$Data[2]";
													$itens[$i]["DataC"] = $DataC;
													
													$Data = $Data[2]."/".$Data[1]."/".$Data[0];
													
													
													$comissao 	= resultValorUnico(executarSQL($db,"select ECOMLIDESC from SFPC.TBCOMISSAOLICITACAO where CCOMLICODI = $Linha[5]"));
													$orgao		= resultValorUnico(executarSQL($db,"select EORGLIDESC from SFPC.TBORGAOLICITANTE where CORGLICODI = $Linha[6]"));
													
													$itens[$i]["Data"] 		= $Data;																// data referência
													$itens[$i]["L/P"] 		= "PL ".$Linha[2]."/".$Linha[3]." - ".$comissao." - ".$orgao;			// licitação
													$itens[$i]["Orgao"] 	= $orgao;																// órgão
													$itens[$i]["Forn"] 		= FormataCNPJ($retorno[2])." ".$retorno[3];								// fornecedor
													$itens[$i]["Marca"] 	= $retorno[0];															// marca
													$itens[$i]["Modelo"] 	= $retorno[1];															// modelo
													$itens[$i]["PU"] 		= converte_valor_estoques($Linha[0]);									// preço unitário
													$itens[$i]["Lote"] 		= $retorno[5];															// ORDEM
													$itens[$i]["Ordem"] 	= $retorno[6];															// LOTE
													
													
												} else{ //verificar se é uma pesquisa de preço de mercado (CPESQMSEQU <> NULL)
													$sql = "SELECT  PESQM.DPESQMREFE, PESQM.EPESQMOBSE, PESQM.CPESQMCNPJ, 
																	PESQM.NPESQMRAZS, PESQM.CPESQMMARC, PESQM.CPESQMMODE,
																	FORN.AFORCRCCGC, FORN.NFORCRRAZS
															FROM 	SFPC.TBPESQUISAPRECOMERCADO PESQM
																	LEFT JOIN SFPC.TBFORNECEDORCREDENCIADO FORN ON FORN.AFORCRSEQU = PESQM.AFORCRSEQU 
															WHERE 	PESQM.CPESQMSEQU = $Linha[1]
																	AND PESQM.CMATEPSEQU = $Material
															ORDER BY
																	PESQM.CPESQMCNPJ, PESQM.NPESQMRAZS, PESQM.CPESQMMARC, PESQM.CPESQMMODE";
													
													$retorno1 = $db->query($sql);
													$retorno1 = $retorno1->fetchRow();
													
													$Data = explode("-",$retorno1[0]);
													
													$DataC = "$Data[0]"."$Data[1]"."$Data[2]";
													
													//$Data = $Data[2]."/".$Data[1]."/".$Data[0];
													
													//$itens[$i]["Data"] 		= $Data;											// data referência*/
													$Data = new DataHora($retorno1[0]);
													if($Data>=$dataMinimaValidaPesquisaMercado){
														
														$itens[$i]["DataC"] = $DataC;
														$itens[$i]["Data"] 		= $Data->formata('d/m/Y');

														$itens[$i]["L/P"] 		= $retorno1[1];										// pesquisa de preço de mercado
														$itens[$i]["Orgao"] 	= "-";												// órgão
														
														if($retorno1[2] != NULL){
															$itens[$i]["Forn"] 		= FormataCNPJ($retorno1[2])." ".$retorno1[3];	// fornecedor
														}
														else{
															$itens[$i]["Forn"] 		= FormataCNPJ($retorno1[6])." ".$retorno1[7];	// fornecedor
														}
														
														$itens[$i]["Marca"] 	= $retorno1[4];										// marca
														$itens[$i]["Modelo"]	= $retorno1[5];										// modelo
														$itens[$i]["PU"] 		= converte_valor_estoques($Linha[0]);				// preço unitário
														$itens[$i]["Lote"] 		= "";												// ORDEM
														$itens[$i]["Ordem"] 	= "";												// LOTE
													}
												}
												
												$i++;
														
											}
											
											
											//Ordenação do array a ser exibido na tela
											foreach($itens as $c=>$key) {
												$sort_data[] = $key['DataC'];
												
											}
											
											array_multisort($sort_data, SORT_DESC, $itens);
											
											$controle = 0;
											//var_dump($itens);
											//exit;
											while($controle < $i){
												
												
												echo "<tr><td align=\"center\">&nbsp;".$itens[$controle]["Data"]."</td>";
												echo "<td align=\"center\">&nbsp;".$itens[$controle]["L/P"]."</td>";
												echo "<td align=\"center\">&nbsp;".$itens[$controle]["Orgao"]."</td>";
												echo "<td align=\"center\">&nbsp;".$itens[$controle]["Lote"]."</td>";
												echo "<td align=\"center\">&nbsp;".$itens[$controle]["Ordem"]."</td>";
												echo "<td align=\"center\">&nbsp;".$itens[$controle]["Forn"]."</td>";
												echo "<td align=\"center\">&nbsp;".$itens[$controle]["Marca"]."</td>";
												echo "<td align=\"center\">&nbsp;".$itens[$controle]["Modelo"]."</td>";
												echo "<td align=\"right\">&nbsp;".$itens[$controle]["PU"]."</td></tr>\n";
												
												$controle++;
											}
										}
										
										?>
										</tr>
										<tr>
											<td colspan="9" align="right">
												<input type="submit" value="Voltar" class="botao" onclick="javascript:self.close();">
												<input type="hidden" name="Botao" value="">
											</td>
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
<?
$db->disconnect();
?>