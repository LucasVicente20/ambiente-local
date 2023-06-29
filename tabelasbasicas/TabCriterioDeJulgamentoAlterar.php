<?php

/*
Arquivo: TabCriterioDeJulgamentoAlterar.php
Nome: Lucas André e Lucas Vicente
Data: 24/11/2022
Tarefa: CR 275539

*/

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/tabelasbasicas/TabCriterioDeJulgamentoExcluir.php');
AddMenuAcesso('/tabelasbasicas/TabCriterioDeJulgamentoSelecionar.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Botao          	   = $_POST['Botao'];
	$DescCriterioDeJulgamento  = strtoupper2(trim($_POST['DescCriterioDeJulgamento']));
	$CodCriterioDeJulgamento   = $_POST['CodCriterioDeJulgamento'];
} else {
	$CodCriterioDeJulgamento        = $_GET['CodCriterioDeJulgamento'];
	$DescCriterioDeJulgamento       = $_GET['DescCriterioDeJulgamento'];
	$_SESSION['DescCriterioDeJulgamento'] = $DescCriterioDeJulgamento;
}
$CodCriterioDeJulgamento = $_SESSION['CodCriterioDeJulgamento'];

if ($CodCriterioDeJulgamento > 0) {
	$db = Conexao();

	$sql = "SELECT ecrjulnome FROM SFPC.tbcriteriojulgamento WHERE ccrjulcodi =" .$CodCriterioDeJulgamento;
	
	$result = $db->query($sql);

	if (db::isError($result)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		$Linha = $result->fetchRow();
	}
	
	$_SESSION['DescCriterioDeJulgamento'] = $Linha[0];
	

	$db->disconnect();
}



# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

$db = Conexao();

$CodigoUsuario = $_SESSION['_cusupocodi_'];

if ($Botao == "Excluir") {
	$Url = "TabCriterioDeJulgamentoExcluir.php?CodCriterioDeJulgamento=$CodCriterioDeJulgamento&CriterioDeJulgamento=$DescCriterioDeJulgamento";

	if(!in_array($Url,$_SESSION['GetUrl'])){
		$_SESSION['GetUrl'][] = $Url;
	}
	header("location: ".$Url);
	exit();

}elseif($Botao == "Voltar"){
	header("location: TabCriterioDeJulgamentoSelecionar.php");
	exit();
}elseif($Botao == "Alterar"){
	$Mens     = 0;
    $Mensagem = "Informe: ";

    if ($DescCriterioDeJulgamento == "") {
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.DescCriterioDeJulgamento.Descricao.focus();\" class=\"titulo2\">Criterio De Julgamento</a>";
    }

	if ($Mens == 0) {
		# Verifica a Duplicidade do Criterio De Julgamento #
		$sql  = "SELECT COUNT(ccrjulcodi) ";
		$sql .= "FROM	SFPC.tbcriteriojulgamento ";
		$sql .= "WHERE	(RTRIM(LTRIM(ecrjulnome))) = '$DescCriterioDeJulgamento' ";
		
		$result = $db->query($sql);
		
		if (db::isError($result)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		} else {
		    $Linha = $result->fetchRow();
			
			$Qtd = $Linha[0];
			
		
			if ($Qtd > 0) {
				$Mens = 1;
				$Tipo = 2;
				$Mensagem = "<a href=\"javascript:document.DescCriterioDeJulgamento.Descricao.focus();\" class=\"titulo2\">Criterio De Julgamento já cadastrado</a>";
			} else {
				# Atualiza Criterio De Julgamento #
				$Data   = date("Y-m-d H:i:s");

				$db->query("BEGIN TRANSACTION");
				   
				$sql  = "UPDATE SFPC.tbcriteriojulgamento ";
				$sql .= "SET ecrjulnome = '$DescCriterioDeJulgamento', CUSUPOCODI = '$CodigoUsuario', tcrjululat = '$Data' ";
				$sql .= "WHERE 	ccrjulcodi = ".$CodCriterioDeJulgamento;
				
				$result = $db->query($sql);
				
				if (db::isError($result)) {
					$db->query("ROLLBACK");
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				} else {
					$db->query("COMMIT");
					$db->query("END TRANSACTION");
					$db->disconnect();

				   	# Envia mensagem para página selecionar #
					$Mensagem = urlencode("Criterio De Julgamento alterado com sucesso");
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
		document.DescCriterioDeJulgamento.Botao.value = valor;
		document.DescCriterioDeJulgamento.submit();
	}
	<?php MenuAcesso(); ?>
	//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="TabCriterioDeJulgamentoAlterar.php" method="post" name="DescCriterioDeJulgamento">
		<br><br><br><br>
		<table cellpadding="3" border="0">
			<!-- Caminho -->
			<tr>
				<br>
				<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="2">
					<font class="titulo2">|</font>
					<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Licitações > Criterio De Julgamento > Manter
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
	           					MANTER - CRITÉRIO DE JULGAMENTO
          					</td>
        				</tr>
        				<tr>
          					<td class="textonormal" >
             					<p align="justify">
			 					Para atualizar um Criterio De Julgamento preencha o campo abaixo e clique no botão "Alterar".
			 					Para apagar um critério clique no botão "Excluir".
             					</p>
          					</td>
        				</tr>
        				<tr>
          					<td>
            					<table>
              						<tr>
                						<td class="textonormal" bgcolor="#DCEDF7">Critério De Julgamento:</td>
               							<td class="textonormal">
               								<input type="text" name="DescCriterioDeJulgamento" size="40" maxlength="40" value="<?php echo $_SESSION['DescCriterioDeJulgamento'];?>" class="textonormal">
											
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
	document.Lote.DescCriterioDeJulgamento.focus();
	//-->
</script>