<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabPendenciaSelecionar.php
# Autor:    Roberta Costa
# Data:     27/12/04
# Objetivo: Programa de Manutenção de Pendência
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabPendenciaAlterar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$PendenciaCodigo = $_POST['PendenciaCodigo'];
		$Botao           = $_POST['Botao'];
}else{
		$Mensagem = urldecode($_GET['Mensagem']);
		$Mens     = $_GET['Mens'];
		$Tipo     = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabPendenciaSelecionar.php";

if( $Botao == "Selecionar" ){
		# Critica dos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";
    if( $PendenciaCodigo == "" ) {
	      $Mens      = 1;
	      $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript: document.Pendencia.PendenciaCodigo.focus();\" class=\"titulo2\">Pendência</a>";
    }else{
    		$Url = "TabPendenciaAlterar.php?PendenciaCodigo=$PendenciaCodigo";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
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
	document.Pendencia.Botao.value=valor;
	document.Pendencia.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabPendenciaSelecionar.php" method="post" name="Pendencia">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Pendência > Manter
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
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
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	           MANTER - PENDÊNCIA
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
             Para atualizar/excluir um Pendência já cadastrada, selecione a Pendência e clique no botão "Selecionar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Pendência </td>
                <td class="textonormal">
                  <select name="PendenciaCodigo" class="textonormal">
                  	<option value="">Selecione uma Pendência...</option>
                  	<!-- Mostra os perfis cadastrados -->
                  	<?php
                		$db     = Conexao();
                		$sql    = "SELECT CTIPPECODI, ETIPPEDESC FROM SFPC.TBTIPOPENDENCIA ORDER BY ETIPPEDESC";
                		$result = $db->query($sql);
                		if( PEAR::isError($result) ){
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												while( $Linha = $result->fetchRow() ){
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
          <td class="textonormal" align="right">
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
document.Pendencia.PendenciaCodigo.focus();
//-->
</script>
