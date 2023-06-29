<?php
# -----------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadIncluirCentroCusto.php
# Objetivo: Programa de Inclusão de Centro de Custo
# Autor:    Roberta Costa
# Data:     09/09/2005
# OBS.:     Tabulação 2 espaços
# -----------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     18/07/2006 - Mudança da busca (com translate) e eliminação do combo
# -----------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     04/08/2006 - Não exibição do detalhamento "Extra Atividade (77)"
# -----------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     18/04/2007 - Não exibição do centro de custo "Almoxarifado"
# -----------------------------------------------------------------------------
# Alterado: Rossana Lira
# Data:     12/03/2008 - Colocação do ano corrente no filtro de seleção dos centros de custos
# -----------------------------------------------------------------------------
# Alterado: João Batista Brito
# Data:     10/07/2012 - Erro corrigido redmine: 12835
# -----------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 73662
# -----------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/CadItemDetalhe.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
		$ProgramaOrigem  = $_POST['ProgramaOrigem'];
		$Botao           = $_POST['Botao'];
		$Descricao       = strtoupper2(trim($_POST['Descricao']));
		$CentroCustoSel  = $_POST['CentroCustoSel'];
		$CentroCusto     = $_POST['CentroCusto'];
		$Todos           = $_POST['Todos'];
		$TipoUsuario     = $_POST['TipoUsuario'];
}else{
		$ProgramaOrigem	= $_GET['ProgramaOrigem'];
		$Requisitante		= $_GET['TipoUsuario'];
}
# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

$permiteCorporativo=true;
$modulo = 'E'; //Estoques
if($ProgramaOrigem=='CadSolicitacaoCompraIncluirManterExcluir'){
	//$permiteCorporativo=false;
	$modulo = 'C'; //Compras
}

if( $Botao == "Incluir" ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( $CentroCustoSel == "" ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadIncluirCentroCusto.CentroCustoSel.focus();\" class=\"titulo2\">Centro de Custo</a>";
		}
		if( $Mens == 0 ){
				echo "<script>opener.document.$ProgramaOrigem.CentroCusto.value=$CentroCustoSel</script>";
				echo "<script>opener.document.$ProgramaOrigem.submit()</script>";
				echo "<script>self.close()</script>";
		}
}elseif( $Botao == "IncluirLink" ){
		echo "<script>opener.document.$ProgramaOrigem.CentroCusto.value=$CentroCusto</script>";
		echo "<script>opener.document.$ProgramaOrigem.submit()</script>";
		echo "<script>self.close()</script>";
}elseif( $Botao == "Pesquisar" ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( $Descricao == "" and $Todos == "" and $Orgao == "" ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadIncluirCentroCusto.Descricao.focus();\" class=\"titulo2\">Descrição Centro de Custo</a>";
		}
}

# Pega a descrição do Perfil do usuário logado #
if( $_SESSION['_cperficodi_'] != 2 and $_SESSION['_cperficodi_'] != 0 ){
		$db  = Conexao();
		$sqlusuario = "SELECT CPERFICODI, EPERFIDESC FROM SFPC.TBPERFIL ";
		$sqlusuario .= "WHERE CPERFICODI = ".$_SESSION['_cperficodi_']." ";
		$resultUsuario = $db->query($sqlusuario);
		if( db::isError($result) ){
				ExibeErroBD("$ErroPrograma\nLinha: 239\nSql: $sqlusuario");
		}else{
				$PerfilUsuario = $resultUsuario->fetchRow();
				$PerfilUsuarioDesc = $PerfilUsuario[1];
		}
}

