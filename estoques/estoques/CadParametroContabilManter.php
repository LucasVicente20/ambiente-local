<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadParametroContabilManter.php
# Objetivo: Programa de manutenção de parâmetros para a Contabilidade
# Autor:    Álvaro Faria
# Data:     28/12/2006
# Alterado: Álvaro Faria
# Data:     24/01/2006 - Liberação para alteração da movimentação 25 e 28
# Alterado: Carlos Abreu
# Data:     28/05/2007 - Liberaçao da escolha do tipo de material
# Alterado: Fausto Feitosa
# Data:     23/11/2007 - Exibição de um novo campo com o subelemento de despesa se o tipo material for permanente
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/CadParametroContabilExcluir.php' );

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao               = $_POST['Botao'];
		$TipoMovimentacao    = $_POST['TipoMovimentacao'];
		$Movimentacao        = $_POST['Movimentacao'];
		$TipoMaterial        = $_POST['TipoMaterial'];
		$AnoConta            = $_POST['AnoConta'];
		$NumeroConta         = $_POST['NumeroConta'];
		$Historico           = $_POST['Historico'];
		$TipoMovCont         = $_POST['TipoMovCont'];
		$Natureza            = $_POST['Natureza'];
		$Lote                = $_POST['Lote'];
    #{ Fausto Feitosa - 22/11/2007
    if ($TipoMaterial == "P") {
       $SubElemento      = $_POST['SubElemento'];
    } else {
       $SubElemento = "";
    }
    #}
    $TipoMovimentacaoANT = $_SESSION['TipoMovimentacaoANT'];
		$MovimentacaoANT     = $_SESSION['MovimentacaoANT'];
		$TipoMaterialANT     = $_SESSION['TipoMaterialANT'];
		$AnoContaANT         = $_SESSION['AnoContaANT'];
		$NumeroContaANT      = $_SESSION['NumeroContaANT'];
		$HistoricoANT        = $_SESSION['HistoricoANT'];
		$TipoMovContANT      = $_SESSION['TipoMovContANT'];
		$NaturezaANT         = $_SESSION['NaturezaANT'];
		$LoteANT             = $_SESSION['LoteANT'];
    #{ Fausto Feitosa - 22/11/2007
    $SubElementoANT      = $_SESSION['SubElementoANT'];
    #}
}else{
		$Mensagem            = urldecode($_GET['Mensagem']);
		$Mens                = $_GET['Mens'];
		$Tipo                = $_GET['Tipo'];
		$Troca               = $_GET['Troca'];
		$TipoMovimentacao    = $_GET['TipoMovimentacao'];
		$Movimentacao        = $_GET['Movimentacao'];
		$TipoMaterial        = $_GET['TipoMaterial'];
		$AnoConta            = $_GET['AnoConta'];
		$NumeroConta         = $_GET['NumeroConta'];
		$Historico           = $_GET['Historico'];
		$TipoMovCont         = $_GET['TipoMovCont'];
		$Natureza            = $_GET['Natureza'];
		$Lote                = $_GET['Lote'];
    #{ Fausto Feitosa 22/11/2007
    $SubElemento         = $_GET['SubElemento'];
    #}
		$TipoMovimentacaoANT = $_GET['TipoMovimentacao'];
		$MovimentacaoANT     = $_GET['Movimentacao'];
		$TipoMaterialANT     = $_GET['TipoMaterial'];
		$AnoContaANT         = $_GET['AnoConta'];
		$NumeroContaANT      = $_GET['NumeroConta'];
		$HistoricoANT        = $_GET['Historico'];
		$TipoMovContANT      = $_GET['TipoMovCont'];
		$NaturezaANT         = $_GET['Natureza'];
		$LoteANT             = $_GET['Lote'];
		#{ Fausto Feitosa - 22/11/2007
    $SubElementoANT      = $_GET['SubElemento'];
    #}
		# Segura os dados iniciais para serem usados no WHERE do Update #
		$_SESSION['TipoMovimentacaoANT'] = $TipoMovimentacaoANT;
		$_SESSION['MovimentacaoANT']     = $MovimentacaoANT;
		$_SESSION['TipoMaterialANT']     = $TipoMaterialANT;
		$_SESSION['AnoContaANT']         = $AnoContaANT;
		$_SESSION['NumeroContaANT']      = $NumeroContaANT;
		$_SESSION['HistoricoANT']        = $HistoricoANT;
		$_SESSION['TipoMovContANT']      = $TipoMovContANT;
		$_SESSION['NaturezaANT']         = $NaturezaANT;
		$_SESSION['LoteANT']             = $LoteANT;
		# { Fausto Feitosa - 22/11/2007
		$_SESSION['SubElementoANT']      = $SubElementoANT;
		# }
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadParametroContabilManter.php";

# Padrão que pode ser mudado durante o programa. Desta forma converte última vírgula da mensagem de erro por "e" #
if(!$Troca) $Troca = 1;

if($Botao == "Voltar"){
		header("location: CadParametroContabilSelecionar.php");
		exit;
}elseif($Botao == "Manter"){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if(!$TipoMovimentacao){
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadParametroContabilManter.TipoMovimentacao.focus();\" class=\"titulo2\">Tipo de Movimentação</a>";
		}
		if(!$Movimentacao){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadParametroContabilManter.Movimentacao.focus();\" class=\"titulo2\">Movimentação</a>";
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
				$Mensagem .= "<a href=\"javascript:document.CadParametroContabilManter.AnoConta.focus();\" class=\"titulo2\">Ano Conta Contábil</a>";
		}
		if(!$NumeroConta){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadParametroContabilManter.NumeroConta.focus();\" class=\"titulo2\">Número Conta Contábil</a>";
		}
		if(!$Historico){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadParametroContabilManter.Historico.focus();\" class=\"titulo2\">Histórico</a>";
		}
		if(!$TipoMovCont){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadParametroContabilManter.TipoMovCont.focus();\" class=\"titulo2\">Movimento Contábil</a>";
		}
		if(!$Natureza){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadParametroContabilManter.Natureza.focus();\" class=\"titulo2\">Natureza Lançamento</a>";
		}
		if(!$Lote){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadParametroContabilManter.Lote.focus();\" class=\"titulo2\">Lote Contábil</a>";
		}elseif(!SoNumeros($Lote) ){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadParametroContabilManter.Lote.focus();\" class=\"titulo2\">Lote Contábil válido</a>";
		}
		if ($TipoMaterial == "P" && !$SubElemento) {
       if($Mens == 1){ $Mensagem .= ", "; }
       $Mens      = 1;
       $Tipo      = 2;
       $Mensagem .= "<a href=\"javascript:document.CadParametroContabilManter.SubElemento.focus();\" class=\"titulo2\">Subelemento de despesa</a>";
    }
		if($Mens == 0){
				$db        = Conexao();
				
				# comentado para liberar alteração nos parametros colocados incorretamente
				
				
				# Verifica se já houve movimentações para o tipo destino especificado, no ano especificado #
				$sqlCheca  = "SELECT COUNT(*) FROM SFPC.TBMOVIMENTACAOMATERIAL ";
				$sqlCheca .= " WHERE CTIPMVCODI = $Movimentacao ";
				$sqlCheca .= "   AND AMOVMAANOM = $AnoConta";
				$resCheca  = $db->query($sqlCheca);
				if( db::isError($resCheca) ){
						$db->disconnect();
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlCheca");
				}else{
						$QtdMovsTipo = $resCheca->fetchRow();
						if($QtdMovsTipo[0] >= 1 and $Movimentacao != 33 and $Movimentacao != 34){
								$db->disconnect();
								$Mensagem = "Parâmetros não podem ser alterados para este tipo de movimentação no ano de $AnoConta, pois já existem movimentações deste tipo neste ano";
								$Url = "CadParametroContabilSelecionar.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2&Troca=2";
								if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
								header("location: ".$Url);
								exit;
						}else{
								# Verifica se já houve movimentações para o tipo origem especificado, no ano especificado #
								$sqlCheca  = "SELECT COUNT(*) FROM SFPC.TBMOVIMENTACAOMATERIAL ";
								$sqlCheca .= " WHERE CTIPMVCODI = $MovimentacaoANT ";
								$sqlCheca .= "   AND AMOVMAANOM = $AnoContaANT ";
								$resCheca  = $db->query($sqlCheca);
								if( db::isError($resCheca) ){
										$db->disconnect();
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlCheca");
								}else{
										$QtdMovsTipo = $resCheca->fetchRow();
										if($QtdMovsTipo[0] >= 1){
												$db->disconnect();
												$Mensagem = "Parâmetros deste tipo de movimentação no ano de $AnoConta não podem ser alterados, pois já existem movimentações deste tipo neste ano";
												$Url = "CadParametroContabilSelecionar.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2&Troca=2";
												if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
												header("location: ".$Url);
												exit;
										}else{
                        #{ Fausto Feitosa - 23/11/2007
                        # Retorna uma matriz de strings com os códigos de despesa.
                        $SubElementoArray = explode("!", $SubElemento);
                        #}
												# Verifica se os dados já estão no banco #
												$sqlCheca  = "SELECT COUNT(*) FROM SFPC.TBMOVCONTABILALMOXARIFADOPARAM ";
												$sqlCheca .= " WHERE AMVCPMANOC = $AnoConta  AND AMVCPMCONT = $NumeroConta ";
												$sqlCheca .= "   AND AMVCPMHIST = $Historico AND AMVCPMTPMC = $TipoMovCont ";
												$sqlCheca .= "   AND FMVCPMDBCD = '$Natureza'  AND AMVCPMLOTE = $Lote ";
												$sqlCheca .= "   AND CTIPMVCODI = $Movimentacao ";
												$sqlCheca .= "   AND FMVCPMTIPM = '$TipoMaterial' ";
												#{ Fausto Feitosa - 23/11/2007 - Atributos de subelemento
												if ($TipoMaterial == "P")  {
                           $sqlCheca .= "   AND CMVCPMELE1 = '$SubElementoArray[0]' AND CMVCPMELE2 = '$SubElementoArray[1]' ";
												   $sqlCheca .= "   AND CMVCPMELE3 = '$SubElementoArray[2]' AND CMVCPMELE4 = '$SubElementoArray[3]'";
												   $sqlCheca .= "   AND CMVCPMSUBE = '$SubElementoArray[4]' AND NMVCPMNOMS = '$SubElementoArray[5]'";
												}
                        # }
                        $resCheca  = $db->query($sqlCheca);
												if( db::isError($resCheca) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlCheca");
												}else{
														$QtdParametro = $resCheca->fetchRow();
														if($QtdParametro[0] >= 1){
																$db->disconnect();
																$Mensagem        = "A manutenção efetuada coincide com parâmetros já cadastrados anteriormente. A operação foi cancelada";
																$Url = "CadParametroContabilSelecionar.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2";
																if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																header("location: ".$Url);
																exit;
														}else{
                                #{ Fausto Feitosa - 23/11/2007
                                # Retorna uma matriz de strings com os códigos de despesa.
                                $SubElementoArrayANT = explode("!", $SubElementoANT);
                                #}
																$DataHora = date("Y-m-d H:i:s");
																# Caso não esteja, atualiza o banco #
																$sqlManter  = "UPDATE SFPC.TBMOVCONTABILALMOXARIFADOPARAM SET ";
																$sqlManter .= " AMVCPMANOC = $AnoConta, AMVCPMCONT = $NumeroConta,";
																$sqlManter .= " AMVCPMHIST = $Historico, AMVCPMTPMC = $TipoMovCont, ";
																$sqlManter .= " FMVCPMDBCD = '$Natureza', AMVCPMLOTE = $Lote, ";
																$sqlManter .= " CTIPMVCODI = $Movimentacao, FMVCPMTIPM = '$TipoMaterial', ";
																$sqlManter .= " TMVCPMULAT = '$DataHora', ";
																#{ Fausto Feitosa - 23/11/2007
																if ($TipoMaterial == "P") {
                                   $sqlManter .= " CMVCPMELE1 = '$SubElementoArray[0]', CMVCPMELE2 = '$SubElementoArray[1]', ";
                                   $sqlManter .= " CMVCPMELE3 = '$SubElementoArray[2]', CMVCPMELE4 = '$SubElementoArray[3]', ";
                                   $sqlManter .= " CMVCPMSUBE = '$SubElementoArray[4]', NMVCPMNOMS = '$SubElementoArray[5]' ";
                                } else {
                                   $sqlManter .= " CMVCPMELE1 = null, CMVCPMELE2 = null, ";
                                   $sqlManter .= " CMVCPMELE3 = null, CMVCPMELE4 = null, ";
                                   $sqlManter .= " CMVCPMSUBE = null, NMVCPMNOMS = null ";
                                }
                       					#}
																$sqlManter .= " WHERE AMVCPMANOC = $AnoContaANT AND AMVCPMCONT = $NumeroContaANT ";
																$sqlManter .= "   AND AMVCPMHIST = $HistoricoANT AND AMVCPMTPMC = $TipoMovContANT ";
																$sqlManter .= "   AND FMVCPMDBCD = '$NaturezaANT' AND AMVCPMLOTE = $LoteANT ";
																$sqlManter .= "   AND CTIPMVCODI = $MovimentacaoANT ";
																$sqlManter .= "   AND FMVCPMTIPM = '$TipoMaterialANT' ";
                                #{ Fausto Feitosa - 23/11/2007 - Atributos de subelemento
                                if ($TipoMaterialANT == "P") {
                                   $sqlCheca .= "   AND CMVCPMELE1 = '$SubElementoArrayANT[0]' AND CMVCPMELE2 = '$SubElementoArrayANT[1]' ";
                                   $sqlCheca .= "   AND CMVCPMELE3 = '$SubElementoArrayANT[2]' AND CMVCPMELE4 = '$SubElementoArrayANT[3]'";
                                   $sqlCheca .= "   AND CMVCPMSUBE = '$SubElementoArrayANT[4]' AND NMVCPMNOMS = '$SubElementoArrayANT[5]'";
                                }
												        # }
                                $resManter  = $db->query($sqlManter);
																if( db::isError($resManter) ){
																		$CodErroEmail  = $resManter->getCode();
																		$DescErroEmail = $resManter->getMessage();
																		$db->disconnect();
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlManter\n\n$DescErroEmail ($CodErroEmail)");
																}else{
																		# EXIBINDO MENSAGEM DE SUCESSO #
																		$db->disconnect();
																		$Mensagem = "Parâmetros alterados com sucesso";
																		$Url = "CadParametroContabilSelecionar.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
																		if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																		header("location: ".$Url);
																		exit;
																}
														}
												}
										}
								}
						}
				}
				$db->disconnect();
		}
}elseif($Botao == "Excluir"){
    #{ Fausto Feitosa - 23/11/2007
    $SubElementoEncode = urlencode($SubElemento);
    #}
		$Url = "CadParametroContabilExcluir.php?TipoMovimentacao=$TipoMovimentacao&Movimentacao=$Movimentacao&TipoMaterial=$TipoMaterial&AnoConta=$AnoConta&NumeroConta=$NumeroConta&Historico=$Historico&TipoMovCont=$TipoMovCont&Natureza=$Natureza&Lote=$Lote&SubElemento=$SubElementoEncode";
		if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit;
}
?>

<html>
<?
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.CadParametroContabilManter.Botao.value = valor;
	document.CadParametroContabilManter.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>

<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadParametroContabilManter.php" method="post" name="CadParametroContabilManter">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Estoques > Contabilidade > Parâmetros > Manter
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if($Mens == 1){?>
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
			<table border="0" cellspacing="0" cellpadding="3">
				<tr>
					<td class="textonormal">
						<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
									MANTER - PARÂMETROS
								</td>
							</tr>
							<tr>
								<td class="textonormal">
									<p align="justify">
										Para alterar um parâmetro, informe os dados abaixo e clique no botão "Manter". Os itens obrigatórios estão com *.
									</p>
								</td>
							</tr>
							<tr>
								<td>
									<table class="textonormal" border="0" align="left" class="caixa">
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo de Movimentação*</td>
											<td class="textonormal">
												<select name="TipoMovimentacao" class="textonormal" onchange="submit();">
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
														$sqlMov .= " WHERE FTIPMVTIPO = '$TipoMovimentacao' AND CTIPMVCODI NOT IN(3,5,7,8,18,19,20,31)";
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
												<select name="TipoMaterial" class="textonormal" onchange="submit();">
													<option value="">Selecione o Tipo de Material...</option>
													<option value="C" <?php if( $TipoMaterial == "C" ){ echo "selected"; }?>>CONSUMO</option>
													<option value="P" <?php if( $TipoMaterial == "P" ){ echo "selected"; }?>>PERMANENTE</option>
												</select>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7">Ano Conta Contabil*</td>
											<td class="textonormal">
												<input type="text" name="AnoConta" disabled value="<?php echo $AnoConta; ?>" size="4" maxlength="4" class="textonormal">
												<input type="hidden" name="AnoConta" value="<?php echo $AnoConta; ?>">
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Número Conta Contábil*</td>
											<td class="textonormal">
												<?php
												# Pega os Números de Conta no Oracle #
												$dbora     = ConexaoOracle();
												$sqlConta  = "SELECT APLCTACONT, NPLCTACONT";
												$sqlConta .= "  FROM SFCT.TBPLANOCONTAS ";
												$sqlConta .= " WHERE APLCTAANOC = $AnoConta";
												$sqlConta .= " ORDER BY APLCTACONT, NPLCTACONT ";
												$resConta   = $dbora->query($sqlConta);
												if( db::isError($resConta) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlConta");
												}else{
														echo "<select name=\"NumeroConta\" class=\"textonormal\" onChange=\"submit();\">\n";
														echo "	<option value=\"\">Selecione a Conta Contábil...</option>\n";
														while($LinhaConta = $resConta->fetchRow()){
																if( $NumeroConta == $LinhaConta[0] ){
																		echo "<option value=\"$LinhaConta[0]\" selected>$LinhaConta[0] - ".substr($LinhaConta[1],0,43)."</option>";
																}else{
																		echo "<option value=\"$LinhaConta[0]\">$LinhaConta[0] - ".substr($LinhaConta[1],0,43)."</option>";
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
																		echo "<option value=\"$LinhaHist[0]\" selected>$LinhaHist[0] - ".substr($LinhaHist[1],0,49)."</option>";
																}else{
																		echo "<option value=\"$LinhaHist[0]\">$LinhaHist[0] - ".substr($LinhaHist[1],0,49)."</option>";
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
                    *  Fausto Feitosa - 23/11/2007
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
                                 $sqlSubE .= " WHERE A.DEXERCANOR = $AnoConta ";
                                 # { Fausto Feitosa - 27/11/2007
                                 $sqlSubE .= " ORDER BY A.CELED1ELE1, A.CELED2ELE2, A.CELED3ELE3, A.CELED4ELE4, A.CSUBEDELEM ";
                                 # }
                                 /* Fausto Feitosa - 27/11/2007 - Retirado a pedido do usuário
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
									<input type="button" name="Manter" value="Manter" class="botao" onClick="javascript:enviar('Manter');">
									<input type="button" name="Excluir" value="Excluir" class="botao" onClick="javascript:enviar('Excluir');">
									<input type="button" name="Voltar" value="Voltar" class="botao" onClick="javascript:enviar('Voltar');">
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
