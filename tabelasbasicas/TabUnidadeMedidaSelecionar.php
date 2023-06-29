<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabUnidadeMedidaSelecionar.php
# Autor:    Roberta Costa
# Data:     31/05/05
# Objetivo: Programa de Manutenção de Unidade de Medida
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabUnidadeMedidaAlterar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$UnidadeMedida = $_POST['UnidadeMedida'];
		$Botao         = $_POST['Botao'];
}else{
		$Mensagem = urldecode($_GET['Mensagem']);
		$Mens     = $_GET['Mens'];
		$Tipo     = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Selecionar" ){
		# Critica dos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";
    if( $UnidadeMedida == "" ) {
	      $Mens      = 1;
	      $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript: document.UnidadeMedida.UnidadeMedida.focus();\" class=\"titulo2\">UnidadeMedida</a>";
    }else{
    		$Url = "TabUnidadeMedidaAlterar.php?UnidadeMedida=$UnidadeMedida";
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
	document.UnidadeMedida.Botao.value=valor;
	document.UnidadeMedida.submit();
}
<?php  MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabUnidadeMedidaSelecionar.php" method="post" name="UnidadeMedida">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Estoques > Unidade de Medida > Manter
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php  if ( $Mens == 1 ) {?>
	<tr>
	  <td width="150"></td>
	  <td align="left" colspan="2"><?php  ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php  } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="150"></td>
		<td class="textonormal">
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	           MANTER - UNIDADE DE MEDIDA
          </td>
        </tr>
        <tr>
          <td class="textonormal" bgcolor="#FFFFFF">
             <p align="justify">
             Para atualizar/excluir um Unidade de Medida já cadastrada, selecione a Unidade de Medida e clique no botão "Selecionar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" width="30%">Unidade de Medida</td>
                <td class="textonormal">
                  <select name="UnidadeMedida" class="textonormal">
                  	<option value="">Unidade de Medida...</option>
                  	<!-- Mostra as unidades de medidas cadastradas -->
                  	<?php 
                		$db     = Conexao();
                		$sql    = "SELECT CUNIDMCODI, EUNIDMSIGL, EUNIDMDESC FROM SFPC.TBUNIDADEDEMEDIDA ORDER BY EUNIDMDESC";
                		$result = $db->query($sql);
                		if( PEAR::isError($result) ){
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												while( $Linha = $result->fetchRow() ){
		          	      			echo"<option value=\"$Linha[0]\">$Linha[1]_$Linha[2]</option>\n";
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
document.UnidadeMedida.UnidadeMedida.focus();
//-->
</script>