?>
<html>
<head>
<title>Portal de Compras - Incluir Centro de Custo</title>
<script language="javascript" type="">
function enviar(valor,seq,detalhe){
	document.CadIncluirCentroCusto.CentroCusto.value = seq;
	document.CadIncluirCentroCusto.Botao.value = valor;
	document.CadIncluirCentroCusto.submit();
}
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
</head>
<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
<form action="CadIncluirCentroCusto.php" method="post" name="CadIncluirCentroCusto">
	<table cellpadding="0" border="0" summary="">
		<!-- Erro -->
		<tr>
			<td align="left" colspan="4">
				<?php if( $Mens != 0 ){ ExibeMens($Mensagem,$Tipo,1);	}?>
			</td>
		</tr>
		<!-- Fim do Erro -->

		<!-- Corpo -->
		<tr>
			<td class="textonormal">
				<table border="0" cellspacing="0" cellpadding="3" summary="">
					<tr>
						<td class="textonormal">
							<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
								<tr>
									<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
										INCLUIR - SELEÇÃO DE CENTRO DE CUSTO
									</td>
								</tr>
								<tr>
									<td class="textonormal" colspan="4">
										<p align="justify">
											Para selecionar um Centro de Custo selecione o item da lista e e clique no botão "Incluir".
											Para pesquisar um Centro de Custo preencha o argumento da pesquisa e clique na figura da lupa e
											depois, clique no Centro de Custo desejado que está marcado com um "*".<br>
											Para voltar a tela anterior clique no botão "Voltar".
										</p>
									</td>
								</tr>
								<tr>
									<td colspan="4">
										<table border="0" cellpadding="0" cellspacing="2" width="100%" summary="">
										<?
										if( (($Requisitante == 'R' or $TipoUsuario == 'R') and $modulo == 'E') or $modulo == 'C' ){
												echo"
												<tr>
													<td class='textonormal' bgcolor='#DCEDF7' width='31%'>Descrição Centro de Custo</td>
													<td class='textonormal' colspan='2'>
														<input type='text' name='Descricao' size='30' maxlength='30' class='textonormal' value='$Descricao'>
														<a href=\"javascript:enviar('Pesquisar');\"><img src='../midia/lupa.gif' border='0'></a>
														<input type='checkbox' disabled name='Todos' value='1' onClick=\"javascript:enviar('Pesquisar');\"> Todos
													</td>
												</tr>";
										}else{
												echo"
												<tr>
													<td class='textonormal' bgcolor='#DCEDF7' width='31%'>Descrição Centro de Custo</td>
													<td class='textonormal' colspan='2'>
														<input type='text' name='Descricao' size='30' maxlength='30' class='textonormal' value='$Descricao'>
														<a href=\"javascript:enviar('Pesquisar');\"><img src='../midia/lupa.gif' border='0'></a>
														<input type='checkbox' name='Todos' value='1' onClick=\"javascript:enviar('Pesquisar');\"> Todos
													</td>
												</tr>";
										}
										?>
										</table>
									</td>
								</tr>
								<tr>
									<td colspan="4" align="right">
										<input type="hidden" name="ProgramaOrigem" value="<?php echo $ProgramaOrigem; ?>">
										<input type="hidden" name="CentroCusto" value="<?php echo $CentroCusto; ?>">
										<input type="button" value="Incluir" class="botao" onclick="javascript:enviar('Incluir');">
										<input type="button" value="Voltar" class="botao" onclick="javascript:self.close();">
										<input type="hidden" name="Botao" value="">
									</td>
								</tr>
								<?php
								if( $Botao == "Pesquisar" and $Mens == 0 or !$Botao){
										# Seleciona os Centros de Custo de acordo com a palavra Pesquisada #
										// //($_GET);die;
										$db = Conexao();
										if( (($_SESSION['_cgrempcodi_'] == 0 ) or ($_SESSION['_fperficorp_'] == 'S')) and $permiteCorporativo ) {
												$sql  = "SELECT A.CCENPOSEQU, A.CCENPOCORG, A.CCENPOUNID, A.ECENPODESC, ";
												$sql .= "       A.CCENPONRPA, A.ECENPODETA, B.EORGLIDESC, B.CORGLICODI ";
												$sql .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B ";
												$sql .= " WHERE A.CORGLICODI IS NOT NULL AND A.ACENPOANOE = ".date("Y")."";
												$sql .= "   AND A.CORGLICODI = B.CORGLICODI ";
												$sql .= "   AND (A.CCENPODETA,A.CCENPOCENT) <> (77,799)";
												if(!$Todos){
														$sql .= " AND ( ";
														//$sql .= " A.ECENPODESC ILIKE '%$Descricao%' ";
														//$sql .= "      TRANSLATE(A.ECENPODESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($Descricao))."%' OR ";
														$sql .= "      TRANSLATE(A.ECENPODESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '%".strtoupper2(RetiraAcentos($Descricao))."%'";
														$sql .= "     )";
												}
												$sql .= "   AND A.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos

												$sql .="    AND B.FORGLISITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
												
												if(!empty($_GET['orgaobase'])){
													$orgao_base = $_GET['orgaobase'];
													$sql .= "  AND B.CORGLICODI = $orgao_base ";
												}

												$sql .= " ORDER BY B.EORGLIDESC, A.CCENPONRPA, A.ECENPODESC, A.ECENPODETA";
											 
												
										}else{
												$sql  = "SELECT A.CCENPOSEQU, A.CCENPOCORG, A.CCENPOUNID, A.ECENPODESC, ";
												$sql .= "       A.CCENPONRPA, A.ECENPODETA, A.CORGLICODI, A.CORGLICODI ";
												$sql .= "FROM SFPC.TBCENTROCUSTOPORTAL A";
												$sql .= " WHERE A.CORGLICODI in ";
												$sql .= "       ( SELECT DISTINCT CEN.CORGLICODI ";
												$sql .= "           FROM SFPC.TBCENTROCUSTOPORTAL CEN, SFPC.TBUSUARIOCENTROCUSTO USU ";
												$sql .= "          WHERE 
																						USU.CCENPOSEQU = CEN.CCENPOSEQU 
																						AND USU.CUSUPOCODI = ". $_SESSION['_cusupocodi_'] ."   
																						AND A.ACENPOANOE = ".date("Y")." 
												";
												if($modulo == 'C') $sql.=" AND USU.FUSUCCTIPO='C'";
												$sql .= "   ) AND (A.CCENPODETA,A.CCENPOCENT) <> (77,799)";
												$sql .= "   AND A.ACENPOANOE = ".date("Y")."";				
												if(!$Todos){
														$sql .= " AND ( ";
														//$sql .= "      TRANSLATE(A.ECENPODESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($Descricao))."%' OR ";
														$sql .= "      TRANSLATE(A.ECENPODESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '%".strtoupper2(RetiraAcentos($Descricao))."%'";
														$sql .= "     )";
												}
												$sql .= "   AND A.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
												//$sql .="    AND B.FORGLISITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
												# Caso o usuário seja requisitante, só trazer os centros de custo vinculados a ele #
												if (($Requisitante == 'R' and $modulo == 'E') or $modulo == 'C'){
														$sqlcc = "SELECT CCENPOSEQU FROM SFPC.TBUSUARIOCENTROCUSTO WHERE CUSUPOCODI = ". $_SESSION['_cusupocodi_'] ."  ";
														$rescc = $db->query($sqlcc);
														if( db::isError($rescc) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlcc");
														}else{
																while ( $Selcc = $rescc->fetchRow() ){
																		if ($JaPassou == 0){
																				$CentroCustocc .= "'".$Selcc[0]."'";
																				$JaPassou = 1;
																		}else{
																				$Concatenacao = ",";
																				$CentroCustocc .= "$Concatenacao"."'".$Selcc[0]."'";
																				$JaPassou = 1;
																		}
																}
																if (empty($CentroCustocc)) $CentroCustocc = 99999999999;
																$sql .= " AND A.CCENPOSEQU IN ($CentroCustocc)";
														}
												}
												
												$sql .= " ORDER BY A.CCENPONRPA, A.ECENPODESC, A.ECENPODETA";
										}
										$res  = $db->query($sql);
										if( db::isError($res) ){
											EmailErroDB('Erro de SQL', 'erro de SQL', $res);
										}else{
												$rows = $res->numRows();
												echo "<tr>\n";
												echo "  <td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"4\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
												echo "</tr>\n";
												if( $rows > 0 ){
														$UnidadeAntes = "";
														$CentroAntes  = "";
														$RPAAntes     = "";
														for( $i=0;$i<$rows;$i++ ){
																$Cont++;
																$Linha      = $res->fetchRow();
																$Sequencial = $Linha[0];
																$Centro     = $Linha[3];
																$RPA        = $Linha[4];
																$Detalhe    = $Linha[5];
																if( (($_SESSION['_cgrempcodi_'] == 0) or ($_SESSION['_fperficorp_'] == 'S')) and $permiteCorporativo ) {
																		$Unidade = $Linha[6];
																}

																# Pega o nome da Unidade Orçamentária de acordo com o Órgão #
																if( $_SESSION['_cgrempcodi_'] != 0 and $_SESSION['_fperficorp_'] <> 'S' and $Cont == 1 and $permiteCorporativo ){																
//																if( $_SESSION['_cgrempcodi_'] != 0 and $Cont == 1 ){
																		$sqluni  = "SELECT B.EORGLIDESC ";
																		$sqluni .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B ";
																		$sqluni .= " WHERE A.CORGLICODI = B.CORGLICODI AND A.CORGLICODI = $Linha[7]";
																		$sqluni .= "   AND A.FCENPOSITU <> 'I' AND A.ACENPOANOE = ".date("Y")." "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
																		$resuni  = $db->query($sqluni);
																		if (db::isError($resuni)) {
																		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqluni");
																		    exit;
																		}else{
																				$Uni     = $resuni->fetchRow();
																				$Unidade = $Uni[0];
																		}
																}
																if( $UnidadeAntes != $Unidade  ) {
																		echo "<tr>\n";
																		echo "  <td class=\"textoabason\" bgcolor=\"#BFDAF2\" colspan=\"4\" align=\"center\">$Unidade</td>\n";
																		echo "</tr>\n";
																}
																if( $RPAAntes != $RPA or $UnidadeAntes != $Unidade ) {
																		echo "<tr>\n";
																		echo "  <td class=\"textoabason\" bgcolor=\"#DDECF9\" colspan=\"4\" align=\"center\">RPA $RPA </td>\n";
																		echo "</tr>\n";
																		echo "<tr>\n";
																		echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"30%\">CENTRO DE CUSTO</td>\n";
																		echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"60%\">DETALHAMENTO</td>\n";
																		echo "</tr>\n";
																}
																echo "<tr>\n";
																if( $CentroAntes != $Centro or $UnidadeAntes != $Unidade ){
																		echo "  <td class=\"textonormal\" bgcolor=\"#F7F7F7\" width=\"30%\">$Centro</td>\n";
																}else{
																		echo "  <td class=\"textonormal\" bgcolor=\"#F7F7F7\" width=\"30%\">&nbsp;</td>\n";
																}
																echo "	<td class=\"textonormal\" bgcolor=\"#F7F7F7\" width=\"60%\">\n";
																echo "		<a href=\"javascript:enviar('IncluirLink',$Sequencial,'".$Unidade.$SimboloConcatenacaoArray." RPA ".$RPA.$SimboloConcatenacaoArray.$Centro.$SimboloConcatenacaoArray.$Detalhe."');\"><font color=\"#000000\">$Detalhe</font></a>\n";
																echo "	</td>\n";
																echo "</tr>\n";
																$UnidadeAntes = $Unidade;
																$CentroAntes  = $Centro;
																$RPAAntes     = $RPA;
														}
												}else{
														echo "<tr>\n";
														echo "	<td valign=\"top\" colspan=\"4\" class=\"textonormal\" bgcolor=\"FFFFFF\">\n";
														echo "		Pesquisa sem Ocorrências.\n";
														echo "	</td>\n";
														echo "</tr>\n";
												}
										}
										$db->disconnect();
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
<script language="javascript" type="">
window.focus();
//-->
</script>
</body>
</html>