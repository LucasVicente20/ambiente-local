<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadNotaFiscalMaterialCancelarSelecionar.php
# Objetivo: Programa que Seleciona a Nota Fiscal para Cancelamento
# Autor:    Altamiro Pedrosa
# Data:     06/09/2005
# Alterado: Marcus Thiago
# Data:     04/01/2006
# Alterado: Álvaro Faria
# Data:     04/10/2006 - Identação / Opção de pesquisa de material com descrição "iniciada por"
#                        Correção da busca por Nota Fiscal contendo Material
# Alterado: Álvaro Faria
# Data:     10/10/2006 - Descrição com no mínimo 2 caracteres para "Descrição iniciada por"
# Alterado: Carlos Abreu
# Data:     15/12/2006 - Filtro no carregamento dos almoxarifados para bloquear quando Sob Inventário
# Alterado: Carlos Abreu
# Data:     27/12/2006 - Filtro no carregamento dos almoxarifados para liberar Almox. Educação quando Sob Inventário
# Alterado: Carlos Abreu
# Data:     04/06/2007 - Filtro no combo do almoxarifado para que quando usuario for do tipo 
#                        atendimento apareça apenas o almox. que ele esteja relacionado
# Alterado: Álvaro Faria
# Data:     20/12/2007 - Correção do select de almoxarifado para bloquear almoxarifados em inventário ou no período de inventário
# Alterado: Rodrigo Melo
# Data:     09/01/2008 - Correção do select de almoxarifado, pois o mesmo não está liberando os almoxarifados a realizarem as 
#                                 movimentações após a realização do inventário.
# Alterado: Rodrigo Melo
# Data:     27/02/2008 - Alteração para exibir após a pesquisa a data de emissão correta no campo DATA EMISSÃO.
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/CadItemDetalhe.php' );
AddMenuAcesso( '/estoques/CadNotaFiscalMaterialCancelar.php' );

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao                   = $_POST['Botao'];
		$DataIni                 = $_POST['DataIni'];
		if($DataIni != ""){ $DataIni = FormataData($DataIni); }
		$DataFim                 = $_POST['DataFim'];
		if($DataFim != ""){ $DataFim = FormataData($DataFim); }
		$Programa                = $_POST['Programa'];
		$Almoxarifado            = $_POST['Almoxarifado'];
		$CarregaAlmoxarifado     = $_POST['CarregaAlmoxarifado'];
		$NumNota                 = $_POST['NumNota'];
		$SerieNota               = strtoupper2($_POST['SerieNota']);
		$OpcaoPesquisaMaterial   = $_POST['OpcaoPesquisaMaterial'];
		$MaterialDescricaoDireta = strtoupper2(trim($_POST['MaterialDescricaoDireta']));
}else{
		$Mensagem = urldecode($_GET['Mensagem']);
		$Mens     = $_GET['Mens'];
		$Tipo     = $_GET['Tipo'];
		$Programa = $_GET['Programa'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadNotaFiscalMaterialCancelarSelecionar.php";

if($Botao == "Limpar"){
		header("location: CadNotaFiscalMaterialCancelarSelecionar.php");
		exit;
}elseif( $Botao == "Pesquisar" ){
		# Critica dos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( ($Almoxarifado == "") && ($CarregaAlmoxarifado == 'N') ){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$FlgSistema = 1;
				$Mensagem .= "Almoxarifado";
		}elseif($Almoxarifado == "") {
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$FlgSistema = 1;
				$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalCancelarSel.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		}
		$MensErro = ValidaPeriodo($DataIni,$DataFim,$Mens,"CadNotaFiscalCancelarSel");
		if($MensErro != ""){ $Mensagem .= $MensErro; $Mens = 1; $Tipo = 2; $FlgSistema = 1; }
		if( ($NumNota) and (!SoNumeros($NumNota)) ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalCancelarSel.NumNota.focus();\" class=\"titulo2\">Número da Nota Válido</a>";
		}
}
if($Botao == "Validar"){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( ($Almoxarifado == "") && ($CarregaAlmoxarifado == 'N') ){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$FlgSistema = 1;
				$Mensagem .= "Almoxarifado";
		}elseif($Almoxarifado == "") {
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$FlgSistema = 1;
				$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalCancelarSel.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		}
		$MensErro = ValidaPeriodo($DataIni,$DataFim,$Mens,"CadNotaFiscalCancelarSel");
		if($MensErro != ""){ $Mensagem .= $MensErro; $Mens = 1; $Tipo = 2; $FlgSistema = 1; }
		if( ($NumNota) and (!SoNumeros($NumNota)) ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalCancelarSel.NumNota.focus();\" class=\"titulo2\">Número da Nota Válido</a>";
		}
		if( $MaterialDescricaoDireta != "" and $OpcaoPesquisaMaterial == 0 and ! SoNumeros($MaterialDescricaoDireta) ){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens = 1;
				$Tipo = 2;
				$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalCancelarSel.MaterialDescricaoDireta.focus();\" class=\"titulo2\">Código reduzido do Material</a>";
		}elseif($MaterialDescricaoDireta != "" and ($OpcaoPesquisaMaterial == 1 or $OpcaoPesquisaMaterial == 2) and strlen($MaterialDescricaoDireta)< 2){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens = 1;
				$Tipo = 2;
				$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalCancelarSel.MaterialDescricaoDireta.focus();\" class=\"titulo2\">Descrição com no mínimo 2 caracteres</a>";
		}
}

?>
<html>
<?
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.CadNotaFiscalCancelarSel.Botao.value = valor;
	document.CadNotaFiscalCancelarSel.submit();
}
function validapesquisa(){
	if(document.CadNotaFiscalCancelarSel.MaterialDescricaoDireta.value != "") {
		document.CadNotaFiscalCancelarSel.Botao.value = "Validar";
		document.CadNotaFiscalCancelarSel.submit();
	}
}

