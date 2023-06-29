<?php
/*
Arquivo: TabFornecedorNaturezaJuridicaAlterar.php
Nome: Lucas André e Lucas Vicente
Data: 29/11/2022
Tarefa: CR 275539
----------------------------------------------------------------------------
Arquivo: TabFornecedorNaturezaJuridicaExcluir.php
Nome: Lucas André
Data: 26/04/2023
Tarefa: CR 282152
----------------------------------------------------------------------------
*/

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/tabelasbasicas/TabFornecedorNaturezaJuridicaAlterar.php');
AddMenuAcesso('/tabelasbasicas/TabFornecedorNaturezaJuridicaSelecionar.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD']  == "POST") {
	$Botao   		    = $_POST['Botao'];
	$DescNaturezaJuridica = $_POST['NaturezaJuridica'];
	$CodNaturezaJuridica = $_POST['NaturezaJuridica'];
} else {
	$DescNaturezaJuridica = $_GET['NaturezaJuridica'];
	$CodNaturezaJuridica = $_GET['NaturezaJuridica'];
}

$CodNaturezaJuridica = $_SESSION['CodNaturezaJuridica'];
$DescNaturezaJuridica = $_SESSION['DescNaturezaJuridica'];

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Critica dos Campos #
$db = Conexao();

if ($Botao == "Voltar") {
	$Url = "TabFornecedorNaturezaJuridicaAlterar.php?NaturezaJuridica=$DescNaturezaJuridica";

	if (!in_array($Url,$_SESSION['GetUrl'])) {
		$_SESSION['GetUrl'][] = $Url;
	}
	
	header("location: ".$Url);
	exit();
} elseif ($Botao == "Excluir") {
	$Mens     = 0;
    $Mensagem = "Informe: ";

	# Verifica se a Natureza Juridica possui Fornecedores relacionados  #
	$sql = "SELECT COUNT(efornjtpnj) FROM SFPC.tbfornecedortiponaturezajuridica WHERE efornjtpnj = '$DescNaturezaJuridica'";

	$result = $db->query($sql);
	
	if (db::isError($result)) {
		$db->query("ROLLBACK");
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		$Linha = $result->fetchRow();
		$Qtd   = $Linha[0];
		
		if ($Qtd > 0) {
			$Mensagem = "Exclusão Cancelada!Natureza Juridica relacionada com ($Qtd) fornecedor(es)";
			$Url = "TabFornecedorNaturezaJuridicaSelecionar.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2";

			if (!in_array($Url,$_SESSION['GetUrl'])) {
				$_SESSION['GetUrl'][] = $Url;
			}
			
			header("location: ".$Url);
			exit();
		} else {
			# Exclui a Natureza Juridica #
			$db->query("BEGIN TRANSACTION");

			$sql = "DELETE FROM SFPC.tbfornecedortiponaturezajuridica WHERE afornjcodi = " . $_SESSION['CodNaturezaJuridica'];
			
			$result = $db->query($sql);

			if (db::isError($result)) {
				$db->query("ROLLBACK");
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			} else {
				$db->query("COMMIT");
				$db->query("END TRANSACTION");
				$db->disconnect();

				# Envia mensagem para página selecionar #
				$Mensagem = urlencode("Natureza Juridica excluída com sucesso!");
				$Url = "TabFornecedorNaturezaJuridicaSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";

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
			document.TabFornecedorNaturezaJuridicaExcluir.Botao.value=valor;
			document.TabFornecedorNaturezaJuridicaExcluir.submit();
		}
		<?php MenuAcesso(); ?>
		//-->
	</script>
	<link rel="stylesheet" type="text/css" href="../estilo.css">
	<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
		<script language="JavaScript" src="../menu.js"></script>
		<script language="JavaScript">Init();</script>
		<form action="TabFornecedorNaturezaJuridicaExcluir.php" method="post" name="TabFornecedorNaturezaJuridicaExcluir">
			<br><br><br><br><br>
			<table cellpadding="3" border="0">
				<!-- Caminho -->
				<tr>
					<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
					<td align="left" class="textonormal" colspan="2">
						<font class="titulo2">|</font>
						<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Fornecedor > Natureza Jurídica > Manter
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
	           					EXCLUIR - NATUREZA JURÍDICA
          						</td>
        					</tr>
        					<tr>
          						<td class="textonormal" >
             						<p align="justify">
               						Para confirmar a exclusão da Natureza Juridica, clique no botão "Excluir", caso contrário clique no botão "Voltar".
             						</p>
          						</td>
        					</tr>
        					<tr>
	          					<td>
            						<table>
										<tr>
	                						<td class="textonormal" bgcolor="#DCEDF7" height="20">Código da Natureza Jurídica:</td>
    	           							<td class="textonormal">
        	       								<?php echo $CodNaturezaJuridica; ?>
            	    							<input type="hidden" name="NaturezaJuridica" value="<?php $CodNaturezaJuridica ?>">
                							</td>
              							</tr>
										<tr>
                							<td class="textonormal" bgcolor="#DCEDF7" height="20">Natureza Jurídica:</td>
               								<td class="textonormal">
               									<?php echo $DescNaturezaJuridica; ?>
                								<input type="hidden" name="NaturezaJuridica" value="<?php $DescNaturezaJuridica ?>">
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