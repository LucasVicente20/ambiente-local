<?php
#------------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelConsumoMaterialPeriodico.php
# Objetivo: Programa de Impressão do Relatório de Consumo Periódico de Material.
# Autor:    Carlos Abreu
# Data:     11/01/2007
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
AddMenuAcesso( '/estoques/RelConsumoMaterialPeriodicoPdf.php' );

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao                   = $_POST['Botao'];
		$Almoxarifado            = $_POST['Almoxarifado'];
		$CarregaAlmoxarifado     = $_POST['CarregaAlmoxarifado'];
		$DataIni                 = $_POST['DataIni'];
		if( $DataIni != "" ){ $DataIni = FormataData($DataIni); }
		$DataFim                 = $_POST['DataFim'];
		if( $DataFim != "" ){ $DataFim = FormataData($DataFim); }
		$ItemZerado              = $_POST['ItemZerado'];
}else{
		$Mensagem                = urldecode($_GET['Mensagem']);
		$Mens                    = $_GET['Mens'];
		$Tipo                    = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if($Botao == "Limpar"){
		header("location: RelConsumoMaterialPeriodico.php");
		exit;
}elseif($Botao == "Validar" or $Botao == "Imprimir"){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( ($Almoxarifado == "") && ($CarregaAlmoxarifado == 'N') ){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Almoxarifado";
		}elseif ($Almoxarifado == ""){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RelConsumoMaterialPeriodico.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		}
		$MensErro = ValidaPeriodo($DataIni,$DataFim,$Mens,"RelAtendimentoMaterial");
		if($MensErro != ""){ $Mensagem .= $MensErro; $Mens = 1; $Tipo = 2; }
		
		if( $Mens == 0 and $Botao == "Imprimir" ){
				$Url = "RelConsumoMaterialPeriodicoPdf.php?Almoxarifado=$Almoxarifado&DataIni=$DataIni&DataFim=$DataFim&ItemZerado=$ItemZerado&".mktime();
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
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
function enviar(valor){
	document.RelConsumoMaterialPeriodico.Botao.value=valor;
	document.RelConsumoMaterialPeriodico.submit();
}
function remeter(val1,val2){
	document.RelConsumoMaterialPeriodico.Botao.value=val1;
	document.RelConsumoMaterialPeriodico.CodigoReduzido.value=val2;
	document.RelConsumoMaterialPeriodico.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RelConsumoMaterialPeriodico.php" method="post" name="RelConsumoMaterialPeriodico">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Relatórios > Consumo Material > Periódico
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
			<table  border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
				<tr>
					<td class="textonormal">
						<table border="1" cellpadding="3" cellspacing="0"  bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" colspan="6" class="titulo3">
									RELATÓRIO DE CONSUMO PERIÓDICO DE MATERIAL
								</td>
							</tr>
							<tr>
								<td class="textonormal" colspan="6">
									<p align="justify">
										Para imprimir os dados do Relatório de Consumo Periódico de Material, indique o período e clique no botão Imprimir.
										Para limpar os campos, clique no botão "Limpar".<br><br>
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
														if ($Almoxarifado) {
																$sql   .= " AND A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A'";
														}
														$sql .= "   AND B.CORGLICODI = ";
														$sql .= "       ( SELECT DISTINCT CEN.CORGLICODI ";
														$sql .= "           FROM SFPC.TBCENTROCUSTOPORTAL CEN, SFPC.TBUSUARIOCENTROCUSTO USU ";
														$sql .= "          WHERE USU.CCENPOSEQU = CEN.CCENPOSEQU AND USU.CUSUPOCODI = ". $_SESSION['_cusupocodi_'] ." AND CEN.FCENPOSITU <> 'I' AND USU.FUSUCCTIPO IN ('T','R')";
														
														# restringir almoxarifado quando requisitante
														$sql .= "            AND CASE WHEN USU.FUSUCCTIPO = 'T' THEN B.CALMPOCODI = USU.CALMPOCODI ELSE CEN.FCENPOSITU <> 'I' END";
														
														$sql .= "       ) ";
												}
												$sql .= " ORDER BY A.EALMPODESC ";
												$res  = $db->query($sql);
												if(db::isError($res)){
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
																for($i=0;$i< $Rows; $i++){
																		$Linha = $res->fetchRow();
																		$DescAlmoxarifado = $Linha[1];
																		if( $Linha[0] == $Almoxarifado ){
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
												if($DataIni == ""){ $DataIni = $DataMes[0]; }
												if($DataFim == ""){ $DataFim = $DataMes[1]; }
												$URLIni = "../calendario.php?Formulario=RelConsumoMaterialPeriodico&Campo=DataIni";
												$URLFim = "../calendario.php?Formulario=RelConsumoMaterialPeriodico&Campo=DataFim";
												?>
												<input type="text" name="DataIni" size="10" maxlength="10" value="<?php echo $DataIni;?>" class="textonormal">
												<a href="javascript:janela('<?php echo $URLIni ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
												&nbsp;a&nbsp;
												<input type="text" name="DataFim" size="10" maxlength="10" value="<?php echo $DataFim;?>" class="textonormal">
												<a href="javascript:janela('<?php echo $URLFim ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Exibir Itens Zerados</td>
											<td class="textonormal">
												<input type="radio" name=ItemZerado value=""<?if ($ItemZerado==""){echo " checked";}?>> Não 
												<input type="radio" name=ItemZerado value="on"<?if ($ItemZerado=="on"){echo " checked";}?>> Sim
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td align="right" colspan="6">
									<input type="hidden" name="CodigoReduzido" value="<?php echo $CodigoReduzido?>">
									<input type="button" name="Imprimir" value="Imprimir" class="botao" onclick="javascript:enviar('Imprimir');">
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
												if(db::isError($res) ){
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
																echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\" align=\"center\" colspan=\"4\">UNIDADE</td>\n";
																echo "</tr>\n";
																while($row = $res->fetchRow() ){
																		$CodigoReduzido		  = $row[0];
																		$MaterialDescricao  = $row[1];
																		$UndMedidaSigla     = $row[2];
																		echo "<tr>\n";
																		echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"80%\">\n";
																		echo "	<a href=\"javascript:remeter('Imprimir',$CodigoReduzido)\"> <input type=\"hidden\" name=\"Material\" value=\"$row[0]\"> <font color=\"#000000\">$MaterialDescricao</font></a>";
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
