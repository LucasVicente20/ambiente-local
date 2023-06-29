<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadParametroContabilIncluir.php
# Objetivo: Programa de inclusão de parâmetros para a Contabilidade
# Autor:    Álvaro Faria
# Data:     28/12/2006
# Alterado: Álvaro Faria
# Data:     24/01/2006 - Liberação para inclusão da movimentação 25 e 28
# Alterado: Carlos Abreu
# Data:     28/05/2007 - Liberaçao da escolha do tipo de material
# Alterado: Carlos Abreu
# Data:     28/05/2007 - Comentado filtro para poder cadastrar movimentacao contabil mesmo que exista 
#                        movimentacao no ano corrente (para atender inclusao de parametros para permanente)
# Alterado: Fausto Feitosa
# Data:     21/11/2007 - Exibição de um novo campo com o subelemento de despesa se o tipo material for permanente
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao            = $_POST['Botao'];
		$TipoMovimentacao = $_POST['TipoMovimentacao'];
		$Movimentacao     = $_POST['Movimentacao'];
		$TipoMaterial     = $_POST['TipoMaterial'];
		$AnoConta         = $_POST['AnoConta'];
		$NumeroConta      = $_POST['NumeroConta'];
		$Historico        = $_POST['Historico'];
		$TipoMovCont      = $_POST['TipoMovCont'];
		$Natureza         = $_POST['Natureza'];
		$Lote             = $_POST['Lote'];
		//Fausto Feitosa - 21/11/2007
		if ($TipoMaterial == "P") {
       $SubElemento   = $_POST['SubElemento'];
    }
		// Fausto Feitosa
}else{
		$Mensagem         = urldecode($_GET['Mensagem']);
		$Mens             = $_GET['Mens'];
		$Tipo             = $_GET['Tipo'];
		$Troca            = $_GET['Troca'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadParametroContabilIncluir.php";

# Descobre o ano atual #
$Ano = date("Y");

# Padrão que pode ser mudado durante o programa. Desta forma converte última vírgula da mensagem de erro por "e" #
if(!$Troca) $Troca = 1;

if($Botao == "Limpar"){
		unset($_SESSION['TodosParametros']);
		header("location: CadParametroContabilIncluir.php");
		exit;
}elseif($Botao == "Incluir"){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if(!$TipoMovimentacao){
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadParametroContabilIncluir.TipoMovimentacao.focus();\" class=\"titulo2\">Tipo de Movimentação</a>";
		}
		if(!$Movimentacao){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadParametroContabilIncluir.Movimentacao.focus();\" class=\"titulo2\">Movimentação</a>";
		}
		if(!$TipoMaterial){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadParametroContabilIncluir.TipoMaterial.focus();\" class=\"titulo2\">Tipo de Material</a>";
		}
		if(!$AnoConta){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadParametroContabilIncluir.AnoConta.focus();\" class=\"titulo2\">Ano Conta Contábil</a>";
		}
		if(!$NumeroConta){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadParametroContabilIncluir.NumeroConta.focus();\" class=\"titulo2\">Número Conta Contábil</a>";
		}
		if(!$Historico){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadParametroContabilIncluir.Historico.focus();\" class=\"titulo2\">Histórico</a>";
		}
		if(!$TipoMovCont){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadParametroContabilIncluir.TipoMovCont.focus();\" class=\"titulo2\">Movimento Contábil</a>";
		}
		if(!$Natureza){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadParametroContabilIncluir.Natureza.focus();\" class=\"titulo2\">Natureza Lançamento</a>";
		}
		if(!$Lote){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadParametroContabilIncluir.Lote.focus();\" class=\"titulo2\">Lote Contábil</a>";
		}elseif(!SoNumeros($Lote) ){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadParametroContabilIncluir.Lote.focus();\" class=\"titulo2\">Lote Contábil válido</a>";
		}
		/*
		* Fausto Feitosa - 21/11/2007 - Início
		* Se o tipo materia for do tipo permanente (P)  e o subelemento for vazio prepara a mensagem de erro.
		*/
		if ($TipoMaterial == "P" && !$SubElemento) {
       if($Mens == 1){ $Mensagem .= ", "; }
       $Mens      = 1;
       $Tipo      = 2;
       $Mensagem .= "<a href=\"javascript:document.CadParametroContabilIncluir.SubElemento.focus();\" class=\"titulo2\">Subelemento de despesa</a>";
    }
    # Fausto Feitosa - Fim
		if($Mens == 0){
				$TodosParametros = $TipoMovimentacao."_".$Movimentacao."_".$TipoMaterial."_".$AnoConta;
				$_SESSION['TodosParametros'] = $TodosParametros;
				$db        = Conexao();
				/*
				# Verifica se já houve movimentações para o tipo especificado no ano especificado #
				$sqlCheca  = "SELECT COUNT(*) FROM SFPC.TBMOVIMENTACAOMATERIAL ";
				$sqlCheca .= " WHERE CTIPMVCODI = $Movimentacao ";
				$sqlCheca .= "   AND AMOVMAANOM = $AnoConta";
				$resCheca  = $db->query($sqlCheca);
				if( db::isError($resCheca) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlCheca");
				}else{
						$QtdMovsTipo = $resCheca->fetchRow();
						if($QtdMovsTipo[0] >= 1 and $Movimentacao != 33 and $Movimentacao != 34){
								$db->disconnect();
								$Mensagem = "Parâmetros não podem ser incluídos para este tipo de movimentação no ano de $AnoConta, pois já existem movimentações deste tipo neste ano";
								$Url = "CadParametroContabilIncluir.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2&Troca=2";
								if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
								header("location: ".$Url);
								exit;
						}else{
						*/
                # Verifica se os dados já estão no banco #
								$sqlCheca  = "SELECT COUNT(*) FROM SFPC.TBMOVCONTABILALMOXARIFADOPARAM";
								$sqlCheca .= " WHERE AMVCPMANOC = $AnoConta  AND AMVCPMCONT = $NumeroConta ";
								$sqlCheca .= "   AND AMVCPMHIST = $Historico AND AMVCPMTPMC = $TipoMovCont ";
								$sqlCheca .= "   AND FMVCPMDBCD = '$Natureza'  AND AMVCPMLOTE = $Lote AND CTIPMVCODI = $Movimentacao ";
								$sqlCheca .= "   AND FMVCPMTIPM = '$TipoMaterial' ";
								#{ Fausto Feitosa - 27/11/2007
								if ($TipoMaterial == "P")  {
                           $SubElementoArray = explode("!", $SubElemento);
                           $sqlCheca .= "   AND CMVCPMELE1 = '$SubElementoArray[0]' AND CMVCPMELE2 = '$SubElementoArray[1]' ";
												   $sqlCheca .= "   AND CMVCPMELE3 = '$SubElementoArray[2]' AND CMVCPMELE4 = '$SubElementoArray[3]'";
												   $sqlCheca .= "   AND CMVCPMSUBE = '$SubElementoArray[4]' AND NMVCPMNOMS = '$SubElementoArray[5]'";
								}
								# }
								$resCheca  = $db->query($sqlCheca);
								if( db::isError($resCheca) ){
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlCheca");
								}else{
										# Grava variáveis na sessão para carregar quando entrar na página por GET e o botão não for Limpar #
										$QtdParametro = $resCheca->fetchRow();
										if($QtdParametro[0] >= 1){
												$db->disconnect();
												$Mensagem        = "Parâmetros já cadastrados anteriormente";
												$Url = "CadParametroContabilIncluir.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2";
												if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
												header("location: ".$Url);
												exit;
										}else{
                        $SubElementoDesp = Array();
                        $DataHora = date("Y-m-d H:i:s");
												# Caso não esteja, insere no banco #
												$sqlInserir  = "INSERT INTO SFPC.TBMOVCONTABILALMOXARIFADOPARAM(";
												$sqlInserir .= "AMVCPMANOC, AMVCPMCONT, AMVCPMHIST, AMVCPMTPMC, FMVCPMDBCD, AMVCPMLOTE, CTIPMVCODI, FMVCPMTIPM, TMVCPMULAT";
												/*
												* Fausto Feitosa - 21/11/2007 - Início
												* Se o tipo material for permanente, prepara o insert para conter os campos com o códigos de
												* elementos de despesa, código do subelemento e o nome do subelemento de despesa
												*/
                        if ($TipoMaterial == "P")  {
                           $sqlInserir .= ", CMVCPMELE1, CMVCPMELE2, CMVCPMELE3, CMVCPMELE4, CMVCPMSUBE, NMVCPMNOMS ";
                           $sqlInserir .= ") VALUES (";
												   $sqlInserir .= "$AnoConta, $NumeroConta, $Historico, $TipoMovCont, '$Natureza', $Lote, $Movimentacao, '$TipoMaterial', '$DataHora'";
												   $SubElementoDesp = explode("!", $SubElemento);
                           $sqlInserir .= ", $SubElementoDesp[0], $SubElementoDesp[1], $SubElementoDesp[2], $SubElementoDesp[3], $SubElementoDesp[4], '$SubElementoDesp[5]'";
                        } else {
                           $sqlInserir .= ") VALUES (";
												   $sqlInserir .= "$AnoConta, $NumeroConta, $Historico, $TipoMovCont, '$Natureza', $Lote, $Movimentacao, '$TipoMaterial', '$DataHora'";
                        }
                        # Fausto Feitosa - Fim
												$sqlInserir .= ")";
												$resInserir  = $db->query($sqlInserir);
												if( db::isError($resInserir) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlInserir");
												}else{
														# EXIBINDO MENSAGEM DE SUCESSO #
														$db->disconnect();
														$Mensagem = "Parâmetros incluídos com sucesso";
														$Url = "CadParametroContabilIncluir.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
														if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
														header("location: ".$Url);
														exit;
												}
										}
								}
								/*
						}
				}
				*/
				$db->disconnect();
		}
}
if($Botao != "Limpar"){
		$TodosParametros = $_SESSION['TodosParametros'];
		if($TodosParametros){
				$ArrayParametros = explode("_",$TodosParametros);
				if(!$TipoMovimentacao) $TipoMovimentacao = $ArrayParametros[0];
				if(!$Movimentacao)     $Movimentacao     = $ArrayParametros[1];
				if(!$TipoMaterial)     $TipoMaterial     = $ArrayParametros[2];
				if(!$AnoConta)         $AnoConta         = $ArrayParametros[3];
		}
}

# Preenche com o próximo ano, se ele já não foi definido #
if(!$AnoConta) $AnoConta = date("Y")+1;

?>

<html>
<?
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.CadParametroContabilIncluir.Botao.value = valor;
	document.CadParametroContabilIncluir.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>

<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadParametroContabilIncluir.php" method="post" name="CadParametroContabilIncluir">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Estoques > Contabilidade > Parâmetros > Incluir
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if($Mens == 1){?>
	<tr>
		<td width="100"></td>
		<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,$Troca); ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
			<table border="0" cellspacing="0" cellpadding="3">
				<tr>
					<td class="textonormal">
						<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
									INCLUIR - PARÂMETROS
								</td>
							</tr>
							<tr>
								<td class="textonormal">
									<p align="justify">
										Para incluir um novo parâmetro, informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.
									</p>
								</td>
							</tr>
							<tr>
								<td>
									<table class="textonormal" border="0" align="left" class="caixa">
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo de Movimentação*</td>
											<td class="textonormal">
												<select name="TipoMovimentacao" class="textonormal" onChange="submit();">
													<option value="">Selecione o Tipo de Movimentação...</option>
													<option value="E" <?php if( $TipoMovimentacao == "E" ){ echo "selected"; }?>>ENTRADA</option>
													<option value="S" <?php if( $TipoMovimentacao == "S" ){ echo "selected"; }?>>SAÍDA</option>
												</select>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Movimentação*</td>
											<td class="textonormal">
												<?php
												if($TipoMovimentacao){
														# Pega os tipos das movimentações #
														$db      = Conexao();
														$sqlMov  = "SELECT CTIPMVCODI, ETIPMVDESC FROM SFPC.TBTIPOMOVIMENTACAO ";
														$sqlMov .= " WHERE FTIPMVTIPO = '$TipoMovimentacao' AND CTIPMVCODI NOT IN (3,5,7,8,18,19,20,31)";
														$sqlMov .= " ORDER BY ETIPMVDESC ";
														$resMov  = $db->query($sqlMov);
														if( db::isError($resMov) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlMov");
														}else{
																$rowsMov = $resMov->numRows();
																echo "<select name=\"Movimentacao\" class=\"textonormal\">\n";
																echo "	<option value=\"\">Selecione a Movimentacao...</option>\n";
																for( $i=0;$i< $rowsMov; $i++ ){
																		$LinhaMov = $resMov->fetchRow();
																		if( $Movimentacao == $LinhaMov[0] ){
																				echo "<option value=\"$LinhaMov[0]\" selected>$LinhaMov[1]</option>";
																		}else{
																				echo "<option value=\"$LinhaMov[0]\">$LinhaMov[1]</option>";
																		}
																}
																echo "</select>";
														}
														$db->disconnect();
												}else{
														echo "<select name=\"Movimentacao\" class=\"textonormal\">";
														echo "	<option value=\"\">Selecione o Tipo de Movimentação acima...</option>";
														echo "</select>";
												}
												?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo de Material*</td>
											<td class="textonormal">
												<select name="TipoMaterial" class="textonormal" onChange="submit();">
													<option value="">Selecione o Tipo de Material...</option>
													<option value="C" <?php if( $TipoMaterial == "C" ){ echo "selected"; }?>>CONSUMO</option>
													<option value="P" <?php if( $TipoMaterial == "P" ){ echo "selected"; }?>>PERMANENTE</option>
												</select>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7">Ano Conta Contabil*</td>
											<td class="textonormal">
												<select name="AnoConta" class="textonormal" onChange="submit();">
													<option value="<?php echo $Ano;?>"   <?php if( $AnoConta == $Ano ){ echo "selected"; }?>><?php echo $Ano;?></option>
													<option value="<?php echo $Ano+1;?>" <?php if( $AnoConta == $Ano+1 ){ echo "selected"; }?>><?php echo $Ano+1;?></option>
												</select>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Número Conta Contábil*</td>
											<td class="textonormal">
												<?php
												# Pega os Números de Conta no Oracle #
												$dbora     = ConexaoOracle();
												$sqlConta  = "SELECT APLCTACONT, NPLCTACONT ";
												$sqlConta .= "  FROM SFCT.TBPLANOCONTAS ";
												$sqlConta .= " WHERE APLCTAANOC = $AnoConta ";
												$sqlConta .= " ORDER BY APLCTACONT, NPLCTACONT ";
												$resConta  = $dbora->query($sqlConta);
												if( db::isError($resConta) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlConta");
												}else{
														echo "<select name=\"NumeroConta\" class=\"textonormal\" onChange=\"submit();\">\n";
														echo "	<option value=\"\">Selecione a Conta Contábil...</option>\n";
														while($LinhaConta = $resConta->fetchRow()){
																if( $NumeroConta == $LinhaConta[0] ){
																		echo "<option value=\"$LinhaConta[0]\" selected>$LinhaConta[0] - ".(substr($LinhaConta[1],0,43))."</option>";
																}else{
																		echo "<option value=\"$LinhaConta[0]\">$LinhaConta[0] - ".(substr($LinhaConta[1],0,43))."</option>";
																}
														}
														echo "</select>";
												}
												$dbora->disconnect();
												?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Histórico*</td>
											<td class="textonormal">
												<?php
												# Pega os Históricos no Oracle #
												$dbora = ConexaoOracle();
												$sqlHist  = "SELECT ATEXTCNUME, XTEXTCCONT ";
												$sqlHist .= "  FROM SFCT.TBTEXTOCONTABIL ";
												$sqlHist .= " ORDER BY ATEXTCNUME, XTEXTCCONT ";
												$resHist  = $dbora->query($sqlHist);
												if( db::isError($resHist) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlHist");
												}else{
														echo "<select name=\"Historico\" class=\"textonormal\" onChange=\"submit();\">\n";
														echo "	<option value=\"\">Selecione o Histórico...</option>\n";
														While($LinhaHist = $resHist->fetchRow()){
																if($Historico == $LinhaHist[0]){
																		echo "<option value=\"$LinhaHist[0]\" selected>$LinhaHist[0] - ".(substr($LinhaHist[1],0,49))."</option>";
																}else{
																		echo "<option value=\"$LinhaHist[0]\">$LinhaHist[0] - ".(substr($LinhaHist[1],0,49))."</option>";
																}
														}
														echo "</select>";
												}
												$dbora->disconnect();
												?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Movimento Contábil*</td>
											<td class="textonormal">
												<?php
												if($Historico){
														# Pega os Históricos no Oracle #
														$dbora = ConexaoOracle();
														$sqlMovC  = "SELECT A.CTIPMOCODI, B.NTIPMOTABE ";
														$sqlMovC .= "  FROM SFCT.TBHISTORICOMOVIMENTO A, SFCT.TBTIPOMOVIMENTOCONTABIL B ";
														$sqlMovC .= " WHERE A.AHMOVINUME = $Historico ";
														$sqlMovC .= "   AND A.CTIPMOCODI = B.CTIPMOCODI ";
														$sqlMovC .= " ORDER BY A.CTIPMOCODI, B.NTIPMOTABE ";
														$resMovC  = $dbora->query($sqlMovC);
														if( db::isError($resMovC) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlMovC");
														}else{
																echo "<select name=\"TipoMovCont\" class=\"textonormal\">\n";
																echo "	<option value=\"\">Selecione o Movimento Contábil...</option>\n";
																While($LinhaMovC = $resMovC->fetchRow()){
																		if($TipoMovCont == $LinhaMovC[0]){
																				echo "<option value=\"$LinhaMovC[0]\" selected>$LinhaMovC[0] - $LinhaMovC[1]</option>";
																		}else{
																				echo "<option value=\"$LinhaMovC[0]\">$LinhaMovC[0] - $LinhaMovC[1]</option>";
																		}
																}
																echo "</select>";
														}
														$dbora->disconnect();
												}else{
														echo "<select name=\"TipoMovCont\" class=\"textonormal\">\n";
														echo "	<option value=\"\">Selecione o Histórico acima...</option>\n";
														echo "</select>";
												}
												?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Natureza Lançamento*</td>
											<td class="textonormal">
												<select name="Natureza" class="textonormal">
													<option value="">Selecione a Natureza do Lançamento...</option>
													<option value="D" <?php if( $Natureza == "D" ){ echo "selected"; }?>>DÉBITO</option>
													<option value="C" <?php if( $Natureza == "C" ){ echo "selected"; }?>>CRÉDITO</option>
												</select>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7">Número Lote Contabil*</td>
											<td class="textonormal">
												99999
												<input type="hidden" name="Lote" value="99999">
											</td>
										</tr>

										<?php
                    /*
                    *  Fausto Feitosa - 21/11/2007
                    *  Exibi o campo de subelemento de despesa se o tipo material for permanente.
                    *  Os dados do campo são extraídos da tabela SPOD.TBSUBELEMENTODESPESA que está no Oracle.
                    */
                    if ($TipoMaterial == "P") { ?>
                       <tr>
                       <td class="textonormal" bgcolor="#DCEDF7" height="20">Subelemento de Despesa*</td>
											 <td class="textonormal">
											     <?php
                                # Pega os subelementos de despesa #
                                 $dbora = ConexaoOracle();
                                 $sqlSubE  = "SELECT A.CELED1ELE1, A.CELED2ELE2, A.CELED3ELE3, A.CELED4ELE4, ";
                                 $sqlSubE .= "       A.CSUBEDELEM, A.NSUBEDNOME ";
                                 $sqlSubE .= "  FROM SPOD.TBSUBELEMENTODESPESA A ";
                                 $sqlSubE .= " WHERE A.DEXERCANOR = $Ano ";
                                 # { Fausto Feitosa - 27/11/2007
                                 $sqlSubE .= " ORDER BY A.CELED1ELE1, A.CELED2ELE2, A.CELED3ELE3, A.CELED4ELE4, A.CSUBEDELEM ";
                                 # }
                                 /* Fausto Feitosa - 27/11/2007 - Retirado a pedido do Usuário
                                 $sqlSubE .= " AND A.CELED1ELE1 = 4 ";
                                 $sqlSubE .= " AND A.CELED2ELE2 = 4 ";
                                 $sqlSubE .= " AND A.CELED3ELE3 = 90 ";
                                 $sqlSubE .= " AND A.CELED4ELE4 = 52 ";
                                 */
                                 $resSubE  = $dbora->query($sqlSubE);
												         if( db::isError($resSubE) ){
                                     ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSubE");
                                 }else {
                                       echo "<select name=\"SubElemento\" class=\"textonormal\">\n";
                                       echo "	<option value=\"\">Selecione o Subelemento de Despesa...</option>\n";
                                       While($LinhaSubE = $resSubE->fetchRow()){
                                          if($SubElemento == ($LinhaSubE[0]."!".$LinhaSubE[1]."!".$LinhaSubE[2]."!".$LinhaSubE[3]."!".$LinhaSubE[4]."!".$LinhaSubE[5])){
                                                echo "<option value=\"$LinhaSubE[0]!$LinhaSubE[1]!$LinhaSubE[2]!$LinhaSubE[3]!$LinhaSubE[4]!$LinhaSubE[5]\" selected>$LinhaSubE[0].$LinhaSubE[1].$LinhaSubE[2].$LinhaSubE[3].$LinhaSubE[4] - $LinhaSubE[5]</option>";
                                          }else{
                                                echo "<option value=\"$LinhaSubE[0]!$LinhaSubE[1]!$LinhaSubE[2]!$LinhaSubE[3]!$LinhaSubE[4]!$LinhaSubE[5]\">$LinhaSubE[0].$LinhaSubE[1].$LinhaSubE[2].$LinhaSubE[3].$LinhaSubE[4] - $LinhaSubE[5]</option>";
                                          }
												               }
												               echo "</select>";
                                 }

                                 $dbora->disconnect();

                            ?>
                            </tr>
                     <?php } ?>
                     <!-- Fausto Feitosa -->
									</table>
								</td>
							</tr>
							<tr>
								<td class="textonormal" align="right">
									<input type="hidden" name="Botao">
									<input type="button" name="Incluir" value="Incluir" class="botao" onClick="javascript:enviar('Incluir');">
									<input type="button" name="Limpar" value="Limpar" class="botao" onClick="javascript:enviar('Limpar');">
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
<script language="javascript" type="">
<!--
document.CadParametroContabilIncluir.TipoMovimentacao.focus();
//-->
</script>
