<?php
#------------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabUnidadeOrcamentariaGerar.php
# Autor:    Roberta Costa
# Data:     20/12/2004
# Objetivo: Programa que ativa a atualização da tabela de Unidade Orçamentária
#-------------------------------------
# Alterado: Álvaro Faria
# Data:     02/01/2007 - Correções para mudança de ano
#                        Identação
#                        Padronização de cabeçalho
# Alterado: Rossana Lira
# Data:     21/12/2007 - Mudança do $Ano para 2008, forçando a integração em 2007
# Alterado: Ariston Cordeiro
# Data:     05/01/2009 - Mudança do $Ano para 2009
# Alterado: Ariston Cordeiro
# Data:     29/12/2009 - Alterando ferramenta para seleção via ferramenta do ano. Só será permitido a seleção do ano corrente ou do próximo ano.
#---------------------------------------
# OBS.:     Tabulação 2 espaços
#------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

echo "[".$_SESSION["AnoGeracaoUnidadeOrcamentaria"]."]";

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/oracle/tabelasbasicas/RotUnidadeOrcamentariaGerar.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Botao      = $_POST['Botao'];

	// Gravando o ano também na sessão para ser usado por RotUnidadeOrcamentariaGerar.php
	$Ano      = $_SESSION["AnoGeracaoUnidadeOrcamentaria"] = $_POST['Ano'];
	$UltimaData = $_POST['UltimaData'];
	$Qtd        = $_POST['Qtd'];
} else {
	$Erro       = $_GET['Erro'];
	$Mens       = $_GET['Mens'];
	$Tipo       = $_GET['Tipo'];
	$Mensagem   = urldecode($_GET['Mensagem']);
}

$AnoAtual = date("Y");

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabUnidadeOrcamentariaGerar.php";

if ($Botao == "Gerar") {
	$Url = "tabelasbasicas/RotUnidadeOrcamentariaGerar.php?NomePrograma=".urlencode($ErroPrograma)."";

	if(!in_array($Url,$_SESSION['GetUrl'])) {
		$_SESSION['GetUrl'][] = $Url;
	}

	// Não permitir anos incorretos
	if($Ano < $AnoAtual or $Ano > $AnoAtual + 1) {
		$Mens     = 1;
		$Tipo     = 2;
		$Mensagem = "Ano deve ser o ano corrente ou o próximo";
	} else {
		Redireciona($Url);
		exit;
	}
} elseif ($Botao == "") {
	// Valor padrão para $Ano é o ano atual.
	// Evitar colocar estas linhas para serem executadas após se clicar no botão "Gerar",
	// para evitar qualquer possível bug em que o ano mude após o usuário já ter selecionado o ano.
	if (is_null($Ano)) {
		if (!is_null($_SESSION["AnoGeracaoUnidadeOrcamentaria"])) {
			$Ano = $_SESSION["AnoGeracaoUnidadeOrcamentaria"];
		} else {
			// pegando ano da sessão, caso exista
			$Ano = $_SESSION["AnoGeracaoUnidadeOrcamentaria"] = $AnoAtual;
		}
	}

	$db     = Conexao();

	$sql    = "SELECT MAX(TUNIDOULAT) FROM SFPC.TBUNIDADEORCAMENTPORTAL";

	$result = $db->query($sql);

	if (PEAR::isError($result)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		$Linha = $result->fetchRow();

		if ($Linha[0] == "") {
			$UltimaData = "-";
		} else {
			$UltimaData = substr($Linha[0],8,2)."/".substr($Linha[0],5,2)."/".substr($Linha[0],0,4)." ".substr($Linha[0],11,8);
		}
	}

	# Pega a quantidade de linhas da tabela #
	$sql    = "SELECT COUNT(tunidoexer) FROM SFPC.TBUNIDADEORCAMENTPORTAL WHERE TUNIDOEXER = $Ano";

	$result = $db->query($sql);

	if (PEAR::isError($result)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		$Linha = $result->fetchRow();
		$Qtd   = $Linha[0];
	}

	$db->disconnect();
}

