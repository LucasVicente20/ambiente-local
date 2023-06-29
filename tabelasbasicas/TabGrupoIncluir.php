<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabGrupoIncluir.php
# Autor:    Rossana Lira
# Data:     02/04/03
# Objetivo: Programa de Inclusão de Grupo
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Critica              = $_POST['Critica'];
		$GrupoDescricao       = strtoupper2(trim($_POST['GrupoDescricao']));
		$Email                = trim($_POST['Email']);
		$WWW                  = trim($_POST['WWW']);
		$Fone                 = trim($_POST['Fone']);
		$OrgaoLicitanteCodigo = $_POST['OrgaoLicitanteCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabGrupoIncluir.php";

# Critica dos Campos #
if( $Critica == 1 ) {
		$Mens     = 0;
		$Mensagem = "Informe: ";
	  if( $GrupoDescricao == "" ) {
		    $Mens = 1;$Tipo = 2;
  			$Mensagem .= "<a href=\"javascript:document.Grupo.GrupoDescricao.focus();\" class=\"titulo2\">Grupo</a>";
    }
		if( $Email != "" and !strchr($Email, "@")){
		    if ($Mens == 1){$Mensagem.=", ";}
		    $Mens = 1;$Tipo = 2;
    		$Mensagem .= "<a href=\"javascript:document.Grupo.Email.focus();\" class=\"titulo2\">E-Mail Válido</a>";
		}
	  if( $Fone == "" ){
		    if ($Mens == 1){$Mensagem.=", ";}
		    $Mens = 1;$Tipo = 2;
    		$Mensagem .= "<a href=\"javascript:document.Grupo.Fone.focus();\" class=\"titulo2\">Telefone</a>";
		}
	  if( $Mens == 0 ) {
	  	  # Verifica a Duplicidade de Grupo #
				$db     = Conexao();
		   	$sql    = "SELECT COUNT(CGREMPCODI) FROM SFPC.TBGRUPOEMPRESA WHERE RTRIM(LTRIM(EGREMPDESC)) = '$GrupoDescricao'";
		 		$result = $db->query($sql);
				if( PEAR::isError($result) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
				    $Linha = $result->fetchRow();
				    $Qtd = $Linha[0];
		    		if( $Qtd > 0 ) {
					    	$Mens = 1;$Tipo = 2;
								$Mensagem = "<a href=\"javascript:document.Grupo.GrupoDescricao.focus();\" class=\"titulo2\">Grupo Já Cadastrado</a>";
						}else{
								# Recupera o último grupo e incrementa mais um
						    $sql    = "SELECT MAX(CGREMPCODI) FROM SFPC.TBGRUPOEMPRESA";
						    $result = $db->query($sql);
								if( PEAR::isError($result) ){
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
						    		$Linha = $result->fetchRow();
						 		   	$Codigo = $Linha[0] + 1;

								    # Insere Grupo #
								    $Data   = date("Y-m-d H:i:s");
								    $db->query("BEGIN TRANSACTION");
								    $sql    = "INSERT INTO SFPC.TBGRUPOEMPRESA ( ";
								    $sql   .= "CGREMPCODI, EGREMPDESC, EGREMPMAIL, EGREMPENDW, AGREMPFONE, TGREMPULAT ";
								    $sql   .= ") VALUES ( ";
										$sql   .= "$Codigo, '$GrupoDescricao', '$Email', '$WWW','$Fone','$Data')";
								    $result = $db->query($sql);
										if( PEAR::isError($result) ){
												$db->query("ROLLBACK");
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												# Grava os órgãos licitantes marcados na tabela de GrupoOrgao #
												for( $P = 0; $P < count($OrgaoLicitanteCodigo); $P++ ) {
															$OrgaoCodigo = $OrgaoLicitanteCodigo[$P];
															$Data   = date("Y-m-d H:i:s");
										    			$sql    = "INSERT INTO SFPC.TBGRUPOORGAO ( ";
										    			$sql   .= "CGREMPCODI, CORGLICODI, TGRUORULAT ";
										    			$sql   .= ") VALUES ( ";
										    			$sql   .= "$Codigo, $OrgaoCodigo, '$Data' )";
													    $result = $db->query($sql);
															if (PEAR::isError($result)) {
																	$db->query("ROLLBACK");
															    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
															}
												}
												$Mens                 = 1;
												$Tipo                 = 1;
												$Mensagem             = "Grupo Incluído com Sucesso";
												$GrupoDescricao       = "";
												$Email                = "";
												$WWW                  = "";
												$Fone                 = "";
												$OrgaoLicitanteCodigo = "";
										}
										$db->query("COMMIT");
										$db->query("END TRANSACTION");
								}
						}
				}
		    $db->disconnect();
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
<form action="TabGrupoIncluir.php" method="post" name="Grupo">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Grupo > Incluir
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
	  <td width="100"></td>
	  <td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php } ?>
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
		    					INCLUIR - GRUPO
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para incluir um novo grupo, informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.
	        	    		Segure a tecla (CTRL) e clique no botão esquerdo do mouse para selecionar mais de um órgão licitante.
	          	   	</p>
	          		</td>
		        	</tr>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left" class="caixa">
	      	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Grupo*</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="GrupoDescricao" value="<?php echo $GrupoDescricao; ?>" size="45" maxlength="60" class="textonormal">
	            	  			<input type="hidden" name="Critica" value="1">
	            	  		</td>
	            			</tr>
										<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">E-mail</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="Email" value="<?php echo $Email; ?>" size="45" maxlength="60" class="textonormal">
	          	    		</td>
	            	  	</tr>
	            			<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Home Page</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="WWW" value="<?php echo $WWW; ?>" size="45" maxlength="60" class="textonormal">
	          	    		</td>
	            	  	</tr>
										<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Telefone*</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="Fone" value="<?php echo $Fone; ?>" size="25" maxlength="25" class="textonormal">
	          	    		</td>
	            	  	</tr>
	            	   	<tr>
	            	   		<td class="textonormal" bgcolor="#DCEDF7">Orgãos Licitantes</td>
	            	   		<td class="textonormal">
												<select name="OrgaoLicitanteCodigo[]" multiple size="8" value="" class="textonormal">
													<?php
													$db     = Conexao();
													$sql    = "SELECT CORGLICODI,EORGLIDESC FROM SFPC.TBORGAOLICITANTE ORDER BY EORGLIDESC";
													$result = $db->query($sql);
													if (PEAR::isError($result)) {
													    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}else{
															while( $Linha = $result->fetchRow() ){
																	if( FindArray($Linha[0],$OrgaoLicitanteCodigo) ){
																			echo "<option value=\"$Linha[0]\" selected>$Linha[1]\n";
																	}else{
																			echo "<option value=\"$Linha[0]\">$Linha[1]\n";
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
</form>
</body>
</html>
<script language="javascript" type="">
<!--
document.Grupo.GrupoDescricao.focus();
//-->
</script>
