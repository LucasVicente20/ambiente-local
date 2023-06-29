<?php
#--------------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadMovimentacaoSelecionar.php
# Autor:    Álvaro Faria
# Data:     06/10/2005
# Alterado: Álvaro Faria
# Data:     25/10/2005
# Alterado: Marcus Thiago
# Data:     04/01/2006
# Alterado: Álvaro Faria
# Data:     26/05/2006
# Alterado: Álvaro Faria
# Data:     01/08/2006
# Alterado: Álvaro Faria
# Data:     22/08/2006 - Proibição da manutenção de movimentações de tipo 25 e 28
# Alterado: Carlos Abreu
# Data:     15/12/2006 - Filtro no carregamento dos almoxarifados para bloquear quando Sob Inventário
# Alterado: Carlos Abreu
# Data:     27/12/2006 - Filtro no carregamento dos almoxarifados para liberar Almox. Educação quando Sob Inventário
# Alterado: Álvaro Faria
# Data:     03/01/2006 - Não exibição na lista de movimentações das movimentações de geração de inventário (33 e 34)
# Alterado: Carlos Abreu
# Data:     15/01/2007 - Apresentar todas as movimentações de alteração relacionadas (35,36) com a movimentação apresentada
# Alterado: Carlos Abreu
# Data:     04/06/2007 - Filtro no combo do almoxarifado para que quando usuario for do tipo
#                        atendimento apareça apenas o almox. que ele esteja relacionado
# Alterado: Álvaro Faria
# Data:     20/12/2007 - Correção do select de almoxarifado para bloquear almoxarifados em inventário ou no período de inventário
# Alterado: Rodrigo Melo
# Data:     09/01/2008 - Correção do select de almoxarifado, pois o mesmo não está liberando os almoxarifados a realizarem as
#                                 movimentações após a realização do inventário.
# Objetivo: Programa para permitir a movimentação de entrada e saída dos itens estocados
# OBS.:     Tabulação 2 espaços
#--------------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/CadRequisicaoConfirmarBaixa.php' );
AddMenuAcesso( '/estoques/CadMovimentacaoAlterar.php' );

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao            = $_POST['Botao'];
		$CodigoReduzido   = $_POST['CodigoReduzido'];
		$Almoxarifado     = $_POST['Almoxarifado'];
		$CarregaAlmoxarifado = $_POST['CarregaAlmoxarifado'];
		$Localizacao      = $_POST['Localizacao'];
		$CarregaLocalizacao = $_POST['CarregaLocalizacao'];
		$Sequencial       = $_POST['Sequencial'];
		$AnoRequisicao    = $_POST['AnoRequisicao'];
		$SeqRequisicao    = $_POST['SeqRequisicao'];
		$Situacao         = $_POST['Situacao'];
		$TipoUsuario      = $_POST['TipoUsuario'];
		$CentroCusto      = $_POST['CentroCusto'];
		$DataRequisicao   = $_POST['DataRequisicao'];
		$GrupoEmp         = $_POST['GrupoEmp'];
		$Usuario          = $_POST['Usuario'];
		$TipoSituacao     = $_POST['TipoSituacao'];
		$Unidade          = $_POST['Unidade'];
		$DescUnidade      = $_POST['DescUnidade'];
		$Material         = $_POST['Material'];
		$QtdSolicitada    = $_POST['QtdSolicitada'];
		$QtdAtendida      = $_POST['QtdAtendida'];
		$Ordem            = $_POST['Ordem'];
		$RowsGeral        = $_POST['RowsGeral'];
		$ValorUnitario    = $_POST['ValorUnitario'];
		$TipoMovimentacao = $_POST['TipoMovimentacao'];
		$Movimentacao     = $_POST['Movimentacao'];
		$MovNumero        = $_POST['MovNumero'];
		$Opcao            = $_POST['Opcao'];
		$DataIni          = $_POST['DataIni'];
		if($DataIni != "" ){ $DataIni = FormataData($DataIni); }
		$DataFim          = $_POST['DataFim'];
		if($DataFim != "" ){ $DataFim = FormataData($DataFim); }
}else{
		$CodigoReduzido   = $_GET['CodigoReduzido'];
		$Localizacao      = $_GET['Localizacao'];
		$Almoxarifado     = $_GET['Almoxarifado'];
		$Sequencial       = $_GET['Sequencial'];
		$AnoRequisicao    = $_GET['AnoRequisicao'];
		$TipoMovimentacao = $_GET['TipoMovimentacao'];
		$MovNumero        = $_GET['MovNumero'];
		$Mens             = $_GET['Mens'];
		$Tipo             = $_GET['Tipo'];
		$Mensagem         = $_GET['Mensagem'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Ano da Requisição Ano Atual #
$AnoRequisicao = date("Y");

if($Botao == "Voltar"){
		header("Location: CadMovimentacaoSelecionar.php");
		exit();
}

if($Botao == "Limpar"){
		$CodigoReduzido   = null;
		$Localizacao      = null;
		$Almoxarifado     = null;
		$Sequencial       = null;
		$AnoRequisicao    = null;
		$TipoMovimentacao = null;
		$DataIni          = null;
		$DataFim          = null;
		$MovNumero        = null;
}
if($Botao == "Pesquisar"){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( ($Localizacao == "") && ($CarregaLocalizacao == 'N') ){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Localização";
		}elseif($Localizacao == "") {
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadMovimentacaoSelecionar.Localizacao.focus();\" class=\"titulo2\">Localização</a>";
		}
		if($TipoMovimentacao == "" ){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript: document.CadMovimentacaoSelecionar.TipoMovimentacao.focus();\" class=\"titulo2\">Tipo de Movimentação</a>";
		}

		# Validação de período #
		$MensErro = ValidaPeriodo($DataIni,$DataFim,$Mens,"CadMovimentacaoSelecionar");
		if( $MensErro != "" ){ $Mensagem .= $MensErro; $Mens = 1; $Tipo = 2; }
}
?>
<html>
<?
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
function enviar(valor){
	document.CadMovimentacaoSelecionar.Botao.value = valor;
	document.CadMovimentacaoSelecionar.submit();
}

function remeter(valor){
	document.CadMovimentacaoSelecionar.Botao.value='Imprimir';
	document.CadMovimentacaoSelecionar.CodigoReduzido.value=valor;
	document.CadMovimentacaoSelecionar.submit();
}

<?php MenuAcesso(); ?>
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadMovimentacaoSelecionar.php" method="post" name="CadMovimentacaoSelecionar">
<br><br><br><br><br>
<table cellpadding="3" border="0" width="100%" summary="">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Movimentação > Manter
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if($Mens == 1){?>
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
			<table border="0" cellspacing="0" cellpadding="3" width="100%" summary="">
				<tr>
					<td class="textonormal">
						<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" width="100%" bgcolor="#FFFFFF" summary="">
							<tr>
								<td colspan="5" align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
									SELECIONAR - MOVIMENTAÇÃO
								</td>
							</tr>
							<tr>
								<td colspan="5" class="textonormal">
									<p align="justify">
										Para atualizar/excluir uma Movimentação já cadastrada, selecione a Movimentação através dos argumentos de pesquisa.
									</p>
								</td>
							</tr>
							<tr>
								<td colspan="5" >
									<table class="textonormal" border="0" align="left" width="100%" summary="">
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Almoxarifado</td>
											<td class="textonormal">
												<?php
												# Mostra o(s) Almoxarifado(s) de Acordo com o Usuário Logado #
												$db = Conexao();
												if($_SESSION['_cgrempcodi_'] == 0){
														$sql    = "SELECT A.CALMPOCODI, A.EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL A ";
														if($Almoxarifado){
																$sql   .= " WHERE A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A'";
														}
												}else{
														$sql    = "SELECT DISTINCT A.CALMPOCODI, A.EALMPODESC ";
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

														# Trecho com relação a data de fechamento #
														/*
														$sql .= "   AND CASE WHEN ('".date("Y-m-d")."'>='".$InventarioDataInicial."') THEN ";
														# Para que inventário seja feito no período determinado, sem passar da data final definida, descomentar a linha abaixo e comentar a posterior #
														# $sql .= "            (A.FALMPOINVE = 'N' OR A.FALMPOINVE IS NULL) AND D.TINVCOFECH >= '".$InventarioDataInicial."' AND D.TINVCOFECH <= '".$InventarioDataFinal."' ";
														$sql .= "            (A.FALMPOINVE = 'N' OR A.FALMPOINVE IS NULL) AND D.TINVCOFECH >= '".$InventarioDataInicial."' ";
														$sql .= "       ELSE ";
														$sql .= "            (A.FALMPOINVE = 'N' OR A.FALMPOINVE IS NULL) ";
														$sql .= "        END ";
														*/
														# Trecho com relação a data de hoje #
														$sql .= "   AND ( ";
														$sql .= "        TO_DATE('".date('Y-m-d')."','YYYY-MM-DD') < TO_DATE('".$InventarioDataInicial."','YYYY-MM-DD') ";
														$sql .= "        OR TO_DATE('".date('Y-m-d')."','YYYY-MM-DD') > TO_DATE('".$InventarioDataFinal."','YYYY-MM-DD') ";
														$sql .= "   ) ";
												}
												$sql .= " ORDER BY A.EALMPODESC ";
												$res  = $db->query($sql);
												if(db::isError($res)){
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
																echo "<select name=\"Almoxarifado\" class=\"textonormal\" onChange=\"submit();\">\n";
																echo "	<option value=\"\">Selecione um Almoxarifado...</option>\n";
																for($i=0;$i< $Rows; $i++){
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
									<?php if($Almoxarifado != ""){ ?>
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
														if( db::isError($res) ){
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
												}else{
														# Mostra as Localizações de acordo com o Almoxarifado #
														$sql    = "SELECT A.CLOCMACODI, A.FLOCMAEQUI, A.ALOCMANEQU, ";
														$sql   .= "       A.ALOCMAPRAT, A.ALOCMACOLU, B.EARLOCDESC ";
														$sql   .= "  FROM SFPC.TBLOCALIZACAOMATERIAL A, SFPC.TBAREAALMOXARIFADO B ";
														$sql   .= " WHERE A.CALMPOCODI = $Almoxarifado AND A.FLOCMASITU = 'A'";
														$sql   .= "   AND A.CARLOCCODI = B.CARLOCCODI	";
														$sql   .= " ORDER BY B.EARLOCDESC DESC, A.FLOCMAEQUI, A.ALOCMANEQU, ";
														$sql   .= "       A.ALOCMAPRAT, A.ALOCMACOLU";
														$res  = $db->query($sql);
														if(db::isError($res)){
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
																						echo " <option value=\"\">Selecione uma Localização...</option>\n";
																						$EquipamentoAntes = "";
																						$DescAreaAntes    = "";
																						for($i=0;$i< $Rows; $i++){
																								$Linha = $res->fetchRow();
																								$CodEquipamento = $Linha[2];
																								if($Linha[1] == "E"){
																										$Equipamento = "ESTANTE";
																								}
																								if($Linha[1] == "A"){
																										$Equipamento = "ARMÁRIO";
																								}
																								if($Linha[1] == "P"){
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
																								if($CodEquipamentoAntes != $CodEquipamento or $EquipamentoAntes != $Equipamento){
																										echo"<option value=\"\">$Edentecao $Equipamento - $NumeroEquip</option>\n";
																								}
																								if($Localizacao == $Linha[0]){
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
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo de Movimentação*</td>
											<td class="textonormal">
												<select name="TipoMovimentacao" class="textonormal">
													<?php
													if($TipoMovimentacao == ""){
															echo "<option value=\"\" selected>Selecione o Tipo de Movimentação...</option>";
															echo "<option value=\"E\" >ENTRADA</option>";
															echo "<option value=\"S\" >SAÍDA</option>";
													}elseif( $TipoMovimentacao == "E" ){
															echo "<option value=\"\" >Selecione o Tipo de Movimentação...</option>";
															echo "<option value=\"E\" selected>ENTRADA</option>";
															echo "<option value=\"S\" >SAÍDA</option>";
													}elseif( $TipoMovimentacao == "S" ){
															echo "<option value=\"\" >Selecione o Tipo de Movimentação...</option>";
															echo "<option value=\"E\" >ENTRADA</option>";
															echo "<option value=\"S\" selected>SAÍDA</option>";
													}
													?>
												</select>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Período*</td>
											<td class="textonormal">
												<?php
												$DataMes = DataMes();
												if($DataIni == ""){ $DataIni = $DataMes[0]; }
												if($DataFim == ""){ $DataFim = $DataMes[1]; }
												$URLIni = "../calendario.php?Formulario=CadMovimentacaoSelecionar&Campo=DataIni";
												$URLFim = "../calendario.php?Formulario=CadMovimentacaoSelecionar&Campo=DataFim";
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
								<td align="right" colspan="5" class="titulo3">
									<input type="hidden" name="Botao">
									<input type="hidden" name="CodigoReduzido">
									<input type="button" name="Pesquisar" value="Pesquisar" class="botao" onClick="javascript:enviar('Pesquisar');">
									<input type="button" name="Limpar" value="Limpar" class="botao" onClick="javascript:enviar('Limpar');">
								</td>
							</tr>
							<?php
									if($Botao == "Pesquisar" and $Mens == 0){
											$db   = Conexao();
											$sql  = "SELECT DISTINCT MOV.CTIPMVCODI, TIP.FTIPMVTIPO, TIP.ETIPMVDESC ";
											$sql .= "  FROM SFPC.TBTIPOMOVIMENTACAO TIP, SFPC.TBMOVIMENTACAOMATERIAL MOV ";
											$sql .= " WHERE TIP.CTIPMVCODI = MOV.CTIPMVCODI AND TIP.FTIPMVTIPO = '$TipoMovimentacao' ";
											# Caso deseje permitir a movimentação de acerto de inventário, retirar os códigos das movimentações 25 e 28 do "NOT IN" abaixo
											$sql .= "   AND TIP.CTIPMVCODI NOT IN(0,1,3,4,5,7,8,18,33,34, 6,9,11,29,  21,22,26,27,31, 25,28, 35,36) "; // Proibida manutenção das movimentações detes tipos por serem alteradas por outras páginas, depender de almoxarifado secundário, ter valor travado ou estarem proibidas.
											$sql .= "   AND MOV.DMOVMAMOVI >= '".DataInvertida($DataIni)."' ";
											$sql .= "   AND MOV.DMOVMAMOVI <= '".DataInvertida($DataFim)."' ";
											$sql .= "   AND MOV.CALMPOCODI = $Almoxarifado ";
											$sql .= "   AND (MOV.FMOVMACORR IS NULL OR MOV.FMOVMACORR = 'N') "; // Apenas movimentações simples ou as que envovem mais de um almoxarifado (12,13,15,30) mas que não foram concluídas
											$sql .= "   AND (MOV.FMOVMASITU IS NULL OR MOV.FMOVMASITU = 'A') "; // Apresentar só as movimentações ativas
											$sql .= " ORDER BY MOV.CTIPMVCODI ";
											$sqlgeral = $sql;
											$res      = $db->query($sqlgeral);
											$qtdres   = $res->numRows();
											if( db::isError($res) ){
													ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
											}else{
													# Cabeçalho da tabela #
													echo "<tr>\n";
													echo "	<td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"5\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
													echo "</tr>\n";
													if($qtdres > 0){
															while($row = $res->fetchRow()){
																	$Movimentacao     = $row[0];
																	$DescMovimentacao = $row[2];
																	# Busca pelas movimentações do tipo determinado por $Movimentacao
																	$sqlitens  = "SELECT MOV.CMATEPSEQU, MOV.CALMPOCODI, MOV.AMOVMAANOM, MOV.CMOVMACODI, ";
																	$sqlitens .= "       MAT.EMATEPDESC, MOV.DMOVMAMOVI, TIP.ETIPMVDESC, MOV.AMOVMAQTDM, ";
																	$sqlitens .= "       MOV.CMOVMACODT, MOV.CREQMASEQU ";
																	$sqlitens .= "  FROM SFPC.TBTIPOMOVIMENTACAO TIP, ";
																	$sqlitens .= "       SFPC.TBMATERIALPORTAL MAT, ";
																	$sqlitens .= "       SFPC.TBMOVIMENTACAOMATERIAL MOV ";
																	$sqlitens .= " WHERE MOV.CALMPOCODI = $Almoxarifado ";
																	$sqlitens .= "   AND MOV.CTIPMVCODI = $Movimentacao ";
																	$sqlitens .= "   AND MOV.DMOVMAMOVI >= '".DataInvertida($DataIni)."' ";
																	$sqlitens .= "   AND MOV.DMOVMAMOVI <= '".DataInvertida($DataFim)."' ";
																	$sqlitens .= "   AND MOV.CTIPMVCODI NOT IN(0,1,3,4,5,7,8,18,33,34, 6,9,11,29, 21,22,26,27,31, 25,28, 35,36)";
																	$sqlitens .= "   AND TIP.CTIPMVCODI = MOV.CTIPMVCODI ";
																	$sqlitens .= "   AND MOV.CMATEPSEQU = MAT.CMATEPSEQU ";
																	$sqlitens .= "   AND (MOV.FMOVMACORR IS NULL OR MOV.FMOVMACORR = 'N') "; // Apenas movimentações simples ou as que envovem mais de um almoxarifado (12,13,15,30) mas que não foram concluídas
																	$sqlitens .= "   AND (MOV.FMOVMASITU IS NULL OR MOV.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																	$sqlitens .= " ORDER BY MOV.CMOVMACODT ";
																	$resitens  = $db->query($sqlitens);
																	if(db::isError($resitens)){
																			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlitens");
																	}else{
																			while($rowitens = $resitens->fetchRow()){
																					$CodigoReduzido        = $rowitens[0];
																					$Almoxarifado          = $rowitens[1];
																					$AnoMovimentacao       = $rowitens[2];
																					$Sequencial            = $rowitens[3];
																					$MaterialDesc          = $rowitens[4];
																					$Data                  = databarra($rowitens[5]);
																					$DescricaoMovimentacao = $rowitens[6];
																					$Quantidade            = converte_quant($rowitens[7]);
																					$MovNumero             = $rowitens[8];
																					$SeqRequisicao         = $rowitens[9];
																					# Verifica a última situação da requisição, caso a movimentação seja do tipo ACERTO DA REQUISIÇÃO (Entrada e saída) #
																					if(  ($SeqRequisicao) && ( ($Movimentacao == 19) or ($Movimentacao == 20) )  ) {
																							$sqlsitreq  = " SELECT CTIPSRCODI FROM SFPC.TBSITUACAOREQUISICAO ";
																							$sqlsitreq .= "  WHERE TSITREULAT IN ";
																							$sqlsitreq .= "                     (SELECT MAX(TSITREULAT) FROM SFPC.TBSITUACAOREQUISICAO ";
																							$sqlsitreq .= "                       WHERE CREQMASEQU = $SeqRequisicao) ";
																							$ressitreq  = $db->query($sqlsitreq);
																							if(db::isError($ressitreq)){
																									ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlsitreq");
																							}else{
																									$rowsitreq   = $ressitreq->fetchRow();
																									$ReqSituacao = $rowsitreq[0];
																							}
																					}else{
																							$ReqSituacao = null;
																					}

																					# Entra neste IF, em duas situações, primeira, caso a movimentação seja do tipo 19 ou 20, mas a situação da requisição não seja 5 (Baixada), nem 6 (Cancelada), segunda, se a movimentação não for nem 19, nem 20, então ReqSituacao vai ser direto null, sem precisar ir ao banco no if anterior #
																					if( ($ReqSituacao != 5) and ($ReqSituacao != 6) ){
																							$Ocorrencia = 1;
																							# Exibe o título da movimentação e o cabeçalho das colunas, se a próxima movimentação a ser exibida for diferente da anterior
																							if($Movimentacao != $MovimentacaoExibida) {
																									$MovimentacaoExibida = $Movimentacao;
																									echo "<tr>\n";
																									echo "	<td align=\"center\" bgcolor=\"#BFDAF2\" colspan=\"5\" class=\"titulo3\">".$DescMovimentacao."</td>\n";
																									echo "</tr>\n";
																									echo "<tr>\n";
																									echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"15%\" align=\"CENTER\">CÓD MOV</td>\n";
																									echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"15%\" align=\"CENTER\">DATA</td>\n";
																									echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"55%\">DESCRIÇÃO DO MATERIAL</td>\n";
																									echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"15%\" align=\"center\">QUANTIDADE</td>\n";
																									echo "</tr>\n";
																							}

																							# Exibe o conteúdo das movimentações
																							echo "<tr>\n";
																							echo "  <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\" width=\"15%\">$MovNumero</td>\n";
																							echo "  <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\" width=\"15%\">$Data</td>\n";
																							echo "  <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"55%\">\n";
																							$Url = "CadMovimentacaoAlterar.php?Almoxarifado=$Almoxarifado&AnoMovimentacao=$AnoMovimentacao&Sequencial=$Sequencial&Localizacao=$Localizacao";
																							if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																							echo "    <a href=\"$Url\"> <input type=\"hidden\" name=\"Material\" value=\"$rowitens[0]\"> <font color=\"#000000\">$MaterialDesc</font></a>";
																							echo "  </td>\n";
																							echo "  <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\" width=\"15%\">$Quantidade</td>\n";
																							echo "</tr>\n";
																					}
																			}
																	}
															}
															# Apesar de ter encontrado movimentações, todas apontam para requisições baixadas ou canceladas, não podendo ser exibidas
															if(!$Ocorrencia){
																	echo "<tr>\n";
																	echo "	<td class=\"textonormal\" colspan=\"5\" >\n";
																	echo "		Pesquisa sem Ocorrências.\n";
																	echo "	</td>\n";
																	echo "</tr>\n";
															}
													}else{
															echo "<tr>\n";
															echo "	<td class=\"textonormal\" colspan=\"5\" >\n";
															echo "		Pesquisa sem Ocorrências.\n";
															echo "	</td>\n";
															echo "</tr>\n";
													}
											}
									$db->disconnect();
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
