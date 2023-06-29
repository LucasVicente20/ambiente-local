<?php
#------------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelConsumoMaterial.php
# Objetivo: Programa de Impressão do Relatório de Consumo Anual de Material.
# Autor:    Álvaro Faria
# Data:     06/07/2006
# Alterado: Álvaro Faria
# Data:     10/09/2006 - Correção no select da pesquisa por material
# Alterado: Álvaro Faria
# Data:     29/08/2006 - Opção de pesquisa de material com descrição "iniciada por"
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
AddMenuAcesso( '/estoques/RelConsumoMaterialPdf.php' );

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao                   = $_POST['Botao'];
		$Almoxarifado            = $_POST['Almoxarifado'];
		$CarregaAlmoxarifado     = $_POST['CarregaAlmoxarifado'];
		$Material                = $_POST['Material'];
		$Ano                     = $_POST['Ano'];
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
		header("location: RelConsumoMaterial.php");
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
				$Mensagem .= "<a href=\"javascript:document.RelConsumoMaterial.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		}
		if( $Mens == 0 and $Botao == "Imprimir" ){
				$Url = "RelConsumoMaterialPdf.php?Almoxarifado=$Almoxarifado&Material=$CodigoReduzido&Ano=$Ano&".mktime();
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
		}
}
if($Mens == 0 and $Botao == "Validar"){
		$Mensagem = "Informe: ";
		if($MaterialDescricaoDireta == ""){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens = 1;
				$Tipo = 2;
				$Mensagem .= "<a href=\"javascript:document.RelConsumoMaterial.txtmaterialdireta.focus();\" class=\"titulo2\">Material</a>";
		}else{
				if( $MaterialDescricaoDireta != "" and $OpcaoPesquisaMaterial == 0 and ! SoNumeros($MaterialDescricaoDireta) ){
						if($Mens == 1){ $Mensagem .= ", "; }
						$Mens = 1;
						$Tipo = 2;
						$Mensagem .= "<a href=\"javascript:document.RelConsumoMaterial.txtmaterialdireta.focus();\" class=\"titulo2\">Código reduzido do Material</a>";
				}elseif($MaterialDescricaoDireta != "" and ($OpcaoPesquisaMaterial == 1 or $OpcaoPesquisaMaterial == 2) and strlen($MaterialDescricaoDireta)< 2){
						if($Mens == 1){ $Mensagem .= ", "; }
						$Mens = 1;
						$Tipo = 2;
						$Mensagem .= "<a href=\"javascript:document.RelConsumoMaterial.txtmaterialdireta.focus();\" class=\"titulo2\">Descrição com no mínimo 2 caracteres</a>";
				}elseif($MaterialDescricaoDireta != "" ){
						$sql  = "SELECT MAT.CMATEPSEQU, MAT.EMATEPDESC, UND.EUNIDMSIGL ";
						$sql .= "	 FROM SFPC.TBMATERIALPORTAL MAT, SFPC.TBUNIDADEDEMEDIDA UND, ";
						$sql .= "	      SFPC.TBARMAZENAMENTOMATERIAL ARM, ";
						$sql .= "       SFPC.TBLOCALIZACAOMATERIAL LOC, ";
						$sql .= "       SFPC.TBSUBCLASSEMATERIAL SUB ";
						$sql .= "	WHERE MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
						$sql .= "   AND MAT.CUNIDMCODI = UND.CUNIDMCODI ";
						$sql .= "	  AND MAT.CMATEPSEQU = ARM.CMATEPSEQU ";
						$sql .= "   AND ARM.CLOCMACODI = LOC.CLOCMACODI ";
						$sql .= "	  AND LOC.CALMPOCODI = $Almoxarifado  ";
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
	document.RelConsumoMaterial.Botao.value=valor;
	document.RelConsumoMaterial.submit();
}
function remeter(val1,val2){
	document.RelConsumoMaterial.Botao.value=val1;
	document.RelConsumoMaterial.CodigoReduzido.value=val2;
	document.RelConsumoMaterial.submit();
}
function validapesquisa(){
		document.RelConsumoMaterial.Botao.value = "Validar";
		document.RelConsumoMaterial.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RelConsumoMaterial.php" method="post" name="RelConsumoMaterial">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Relatórios > Consumo Material > Anual
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
									RELATÓRIO DE CONSUMO ANUAL DE MATERIAL
								</td>
							</tr>
							<tr>
								<td class="textonormal" colspan="6">
									<p align="justify">
										Para imprimir os dados do Relatório de Consumo Anual de Material, indique o ano e efetue a busca pelo material desejado.
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
											<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Ano</td>
											<td class="textonormal">
												<select name="Ano" class="textonormal">
													<?
													for ($i=date("Y");$i>=2006;$i--){
															if ($Ano==$i){
																	echo "<option value=\"$i\" selected>$i</option>\n";
															} else {
																	echo "<option value=\"$i\">$i</option>\n";
															}
													}
													?>
												</select>
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
