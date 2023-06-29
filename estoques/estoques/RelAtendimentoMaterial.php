<?php
#------------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelAtendimentoMaterial.php
# Objetivo: Programa de Impressão do Relatório de Atendimento por Material em um Período.
# Autor:    Filipe Cavalcanti
# Data:     16/08/2005
# Alterado: Marcus Thiago
# Data:     04/01/2006
# Alterado: Álvaro Faria
# Data:     22/08/2006 - Pesquisa só retorna materiais que foram atendidos no período
# Alterado: Álvaro Faria
# Data:     29/08/2006 - Opção de pesquisa de material com descrição "iniciada por"
# Data:     18/12/2006 - Correção do select para mostrar a data do atendimento,
#                        e não a data da última atualização da requisição
#                        Período --> Período requisição
# Alterado: Carlos Abreu
# Data:     04/06/2007 - Filtro no combo do almoxarifado para que quando usuario for do tipo 
#                        atendimento apareça apenas o almox. que ele esteja relacionado
# OBS.:     Tabulação 2 espaços
#------------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/RelAtendimentoMaterial.php' );
AddMenuAcesso( '/estoques/RelAtendimentoMaterialPdf.php' );

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao                   = $_POST['Botao'];
		$Almoxarifado            = $_POST['Almoxarifado'];
		$CarregaAlmoxarifado     = $_POST['CarregaAlmoxarifado'];
		$Material                = $_POST['Material'];
		$DataIni                 = $_POST['DataIni'];
		if( $DataIni != "" ){ $DataIni = FormataData($DataIni); }
		$DataFim                 = $_POST['DataFim'];
		if( $DataFim != "" ){ $DataFim = FormataData($DataFim); }
		$CodigoReduzido          = $_POST['CodigoReduzido'];
		$OpcaoPesquisaMaterial   = $_POST['opcaopesquisamaterial'];
		$MaterialDescricaoDireta = strtoupper2(trim($_POST['txtmaterialdireta']));
}else{
		$Mensagem                = urldecode($_GET['Mensagem']);
		$Mens                    = $_GET['Mens'];
		$Tipo                    = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if($Botao == "Limpar"){
		header("location: RelAtendimentoMaterial.php");
		exit;
}elseif( $Botao == "Validar" or $Botao == "Imprimir" ){
		$Mens     = 0;
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
				$Mensagem .= "<a href=\"javascript:document.RelAtendimentoMaterial.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		}
		$MensErro = ValidaPeriodo($DataIni,$DataFim,$Mens,"RelAtendimentoMaterial");
		if($MensErro != ""){ $Mensagem .= $MensErro; $Mens = 1; $Tipo = 2; }

		if($Mens == 0 and $Botao == "Imprimir"){
				$Url = "RelAtendimentoMaterialPdf.php?Almoxarifado=$Almoxarifado&CC=$CC&Material=$CodigoReduzido&DataIni=$DataIni&DataFim=$DataFim&".mktime();
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
		}
}
if($Mens == 0 and $Botao == "Validar"){
		$Mensagem = "Informe: ";
		if($MaterialDescricaoDireta == ""){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens = 1;
				$Tipo = 2;
				$Mensagem .= "<a href=\"javascript:document.RelAtendimentoMaterial.txtmaterialdireta.focus();\" class=\"titulo2\">Material</a>";
		}else{
				if($MaterialDescricaoDireta != "" and $OpcaoPesquisaMaterial == 0 and ! SoNumeros($MaterialDescricaoDireta) ){
						if( $Mens == 1 ){ $Mensagem .= ", "; }
						$Mens = 1;
						$Tipo = 2;
						$Mensagem .= "<a href=\"javascript:document.RelAtendimentoMaterial.txtmaterialdireta.focus();\" class=\"titulo2\">Código reduzido do Material</a>";
				}elseif($MaterialDescricaoDireta != "" and ($OpcaoPesquisaMaterial == 1 or $OpcaoPesquisaMaterial == 2) and strlen($MaterialDescricaoDireta)< 2){
						if($Mens == 1){ $Mensagem .= ", "; }
						$Mens = 1;
						$Tipo = 2;
						$Mensagem .= "<a href=\"javascript:document.RelAtendimentoMaterial.txtmaterialdireta.focus();\" class=\"titulo2\">Descrição com no mínimo 2 caracteres</a>";
				}elseif($MaterialDescricaoDireta != ""){
						$DataInibd = substr($DataIni,6,4)."-".substr($DataIni,3,2)."-".substr($DataIni,0,2);
						$DataFimbd = substr($DataFim,6,4)."-".substr($DataFim,3,2)."-".substr($DataFim,0,2);
						$sql  = "SELECT DISTINCT MAT.CMATEPSEQU, MAT.EMATEPDESC, UND.EUNIDMSIGL ";
						$sql .= "  FROM SFPC.TBMATERIALPORTAL MAT, ";
						$sql .= "       SFPC.TBUNIDADEDEMEDIDA UND, ";
						$sql .= "       SFPC.TBREQUISICAOMATERIAL REQ, ";
						$sql .= "       SFPC.TBSITUACAOREQUISICAO SIT, ";
						$sql .= "       SFPC.TBITEMREQUISICAO ITE ";
						$sql .= " WHERE MAT.CUNIDMCODI = UND.CUNIDMCODI ";
						$sql .= "   AND MAT.CMATEPSEQU = ITE.CMATEPSEQU ";
						$sql .= "   AND REQ.CREQMASEQU = SIT.CREQMASEQU ";
						$sql .= "   AND REQ.CREQMASEQU = ITE.CREQMASEQU ";
						$sql .= "   AND REQ.DREQMADATA >= '$DataInibd' ";
						$sql .= "   AND REQ.DREQMADATA <= '$DataFimbd' ";
						$sql .= "   AND REQ.CALMPOCODI = $Almoxarifado ";
						$sql .= "   AND SIT.TSITRESITU = ";
						$sql .= "      (SELECT MAX(SITU.TSITRESITU) FROM SFPC.TBSITUACAOREQUISICAO SITU ";
						$sql .= "        WHERE SITU.CREQMASEQU = SIT.CREQMASEQU AND (SITU.CTIPSRCODI IN (3,4) OR SITU.CTIPSRCODI = 6) ) ";
						$sql .= "   AND SIT.CTIPSRCODI <> 6 ";
						# Se foi digitado algo na caixa de texto do material em pesquisa direta #
						if($MaterialDescricaoDireta != ""){
								if($OpcaoPesquisaMaterial == 0){
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
	document.RelAtendimentoMaterial.Botao.value=valor;
	document.RelAtendimentoMaterial.submit();
}
function remeter(val1,val2){
	document.RelAtendimentoMaterial.Botao.value=val1;
	document.RelAtendimentoMaterial.CodigoReduzido.value=val2;
	document.RelAtendimentoMaterial.submit();
}
function validapesquisa(){
		document.RelAtendimentoMaterial.Botao.value = "Validar";
		document.RelAtendimentoMaterial.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
</head>
<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RelAtendimentoMaterial.php" method="post" name="RelAtendimentoMaterial">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Relatórios > Atendimento por Material em um Período
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
						<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" colspan="6" class="titulo3">
									RELATÓRIO DE ATENDIMENTO POR MATERIAL EM UM PERÍODO
								</td>
							</tr>
							<tr>
								<td class="textonormal" colspan="6">
									<p align="justify">
										Para imprimir os dados do Relatório de Atendimento por Material, preencha os campos abaixo e clique no material desejado.
										Para limpar os campos, clique no botão "Limpar".<br><br>
										O período da pesquisa é referente a data de requisições atendidas.<BR><BR>
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
														
														$sql .= "       ) ";
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
																echo "ALMOXARIFADO NÃO CADASTRADO OU INATIVO";
																echo "<input type=\"hidden\" name=\"CarregaAlmoxarifado\" value=\"N\">";
														}
												}
												$db->disconnect();
												?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Período da requisição</td>
											<td class="textonormal">
												<?php
												$DataMes = DataMes();
												if($DataIni == ""){ $DataIni = $DataMes[0]; }
												if($DataFim == ""){ $DataFim = $DataMes[1]; }
												$URLIni = "../calendario.php?Formulario=RelAtendimentoMaterial&Campo=DataIni";
												$URLFim = "../calendario.php?Formulario=RelAtendimentoMaterial&Campo=DataFim";
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
											<td class="textonormal" colspan="2">
												<select name="opcaopesquisamaterial" class="textonormal">
													<option value="0" selected>Código Reduzido</option>
													<option value="1">Descrição contendo</option>
													<option value="2">Descrição iniciada por</option>
												</select>
												<input type="text" name="txtmaterialdireta" size="10" maxlength="10" class="textonormal">
												<a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0"></a>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td align="right" colspan="6">
									<input type="hidden" name="CodigoReduzido" value="<?php echo $CodigoReduzido?>">
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
																echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\" align=\"center\">CÓD.RED.</td>\n";
																echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\" align=\"center\">UNIDADE</td>\n";
																echo "</tr>\n";
																while( $row = $res->fetchRow() ){
																		$CodigoReduzido     = $row[0];
																		$MaterialDescricao  = $row[1];
																		$UndMedidaSigla     = $row[2];
																		echo "<tr>\n";
																		echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"80%\">\n";
																		echo "		<a href=\"javascript:remeter('Imprimir',$CodigoReduzido)\"> <input type=\"hidden\" name=\"Material\" value=\"$row[0]\"> <font color=\"#000000\">$MaterialDescricao</font></a>";
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
																echo "	<td valign=\"top\" colspan=\"3\" class=\"textonormal\" bgcolor=\"FFFFFF\">\n";
																echo "		Pesquisa sem Ocorrências.\n";
																echo "	</td>\n";
																echo "</tr>\n";
														}
												}
										}
								}
								?>
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
