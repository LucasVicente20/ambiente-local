<?php
#-----------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelInventarioPeriodicoContagem.php
# Objetivo: Programa de Impressão da contagem de inventário de acordo com o Almoxarifado
# Autor:    Carlos Abreu
# Data:     21/11/2006
# Alterado: Carlos Abreu
# Data:     28/05/2007 - Correção para aparecer lista de almoxarifados disponiveis quando entrar como administrador
# OBS.:     Tabulação 2 espaços
#-----------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/RelInventarioPeriodicoContagem.php' );
AddMenuAcesso( '/estoques/RelInventarioPeriodicoContagemPdf.php' );

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao               = $_POST['Botao'];
		$Almoxarifado        = $_POST['Almoxarifado'];
		$CarregaAlmoxarifado = $_POST['CarregaAlmoxarifado'];
		$Localizacao         = $_POST['Localizacao'];
		$CarregaLocalizacao  = $_POST['CarregaLocalizacao'];
		$Ordem               = $_POST['Ordem'];
		$Etapa               = $_POST['Etapa'];
}else{
		$Mensagem            = urldecode($_GET['Mensagem']);
		$Mens                = $_GET['Mens'];
		$Tipo                = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if($Botao == "Limpar"){
		header("location: RelInventarioPeriodicoContagem.php");
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
				$Mensagem .= "<a href=\"javascript:document.RelInventarioPeriodicoContagem.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
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
				$Mensagem .= "<a href=\"javascript:document.RelInventarioPeriodicoContagem.Localizacao.focus();\" class=\"titulo2\">Localização</a>";
		}
		if($Ordem == 0){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RelInventarioPeriodicoContagem.Ordem.focus();\" class=\"titulo2\">Ordem</a>";
		}
		if(!$Etapa){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RelInventarioPeriodicoContagem.Etapa.focus();\" class=\"titulo2\">Etapa</a>";
		}
		if($Mens == 0){
				$Url = "RelInventarioPeriodicoContagemPdf.php?Almoxarifado=$Almoxarifado&Localizacao=$Localizacao&Ordem=$Ordem&Etapa=$Etapa&".mktime();
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
	document.RelInventarioPeriodicoContagem.Botao.value=valor;
	document.RelInventarioPeriodicoContagem.submit();
}

<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RelInventarioPeriodicoContagem.php" method="post" name="RelInventarioPeriodicoContagem">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Inventário > Relatórios > Contagem/Recontagem Inventário
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
									RELATÓRIO DE CONTAGEM/RECONTAGEM DE INVENTÁRIO
								</td>
							</tr>
							<tr>
								<td class="textonormal">
									<p align="justify">
										Para imprimir os dados para a contagem do Inventário do Almoxarifado, preencha os campos abaixo e clique no botão "Imprimir".
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
															$sql  = "SELECT A.CALMPOCODI, A.EALMPODESC ";
															$sql .= "  FROM SFPC.TBALMOXARIFADOPORTAL A ";
															$sql .= " WHERE A.FALMPOSITU = 'A'";
															$sql .= "   AND A.FALMPOINVE = 'S'";
															if($Almoxarifado){
																	$sql   .= "  AND A.CALMPOCODI = $Almoxarifado";
															}
													}else{
															$sql  = "SELECT A.CALMPOCODI, A.EALMPODESC ";
															$sql .= "  FROM SFPC.TBALMOXARIFADOPORTAL A, SFPC.TBALMOXARIFADOORGAO B ";
															$sql .= " WHERE A.CALMPOCODI = B.CALMPOCODI ";
															$sql .= "   AND A.FALMPOSITU = 'A'";
															$sql .= "   AND A.FALMPOINVE = 'S'";
															$sql .= "   AND B.CORGLICODI IN ";
															$sql .= "       ( SELECT DISTINCT CEN.CORGLICODI ";
															$sql .= "           FROM SFPC.TBCENTROCUSTOPORTAL CEN, SFPC.TBUSUARIOCENTROCUSTO USU ";
															$sql .= "          WHERE USU.CCENPOSEQU = CEN.CCENPOSEQU AND USU.FUSUCCTIPO IN ('T','R') ";
															$sql .= "            AND USU.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." ";
															$sql .= "            AND CEN.FCENPOSITU <> 'I') ";
															$sql .= "   AND A.CALMPOCODI NOT IN ";
															$sql .= "       ( SELECT CALMPOCODI ";
															$sql .= "           FROM SFPC.TBMOVIMENTACAOMATERIAL ";
															$sql .= "          GROUP BY CALMPOCODI ";
															$sql .= "         HAVING COUNT(*) = 0)";
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
																	echo "NENHUM ALMOXARIFADO DISPONÍVEL";
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
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" width="30%">Etapa*</td>
											<td class="textonormal">
												<select name="Etapa" class="textonormal">
													<option value="" selected>Selecione uma Etapa...</option>
													<option value="1" <?php if( $Etapa == "1" ){ echo "selected"; }?>>CONTAGEM</option>
													<option value="2" <?php if( $Etapa == "2" ){ echo "selected"; }?>>RECONTAGEM</option>
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
