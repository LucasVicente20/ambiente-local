<?php
/**
 * Portal de Compras
 *
 * Programa: TabSituacaoDFDSelecionar.php
 * Autor: Diógenes Dantas
 * Data: 16/11/2022
 * Objetivo: Programa de seleção de situação do DFD
 * Tarefa Redmine: 275120
 * -------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     29/11/2022
 * Tarefa:   CR 275683
 * -------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/tabelasbasicas/TabSituacaoDFDAlterar.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Botao	= $_POST['Botao'];
	$CodDFD = $_POST['CodDFD'];
} else {
	$Mensagem = urldecode($_GET['Mensagem']);
	$Mens = $_GET['Mens'];
	$Tipo = $_GET['Tipo'];
}
$_SESSION['CodDFD'] = $CodDFD;

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if ($Botao == "Selecionar") {
	# Critica dos Campos #
	$Mens = 0;
	$Mensagem = "Atenção: ";

	if ($CodDFD == "") {
	    $Mens = 1;
	    $Tipo = 2;
        $Mensagem .= "<a href=\"javascript: document.Planejamento.SituacaoDFD.focus();\" class=\"titulo2\">Selecione uma situação de DFD</a>";
    } else {
    	$Url = "TabSituacaoDFDAlterar.php?CodDFD=$CodDFD";

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
	function enviar(valor){
		document.TabSituacaoDFDSelecionar.Botao.value=valor;
		document.TabSituacaoDFDSelecionar.submit();
	}
	<?php MenuAcesso(); ?>
	//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="TabSituacaoDFDSelecionar.php" method="post" name="TabSituacaoDFDSelecionar">
		<br><br><br><br><br>
		<table cellpadding="3" border="0">
  			<!-- Caminho -->
  			<tr>
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
					<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
        				<tr>
          					<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	           					MANTER - SITUAÇÃO DO DFD
          					</td>
        				</tr>
        				<tr>
          					<td class="textonormal" bgcolor="#FFFFFF">
            					<p align="justify">Para alterar ou excluir a situação da DFD, selecione entre as opções abaixo e clique em 'Selecionar'.</p>
          					</td>
        				</tr>
        				<tr>
          					<td>
            					<table>
              						<tr>
                						<td class="textonormal" bgcolor="#DCEDF7" width="30%">Situação do DFD:</td>
                						<td class="textonormal">
                  							<select name="CodDFD" class="textonormal">
                  								<option value="">Selecione...</option>
                  								<!-- Mostra as DFD's cadastradas -->
                  								<?php
                								$db = Conexao();

                								$sql = "SELECT CPLSITCODI, EPLSITNOME 
                                						FROM SFPC.TBPLANEJAMENTOSITUACAODFD 
                                						ORDER BY EPLSITNOME ";

                								$result = $db->query($sql);

                								if (db::isError($result)) {
                            						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                        						} else {
                            						while ($Linha = $result->fetchRow()) {
                            							echo"<option value=\"$Linha[0]\">$Linha[1]</option>\n";
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
	document.Planejamento.SituacaoDFD.focus();
	//-->
</script>