<?php
#-----------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelInventarioFechamento.php
# Autor:    Carlos Abreu
# Data:     13/12/2006
# Objetivo: Programa de Impressão do Relatório de Conclusão do Inventário
# Alterado: Rossana Lira
# Data:     24/05/2007 - Exibição da data de fechamento do inventário
# Alterado: Carlos Abreu
# Data:     04/06/2007 - Filtro no combo do almoxarifado para que quando usuario for do tipo 
#                        atendimento apareça apenas o almox. que ele esteja relacionado
# OBS.:     Tabulação 2 espaços
#-----------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/RelInventarioFechamento.php' );
AddMenuAcesso( '/estoques/RelInventarioFechamentoPdf.php' );

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao                   = $_POST['Botao'];
		$Almoxarifado            = $_POST['Almoxarifado'];
		$CarregaAlmoxarifado     = $_POST['CarregaAlmoxarifado'];
		$Localizacao             = $_POST['Localizacao'];
		$CarregaLocalizacao      = $_POST['CarregaLocalizacao'];
		$Ano_Sequencial_DataBase_DataFecha = $_POST['Ano_Sequencial_DataBase_DataFecha'];
		list($Ano,$Sequencial,$DataBase,$DataFecha) = explode("_",$Ano_Sequencial_DataBase_DataFecha);
		$Ordem                   = $_POST['Ordem'];
}else{
		$Mensagem                = urldecode($_GET['Mensagem']);
		$Mens                    = $_GET['Mens'];
		$Tipo                    = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if($Botao == "Limpar"){
		header("location: RelInventarioFechamento.php");
		exit;
}elseif($Botao == "Imprimir"){
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
				$Mensagem .= "<a href=\"javascript:document.RelInventarioFechamento.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		}
		if( ($Localizacao == "") && ($CarregaLocalizacao == 'N') ){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Localização";
		}elseif($Localizacao == "") {
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RelInventarioFechamento.Localizacao.focus();\" class=\"titulo2\">Localização</a>";
		} elseif($Ano_Sequencial_DataBase_DataFecha=="") {
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RelInventarioFechamento.Ano_Sequencial.focus();\" class=\"titulo2\">Data Base</a>";
		}
		if($Ordem == 0){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RelInventarioFechamento.Ordem.focus();\" class=\"titulo2\">Ordem</a>";
		}
		if($Mens == 0){
				$Url = "RelInventarioFechamentoPdf.php?Almoxarifado=$Almoxarifado&Localizacao=$Localizacao&Ordem=$Ordem&Ano=$Ano&Sequencial=$Sequencial&DataBase=$DataBase&DataFecha=$DataFecha&New=&".mktime();
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
	document.RelInventarioFechamento.Botao.value=valor;
	document.RelInventarioFechamento.submit();
}

<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RelInventarioFechamento.php" method="post" name="RelInventarioFechamento">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Inventário > Relatórios > Fechamento
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
			<table border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
				<tr>
					<td class="textonormal">
						<table border="1" cellpadding="3" cellspacing="0"  bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" colspan="4" class="titulo3">
									RELATÓRIO DE FECHAMENTO DE INVENTÁRIO
								</td>
							</tr>
							<tr>
								<td class="textonormal">
									<p align="justify">
										Para imprimir os dados de Fechamento do Inventário do Almoxarifado, preencha os campos abaixo e clique no botão "Imprimir".
										Para limpar os campos, clique no botão "Limpar".<br><br>
										Se você não possui o Acrobat Reader, clique <a href="javascript:janela('../pdf.php','Relatorio',400,400,1,0)" class="titulo2">AQUI</a> para fazer o download.
									</p>
								</td>
							</tr>
							<tr>
								<td class="textonormal">
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
															$sql .= "          WHERE USU.CCENPOSEQU = CEN.CCENPOSEQU AND USU.CUSUPOCODI = ". $_SESSION['_cusupocodi_'] ." AND USU.FUSUCCTIPO IN ('T','R') ";
															
															# restringir almoxarifado quando requisitante
															$sql .= "            AND CASE WHEN USU.FUSUCCTIPO = 'T' THEN B.CALMPOCODI = USU.CALMPOCODI ELSE CEN.FCENPOSITU <> 'I' END";
															
															$sql .= "            AND CEN.FCENPOSITU <> 'I')"; // Inclusão da condição para mostrar centro de custos diferentes de inativos
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
																	echo "<select name=\"Almoxarifado\" class=\"textonormal\" onChange=\"javascript:enviar('TrocaAlmoxarifado');\">\n";
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
																	if(!$Almoxarifado){
																			echo "<input type=\"hidden\" name=\"CarregaLocalizacao\" value=\"N\">";
																	}
															}else{
																	echo "ALMOXARIFADO NÃO CADASTRADO OU INATIVO";
																	echo "<input type=\"hidden\" name=\"CarregaAlmoxarifado\" value=\"N\">";
																	echo "<input type=\"hidden\" name=\"CarregaLocalizacao\" value=\"N\">";
															}
													}
													$db->disconnect();
													?>
												</td>
											</tr>
											<tr>
												<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Localização</td>
												<td class="textonormal">
													<?php
													$db = Conexao();
													if($Localizacao != ""){
															# Mostra a Descrição de Acordo com o Almoxarifado #
															$sql    = "SELECT A.FLOCMAEQUI, A.ALOCMANEQU, A.ALOCMAPRAT, A.ALOCMACOLU, B.EARLOCDESC ";
															$sql   .= "  FROM SFPC.TBLOCALIZACAOMATERIAL A, SFPC.TBAREAALMOXARIFADO B";
															$sql   .= " WHERE A.CLOCMACODI = $Localizacao AND A.FLOCMASITU = 'A'";
															$sql   .= "   AND A.CARLOCCODI = B.CARLOCCODI	";
															$res  = $db->query($sql);
															if( db::isError($res) ){
																	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
															}else{
																	$Linha = $res->fetchRow();
																	if($Linha[0] == "E"){
																			$Equipamento = "ESTANTE";
																	}
																	if($Linha[0] == "A"){
																			$Equipamento = "ARMÁRIO";
																	}
																	if($Linha[0] == "P"){
																			$Equipamento = "PALETE";
																	}
																	$DescArea = $Linha[4];
																	echo "ÁREA: $DescArea - $Equipamento - $Linha[1]: ESCANINHO $Linha[2]$Linha[3]";
																	echo "<input type=\"hidden\" name=\"Localizacao\" value=\"$Localizacao\">";
															}
													}elseif($Almoxarifado){
															# Mostra as Localizações de acordo com o Almoxarifado #
															$sql    = "SELECT A.CLOCMACODI, A.FLOCMAEQUI, A.ALOCMANEQU, ";
															$sql   .= "       A.ALOCMAPRAT, A.ALOCMACOLU, B.EARLOCDESC ";
															$sql   .= "  FROM SFPC.TBLOCALIZACAOMATERIAL A, SFPC.TBAREAALMOXARIFADO B ";
															$sql   .= " WHERE A.CALMPOCODI = $Almoxarifado AND A.FLOCMASITU = 'A'";
															$sql   .= "   AND A.CARLOCCODI = B.CARLOCCODI	";
															$sql   .= " ORDER BY B.EARLOCDESC DESC, A.FLOCMAEQUI, A.ALOCMANEQU, ";
															$sql   .= "       A.ALOCMAPRAT, A.ALOCMACOLU";
															$res  = $db->query($sql);
															if( db::isError($res) ){
																	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
															}else{
																	$Rows = $res->numRows();
																	if($Rows == 0){
																			echo "NENHUMA LOCALIZAÇÃO CADASTRADA PARA ESTE ALMOXARIFADO";
																			echo "<input type=\"hidden\" name=\"CarregaLocalizacao\" value=\"N\">";
																	}else{
																			if($Rows == 1){
																					$Linha = $res->fetchRow();
																					if($Linha[1] == "E"){
																							$Equipamento = "ESTANTE";
																					}
																					if($Linha[1] == "A"){
																							$Equipamento = "ARMÁRIO";
																					}
																					if($Linha[1] == "P"){
																							$Equipamento = "PALETE";
																					}
																					echo "ÁREA: $Linha[5] - $Equipamento - $Linha[2]: ESCANINHO $Linha[3]$Linha[4]";
																					$Localizacao = $Linha[0];
																					echo "<input type=\"hidden\" name=\"Localizacao\" value=\"$Localizacao\">";
																			}else{
																				if($Rows == 1){
																						$Linha = $res->fetchRow();
																						if($Linha[1] == "E"){
																								$Equipamento = "ESTANTE";
																						}
																						if($Linha[1] == "A"){
																								$Equipamento = "ARMÁRIO";
																						}
																						if($Linha[1] == "P"){
																								$Equipamento = "PALETE";
																						}
																						echo "ÁREA: $Linha[5] - $Equipamento - $Linha[2]: ESCANINHO $Linha[3]$Linha[4]";
																						$Localizacao = $Linha[0];
																						echo "<input type=\"hidden\" name=\"Localizacao\" value=\"$Localizacao\">";
																				}else{
																						echo "<select name=\"Localizacao\" class=\"textonormal\" onChange=\"submit();\">\n";
																						echo "	<option value=\"\">Selecione uma Localização...</option>\n";
																						$EquipamentoAntes = "";
																						$DescAreaAntes    = "";
																						for($i=0; $i< $Rows; $i++){
																								$Linha = $res->fetchRow();
																								$CodEquipamento = $Linha[2];
																								if($Linha[1] == "E"){
																										$Equipamento = "ESTANTE";
																								}if($Linha[1] == "A"){
																										$Equipamento = "ARMÁRIO";
																								}if($Linha[1] == "P"){
																										$Equipamento = "PALETE";
																								}
																								$NumeroEquip = $Linha[2];
																								$Prateleira  = $Linha[3];
																								$Coluna      = $Linha[4];
																								$DescArea    = $Linha[5];
																								if($DescAreaAntes != $DescArea){
																										echo"<option value=\"\">$DescArea</option>\n";
																										$Edentecao = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
																								}
																								if( $CodEquipamentoAntes != $CodEquipamento or $EquipamentoAntes != $Equipamento ){
																										echo"<option value=\"\">$Edentecao $Equipamento - $NumeroEquip</option>\n";
																								}
																								if( $Localizacao == $Linha[0] ){
																										echo"<option value=\"$Linha[0]\" selected>$Edentecao $Edentecao ESCANINHO $Prateleira$Coluna</option>\n";
																								}else{
																										echo"<option value=\"$Linha[0]\">$Edentecao $Edentecao ESCANINHO $Prateleira$Coluna</option>\n";
																								}
																								$DescAreaAntes       = $DescArea;
																								$CodEquipamentoAntes = $CodEquipamento;
																								$EquipamentoAntes    = $Equipamento;
																						}
																							echo "</select>\n";
																						$CarregaLocalizacao = "";
																				}
																		}
																}
														}
												}
												$db->disconnect();
												?>
											</td>
										</tr>
										<?php
										if($Localizacao != ""){
												$db = Conexao();
												echo "<tr>\n";
												echo "<td class=\"textonormal\" bgcolor=\"#DCEDF7\" width=\"30%\">Data Base*</td>\n";
												echo "<td class=\"textonormal\">\n";
												echo "<select name=\"Ano_Sequencial_DataBase_DataFecha\" class=\"textonormal\">\n";
												$sql  = "SELECT AINVCOANOB, AINVCOSEQU, TO_CHAR(TINVCOBASE,'DD/MM/YYYY'), TO_CHAR(TINVCOFECH,'DD/MM/YYYY') ";
												$sql .= "  FROM SFPC.TBINVENTARIOCONTAGEM ";
												$sql .= " WHERE CLOCMACODI = $Localizacao ";
												$sql .= "   AND FINVCOFECH = 'S' AND TINVCOBASE IS NOT NULL ";
												$sql .= " ORDER BY TINVCOBASE DESC ";
												$res = $db->query($sql);
												if( db::isError($res) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														echo "<option value=\"\" selected>Selecione uma Data Base...</option>\n";
														while ( $Linha = $res->fetchRow()){
																echo "<option value=\"$Linha[0]_$Linha[1]_$Linha[2]_$Linha[3]\">$Linha[2]</option>\n";
														}
												}
												echo "</select>\n";
												echo "</td>\n";
												echo "</tr>\n";
												$db->disconnect();
										}
										?>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" width="30%">Ordem*</td>
											<td class="textonormal">
												<select name="Ordem" class="textonormal">
													<option value="" selected>Selecione uma Ordem...</option>
													<?php if ( $Ordem == "" ) $Ordem = 2;?>
													<option value="1" <?php if( $Ordem == "1" ){ echo "selected"; }?>>FAMÍLIA</option>
													<option value="2" <?php if( $Ordem == "2" ){ echo "selected"; }?>>MATERIAL</option>
												</select>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td align="right">
									<input type="button" name="Imprimir" value="Imprimir" class="botao" onclick="javascript:enviar('Imprimir');">
									<input type="button" name="Limpar" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
									<input type="hidden" name="Botao" value="">
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
