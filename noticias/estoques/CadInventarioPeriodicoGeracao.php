<?php
#------------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadInventarioPeriodicoGeracao.php
# Objetivo: Programa de Geração(Inclusão/Alteração) de Inventário do Estoque
# Autor:    Carlos Abreu
# Data:     16/11/2006
# Alterado: Carlos Abreu
# Data:     15/05/2007 - Ajuste para evitar erro quando trabalha com mais de uma localizacao
# Alterado: Carlos Abreu
# Data:     04/06/2007 - Filtro no combo do almoxarifado para que quando usuario for do tipo
# Alterado: Rodrigo Melo
# Data:     11/08/2009 - Alteração para permitir que os almoxarifado que não realizaram movimentação no ano corrente possam abrir o inventário periódico.
#                        atendimento apareça apenas o almox. que ele esteja relacionado
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
		$sql .= " FROM SFPC.TBINVENTARIOCONTAGEM ";
		$sql .= "WHERE CLOCMACODI = $Localizacao ";
		$sql .= "  AND AINVCOANOB = $AnoExercicio ";
		$res  = $db->query($sql);
		if( db::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				$db->disconnect();
				exit;
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
				$Mensagem .= "<a href=\"javascript:document.CadInventarioPeriodicoGeracao.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		}
		if ($Localizacao == "") {
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadInventarioPeriodicoGeracao.Localizacao.focus();\" class=\"titulo2\">Localização</a>";
		}
		//$MensErro = ValidaData($DataBase);
		//if( $MensErro != "" ){
		//		if( $Mens == 1 ){ $Mensagem .= ", "; }
		//		$Mens      = 1;
		//		$Tipo      = 2;
		//		$Mensagem .= "<a href=\"javascript:document.CadInventarioPeriodicoGeracao.DataBase.focus();\" class=\"titulo2\">Data Base Válida</a>";
		//}
		if( $Mens == 0 ){
				$db = Conexao();
				$sql  = "SELECT A.AINVCOANOB, MAX(A.AINVCOSEQU) AS AINVCOSEQU ";
				$sql .= "  FROM SFPC.TBINVENTARIOCONTAGEM A ";
				$sql .= " WHERE A.CLOCMACODI=$Localizacao ";
				$sql .= "   AND A.FINVCOFECH = 'S' ";
				$sql .= "   AND A.AINVCOANOB=( ";
				$sql .= "       SELECT MAX(AINVCOANOB) ";
				$sql .= "         FROM SFPC.TBINVENTARIOCONTAGEM ";
				$sql .= "        WHERE CLOCMACODI=$Localizacao ";
				$sql .= "       ) ";
				$sql .= " GROUP BY A.AINVCOANOB";
				//var_dump($sql);die;
				$res  = $db->query($sql);
				if(db::isError($res)){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						$db->disconnect();
						exit;
				}else{
						$Rows = $res->numRows();
						if( $Rows != 0 ){
								$Linha = $res->fetchRow();
						}
						$Ano        = $Linha[0];
						if (!$Ano){$Ano=date("Y");}
						$Sequencial = $Linha[1]+1;
						$datahora = date("Y-m-d H:i:s");
						$db->query('BEGIN');
						$sql  = "INSERT INTO SFPC.TBINVENTARIOCONTAGEM ";
						$sql .= "       (CLOCMACODI, AINVCOANOB, AINVCOSEQU, TINVCOFECH, TINVCOBASE, ";
						$sql .= "        CGREMPCODI, CUSUPOCODI, TINVCOULAT";
						$sql .= "       ) VALUES (";
						$sql .= "        $Localizacao, ".date("Y").", $Sequencial, NULL, '$datahora',";
						$sql .= "        ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].", '$datahora'";
						$sql .= "       )";
						//var_dump($sql);die;
						$res  = $db->query($sql);
						
						if( db::isError($res) ){
								$res  = $db->query('ROLLBACK');
								$res  = $db->query('END');
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								$db->disconnect();
								exit;
						}
						$sql  = "UPDATE SFPC.TBALMOXARIFADOPORTAL ";
						$sql .= "   SET FALMPOINVE = 'S', TALMPOULAT = '$datahora' ";
						$sql .= " WHERE CALMPOCODI = $Almoxarifado";
						//var_dump($sql);die;
						$res  = $db->query($sql);
						if( db::isError($res) ){
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
						$Mensagem       = "Geração do Inventário Periódico Efetuada com Sucesso";
						$Almoxarifado	  = "";
				}
				$db->disconnect();
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
	document.CadInventarioPeriodicoGeracao.Botao.value = valor;
	document.CadInventarioPeriodicoGeracao.submit();
}
function AbreJanela(url,largura,altura) {
	window.open(url,'detalhe','status=no,scrollbars=yes,left=70,top=130,width='+largura+',height='+altura);
}
function AbreJanelaItem(url,largura,altura) {
	if( ! document.CadInventarioPeriodicoGeracao.Almoxarifado.value ){
			document.CadInventarioPeriodicoGeracao.submit();
	}else	if( ! document.CadInventarioPeriodicoGeracao.Localizacao.value ){
			document.CadInventarioPeriodicoGeracao.submit();
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
<form action="CadInventarioPeriodicoGeracao.php" method="post" name="CadInventarioPeriodicoGeracao">
<br><br><br><br><br>
<table cellpadding="3" border="0" width="100%" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Inventário > Periódico > Geração
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
										Para efetuar a Geração do Inventário, Informe o Almoxarifado, Localização e clique no botão "Iniciar".
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
														$sql .= " WHERE A.FALMPOSITU = 'A'";
														$sql .= "   AND A.FALMPOINVE = 'N'";
												} else {
														$sql = "SELECT A.CALMPOCODI, A.EALMPODESC
																		  FROM (
																		SELECT A.CALMPOCODI, A.EALMPODESC, COUNT(C.*) AS QTDMOV
																		  FROM SFPC.TBALMOXARIFADOPORTAL A
																		  LEFT OUTER JOIN SFPC.TBMOVIMENTACAOMATERIAL C
																		    ON A.CALMPOCODI = C.CALMPOCODI,
																		       SFPC.TBALMOXARIFADOORGAO B
																		 WHERE A.CALMPOCODI = B.CALMPOCODI
																		   AND A.FALMPOSITU = 'A'
																		   AND ( A.FALMPOINVE = 'N' OR A.FALMPOINVE IS NULL )
																		   AND B.CORGLICODI IN
																		       ( SELECT DISTINCT CEN.CORGLICODI
																		           FROM SFPC.TBCENTROCUSTOPORTAL CEN, SFPC.TBUSUARIOCENTROCUSTO USU
																		          WHERE USU.CCENPOSEQU = CEN.CCENPOSEQU AND USU.FUSUCCTIPO IN ('T','R')
																		            AND USU.CUSUPOCODI =  ".$_SESSION['_cusupocodi_']."
																		            AND CEN.FCENPOSITU <> 'I'

																		            AND CASE WHEN USU.FUSUCCTIPO = 'T' THEN B.CALMPOCODI = USU.CALMPOCODI ELSE CEN.FCENPOSITU <> 'I' END

																		       )
																		 GROUP BY A.CALMPOCODI, A.EALMPODESC
																		--HAVING COUNT(C.*)<>0 --REMOVIDO PARA QUE OS ALMOXARIFADOS QUE NÃO TIVERAM MOVIMENTAÇÃO POSSAM GERAR O INVENTÁRIO PERIODICO
																					 ) AS A
																			LEFT OUTER JOIN SFPC.TBLOCALIZACAOMATERIAL D
																	  		ON A.CALMPOCODI = D.CALMPOCODI
																			LEFT OUTER JOIN SFPC.TBINVENTARIOREGISTRO E
																	  		ON D.CLOCMACODI = E.CLOCMACODI
																		 GROUP BY A.CALMPOCODI, A.EALMPODESC, A.QTDMOV
																	    --HAVING COUNT(E.*) <= 0";
												}
												$sql .= " ORDER BY A.EALMPODESC ";
												var_dump($sql);
												$res  = $db->query($sql);
												if( db::isError($res) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														$db->disconnect();
														exit;
												}else{
														$Rows = $res->numRows();
														echo "<select name=\"Almoxarifado\" class=\"textonormal\" onchange=\"Localizacao[0].selected=true;submit()\">\n";
														if( $Rows == 0 ){
																echo "	<option value=\"\">Nenhum Almoxarifado Disponível para Inventário</option>\n";
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
														if( db::isError($res) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																$db->disconnect();
																exit;
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
											<td class="textonormal"><input type="text" name="DataBase" value="<?=$DataBase?>" maxlength="10" size="10" class="textonormal" disabled>
											<input type="hidden" name="DataBase" value="<?=$DataBase?>">
										</tr>
										<!--<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Data Base*</td>
											<?php
											if($DataBase == ""){ $DataBase = date("d/m/Y"); }
											$URL = "../calendario.php?Formulario=CadInventarioPeriodicoGeracao&Campo=DataBase";
											?>
											<td class="textonormal"><input type="text" name="DataBase" value="<?=$DataBase?>" maxlength="10" size="10" class="textonormal">
											<a href="javascript:janela('<?php echo $URL ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a></td>
										</tr>-->
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
