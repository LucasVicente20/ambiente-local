<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelListagemFornecedor.php
# Autor:    Roberta Costa
# Data:     26/10/04
# Objetivo: Programa de Impressão dos Fornecedores
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/fornecedores/RelListagemFornecedor.php' );
AddMenuAcesso( '/fornecedores/RelListagemFornecedorPdf.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao = $_POST['Botao'];
		$Ordem = $_POST['Ordem'];
}else{
		$Mensagem = urldecode($_GET['Mensagem']);
		$Mens     = $_GET['Mens'];
		$Tipo			= $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Limpar" ){
	  header("location: RelListagemFornecedor.php");
	  exit;
}elseif( $Botao == "Imprimir" ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( $Ordem == "" ){
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RelListagemFornecedor.Ordem.focus();\" class=\"titulo2\">Ordem</a>";
		}
		if( $Mens == 0 ){
				$Url = "RelListagemFornecedorPdf.php?Ordem=$Ordem&".mktime();
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
		}
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
function enviar(valor){
	document.RelListagemFornecedor.Botao.value=valor;
	document.RelListagemFornecedor.submit();
}

<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RelListagemFornecedor.php" method="post" name="RelListagemFornecedor">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedores > Relatórios > Listagem
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
	  <td width="100"></td>
	  <td align="left" colspan="2">
	  	<?php if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,1); } ?>
	  </td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
      <table  border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0"  bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" colspan="4" class="titulo3">
		    					LISTAGEM DOS FORNECEDORES
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para emitir a Listagem dos Fornecedores, selecione a ordem do relatório e clique no botão "Imprimir".
										Para limpar o campo, clique no botão "Limpar". <br><br>
						        Se você não possui o Acrobat Reader, clique <a href="javascript:janela('../pdf.php','Relatorio',400,400,1,0)" class="titulo2">AQUI</a> para fazer o download.
	          	   	</p>
	          		</td>
	          	</tr>
		        	<tr>
								<td class="textonormal">
									<table class="textonormal" border="0" align="left" summary="" width="100%">
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7" width="30%">Ordem </td>
				              <td class="textonormal">
				              	<select name="Ordem" class="textonormal">
				              		<option value="">Selecione uma Ordem...</option>
				              		<option value="C" <?php if( $Ordem == "C" ){ echo "selected"; }?>>CÓDIDO DO FORNECEDOR</option>
				              		<option value="N" <?php if( $Ordem == "N" ){ echo "selected"; }?>>NOME/RAZÃO SOCIAL</option>
				              	</select>
				              </td>
				            </tr>
									</table>
								</td>
        			</tr>
				      <tr>
			      		<td align="right">
			    				<input type="button" name="Imprimir" value="Imprimir" class="botao" onclick="javascript:enviar('Imprimir');">
			    				<input type="button" name="Limpar" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
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
