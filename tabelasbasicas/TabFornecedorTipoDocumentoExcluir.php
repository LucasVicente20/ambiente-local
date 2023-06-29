<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabFornecedorTipoDocumentoExcluir.php
# Autor:    Lucas Baracho
# Data:     17/08/2018
# Objetivo: Tarefa Redmine 201304
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso ('/tabelasbasicas/TabTramitacaoAcaoAlterar.php');
AddMenuAcesso ('/tabelasbasicas/TabTramitacaoAcaoSelecionar.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD']  == "POST") {
	$Botao	 = $_POST['Botao'];
	$Critica = $_POST['Critica'];
	$Codigo	 = $_POST['Codigo'];
} else {
	$Codigo = $_GET['Codigo'];
}

# Critica dos Campos #
if ($Botao == "Voltar") {
	$Url = "TabFornecedorTipoDocumentoAlterar.php?Codigo=$Codigo";
	
	if (!in_array($Url,$_SESSION['GetUrl'])) {
		$_SESSION['GetUrl'][] = $Url;
	}
	header("location: ".$Url);
	exit();
} else {
	$Mens     = 0;
	$Mensagem = "Informe: ";

	# Verifica se está relacionada com algum documento #
	$db = Conexao();
		
	$sql = "SELECT	COUNT(*)FROM SFPC.TBFORNECEDORDOCUMENTO WHERE CFDOCTCODI = $Codigo";
		
	$result = $db->query($sql);
		
	if (PEAR::isError($result)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		$Linha = $result->fetchRow();
			
		$qtdProceso = $Linha[0];
			
		if ($qtdProceso > 0) {
			$Mensagem = urlencode("Exclusão cancelada!<br>Tipo relacionado com ($qtdProceso) documento(s)");
			$Url      = "TabFornecedorTipoDocumentoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=2";
				
			if (!in_array($Url,$_SESSION['GetUrl'])) {
				$_SESSION['GetUrl'][] = $Url;
			}
			header("location: ".$Url);
			exit();
		}
					
		if ($Mens == 0) {
			# Exclui ações #
			$db = Conexao();
				
			$db->query("BEGIN TRANSACTION");
				
			$sql = "DELETE
					FROM	SFPC.TBFORNECEDORDOCUMENTOTIPO
					WHERE	CFDOCTCODI = $Codigo";
				
			$result = $db->query($sql);
				
			if (PEAR::isError($result)) {
				$db->query("ROLLBACK");
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			} else {
				$db->query("COMMIT");
				$db->query("END TRANSACTION");
				$db->disconnect();

				# Envia mensagem para página selecionar #
				$Mensagem = urlencode("Tipo de documento excluído com sucesso");
				$Url      = "TabFornecedorTipoDocumentoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
					
				if (!in_array($Url,$_SESSION['GetUrl'])) {
					$_SESSION['GetUrl'][] = $Url;
				}
				header("location: ".$Url);
				exit();
			}
			$db->disconnect();
		}	
	}
}
	
if ($Critica == 0) {
	$db = Conexao();

	$sql = "SELECT	CFDOCTCODI, EFDOCTDESC, FFDOCTSITU
			FROM	SFPC.TBFORNECEDORDOCUMENTOTIPO
			WHERE	CFDOCTCODI = $Codigo";
	
	$result = $db->query($sql);
	
	if (PEAR::isError($result)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		while ($Linha = $result->fetchRow()) {
			$Descricao = $Linha[1];
			$Situacao  = $Linha[5];
		}
	}
	$db->disconnect();
}

?>

<html>
	<?php 
		# Carrega o layout padrão #
		layout();
	?>
	<script language="javascript" type="">
		<!--
			function enviar(valor){
				document.TipoDocumento.Botao.value=valor;
				document.TipoDocumento.submit();
			}
			<?php  MenuAcesso(); ?>
		//-->
	</script>
	<link rel="stylesheet" type="text/css" href="../estilo.css">
	<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
		<script language="JavaScript" src="../menu.js"></script>
		<script language="JavaScript">Init();</script>
		<form action="TabFornecedorTipoDocumentoExcluir.php" method="post" name="TipoDocumento">
			<br><br><br><br><br>
			<table cellpadding="3" border="0">
				<!-- Caminho -->
				<tr>
					<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    				<td align="left" class="textonormal">
      					<font class="titulo2">|</font>
      					<a href="../index.php">
						<font color="#000000">Página Principal</font></a> > Tabelas > Fornecedores > Documentos > Tipo Documento > Manter > Excluir
					</td>
				</tr>
				<!-- Fim do Caminho-->
				<!-- Erro -->
				<?php  if ( $Mens == 1 ) {?>
  				<tr>
  					<td width="150"></td>
					<td align="left" colspan="2"><?php  ExibeMens($Mensagem,$Tipo,1); ?></td>
				</tr>
				<?php  } ?>
				<!-- Fim do Erro -->
				<!-- Corpo -->
				<tr>
					<td width="150"></td>
					<td class="textonormal">
						<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#ffffff">
        					<tr>
          						<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">EXCLUIR - TIPO DE DOCUMENTO</td>
        					</tr>
        					<tr>
          						<td class="textonormal">
             						<p align="justify">
               							Para confirmar a exclusão da ação, clique no botão "Excluir", caso contrário clique no botão "Voltar".
             						</p>
          						</td>
        					</tr>
        					<tr>
          						<td>
            						<table border="0">										
            							<tr>
                							<td class="textonormal" bgcolor="#DCEDF7" height="20">Descrição</td>
               								<td class="textonormal"><?php  echo $Descricao; ?>
    	            							<input type="hidden" name="Critica" value="1">
        	        							<input type="hidden" name="Codigo" value="<?php  echo $Codigo; ?>">
                							</td>
              							</tr>
              							<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Situação</td>
											<td class="textonormal"><?php  echo ($Situacao == 'A') ? 'Ativo' : 'Inativo'; ?></td>
										</tr>
            						</table>
          						</td>
        					</tr>
        					<tr>
 	        					<td class="textonormal" align="right">
          							<input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Excluir');">
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