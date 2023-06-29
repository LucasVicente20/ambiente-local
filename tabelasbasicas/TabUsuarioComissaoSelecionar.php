<?php
#-------------------------------------------------------------------------
# Portal da DGCO teste
# Programa: TabUsuarioComissaoSelecionar.php
# Autor:    Rossana Lira
# Data:     03/05/03
# Objetivo: Programa de Seleção de Usuário/Comissão
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabUsuarioComissaoManter.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$UsuarioCodigo = $_POST['UsuarioCodigo'];
		$Critica       = $_POST['Critica'];
}else{
		$Critica       = $_GET['Critica'];
		$Mensagem      = urldecode($_GET['Mensagem']);
		$Mens          = $_GET['Mens'];
		$Tipo          = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabUsuarioComissaoSelecionar.php";

if( $Critica == 1 ){
		# Critica dos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";
    if( $UsuarioCodigo == "" ) {
	      $Mens      = 1;
	      $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript: document.Usuario.UsuarioCodigo.focus();\" class=\"titulo2\">Usuário</a>";
    }else{
    		$Url = "TabUsuarioComissaoManter.php?UsuarioCodigo=$UsuarioCodigo";
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
<form action="TabUsuarioComissaoSelecionar.php" method="post" name="Usuario">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Usuário > Usuário/Comissão
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
	        	MANTER - USUÁRIO/COMISSÃO
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
             Para atualizar/excluir uma Comissão para o Usuário cadastrado, selecione o Usuário e clique no botão "Selecionar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" summary="">
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Usuário </td>
                <td class="textonormal">
                  <select name="UsuarioCodigo" class="textonormal">
                  	<option value="">Selecione um Usuário...</option>
                  	<?php
                		# Mostra os usuários cadastrados #
                		$db   = Conexao();
            				$sql  = "SELECT A.CUSUPOCODI, A.EUSUPOLOGI, B.CGREMPCODI, B.EGREMPDESC, A.EUSUPORESP ";
                		$sql .= "  FROM SFPC.TBUSUARIOPORTAL A, SFPC.TBGRUPOEMPRESA B ";
                		if( $_cgrempcodi_ == 0){
	                  		$sql .= "WHERE A.CGREMPCODI = B.CGREMPCODI ";
	                  }else{
	  	              		$sql .= "WHERE A.CGREMPCODI = B.CGREMPCODI AND B.CGREMPCODI <> 0 ";
	                  }
                		$sql   .= "ORDER BY A.EUSUPORESP, B.EGREMPDESC ";
                		$result = $db->query($sql);
										if( PEAR::isError($result) ){
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												while( $Linha = $result->fetchRow() ){
														$DescGrupo = substr($Linha[3],0,40);
		          	      			echo"<option value=\"$Linha[0]\">".strtoupper2("$Linha[4]")." - $DescGrupo </option>\n";
			                	}
			              }
	           			 	$db->disconnect();
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
document.Usuario.UsuarioCodigo.focus();
//-->
</script>
