<?php
#------------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: GrafAtendimentoMaterial.php
# Autor:    Álvaro Faria
# Data:     23/01/2007
# Objetivo: Programa de seleção de informações para geração de gráfico de atendimento de Material.
# OBS.:     Tabulação 2 espaços
#------------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/GrafAtendimentoMaterialImg.php' );
AddMenuAcesso( '/estoques/GrafAtendimentoMaterialPieImg.php' );

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao          = $_POST['Botao'];
		$Almoxarifado   = $_POST['Almoxarifado'];
		$CarregaAlmoxarifado = $_POST['CarregaAlmoxarifado'];
		$Localizacao    = $_POST['Localizacao'];
		$CarregaLocalizacao = $_POST['CarregaLocalizacao'];
		$Quantidade     = $_POST['Quantidade'];
		$DataIni        = $_POST['DataIni'];
		if($DataIni != ""){ $DataIni = FormataData($DataIni); }
		$DataFim        = $_POST['DataFim'];
		if($DataFim != ""){ $DataFim = FormataData($DataFim); }
		$Alteracoes     = $_POST['Alteracoes'];
}else{
		$Mensagem       = urldecode($_GET['Mensagem']);
		$Mens           = $_GET['Mens'];
		$Tipo           = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Critica dos Campos #
if($Botao == "Limpar"){
		header("location: GrafAtendimentoMaterial.php");
		exit;
}elseif($Botao == "Gerar"){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( ($Almoxarifado == "") && ($CarregaAlmoxarifado == 'N') ){
				if ($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Almoxarifado";
		}elseif($Almoxarifado == "") {
				if($Mens == 1) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.GrafAtendimentoMaterial.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		}
		if( ($Localizacao == "" and $Almoxarifado != 'T') && ($CarregaLocalizacao == 'N') ){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Localização";
		}elseif($Localizacao == "" and $Almoxarifado != 'T') {
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.GrafAtendimentoMaterial.Localizacao.focus();\" class=\"titulo2\">Localização</a>";
		}
		$MensErro = ValidaPeriodo($DataIni,$DataFim,$Mens,"GrafAtendimentoMaterial");
		if($MensErro != ""){ $Mensagem .= $MensErro; $Mens = 1; $Tipo = 2; }

		if($Mens == 0){
				# Datas para consulta no banco de dados #
				$DataInibd = substr($DataIni,6,4)."-".substr($DataIni,3,2)."-".substr($DataIni,0,2);
				$DataFimbd = substr($DataFim,6,4)."-".substr($DataFim,3,2)."-".substr($DataFim,0,2);
				# Testa se o select irá retornar informações para a geração do gráfico #
				$db   = Conexao();
				$sql  = "SELECT A.CMATEPSEQU, SUM(CASE WHEN B.FTIPMVTIPO = 'S' THEN A.AMOVMAQTDM ELSE -A.AMOVMAQTDM END) AS SOMA ";
				$sql .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A, SFPC.TBTIPOMOVIMENTACAO B ";
				$sql .= " WHERE A.CTIPMVCODI = B.CTIPMVCODI ";
				if($Alteracoes == 'S'){
						$sql .= "   AND A.CTIPMVCODI IN (4,20,22, 2,18,19,21) ";
				}else{
						$sql .= "   AND A.CTIPMVCODI = 4 ";
				}
				$sql .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A') "; // Apresentar só as movimentações ativas
				$sql .= "   AND A.DMOVMAMOVI >= '$DataInibd' AND A.DMOVMAMOVI <= '$DataFimbd' ";
				if($Almoxarifado != 'T') $sql .= "   AND A.CALMPOCODI = $Almoxarifado ";
				$sql .= " GROUP BY A.CMATEPSEQU ";
				$res  = $db->query($sql);
				if( PEAR::isError($res) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$QtdAtend = 0;
						while($qtd = $res->fetchRow()){
								$QtdAtend = $QtdAtend + $qtd[1];
						}
						if($QtdAtend <= 0){
								$Mens = 1; $Tipo = 1;
								$Mensagem = "Nenhuma Ocorrência Encontrada";
						}
				}
				$db->disconnect();
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
<?php MenuAcesso(); ?>
function enviar(valor){
	document.GrafAtendimentoMaterial.Botao.value=valor;
	document.GrafAtendimentoMaterial.submit();
}
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="GrafAtendimentoMaterial.php" method="post" name="GrafAtendimentoMaterial">
<br><br><br><br><br>
<table cellpadding="3" border="0" width="100%" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="3">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Gráficos > Atendimento Material
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if($Mens == 1){?>
	<tr>
		<td width="100"></td>
		<td align="left" colspan="3">
			<?php if($Mens == 1){ ExibeMens($Mensagem,$Tipo,1); } ?>
		</td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal" colspan="3">
			<table  border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
				<tr>
					<td class="textonormal" >
						<table border="1" cellpadding="3" cellspacing="0"  bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle"  class="titulo3" colspan="3">
									GRÁFICO DE ATENDIMENTO DE MATERIAL
								</td>
							</tr>
							<tr>
								<td class="textonormal" colspan="3">
									<p align="justify">
										Selecione o Almoxarifado, Localização, Período, Quantidade Máxima de Materiais, Considerar Alterações e clique em Gerar para visualizar o gráfico.<BR>
										Caso não deseje considerar alterações, movimentações de Acerto de Requisição e Devolução Interna serão desconsideradas, mostrando o atendimento bruto.<BR>
										Para limpar os campos e o gráfico, clique no botão "Limpar".
									</p>
								</td>
							</tr>
							<tr>
								<td class="textonormal" colspan="3">
									<table class="textonormal" border="0" align="left" summary="" width="100%">
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Almoxarifado</td>
											<td class="textonormal">
												<?php
												# Mostra o(s) Almoxarifado(s) de Acordo com o Usuário Logado #
												$db  = Conexao();
												if($_SESSION['_cgrempcodi_'] == 0 or $_SESSION['_fperficorp_'] == 'S'){
														$sql    = "SELECT A.CALMPOCODI, A.EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL A ";
														if($Almoxarifado and $Almoxarifado != 'T'){
																$sql   .= " WHERE A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A'";
														}
												}else{
														$sql    = "SELECT A.CALMPOCODI, A.EALMPODESC ";
														$sql   .= "  FROM SFPC.TBALMOXARIFADOPORTAL A, SFPC.TBALMOXARIFADOORGAO B ";
														$sql   .= " WHERE A.CALMPOCODI = B.CALMPOCODI ";
														if($Almoxarifado and $Almoxarifado != 'T') {
																$sql   .= " AND A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A'";
														}
														$sql .= "   AND B.CORGLICODI = ";
														$sql .= "       ( SELECT DISTINCT CEN.CORGLICODI ";
														$sql .= "           FROM SFPC.TBCENTROCUSTOPORTAL CEN, SFPC.TBUSUARIOCENTROCUSTO USU ";
														$sql .= "          WHERE USU.CCENPOSEQU = CEN.CCENPOSEQU AND USU.CUSUPOCODI = ". $_SESSION['_cusupocodi_'] ." AND USU.FUSUCCTIPO IN ('T','R')) ";
												}
												$sql .= " ORDER BY A.EALMPODESC ";
												$res  = $db->query($sql);
												if( PEAR::isError($res) ){
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
																echo "<select name=\"Almoxarifado\" class=\"textonormal\" onChange=\"submit();\">\n";
																echo "	<option value=\"\">Selecione um Almoxarifado...</option>\n";
																if($_SESSION['_cgrempcodi_'] == 0 or $_SESSION['_fperficorp_'] == 'S'){
																		if($Almoxarifado == 'T'){
																				echo "	<option value=\"T\" selected>TODOS</option>\n";
																		}else{
																				echo "	<option value=\"T\">TODOS</option>\n";
																		}
																}
																for( $i=0;$i< $Rows; $i++ ){
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
																if(!$Almoxarifado){
																		echo "<input type=\"hidden\" name=\"CarregaLocalizacao\" value=\"N\">";
																}
														}elseif(!$Almoxarifado){
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
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Localização*</td>
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
														if( PEAR::isError($res) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
																$Linha = $res->fetchRow();
																if($Linha[0] == "E"){
																		$Equipamento = "ESTANTE";
																}if($Linha[0] == "A"){
																		$Equipamento = "ARMÁRIO";
																}if($Linha[0] == "P"){
																		$Equipamento = "PALETE";
																}
																$DescArea = $Linha[4];
																echo "ÁREA: $DescArea - $Equipamento - $Linha[1]: ESCANINHO $Linha[2]$Linha[3]";
																echo "<input type=\"hidden\" name=\"Localizacao\" value=\"$Localizacao\">";
														}
												}elseif($Almoxarifado and $Almoxarifado!='T') {
														# Mostra as Localizações de acordo com o Almoxarifado #
														$sql    = "SELECT A.CLOCMACODI, A.FLOCMAEQUI, A.ALOCMANEQU, ";
														$sql   .= "       A.ALOCMAPRAT, A.ALOCMACOLU, B.EARLOCDESC ";
														$sql   .= "  FROM SFPC.TBLOCALIZACAOMATERIAL A, SFPC.TBAREAALMOXARIFADO B ";
														$sql   .= " WHERE A.CALMPOCODI = $Almoxarifado AND A.FLOCMASITU = 'A'";
														$sql   .= "   AND A.CARLOCCODI = B.CARLOCCODI	";
														$sql   .= " ORDER BY B.EARLOCDESC DESC, A.FLOCMAEQUI, A.ALOCMANEQU, ";
														$sql   .= "       A.ALOCMAPRAT, A.ALOCMACOLU";
														$res  = $db->query($sql);
														if( PEAR::isError($res) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
																$Rows = $res->numRows();
																if( $Rows == 0 ){
																		echo "NENHUMA LOCALIZAÇÃO CADASTRADA PARA ESTE ALMOXARIFADO";
																		echo "<input type=\"hidden\" name=\"CarregaLocalizacao\" value=\"N\">";
																}else{
																		if( $Rows == 1 ){
																				$Linha = $res->fetchRow();
																				if($Linha[1] == "E"){
																						$Equipamento = "ESTANTE";
																				}if($Linha[1] == "A"){
																						$Equipamento = "ARMÁRIO";
																				}if($Linha[1] == "P"){
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
																						}if($Linha[1] == "A"){
																								$Equipamento = "ARMÁRIO";
																						}if($Linha[1] == "P"){
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
																						for($i=0;$i< $Rows; $i++ ){
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
																								if( $DescAreaAntes != $DescArea ){
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
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Período</td>
											<td class="textonormal">
												<?php
												$DataMes = DataMes();
												if( $DataIni == "" ){ $DataIni = $DataMes[0]; }
												if( $DataFim == "" ){ $DataFim = $DataMes[1]; }
												$URLIni = "../calendario.php?Formulario=GrafAtendimentoMaterial&Campo=DataIni";
												$URLFim = "../calendario.php?Formulario=GrafAtendimentoMaterial&Campo=DataFim";
												?>
												<input type="text" name="DataIni" size="10" maxlength="10" value="<?php echo $DataIni;?>" class="textonormal">
												<a href="javascript:janela('<?php echo $URLIni ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
												&nbsp;a&nbsp;
												<input type="text" name="DataFim" size="10" maxlength="10" value="<?php echo $DataFim;?>" class="textonormal">
												<a href="javascript:janela('<?php echo $URLFim ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Quantidade máxima de Materiais</td>
											<td class="textonormal">
												<select name="Quantidade" class="textonormal">
													<?php
													if($Quantidade == 5) echo "<option value=5 selected>5</option>"; else echo "<option value=5>5</option>";
													if($Quantidade == 10) echo "<option value=10 selected>10</option>"; else echo "<option value=10>10</option>";
													if($Quantidade == 15) echo "<option value=15 selected>15</option>"; else echo "<option value=15>15</option>";
													if($Quantidade == 20 or !$Quantidade) echo "<option value=20 selected>20</option>"; else echo "<option value=20>20</option>";
													?>
												</select>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Considerar alterações</td>
											<td class="textonormal">
												<select name="Alteracoes" class="textonormal">
													<?php
													if($Alteracoes == 'S') echo "<option value='S' selected>Sim</option>"; else echo "<option value='S'>Sim</option>";
													if($Alteracoes == 'N') echo "<option value='N' selected>Não</option>"; else echo "<option value='N'>Não</option>";
													?>
												</select>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td colspan="3" align="right">
									<input type="hidden" name="CodigoReduzido" value="<?php echo $CodigoReduzido?>">
									<input type="hidden" name="Critica" value="1">
									<input type="hidden" name="Botao" value="">
									<input type="button" name="Gerar" value="Gerar" class="botao" onclick="javascript:enviar('Gerar');">
									<input type="button" name="Limpar" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
								</td>
							</tr>
							<?php
							if($Botao == "Gerar" and $Mens == 0){
									echo "<tr>";
									echo "	<td colspan=\"3\" align=\"right\">";
									$Url = "GrafAtendimentoMaterialImg.php?Almoxarifado=$Almoxarifado&Localizacao=$Localizacao&Alteracoes=$Alteracoes&Quantidade=$Quantidade&DataIni=$DataIni&DataFim=$DataFim";
									if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
									echo "	<img src=\"$Url\" border=0>";
									echo "	</td>";
									echo "</tr>";
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
