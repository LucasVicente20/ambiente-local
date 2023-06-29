<?php

/*
Arquivo: TabCriterioDeJulgamentoAlterar.php
Nome: Lucas André e Lucas Vicente
Data: 
Tarefa: CR 276712

*/

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/tabelasbasicas/TabCriterioDeJulgamentoAlterar.php');
AddMenuAcesso('/tabelasbasicas/TabCriterioDeJulgamentoSelecionar.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD']  == "POST") {
	$Botao   		    = $_POST['Botao'];
	$DescCriterioDeJulgamento = $_POST['CriterioDeJulgamento'];
	$CodCriterioDeJulgamento = $_POST['CriterioDeJulgamento'];
} else {
	$DescCriterioDeJulgamento = $_GET['CriterioDeJulgamento'];
	$CodCriterioDeJulgamento = $_GET['CriterioDeJulgamento'];
}
$CodCriterioDeJulgamento = $_SESSION['CodCriterioDeJulgamento'];
$_SESSION['DescCriterioDeJulgamento'] = $DescCriterioDeJulgamento;

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Critica dos Campos #
$db = Conexao();

if ($Botao == "Voltar") {
	$Url = "TabCriterioDeJulgamentoAlterar.php?CriterioDeJulgamento=$DescCriterioDeJulgamento";
	if (!in_array($Url,$_SESSION['GetUrl'])) {
		$_SESSION['GetUrl'][] = $Url;
	}
	
	header("location: ".$Url);
	exit();
} elseif ($Botao == "Excluir") {
	$Mens     = 0;
    $Mensagem = "Informe: ";

	# Verifica se o criterio de julgamento possui Licitações relacionados  #
	$sql = "SELECT COUNT(ccrjulcodi) FROM SFPC.tblicitacaoportal WHERE ccrjulcodi = $CodCriterioDeJulgamento";

	$result = $db->query($sql);
	
	if (db::isError($result)) {
		$db->query("ROLLBACK");
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		$Linha = $result->fetchRow();
		$Qtd   = $Linha[0];
		
		
		if ($Qtd > 0) {
			$Mensagem = "Exclusão Cancelada! O Criterio de Julgamento está relacionado com $Qtd licitação(es)";
			$Url = "TabCriterioDeJulgamentoSelecionar.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2";
			
			if (!in_array($Url,$_SESSION['GetUrl'])) {
				$_SESSION['GetUrl'][] = $Url;
			}
			
			header("location: ".$Url);
			exit();
		} else {
			# Exclui o criterio de julgamento #
			$db->query("BEGIN TRANSACTION");
			$sql = "DELETE FROM SFPC.tbcriteriojulgamento WHERE ccrjulcodi = " . $_SESSION['CodCriterioDeJulgamento'];
	
			$result = $db->query($sql);

			if (db::isError($result)) {
				$db->query("ROLLBACK");
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			} else {
				$db->query("COMMIT");
				$db->query("END TRANSACTION");
				$db->disconnect();

				# Envia mensagem para página selecionar #
				$Mensagem = urlencode("Critério de Julgamento excluído com sucesso!");
				$Url = "TabCriterioDeJulgamentoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";

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
		document.TabCriterioDeJulgamentoExcluir.Botao.value=valor;
		document.TabCriterioDeJulgamentoExcluir.submit();
	}
	<?php MenuAcesso(); ?>
	//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="TabCriterioDeJulgamentoExcluir.php" method="post" name="TabCriterioDeJulgamentoExcluir">
		<br><br><br><br><br>
		<table cellpadding="3" border="0">
			<!-- Caminho -->
			<tr>
				<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="2">
					<font class="titulo2">|</font>
					<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Fornecedor > Critério De Julgamento > Excluir
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
	           				EXCLUIR - CRITÉRIO DE JULGAMENTO
          					</td>
        				</tr>
        				<tr>
          					<td class="textonormal" >
             					<p align="justify">
               					Para confirmar a exclusão do Critério de Julgamento, clique no botão "Excluir", caso contrário clique no botão "Voltar".
             					</p>
          					</td>
        				</tr>
        				<tr>
          					<td>
            					<table>
									<tr>
                						<td class="textonormal" bgcolor="#DCEDF7" height="20">Critério De Julgamento:</td>
               							<td class="textonormal">
               								<?php echo $DescCriterioDeJulgamento; ?>
                							<input type="hidden" name="CriterioDeJulgamento" value="<?php $DescCriterioDeJulgamento ?>">
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