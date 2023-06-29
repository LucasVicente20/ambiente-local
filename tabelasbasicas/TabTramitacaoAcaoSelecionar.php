<?php
# -------------------------------------------------------------------------
# Prefeitura do Recife
# Portal de Compras
# Programa: TabTramitacaoAcaoSelecionar.php
# Autor:    Lucas Baracho
# Data:     11/07/2018
# Objetivo: Tarefa Redmine 199049
# -------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     07/03/2019
# Objetivo: Tarefa Redmine 212058
# -------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";
include "./funcoesTramitacao.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso ('/tabelasbasicas/TabTramitacaoAcaoAlterar.php');

$db = Conexao();

# Grupos
$grupo = null;

if ($_SESSION['_fperficorp_'] != 'S') {
    $grupo = $_SESSION['_cgrempcodi_'];
}

$grupos = getGruposAcao($db, $grupo);

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$acaoCodigo	= $_POST['AcaoCodigo'];
	$Critica	= $_POST['Critica'];
} else {
	$Critica  = $_GET['Critica'];
	$Mensagem = $_GET['Mensagem'];
	$Mens     = $_GET['Mens'];
	$Tipo     = $_GET['Tipo'];
}

# Critica dos Campos #
$Mensagem = urldecode($Mensagem);

if ($Critica == 1) {
	$Mens     = 0;
	$Mensagem = "Informe: ";
	
	if ($acaoCodigo == "") {
	    $Mens      = 1;
	    $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript: document.Acao.AcaoCodigo.focus();\" class=\"titulo2\">Ação</a>";
    } else {
		$Url = "TabTramitacaoAcaoAlterar.php?AcaoCodigo=$acaoCodigo";
				
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
#Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
	<!--
	<?php MenuAcesso(); ?>
	//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="TabTramitacaoAcaoSelecionar.php" method="post" name="Acao">
		<br><br><br><br><br>
		<table cellpadding="3" border="0">
  			<!-- Caminho -->
  			<tr>
    			<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal">
					<font class="titulo2">|</font>
					<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Licitações > Tramitação > Ação > Manter
				</td>
  			</tr>
  			<!-- Fim do Caminho-->
			<!-- Erro -->
			<?php if ($Mens == 1) { ?>
				<tr>
	  				<td width="150"></td>
	  				<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
				</tr>
			<?php } ?>
			<!-- Fim do Erro -->
			<!-- Corpo -->
			<tr>
				<td width="150"></td>
				<td class="textonormal">
					<table width="100%" border="1" cellpadding="3" cellspacing="0"  bgcolor="#ffffff" bordercolor="#75ADE6"  class="textonormal" summary="">
        				<tr>
          					<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	        					MANTER - AÇÃO
          					</td>
        				</tr>
        				<tr>
	          				<td class="textonormal">
             					<p align="justify">
             						Para atualizar/excluir uma ação já cadastrada, selecione a mesma e clique no botão "Selecionar".
             					</p>
          					</td>
        				</tr>
        				<tr>
          					<td>
            					<table border="0">
              						<tr>
                						<td class="textonormal" bgcolor="#DCEDF7">Ação:</td>
                						<td class="textonormal">
                  							<select name="AcaoCodigo" class="textonormal">
                  								<option value="">Selecione uma ação...</option>
                  								
												<?php
                	  							/*# Mostra as ações cadastradas #
                								$db = Conexao();
												
												$sql = "SELECT CTACAOSEQU, ETACAODESC, ATACAOORDE FROM SFPC.TBTRAMITACAOACAO ORDER BY ATACAOORDE";
												
												$result = $db->query($sql);
												
												if (PEAR::isError($result)) {
										    		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												} else {
													while ($Linha = $result->fetchRow()) {
		        	      								echo"<option value=\"$Linha[0]\">$Linha[1]</option>\n";
			                						}
			              						}
													
												$db->disconnect();*/
      	            							?>
												  <?php  # Mostra os grupos #
                                                        foreach ($grupos as $key => $value) {
                                                            if(!empty($value[0]['acao'])) {
                                                                ?>
                                                                <option disabled value=""><?php echo $key; ?></option>
                                                            <?php
                                                                foreach ($value as $key_ => $value_) {
                                                                    ?>
                                                                    <option value="<?php echo $value_['acao']; ?>"><?php echo "&nbsp;&nbsp;&nbsp;&nbsp;" . strtoupper($value_['descricao']); ?></option>
                                                                    <?php
                                                                }
                                                            }
                                                        }
                                                        ?>
                  							</select>
                  							<input type="hidden" name="Critica" value="1">
                						</td>
              						</tr>
            					</table>
          					</td>
        				</tr>
        				<tr>
 	        				<td class="textonormal" align="right">
          						<input type="submit" value="Selecionar" class="botao">
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
<script language="javascript" type="">
	<!--
	<?php MenuAcesso(); ?>
	//-->
</script>