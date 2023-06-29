<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadRequisicaoBaixaSelecionar.php
# Objetivo: Programa que Seleciona a Requisição de Material
# Autor:    Altamiro Pedrosa
# Data:     17/08/2005
# Alterado: Marcus Thiago
# Data:     12/01/2006
# Alterado: Álvaro Faria
# Data:     10/03/2006
# Alterado: Álvaro Faria
# Data:     24/11/2006 - Suporte ao include da rotina de Custo/Contabilidade
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
# OBS.:     Tabulação 2 espaços
# ------------------------------------------------------------------------
# Alterado: José Almir <jose.almir@pitang.com>
# Data:		09/12/2014 - CR 213 - Alterar as funcionalidades "Incluir / Manter Nota Fiscal" e 
#								  "Incluir / Manter / Atender / Baixa Requisição" para liberar movimentações dos 
#								  usuários dos órgãos nos períodos cadastrados na nova funcionalidade de 
#								  liberação de movimentação.
# ------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/CadRequisicaoBaixa.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
		$Botao        = $_POST['Botao'];
		$Situacao     = $_POST['Situacao'];
		$Todas        = $_POST['Todas'];
		$DataIni      = $_POST['DataIni'];
		if( $DataIni != "" ){ $DataIni = FormataData($DataIni); }
		$DataFim      = $_POST['DataFim'];
		if( $DataFim != "" ){ $DataFim = FormataData($DataFim); }
		$Programa	    = $_POST['Programa'];
		$Almoxarifado = $_POST['Almoxarifado'];
		$CarregaAlmoxarifado = $_POST['CarregaAlmoxarifado'];
		$Mensagem     = $_POST['Mensagem'];
		$Mens         = $_POST['Mens'];
		$Tipo         = $_POST['Tipo'];
}else{
		$Mensagem = urldecode($_GET['Mensagem']);
		$Mens     = $_GET['Mens'];
		$Tipo     = $_GET['Tipo'];
		$Botao    = $_GET['Botao'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Ano do Exercicio #
$AnoExercicio = date("Y");

if( $Botao == "Limpar" ){
		header("location: CadRequisicaoBaixaSelecionar.php");
		exit;
}elseif( $Botao == "Pesquisar" ){
		# Critica dos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( ($Almoxarifado == "") && ($CarregaAlmoxarifado == 'N') ){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$FlgSistema = 1;
				$Mensagem .= "Almoxarifado";
		} elseif ($Almoxarifado == "") {
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$FlgSistema = 1;
				$Mensagem .= "<a href=\"javascript:document.CadRequisicaoBaixaSelecionar.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		}
		$MensErro = ValidaPeriodo($DataIni,$DataFim,$Mens,"CadRequisicaoBaixaSelecionar");
		if( $MensErro != "" ){ $Mensagem .= $MensErro; $Mens = 1; $Tipo = 2; $FlgSistema = 1; }
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
	document.CadRequisicaoBaixaSelecionar.Botao.value = valor;
	document.CadRequisicaoBaixaSelecionar.submit();
}
<?php MenuAcesso(); ?>
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>+
<form action="CadRequisicaoBaixaSelecionar.php" method="post" name="CadRequisicaoBaixaSelecionar">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Requisição > Baixa
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
							BAIXA - REQUISIÇÃO DE MATERIAL
					</td>
				</tr>
				<tr>
					<td class="textonormal" colspan="4">
						 <p align="justify">
						 Para dar baixa em uma Requisição de Material cadastrada, selecione a Requisição desejada e clique no botão "Selecionar".
						 </p>
					</td>
				</tr>
				<tr>
					<td colspan="4">
						<table border="0" width="100%" summary="">
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Almoxarifado</td>
								<td class="textonormal">
									<?php
									# Mostra o(s) Almoxarifado(s) de Acordo com o Usuário Logado #
									$db  = Conexao();
									if( $_SESSION['_cgrempcodi_'] == 0 ){
											$sql    = "SELECT A.CALMPOCODI, A.EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL A ";
											if ($Almoxarifado) {
													$sql   .= " WHERE A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A'";
											}
									} else {
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

											/*
											# Trecho com relação a data de fechamento #
											$sql .= "   AND CASE WHEN ('".date("Y-m-d")."'>='".$InventarioDataInicial."') THEN ";
											# Para que inventário seja feito no período determinado, sem passar da data final definida, descomentar a linha abaixo e comentar a posterior #
											# $sql .= "            (A.FALMPOINVE = 'N' OR A.FALMPOINVE IS NULL) AND D.TINVCOFECH >= '".$InventarioDataInicial."' AND D.TINVCOFECH <= '".$InventarioDataFinal."' ";
											$sql .= "            (A.FALMPOINVE = 'N' OR A.FALMPOINVE IS NULL) AND D.TINVCOFECH >= '".$InventarioDataInicial."' ";
											$sql .= "       ELSE ";
											$sql .= "            (A.FALMPOINVE = 'N' OR A.FALMPOINVE IS NULL) ";
											$sql .= "        END ";
											# Trecho com relação a data de hoje #
											$sql .= "   AND ( ";
											$sql .= "        TO_DATE('".date('Y-m-d')."','YYYY-MM-DD') < TO_DATE('".$InventarioDataInicial."','YYYY-MM-DD') ";
											$sql .= "        OR TO_DATE('".date('Y-m-d')."','YYYY-MM-DD') > TO_DATE('".$InventarioDataFinal."','YYYY-MM-DD') ";
											$sql .= "   ) ";*/
											
											// [CUSTOMIZAÇÃO] - Nova condição para liberar órgãos dentro do período de bloqueio do sistema.
											//					As variáveis $InventarioDataInicial e $InventarioDataFinal são alimentadas
											//					com os valores definidos nos parâmetros do sistema conforme CR 212.
											$sql .= "   AND (( TRUE ";
											
											$sql .= "   AND ( ";
											$sql .= "        TO_DATE('".date('Y-m-d')."','YYYY-MM-DD') < TO_DATE('".$InventarioDataInicial."','YYYY-MM-DD') ";
											$sql .= "        OR TO_DATE('".date('Y-m-d')."','YYYY-MM-DD') > TO_DATE('".$InventarioDataFinal."','YYYY-MM-DD')";
											$sql .= "   )";
											$sql .= "   )";
											
											$dataAtual = date('Y-m-d') . ' 23:59:59';
											
											$sql .= "				OR B.CORGLICODI IN ( ";
											$sql .= " SELECT CORGLICODI ";
											$sql .= " FROM SFPC.TBLIBERACAOMOVIMENTACAO ";
											$sql .= " WHERE ";
											$sql .= " 	TLIBMODINI BETWEEN '" . $InventarioDataInicial . "' AND '" . $dataAtual . "' ";
											$sql .= " 	OR ";
											$sql .= " 	TLIBMODFIN BETWEEN '" . $dataAtual . "' AND '" . $InventarioDataFinal . "' ";
											$sql .= " GROUP BY CORGLICODI ";
											$sql .= " ORDER BY CORGLICODI ";
											$sql .= " ) ";
											
											$sql .= "   ) ";
											// [/CUSTOMIZAÇÃO]
									}
									
									$sql .= " ORDER BY A.EALMPODESC ";
									
// 									echo $sql;
// 									exit;
									
									$res  = $db->query($sql);
									if( PEAR::isError($res) ){
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
															if( $Linha[0] == $Almoxarifado ){
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
								<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Situação*</td>
								<td class="textonormal">
									<select name="Situacao" class="textonormal">
										<option value="">Selecione uma Situação...</option>
										<?php
										# Mostra os órgãos cadastrados #
										$db     = Conexao();
										$sql    = "SELECT CTIPSRCODI, ETIPSRDESC FROM SFPC.TBTIPOSITUACAOREQUISICAO WHERE CTIPSRCODI IN(3,4)";
										$sql   .= " ORDER BY ETIPSRDESC";
										$result = $db->query($sql);
										if( PEAR::isError($result) ){
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												while( $Linha = $result->fetchRow() ){
														if( $Situacao == $Linha[0] ){
																echo"<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
														}else{
																echo"<option value=\"$Linha[0]\">$Linha[1]</option>\n";
														}
												}
										}
										$db->disconnect();
										?>
									</select>
									<input type="checkbox" name="Todas" value="S" onClick="javascript:enviar('Pesquisar');">Todas
								</td>
							</tr>
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Período*</td>
								<td class="textonormal">
									<?php
									$DataMes = DataMes();
									if( $DataIni == "" ){ $DataIni = $DataMes[0]; }
									if( $DataFim == "" ){ $DataFim = $DataMes[1]; }
									$URLIni = "../calendario.php?Formulario=CadRequisicaoBaixaSelecionar&Campo=DataIni";
									$URLFim = "../calendario.php?Formulario=CadRequisicaoBaixaSelecionar&Campo=DataFim";
									?>
									<input type="text" name="DataIni" size="10" maxlength="10" value="<?php echo $DataIni;?>" class="textonormal">
									<a href="javascript:janela('<?php echo $URLIni ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
									&nbsp;a&nbsp;
									<input type="text" name="DataFim" size="10" maxlength="10" value="<?php echo $DataFim;?>" class="textonormal">
									<a href="javascript:janela('<?php echo $URLFim ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td class="textonormal" align="right" colspan="4">
						<input type="hidden" name="Programa" value="<?php echo $Programa; ?>">
						<?php if ($FlgSistema == "") { ?>
								<input type="hidden" name="Mens" value="<?php echo $Mens; ?>">
								<input type="hidden" name="Tipo" value="<?php echo $Tipo; ?>">
								<input type="hidden" name="Mensagem" value="<?php echo $Mensagem; ?>">
						<?php } ?>
						<input type="button" name="Pesquisar" value="Pesquisar" class="botao" onClick="javascript:enviar('Pesquisar')">
						<input type="button" name="Limpar" value="Limpar" class="botao" onClick="javascript:enviar('Limpar')">
						<input type="hidden" name="Botao" value="">
					</td>
				</tr>
				<?php
				if( ($Botao == "Pesquisar" and $Mens == 0) or ($Botao == "Retornar" and $Mens == 1) ){
						if( $Botao == "Retornar" ){ $Situacao == ""; }

						# Busca os Dados da Tabela de Requisição de Material de Acordo com o Argumento da Pesquisa #
						$db	  = Conexao();
						$sql  = "SELECT A.CREQMASEQU, A.AREQMAANOR, A.CREQMACODI, A.FREQMATIPO, ";
						$sql .= "       A.DREQMADATA, C.CTIPSRCODI, C.ETIPSRDESC, D.ECENPODESC, ";
						$sql .= "       E.EORGLIDESC, D.ECENPODETA ";
						$sql .= "  FROM SFPC.TBREQUISICAOMATERIAL A, SFPC.TBSITUACAOREQUISICAO B, SFPC.TBTIPOSITUACAOREQUISICAO C, ";
						$sql .= "       SFPC.TBCENTROCUSTOPORTAL D, SFPC.TBORGAOLICITANTE E ";
						$sql .= " WHERE A.CREQMASEQU = B.CREQMASEQU AND B.CTIPSRCODI = C.CTIPSRCODI ";
						$sql .= "   AND A.CORGLICODI = D.CORGLICODI AND D.CORGLICODI = E.CORGLICODI ";
						$sql .= "   AND A.CCENPOSEQU = D.CCENPOSEQU ";
						$sql .= "   AND A.CALMPOCODI = $Almoxarifado ";
						$sql .= "   AND A.FREQMATIPO = 'R' AND B.CTIPSRCODI IN (3,4) ";
						$sql .= "   AND D.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos

						if( $Situacao != "" and $Todas == "" ){
								$sql .= "AND B.CTIPSRCODI = $Situacao ";
						}
						$sql .= "   AND B.TSITREULAT IN ";
						$sql .= "       (SELECT MAX(TSITREULAT) FROM SFPC.TBSITUACAOREQUISICAO SIT";
						$sql .= "           WHERE SIT.CREQMASEQU = A.CREQMASEQU) ";

						if( $DataIni != "" and $DataFim != "" ){
								$sql .= "AND A.DREQMADATA >= '".DataInvertida($DataIni)."' AND A.DREQMADATA <= '".DataInvertida($DataFim)."' ";
						}
						$sql .= " ORDER BY E.EORGLIDESC, D.ECENPODESC, A.CREQMASEQU, A.AREQMAANOR DESC, C.ETIPSRDESC ";
						$res  = $db->query($sql);
						if( PEAR::isError($res) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$Qtd = $res->numRows();
								echo "<tr>\n";
								echo "	<td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"4\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
								echo "</tr>\n";
								if( $Qtd > 0 ){
										$DescOrgaoAntes  = "";
										$DescCentroAntes = "";
										while( $Linha = $res->fetchRow() ){
												$SeqRequisicao  = $Linha[0];
												$AnoRequisicao  = $Linha[1];
												$Requisicao     = $Linha[2];
												if($Linha[3] == "R"){
														$TipoRequisicao = "REQUISITANTE";
												}elseif( $Linha[3] == "S" ){
														$TipoRequisicao = "SUBALMOXARIFADO";
												}
												$Data           = DataBarra($Linha[4]);
												$TipoSituacao   = $Linha[5];
												$DescSituacao   = $Linha[6];
												$DescCentro     = $Linha[7];
												$DescOrgao      = $Linha[8];
												$Detalhamento   = $Linha[9];
												if($DescOrgaoAntes != $DescOrgao){
														echo "<tr>\n";
														echo "	<td align=\"center\" bgcolor=\"#BFDAF2\" colspan=\"4\" class=\"titulo3\">$DescOrgao</td>\n";
														echo "</tr>\n";
												}
												if($DescCentroAntes != $DescCentro){
														echo "<tr>\n";
														echo "	<td align=\"center\" bgcolor=\"#DDECF9\" colspan=\"4\" class=\"titulo3\">$DescCentro</td>\n";
														echo "</tr>\n";
														echo "<tr>\n";
														echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\">REQUISIÇÃO</td>\n";
														echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\">DETALHAMENTO</td>\n";
														echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\">DATA</td>\n";
														echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\">SITUAÇÃO</td>\n";
														echo "</tr>\n";
												}
												echo "<tr>\n";
												echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">";
												$Url = "CadRequisicaoBaixa.php?SeqRequisicao=$SeqRequisicao&AnoRequisicao=$AnoRequisicao&Almoxarifado=$Almoxarifado";
												if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
												echo "	<a href=\"$Url\"><font color=\"#000000\">".substr($Requisicao+100000,1)."/$AnoRequisicao</font></a>";
												echo "	</td>\n";
												echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Detalhamento</td>\n";
												echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Data</td>\n";
												echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$DescSituacao</td>\n";
												echo "</tr>\n";
												$DescOrgaoAntes  = $DescOrgao;
												$DescCentroAntes = $DescCentro;
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
