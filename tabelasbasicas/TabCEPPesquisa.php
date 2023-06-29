<?php
/*
 * Created on 25/05/2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabCEPPesquisa.php
# Autor:    Everton Lino
# Data:     25/05/2010
# Objetivo: Programa de Alteração de CEP
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabCEPAlterar.php' );

if( $_SERVER['REQUEST_METHOD'] == "POST")
{
	 		$Botao          = $_POST['Botao'];
		  $CEP            = $_POST['CEP'];
		  $Logradouro     = strtoupper2(trim($_POST['Logradouro']));
      $Critica      = $_POST['Critica'];
      $Mensagem     = urldecode($_GET['Mensagem']);
			$Mens         = $_GET['Mens'];
			$Tipo         = $_GET['Tipo'];
}else{
		$Critica      = $_GET['Critica'];
		$Mensagem     = urldecode($_GET['Mensagem']);
		$Mens         = $_GET['Mens'];
		$Tipo         = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;


# Critica dos Campos #
if( $Botao == "Pesquisar" ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
    if( $CEP == "" )
    {
	      $Mens      = 1;
	      $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript: document.TabCEPPesquisa.CEP.focus();\" class=\"titulo2\">CEP</a>";
    }elseif(!is_numeric($CEP)){
	      $Mens      = 1;
	      $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript: document.TabCEPPesquisa.CEP.focus();\" class=\"titulo2\">CEP Válido</a>";
    }else{
			$db = Conexao();
			$sql = "SELECT * FROM PPDV.TBCEPLOGRADOUROBR WHERE CCEPPOCODI = '".$CEP."' " ;
			$result 	= $db->query($sql);
			 if( PEAR::isError($result) )
		   {
		  	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			 }else{

			 		$Qtd = $result->numRows();

  		 		if($Qtd > 0){
 			 	 		# CEP encontrado em logradouro
  	    		$Url = "TabCEPAlterar.php?CEP=$CEP";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	    		  header("location: ".$Url);
	    		  exit();
	    	 }else{
				    $db = Conexao();
						$sql = "SELECT * FROM PPDV.TBCEPLOCALIDADEBR WHERE CCELOCCODI = '".$CEP."' " ;
						$result 	= $db->query($sql);
						 if( PEAR::isError($result) )
				 	  {
				 	 		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
					 		$Qtd = $result->numRows();
					 		if($Qtd > 0){
		 			 	 		# CEP encontrado em localidade
								$Url = "TabCEPAlterar.php?CEP=$CEP";
								if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
							  header("location: ".$Url);
							  exit();
				     }else{
				     		$Mens      = 1;
			      		$Tipo      = 2;
		        		$Mensagem .= "<a href=\"javascript: document.TabCEPPesquisa.CEP.focus();\" class=\"titulo2\">CEP não encontrado</a>";
				     }

				 	  }

		     }
		   }

		 }
		  // $db->disconnect();
}

?>
<html>
<?php 
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.TabCEPPesquisa.Botao.value=valor;
	document.TabCEPPesquisa.submit();
}
<?php  MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabCEPPesquisa.php" method="post" name="TabCEPPesquisa">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Fornecedores > CEP > Manter
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<tr>
	  <td width="100"></td>
	  <td align="left" colspan="2">
	  	<?php  if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,1); } ?>
	  </td>
	</tr>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
      <table  border="0" cellspacing="0" cellpadding="3">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	            		MANTER - CEP
	            	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para Pesquisar um CEP, informe os dados abaixo e clique no botão "Pesquisar". Só são permitidos números.
	        	    	</p>
	          		</td>
		        	</tr>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left" summary="">
					           <tr>
				              <td class="textonormal" bgcolor="#DCEDF7" width="30%">CEP*</td>
											<td class="textonormal">
												<input type="text" name="CEP" size="8" maxlength="8" value="<?php echo $CEP; ?>" class="textonormal">
				            	</td>
				            </tr>
	            		</table>
		          	</td>
		        	</tr>
    	      	<tr>
      	      	<td class="textonormal" align="right">
      	      		<input type="button" value="Pesquisar" class="botao" onClick="javascript:enviar('Pesquisar');">
 									<input type="hidden" name="Botao" value="">
      	      	</td>
        	  	</tr>
    	  	  </table>
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
<script language="JavaScript">
<!--
document.TabCEPPesquisa.CEP.focus();
//-->
</script>


