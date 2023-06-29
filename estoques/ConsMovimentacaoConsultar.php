<?php
#----------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsMovimentacaoConsultar.php
# Autor:    Wagner Barros
# Data:     19/07/2006
# Alterado: Álvaro Faria
# Data:     17/08/2006
# Alterado: Álvaro Faria
# Data:     21/08/2006 - Correções para exibição de movimentações antigas 
#           (quando não gravavam correspondência)
# Alterado: Álvaro Faria
# Data:     18/09/2006
# Alterado: Carlos Abreu
# Data:     16/01/2007
# Objetivo: Remover atribuição de date(Y) para AnoMovimentacao
# OBS.:     Tabulação 2 espaços
#----------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao            = $_POST['Botao'];
		$Sequencial       = $_POST['Sequencial'];
		$AnoMovimentacao  = $_POST['AnoMovimentacao'];
		$Movimentacao     = $_POST['Movimentacao'];		
		$Almoxarifado     = $_POST['Almoxarifado'];
		$Localizacao      = $_POST['Localizacao'];
}else{
		$Sequencial       = $_GET['Sequencial'];
		$AnoMovimentacao  = $_GET['AnoMovimentacao'];
		$Almoxarifado     = $_GET['Almoxarifado'];
		$Localizacao      = $_GET['Localizacao'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Data da Movimentação Atual #
$DataMovimentacao = date("Y-m-d");

# DAta e Hora da Movimentação Atual #
$Datahora = Date("Y-M-D H:I:S");

# Grupo e Código do Usuário #
$UsoGrupo = $_SESSION['_cgrempcodi_'];
$UsoCodi  = $_SESSION['_cusupocodi_'];

if( $Botao == null ){
		$db     = Conexao();
		# Resgata os dados da Movimentação #
		$sql    = "SELECT TIP.FTIPMVTIPO, MOV.CTIPMVCODI, TIP.ETIPMVDESC, MAT.EMATEPDESC, ";
		$sql   .= "       UND.EUNIDMSIGL, MOV.AMOVMAQTDM, MOV.VMOVMAVALO, MOV.CMATEPSEQU, ";
		$sql   .= "       MOV.CMOVMACODI, MOV.CMOVMACODT, MOV.AMOVMAMATR, MOV.NMOVMARESP, ";
		$sql   .= "       MOV.CREQMASEQU, MOV.EMOVMAOBSE, MOV.DMOVMAMOVI, MOV.CENTNFCODI, ";
		$sql   .= "       MOV.AENTNFANOE, MOV.CALMPOCOD1, MOV.CMOVMACOD1, MOV.AMOVMAANO1, ";
		$sql   .= "  		  MOV.CMATEPSEQ1, MOV.AMOVMAQCOR, MOV.CUSUPOCODI, MOV.TMOVMAULAT, ";
		$sql   .= "       MOV.FMOVMACORR, MOV.AMOVMAANOM ";	
		$sql   .= "  FROM SFPC.TBMATERIALPORTAL MAT, SFPC.TBMOVIMENTACAOMATERIAL MOV, ";
		$sql   .= "       SFPC.TBTIPOMOVIMENTACAO TIP, SFPC.TBUNIDADEDEMEDIDA UND ";
		$sql   .= " WHERE MAT.CMATEPSEQU = MOV.CMATEPSEQU "; // Material: SFPC.TBMATERIALPORTAL = SFPC.TBMOVIMENTACAOMATERIAL
		$sql   .= "   AND MOV.CTIPMVCODI = TIP.CTIPMVCODI "; // Tipo de movimentação: SFPC.TBMOVIMENTACAOMATERIAL = SFPC.TBTIPOMOVIMENTACAO
		$sql   .= "   AND MAT.CUNIDMCODI = UND.CUNIDMCODI "; // Unidade de medida: SFPC.TBMATERIALPORTAL = SFPC.TBUNIDADEDEMEDIDA
		$sql   .= "	  AND MOV.CALMPOCODI = $Almoxarifado ";
		$sql   .= "   AND MOV.AMOVMAANOM = $AnoMovimentacao ";
		$sql   .= "   AND MOV.CMOVMACODI = $Sequencial";
		$sql   .= "	  AND (MOV.FMOVMASITU IS NULL OR MOV.FMOVMASITU = 'A') ";
		$res    = $db->query($sql);
		if( PEAR::isError($res) ){     
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$qtdres = $res->numRows();
				if( $qtdres > 0 ){
						$row = $res->fetchRow();
						$TipoMovimentacao = $row[0];
						$Movimentacao     = $row[1];
						$DescMovimentacao = $row[2];
						$DescMaterial     = $row[3];
						$UnidSigl         = $row[4];
						$QtdMovimentada   = str_replace(".",",",$row[5]);
						$ValorMovimentado = str_replace(".",",",$row[6]);
						$Material         = $row[7];
						$Sequencial       = $row[8];
						$MovNumero        = $row[9];
						$Matricula        = $row[10];
						$Responsavel      = $row[11];
						$Observacao       = $row[13];
						$DataMovimento    = $row[14];
						$NumeroNota       = $row[15];
						$AnoNota          = $row[16];
						$AlmoxCorresp     = $row[17];
						$NumeroMovCorresp = $row[18];
						$NumeroMovAno     = $row[19];
						$MaterialCorresp  = $row[20];
						$QtdeCorresp      = str_replace(".",",",$row[21]);
						$CodUsuarioResp   = $row[22];
						$DataHoraAlter    = $row[23];
						$MovConcluida     = $row[24];
						$MovAno           = $row[25];
						if( $Movimentacao == 2 or $Movimentacao == 19 or $Movimentacao == 20 ){
								$Requisicao = $row[12];
						}
				}
		}
		$db->disconnect();
}
if($Botao == "Voltar"){
		header("Location: ConsMovimentacaoSelecionar.php");
		exit;
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
	document.ConsMovimentacaoConsultar.Botao.value = valor;
	document.ConsMovimentacaoConsultar.submit();
}
<?php MenuAcesso(); ?>
function ncaracteresO(valor){
	document.ConsMovimentacaoConsultar.NCaracteresO.value = '' +  document.ConsMovimentacaoConsultar.Observacao.value.length;
	if( navigator.appName == 'Netscape' && valor ) {  //Netscape Only
		document.ConsMovimentacaoConsultar.NCaracteresO.focus();
	}
}
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="ConsMovimentacaoConsultar.php" method="post" name="ConsMovimentacaoConsultar">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Movimentação > Consultar
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if($Mens == 1) {?>
	<tr>
		<td width="100"></td>
		<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,$Virgula); ?></td>
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
								<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
									CONSULTAR - MOVIMENTAÇÃO DE MATERIAL
								</td>
							</tr>
							<tr>
								<td class="textonormal">
									<p align="justify">
										Para retornar a tela anterior clique no botão "Voltar".
									</p>
								</td>
							</tr>
							<tr>
								<td>
									<table class="textonormal" border="0" align="left" width="100%" summary="">
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Almoxarifado</td>
											<td class="textonormal">
												<?php
												$db   = Conexao();
												# Mostra a Descrição de Acordo com o Almoxarifado #
												$sql  = "SELECT EALMPODESC FROM  SFPC.TBALMOXARIFADOPORTAL";
												$sql .= " WHERE CALMPOCODI = $Almoxarifado AND FALMPOSITU = 'A'";
												$res  = $db->query($sql);
												if( PEAR::isError($res) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Linha = $res->fetchRow();
														echo "$Linha[0]";
												}
												?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Localização</td>
											<td class="textonormal">
												<?php
												# Mostra a Descrição de Acordo com o Almoxarifado #
												$sql  = "SELECT A.FLOCMAEQUI, A.ALOCMANEQU, A.ALOCMAPRAT, A.ALOCMACOLU, B.EARLOCDESC ";
												$sql .= "  FROM  SFPC.TBLOCALIZACAOMATERIAL A,  SFPC.TBAREAALMOXARIFADO B";
												$sql .= " WHERE A.CLOCMACODI = $Localizacao AND A.FLOCMASITU = 'A'";
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
												$db->disconnect();
												?>
											</td>
										</tr>
										<tr>
											<td class="textonormal"bgcolor="#DCEDF7" height="20" width="30%">Data da Movimentação</td>
											<td class="textonormal"><?php echo databarra($DataMovimento); ?></td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Tipo de Movimentação</td>
											<td class="textonormal">
												<?php
												# Mostra o tipo de Movimentação#
												if($TipoMovimentacao == "E"){
														echo "ENTRADA";
												}elseif($TipoMovimentacao == "S"){
														echo "SAÍDA";
												}
												?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Movimentação</td>
											<td class="textonormal"><?php echo $DescMovimentacao; ?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Número da Movimentação/Ano</td>
											<td class="textonormal"><?php echo $MovNumero."/".$MovAno; ?></td>
										</tr>
										<?php if( $Movimentacao == 2 or $Movimentacao == 19 or $Movimentacao == 20 ){ ?>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Número/Ano da Requisição</td>
											<td class="textonormal" colspan="2">
											<?php
											$db   = Conexao();
											# Mostra Dados da Requisição #
											$sql  = "SELECT A.CREQMACODI, A.AREQMAANOR, B.CTIPSRCODI ";
											$sql .= "  FROM SFPC.TBREQUISICAOMATERIAL A, SFPC.TBSITUACAOREQUISICAO B ";
											$sql .= " WHERE A.CREQMASEQU = $Requisicao AND A.CREQMASEQU = B.CREQMASEQU ";
											$sql .= "   AND B.TSITREULAT IN ";
											$sql .= "       (SELECT MAX(TSITREULAT) FROM SFPC.TBSITUACAOREQUISICAO SIT ";
											$sql .= "         WHERE SIT.CREQMASEQU = A.CREQMASEQU)";
											$res  = $db->query($sql);
											if(PEAR::isError($res)){
													ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
											}else{
													$Linha            = $res->fetchRow();
													$NumeroRequisicao = $Linha[0];
													$AnoRequisicao 		= $Linha[1];
													$Situacao 		    = $Linha[2];
													echo substr($NumeroRequisicao+100000,1)."/".$AnoRequisicao;
											}
											$db->disconnect();
											?>
											</td>
										</tr>
										<?php } ?>
 										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Material</td>
											<td class="textonormal"><?php echo $DescMaterial; ?></td>
										</tr>
 										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Unidade</td>
											<td class="textonormal"><?php echo $UnidSigl; ?></td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Quantidade</td>
											<td class="textonormal"><?php echo $QtdMovimentada; ?></td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Valor Movimentado</td>
											<td class="textonormal"><?php echo $ValorMovimentado; ?></td>
										</tr>
										<?php if (($NumeroNota) <> "" and ($NumeroNota) <> 0) {?>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Número da Nota Fiscal/Ano</td>
											<td class="textonormal"><?php echo $NumeroNota."/".$AnoNota; ?></td>
										</tr>
										<?php }
										if ($AlmoxCorresp <> "" ){
												echo "<tr>\n";
												if ($TipoMovimentacao == "E" ) {
														echo "<td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\" width=\"30%\">Almoxarifado Origem</td>\n";
												} else { 
														echo "<td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\" width=\"30%\">Almoxarifado Destino</td>\n";
												}
														echo "<td class=\"textonormal\">\n";
												$db   = Conexao();
												# Mostra a Descrição de Acordo com o Almoxarifado #
												$sql  = "SELECT EALMPODESC FROM  SFPC.TBALMOXARIFADOPORTAL";
												$sql .= " WHERE CALMPOCODI = $AlmoxCorresp AND FALMPOSITU = 'A'";
												$res  = $db->query($sql);
												if( PEAR::isError($res) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Linha = $res->fetchRow();
														echo "$Linha[0]";
												}
												$db->disconnect();
												echo "	</td>\n";
												echo "</tr>\n";
										}
										if($NumeroMovCorresp <> ""){
												$db   = Conexao();
												$sql  = "SELECT CMOVMACODT, FMOVMACORR, AMOVMAANOM FROM SFPC.TBMOVIMENTACAOMATERIAL ";
												$sql .= " WHERE CALMPOCODI = $AlmoxCorresp ";
												$sql .= "   AND AMOVMAANOM = $AnoMovimentacao ";
												$sql .= "   AND CMOVMACODI = $NumeroMovCorresp ";
												$sql .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
												$res  = $db->query($sql);
												if( PEAR::isError($res) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Linha = $res->fetchRow();
														$NumeroMovCorrespTipo = $Linha[0];
														$NumeroMovAno         = $Linha[2];
												}
												$db->disconnect();
												echo "<tr>\n";
												echo "	<td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\" wisth=\"30%\">Número da Movimentação Correspondente/Ano</td>\n";
												echo "	<td class=\"textonormal\">$NumeroMovCorrespTipo/$NumeroMovAno</td> \n";
												echo "</tr>\n";
										}
										if($MaterialCorresp <> ""){
												$db   = Conexao();
												# Mostra Dados da Requisição #
												$sql  = "SELECT EMATEPDESC ";
												$sql .= "  FROM SFPC.TBMATERIALPORTAL ";
												$sql .= " WHERE CMATEPSEQU = $MaterialCorresp ";
												$res  = $db->query($sql);
												if(PEAR::isError($res)){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Linha               = $res->fetchRow();
														$DescMaterialCorresp = $Linha[0];
												}
												$db->disconnect();
												echo "<tr>\n";
												echo "	<td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\" wisth=\"30%\">Material Correspondente</td>\n";
												echo "	<td class=\"textonormal\">$DescMaterialCorresp</td> \n";
												echo "</tr>\n";
										}
										if($QtdeCorresp <> ""){
													echo "<tr>\n";
													echo "<td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\" wisth=\"30%\">Quantidade Correspondente</td>\n";
													echo "<td class=\"textonormal\">$QtdeCorresp</td> \n";
													echo "</tr>\n";
										} ?>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Matrícula do Responsável pela Autorização da Movimentação</td>
											<td class="textonormal"><?php echo $Matricula; ?></td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Nome do Responsável</td>
											<td class="textonormal"><?php echo $Responsavel; ?></td>
											</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7">Observação</td>
											<td class="textonormal"><?php echo $Observacao; ?></td>
											</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7">Nome do Usuário responsável pela Alteração</td>
												<?php
												$db   = Conexao();
												$sql  = " SELECT EUSUPORESP ";
												$sql .= " FROM SFPC.TBUSUARIOPORTAL ";
												$sql .= " WHERE CUSUPOCODI = $CodUsuarioResp ";
												$res  = $db->query($sql);
												if( PEAR::isError($res) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Linha = $res->fetchRow();
														$DescUsuarioResp = $Linha[0];
												}
												$db->disconnect();
												?>
											<td class="textonormal"><?php echo $DescUsuarioResp; ?></td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7">Data/Hora da Última Alteração</td>
											<td class="textonormal"><?php echo databarra(substr($DataHoraAlter,0,10))." ".substr($DataHoraAlter,11); ?></td>		        	  		
										</tr>
										<?php
											if($AlmoxCorresp and ($Movimentacao == 6 or $Movimentacao == 9 or $Movimentacao == 11 or $Movimentacao == 12 or $Movimentacao == 13 or $Movimentacao == 15 or $Movimentacao == 29 or $Movimentacao == 30)){
													echo "<tr>\n";
													echo "	<td class=\"textonormal\" bgcolor=\"#DCEDF7\">Andamento da Movimentação:</td>\n";
													echo "	<td class=\"textonormal\">\n";
													if($MovConcluida == "S" or $Movimentacao == 6 or $Movimentacao == 9 or $Movimentacao == 11 or $Movimentacao == 29){ // Se a flag for 'S' ou se for uma entrada de uma movimentação de saída, o andamento é 'Concluído'
															echo "Concluída";
													}else{
															echo "Pendente";
													}
													echo "	</td>\n";
													echo "</tr>\n";
											}

											# Mostrar dados de emprestimo #
											if( ($Movimentacao == 6 ) or ($Movimentacao == 9 ) or ($Movimentacao == 12 ) or ($Movimentacao == 13 ) ){
													# Resgata os dados da Movimentação #
													# Buscar dados do tipo 12 (Entrada por Emprestimo) #
													if($Movimentacao == 12){
															$AlmoxTipo12   = $Almoxarifado;
															$AnoMovTipo12  = $AnoMovimentacao;
															$CodMovTipo12  = $Sequencial;
													}elseif($Movimentacao == 6){ # Buscar dados do tipo 12 (Entrada por Emprestimo) atraves do tipo 6 #
															$db = Conexao();
															$sql    = "SELECT CALMPOCOD1, AMOVMAANO1, CMOVMACOD1 ";
															$sql   .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL ";
															$sql   .= " WHERE CTIPMVCODI = 6 "; 
															$sql   .= "   AND CALMPOCODI = $Almoxarifado ";
															$sql   .= "   AND AMOVMAANOM = $AnoMovimentacao ";
															$sql   .= "   AND CMOVMACODI = $Sequencial";
															$sql   .= "   AND CMATEPSEQU = $Material"; 
															$sql   .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
															$res    = $db->query($sql);
															if( PEAR::isError($res) ){
																	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
															}else{
																	$row           = $res->fetchRow();
																	$AlmoxTipo12   = $row[0];
																	$AnoMovTipo12  = $row[1];
																	$CodMovTipo12  = $row[2];
															}
															$db->disconnect();
													}elseif($Movimentacao == 13 ){# Buscar dados do tipo 12 (Entrada por Emprestimo) atraves do tipo 13 #
															$db = Conexao();
															$sql    = "SELECT CALMPOCOD1, AMOVMAANO1, CMOVMACOD1 ";
															$sql   .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A  ";
															$sql   .= " WHERE A.CTIPMVCODI = 13 "; 
															$sql   .= "   AND A.CALMPOCODI = $Almoxarifado ";
															$sql   .= "   AND A.AMOVMAANOM = $AnoMovimentacao ";
															$sql   .= "   AND A.CMOVMACODI = $Sequencial";
															$sql   .= "   AND A.CMATEPSEQU = $Material"; 
															$sql   .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A') ";
															$res    = $db->query($sql); 
															if( PEAR::isError($res) ){
																	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
															}else{
																	$row           = $res->fetchRow();
																	$AlmoxTipo12   = $row[0];
																	$AnoMovTipo12  = $row[1];
																	$CodMovTipo12  = $row[2];
															}
															$db->disconnect();
													}elseif($Movimentacao == 9){ # Buscar dados do tipo 12 (Entrada por Emprestimo) atraves do tipo 9 #
															$db = Conexao();
															$sql    = "SELECT CALMPOCOD1, AMOVMAANO1, CMOVMACOD1 ";
															$sql   .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL ";
															$sql   .= " WHERE CTIPMVCODI = 9 "; 
															$sql   .= "   AND CALMPOCODI = $Almoxarifado ";
															$sql   .= "   AND AMOVMAANOM = $AnoMovimentacao ";
															$sql   .= "   AND CMOVMACODI = $Sequencial";
															$sql   .= "   AND CMATEPSEQU = $Material"; 
															$sql   .= "	  AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
															$res    = $db->query($sql);
															if( PEAR::isError($res) ){
																	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
															}else{
																	$row  = $res->fetchRow();
																	$AlmoxTipo13   = $row[0];
																	$AnoMovTipo13  = $row[1];
																	$CodMovTipo13  = $row[2];
																	if($AlmoxTipo13 and $AnoMovTipo13 and $CodMovTipo13){
																			# Buscar dados do tipo 12 (Entrada por Emprestimo) atraves do tipo 13 #
																			$sql    = "SELECT CALMPOCOD1, AMOVMAANO1, CMOVMACOD1 ";
																			$sql   .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL ";
																			$sql   .= " WHERE CTIPMVCODI = 13 "; 
																			$sql   .= "   AND CALMPOCODI = $AlmoxTipo13 ";
																			$sql   .= "   AND AMOVMAANOM = $AnoMovTipo13 ";
																			$sql   .= "   AND CMOVMACODI = $CodMovTipo13";
																			$sql   .= "   AND CMATEPSEQU = $Material"; 
																			$sql   .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
																			$res    = $db->query($sql);
																			if( PEAR::isError($res) ){
																					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																			}else{
																					$row           = $res->fetchRow();
																					$AlmoxTipo12   = $row[0];
																					$AnoMovTipo12  = $row[1];
																					$CodMovTipo12  = $row[2];
																			}
																	}
															}
															$db->disconnect();
													}
													?>
													<?php
													# Exibe detalhamento apenas se o empréstimo foi feito após as mudanças na programa de movimentação #
													if($AlmoxCorresp and $AlmoxTipo12 and $AnoMovTipo12 and $CodMovTipo12){
													?>
															<tr>
																<td colspan="2">
																	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" width="100%" bgcolor="#FFFFFF" summary="">
																		<tr>
																			<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="6">
																				DETALHAMENTO DE EMPRÉSTIMO
																			</td>
																		</tr>
																		<?php
																		$db     = Conexao();
																		# SAÍDA POR EMPRÉSTIMO (12)
																		$sql    = "SELECT MOV.DMOVMAMOVI, TIP.ETIPMVDESC, TIP.FTIPMVTIPO, MOV.CALMPOCODI, ";
																		$sql   .= "       MOV.CALMPOCOD1, MOV.AMOVMAANOM, MOV.CMOVMACODT, MOV.AMOVMAQTDM, ";
																		$sql   .= "       MOV.VMOVMAVALO ";
																		$sql   .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL MOV, SFPC.TBTIPOMOVIMENTACAO TIP";
																		$sql   .= " WHERE MOV.CTIPMVCODI = 12 "; 
																		$sql   .= "	  AND MOV.CALMPOCODI = $AlmoxTipo12 ";
																		$sql   .= "   AND MOV.AMOVMAANOM = $AnoMovTipo12 ";
																		$sql   .= "   AND MOV.CMOVMACODI = $CodMovTipo12 ";
																		$sql   .= "   AND MOV.CMATEPSEQU = $Material "; 
																		$sql   .= "   AND MOV.CTIPMVCODI = TIP.CTIPMVCODI "; 
																		$sql   .= "	  AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
																		$res    = $db->query($sql);
																		if( PEAR::isError($res) ){
																				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																		}else{
																				$qtdres = $res->numRows();
																				if( $qtdres > 0 ){
																						$row       = $res->fetchRow();
																						$DataMov   = $row[0];
																						$DescMov   = $row[1];
																						$TipMov    = $row[2];
																						$CodAlmox1 = $row[3];
																						$CodAlmox2 = $row[4];
																						$AnoMov    = $row[5];
																						$CodMov    = $row[6];
																						$QtdMov    = str_replace(".",",",$row[7]);
																						$ValorMov  = str_replace(".",",",$row[8]); ?>
																						<tr>
																							<td align="center" class="titulo3" bgcolor="#BFDAF2" colspan="6">
																									<?php echo $DescMov; ?>
																							</td>
																						</tr>
																						<tr class="textonormal">
																							<td class="titulo3" bgcolor="#F7F7F7" align="CENTER">DATA</td>
																							<td class="titulo3" bgcolor="#F7F7F7" align="CENTER">ALMOXARIFADO</td>
																							<td class="titulo3" bgcolor="#F7F7F7" align="CENTER">ALMOXARIFADO CORRESPONDENTE</td>
																							<td class="titulo3" bgcolor="#F7F7F7" align="CENTER">CÓD. MOV.</td>
																							<td class="titulo3" bgcolor="#F7F7F7" align="CENTER">QUANTIDADE</td>
																							<td class="titulo3" bgcolor="#F7F7F7" align="CENTER">VALOR</td>
																						</tr>
																						<tr align="left" class="textonormal">
																							<td><?php list($A,$M,$D)=explode("-",$DataMov);echo "$D/$M/$A"; ?></td>
																							<?php
																							# Mostra a Descrição de Acordo com o Almoxarifado origem #
																							$sqlao  = "SELECT EALMPODESC FROM  SFPC.TBALMOXARIFADOPORTAL";
																							$sqlao .= " WHERE CALMPOCODI = $CodAlmox1 AND FALMPOSITU = 'A'";
																							$resao  = $db->query($sqlao);
																							if( PEAR::isError($resao) ){
																									ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlao");
																							}else{
																									$Linhaao = $resao->fetchRow();
																									$DescAlmox1 = $Linhaao[0];
																							}
																							# Mostra a Descrição de Acordo com o Almoxarifado destino #
																							$sqlad  = "SELECT EALMPODESC FROM  SFPC.TBALMOXARIFADOPORTAL";
																							$sqlad .= " WHERE CALMPOCODI = $CodAlmox2 AND FALMPOSITU = 'A'";
																							$resad  = $db->query($sqlad);
																							if( PEAR::isError($resad) ){
																									ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlad");
																							}else{
																									$Linhaad = $resad->fetchRow();
																									$DescAlmox2 = $Linhaad[0];
																							}?>
																							<td><?php echo $DescAlmox1; ?></td>
																							<td><?php echo $DescAlmox2; ?></td>
																							<td><?php echo $CodMov."/".$AnoMov; ?></td>
																							<td align="right"><?php echo $QtdMov;   ?></td>
																							<td align="right"><?php echo $ValorMov; ?></td>
																						</tr>
																				<?php
																				}
																		}
																		# ENTRADA POR EMPRÉSTIMO (6)
																		$sql    = "SELECT MOV.DMOVMAMOVI, TIP.ETIPMVDESC, TIP.FTIPMVTIPO,  MOV.CALMPOCODI, ";
																		$sql   .= "       MOV.CALMPOCOD1, MOV.AMOVMAANOM, MOV.CMOVMACODT,  MOV.AMOVMAQTDM, ";
																		$sql   .= "       MOV.VMOVMAVALO ";
																		$sql   .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL MOV, SFPC.TBTIPOMOVIMENTACAO TIP";
																		$sql   .= " WHERE MOV.CTIPMVCODI = 6 "; 
																		$sql   .= "	  AND MOV.CALMPOCOD1 = $AlmoxTipo12 ";
																		$sql   .= "   AND MOV.AMOVMAANO1 = $AnoMovTipo12 ";
																		$sql   .= "   AND MOV.CMOVMACOD1 = $CodMovTipo12";
																		$sql   .= "   AND MOV.CMATEPSEQU = $Material"; 
																		$sql   .= "   AND MOV.CTIPMVCODI = TIP.CTIPMVCODI"; 
																		$sql   .= "	  AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
																		$res    = $db->query($sql);
																		if( PEAR::isError($res) ){
																				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																		}else{
																				$qtdres = $res->numRows();
																				if( $qtdres > 0 ){
																						$row       = $res->fetchRow();
																						$DataMov   = $row[0];
																						$DescMov   = $row[1];
																						$TipMov    = $row[2];
																						$CodAlmox1 = $row[3];
																						$CodAlmox2 = $row[4];
																						$AnoMov    = $row[5];
																						$CodMov    = $row[6];
																						$QtdMov    = str_replace(".",",",$row[7]);
																						$ValorMov  = str_replace(".",",",$row[8]); ?>
																						<tr>
																							<td align="center" class="titulo3" bgcolor="#BFDAF2" colspan="6">
																									<?php echo $DescMov; ?>
																							</td>
																						</tr>
																						<tr class="textonormal">
																							<td class="titulo3" bgcolor="#F7F7F7" align="CENTER">DATA</td>
																							<td class="titulo3" bgcolor="#F7F7F7" align="CENTER">ALMOXARIFADO</td>
																							<td class="titulo3" bgcolor="#F7F7F7" align="CENTER">ALMOXARIFADO CORRESPONDENTE</td>
																							<td class="titulo3" bgcolor="#F7F7F7" align="CENTER">CÓD. MOV.</td>
																							<td class="titulo3" bgcolor="#F7F7F7" align="CENTER">QUANTIDADE</td>
																							<td class="titulo3" bgcolor="#F7F7F7" align="CENTER">VALOR</td>
																						</tr>
																						<tr align="left" class="textonormal">
																							<td><?php list($A,$M,$D)=explode("-",$DataMov);echo "$D/$M/$A"; ?></td>
																							<?php
																							# Mostra a Descrição de Acordo com o Almoxarifado origem #
																							$sqlao  = "SELECT EALMPODESC FROM  SFPC.TBALMOXARIFADOPORTAL";
																							$sqlao .= " WHERE CALMPOCODI = $CodAlmox1 AND FALMPOSITU = 'A'";
																							$resao  = $db->query($sqlao);
																							if( PEAR::isError($resao) ){
																									ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlao");
																							}else{
																									$Linhaao = $resao->fetchRow();
																									$DescAlmox1 = $Linhaao[0];
																							}
																							# Mostra a Descrição de Acordo com o Almoxarifado destino #
																							$sqlad  = "SELECT EALMPODESC FROM  SFPC.TBALMOXARIFADOPORTAL";
																							$sqlad .= " WHERE CALMPOCODI = $CodAlmox2 AND FALMPOSITU = 'A'";
																							$resad  = $db->query($sqlad);
																							if( PEAR::isError($resad) ){
																									ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlad");
																							}else{
																									$Linhaad = $resad->fetchRow();
																									$DescAlmox2 = $Linhaad[0];
																							}?>
																							<td><?php echo $DescAlmox1; ?></td>
																							<td><?php echo $DescAlmox2; ?></td>
																							<td><?php echo $CodMov."/".$AnoMov; ?></td>
																							<td align="right"><?php echo $QtdMov;   ?></td>
																							<td align="right"><?php echo $ValorMov; ?></td>
																						</tr>
																				<?php
																				}
																		}
																		# SAÍDA POR DEVOLUÇÃO DE EMPRÉSTIMO (13)
																		$sql    = "SELECT MOV.DMOVMAMOVI, TIP.ETIPMVDESC, TIP.FTIPMVTIPO,  MOV.CALMPOCODI, ";
																		$sql   .= "       MOV.CALMPOCOD1, MOV.AMOVMAANOM, MOV.CMOVMACODI,  MOV.AMOVMAQTDM, ";
																		$sql   .= "       MOV.VMOVMAVALO, MOV.CMOVMACOD1, MOV.AMOVMAANO1 ";
																		$sql   .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL MOV, SFPC.TBTIPOMOVIMENTACAO TIP";
																		$sql   .= " WHERE MOV.CTIPMVCODI = 13 "; 
																		$sql   .= "	  AND MOV.CALMPOCOD1 = $AlmoxTipo12 ";
																		$sql   .= "   AND MOV.AMOVMAANO1 = $AnoMovTipo12 ";
																		$sql   .= "   AND MOV.CMOVMACOD1 = $CodMovTipo12";
																		$sql   .= "   AND MOV.CMATEPSEQU = $Material"; 
																		$sql   .= "   AND MOV.CTIPMVCODI = TIP.CTIPMVCODI"; 
																		$sql   .= "	  AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
																		$res    = $db->query($sql);
																		if( PEAR::isError($res) ){
																				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																		}else{
																				$qtdres = $res->numRows();
																				if( $qtdres > 0 ){
																						while($row = $res->fetchRow()){
																								$exec++;
																								$DataMov    = $row[0];
																								$DescMov    = $row[1];
																								$TipMov     = $row[2];
																								$CodAlmox1  = $row[3];
																								$CodAlmox2  = $row[4];
																								$AnoMov     = $row[5];
																								$CodArray[] = $row[6];
																								$QtdMov     = str_replace(".",",",$row[7]);
																								$ValorMov   = str_replace(".",",",$row[8]);
																								$CodMov13   = $row[9];
																								$AnoMov13   = $row[10];
																								If($exec == 1){?>
																										<tr>
																											<td align="center" class="titulo3" bgcolor="#BFDAF2" colspan="6">
																													<?php echo $DescMov; ?>
																											</td>
																										</tr>
																										<tr class="textonormal">
																											<td class="titulo3" bgcolor="#F7F7F7" align="CENTER">DATA</td>
																											<td class="titulo3" bgcolor="#F7F7F7" align="CENTER">ALMOXARIFADO</td>
																											<td class="titulo3" bgcolor="#F7F7F7" align="CENTER">ALMOXARIFADO CORRESPONDENTE</td>
																											<td class="titulo3" bgcolor="#F7F7F7" align="CENTER">CÓD. MOV.</td>
																											<td class="titulo3" bgcolor="#F7F7F7" align="CENTER">QUANTIDADE</td>
																											<td class="titulo3" bgcolor="#F7F7F7" align="CENTER">VALOR</td>
																										</tr>
																								<?php
																								}
																								?>
																								<tr align="left" class="textonormal">
																									<td><?php list($A,$M,$D)=explode("-",$DataMov);echo "$D/$M/$A"; ?></td>
																									<?php
																									# Mostra a Descrição de Acordo com o Almoxarifado origem #
																									$sqlao  = "SELECT EALMPODESC FROM  SFPC.TBALMOXARIFADOPORTAL";
																									$sqlao .= " WHERE CALMPOCODI = $CodAlmox1 AND FALMPOSITU = 'A'";
																									$resao  = $db->query($sqlao);
																									if( PEAR::isError($resao) ){
																											ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlao");
																									}else{
																											$Linhaao = $resao->fetchRow();
																											$DescAlmox1 = $Linhaao[0];
																									}
																									# Mostra a Descrição de Acordo com o Almoxarifado destino #
																									$sqlad  = "SELECT EALMPODESC FROM  SFPC.TBALMOXARIFADOPORTAL";
																									$sqlad .= " WHERE CALMPOCODI = $CodAlmox2 AND FALMPOSITU = 'A'";
																									$resad  = $db->query($sqlad);
																									if( PEAR::isError($resad) ){
																											ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlad");
																									}else{
																											$Linhaad = $resad->fetchRow();
																											$DescAlmox2 = $Linhaad[0];
																									}?>
																									<td><?php echo $DescAlmox1; ?></td>
																									<td><?php echo $DescAlmox2; ?></td>
																									<td><?php echo $CodMov."/".$AnoMov; ?></td>
																									<td align="right"><?php echo $QtdMov;   ?></td>
																									<td align="right"><?php echo $ValorMov; ?></td>
																								</tr>
																						<?php
																						}
																				}
																		}
																		# ENTRADA POR DEVOLUÇÃO DE EMPRÉSTIMO (9)	
																		$sql    = "SELECT MOV.DMOVMAMOVI, TIP.ETIPMVDESC, TIP.FTIPMVTIPO,  MOV.CALMPOCODI, ";
																		$sql   .= "       MOV.CALMPOCOD1, MOV.AMOVMAANOM, MOV.CMOVMACODT,  MOV.AMOVMAQTDM, ";
																		$sql   .= "       MOV.VMOVMAVALO ";
																		$sql   .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL MOV, SFPC.TBTIPOMOVIMENTACAO TIP";
																		$sql   .= " WHERE MOV.CTIPMVCODI = 9 "; 
																		$sql   .= "   AND MOV.CALMPOCOD1 = $CodAlmox1 ";
																		$sql   .= "   AND MOV.AMOVMAANO1 = $AnoMov ";
																		if($CodArray){
																				$sql   .= "   AND MOV.CMOVMACOD1 IN (";
																				$exec = null;
																				foreach($CodArray as $Cod){
																						if($escrevevirg == null){
																								$sql   .= "$Cod";
																						}else{
																								$sql   .= ",$Cod";
																						}
																						$escrevevirg++;
																				}
																				$sql   .= "   )";
																		}
																		$sql   .= "   AND MOV.CMATEPSEQU = $Material"; 
																		$sql   .= "   AND MOV.CTIPMVCODI = TIP.CTIPMVCODI"; 
																		$sql   .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
																		$res    = $db->query($sql);
																		if( PEAR::isError($res) ){
																				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																		}else{
																				$qtdres = $res->numRows();
																				if($qtdres > 0){
																						while($row = $res->fetchRow()){
																								$exec++;
																								$DataMov   = $row[0];
																								$DescMov   = $row[1];
																								$TipMov    = $row[2];
																								$CodAlmox1 = $row[3];
																								$CodAlmox2 = $row[4];
																								$AnoMov    = $row[5];
																								$CodMov    = $row[6];
																								$QtdMov    = str_replace(".",",",$row[7]);
																								$ValorMov  = str_replace(".",",",$row[8]);
																								If($exec == 1){?>
																										<tr>
																											<td align="center" class="titulo3" bgcolor="#BFDAF2" colspan="6">
																													<?php echo $DescMov; ?>
																											</td>
																										</tr>
																										<tr class="textonormal">
																											<td class="titulo3" bgcolor="#F7F7F7" align="CENTER">DATA</td>
																											<td class="titulo3" bgcolor="#F7F7F7" align="CENTER">ALMOXARIFADO</td>
																											<td class="titulo3" bgcolor="#F7F7F7" align="CENTER">ALMOXARIFADO CORRESPONDENTE</td>
																											<td class="titulo3" bgcolor="#F7F7F7" align="CENTER">CÓD. MOV.</td>
																											<td class="titulo3" bgcolor="#F7F7F7" align="CENTER">QUANTIDADE</td>
																											<td class="titulo3" bgcolor="#F7F7F7" align="CENTER">VALOR</td>
																										</tr>
																								<?php
																								}
																								?>
																								<tr align="left" class="textonormal">
																									<td><?php list($A,$M,$D)=explode("-",$DataMov);echo "$D/$M/$A"; ?></td>
																									<?php
																									# Mostra a Descrição de Acordo com o Almoxarifado origem #
																									$sqlao  = "SELECT EALMPODESC FROM  SFPC.TBALMOXARIFADOPORTAL";
																									$sqlao .= " WHERE CALMPOCODI = $CodAlmox1 AND FALMPOSITU = 'A'";
																									$resao  = $db->query($sqlao);
																									if( PEAR::isError($resao) ){
																											ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlao");
																									}else{
																											$Linhaao = $resao->fetchRow();
																											$DescAlmox1 = $Linhaao[0];
																									}
																									# Mostra a Descrição de Acordo com o Almoxarifado destino #
																									$sqlad  = "SELECT EALMPODESC FROM  SFPC.TBALMOXARIFADOPORTAL";
																									$sqlad .= " WHERE CALMPOCODI = $CodAlmox2 AND FALMPOSITU = 'A'";
																									$resad  = $db->query($sqlad);
																									if( PEAR::isError($resad) ){
																											ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlad");
																									}else{
																											$Linhaad = $resad->fetchRow();
																											$DescAlmox2 = $Linhaad[0];
																									}
																									?>
																									<td><?php echo $DescAlmox1; ?></td>
																									<td><?php echo $DescAlmox2; ?></td>
																									<td><?php echo $CodMov."/".$AnoMov; ?></td>
																									<td align="right"><?php echo $QtdMov;   ?></td>
																									<td align="right"><?php echo $ValorMov; ?></td>
																								</tr>
																								<?php
																						}
																				}
																		}
																		$db->disconnect();
																		?>
																	</table>
																</td>
															</tr>
													<?php
													}
											}
											?>
									</table>
								</td>
							</tr>
							<tr>
								<td class="textonormal" align="right">
									<input type="hidden" name="Requisicao" value="<?php echo $Requisicao; ?>">
									<input type="hidden" name="Situacao" value="<?php echo $Situacao; ?>">
									<input type="hidden" name="Almoxarifado" value="<?php echo $Almoxarifado; ?>">
									<input type="hidden" name="Sequencial" value="<?php echo $Sequencial ?>">
									<input type="hidden" name="AnoMovimentacao" value="<?php echo $AnoMovimentacao ?>">
									<input type="hidden" name="Material" value="<?php echo $Material ?>">
									<input type="hidden" name="DataMovimento" value="<?php echo $DataMovimento ?>">
									<input type="hidden" name="TipoMovimentacao" value="<?php echo $TipoMovimentacao ?>">
									<input type="hidden" name="Localizacao" value="<?php echo $Localizacao ?>">
									<input type="hidden" name="Localozacao" value="<?php echo $AlmoxCorresp ?>">
									<input type="hidden" name="Movimentacao" value="<?php echo $Movimentacao ?>">
									<input type="hidden" name="MovNumero" value="<?php echo $MovNumero ?>">
									<input type="hidden" name="DescMovimentacao" value="<?php echo $DescMovimentacao ?>">
									<input type="hidden" name="ValorMovimentado" value="<?php echo $ValorMovimentado ?>">
									<input type="hidden" name="UnidSigl" value="<?php echo $UnidSigl ?>">
									<input type="hidden" name="DescMaterial" value="<?php echo $DescMaterial ?>">	
									<input type="hidden" name="NumeroMovCorresp" value="<?php echo $NumeroMovCorresp ?>">
									<input type="hidden" name="NumeroMovAno" value="<?php echo $NumeroMovAno ?>">
									<input type="hidden" name="MaterialCorresp" value="<?php echo $MaterialCorresp ?>">
									<input type="hidden" name="QtdeCorresp" value="<?php echo $QtdeCorresp ?>">
									<input type="hidden" name="CodUsuarioResp" value="<?php echo $CodUsuarioResp ?>">
									<input type="hidden" name="DataHoraAlter" value="<?php echo $DataHoraAlter ?>">
									<input type="hidden" name="MovConcluida" value="<?php echo $MovConcluida ?>">
									<input type="button" name="Voltar" value="Voltar" class="botao" onClick="javascript:enviar('Voltar');">
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
