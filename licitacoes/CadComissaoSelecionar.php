<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadComissaoSelecionar.php
# Autor:    Rossana Lira
# Data:     07/04/03
# Objetivo: Programa de Manutenção de Comissão de Licitação
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/licitacoes/CadComissaoAlterar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Critica        = $_POST['Critica'];
		$ComissaoCodigo = $_POST['ComissaoCodigo'];
}else{
		$Critica  = $_GET['Critica'];
		$Mensagem = urldecode($_GET['Mensagem']);
		$Mens     = $_GET['Mens'];
		$Tipo     = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadComissaoSelecionar.php";

# Critica dos Campos #
if( $Critica == 1 ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
    if( $ComissaoCodigo == "" ) {
	      $Mens = 1; $Tipo = 2; $Troca = 1;
        $Mensagem .= "<a href=\"javascript: document.Comissao.ComissaoCodigo.focus();\" class=\"titulo2\">Comissão</a>";
    }else{
    		$Url = "CadComissaoAlterar.php?ComissaoCodigo=$ComissaoCodigo";
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
<script language="javascript" type="">
<!--
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadComissaoSelecionar.php" method="post" name="Comissao">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Comissão > Manter
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
		<td class="textonormal"><br>
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	           MANTER - COMISSÃO DE LICITAÇÃO
          </td>
        </tr>
        <tr>
          <td class="textonormal">
						<p align="justify">
							Para atualizar/excluir uma Comissão já cadastrada, selecione a Comissão e clique no botão "Selecionar".
						</p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" summary="">
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Comissão </td>
                <td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
                <td bgcolor="#FFFFFF">
                  <select name="ComissaoCodigo" value="" class="textonormal">
                  	<option value="">Selecione uma Comissão...</option>
                  	<!-- Mostra as comissões cadastradas -->
                  	<?php
                		$db   = Conexao();
                		$sql  = "SELECT CCOMLICODI, ECOMLIDESC FROM SFPC.TBCOMISSAOLICITACAO ";
                		$sql .= "WHERE CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ORDER BY ECOMLIDESC";
                		$res  = $db->query($sql);
										if( PEAR::isError($res) ){
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}
										while( $Linha = $res->fetchRow() ){
          	      			echo"<option value=\"$Linha[0]\">$Linha[1]</option>\n";
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
document.Comissao.ComissaoCodigo.focus();
//-->
</script>
