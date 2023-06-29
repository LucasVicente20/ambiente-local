<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadParametroContabilExcluir.php
# Autor:    Álvaro Faria
# Data:     28/12/2006
# Objetivo: Programa para confirmar exclusão de parâmetros para a Contabilidade
# Autor:    Fausto Feitosa
# Data:     26/11/2007
# Objetivo: Exibir o campo de subelemento de despesa, caso o tipo material seja permanente.
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
		#{ Fausto Feitosa - 26/11/2007
    if ($TipoMaterial == "P") {
       $SubElemento      = $_POST['SubElemento'];
    }
    #}
}else{
		$TipoMovimentacao = $_GET['TipoMovimentacao'];
		$Movimentacao     = $_GET['Movimentacao'];
		$TipoMaterial     = $_GET['TipoMaterial'];
		$AnoConta         = $_GET['AnoConta'];
		$NumeroConta      = $_GET['NumeroConta'];
		$Historico        = $_GET['Historico'];
		$TipoMovCont      = $_GET['TipoMovCont'];
		$Natureza         = $_GET['Natureza'];
		$Lote             = $_GET['Lote'];
		#{ Fausto Feitosa 26/11/2007
    $SubElemento      = $_GET['SubElemento'];
    #}

}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadParametroContabilExcluir.php";

