<?php
/**
 * Portal de Compras
 *
 * Programa: TabSituacaoDFDAlterar.php
 * Autor: Diógenes Dantas
 * Data: 16/11/2022
 * Objetivo: Programa de alteração de situação do DFD
 * Tarefa Redmine: 275120
 * -------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     29/11/2022
 * Tarefa:   CR 275683
 * -------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/tabelasbasicas/TabSituacaoDFDExcluir.php');
AddMenuAcesso('/tabelasbasicas/TabSituacaoDFDSelecionar.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$botao 			= $_POST['Botao'];
	$SituacaoDFD	= strtoupper2(trim($_POST['SituacaoDFD']));
	$CodDFD	 = $_POST['CodDFD'];
} else {
	$CodDFD	 = $_GET['CodDFD'];
	$SituacaoDFD = $_GET['SituacaoDFD'];

}

$CodDFD = $_SESSION['CodDFD'];
if ($CodDFD > 0) {
	$db = Conexao();

	$sql = "SELECT EPLSITNOME FROM SFPC.TBPLANEJAMENTOSITUACAODFD WHERE CPLSITCODI = " . $CodDFD;
	// var_dump($sql);die;
	$result = $db->query($sql);

	if (db::isError($result)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		$Linha = $result->fetchRow();
	}

	$_SESSION['SituacaoDFD'] = $Linha[0];

	$db->disconnect();
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

$db = Conexao();

$CodigoUsuario = $_SESSION['_cusupocodi_'];


// var_dump($_SESSION['CodDFD']);die;
if ($botao == "Excluir") {
	$Url = "TabSituacaoDFDExcluir.php?SituacaoDFD=$SituacaoDFD&CodDFD=".$CodDFD;

	if (!in_array($Url,$_SESSION['GetUrl'])) {
		$_SESSION['GetUrl'][] = $Url;
	}
	
	header("location: ".$Url);
	exit();
} elseif ($botao == "Voltar") {
	header("location: TabSituacaoDFDSelecionar.php");
	exit();
} elseif ($botao == "Alterar") {
	$Mens = 0;
    $Mensagem = "Informe: ";

    if ($SituacaoDFD == "") {
		$Mens = 1;
		$Tipo = 2;
		$Mensagem .= "<a href=\"javascript:document.SituacaoDFD.Descricao.focus();\" class=\"titulo2\">Situação do DFD</a>";
    }

	if ($Mens == 0) {
		# Verifica a duplicidade de situacao #
		$sql  = "SELECT COUNT(CPLSITCODI) FROM SFPC.TBPLANEJAMENTOSITUACAODFD WHERE (RTRIM(LTRIM(EPLSITNOME))) = '$SituacaoDFD'";

		$result = $db->query($sql);

		if (db::isError($result)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		} else {
		    $Linha = $result->fetchRow();
			$Qtd = $Linha[0];

			if ($Qtd > 0) {
				$Mens = 1;
				$Tipo = 2;
				$Mensagem = "<a href=\"javascript:document.SituacaoDFD.Descricao.focus();\" class=\"titulo2\">Situação já cadastrada</a>";
			} else {
				# Atualiza SituacaoLote #
				$Data   = date("Y-m-d H:i:s");
				$db->query("BEGIN TRANSACTION");

				$sql  = "UPDATE SFPC.TBPLANEJAMENTOSITUACAODFD ";
				$sql .= "SET EPLSITNOME = '$SituacaoDFD', CUSUPOCODI = '$CodigoUsuario', TPLSITULAT = '$Data' ";
				$sql .= "WHERE 	CPLSITCODI = " .$CodDFD;
				
				$result = $db->query($sql);

				if (db::isError($result)) {
					$db->query("ROLLBACK");
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				} else {
					$db->query("COMMIT");
					$db->query("END TRANSACTION");
					$db->disconnect();

				   	# Envia mensagem para página selecionar #
					$Mensagem = urlencode("Situação do DFD alterada com sucesso");
					$Url = "TabSituacaoDFDSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";

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
		document.SituacaoDFD.Botao.value=valor;
		document.SituacaoDFD.submit();
	}
	<?php MenuAcesso(); ?>
	//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="TabSituacaoDFDAlterar.php" method="post" name="SituacaoDFD">
		<br><br><br><br>
		<table cellpadding="3" border="0">
			<!-- Caminho -->
			<tr>
				<br>
				<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="2">
					<font class="titulo2">|</font>
					<a href="../index.php"><font color="#000000">Página Principal</font>
                    </a> > Tabelas > Planejamento > Situação DFD > Manter
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
	           					ATUALIZAR - SITUAÇÃO DO DFD
          					</td>
        				</tr>
        				<tr>
							<td class="textonormal" bgcolor="#FFFFFF">
            					<p align="justify">Para atualizar a situação do DFD, preencha o campo abaixo com um novo nome e clique no botão "Alterar". Para apagar uma situação, clique no botão "Excluir".</p>
          					</td>
        				</tr>
        				<tr>
          					<td>
            					<table>
              						<tr>
                						<td class="textonormal" bgcolor="#DCEDF7">Situação do DFD:</td>
               							<td class="textonormal">
               								<input type="text" name="SituacaoDFD" size="40" maxlength="40" value="<?php echo $_SESSION['SituacaoDFD']; ?>" class="textonormal">
											<input type="hidden" name="CodDFD" value="<?php $_SESSION['CodDFD']; ?>">
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
	document.Planejamento.SituacaoDFD.focus();
	//-->
</script>