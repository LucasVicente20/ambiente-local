<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabAcessoIncluir.php
# Autor:    Roberta Costa
# Data:     09/04/2003
# Objetivo: Programa de Inclusão de Acesso
# Alterado: Carlos Abreu
# Data:     28/06/2007 - corrigido erro no cadastramento do caminho quando caminho é NULL
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------
# Alterado: João Madson
# Data:     04/03/2021
# Objetivo: Tarefa Redmine 244819
#-------------------------------------------------------------------------


# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Critica          = $_POST['Critica'];
		$AcessoDescricao  = trim($_POST['AcessoDescricao']);
		$AcessoCaminho    = trim($_POST['AcessoCaminho']);
		$AcessoOrdem      = trim($_POST['AcessoOrdem']);
		$HierarquiaCodigo = $_POST['HierarquiaCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabAcessoIncluir.php";

$Qtd_Caracters         = strlen($AcessoDescricao);
$Qtd_Caracters_Caminho = strlen($AcessoCaminho);

# Critica dos Campos #
if( $Critica == 1 ) {
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( $AcessoDescricao == "" ) {
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.Acesso.AcessoDescricao.focus();\" class=\"titulo2\">Descrição</a>";
		}
		if( $AcessoOrdem == "" || ! SoNumeros($AcessoOrdem) ){
				if ($Mens == 1){$Mensagem.=", ";}
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.Acesso.AcessoOrdem.focus();\" class=\"titulo2\">Ordem Válida</a>";
		}
		if( ( $HierarquiaCodigo == "") and ($Qtd_Caracters > 13)) {
			  $Mens     = 1;
			  $Tipo     = 2;
			  $Mensagem = "<a href=\"javascript:document.Acesso.AcessoDescricao.focus();\" class=\"titulo2\">Esse Acesso</a> é de primeiro nível, deve conter no máximo 13 caracteres";
		}
		if( $Qtd_Caracters > 30) {
			  $Mens     = 1;
			  $Tipo     = 2;
			  $Mensagem = "<a href=\"javascript:document.Acesso.AcessoDescricao.focus();\" class=\"titulo2\">A descrição do Acesso</a> deve conter no máximo 30 caracteres";
		}
		if( $Qtd_Caracters_Caminho > 255) {
			  $Mens     = 1;
			  $Tipo     = 2;
			  $Mensagem = "<a href=\"javascript:document.Acesso.AcessoCaminho.focus();\" class=\"titulo2\">O caminho do acesso</a> deve conter no máximo 255 caracteres";
		}else{
				if( $Mens == 0 ) {
						$db     = Conexao();
						$sql    = "SELECT MAX(CACEPOCODI) FROM SFPC.TBACESSOPORTAL";
						$result = $db->query($sql);
						if( PEAR::isError($result) ){
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$Linha = $result->fetchRow();
								if ( $Linha[0] == "" ) { $Codigo  = 1; } else { 	$Codigo = $Linha[0] + 1; }
								if ( $HierarquiaCodigo == "" ) { $CodigoPai = $Codigo; } else { $CodigoPai = $HierarquiaCodigo; }
								if ( $AcessoCaminho == "" ) { $Caminho = "NULL"; } else { $Caminho = "'$AcessoCaminho'"; }

								# Insere na tabela SFPC.TBACESSSO #
								$Data   = date("Y-m-d H:i:s");
								$db->query("BEGIN TRANSACTION");
								$sql    = "INSERT INTO SFPC.TBACESSOPORTAL ( ";
								$sql   .= "CACEPOCODI, EACEPODESC, CACEPOCPAI, ";
								$sql   .= "AACEPOORDE, EACEPOCAMI, TACEPOULAT ";
								$sql   .= ") VALUES ( ";
								$sql   .= "$Codigo, '$AcessoDescricao', $CodigoPai, ";
								$sql   .= "$AcessoOrdem , $Caminho, '$Data')";
								$result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("BEGIN TRANSACTION");
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$db->query("COMMIT");
										$db->query("END TRANSACTION");
										$db->disconnect();

										# Limpando Variáveis #
										$Mens             = 1;
										$Tipo             = 1;
										$Mensagem         = "Acesso Incluído com Sucesso";
										$AcessoDescricao  = "";
										$AcessoCaminho    = "";
										$HierarquiaCodigo = "";
										$AcessoOrdem      = "";
								}
						}
				}
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
<form action="TabAcessoIncluir.php" method="post" name="Acesso">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Acesso > Incluir
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
	  <td width="100"></td>
	  <td align="left" colspan="2"><?php if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,1); } ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
      <table  border="0" cellspacing="0" cellpadding="3" bgcolor="#FFFFFF">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
		    					INCLUIR - ACESSO
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal" >
	      	    		<p align="justify">
	        	    		Para incluir um novo acesso, informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *. O acesso será de primeiro nível se nenhuma hierarquia for selecionada.
	          	   	</p>
	          		</td>
		        	</tr>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left">
	      	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7" width="30%">Descrição*</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="AcessoDescricao" size="45" maxlength="30" value="<?php echo $AcessoDescricao;?>" class="textonormal" style="text-Transform: none; ">
	            	  			<input type="hidden" name="Critica" value="1">
	            	  		</td>
	            			</tr>
										<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Caminho</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="AcessoCaminho" size="45" maxlength="255" value="<?php echo $AcessoCaminho;?>" class="textonormal" style="text-Transform: none; ">
	          	    		</td>
	            			</tr>
	            			<tr>
		              		<td class="textonormal"  bgcolor="#DCEDF7">Hierarquia</td>
		              		<td class="textonormal">
		  				  	      <select name="HierarquiaCodigo" class="textonormal">
													<option value="">Selecione uma Ordem - Hierarquia...</option>
													<?php
													$db     = Conexao();
													$sql    = "SELECT CACEPOCODI, EACEPODESC, AACEPOORDE FROM SFPC.TBACESSOPORTAL ";
													$sql   .= " WHERE CACEPOCODI = CACEPOCPAI ORDER BY AACEPOORDE";
													$result = $db->query($sql);
													if( PEAR::isError($result) ){
													    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}else{
															while( $Linha = $result->fetchRow() ){
																	if( $Linha[0] == $HierarquiaCodigo ){
																			echo "<option value=\"$Linha[0]\" selected>$Linha[2] - $Linha[1]</option>\n";
																	}else{
																			echo "<option value=\"$Linha[0]\">$Linha[2] - $Linha[1]</option>\n";
																	}
																	TipoSon($Linha[0], $db, 1, $HierarquiaCodigo);
															}
													}
													$db->disconnect();
													?>
											  </select>
										  </td>
	            			</tr>
	            			<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Ordem*</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="AcessoOrdem" size="2" maxlength="3" value="<?php echo $AcessoOrdem;?>" class="textonormal">
	          	    		</td>
	            			</tr>
	          			</table>
		          	</td>
		        	</tr>
	  	      	<tr>
	    	  			<td class="textonormal" align="right">
	          	  	<input type="submit" name="Incluir" value="Incluir" class="botao">
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
<?php
# Verifica se um Acesso tem Filhos #
function TipoSon($_AcessoCodigoPai_, $_Db_, $_Nivel_, $_HierarquiaCodigo_) {
	$Endentacao = str_repeat("&nbsp;&nbsp;&nbsp;",$_Nivel_ );
	$db     = Conexao();
	$sql    = "SELECT CACEPOCODI, EACEPODESC, AACEPOORDE FROM SFPC.TBACESSOPORTAL ";
	$sql   .= " WHERE CACEPOCODI <> CACEPOCPAI ";
	$sql   .= "   AND CACEPOCPAI = $_AcessoCodigoPai_ ";
	$sql   .= " ORDER BY AACEPOORDE";
	$result = $db->query($sql);
	if( PEAR::isError($result) ){
	    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	}
	while( $Linha = $result->fetchRow() ){
			if( $Linha[0] == $_HierarquiaCodigo_ ){
					echo "<option value=\"$Linha[0]\" selected>$Endentacao $Linha[2] - $Linha[1]\n";
			}else{
					echo "<option value=\"$Linha[0]\">$Endentacao $Linha[2] - $Linha[1]\n";
			}
			TipoSon($Linha[0], $_Db_, $_Nivel_+1, $_HierarquiaCodigo_);
	}
	return;
}
?>
</form>
</body>
</html>
<script language="javascript" type="">
<!--
 document.Acesso.AcessoDescricao.focus();
//-->
</script>