if($Botao == "Voltar"){
		header("location: CadParametroContabilSelecionar.php");
		exit;
}elseif($Botao == "Excluir"){
		$db        = Conexao();
		# Verifica se já houve movimentações para o tipo especificado no ano especificado #
		$sqlCheca  = "SELECT COUNT(*) FROM SFPC.TBMOVIMENTACAOMATERIAL ";
		$sqlCheca .= " WHERE CTIPMVCODI = $Movimentacao ";
		$sqlCheca .= "   AND AMOVMAANOM = $AnoConta ";
		$resCheca  = $db->query($sqlCheca);
		if( db::isError($resCheca) ){
				$db->disconnect();
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlCheca");
		}else{
				$QtdMovsTipo = $resCheca->fetchRow();
				if($QtdMovsTipo[0] >= 1 and $Movimentacao != 33 and $Movimentacao != 34){
						$Mensagem = "Operação Cancelada. Parâmetros de movimentações já utilizadas no ano especificado não podem ser excluídos";
						$Url = "CadParametroContabilSelecionar.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2";
						if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						header("location: ".$Url);
						exit;
				}else{
						# Caso não exista movimentações, apaga do banco #
						$sqlmanter  = "DELETE FROM SFPC.TBMOVCONTABILALMOXARIFADOPARAM ";
						$sqlmanter .= " WHERE AMVCPMANOC = $AnoConta  AND AMVCPMCONT = $NumeroConta ";
						$sqlmanter .= "   AND AMVCPMHIST = $Historico AND AMVCPMTPMC = $TipoMovCont ";
						$sqlmanter .= "   AND FMVCPMDBCD = '$Natureza'  AND AMVCPMLOTE = $Lote ";
						$sqlmanter .= "   AND CTIPMVCODI = $Movimentacao ";
						$sqlmanter .= "   AND FMVCPMTIPM = '$TipoMaterial' ";
						# { Fausto Feitosa - 26/11/07
						if ($TipoMaterial == "P") {
               $SubElementoArray = explode("!", $SubElemento);
               $sqlmanter .= "   AND CMVCPMELE1 = '$SubElementoArray[0]' AND CMVCPMELE2 = '$SubElementoArray[1]' ";
               $sqlmanter .= "   AND CMVCPMELE3 = '$SubElementoArray[2]' AND CMVCPMELE4 = '$SubElementoArray[3]' ";
               $sqlmanter .= "   AND CMVCPMSUBE = '$SubElementoArray[4]' AND NMVCPMNOMS = '$SubElementoArray[5]' ";
            }
            #}
            $resmanter  = $db->query($sqlmanter);
						if( db::isError($resmanter) ){
								$db->disconnect();
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmanter");
						}else{
								# EXIBINDO MENSAGEM DE SUCESSO #
								$db->disconnect();
								$Mensagem = "Parâmetros excluídos com sucesso";
								$Url = "CadParametroContabilSelecionar.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
								if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
								header("location: ".$Url);
								exit;
						}
				}
		}
		$db->disconnect();
}else{
		# Carrega os dados do parâmetro selecionado #
		if($TipoMovimentacao == "E"){
				$TipoMovimentacaoDesc = "ENTRADA";
		}elseif($TipoMovimentacao == "S"){
				$TipoMovimentacaoDesc = "SAÍDA";
		}
		if($TipoMaterial == "C"){
				$TipoMaterialDesc = "CONSUMO";
		}elseif($TipoMaterial == "P"){
				$TipoMaterialDesc = "PERMANENTE";
		}
		if($Natureza == "D"){
				$NaturezaDesc = "DÉBITO";
		}elseif($Natureza == "C"){
				$NaturezaDesc = "CRÉDITO";
		}
		$db      = Conexao();
		$sqlMov  = "SELECT ETIPMVDESC FROM SFPC.TBTIPOMOVIMENTACAO ";
		$sqlMov .= " WHERE CTIPMVCODI = $Movimentacao ";
		$resMov  = $db->query($sqlMov);
		if( db::isError($resMov) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlMov");
		}else{
				$LinhaMov         = $resMov->fetchRow();
				$MovimentacaoDesc = $LinhaMov[0];
		}
		$db->disconnect();
		$dbora     = ConexaoOracle();
		# Pega os dados do Número de Conta no Oracle #
		$sqlConta  = "SELECT NPLCTACONT";
		$sqlConta .= "  FROM SFCT.TBPLANOCONTAS ";
		$sqlConta .= " WHERE APLCTAANOC = $AnoConta AND APLCTACONT = $NumeroConta ";
		$resConta   = $dbora->query($sqlConta);
		if( db::isError($resConta) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlConta");
		}else{
				$LinhaConta      = $resConta->fetchRow();
				$NumeroContaDesc = $NumeroConta." - ".$LinhaConta[0];
				# Pega os dados do Histórico no Oracle #
				$sqlHist  = "SELECT XTEXTCCONT ";
				$sqlHist .= "  FROM SFCT.TBTEXTOCONTABIL ";
				$sqlHist .= " WHERE ATEXTCNUME = $Historico ";
				$resHist  = $dbora->query($sqlHist);
				if( db::isError($resHist) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlHist");
				}else{
						$LinhaHist     = $resHist->fetchRow();
						$HistoricoDesc = $Historico." - ".$LinhaHist[0];
				}
		}
		$dbora->disconnect();
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
	document.CadParametroContabilExcluir.Botao.value = valor;
	document.CadParametroContabilExcluir.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>

<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadParametroContabilExcluir.php" method="post" name="CadParametroContabilExcluir">
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
									EXCLUIR - PARÂMETROS
								</td>
							</tr>
							<tr>
								<td class="textonormal">
									<p align="justify">
										Para excluir o parâmetro, confirme no botão "Excluir".
									</p>
								</td>
							</tr>
							<tr>
								<td>
									<table class="textonormal" border="0" align="left" class="caixa">
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo de Movimentação</td>
											<td class="textonormal">
												<?php echo $TipoMovimentacaoDesc; ?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Movimentação</td>
											<td class="textonormal">
												<?php echo $MovimentacaoDesc; ?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo do Material</td>
											<td class="textonormal">
												<?php echo $TipoMaterialDesc; ?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Ano Conta Contabil</td>
											<td class="textonormal">
												<?php echo $AnoConta; ?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Número Conta Contábil</td>
											<td class="textonormal">
												<?php echo $NumeroContaDesc; ?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Histórico</td>
											<td class="textonormal">
												<?php echo $HistoricoDesc; ?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Natureza Lançamento</td>
											<td class="textonormal">
												<?php echo $NaturezaDesc; ?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Número Lote Contabil</td>
											<td class="textonormal">
												<?php echo $Lote; ?>
											</td>
										</tr>
										<?php
										# { Fausto Feitosa - 26/11/2007
										if ($TipoMaterial == "P") {
										$SubElementoArray = explode("!", $SubElemento);
										echo "<tr>";
                        echo "<td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\">Subelemento de Despesa</td>";
                        echo "<td class=\"textonormal\">";
                        echo $SubElementoArray[0].".".$SubElementoArray[1].".".$SubElementoArray[2].".".$SubElementoArray[3].".".$SubElementoArray[4]." - ".$SubElementoArray[5];
											  echo "</td>";
										echo "</tr>";
										}
										# }
                    ?>
									</table>
								</td>
							</tr>
							<tr>
								<td class="textonormal" align="right">
									<input type="hidden" name="Botao">
									<input type="button" name="Excluir" value="Excluir" class="botao" onClick="javascript:enviar('Excluir');">
									<input type="button" name="Voltar" value="Voltar" class="botao" onClick="javascript:enviar('Voltar');">
									<input type="hidden" name="TipoMovimentacao" value="<?php echo $TipoMovimentacao; ?>">
									<input type="hidden" name="Movimentacao" value="<?php echo $Movimentacao; ?>">
									<input type="hidden" name="TipoMaterial" value="<?php echo $TipoMaterial; ?>">
									<input type="hidden" name="AnoConta" value="<?php echo $AnoConta; ?>">
									<input type="hidden" name="NumeroConta" value="<?php echo $NumeroConta; ?>">
									<input type="hidden" name="Historico" value="<?php echo $Historico; ?>">
									<input type="hidden" name="TipoMovCont" value="<?php echo $TipoMovCont; ?>">
									<input type="hidden" name="Natureza" value="<?php echo $Natureza; ?>">
									<input type="hidden" name="Lote" value="<?php echo $Lote; ?>">
                  <?php
                  #{ Fausto Feitosa 26/11/2007
                  # Se o tipo for do tipo material pendente mostra o campo de subelemento de despesa.
                  if ($TipoMaterial == "P") {
                  		echo "<input type=\"hidden\" name=\"SubElemento\" value=\"$SubElemento\">";
                  }
                  #}
                  ?>
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
