<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabModalidadeIncluir.php
# Autor:    Rossana Lira
# Data:     02/04/03
# Objetivo: Programa de Inclusão de Modalidade
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
		$Botao               = $_POST['Botao'];
		$ModalidadeDescricao = strtoupper2(trim($_POST['ModalidadeDescricao']));
		$Ordem               = trim($_POST['Ordem']);
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Incluir" ) {
		# Critica dos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";
	  if( $ModalidadeDescricao == "" ) {
		    $Mens      = 1;
		    $Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.Modalidade.ModalidadeDescricao.focus();\" class=\"titulo2\">Modalidade</a>";
    }
	  if( $Ordem == "" ){
		    if ($Mens == 1){$Mensagem.=", ";}
		    $Mens      = 1;
		    $Tipo      = 2;
    		$Mensagem .= "<a href=\"javascript:document.Modalidade.Ordem.focus();\" class=\"titulo2\"> Ordem de Exibição </a>";
    }else{
	    	if( !SoNumeros($Ordem) ){
		    		$Mens = 1;$Tipo = 2;
		    		$Mensagem = "<a href=\"javascript:document.Modalidade.Ordem.focus();\" class=\"titulo2\"> Ordem de Exibição Inválida</a>";
				}
		}
	  if( $Mens == 0 ) {
	  	  # Verifica a Duplicidade de Modalidade #
				$db  = Conexao();
		   	$sql = "SELECT COUNT(CMODLICODI) FROM SFPC.TBMODALIDADELICITACAO WHERE RTRIM(LTRIM(EMODLIDESC)) = '$ModalidadeDescricao' ";
		 		$res = $db->query($sql);
				if( PEAR::isError($res) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
		    		$Qtd = $res->fetchRow();
		    		if( $Qtd[0] > 0 ) {
					    	$Mens     = 1;
					    	$Tipo     = 2;
								$Mensagem = "<a href=\"javascript:document.Modalidade.ModalidadeDescricao.focus();\" class=\"titulo2\"> Modalidade Já Cadastrada</a>";
						}else{
						    # Verifica a Duplicidade da Ordem #
								$sql = "SELECT COUNT(CMODLICODI) FROM SFPC.TBMODALIDADELICITACAO WHERE AMODLIORDE = $Ordem";
						 		$res = $db->query($sql);
								if( PEAR::isError($res) ){
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
						    		$Qtd = $res->fetchRow();
						    		if( $Qtd[0] > 0 ) {
									    	$Mens     = 1;
									    	$Tipo     = 2;
												$Mensagem = "<a href=\"javascript:document.Modalidade.Ordem.focus();\" class=\"titulo2\"> Ordem de Exibição Já Cadastrada</a>";
										}else{
												# Recupera a última Modalidade e incrementa mais um #
												$sql = "SELECT MAX(CMODLICODI) FROM SFPC.TBMODALIDADELICITACAO";
												$res = $db->query($sql);
												if (PEAR::isError($res)) {
												    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Linha  = $res->fetchRow();
														$Codigo = $Linha[0] + 1;

														# Insere Modalidade #
														$sql  = "INSERT INTO SFPC.TBMODALIDADELICITACAO ( ";
														$sql .= "CMODLICODI, AMODLIORDE, EMODLIDESC, ";
														$sql .= "TMODLIULAT ";
														$sql .= " ) VALUES ( ";
														$sql .= "$Codigo, $Ordem, '$ModalidadeDescricao', ";
														$sql .= "'".date("Y-m-d H:i:s")."' )";
														$res  = $db->query($sql);
														if( PEAR::isError($result) ){
														    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
																# Limpa Variáveis #
																$ModalidadeDescricao = "";
																$Ordem               = "";
																$Mens                = 1;
																$Tipo                = 1;
																$Mensagem            = "Modalidade Incluída com Sucesso";
														}
												}
									  }
								}
						}
				}
		    $db->disconnect();
		}
}
?>
<html>
<?php 
# Carrega o layout padrão
layout();
?>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.Modalidade.Botao.value = valor;
	document.Modalidade.submit();
}
<?php  MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabModalidadeIncluir.php" method="post" name="Modalidade">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Modalidade > Incluir
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php  if ( $Mens == 1 ) {?>
	<tr>
	  <td width="100"></td>
	  <td align="left" colspan="2"><?php  ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php  } ?>
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
		    					INCLUIR - MODALIDADE
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para incluir uma nova Modalidade, informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.
	        	    	</p>
	          		</td>
		        	</tr>
		        	<tr>
	  	        	<td>
	    	      		<table border="0" width="100%">
	      	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7" width="30%">Modalidade*</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="ModalidadeDescricao" value="<?php  echo $ModalidadeDescricao; ?>" size="45" maxlength="50" class="textonormal">
	          	    		</td>
	            			</tr>
										<tr>
											<td class="textonormal"  bgcolor="#DCEDF7">Ordem de Exibição*</td>
											<td class="textonormal">
												<input type="text" name="Ordem" size="2" value="<?php  echo $Ordem; ?>" maxlength="2" class="textonormal">
											</td>
	            	  	</tr>
	            		</table>
		          	</td>
		        	</tr>
	  	      	<tr>
  	  	  			<td class="textonormal" align="right">
	          	  	<input type="button" name="Incluir" value="Incluir" class="botao" onClick="javascript:enviar('Incluir')">
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
<script language="javascript" type="">
<!--
document.Modalidade.ModalidadeDescricao.focus();
//-->
</script>