if ($Erro == 1) {
	$Mens     = 1;
	$Tipo     = 2;
	$Mensagem = "Erro ao tentar atualizar a base de dados";
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
	<!--
	function enviar(valor) {
		document.TabUnidadeOrcamentariaGerar.Botao.value=valor;
		document.TabUnidadeOrcamentariaGerar.submit();
	}
	<?php MenuAcesso(); ?>
	//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="TabUnidadeOrcamentariaGerar.php" method="post" name="TabUnidadeOrcamentariaGerar">
		<br><br><br><br><br>
		<table cellpadding="3" border="0" summary="">
			<!-- Caminho -->
			<tr>
				<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="2">
					<font class="titulo2">|</font>
					<a href="../index.php">
						<font color="#000000">Página Principal</font>
					</a> > Tabelas > Unidade Orçamentária > Gerar
				</td>
			</tr>
			<!-- Fim do Caminho-->
			<!-- Erro -->
			<?php
			if ($Mens == 1) {
				?>
				<tr>
					<td width="150"></td>
					<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
				</tr>
				<?php
			}
			?>
			<!-- Fim do Erro -->
			<!-- Corpo -->
			<tr>
				<td width="100"></td>
				<td class="textonormal">
					<table border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff">
						<tr>
							<td class="textonormal">
								<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" class="textonormal" summary="">
									<tr>
										<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
											GERAÇÃO DA TABELA DE UNIDADE ORÇAMENTÁRIA
										</td>
									</tr>
									<tr>
										<td class="textonormal">
											<p align="justify">
												Para fazer a atualização da tabela de Unidade Orçamentária do Portal de Compras a partir do SOFIN, selecione o ano apropriado e clique no botão "Gerar".
												<br/><br/>
												<b>AVISO:</b> Tenha certeza de que nenhuma Unidade Orçamentária do ano selecionado está sendo usada. Ao gerar, todas Unidades Orçamentárias existentes no ano selecionado serão deletadas. Também, verifique se as Unidades Orçamentárias inativas estão sendo informadas no sistema SPOD.
											</p>
										</td>
									</tr>
									<tr>
										<td>
											<table class="textonormal" border="0" align="left" class="caixa">
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20" align="right">Última Atualização:</td>
													<td class="textonormal"><?php echo $UltimaData; ?></td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20" align="right">Total de Unidades Orçamentárias <br/> existentes no ano selecionado:<br/>(<?php echo $Ano?>)</td>
													<td class="textonormal"><?php echo $Qtd; ?></td>
												</tr>
												<tr>
													<?php /* Ano. Só pode ser o corrente ou o próximo. */ ?>
													<td class="textonormal" bgcolor="#DCEDF7" height="20" align="right">Ano a ser gerado:</td>
													<td class="textonormal">
														<select name="Ano" size="1" onChange="javascript:enviar('');">
															<?php $AnoItem = $AnoAtual; ?>
															<option value="<?php echo $AnoItem?>" <?php if($Ano == $AnoItem){ echo "selected"; } ?>><?php echo $AnoItem?></option>
															<?php $AnoItem = $AnoAtual+1; ?>
															<option value="<?php echo $AnoItem?>" <?php if($Ano == $AnoItem){ echo "selected"; } ?>><?php echo $AnoItem?></option>
														</select>
													</td>
												</tr>
											</table>
										</td>
									</tr>
									<tr>
										<td class="textonormal" align="right">
											<input type="hidden" name="UltimaData" value="<?php echo $UltimaData; ?>">
											<input type="hidden" name="Qtd" value="<?php echo $Qtd; ?>">
											<input type="button" value="Gerar" class="botao" onclick="javascript:enviar('Gerar');">
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
<?php echo "[".$_SESSION["AnoGeracaoUnidadeOrcamentaria"]."]";?>