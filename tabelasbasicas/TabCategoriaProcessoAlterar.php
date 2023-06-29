<?php
/*
Arquivo: TabCategoriaProcessoAlterar.php
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
AddMenuAcesso('/tabelasbasicas/TabCategoriaProcessoExcluir.php');
AddMenuAcesso('/tabelasbasicas/TabCategoriaProcessoSelecionar.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Botao          	   = $_POST['Botao'];
	$DescCategoriaProcesso  = mb_convert_case($_POST['DescCategoriaProcesso'], MB_CASE_UPPER);
	$CodCategoriaProcesso   = $_POST['CodCategoriaProcesso'];
} else {
	$CodCategoriaProcesso   = $_GET['CodCategoriaProcesso'];

	$db = Conexao();

	$sql = "SELECT epnccpnome, cpnccpcodi FROM sfpc.tbpncpdominiocategoriaprocesso WHERE cpnccpcodi = " .$CodCategoriaProcesso;
	
	$result = $db->query($sql);

	if (db::isError($result)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		$Linha = $result->fetchRow();
	}
	
	$_SESSION['DescCategoriaProcesso'] = $DescCategoriaProcesso = $Linha[0];
	$_SESSION['CodCategoriaProcesso']  = $CodCategoriaProcesso  = $Linha[1];
	$db->disconnect();
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

$db = Conexao();

$CodigoUsuario = $_SESSION['_cusupocodi_'];

if ($Botao == "Excluir") {
	$Url = "TabCategoriaProcessoExcluir.php?CodCategoriaProcesso=$CodCategoriaProcesso&DescCategoriaProcesso=$DescCategoriaProcesso";

	if(!in_array($Url,$_SESSION['GetUrl'])){
		$_SESSION['GetUrl'][] = $Url;
	}

	header("location: ".$Url);
	exit();
} elseif ($Botao == "Voltar") {
	header("location: TabCategoriaProcessoSelecionar.php");
	exit();
} elseif ($Botao == "Alterar") {
	$Mens     = 0;
    $Mensagem = "Informe: ";

    if ($DescCategoriaProcesso == "") {
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.DescCategoriaProcesso.Descricao.focus();\" class=\"titulo2\">Categoria do Processo</a>";
    }

	if ($Mens == 0) {
		//Verifica a duplicidade da descrição da Categoria do Processo
		$sql3 = "SELECT count(epnccpnome) FROM sfpc.tbpncpdominiocategoriaprocesso WHERE epnccpnome = '$DescCategoriaProcesso' and cpnccpcodi <> " . $_SESSION['CodCategoriaProcesso'];

		$result3 = $db->query($sql3);

		if (db::isError($result3)) {
			ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql3");
		} else {
			$Linha3 = $result3->fetchRow();
			$QtdCod3 = $Linha3[0];

			if ($QtdCod3 > 0) {
				$Mens = 1;
				$Tipo = 2;
				$Mensagem = "<a href=\"javascript:document.Lote.DescCategoriaProcesso.focus();\" class=\"titulo2\">Categoria do Processo já cadastrada</a>";
			} else {
				# Atualiza Categoria do Processo #
				$Data   = date("Y-m-d H:i:s");

				$db->query("BEGIN TRANSACTION");

				$sql  = "UPDATE sfpc.tbpncpdominiocategoriaprocesso ";
				$sql .= "SET	epnccpnome = '$DescCategoriaProcesso', cusupocodi = '$CodigoUsuario', tpnccpulat = '$Data'";
				$sql .= "WHERE 	cpnccpcodi = ". $_SESSION['CodCategoriaProcesso'];

				$result = $db->query($sql);
				
				if (db::isError($result)) {
						$db->query("ROLLBACK");
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				} else {
					$db->query("COMMIT");
					$db->query("END TRANSACTION");
					$db->disconnect();

		   			# Envia mensagem para página selecionar #
					$Mensagem = urlencode("Categoria do Processo alterada alterada com sucesso");
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
			document.TabCategoriaProcessoAlterar.Botao.value = valor;
			document.TabCategoriaProcessoAlterar.submit();
		}
	
		<?php MenuAcesso(); ?>
		//-->
	</script>
	<link rel="stylesheet" type="text/css" href="../estilo.css">
	<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
		<script language="JavaScript" src="../menu.js"></script>
		<script language="JavaScript">Init();</script>
		<form action="TabCategoriaProcessoAlterar.php" method="post" name="TabCategoriaProcessoAlterar">
			<br><br><br><br>
			<table cellpadding="3" border="0">
				<!-- Caminho -->
				<tr>
					<br>
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
	           					MANTER - CATEGORIA DO PROCESSO
          						</td>
        					</tr>
        					<tr>
          						<td class="textonormal" >
             						<p align="justify">
			 						Para atualizar uma Categoria do Proceso preencha o campo abaixo e clique no botão "Alterar".
			 						Para apagar uma Categoria clique no botão "Excluir".
             						</p>
          						</td>
        					</tr>
        					<tr>
          						<td>
            						<table>
              							<tr>
                							<td class="textonormal" bgcolor="#DCEDF7">Categoria do Processo:</td>
               								<td class="textonormal">
               									<input type="text" name="DescCategoriaProcesso" size="40" maxlength="40" value="<?php echo $DescCategoriaProcesso;?>" class="textonormal">
                							</td>
              							</tr>
            						</table>
          						</td>
        					</tr>
        					<tr align="right">
          						<td>
          							<input type="button" value="Alterar" class="botao" onclick="javascript:enviar('Alterar');">
									<input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Excluir');">
          							<input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar')">
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
	document.Lote.CategoriaProcesso.focus();
	//-->
</script>