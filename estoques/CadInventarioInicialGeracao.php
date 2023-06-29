<?php
#------------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadInventarioInicialGeracao.php
# Objetivo: Programa de Geração(Inclusão/Alteração) de Inventário do Estoque
# Autor:    Carlos Abreu
# Data:     10/11/2006
# Alterado: Carlos Abreu
# Data:     04/06/2007 - Filtro no combo do almoxarifado para que quando usuario for do tipo 
#                        atendimento apareça apenas o almox. que ele esteja relacionado
# Alterado: Carlos Abreu
# Data:     31/08/2007 - Ajuste no select do carregamento de almoxarifados
# OBS.:     Tabulação 2 espaços
#------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao               = $_POST['Botao'];
		$Almoxarifado        = $_POST['Almoxarifado'];
		$Localizacao         = $_POST['Localizacao'];
		$DataBase            = $_POST['DataBase'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Ano de Exercício #
$AnoExercicio = date("Y");

if( $Localizacao != "" ){
		# Resgata a flag do fechamento #
		$db   = Conexao();
		$sql  = "SELECT FINVCOFECH ";
		$sql .= "FROM SFPC.TBINVENTARIOCONTAGEM ";
		$sql .= "WHERE CLOCMACODI = $Localizacao AND AINVCOANOB = $AnoExercicio ";
		$res  = $db->query($sql);
		if( PEAR::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha         = $res->fetchRow();
				$FlgFechamento = $Linha[0];
				if( $FlgFechamento == "S" ){
						if( $Mens == 1 ){ $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 1;
						$Mensagem .= "Inventário Fechado. A geração não pode ser efetuada";
				}
		}
		$db->disconnect();
}
if( $Botao == "Iniciar" ){
		# Critica dos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if ($Almoxarifado == "") {
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadInventarioInicialGeracao.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		}
		if ($Localizacao == "") {
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadInventarioInicialGeracao.Localizacao.focus();\" class=\"titulo2\">Localização</a>";
		}
		$MensErro = ValidaData($DataBase);
		if( $MensErro != "" ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadInventarioInicialGeracao.DataBase.focus();\" class=\"titulo2\">Data Base Válida</a>";
		}
		if( $Mens == 0 ){
				$db = Conexao();
				$Ano=date("Y");
				$Sequencial = 1;
				$datahora = date("Y-m-d H:i:s");
				$db->query('BEGIN');
				$sql  = "INSERT INTO SFPC.TBINVENTARIOCONTAGEM ";
				$sql .= "       (CLOCMACODI, AINVCOANOB, AINVCOSEQU, FINVCOFECH, TINVCOBASE, ";
				$sql .= "        CGREMPCODI, CUSUPOCODI, TINVCOULAT";
				$sql .= "       ) VALUES (";
				$sql .= "        $Localizacao, ".date("Y").", $Sequencial, NULL, TO_DATE('".$DataBase."','DD/MM/YYYY'),";
				$sql .= "        ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].", '$datahora'";
				$sql .= "       )";
				$res  = $db->query($sql);
				if( PEAR::isError($res) ){
						$res  = $db->query('ROLLBACK');
						$res  = $db->query('END');
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						$db->disconnect();
						exit;
				}
				$sql  = "UPDATE SFPC.TBALMOXARIFADOPORTAL ";
				$sql .= "   SET FALMPOINVE = 'S' ";
				$sql .= " WHERE CALMPOCODI = $Almoxarifado";
				$res  = $db->query($sql);
				if( PEAR::isError($res) ){
						$res  = $db->query('ROLLBACK');
						$res  = $db->query('END');
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						$db->disconnect();
						exit;
				}
				$db->query('COMMIT');
				$db->query('END');
				$Mens           = 1;
				$Tipo           = 1;
				$Mensagem       = "Geração do Inventário Inicial Efetuada com Sucesso";
				$Almoxarifado	  = "";
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
<!--
function enviar(valor){
	document.CadInventarioInicialGeracao.Botao.value = valor;
	document.CadInventarioInicialGeracao.submit();
}
function AbreJanela(url,largura,altura) {
	window.open(url,'detalhe','status=no,scrollbars=yes,left=70,top=130,width='+largura+',height='+altura);
}
function AbreJanelaItem(url,largura,altura) {
	if( ! document.CadInventarioInicialGeracao.Almoxarifado.value ){
			document.CadInventarioInicialGeracao.submit();
	}else	if( ! document.CadInventarioInicialGeracao.Localizacao.value ){
			document.CadInventarioInicialGeracao.submit();
	}else{
		window.open(url,'item','status=no,scrollbars=yes,left=70,top=130,width='+largura+',height='+altura);
	}
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadInventarioInicialGeracao.php" method="post" name="CadInventarioInicialGeracao">
<br><br><br><br><br>
<table cellpadding="3" border="0" width="100%" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Inventário > Inicial > Geração
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
		<td width="100"></td>
		<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
			<table  border="0" cellspacing="0" cellpadding="3" width="100%" summary="">
				<tr>
					<td class="textonormal">
						<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" width="100%" bgcolor="#FFFFFF" summary="">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
									INVENTÁRIO - GERAÇÃO
								</td>
							</tr>
							<tr>
								<td class="textonormal">
									<p align="justify">
										Para efetuar a Geração do Inventário, informe a(s) Quantidade(s) e clique no botão "Salvar".
									</p>
								</td>
							</tr>
							<tr>
								<td>
									<table class="textonormal" border="0" align="left" width="100%" summary="">
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Almoxarifado*</td>
											<td class="textonormal">
												<?php
												# Mostra o(s) Almoxarifado(s) de Acordo com o Usuário Logado #
												$db  = Conexao();
												if( $_SESSION['_cgrempcodi_'] == 0 ){
														$sql  = "SELECT A.CALMPOCODI, A.EALMPODESC ";
														$sql .= "  FROM SFPC.TBALMOXARIFADOPORTAL A ";
														$sql .= "left JOIN SFPC.TBLOCALIZACAOMATERIAL LOC on loc.calmpocodi = a.calmpocodi ";
														$sql .= "left JOIN SFPC.TBAREAALMOXARIFADO area on area.calmpocodi = loc.calmpocodi and area.carloccodi = loc.carloccodi ";
														$sql .= "LEFT OUTER JOIN SFPC.TBINVENTARIOCONTAGEM INV ";
														$sql .= "ON LOC.CLOCMACODI = INV.CLOCMACODI ";
														$sql .= " WHERE A.FALMPOSITU = 'A'";
														$sql .= "   AND ( A.FALMPOINVE = 'N' OR A.FALMPOINVE IS NULL ) ";
														$sql .= " GROUP BY A.CALMPOCODI, A.EALMPODESC ";
														$sql .= "HAVING COUNT(INV.*)=0 ";
												} else {
														$sql = "SELECT A.CALMPOCODI, A.EALMPODESC, COUNT(INV.*) ";
														$sql .= "FROM SFPC.TBALMOXARIFADOPORTAL A  ";
														$sql .= "left JOIN SFPC.TBLOCALIZACAOMATERIAL LOC on loc.calmpocodi = a.calmpocodi ";
														$sql .= "left JOIN SFPC.TBAREAALMOXARIFADO area on area.calmpocodi = loc.calmpocodi and area.carloccodi = loc.carloccodi ";
														$sql .= "LEFT OUTER JOIN SFPC.TBINVENTARIOCONTAGEM INV ";
														$sql .= "ON LOC.CLOCMACODI = INV.CLOCMACODI, ";
														$sql .= "SFPC.TBALMOXARIFADOORGAO B  ";
														$sql .= "WHERE A.CALMPOCODI = B.CALMPOCODI  ";
														$sql .= " AND A.FALMPOSITU = 'A' ";
														$sql .= " AND ( A.FALMPOINVE = 'N' OR A.FALMPOINVE IS NULL )  ";
														$sql .= " AND B.CORGLICODI IN  ";
														$sql .= "     ( SELECT DISTINCT CEN.CORGLICODI  ";
														$sql .= "         FROM SFPC.TBCENTROCUSTOPORTAL CEN, SFPC.TBUSUARIOCENTROCUSTO USU  ";
														$sql .= "        WHERE USU.CCENPOSEQU = CEN.CCENPOSEQU AND USU.FUSUCCTIPO IN ('T','R')  ";
														$sql .= "          AND USU.CUSUPOCODI =  ".$_SESSION['_cusupocodi_']."  ";
														$sql .= "          AND CEN.FCENPOSITU <> 'I' ";
														$sql .= "AND CASE WHEN USU.FUSUCCTIPO = 'T' THEN B.CALMPOCODI = USU.CALMPOCODI ELSE CEN.FCENPOSITU <> 'I' END ";
														$sql .= "     )  ";
														$sql .= "GROUP BY A.CALMPOCODI, A.EALMPODESC ";
														$sql .= "HAVING COUNT(INV.*)=0";
												}
												$sql .= " ORDER BY A.EALMPODESC ";
												$res  = $db->query($sql);
												if( PEAR::isError($res) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Rows = $res->numRows();
														echo "<select name=\"Almoxarifado\" class=\"textonormal\" onchange=\"Localizacao[0].selected=true;submit()\">\n";
														if( $Rows == 0 ){
																echo "	<option value=\"\">Nenhum Almoxarifado Disponível para Inventário Inicial</option>\n";
														}else{
																echo "	<option value=\"\">Selecione um Almoxarifado...</option>\n";
																for( $i=0;$i< $Rows; $i++ ){
																		$Linha = $res->fetchRow();
																		$DescAlmoxarifado = $Linha[1];
																		if( $Linha[0] == $Almoxarifado ){
																				echo"<option value=\"$Linha[0]\" selected>$DescAlmoxarifado</option>\n";
																		}else{
																				echo"<option value=\"$Linha[0]\">$DescAlmoxarifado</option>\n";
																		}
																}
																
														}
														echo "</select>\n";
												}
												$db->disconnect();
												?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Localização*</td>
											<td class="textonormal">
												<?php
												echo "<select name=\"Localizacao\" class=\"textonormal\">\n";
												if (!$Almoxarifado){
														echo "	<option value=\"\">---</option>\n";
												} else {
														echo "	<option value=\"\">Selecione uma Localização...</option>\n";
														$db = Conexao();
														# Mostra as Localizações de acordo com o Almoxarifado #
														$sql  = "SELECT A.CLOCMACODI, A.FLOCMAEQUI, A.ALOCMANEQU, ";
														$sql .= "       A.ALOCMAPRAT, A.ALOCMACOLU, B.EARLOCDESC ";
														$sql .= "  FROM SFPC.TBLOCALIZACAOMATERIAL A, SFPC.TBAREAALMOXARIFADO B ";
														$sql .= " WHERE A.CALMPOCODI = $Almoxarifado ";
														$sql .= "   AND A.FLOCMASITU = 'A'";
														$sql .= "   AND A.CARLOCCODI = B.CARLOCCODI	";
														$sql .= " ORDER BY B.EARLOCDESC DESC, A.FLOCMAEQUI, A.ALOCMANEQU, ";
														$sql .= "       A.ALOCMAPRAT, A.ALOCMACOLU";
														$res  = $db->query($sql);
														if( PEAR::isError($res) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
																$Rows = $res->numRows();
																if( $Rows == 0 ){
																		echo "	<option value=\"\">NENHUMA LOCALIZAÇÃO CADASTRADA PARA ESTE ALMOXARIFADO</option>\n";
																}else{
																		$EquipamentoAntes = "";
																		$DescAreaAntes    = "";
																		for( $i=0;$i< $Rows; $i++ ){
																				$Linha = $res->fetchRow();
																				$CodEquipamento = $Linha[2];
																				if( $Linha[1] == "E" ){
																						$Equipamento = "ESTANTE";
																				}if( $Linha[1] == "A" ){
																						$Equipamento = "ARMÁRIO";
																				}if( $Linha[1] == "P" ){
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
																		$CarregaLocalizacao = "";
																}
														}
														$db->disconnect();
												 }
												echo "</select>\n";
												?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Data Base</td>
											<?php
											$DataBase = date("d/m/Y");
											?>
											<td class="textonormal"><input type="text" name="DataBase" value="<?php $DataBase?>" maxlength="10" size="10" class="textonormal" disabled>
											<input type="hidden" name="DataBase" value="<?php $DataBase?>">
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td class="textonormal" align="right">
									<input type="submit" name="Botao" value="Iniciar" class="botao">
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