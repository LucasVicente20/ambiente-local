<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelFornecedorSituacao.php
# Autor:    Roberta Costa
# Data:     26/10/04
# Objetivo: Programa de Impressão dos Fornecedores Inscritos
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/fornecedores/RelFornecedorSituacao.php' );
AddMenuAcesso( '/fornecedores/RelFornecedorSituacaoPdf.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao    = $_POST['Botao'];
		$Situacao = $_POST['Situacao'];
}else{
		$Mensagem = urldecode($_GET['Mensagem']);
		$Mens     = $_GET['Mens'];
		$Tipo			= $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Limpar" ){
	  header("location: RelFornecedorSituacao.php");
	  exit;
}elseif( $Botao == "Imprimir" ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( $Situacao == 0 ){
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RelFornecedorSituacao.Situacao.focus();\" class=\"titulo2\">Situação</a>";
		}
		if( $Mens == 0 ){
				$Url = "RelFornecedorSituacaoPdf.php?Situacao=$Situacao&".mktime();
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
	document.RelFornecedorSituacao.Botao.value=valor;
	document.RelFornecedorSituacao.submit();
}

<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RelFornecedorSituacao.php" method="post" name="RelFornecedorSituacao">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedores > Relatórios > Fornecedores por Situação
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
		    					RELATÓRIO DOS FORNECEDORES POR SITUAÇÃO
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para imprimir os Fornecedores por Situação, preencha os campos abaixo e clique no botão "Imprimir".
										Para limpar os campos, clique no botão "Limpar". Os campos obrigatórios estão com *.<br><br>
						        Se você não possui o Acrobat Reader, clique <a href="javascript:janela('../pdf.php','Relatorio',400,400,1,0)" class="titulo2">AQUI</a> para fazer o download.
	          	   	</p>
	          		</td>
	          	</tr>
		        	<tr>
								<td class="textonormal">
									<table class="textonormal" border="0" align="left" summary="" width="100%">
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7" width="30%">Situação </td>
				              <td class="textonormal">
				              	<select name="Situacao" class="textonormal">
				              		<option value="">Selecione um Situação...</option>
				              		<?php
				              		$db   = Conexao();
  												$sql  = "SELECT CFORTSCODI,EFORTSDESC FROM SFPC.TBFORNECEDORTIPOSITUACAO ORDER BY EFORTSDESC";
  												$res  = $db->query($sql);
												  if( PEAR::isError($res) ){
														  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}else{
															while( $Linha = $res->fetchRow() ){
					          	      			$Descricao   = substr($Linha[1],0,75);
					          	      			if( $Linha[0] == $Grupo ){
										    	      			echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
								      	      		}else{
										    	      			echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
								      	      		}
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
