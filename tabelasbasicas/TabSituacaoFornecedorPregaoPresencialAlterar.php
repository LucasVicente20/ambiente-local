<?php
/**
 * Portal de Compras
 * 
 * Programa: TabSituacaoFornecedorPregaoPresencialAlterar.php
 * Autor:    Lucas Baracho
 * Data:     22/07/2019
 * Objetivo: Programa de alteração da situação do fornecedor
 */

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/tabelasbasicas/TabSituacaoLotePregaoPresencialExcluir.php');
AddMenuAcesso('/tabelasbasicas/TabSituacaoLotePregaoPresencialSelecionar.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Botao          	= $_POST['Botao'];
	$SituacaoFornecedor	= strtoupper2(trim($_POST['SituacaoFornecedor']));
	$CodFornecedor  	= $_POST['CodFornecedor'];
} else {
	$CodFornecedor 			   = $_GET['CodFornecedor'];
	$_SESSION['CodFornecedor'] = $CodFornecedor;
}

if ($CodFornecedor > 0) {
	$db = Conexao();

	$sql = "SELECT EPRESFNOME FROM SFPC.TBPREGAOPRESENCIALSITUACAOFORNECEDOR WHERE CPRESFSEQU = $CodFornecedor";

	$result = $db->query($sql);

	if (PEAR::isError($result)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		$Linha = $result->fetchRow();
	}

	$_SESSION['SituacaoFornecedor'] = $Linha[0];
	
	$db->disconnect();
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

$db = Conexao();

$CodigoUsuario = $_SESSION['_cusupocodi_'];

if ($Botao == "Excluir") {
	$Url = "TabSituacaoFornecedorPregaoPresencialExcluir.php?SituacaoFornecedor=$SituacaoFornecedor&CodFornecedor=".$_SESSION['CodFornecedor'];

	if (!in_array($Url,$_SESSION['GetUrl'])) {
		$_SESSION['GetUrl'][] = $Url;
	}
	
	header("location: ".$Url);
	exit();
} elseif ($Botao == "Voltar") {
	header("location: TabSituacaoFornecedorPregaoPresencialSelecionar.php");
	exit();
} elseif ($Botao == "Alterar") {
	$Mens     = 0;
    $Mensagem = "Informe: ";

    if ($SituacaoFornecedor == "") {
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.SituacaoFornecedor.Descricao.focus();\" class=\"titulo2\">Situacao</a>";
    }

	if ($Mens == 0) {
		# Verifica a Duplicidade de Situacao #
		$sql  = "SELECT COUNT(CPRESFSEQU) ";
		$sql .= "FROM	SFPC.TBPREGAOPRESENCIALSITUACAOFORNECEDOR ";
		$sql .= "WHERE	(RTRIM(LTRIM(EPRESFNOME))) = '$SituacaoFornecedor' ";

		$result = $db->query($sql);

		if (PEAR::isError($result)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		} else {
		    $Linha = $result->fetchRow();
			$Qtd = $Linha[0];
		
			if ($Qtd > 0) {
				$Mens = 1;
				$Tipo = 2;
				$Mensagem = "<a href=\"javascript:document.SituacaoFornecedor.Descricao.focus();\" class=\"titulo2\">Situação já cadastrada!</a>";
			} else {
				# Atualiza SituacaoLote #
				$Data   = date("Y-m-d H:i:s");

				$db->query("BEGIN TRANSACTION");
				   
				$sql  = "UPDATE SFPC.TBPREGAOPRESENCIALSITUACAOFORNECEDOR ";
				$sql .= "SET	EPRESFNOME = '$SituacaoFornecedor', CUSUPOCODI = '$CodigoUsuario', TPRESFULAT = '$Data' ";
				$sql .= "WHERE 	CPRESFSEQU =".$_SESSION['CodFornecedor'];
		
				$result = $db->query($sql);
				
				if (PEAR::isError($result)) {
					$db->query("ROLLBACK");
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				} else {
					$db->query("COMMIT");
					$db->query("END TRANSACTION");
					$db->disconnect();

				   	# Envia mensagem para página selecionar #
					$Mensagem = urlencode("Situação do fornecedor alterada com sucesso!");
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
}

$db->disconnect();
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript">
	<!--
	function enviar(valor) {
		document.SituacaoFornecedor.Botao.value=valor;
		document.SituacaoFornecedor.submit();
	}
	<?php MenuAcesso(); ?>
	//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="TabSituacaoFornecedorPregaoPresencialAlterar.php" method="post" name="SituacaoFornecedor">
		<br><br><br><br>
		<table cellpadding="3" border="0">
			<!-- Caminho -->
			<tr>
				<br>
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
	           					MANTER - SITUAÇÃO DO FORNECEDOR
          					</td>
        				</tr>
        				<tr>
          					<td class="textonormal" >
             					<p align="justify">
			 					Para atualizar uma situação preencha o campo abaixo e clique no botão "Alterar".
			 					Para apagar uma situação clique no botão "Excluir".
             					</p>
          					</td>
        				</tr>
        				<tr>
          					<td>
            					<table>
              						<tr>
                						<td class="textonormal" bgcolor="#DCEDF7">Situação do fornecedor:</td>
               							<td class="textonormal">
               								<input type="text" name="SituacaoFornecedor" size="40" maxlength="40" value="<?php echo $_SESSION['SituacaoFornecedor']?>" class="textonormal">
                						</td>
              						</tr>
            					</table>
          					</td>
        				</tr>
        				<tr align="right">
          					<td>
          						<input type="button" value="Alterar" class="botao" onclick="javascript:enviar('Alterar');">
								<input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Excluir');">
          						<input name="voltar" type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar')">
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
	document.Lote.SituacaoFornecedor.focus();
	//-->
</script>