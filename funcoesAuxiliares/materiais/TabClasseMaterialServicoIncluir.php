<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabClasseMaterialServicoIncluir.php
# Autor:    Rossana Lira
# Data:     02/02/05
# Alterado: Rodrigo Melo
# Data:     08/05/07 - Correção de erro ao incluir classe em grupo de serviço.
# Objetivo: Programa de Inclusão de Classe de Material e Serviço
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
		$TipoGrupo	          = $_POST['TipoGrupo'];
		$TipoMaterial	        = $_POST['TipoMaterial'];
		$Grupo	 				      = $_POST['Grupo'];
		$ClasseDescricao      = strtoupper2(trim($_POST['ClasseDescricao']));
		$Situacao             = $_POST['Situacao'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Critica dos Campos #
if( $Critica == 1 ) {
		$Mens     = 0;
		$Mensagem = "Informe: ";
    if(( $TipoGrupo != 'M' )&&($TipoGrupo != 'S' )){
        $Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Tipo de Grupo";
		}
	  if( $Grupo == "" ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.Classe.Grupo.focus();\" class=\"titulo2\">Grupo</a>";
		}
	  if(($TipoGrupo == "M") && (( $TipoMaterial != 'C' )&&($TipoMaterial != 'P' ))){
        if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.Classe.TipoGrupo.focus();\" class=\"titulo2\">Tipo de Material</a>";
		}
	  if( $ClasseDescricao == "" ) {
				if( $Mens == 1 ){ $Mensagem .= ", "; }
		    $Mens = 1;$Tipo = 2;
  			$Mensagem .= "<a href=\"javascript:document.Classe.ClasseDescricao.focus();\" class=\"titulo2\">Classe</a>";
    }
	  if( $Mens == 0 ) {
	  	  # Verifica a Duplicidade de Classe #
				$db     = Conexao();
		   	$sql    = "SELECT COUNT(CCLAMSCODI) FROM SFPC.TBCLASSEMATERIALSERVICO ";
		   	$sql   .= "WHERE 	RTRIM(LTRIM(ECLAMSDESC)) = '$ClasseDescricao' ";
		   	$sql   .= "AND 		CGRUMSCODI = $Grupo";
		 		$result = $db->query($sql);
				if( PEAR::isError($result) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
				    $Linha = $result->fetchRow();
				    $Qtd = $Linha[0];
		    		if( $Qtd > 0 ) {
					    	$Mens = 1;$Tipo = 2;
								$Mensagem = "<a href=\"javascript:document.Classe.ClasseDescricao.focus();\" class=\"titulo2\">Classe de Material ou Serviço Já Cadastrada</a>";
						}else{
								# Recupera o último Classe e incrementa mais um
						    $sql    = "SELECT MAX(CCLAMSCODI) FROM SFPC.TBCLASSEMATERIALSERVICO ";
		   					$sql   .= "WHERE	CGRUMSCODI = $Grupo";
						    $result = $db->query($sql);
								if( PEAR::isError($result) ){
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
						    		$Linha = $result->fetchRow();
						 		   	$Codigo = $Linha[0] + 1;

								    # Insere Classe #
								    $Data   = date("Y-m-d H:i:s");
								    $db->query("BEGIN TRANSACTION");
								    $sql    = "INSERT INTO SFPC.TBCLASSEMATERIALSERVICO ( ";
								    $sql   .= "CGRUMSCODI, CCLAMSCODI, ECLAMSDESC, FCLAMSSITU, TCLAMSULAT ";
								    $sql   .= ") VALUES ( ";
										$sql   .= "$Grupo, $Codigo, '$ClasseDescricao', '$Situacao','$Data')";
								    $result = $db->query($sql);
										if( PEAR::isError($result) ){
												$db->query("ROLLBACK");
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$Mens                 = 1;
												$Tipo                 = 1;
												$Mensagem             = "Classe Incluída com Sucesso";
												$TipoGrupo	          = "";
												$TipoMaterial	        = "";
												$Grupo						    = "";
												$ClasseDescricao      = "";
												$Situacao             = "";
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
<form action="TabClasseMaterialServicoIncluir.php" method="post" name="Classe">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais > Classe > Incluir
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
      <table  border="0" cellspacing="0" cellpadding="3" summary="">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
		    					INCLUIR - CLASSE DE MATERIAL OU SERVIÇO
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para incluir uma nova Classe de Material ou Serviço, selecione o Tipo, o Grupo, informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.
	          	   	</p>
	          		</td>
		        	</tr>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left" summary="">
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7" width="40%">Tipo de Grupo*</td>
				              <td class="textonormal">
				              	<input type="radio" name="TipoGrupo" value="M" onClick="javascript:document.Classe.Critica.value=0;document.Classe.submit();" <?php if( $TipoGrupo == "M" ){ echo "checked"; } ?> > Material
				              	<input type="radio" name="TipoGrupo" value="S" onClick="javascript:document.Classe.Critica.value=0;document.Classe.submit();" <?php if( $TipoGrupo == "S" ){ echo "checked"; }?> > Serviço
				              </td>
				            </tr>
				            <?php if ($TipoGrupo == "M") { ?>
					            <tr>
					              <td class="textonormal" bgcolor="#DCEDF7" width="40%">Tipo de Material</td>
					              <td class="textonormal">
					              	<input type="radio" name="TipoMaterial" value="C" onClick="javascript:document.Classe.Critica.value=0;document.Classe.submit();" <?php if( $TipoMaterial == "C" ){ echo "checked"; } ?> > Consumo
					              	<input type="radio" name="TipoMaterial" value="P" onClick="javascript:document.Classe.Critica.value=0;document.Classe.submit();" <?php if( $TipoMaterial == "P" ){ echo "checked"; }?> > Permanente
					              </td>
					            </tr>
			 		          <?php } ?>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Grupo* </td>
				              <td class="textonormal">
				              	<select name="Grupo" class="textonormal" >
				              		<option value="">Selecione um Grupo...</option>
				              		<?php
													if( $TipoGrupo == "M" or $TipoGrupo == "S") {
				              			$db   = Conexao();
														if( $TipoMaterial == "C" or $TipoMaterial == "P") {
															$sql 		= "SELECT CGRUMSCODI,EGRUMSDESC FROM SFPC.TBGRUPOMATERIALSERVICO ";
															$sql   .= "WHERE  FGRUMSTIPO = 'M' AND FGRUMSSITU = 'A' AND FGRUMSTIPM = '$TipoMaterial' ";
					                		$sql   .= "ORDER  BY EGRUMSDESC";
					                		$result = $db->query($sql);
					                		if (PEAR::isError($result)) {
															    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
															}else{
																	while( $Linha = $result->fetchRow() ){
						          	      			$Descricao   = substr($Linha[1],0,75);
						          	      			if( $Linha[0] == $Grupo ){
											    	      			echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
									      	      		}else{
											    	      			echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
									      	      		}
									      	      	}
								              }
					                	}	else {
															if( $TipoGrupo == "S" ){
						                	  # Mostra os grupos cadastrados #
						                		$db     = Conexao();
																$sql 		= "SELECT CGRUMSCODI,EGRUMSDESC FROM SFPC.TBGRUPOMATERIALSERVICO ";
																$sql   .= "WHERE FGRUMSTIPO = 'S' ORDER BY EGRUMSDESC";
						                		$result = $db->query($sql);
						                		if (PEAR::isError($result)) {
																    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																}else{
																		while( $Linha = $result->fetchRow() ){
							          	      			$Descricao   = substr($Linha[1],0,75);
							          	      			if( $Linha[0] == $Grupo ){
												    	      			echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
										      	      		}else{
												    	      			echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
										      	      		}
									                	}
									              }
						  	              }
						  	            }
						  	            $db->disconnect();
						  	          }
				              		?>
				              	</select>
				              </td>
				            </tr>
	      	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Classe*</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="ClasseDescricao" value="<?php echo $ClasseDescricao; ?>" size="45" maxlength="100" class="textonormal">
	            	  			<input type="hidden" name="Critica" value="1">
	            	  		</td>
	            			</tr>
	            			<tr>
		              		<td class="textonormal"  bgcolor="#DCEDF7">Situação*</td>
		              		<td class="textonormal" >
	  	              		<select name="Situacao" size="1" value="A"  class="textonormal">
	      	            		<option value="A">ATIVO </option>
	    	              		<option value="I">INATIVO</option>
	        	        		</select>
	          	    		</td>
	            			</tr>
	            		</table>
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
<script language="javascript" type="">
<!--
document.Classe.Grupo.focus();
//-->
</script>
</body>
</html>