<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadNotaFiscalMaterialCancelarSelecionar.php" method="post" name="CadNotaFiscalCancelarSel">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Nota Fiscal > Cancelar
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
		<td class="textonormal">
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
				<tr>
					<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
						 CANCELAR - NOTA FISCAL
					</td>
				</tr>
				<tr>
					<td class="textonormal" colspan="4">
						<p align="justify">
						Para cancelamento de uma Nota Fiscal cadastrada, informe os campos abaixo, clique no botão "Pesquisar" e clique no número da Nota Fiscal desejada.
						</p>
					</td>
				</tr>
				<tr>
					<td colspan="4">
						<table border="0" width="100%" summary="">
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Almoxarifado*</td>
								<td class="textonormal">
									<?php
									# Mostra o(s) Almoxarifado(s) de Acordo com o Usuário Logado #
									$db  = Conexao();
									if($_SESSION['_cgrempcodi_'] == 0){
											$sql    = "SELECT A.CALMPOCODI, A.EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL A ";
											if ($Almoxarifado) {
													$sql   .= " WHERE A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A'";
											}
									}else{
											$sql    = "SELECT A.CALMPOCODI, A.EALMPODESC ";
											$sql   .= "  FROM SFPC.TBALMOXARIFADOPORTAL A, SFPC.TBALMOXARIFADOORGAO B , SFPC.TBLOCALIZACAOMATERIAL C ";
											#$sql   .= "  LEFT OUTER JOIN (SELECT * FROM SFPC.TBINVENTARIOCONTAGEM WHERE (CLOCMACODI, AINVCOANOB, AINVCOSEQU) IN ( SELECT A.CLOCMACODI, A.AINVCOANOB, MAX(A.AINVCOSEQU) AS AINVCOSEQU FROM SFPC.TBINVENTARIOCONTAGEM A WHERE (A.FINVCOFECH IS NULL OR A.FINVCOFECH = 'N') AND (A.CLOCMACODI, A.AINVCOANOB) IN ( SELECT CLOCMACODI, MAX(AINVCOANOB) FROM SFPC.TBINVENTARIOCONTAGEM GROUP BY CLOCMACODI) GROUP BY A.CLOCMACODI, A.AINVCOANOB ) ) AS D";
											$sql   .= "  LEFT OUTER JOIN (SELECT * FROM SFPC.TBINVENTARIOCONTAGEM WHERE (CLOCMACODI, AINVCOANOB, AINVCOSEQU) IN ( SELECT A.CLOCMACODI, A.AINVCOANOB, MAX(A.AINVCOSEQU) AS AINVCOSEQU FROM SFPC.TBINVENTARIOCONTAGEM A WHERE (A.FINVCOFECH = 'S') AND (A.CLOCMACODI, A.AINVCOANOB) IN ( SELECT CLOCMACODI, MAX(AINVCOANOB) FROM SFPC.TBINVENTARIOCONTAGEM GROUP BY CLOCMACODI) GROUP BY A.CLOCMACODI, A.AINVCOANOB ) ) AS D";
											$sql   .= "    ON C.CLOCMACODI = D.CLOCMACODI ";
											$sql   .= " WHERE A.CALMPOCODI = C.CALMPOCODI AND A.CALMPOCODI = B.CALMPOCODI ";
											if($Almoxarifado){
													$sql   .= " AND A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A'";
											}
											$sql .= "   AND B.CORGLICODI in ";
											$sql .= "       (SELECT DISTINCT CEN.CORGLICODI ";
											$sql .= "           FROM SFPC.TBCENTROCUSTOPORTAL CEN, SFPC.TBUSUARIOCENTROCUSTO USU ";
											$sql .= "          WHERE USU.CCENPOSEQU = CEN.CCENPOSEQU AND USU.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." AND CEN.FCENPOSITU <> 'I' AND USU.FUSUCCTIPO IN ('T','R') AND CASE WHEN USU.FUSUCCTIPO = 'T' THEN B.CALMPOCODI = USU.CALMPOCODI ELSE CEN.FCENPOSITU <> 'I' END) ";

											# Trecho com relação ao bloqueio no inventário #
											$sql .= "   AND (( TRUE ";
											
											# Trecho com bloqueio quando inventário está aberto #
											# DESCOMENTAR DEPOIS (01-2014)

											/*
											$sql .= "   AND CASE WHEN ('".date("Y-m-d")."'>='".$InventarioDataInicial."') THEN ";
											# Para que inventário seja feito no período determinado, sem passar da data final definida, descomentar a linha abaixo e comentar a posterior #
											# $sql .= "            (A.FALMPOINVE = 'N' OR A.FALMPOINVE IS NULL) AND D.TINVCOFECH >= '".$InventarioDataInicial."' AND D.TINVCOFECH <= '".$InventarioDataFinal."' ";
											$sql .= "            (A.FALMPOINVE = 'N' OR A.FALMPOINVE IS NULL) AND D.TINVCOFECH >= '".$InventarioDataInicial."' ";
											$sql .= "       ELSE ";
											$sql .= "            (A.FALMPOINVE = 'N' OR A.FALMPOINVE IS NULL) ";
											$sql .= "        END ";
											# FIM DESCOMENTAR  
											# Trecho com relação ao período de inventário obrigatório #
											$sql .= "   AND ( ";
											$sql .= "        TO_DATE('".date('Y-m-d')."','YYYY-MM-DD') < TO_DATE('".$InventarioDataInicial."','YYYY-MM-DD') ";
											$sql .= "        OR TO_DATE('".date('Y-m-d')."','YYYY-MM-DD') > TO_DATE('".$InventarioDataFinal."','YYYY-MM-DD')";
											$sql .= "   )";
											*/ 
											
											$sql .= "   )";
											# Linha para permitir movimentações de um determinado órgão
											# COMENTAR DEPOIS (01-2014)
											/* $sql .= "				OR B.CORGLICODI IN (10, 6, 39)"; 
											# FIM COMENTAR */
											
											$sql .= "   ) ";
								
											
												
											
									}
									$sql .= " ORDER BY A.EALMPODESC ";
									$res  = $db->query($sql);
									if( db::isError($res) ){
											$CodErroEmail  = $res->getCode();
											$DescErroEmail = $res->getMessage();
											ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
									}else{
											$Rows = $res->numRows();
											if($Rows == 1){
													$Linha = $res->fetchRow();
													$Almoxarifado = $Linha[0];
													echo "$Linha[1]<br>";
													echo "<input type=\"hidden\" name=\"Almoxarifado\" value=\"$Almoxarifado\">";
													echo $DescAlmoxarifado;
											}elseif($Rows > 1){
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
													echo "ALMOXARIFADO NÃO CADASTRADO, INATIVO OU SOB INVENTÁRIO";
													echo "<input type=\"hidden\" name=\"CarregaAlmoxarifado\" value=\"N\">";
											}
									}
									$db->disconnect();
									?>
								</td>
							</tr>
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Período de Emissão*</td>
								<td class="textonormal">
									<?php
									$DataMes = DataMes();
									if( $DataIni == "" ){ $DataIni = $DataMes[0]; }
									if( $DataFim == "" ){ $DataFim = $DataMes[1]; }
									$URLIni = "../calendario.php?Formulario=CadNotaFiscalCancelarSel&Campo=DataIni";
									$URLFim = "../calendario.php?Formulario=CadNotaFiscalCancelarSel&Campo=DataFim";
									?>
									<input type="text" name="DataIni" size="10" maxlength="10" value="<?php echo $DataIni;?>" class="textonormal">
									<a href="javascript:janela('<?php echo $URLIni ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
									&nbsp;a&nbsp;
									<input type="text" name="DataFim" size="10" maxlength="10" value="<?php echo $DataFim;?>" class="textonormal">
									<a href="javascript:janela('<?php echo $URLFim ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
								</td>
							</tr>
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7">Número da Nota/Série</td>
								<td class="textonormal" colspan="2">
									<input type="text" name="NumNota" size="15" maxlength="10" class="textonormal"> /
									<input type="text" name="SerieNota" size="10" maxlength="8" class="textonormal">
								</td>
							</tr>
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7">Material</td>
								<td class="textonormal" colspan="2">
									<select name="OpcaoPesquisaMaterial" class="textonormal">
										<option value="0" <?php if( $OpcaoPesquisaMaterial == 0 or $OpcaoPesquisaMaterial == "" ){ echo "selected"; }?>>Código Reduzido</option>
										<option value="1" <?php if( $OpcaoPesquisaMaterial == 1 ){ echo "selected"; }?>>Descrição contendo</option>
										<option value="2" <?php if( $OpcaoPesquisaMaterial == 2 ){ echo "selected"; }?>>Descrição iniciada por</option>
									</select>
									<input type="text" name="MaterialDescricaoDireta" size="10" maxlength="10" class="textonormal">
									<a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0"></a>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td class="textonormal" align="right" colspan="4">
						<input type="hidden" name="Programa" value="<?php echo $Programa; ?>">
						<input type="button" name="Pesquisar" value="Pesquisar" class="botao" onClick="javascript:enviar('Pesquisar')">
						<input type="button" name="Limpar" value="Limpar" class="botao" onClick="javascript:enviar('Limpar')">
						<input type="hidden" name="Botao" value="">
					</td>
				</tr>
				<?php
				if( ($Botao == "Pesquisar" or $Botao == "Validar") and ($Mens == 0) ) {
						# Busca os Dados da Tabela de Entrada NF de Acordo com o Argumento da Pesquisa #
						$db   = Conexao();
						$sql  = "SELECT DISTINCT(A.CENTNFCODI), A.AENTNFANOE, A.AENTNFNOTA, ";
						$sql .= "       A.AENTNFSERI, A.DENTNFEMIS, A.AFORCRSEQU, A.CFORESCODI ";
						$sql .= "  FROM SFPC.TBENTRADANOTAFISCAL A, SFPC.TBITEMNOTAFISCAL B, SFPC.TBMATERIALPORTAL C ";
						$sql .= " WHERE A.CALMPOCODI = B.CALMPOCODI ";
						$sql .= "   AND A.AENTNFANOE = B.AENTNFANOE ";
						$sql .= "   AND A.CENTNFCODI = B.CENTNFCODI ";
						$sql .= "   AND B.CMATEPSEQU = C.CMATEPSEQU ";
						$sql .= "   AND A.CALMPOCODI = $Almoxarifado ";
						$sql .= "   AND (A.FENTNFCANC IS NULL OR A.FENTNFCANC = 'N') ";
						if($DataIni != "" and $DataFim != ""){
								$sql .= " AND A.DENTNFEMIS >= '".DataInvertida($DataIni)."' AND A.DENTNFEMIS <= '".DataInvertida($DataFim)."' ";
						}
						if($NumNota != ""){
								$sql .= " AND A.AENTNFNOTA = $NumNota ";
						}
						if($SerieNota != ""){
								$sql .= " AND A.AENTNFSERI = '$SerieNota' ";
						}
						if($MaterialDescricaoDireta != "" ){
								if($OpcaoPesquisaMaterial == 0){
										if( SoNumeros($MaterialDescricaoDireta ) ){
												$sql .= " AND C.CMATEPSEQU = $MaterialDescricaoDireta ";
										}
								}elseif($OpcaoPesquisaMaterial == 1){
										$sql .= " AND ( ";
										$sql .= "      TRANSLATE(C.EMATEPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($MaterialDescricaoDireta))."%' OR ";
										$sql .= "      TRANSLATE(C.EMATEPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '% ".strtoupper2(RetiraAcentos($MaterialDescricaoDireta))."%' ";
										$sql .= "  )";
								}else{
										$sql .= " AND TRANSLATE(C.EMATEPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($MaterialDescricaoDireta))."%' ";
								}
						}
						$sql .= " ORDER BY A.AENTNFNOTA, A.AENTNFSERI ";
						$res  = $db->query($sql);
						if( db::isError($res) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$Qtd = $res->numRows();
								echo "<tr>\n";
								echo "	<td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"4\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
								echo "</tr>\n";
								if($Qtd > 0){
										echo "<tr>\n";
										echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" align=\"center\">NÚMERO</td>\n";
										echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" align=\"center\">SÉRIE</td>\n";
										echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" align=\"center\">DATA EMISSÃO</td>\n";
										echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\">FORNECEDOR</td>\n";
										echo "</tr>\n";
										
										while( $Linha = $res->fetchRow() ){
												$NotaFiscal     = $Linha[0];
												$AnoNota        = $Linha[1];
												$NumeroNota     = $Linha[2];
												$SerieNota      = $Linha[3];
												$DataEmissao    = DataBarra($Linha[4]);
												$FornecedorSequ = $Linha[5];
												$FornecedorCodi = $Linha[6];
												# Resgata o nome do fornecedor #
												if ($FornecedorSequ != "") {
														# Verifica se o Fornecedor de Estoque é Credenciado #
														$sqlforn  = "SELECT NFORCRRAZS,AFORCRCCGC FROM SFPC.TBFORNECEDORCREDENCIADO ";
														$sqlforn .= " WHERE AFORCRSEQU = $FornecedorSequ ";
														$resforn  = $db->query($sqlforn);
														if( db::isError($resforn) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlforn");
														}else{
																$Linhaforn  = $resforn->fetchRow();
																$Razao = $Linhaforn[0];
																$CNPJ  = $Linhaforn[1];
														}
												}else{/*
														# Verifica se o Fornecedor de Estoque já está cadastrado #
														$sqlforn  = "SELECT EFORESRAZS,AFORESCCGC FROM SFPC.TBFORNECEDORESTOQUE ";
														$sqlforn .= " WHERE CFORESCODI = $FornecedorCodi ";
														$resforn  = $db->query($sqlforn);
														if( db::isError($resforn) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlforn");
														}else{
																$Linhaforn  = $resforn->fetchRow();
																$Razao      = $Linhaforn[0];
																$CNPJ       = $Linhaforn[1];
														}*/
														EmailErro(__FILE__."- Fornecedor não encontrado.", __FILE__, __LINE__, "Fornecedor informado não foi encontrado em SFPC.TBFORNECEDORCREDENCIADO.\n\nSequencial do fornecedor informado: '".$FornecedorSequ."'\n\nVerificar se o dado informado pelo usuário foi correto ou se o fornecedor existe.");
												}
												echo "<tr>\n";
												echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\">";
												$Url = "CadNotaFiscalMaterialCancelar.php?NotaFiscal=$NotaFiscal&AnoNota=$AnoNota&Almoxarifado=$Almoxarifado";
												if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
												echo "		<a href=\"$Url\"><font color=\"#000000\">".$NumeroNota."/".$AnoNota."</font></a>";
												echo "	</td>\n";
												echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\">$SerieNota</td>\n";
												echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\">$DataEmissao</td>\n";
												echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Razao</td>\n";
												echo "</tr>\n";
										}
								}else{
										echo "<tr>\n";
										echo "	<td valign=\"top\" colspan=\"4\" class=\"textonormal\" bgcolor=\"FFFFFF\">\n";
										echo "	Pesquisa sem Ocorrências.\n";
										echo "	</td>\n";
										echo "</tr>\n";
								}
								echo "</table>\n";
						}
						$db->disconnect();
				}
				?>
			</table>
		</td>
	</tr>
	<!-- Fim do Corpo -->
</table>
</form>
</body>
</html>
