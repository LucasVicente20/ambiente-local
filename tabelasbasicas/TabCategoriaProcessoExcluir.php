<?php
/*
Arquivo: TabCategoriaProcessoExcluir.php
Nome: Lucas André
Data: 27/04/2023
Tarefa: CR 282318
----------------------------------------------------------------------------
*/

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/tabelasbasicas/TabCategoriaProcessoAlterar.php');
AddMenuAcesso('/tabelasbasicas/TabCategoriaProcessoSelecionar.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD']  == "POST") {
	$Botao   		    = $_POST['Botao'];
	$DescCategoriaProcesso = $_POST['DescCategoriaProcesso'];
	$CodCategoriaProcesso = $_POST['CodCategoriaProcesso'];
} else {
	$DescCategoriaProcesso = $_GET['DescCategoriaProcesso'];
	$CodCategoriaProcesso = $_GET['CodCategoriaProcesso'];
}

$CodCategoriaProcesso = $_SESSION['CodCategoriaProcesso'];
$DescCategoriaProcesso = $_SESSION['DescCategoriaProcesso'];

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Critica dos Campos #
$db = Conexao();

if ($Botao == "Voltar") {
	$Url = "TabCategoriaProcessoAlterar.php?CategoriaProcesso=$DescCategoriaProcesso";

	if (!in_array($Url,$_SESSION['GetUrl'])) {
		$_SESSION['GetUrl'][] = $Url;
	}
	
	header("location: ".$Url);
	exit();
} elseif ($Botao == "Excluir") {
	$Mens     = 0;
    $Mensagem = "Informe: ";

	# Verifica se a Categoria do Processo possui contratos relacionados  #
	$sql = "SELECT COUNT(*) FROM sfpc.tbcontratosfpc WHERE cpnccpcodi = " .$CodCategoriaProcesso;

	$result = $db->query($sql);
	
	if (db::isError($result)) {
		$db->query("ROLLBACK");
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		$Linha = $result->fetchRow();
		$Qtd   = $Linha[0];
		
		if ($Qtd > 0) {
			$Mensagem = "Exclusão Cancelada!Categoria relacionada com ($Qtd) contrato(s)";
			$Url = "TabCategoriaProcessoSelecionar.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2";

			if (!in_array($Url,$_SESSION['GetUrl'])) {
				$_SESSION['GetUrl'][] = $Url;
			}
			
			header("location: ".$Url);
			exit();
		} else {
			# Exclui a Categoria #
			$db->query("BEGIN TRANSACTION");

			$sql = "DELETE FROM sfpc.tbpncpdominiocategoriaprocesso WHERE cpnccpcodi = " . $_SESSION['CodCategoriaProcesso'];
			
			$result = $db->query($sql);

			if (db::isError($result)) {
				$db->query("ROLLBACK");
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			} else {
				$db->query("COMMIT");
				$db->query("END TRANSACTION");
				$db->disconnect();

				# Envia mensagem para página selecionar #
				$Mensagem = urlencode("Categoria de Processo excluída com sucesso!");
				$Url = "TabCategoriaProcessoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";

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
			document.TabCategoriaProcessoExcluir.Botao.value=valor;
			document.TabCategoriaProcessoExcluir.submit();
		}
		<?php MenuAcesso(); ?>
		//-->
	</script>
	<link rel="stylesheet" type="text/css" href="../estilo.css">
	<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
		<script language="JavaScript" src="../menu.js"></script>
		<script language="JavaScript">Init();</script>
		<form action="TabCategoriaProcessoExcluir.php" method="post" name="TabCategoriaProcessoExcluir">
			<br><br><br><br><br>
			<table cellpadding="3" border="0">
				<!-- Caminho -->
				<tr>
					<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
					<td align="left" class="textonormal" colspan="2">
						<font class="titulo2">|</font>
						<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > PNCP > Contratos > Categoria do Processo > Manter
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
	           					EXCLUIR - CATEGORIA DO PROCESSO
          						</td>
        					</tr>
        					<tr>
          						<td class="textonormal" >
             						<p align="justify">
               						Para confirmar a exclusão da Categoria do Processo, clique no botão "Excluir", caso contrário clique no botão "Voltar".
             						</p>
          						</td>
        					</tr>
        					<tr>
	          					<td>
            						<table>
										<tr>
	                						<td class="textonormal" bgcolor="#DCEDF7" height="20">Código da Categoria do Processo:</td>
    	           							<td class="textonormal">
        	       								<?php echo $CodCategoriaProcesso; ?>
            	    							<input type="hidden" name="CodCategoriaProcesso" value="<?php $CodCategoriaProcesso ?>">
                							</td>
              							</tr>
										<tr>
                							<td class="textonormal" bgcolor="#DCEDF7" height="20">Categoria do Processo:</td>
               								<td class="textonormal">
               									<?php echo $DescCategoriaProcesso; ?>
                								<input type="hidden" name="DescCategoriaProcesso" value="<?php $DescCategoriaProcesso ?>">
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