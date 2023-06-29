<?php
# ---------------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelConsumoCentroCusto.php
# Objetivo: Programa de Impressão do Relatório de Consumo por Centro de Custo
# Autor:    Filipe Cavalcanti / Rossana Lira
# Data:     17/02/2006
# OBS.:     Tabulação 2 espaços
# ---------------------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     07/03/2006
# ---------------------------------------------------------------------------------------------
# Alterado: Wagner Barros
# Data:     04/08/2006
# ---------------------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     23/08/2006
# ---------------------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     29/08/2006 - Opção de pesquisa de material com descrição "iniciada por"
# ---------------------------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     04/06/2007 - Filtro no combo do almoxarifado para que quando usuario for do tipo 
#                        atendimento apareça apenas o almox. que ele esteja relacionado
# ---------------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 73662
# ---------------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança#
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/RelConsumoCentroCusto.php' );
AddMenuAcesso( '/estoques/RelConsumoCentroCustoPdf.php' );
AddMenuAcesso( '/estoques/RelConsumoCentroCustoTodosPdf.php' );
AddMenuAcesso( '/estoques/CadIncluirCentroCusto.php' );
AddMenuAcesso( '/estoques/RelConsumoCentroCustoMaterialPdf.php' );

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD']    == "POST"){
		$Botao                         = $_POST['Botao'];
		$DataIni                       = $_POST['DataIni'];
		if($DataIni != ""){ $DataIni = FormataData($DataIni); }
		$DataFim                       = $_POST['DataFim'];
		if($DataFim != ""){ $DataFim = FormataData($DataFim); }
		$Almoxarifado                  = $_POST['Almoxarifado'];
		$CarregaAlmoxarifado           = $_POST['CarregaAlmoxarifado'];
		$CentroCusto                   = $_POST['CentroCusto'];
		$Material                      = $_POST['Material'];
		$CodigoReduzido                = $_POST['CodigoReduzido'];
		$OpcaoPesquisaMaterial         = $_POST['OpcaoPesquisaMaterial'];
		$MaterialDescricaoDireta       = strtoupper2(trim($_POST['MaterialDescricaoDireta']));
		$Todos                         = $Post['Todos'];
}else{
		$Mens                          = $_GET['Mens'];
		$Tipo                          = $_GET['Tipo'];
		$DataIni                       = urldecode($_GET['DataIni']);
		$DataFim                       = urldecode($_GET['DataFim']);
		$Almoxarifado                  = $_GET['Almoxarifado'];
		$Mensagem                      = $_GET['Mensagem'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

$DataAtual     = date("Y-m-d");

$DataInibd = substr($DataIni,6,4)."-".substr($DataIni,3,2)."-".substr($DataIni,0,2);
$DataFimbd = substr($DataFim,6,4)."-".substr($DataFim,3,2)."-".substr($DataFim,0,2);

if($Botao == ""){
		if($_SESSION['_cgrempcodi_'] != 0){
				# Verifica se o Usuário está ligado a algum centro de Custo #
				$db   = Conexao();
				$sql  = "SELECT USUCEN.CUSUPOCODI ";
				$sql .= "  FROM SFPC.TBUSUARIOCENTROCUSTO USUCEN, SFPC.TBCENTROCUSTOPORTAL CENCUS, ";
				$sql .= "       SFPC.TBGRUPOEMPRESA GRUEMP,SFPC.TBORGAOLICITANTE ORGSOL,SFPC.TBUSUARIOPORTAL USUPOR ";
				$sql .= " WHERE USUCEN.CGREMPCODI <> 0 AND USUCEN.CCENPOSEQU = CENCUS.CCENPOSEQU AND USUCEN.FUSUCCTIPO IN ('T','R') ";
				$sql .= "   AND USUCEN.CGREMPCODI = GRUEMP.CGREMPCODI ";
				$sql .= "   AND CENCUS.CORGLICODI = ORGSOL.CORGLICODI ";
				$sql .= "   AND USUCEN.CUSUPOCODI = USUPOR.CUSUPOCODI ";
				$sql .= "   AND USUCEN.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ";
				$sql .= "   AND USUCEN.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." ";
				$sql .= " ORDER BY GRUEMP.EGREMPDESC, ORGSOL.EORGLIDESC, CENCUS.ECENPODESC, USUPOR.EUSUPORESP ";
				$res  = $db->query($sql);
				if( PEAR::isError($res) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$Rows = $res->numRows();
						if( $Rows == 0 ){
								$Mens      = 1;
								$Tipo      = 2;
								$Mensagem .= "O Usuário não está ligado a nenhum Centro de Custo";
						}
				}

				# Carrega o Tipo do Usuário e Orgão Solicitante do GrupoEmpresa/Usuário Logado #
				$sql  = "SELECT USUCEN.FUSUCCTIPO, CENCUS.CORGLICODI ";
				$sql .= "  FROM SFPC.TBUSUARIOCENTROCUSTO USUCEN, SFPC.TBCENTROCUSTOPORTAL CENCUS ";
				$sql .= " WHERE USUCEN.CCENPOSEQU = CENCUS.CCENPOSEQU AND USUCEN.FUSUCCTIPO IN ('T','R') ";
				$sql .= "   AND ( ( USUCEN.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." ";
				$sql .= "       AND USUCEN.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ) ";
				$sql .= "        OR ( USUCEN.CUSUPOCOD1 = ".$_SESSION['_cusupocodi_']." AND ";
				$sql .= "             USUCEN.CGREMPCOD1 = ".$_SESSION['_cgrempcodi_']." AND ";
				$sql .= "             '$DataAtual' BETWEEN DUSUCCINIS AND DUSUCCFIMS )";
				$sql .= "       ) AND USUCEN.FUSUCCTIPO = 'T' ";
				$res  = $db->query($sql);
				if( PEAR::isError($res) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$Rows = $res->numRows();
						if ($Rows != 0) {
								$Linha        = $res->fetchRow();
								$TipoUsuario  = $Linha[0];
								$OrgaoUsuario = $Linha[1];
						}else{
								$sql  = "SELECT USUCEN.FUSUCCTIPO, CENCUS.CORGLICODI ";
								$sql .= "  FROM SFPC.TBUSUARIOCENTROCUSTO USUCEN, SFPC.TBCENTROCUSTOPORTAL CENCUS ";
								$sql .= " WHERE USUCEN.CCENPOSEQU = CENCUS.CCENPOSEQU AND USUCEN.FUSUCCTIPO IN ('T','R') ";
								$sql .= "   AND ( ( USUCEN.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." AND ";
								$sql .= "           USUCEN.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ) OR ";
								$sql .= "         ( USUCEN.CUSUPOCOD1 = ".$_SESSION['_cusupocodi_']." AND ";
								$sql .= "           USUCEN.CGREMPCOD1 = ".$_SESSION['_cgrempcodi_']." AND ";
								$sql .= "           '$DataAtual' BETWEEN DUSUCCINIS AND DUSUCCFIMS ) ";
								$sql .= "       ) AND USUCEN.FUSUCCTIPO <> 'T' ";
								$res  = $db->query($sql);
								if( PEAR::isError($res) ){
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$Rows = $res->numRows();
										if($Rows != 0){
												$Linha        = $res->fetchRow();
												$TipoUsuario  = $Linha[0];
												$OrgaoUsuario = $Linha[1];
										}
								}
						}
				}
				$db->disconnect();
		}
}

if($Botao == "Limpar"){
		header("location: RelConsumoCentroCusto.php");
		exit;
}elseif($Botao == "Validar"){
		$Mensagem = "Informe: ";
		if( ($Almoxarifado == "") && ($CarregaAlmoxarifado == 'N') ){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Almoxarifado";
		}elseif($Almoxarifado == ""){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RelConsumoCentroCusto.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		}
		$MensErro = ValidaPeriodo($DataIni,$DataFim,$Mens,"RelConsumoCentroCusto");
		if($MensErro != ""){ $Mensagem .= $MensErro; $Mens = 1; $Tipo = 2; }
		if( $MaterialDescricaoDireta == "" ) {
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens = 1;
				$Tipo = 2;
				$Mensagem .= "<a href=\"javascript:document.RelConsumoCentroCusto.MaterialDescricaoDireta.focus();\" class=\"titulo2\">Material</a>";
		}else{
				if( $MaterialDescricaoDireta != "" and $OpcaoPesquisaMaterial == 0 and ! SoNumeros($MaterialDescricaoDireta) ){
						if( $Mens == 1 ){ $Mensagem .= ", "; }
						$Mens = 1;
						$Tipo = 2;
						$Mensagem .= "<a href=\"javascript:document.RelConsumoCentroCusto.MaterialDescricaoDireta.focus();\" class=\"titulo2\">Código reduzido do Material</a>";
				}elseif($MaterialDescricaoDireta != "" and ($OpcaoPesquisaMaterial == 1 or $OpcaoPesquisaMaterial == 2) and strlen($MaterialDescricaoDireta)< 2){
						if($Mens == 1){ $Mensagem .= ", "; }
						$Mens = 1;
						$Tipo = 2;
						$Mensagem .= "<a href=\"javascript:document.RelConsumoCentroCusto.MaterialDescricaoDireta.focus();\" class=\"titulo2\">Descrição com no mínimo 2 caracteres</a>";
				}elseif ($MaterialDescricaoDireta != "" ){
						$sql  = "SELECT DISTINCT MAT.CMATEPSEQU, MAT.EMATEPDESC, UND.EUNIDMSIGL ";
						$sql .= "	 FROM SFPC.TBMATERIALPORTAL MAT, SFPC.TBUNIDADEDEMEDIDA UND, ";
						$sql .= "	      SFPC.TBMOVIMENTACAOMATERIAL MOV, ";
						$sql .= "       SFPC.TBSUBCLASSEMATERIAL SUB ";
						$sql .= "	WHERE MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
						$sql .= "   AND MAT.CUNIDMCODI = UND.CUNIDMCODI ";
						$sql .= "	  AND MAT.CMATEPSEQU = MOV.CMATEPSEQU ";
						$sql .= "   AND MOV.CMATEPSEQU = MAT.CMATEPSEQU ";
						$sql .= "   AND MOV.CTIPMVCODI IN (2,4,18,19,20,21,22) ";
						$sql .= "   AND MOV.CALMPOCODI = $Almoxarifado ";
						$sql .= "   AND MOV.DMOVMAMOVI >= '$DataInibd' AND MOV.DMOVMAMOVI <= '$DataFimbd' ";
						# Se foi digitado algo na caixa de texto do material em pesquisa direta #
						if( $MaterialDescricaoDireta != "" ){
								if( $OpcaoPesquisaMaterial == 0 ){
										if( SoNumeros($MaterialDescricaoDireta) ){
												$sql .= " AND MAT.CMATEPSEQU = $MaterialDescricaoDireta ";
										}
								}elseif($OpcaoPesquisaMaterial == 1){
										$sql .= " AND ( ";
										$sql .= "      TRANSLATE(MAT.EMATEPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($MaterialDescricaoDireta))."%' OR ";
										$sql .= "      TRANSLATE(MAT.EMATEPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '% ".strtoupper2(RetiraAcentos($MaterialDescricaoDireta))."%' ";
										$sql .= "     )";
								}else{
										$sql .= " AND TRANSLATE(MAT.EMATEPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($MaterialDescricaoDireta))."%' ";
								}
						}
						$sql .= " ORDER BY MAT.EMATEPDESC ";
						# Gera o SQL com a concatenação das variaveis $sql #
						$sqlgeral = $sql;
				}
		}
}elseif($Botao <> ""){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( ($Almoxarifado == "") && ($CarregaAlmoxarifado == 'N') ){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RelConsumoCentroCusto.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";				
		}elseif($Almoxarifado == "") {
				if($Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RelConsumoCentroCusto.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		}
		$MensErro = ValidaPeriodo($DataIni,$DataFim,$Mens,"RelConsumoCentroCusto");
		if($MensErro != ""){ $Mensagem .= $MensErro; $Mens = 1; $Tipo = 2; }
		if($Mens == 0 and $Botao == "Imprimir"){
				$Url = "RelConsumoCentroCustoMaterialPdf.php?Almoxarifado=$Almoxarifado&Material=$CodigoReduzido&DataIni=$DataIni&DataFim=$DataFim&".mktime();
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
		}
		if($Mens == 0 and $Botao == "ImprimirTodos"){
				$Url = "RelConsumoCentroCustoPdf.php?Almoxarifado=$Almoxarifado&DataIni=$DataIni&DataFim=$DataFim&".mktime();
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
		}
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<head>
<title>Portal de Compras - Relatório de Consumo </title>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
function enviar(valor){
	document.RelConsumoCentroCusto.Botao.value=valor;
	document.RelConsumoCentroCusto.submit();
}
function emitir(valor){
	document.RelConsumoCentroCusto.Botao.value=valor;
	document.RelConsumoCentroCusto.submit();
}
function validapesquisa(){
	document.RelConsumoCentroCusto.Botao.value = "Validar";
	document.RelConsumoCentroCusto.submit();
}
function remeter(val1,val2){
	document.RelConsumoCentroCusto.Botao.value=val1;
	document.RelConsumoCentroCusto.CodigoReduzido.value=val2;
	document.RelConsumoCentroCusto.submit();
}
function chektodos(valor){
	document.RelConsumoCentroCusto.Botao.value=valor;
	document.RelConsumoCentroCusto.Todos.checked = false;
	document.RelConsumoCentroCusto.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
</head>
<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RelConsumoCentroCusto.php" method="post" name="RelConsumoCentroCusto">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="" width=100%>
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Relatórios > Consumo por Centro de Custo
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if($Mens == 1){?>
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
			<table border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="" width="100%">
				<tr>
					<td class="textonormal">
						<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
									RELATÓRIO DE CONSUMO POR CENTRO DE CUSTO
								</td>
							</tr>
							<tr>
								<td class="textonormal" colspan="4">
									<p align="justify">
										Para imprimir os dados, escolha o período e clique no botão "Imprimir".<br>
										Se você não possui o Acrobat Reader, clique <a href="javascript:janela('../pdf.php','Relatorio',400,400,1,0)" class="titulo2">AQUI</a> para fazer o download.
									</p>
								</td>
							</tr>
							<tr>
								<td colspan="4">
									<table border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" width="100%" summary="">
										<tr>
											<td colspan="4">
												<table class="textonormal" border="0" width="100%" summary="">
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Almoxarifado </td>
														<td class="textonormal">
															<?php
															# Mostra o(s) Almoxarifado(s) de Acordo com o Usuário Logado #
															$db = Conexao();
															if($_SESSION['_cgrempcodi_'] == 0 or $_SESSION['_fperficorp_'] == 'S'){
																	$sql    = "SELECT A.CALMPOCODI, A.EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL A ";
																	if($Almoxarifado){
																			$sql   .= " WHERE A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A'";
																	}
															}else{
																	$sql    = "SELECT A.CALMPOCODI, A.EALMPODESC ";
																	$sql   .= "  FROM SFPC.TBALMOXARIFADOPORTAL A, SFPC.TBALMOXARIFADOORGAO B ";
																	$sql   .= " WHERE A.CALMPOCODI = B.CALMPOCODI ";
																	if($Almoxarifado){
																			$sql   .= " AND A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A'";
																	}
																	$sql .= "   AND B.CORGLICODI = ";
																	$sql .= "       ( SELECT DISTINCT CEN.CORGLICODI ";
																	$sql .= "           FROM SFPC.TBCENTROCUSTOPORTAL CEN, SFPC.TBUSUARIOCENTROCUSTO USU ";
																	$sql .= "          WHERE USU.CCENPOSEQU = CEN.CCENPOSEQU AND USU.CUSUPOCODI = ". $_SESSION['_cusupocodi_'] ." AND CEN.FCENPOSITU <> 'I' AND USU.FUSUCCTIPO IN ('T','R') ";
																	
																	# restringir almoxarifado quando requisitante
																	$sql .= "            AND CASE WHEN USU.FUSUCCTIPO = 'T' THEN B.CALMPOCODI = USU.CALMPOCODI ELSE CEN.FCENPOSITU <> 'I' END";
																	
																	$sql .= "       )";
															}
															$sql .= " ORDER BY A.EALMPODESC ";
															$res  = $db->query($sql);
															if( PEAR::isError($res) ){
																	$CodErro = $res->getCode();
																	$DescErro = $res->getMessage();
																	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErro ($CodErro)");
															}else{
																	$Rows = $res->numRows();
																	if($Rows == 1){
																			$Linha = $res->fetchRow();
																			$Almoxarifado = $Linha[0];
																			echo "$Linha[1]<br>";
																			echo "<input type=\"hidden\" name=\"Almoxarifado\" value=\"$Almoxarifado\">";
																			echo $DescAlmoxarifado;
																	}elseif( $Rows > 1 ){
																			echo "<select name=\"Almoxarifado\" class=\"textonormal\">\n";
																			echo "	<option value=\"\">Selecione um Almoxarifado...</option>\n";
																			for($i=0; $i< $Rows; $i++){
																					$Linha = $res->fetchRow();
																					$DescAlmoxarifado = $Linha[1];
																					if($Linha[0] == $Almoxarifado){
																							echo"<option value=\"$Linha[0]\" selected>$DescAlmoxarifado</option>\n";
																					}else{
																							echo"<option value=\"$Linha[0]\">$DescAlmoxarifado</option>\n";
																					}
																			}
																			echo "</select>\n";
																			$CarregaAlmoxarifado = "";
																	}else{
																			echo "ALMOXARIFADO NÃO CADASTRADO OU INATIVO";
																			echo "<input type=\"hidden\" name=\"CarregaAlmoxarifado\" value=\"N\">";
																	}
															}
															$db->disconnect();
															?>
														</td>
													</tr>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Período</td>
														<td class="textonormal">
															<?php
															$DataMes = DataMes();
															if( $DataIni == "" ){ $DataIni = $DataMes[0]; }
															if( $DataFim == "" ){ $DataFim = $DataMes[1]; }
															$URLIni = "../calendario.php?Formulario=RelConsumoCentroCusto&Campo=DataIni";
															$URLFim = "../calendario.php?Formulario=RelConsumoCentroCusto&Campo=DataFim";
															?>
															<input type="text" name="DataIni" size="10" maxlength="10" value="<?php echo $DataIni;?>" class="textonormal">
															<a href="javascript:janela('<?php echo $URLIni ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
															&nbsp;a&nbsp;
															<input type="text" name="DataFim" size="10" maxlength="10" value="<?php echo $DataFim;?>" class="textonormal">
															<a href="javascript:janela('<?php echo $URLFim ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
														</td>
													</tr>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7">Material</td>
														<td class="textonormal" colspan="4">
															<select name="OpcaoPesquisaMaterial" class="textonormal">
																<option value="0">Código Reduzido</option>
																<option value="1">Descrição contendo</option>
																<option value="2">Descrição iniciada por</option>
															</select>
															<input type="text" name="MaterialDescricaoDireta" size="10" maxlength="10" class="textonormal">
															<a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0"></a>
															<input type="checkbox" name="Todos" onClick="javascript:chektodos('ImprimirTodos');" value="T"> Todos
														</td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td class="textonormal" align="right" colspan="4">
									<input type="button" name="Limpar" value="Limpar" class="botao" onClick="javascript:enviar('Limpar')">
									<input type="hidden" name="CodigoReduzido" value="">
									<input type="hidden" name="Botao" value="">
								</td>
							</tr>
							<?php
							if($MaterialDescricaoDireta != ""){
									if($OpcaoPesquisaMaterial == '0'){
											if(!SoNumeros($MaterialDescricaoDireta)){
													$sqlgeral = "";
											}
									}
							}
							if($sqlgeral != "" and $Mens == 0){
									if($MaterialDescricaoDireta != ""){
											$db  = Conexao();
											$res = $db->query($sqlgeral);
											if(PEAR::isError($res) ){
													ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlgeral");
											}else{
													$qtdres = $res->numRows();
													echo "<tr>\n";
													echo "	<td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"4\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
													echo "</tr>\n";
													if($qtdres > 0){
															echo "<tr>\n";
															echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"80%\">DESCRIÇÃO DO MATERIAL</td>\n";
															echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\">CÓD.RED.</td>\n";
															echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\" align=\"center\" >UNIDADE</td>\n";
															echo "</tr>\n";
															while($row = $res->fetchRow() ){
																	$CodigoReduzido		  = $row[0];
																	$MaterialDescricao  = $row[1];
																	$UndMedidaSigla     = $row[2];
																	echo "<tr>\n";
																	echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"80%\">\n";
																	echo "		<a href=\"javascript:remeter('Imprimir',$CodigoReduzido)\"> <font color=\"#000000\">$MaterialDescricao</font></a>";
																	echo "	</td>\n";
																	echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\" width=\"10%\">\n";
																	echo "		$CodigoReduzido";
																	echo "		<input type=\"hidden\" name=\"Material\" value=\"$row[0]\">";																	
																	echo "	</td>\n";
																	echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\" width=\"10%\">\n";
																	echo "		$UndMedidaSigla";
																	echo "	</td>\n";
																	echo "</tr>\n";
																	$cont++;
															}
															$db->disconnect();
													}else{
															echo "<tr>\n";
															echo "	<td valign=\"top\" colspan=\"4\" class=\"textonormal\" bgcolor=\"FFFFFF\">\n";
															echo "		Pesquisa sem Ocorrências.\n";
															echo "	</td>\n";
															echo "</tr>\n";
													}
											}
									}
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
