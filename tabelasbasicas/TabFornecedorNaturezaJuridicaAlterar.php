<?php
/*
Arquivo: TabFornecedorNaturezaJuridicaAlterar.php
Nome: Lucas André e Lucas Vicente
Data: 24/11/2022
Tarefa: CR 275539
----------------------------------------------------------------------------
Arquivo: TabFornecedorNaturezaJuridicaAlterar.php
Nome: Lucas André
Data: 26/04/2023
Tarefa: CR 275539
----------------------------------------------------------------------------
*/

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/tabelasbasicas/TabFornecedorNaturezaJuridicaExcluir.php');
AddMenuAcesso('/tabelasbasicas/TabFornecedorNaturezaJuridicaSelecionar.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Botao          	   = $_POST['Botao'];
	$DescNaturezaJuridica  = mb_convert_case($_POST['DescNaturezaJuridica'], MB_CASE_UPPER);
	$CodNaturezaJuridica   = $_POST['CodNaturezaJuridica'];
} else {
	$CodNaturezaJuridica   = $_GET['CodNaturezaJuridica'];

	$db = Conexao();

	$sql = "SELECT efornjtpnj, afornjcodi, cfornjsequ FROM SFPC.tbfornecedortiponaturezajuridica WHERE afornjcodi = " .$CodNaturezaJuridica;
	
	$result = $db->query($sql);

	if (db::isError($result)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		$Linha = $result->fetchRow();
	}
	
	$_SESSION['DescNaturezaJuridica'] = $DescNaturezaJuridica = $Linha[0];
	$_SESSION['CodNaturezaJuridica']  = $CodNaturezaJuridica  = $Linha[1];
	$_SESSION['SeqNaturezaJuridica']  = $Linha[2];
	$db->disconnect();
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

$db = Conexao();

$CodigoUsuario = $_SESSION['_cusupocodi_'];

if ($Botao == "Excluir") {
	$Url = "TabFornecedorNaturezaJuridicaExcluir.php?CodNaturezaJuridica=$CodNaturezaJuridica&NaturezaJuridica=$DescNaturezaJuridica";

	if(!in_array($Url,$_SESSION['GetUrl'])){
		$_SESSION['GetUrl'][] = $Url;
	}

	header("location: ".$Url);
	exit();
} elseif ($Botao == "Voltar") {
	header("location: TabFornecedorNaturezaJuridicaSelecionar.php");
	exit();
} elseif ($Botao == "Alterar") {
	$Mens     = 0;
    $Mensagem = "Informe: ";

    if ($DescNaturezaJuridica == "") {
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.DescNaturezaJuridica.Descricao.focus();\" class=\"titulo2\"> Natureza Jurídica</a>";
    }

    if ($CodNaturezaJuridica == "") {
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CodNaturezaJuridica.Codigo.focus();\" class=\"titulo2\"> Código da Natureza Jurídica</a>";
    }

	if ($Mens == 0) {
		//Verifica a duplicidade do Código da Natureza Jurídica
		$sql2 = "SELECT count(afornjcodi) FROM SFPC.tbfornecedortiponaturezajuridica WHERE afornjcodi = $CodNaturezaJuridica and cfornjsequ <> " . $_SESSION['SeqNaturezaJuridica'];

		$result2 = $db->query($sql2);

		if (db::isError($result2)) {
			ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql2");
		} else {
			$Linha2 = $result2->fetchRow();
			$QtdCod = $Linha2[0];

			if ($QtdCod > 0) {
				$Mens = 1;
				$Tipo = 2;
				$Mensagem = "<a href=\"javascript:document.Lote.NaturezaJuridica.focus();\" class=\"titulo2\">Código da Natureza Jurídica já cadastrado</a>";
			} else {
				//Verifica a duplicidade da descrição da Natureza Jurídica
				$sql3 = "SELECT count(efornjtpnj) FROM SFPC.tbfornecedortiponaturezajuridica WHERE efornjtpnj = '$DescNaturezaJuridica' and cfornjsequ <> " . $_SESSION['SeqNaturezaJuridica'];

				$result3 = $db->query($sql3);

				if (db::isError($result3)) {
					ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql3");
				} else {
					$Linha3 = $result3->fetchRow();
					$QtdCod3 = $Linha3[0];

					if ($QtdCod3 > 0) {
						$Mens = 1;
						$Tipo = 2;
						$Mensagem = "<a href=\"javascript:document.Lote.NaturezaJuridica.focus();\" class=\"titulo2\">Descrição da Natureza Jurídica já cadastrado</a>";
					} else {
						# Atualiza Natureza Jurídica #
						$Data   = date("Y-m-d H:i:s");

						$db->query("BEGIN TRANSACTION");

						$sql  = "UPDATE SFPC.tbfornecedortiponaturezajuridica ";
						$sql .= "SET	efornjtpnj = '$DescNaturezaJuridica', CUSUPOCODI = '$CodigoUsuario', tfornjulat = '$Data', afornjcodi = $CodNaturezaJuridica ";
						$sql .= "WHERE 	cfornjsequ = ". $_SESSION['SeqNaturezaJuridica'];

						$result = $db->query($sql);
				
						if (db::isError($result)) {
							$db->query("ROLLBACK");
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						} else {
							$db->query("COMMIT");
							$db->query("END TRANSACTION");
							$db->disconnect();

			   				# Envia mensagem para página selecionar #
							$Mensagem = urlencode("Natureza Jurídica alterada alterada com sucesso");
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
			document.TabFornecedorNaturezaJuridicaAlterar.Botao.value = valor;
			document.TabFornecedorNaturezaJuridicaAlterar.submit();
		}
	
		<?php MenuAcesso(); ?>
		//-->
	</script>
	<link rel="stylesheet" type="text/css" href="../estilo.css">
	<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
		<script language="JavaScript" src="../menu.js"></script>
		<script language="JavaScript">Init();</script>
		<form action="TabFornecedorNaturezaJuridicaAlterar.php" method="post" name="TabFornecedorNaturezaJuridicaAlterar">
			<br><br><br><br>
			<table cellpadding="3" border="0">
				<!-- Caminho -->
				<tr>
					<br>
					<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
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
	           					MANTER - NATUREZA JURÍDICA
          						</td>
        					</tr>
        					<tr>
          						<td class="textonormal" >
             						<p align="justify">
			 						Para atualizar uma natureza jurídica preencha o campo abaixo e clique no botão "Alterar".
			 						Para apagar uma natureza clique no botão "Excluir".
             						</p>
          						</td>
        					</tr>
        					<tr>
          						<td>
            						<table>
										<tr>
                							<td class="textonormal" bgcolor="#DCEDF7">Código da Natureza Jurídica:</td>
               								<td class="textonormal">
               									<input type="text" name="CodNaturezaJuridica" size="40" maxlength="40" value="<?php echo $CodNaturezaJuridica;?>" class="textonormal">											
                							</td>
              							</tr>
              							<tr>
                							<td class="textonormal" bgcolor="#DCEDF7">Natureza Jurídica:</td>
               								<td class="textonormal">
               									<input type="text" name="DescNaturezaJuridica" size="40" maxlength="40" value="<?php echo $DescNaturezaJuridica;?>" class="textonormal">
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
	document.Lote.NaturezaJuridica.focus();
	//-->
</script>