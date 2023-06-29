<?php
#------------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelAtendimentoCCMaterial.php
# Objetivo: Programa de Impressão do Relatório de Atendimento por Centro de Custo/Material.
# Autor:    Filipe Cavalcanti
# Data:     16/08/2005
# Alterado: Marcus Thiago
# Data:     04/01/2006
# Alterado: Álvaro Faria
# Data:     23/08/2006
# Alterado: Wagner Barros
# Data:     06/10/2006
# Data:     18/12/2006 - Correção do select para buscar a data do atendimento,
#                        e não a data da última atualização da requisição
#                        Período --> Período requisição
# Alterado: Carlos Abreu
# Data:     04/06/2007 - Filtro no combo do almoxarifado para que quando usuario for do tipo 
#                        atendimento apareça apenas o almox. que ele esteja relacionado
# Alterado: Carlos Abreu
# Data:     13/06/2007 - Colocado funcao mktime, para sempre gerar novo relatorio, na chamada do arquivo que gera o relatorio pdf
# OBS.:     Tabulação 2 espaços
#------------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/RelAtendimentoCCMaterial.php' );
AddMenuAcesso( '/estoques/RelAtendimentoCCMaterialPdf.php' );
AddMenuAcesso( '/estoques/RelAtendimentoCCMaterialTodosPdf.php' );
AddMenuAcesso( '/estoques/RelAtendimentoCCMaterialTodosAgrupadosPdf.php' );
AddMenuAcesso( '/estoques/CadIncluirCentroCusto.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao                    = $_POST['Botao'];
		$TipoUsuario              = $_POST['TipoUsuario'];
		$Almoxarifado             = $_POST['Almoxarifado'];
		$CarregaAlmoxarifado      = $_POST['CarregaAlmoxarifado'];
		$CentroCusto              = $_POST['CentroCusto'];
		$Material                 = $_POST['Material'];
		$DataIni                  = $_POST['DataIni'];
		if($DataIni != ""){ $DataIni = FormataData($DataIni); }
		$DataFim                  = $_POST['DataFim'];
		if($DataFim != ""){ $DataFim = FormataData($DataFim); }
		$CodigoReduzido           = $_POST['CodigoReduzido'];
		$Critica                  = $_POST['Critica'];
		$TipoMaterial             = $_POST['TipoMaterial'];		
		$OpcaoPesquisaMaterial    = $_POST['opcaopesquisamaterial'];
		$MaterialDescricaoDireta  = strtoupper2(trim($_POST['txtmaterialdireta']));
}else{
		$Mensagem                 = urldecode($_GET['Mensagem']);
		$Mens                     = $_GET['Mens'];
		$Tipo                     = $_GET['Tipo'];
		$Almoxarifado             = $_GET['Almoxarifado'];
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
				if( db::isError($res) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$Rows = $res->numRows();
						if($Rows == 0){
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
				if( db::isError($res) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$Rows = $res->numRows();
						if($Rows != 0) {
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
								if( db::isError($res) ){
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$Rows = $res->numRows();
										if($Rows != 0) {
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
if( $Botao == "Limpar" ){
		header("location: RelAtendimentoCCMaterial.php");
		exit;
} elseif( $Botao == "Validar" or $Botao == "Imprimir" or $Botao == "Gerar") {
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( ($Almoxarifado == "") && ($CarregaAlmoxarifado == 'N') ){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Almoxarifado";
		}elseif($Almoxarifado == "") {
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RelAtendimentoCCMaterial.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		}
		if($CentroCusto == ""){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Centro de Custo";
		}
		$MensErro = ValidaPeriodo($DataIni,$DataFim,$Mens,"RelAtendimentoCCMaterial");
		if($MensErro != ""){ $Mensagem .= $MensErro; $Mens = 1; $Tipo = 2; }
		if (($Mens == 0) and ($TipoMaterial == 1) and ($Botao == 'Imprimir')){
				# Imprime relatório de todos os materiais atendidos pelo centro de custo
				$Url = "RelAtendimentoCCMaterialTodosPdf.php?Almoxarifado=$Almoxarifado&CentroCusto=$CentroCusto&DataIni=$DataIni&DataFim=$DataFim&".mktime();
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
		} else if (($Mens == 0) and (($TipoMaterial == 2)) and (($Botao == 'Imprimir' ))){
				# Imprime relatório de todos os materiais atendidos pelo centro de custo agrupado por material			
				$Url = "RelAtendimentoCCMaterialTodosAgrupadosPdf.php?Almoxarifado=$Almoxarifado&CentroCusto=$CentroCusto&CodigoReduzido=$CodigoReduzido&DataIni=$DataIni&DataFim=$DataFim&".mktime();
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
		}	

		if($Mens == 0 and $Botao == "Gerar"){
				$Url = "RelAtendimentoCCMaterialPdf.php?Almoxarifado=$Almoxarifado&CentroCusto=$CentroCusto&CodigoReduzido=$CodigoReduzido&DataIni=$DataIni&DataFim=$DataFim&".mktime();
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
		}
}
if($Mens == 0 and $Botao and ($TipoMaterial <> 1 and $TipoMaterial <> 2)){
		$Mensagem = "Informe: ";
		if( $MaterialDescricaoDireta == "" ) {
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens = 1;
				$Tipo = 2;
				$Mensagem .= "<a href=\"javascript:document.RelAtendimentoCCMaterial.txtmaterialdireta.focus();\" class=\"titulo2\">Material</a>";
		}else{
				if($MaterialDescricaoDireta != "" and $OpcaoPesquisaMaterial == '0' and ! SoNumeros($MaterialDescricaoDireta) ){
						if( $Mens == 1 ){ $Mensagem .= ", "; }
						$Mens = 1;
						$Tipo = 2;
						$Mensagem .= "<a href=\"javascript:document.RelAtendimentoCCMaterial.txtmaterialdireta.focus();\" class=\"titulo2\">Código reduzido do Material</a>";
				}elseif( $MaterialDescricaoDireta != "" ){
						$sql  = "SELECT DISTINCT G.CMATEPSEQU, G.EMATEPDESC, J.EUNIDMSIGL ";
						$sql .= "  FROM SFPC.TBREQUISICAOMATERIAL A,SFPC.TBSITUACAOREQUISICAO B, ";
						$sql .= "       SFPC.TBTIPOSITUACAOREQUISICAO C, SFPC.TBCENTROCUSTOPORTAL D, ";
						$sql .= "       SFPC.TBITEMREQUISICAO E, SFPC.TBALMOXARIFADOPORTAL F , SFPC.TBMATERIALPORTAL G, ";
						$sql .= "       SFPC.TBLOCALIZACAOMATERIAL H, SFPC.TBARMAZENAMENTOMATERIAL I, SFPC.TBUNIDADEDEMEDIDA J ";

						# Se foi digitado algo na caixa de texto do material em pesquisa direta #
						if($MaterialDescricaoDireta != ""){
								if($OpcaoPesquisaMaterial == 0){
										if(SoNumeros($MaterialDescricaoDireta)){
												$sql .= " WHERE G.CMATEPSEQU = $MaterialDescricaoDireta ";
										}
								}else{
											$sql .= " WHERE ( ";
											$sql .= "      TRANSLATE(G.EMATEPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($MaterialDescricaoDireta))."%' OR ";
											$sql .= "      TRANSLATE(G.EMATEPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '% ".strtoupper2(RetiraAcentos($MaterialDescricaoDireta))."%' ";
											$sql .= "     )";
								}
								$sql .= "    AND B.CREQMASEQU = A.CREQMASEQU ";
								$sql .= "    AND B.TSITRESITU = ";
								$sql .= "       (SELECT MAX(SIT.TSITRESITU) FROM SFPC.TBSITUACAOREQUISICAO SIT ";
								$sql .= "        WHERE SIT.CREQMASEQU = B.CREQMASEQU AND (SIT.CTIPSRCODI IN (3,4) OR SIT.CTIPSRCODI = 6) ) ";
								$sql .= "    AND B.CTIPSRCODI <> 6 ";
								$sql .= "    AND A.DREQMADATA >= '$DataInibd' AND A.DREQMADATA <= '$DataFimbd' ";
								$sql .= "    AND A.CCENPOSEQU = D.CCENPOSEQU AND D.CCENPOSEQU = $CentroCusto ";
								$sql .= "    AND A.CREQMASEQU = E.CREQMASEQU AND E.AITEMRQTAT > 0 ";
								$sql .= "    AND E.CMATEPSEQU = G.CMATEPSEQU ";
								$sql .= "    AND G.CMATEPSEQU = I.CMATEPSEQU AND I.CLOCMACODI = H.CLOCMACODI ";
								$sql .= "    AND H.CALMPOCODI = F.CALMPOCODI AND F.CALMPOCODI = $Almoxarifado ";
								$sql .= "    AND G.CUNIDMCODI = J.CUNIDMCODI ";
								# Gera o SQL com a concatenação #
								$sqlgeral = $sql;
						}
				}
		}
}

?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
function enviar(valor){
	document.RelAtendimentoCCMaterial.Botao.value=valor;
	document.RelAtendimentoCCMaterial.submit();
}
function remeter(val1,val2){
	document.RelAtendimentoCCMaterial.Botao.value=val1;
	document.RelAtendimentoCCMaterial.CodigoReduzido.value=val2;
	document.RelAtendimentoCCMaterial.submit();
}
function validapesquisa(){
		document.RelAtendimentoCCMaterial.Botao.value = "Validar";
		document.RelAtendimentoCCMaterial.submit();
}
function AbreJanela(url,largura,altura) {
	window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=90,top=150,width='+largura+',height='+altura);
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RelAtendimentoCCMaterial.php" method="post" name="RelAtendimentoCCMaterial">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="6">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Relatórios > Atendimento CC/Material
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
		<td width="100"></td>
		<td align="left" colspan="6">
			<?php if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,1); } ?>
		</td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
			<table  border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
				<tr>
					<td class="textonormal">
						<table border="1" cellpadding="3" cellspacing="0"  bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" colspan="6" class="titulo3">
									RELATÓRIO DE ATENDIMENTO POR CENTRO DE CUSTO/MATERIAL
								</td>
							</tr>
							<tr>
								<td class="textonormal" colspan="6">
									<p align="justify">
										Para imprimir os dados do Relatório de Atendimento por Centro de Custo/Material, preencha os campos abaixo e clique no material desejado.<br><br>
										O tipo de material 'Específico', exibe as informações de atendimento detalhado de um material específico por centro de custo. O tipo de material 'Todos por Atendimento'
										exibe o atendimento dos materiais por centro de custo em cada dia de atendimento. O tipo de material 'Todos por Agrupamento' exibe o atendimento de todos os materiais
										solicitados pelo centro de custo agrupados por material.<br><br>
										Para limpar os campos, clique no botão "Limpar".<br><br>
										O período da pesquisa é referente a data de requisições.<BR><BR>
										Se você não possui o Acrobat Reader, clique <a href="javascript:janela('../pdf.php','Relatorio',400,400,1,0)" class="titulo2">AQUI</a> para fazer o download.
									</p>
								</td>
							</tr>
							<tr>
								<td class="textonormal" colspan="6">
									<table class="textonormal" border="0" align="left" summary="" width="100%">
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Almoxarifado</td>
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
												if( db::isError($res) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
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
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Centro de Custo*</td>
											<td class="textonormal">
												<?php
												# Exibe os Centro de Custo #
												$db     = Conexao();
												$sql    = "SELECT A.CCENPOSEQU, A.CCENPOCORG, A.CCENPOUNID, A.ECENPODESC, ";
												$sql   .= "       A.CCENPONRPA, A.ECENPODETA, B.EUNIDODESC, A.CCENPODETA, ";
												$sql   .= "       F.CORGLICODI, F.EORGLIDESC";
												$from   = "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBUNIDADEORCAMENTPORTAL B, ";
												$from  .= "       SFPC.TBGRUPOORGAO C, SFPC.TBGRUPOEMPRESA D, SFPC.TBORGAOLICITANTE F ";
												$where  = " WHERE A.CORGLICODI IS NOT NULL AND A.ACENPOANOE = ".date("Y")."";
												$where .= "   AND A.CCENPOCORG = B.CUNIDOORGA AND A.CCENPOUNID = B.CUNIDOCODI ";
												$where .= "   AND A.CORGLICODI = C.CORGLICODI AND C.CGREMPCODI = D.CGREMPCODI ";
												$where .= "   AND C.CORGLICODI = F.CORGLICODI ";
												if($_SESSION['_cgrempcodi_'] != 0){
														if($TipoUsuario == "T"){
																$where .= " AND D.CGREMPCODI = ".$_SESSION['_cgrempcodi_']."";
														}else{
																$from  .= " , SFPC.TBUSUARIOCENTROCUSTO E ";
																$where .= " AND E.FUSUCCTIPO IN ('T','R') AND E.CCENPOSEQU = A.CCENPOSEQU AND	";
																$where .= " E.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." AND ";
																$where .= " E.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." ";
														}
												}
												$order      = " ORDER BY B.EUNIDODESC, A.CCENPONRPA, A.CCENPOCENT, A.CCENPODETA";
												$sqlgeralCC = $sql.$from.$where.$order;
												$resCC      = $db->query($sqlgeralCC);
												if( db::isError($resCC) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlgeralCC");
												}else{
														$RowsCC = $resCC->numRows();
														if($RowsCC == 0){
																echo "Nenhum Centro de Custo cadastrado";
														}elseif($RowsCC == 1){
																$Linha           = $resCC->fetchRow();
																$CentroCusto     = $Linha[0];
																$DescCentroCusto = $Linha[3];
																$RPA             = $Linha[4];
																$Detalhamento    = $Linha[5];
																$Orgao           = $Linha[8];
																$DescOrgao       = $Linha[9];
																echo $DescOrgao."<br>&nbsp;&nbsp;&nbsp;&nbsp;";
																echo "RPA ".$RPA."<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
																echo $DescCentroCusto."<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
																echo $Detalhamento;
														}else{
																$Url = "CadIncluirCentroCusto.php?ProgramaOrigem=RelAtendimentoCCMaterial&TipoUsuario=$TipoUsuario&Almoxarifado=$Almoxarifado";
																if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																echo "<a href=\"javascript:AbreJanela('$Url',700,370);\"><img src=\"../midia/lupa.gif\" border=\"0\"></a><br>\n";
																if($CentroCusto != ""){
																		# Carrega os dados do Centro de Custo selecionado #
																		$db   = Conexao();
																		$sql  = "SELECT A.ECENPODESC, B.EORGLIDESC, A.CORGLICODI, A.CCENPONRPA, A.ECENPODETA ";
																		$sql .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B ";
																		$sql .= " WHERE A.CORGLICODI = B.CORGLICODI AND A.CCENPOSEQU = $CentroCusto ";
																		$res  = $db->query($sql);
																		if( db::isError($res) ){
																				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																		}else{
																				while( $Linha = $res->fetchRow() ){
																						$DescCentroCusto = $Linha[0];
																						$DescOrgao       = $Linha[1];
																						$Orgao           = $Linha[2];
																						$RPA             = $Linha[3];
																						$Detalhamento    = $Linha[4];
																				}
																				echo $DescOrgao."<br>&nbsp;&nbsp;&nbsp;&nbsp;";
																				echo "RPA ".$RPA."<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
																				echo $DescCentroCusto."<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
																				echo $Detalhamento;
																		}
																}
														}
												}
												$db->disconnect();
												?>
											</td>
											<input type="hidden" name="CentroCusto" value="<?php echo $CentroCusto; ?>">
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Período requisição</td>
											<td class="textonormal">
												<?php
												$DataMes = DataMes();
												if( $DataIni == "" ){ $DataIni = $DataMes[0]; }
												if( $DataFim == "" ){ $DataFim = $DataMes[1]; }
												$URLIni = "../calendario.php?Formulario=RelAtendimentoCCMaterial&Campo=DataIni";
												$URLFim = "../calendario.php?Formulario=RelAtendimentoCCMaterial&Campo=DataFim";
												?>
												<input type="text" name="DataIni" size="10" maxlength="10" value="<?php echo $DataIni;?>" class="textonormal">
												<a href="javascript:janela('<?php echo $URLIni ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
												&nbsp;a&nbsp;
												<input type="text" name="DataFim" size="10" maxlength="10" value="<?php echo $DataFim;?>" class="textonormal">
												<a href="javascript:janela('<?php echo $URLFim ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
											</td>
										</tr>
										<tr>		
												<td class="textonormal" bgcolor="#DCEDF7">Tipo de Material</td>
												<td class="textonormal" colspan="6">
												<select name="TipoMaterial" class="textonormal" onchange="Botao.value='Validar';submit();">
													<option value="0"<?if ($TipoMaterial==0){echo " selected";}?>> Específico</option>
													<option value="1"<?if ($TipoMaterial==1){echo " selected";}?>>Todos por Atendimento</option>
													<option value="2"<?if ($TipoMaterial==2){echo " selected";}?>>Todos por Agrupamento</option>
												</td>
										</tr>		
										<?php if (($TipoMaterial <> 1) and ($TipoMaterial <> 2)) {?>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7">Material</td>
													<td class="textonormal" colspan="6">
														<select name="opcaopesquisamaterial" class="textonormal">
															<option value="0" selected>Código Reduzido</option>
															<option value="1">Descrição contendo</option>
															<option value="2">Descrição iniciada por</option>
														</select>
														<input type="text" name="txtmaterialdireta" size="10" maxlength="10" class="textonormal">
														<a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0"></a>
													</td>
												</tr>
										<?}?>										
									</table>
								</td>
							</tr>
							<tr>
								<td align="right" colspan="6">
									<input type="hidden" name="CodigoReduzido" value="<?php echo $CodigoReduzido?>">
									<?php if (($TipoMaterial == 1) or ($TipoMaterial == 2)) {?>
											<input type="button" name="Imprimir" value="Imprimir" class="botao" onclick="Botao.value='Imprimir';submit();">
									<?}?>		
									<input type="button" name="Limpar" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
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
												$db = Conexao();
												$res  = $db->query($sqlgeral);
												if( db::isError($res) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlgeral");
												}else{
														$qtdres = $res->numRows();
														echo "<tr>\n";
														echo "	<td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"6\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
														echo "</tr>\n";
														if($qtdres > 0){
																echo "<tr>\n";
																echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"80%\">DESCRIÇÃO DO MATERIAL</td>\n";
																echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\">CÓD.RED.</td>\n";
																echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\" align=\"center\">UNIDADE</td>\n";
																echo "</tr>\n";
																while( $row = $res->fetchRow() ){
																		$CodigoReduzido     = $row[0];
																		$MaterialDescricao  = $row[1];
																		$UndMedidaSigla     = $row[2];
																		echo "<tr>\n";
																		echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"80%\">\n";
																		echo "		<a href=\"javascript:remeter('Gerar',$CodigoReduzido)\"> <input type=\"hidden\" name=\"Material\" value=\"$row[0]\"> <font color=\"#000000\">$MaterialDescricao</font></a>";
																		echo "	</td>\n";
																		echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\" width=\"10%\">\n";
																		echo "		$CodigoReduzido";
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
																echo "	<td valign=\"top\" colspan=\"6\" class=\"textonormal\" bgcolor=\"FFFFFF\">\n";
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
