<?php
/**
 * Portal de Compras
 * 
 * Programa: TabSituacaoFornecedorPregaoPresencialExcluir.php
 * Autor:    Lucas Baracho
 * Data:     15/05/2017
 * Objetivo: Programa de exclusão da situação do fornecedor
 * ----------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     22/07/2019
 * Objetivo: Tarefa Redmine 217018
 * ----------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/tabelasbasicas/TabSituacaoFornecedorPregaoPresencialAlterar.php');
AddMenuAcesso('/tabelasbasicas/TabSituacaoFornecedorPregaoPresencialSelecionar.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD']  == "POST") {
	$Botao   		    = $_POST['Botao'];
	$SituacaoFornecedor = $_POST['SituacaoFornecedor'];
	$CodFornecedor      = $_POST['CodFornecedor'];	
} else {
	$SituacaoFornecedor = $_GET['SituacaoFornecedor'];
	$CodFornecedor      = $_GET['CodFornecedor'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Critica dos Campos #
$db = Conexao();

if ($Botao == "Voltar") {
	$Url = "TabSituacaoFornecedorPregaoPresencialAlterar.php?SituacaoFornecedor=$SituacaoFornecedor";

	if (!in_array($Url,$_SESSION['GetUrl'])) {
		$_SESSION['GetUrl'][] = $Url;
	}
	
	header("location: ".$Url);
	exit();
} elseif ($Botao == "Excluir") {
	$Mens     = 0;
    $Mensagem = "Informe: ";

	# Verifica se a situação tem algum fornecedor relacionado #
	$sql = "SELECT COUNT(CPRESFSEQU) FROM SFPC.TBPREGAOPRESENCIALCLASSIFICACAO WHERE CPRESFSEQU = " . $SituacaoFornecedor;

	$result = $db->query($sql);
	
	if (PEAR::isError($result)) {
		$db->query("ROLLBACK");
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		$Linha = $result->fetchRow();
		$Qtd   = $Linha[0];
		
		if ($Qtd > 0) {
			$Mensagem = "Exclusão Cancelada!<br>Situação relacionada com ($Qtd) fornecedor(es)";
			$Url = "TabSituacaoFornecedorPregaoPresencialSelecionar.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2";

			if (!in_array($Url,$_SESSION['GetUrl'])) {
				$_SESSION['GetUrl'][] = $Url;
			}
			
			header("location: ".$Url);
			exit();
		} else {
			# Exclui a situação #
			$db->query("BEGIN TRANSACTION");
	
			$sql = "DELETE FROM SFPC.TBPREGAOPRESENCIALSITUACAOFORNECEDOR WHERE CPRESFSEQU = " . $_SESSION['CodFornecedor'];

			$result = $db->query($sql);

			if (PEAR::isError($result)) {
				$db->query("ROLLBACK");
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			} else {
				$db->query("COMMIT");
				$db->query("END TRANSACTION");
				$db->disconnect();

				# Envia mensagem para página selecionar #
				$Mensagem = urlencode("Situação excluída com sucesso!");
				$Url = "TabSituacaoFornecedorPregaoPresencialSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";

				if (!in_array($Url,$_SESSION['GetUrl'])) {
					$_SESSION['GetUrl'][] = $Url;
				}

				header("location: ".$Url);
				exit();
			}
		}
	}
}
		
$db->disconnect();
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
	<!--
	function enviar(valor) {
		document.TabSituacaoFornecedorPregaoPresencialExcluir.Botao.value=valor;
		document.TabSituacaoFornecedorPregaoPresencialExcluir.submit();
	}
	<?php MenuAcesso(); ?>
	//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="TabSituacaoFornecedorPregaoPresencialExcluir.php" method="post" name="TabSituacaoFornecedorPregaoPresencialExcluir">
		<br><br><br><br><br>
		<table cellpadding="3" border="0">
			<!-- Caminho -->
			<tr>
				<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="2">
					<font class="titulo2">|</font>
					<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Pregão Presencial > Situação Fornecedor > Manter
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
				<td width="150"></td>
				<td class="textonormal">
					<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
        				<tr>
          					<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	           				EXCLUIR - SITUAÇÃO DO FORNECEDOR
          					</td>
        				</tr>
        				<tr>
          					<td class="textonormal" >
             					<p align="justify">
               					Para confirmar a exclusão da situação, clique no botão "Excluir", caso contrário clique no botão "Voltar".
             					</p>
          					</td>
        				</tr>
        				<tr>
          					<td>
            					<table>
									<tr>
                						<td class="textonormal" bgcolor="#DCEDF7" height="20">Situação do fornecedor:</td>
               							<td class="textonormal">
               								<?php echo $SituacaoFornecedor ?>
                							<input type="hidden" name="SituacaoFornecedor" value="<?php echo $SituacaoFornecedor; ?>">
                						</td>
              						</tr>
            					</table>
          					</td>
        				</tr>
        				<tr>
          					<td align="right">
          						<input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Excluir')">
          						<input type="button" value="Voltar"  class="botao" onclick="javascript:enviar('Voltar')">
								<input type="hidden" name="Botao" value="">
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