<?php
/*
Arquivo: TabFornecedorNaturezaJuridicaAlterar.php
Nome: Lucas André e Lucas Vicente
Data: 29/11/2022
Tarefa: CR 275539
----------------------------------------------------------------------------
Arquivo: TabFornecedorNaturezaJuridicaSelecionar.php
Nome: Lucas André
Data: 26/04/2023
Tarefa: CR 282152
----------------------------------------------------------------------------
*/

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/tabelasbasicas/TabFornecedorNaturezaJuridicaAlterar.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$CodNaturezaJuridica    = $_POST['CodNaturezaJuridica'];
	$Botao        		 	= $_POST['Botao'];
} else {
	$DescNaturezaJuridica 	= $_GET['DescNaturezaJuridica'];
	$CodNaturezaJuridica    = $_GET['CodNaturezaJuridica'];
	$Mensagem = urldecode($_GET['Mensagem']);
	$Mens     = $_GET['Mens'];
	$Tipo     = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if ($Botao == "Selecionar" ) {
	# Critica dos Campos #
	$Mens     = 0;
	$Mensagem = "Informe: ";

	if ($CodNaturezaJuridica == "") {
	    $Mens      = 1;
	    $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript: document.Lote.CodNaturezaJuridica.focus();\" class=\"titulo2\">Código da Natureza Jurídica</a>";
    } else {
    	$Url = "TabFornecedorNaturezaJuridicaAlterar.php?CodNaturezaJuridica=$CodNaturezaJuridica";

		if (!in_array($Url,$_SESSION['GetUrl'])) {
			$_SESSION['GetUrl'][] = $Url;
		}

		header("location: ".$Url);
	    exit();
    }
}
?>
<html>
	<?php
	# Carrega o layout padrão #
	layout();
	?>
	<script language="javascript">
		<!--
		function enviar(valor) {
			document.TabFornecedorNaturezaJuridicaSelecionar.Botao.value=valor;
			document.TabFornecedorNaturezaJuridicaSelecionar.submit();
		}

		<?php MenuAcesso(); ?>
		//-->
	</script>
	<link rel="stylesheet" type="text/css" href="../estilo.css">
	<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
		<script language="JavaScript" src="../menu.js"></script>
		<script language="JavaScript">Init();</script>
		<form action="TabFornecedorNaturezaJuridicaSelecionar.php" method="post" name="TabFornecedorNaturezaJuridicaSelecionar">
			<br><br><br><br><br>
			<table cellpadding="3" border="0">
  				<!-- Caminho -->
  				<tr>
    				<td width="150">
						<img border="0" src="../midia/linha.gif" alt="">
					</td>
    				<td align="left" class="textonormal" colspan="2">
      					<font class="titulo2">|</font>
      					<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Fornecedores > Natureza Jurídica > Manter
    				</td>
  				</tr>
  				<!-- Fim do Caminho-->
				<!-- Erro -->
				<?php
				if ($Mens == 1) {
					?>
					<tr>
	  					<td width="150"></td>
	  					<td align="left" colspan="2">
							<?php ExibeMens($Mensagem,$Tipo,1); ?>
						</td>
					</tr>
					<?php
				}
				?>
				<!-- Fim do Erro -->
				<!-- Corpo -->
				<tr>
					<td width="150"></td>
					<td class="textonormal">
						<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
        					<tr>
          						<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	        						MANTER - NATUREZA JURÍDICA
          						</td>
        					</tr>
        					<tr>
          						<td class="textonormal" bgcolor="#FFFFFF">
            						<p align="justify">
            						Para atualizar ou excluir uma natureza jurídica, selecione a mesma e clique em "Selecionar".
           							</p>
        						</td>
        					</tr>
        					<tr>
        						<td>
           							<table>
           								<tr>
              								<td class="textonormal" bgcolor="#DCEDF7" width="30%">Natureza Jurídica:</td>
               								<td class="textonormal">
               									<select name="CodNaturezaJuridica" class="textonormal">
               										<option value="">Selecione uma Natureza Jurídica</option>
                									<!-- Mostra as Naturezas Jurídicas cadastradas -->
                 									<?php
                									$db     = Conexao();

													$sql    = "SELECT AFORNJCODI, EFORNJTPNJ FROM SFPC.TBFORNECEDORTIPONATUREZAJURIDICA ORDER BY AFORNJCODI ASC";

													$result = $db->query($sql);

													if (db::isError($result)) {
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													} else {
														while ($Linha = $result->fetchRow()) {
		          	      									echo"<option value=\"$Linha[0]\">$Linha[0] - $Linha[1]</option>\n";
														}
			                						}

													$db->disconnect();
													?>
                  								</select>
               								</td>
            							</tr>
            						</table>
          						</td>
        					</tr>
        					<tr>
	      						<td align="right">
	      							<input type="button" value="Selecionar" class="botao" onClick="javascript:enviar('Selecionar');">
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
<script language="javascript">
	<!--
	document.Lote.CodNaturezaJuridica.focus();
	//-->
</script>