<?php

/*
Arquivo: TabRelLegisPNCPExcluir.php
Nome: Lucas Vicente
Data: 01/03/2023
Tarefa: 279688 

*/

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/tabelasbasicas/TabRelLegisPNCPAlterar.php');
AddMenuAcesso('/tabelasbasicas/TabRelLegisPNCPSelecionar.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Botao        = $_POST['Botao'];
	$CodigoPNCP   = $_POST['CodigoPNCP'];
	$Lei   		  = $_POST['Lei'];
	$Artigo   	  = $_POST['Artigo'];
	$Inciso   	  = $_POST['Inciso'];
	$CodigoPNCP   = $_SESSION['CodigoPNCP'];
    $Lei          = $_SESSION['Lei'];
    $Artigo       = $_SESSION['Artigo'];
    $Inciso       = $_SESSION['Inciso'];
} else {
	$CodigoPNCP   = $_GET['CodigoPNCP'];
    $CodigoPNCP   = $_SESSION['CodigoPNCP'];
    $Lei          = $_SESSION['Lei'];
    $Artigo       = $_SESSION['Artigo'];
    $Inciso       = $_SESSION['Inciso'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Critica dos Campos #
$db = Conexao();

if ($Botao == "Voltar") {
    $_SESSION['CodigoPNCP'] = $CodigoPNCP;
	$Url = "TabRelLegisPNCPAlterar.php";
	header("location: ".$Url);
	exit();
} elseif ($Botao == "Excluir") {
	$Mens     = 0;
    $Mensagem = "Informe: ";
    
    # Exclui o código de Legislação #
    $db->query("BEGIN TRANSACTION");
   
    $sql = "DELETE FROM SFPC.tblegislacaocompraspncp WHERE clcpnccodi = " .$CodigoPNCP;
    $result = $db->query($sql);

    if (db::isError($result)) {
        $db->query("ROLLBACK");
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        $db->query("COMMIT");
        $db->query("END TRANSACTION");
        $db->disconnect();

        # Envia mensagem para página selecionar #
        $Mensagem = urlencode("Código de Legislação excluído com sucesso");
        $Url = "TabRelLegisPNCPSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";

        header("location: ".$Url);
        exit();
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
	function enviar(valor) {
		document.TabRelLegisPNCPExcluir.Botao.value=valor;
		document.TabRelLegisPNCPExcluir.submit();
	}
	<?php MenuAcesso(); ?>
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="TabRelLegisPNCPExcluir.php" method="post" name="TabRelLegisPNCPExcluir">
		<br><br><br><br><br>
		<table cellpadding="3" border="0">
			<!-- Caminho -->
			<tr>
				<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="2">
					<font class="titulo2">|</font>
					<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Planejamento > PNCP > Excluir
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
	           				EXCLUIR - PNCP
          					</td>
        				</tr>
        				<tr>
          					<td class="textonormal" >
             					<p align="justify">
               					Para confirmar a exclusão de Código de Legislação de Compras, clique no botão "Excluir", caso contrário clique no botão "Voltar".
             					</p>
          					</td>
        				</tr>
        				<tr>
          					<td>
            					<table>
									<tr>
                						<td class="textonormal" bgcolor="#DCEDF7" height="20">Código de Legislação de Compras:</td>
               							<td class="textonormal">
               								<?php echo $CodigoPNCP; ?>
                							<input type="hidden" name="CodigoPNCP" id="CodigoPNCP" value="<?php $CodigoPNCP ?>">
                						</td>
              						</tr>
									<tr>
                						<td class="textonormal" bgcolor="#DCEDF7" height="20">Lei:</td>
               							<td class="textonormal">
               								<?php echo $Lei; ?>
                							<input type="hidden" name="Lei" id="Lei" value="<?php $Lei ?>">
                						</td>
                                    </tr>
                                    <tr>
                						<td class="textonormal" bgcolor="#DCEDF7" height="20">Artigo:</td>
               							<td class="textonormal">
               								<?php echo $Artigo; ?>
                							<input type="hidden" name="Artigo" id="Artigo" value="<?php $Artigo ?>">
                						</td>
                                    </tr>
                                    <tr>
                						<td class="textonormal" bgcolor="#DCEDF7" height="20">Inciso:</td>
               							<td class="textonormal">
               								<?php echo $Inciso; ?>
                							<input type="hidden" name="Inciso" id="Inciso" value="<?php $Inciso ?>">
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