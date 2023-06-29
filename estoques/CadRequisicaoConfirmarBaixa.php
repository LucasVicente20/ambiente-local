<?php
# -----------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadRequisicaoConfirmarBaixa.php
# Autor:    Altamiro Pedrosa
# Data:     25/08/2005
# OBS.:     Tabulação 2 espaços
# -----------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     26/05/2006
# -----------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     12/09/2006 - Alteração do label dos campos para equivaler ao comprovante
# -----------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     24/11/2006 - Suporte ao include da rotina de Custo/Contabilidade
# -----------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     22/12/2006 - Alteração do limite da caixa de texto do nome do
#                        responsável para 70 caracteres
# -----------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     02/05/2008 - Alteração para enviar o ano da movimentação para ser armazenado na 
#                                  tabela SFCT.TBMOVCONTABILALMOXARIFADO do SOFIN.
# Objetivo: Programa de Confirmação da Baixa da Requisição
# -----------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 73662
# -----------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

$DataBaixa = date("Y-m-d");

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao          = $_POST['Botao'];
		$Matricula      = $_POST['Matricula'];
		$Responsavel    = strtoupper2(RetiraAcentos($_POST['Responsavel']));
		$SeqRequisicao  = $_POST['SeqRequisicao'];
		$Localizacao    = $_POST['Localizacao'];
		$Orgao          = $_POST['Orgao'];
		$Unidade        = $_POST['Unidade'];
		$RPA            = $_POST['RPA'];
		$CentroCusto    = $_POST['CentroCusto'];
		$Detalhamento   = $_POST['Detalhamento'];
		$ProgramaOrigem = $_POST['ProgramaOrigem'];
}else{
		$ProgramaOrigem = urldecode($_GET['ProgramaOrigem']);
		$SeqRequisicao  = $_GET['SeqRequisicao'];
		$Localizacao    = $_GET['Localizacao'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if ($Botao == "Confirmar") {
	$Mens     = 0;
	$Mensagem = "Informe: ";

	if ($Matricula == "") {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}

		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadRequisicaoConfirmarBaixa.Matricula.focus();\" class=\"titulo2\">Matrícula/Identidade</a>";
	} else {
		if (!SoNumeros($Matricula)) {
			if ($Mens == 1) {
				$Mensagem .= ", ";
			}

			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.CadRequisicaoConfirmarBaixa.Matricula.focus();\" class=\"titulo2\">Matrícula/Identidade Válida</a>";
		}
	}

	if ($Responsavel == "") {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}

		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadRequisicaoConfirmarBaixa.Responsavel.focus();\" class=\"titulo2\">Nome</a>";
	} elseif (!NomeSobrenome($Responsavel)) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}

		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript: document.CadMovimentacaoConfirmar.Responsavel.focus();\" class=\"titulo2\">Nome e Sobrenome</a>";
	}

	if ($Mens == 0) {
		# Resgata os valores na tabela de centro de custo para inclusão na tabela de custos do oracle #
		$db   = Conexao();

		$sql  = "SELECT DISTINCT CCENPOCORG, CCENPOUNID, CCENPONRPA, CCENPOCENT, CCENPODETA, C.AMOVMAANOM ";
		$sql .= "FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBREQUISICAOMATERIAL B, SFPC.TBMOVIMENTACAOMATERIAL C ";
		$sql .= "WHERE A.CCENPOSEQU = B.CCENPOSEQU AND B.CREQMASEQU = $SeqRequisicao AND C.CREQMASEQU = B.CREQMASEQU ";

		$result = $db->query($sql);

		if (PEAR::isError($result)) {
			$db->query("ROLLBACK");
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		} else {
			$Linha = $result->fetchRow();
			$Orgao           = $Linha[0];
			$Unidade         = $Linha[1];
			$RPA             = $Linha[2];
			$CentroCusto     = $Linha[3];
			$Detalhamento    = $Linha[4];
           	$AnoMovimentacao = $Linha[5];
		}

		$db->disconnect();

		$AnoBaixa = substr($DataBaixa,0,4);
		$MesBaixa = substr($DataBaixa,5,2);
		$DiaBaixa = substr($DataBaixa,8,2);

		if ($ProgramaOrigem == "CadRequisicaoBaixa") {
			echo "<script>opener.document.$ProgramaOrigem.Orgao.value=$Orgao</script>";
			echo "<script>opener.document.$ProgramaOrigem.Unidade.value=$Unidade</script>";
			echo "<script>opener.document.$ProgramaOrigem.RPA.value=$RPA</script>";
			echo "<script>opener.document.$ProgramaOrigem.CentroCusto.value=$CentroCusto</script>";
			echo "<script>opener.document.$ProgramaOrigem.Detalhamento.value=$Detalhamento</script>";
           	echo "<script>opener.document.$ProgramaOrigem.AnoMovimentacao.value=$AnoMovimentacao</script>";
		}

		echo "<script>opener.document.$ProgramaOrigem.DiaBaixa.value='$DiaBaixa'</script>";
		echo "<script>opener.document.$ProgramaOrigem.MesBaixa.value='$MesBaixa'</script>";
		echo "<script>opener.document.$ProgramaOrigem.AnoBaixa.value=$AnoBaixa</script>";
		echo "<script>opener.document.$ProgramaOrigem.Matricula.value=$Matricula</script>";
		echo "<script>opener.document.$ProgramaOrigem.Responsavel.value='$Responsavel'</script>";
		echo "<script>opener.document.$ProgramaOrigem.Botao.value='Baixou'</script>";
		echo "<script>opener.document.$ProgramaOrigem.submit()</script>";
		echo "<script>self.close();</script>";
	}
}
?>
<html>
	<head>
		<title>Portal de Compras - Efetuar Baixa da Requisição</title>
		<script language="javascript" type="">
			function enviar(valor) {
				document.CadRequisicaoConfirmarBaixa.Botao.value = valor;
				document.CadRequisicaoConfirmarBaixa.submit();
			}
		</script>
		<link rel="stylesheet" type="text/css" href="../estilo.css">
		<script language="javascript" src="../janela.js" type="text/javascript"></script>
	</head>
	<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
		<form action="CadRequisicaoConfirmarBaixa.php" method="post" name="CadRequisicaoConfirmarBaixa">
			<table cellpadding="0" border="0" summary="">
				<!-- Erro -->
				<tr>
					<td align="left" colspan="2">
						<?php
						if ($Mens != 0) {
							ExibeMens($Mensagem,$Tipo,$Virgula);
						}
						?>
					</td>
				</tr>
				<!-- Fim do Erro -->
				<!-- Corpo -->
				<tr>
					<td class="textonormal">
						<table border="0" cellspacing="0" cellpadding="3" summary="" width="100%">
							<tr>
								<td class="textonormal">
									<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" width="100%" class="textonormal" bgcolor="#FFFFFF" summary="">
										<tr>
											<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
												CONFIRMAÇÃO DA BAIXA DA REQUISIÇÃO
											</td>
										</tr>
										<tr>
											<td class="textonormal">
												<p align="justify">
													Para fechar a janela clique no botão "Voltar".<BR>
													Preencha com os dados do Recebedor do Material.
												</p>
											</td>
										</tr>
										<tr>
											<td>
												<table border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" width="100%" summary="">
													<tr>
														<td colspan="2">
															<table class="textonormal" border="0" width="100%" summary="">
																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" height="20">Matrícula/Identidade*</td>
																	<td class="textonormal">
																		<input type="text" name="Matricula" size="10" maxlength="10" class="textonormal"  value="<?php echo $Matricula; ?>">
																	</td>
																</tr>
																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" height="20">Nome*</td>
																	<td class="textonormal">
																		<input type="text" name="Responsavel" size="55" maxlength="70" class="textonormal"  value="<?php echo $Responsavel; ?>">
																	</td>
																</tr>
															</table>
														</td>
													</tr>
												</table>
											</td>
										</tr>
										<tr>
											<td colspan="2" align="right">
												<input type="hidden" name="ProgramaOrigem" value="<?php echo $ProgramaOrigem; ?>">
												<input type="hidden" name="Orgao" value="<?php echo $Orgao; ?>">
												<input type="hidden" name="Unidade" value="<?php echo $Unidade; ?>">
												<input type="hidden" name="RPA" value="<?php echo $RPA; ?>">
												<input type="hidden" name="CentroCusto" value="<?php echo $CentroCusto; ?>">
												<input type="hidden" name="Detalhamento" value="<?php echo $Detalhamento; ?>">
												<input type="hidden" name="Localizacao" value="<?php echo $Localizacao; ?>">
												<input type="hidden" name="SeqRequisicao" value="<?php echo $SeqRequisicao; ?>">
												<input type="button" name="Confirmar Baixa" value="Confirmar" class="botao" onClick="javascript:enviar('Confirmar');">
												<input type="button" value="Voltar" class="botao" onclick="javascript:self.close();">
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
		<script language="javascript" type="">
			window.focus();
		</script>
	</body>
</html>