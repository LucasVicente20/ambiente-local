<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabGrupoMaterialServicoIncluir.php
# Autor:    Rossana Lira
# Data:     01/02/05
# Objetivo: Programa de Inclusão de Grupo de Material e Serviço
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Critica        = $_POST['Critica'];
		$TipoGrupo	    = $_POST['TipoGrupo'];
		$TipoMaterial   = $_POST['TipoMaterial'];
		$GrupoDescricao = strtoupper2(trim($_POST['GrupoDescricao']));
		$Situacao       = $_POST['Situacao'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Critica dos Campos #
if( $Critica == 1 ) {
		$Mens     = 0;
		$Mensagem = "Informe: ";

	  if(( $TipoGrupo != 'S')&&( $TipoGrupo != 'M')){
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Tipo de Grupo";
		}
	  if( $GrupoDescricao == "" ) {
	  	if( $Mens == 1 ){ $Mensagem .= ", "; }
		    $Mens = 1;
		    $Tipo = 2;
  			$Mensagem .= "<a href=\"javascript:document.Grupo.GrupoDescricao.focus();\" class=\"titulo2\">Grupo</a>";
    }
	  if( $Mens == 0 ) {
	  	  # Verifica a Duplicidade de Grupo #
				$db     = Conexao();
		   	$sql    = "SELECT COUNT(CGRUMSCODI) FROM SFPC.TBGRUPOMATERIALSERVICO ";
		   	$sql   .= " WHERE RTRIM(LTRIM(EGRUMSDESC)) = '$GrupoDescricao' AND FGRUMSTIPO = '$TipoGrupo'";
		 		$result = $db->query($sql);
				if( PEAR::isError($result) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
				    $Linha = $result->fetchRow();
				    $Qtd = $Linha[0];
		    		if( $Qtd > 0 ) {
					    	$Mens = 1;$Tipo = 2;
								$Mensagem = "<a href=\"javascript:document.Grupo.GrupoDescricao.focus();\" class=\"titulo2\">Grupo Já Cadastrado para o Tipo de Material ou Serviço</a>";						}else{
								# Recupera o último grupo e incrementa mais um
						    $sql    = "SELECT MAX(CGRUMSCODI) FROM SFPC.TBGRUPOMATERIALSERVICO";
						    $result = $db->query($sql);
								if( PEAR::isError($result) ){
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
						    		$Linha = $result->fetchRow();
						 		   	$Codigo = $Linha[0] + 1;

								    # Insere Grupo #
								    $Data   = date("Y-m-d H:i:s");
								    $db->query("BEGIN TRANSACTION");
								    $sql    = "INSERT INTO SFPC.TBGRUPOMATERIALSERVICO ( ";
								    $sql   .= "CGRUMSCODI, FGRUMSTIPO, FGRUMSTIPM, EGRUMSDESC, FGRUMSSITU, TGRUMSULAT ";
								    $sql   .= ") VALUES ( ";
										$sql   .= "$Codigo, '$TipoGrupo', '$TipoMaterial', '$GrupoDescricao', '$Situacao','$Data')";
								    $result = $db->query($sql);
										if( PEAR::isError($result) ){
												$db->query("ROLLBACK");
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$Mens                 = 1;
												$Tipo                 = 1;
												$Mensagem             = "Grupo Incluído com Sucesso";
												$TipoGrupo			      = "";
												$GrupoDescricao       = "";
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
<form action="TabGrupoMaterialServicoIncluir.php" method="post" name="Grupo">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais > Grupo > Incluir
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
		    					INCLUIR - GRUPO DE MATERIAL OU SERVIÇO
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para incluir um novo Grupo de Material ou Serviço, informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.
	          	   	</p>
	          		</td>
		        	</tr>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left" summary="">
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7" width="40%">Tipo de Grupo* </td>
				              <td class="textonormal">
				              	<input type="radio" name="TipoGrupo" value="M" onClick="javascript:document.Grupo.Critica.value=0;document.Grupo.submit();"  <?php if( $TipoGrupo == "M" ){ echo "checked"; } ?> > Material
				              	<input type="radio" name="TipoGrupo" value="S" onClick="javascript:document.Grupo.Critica.value=0;document.Grupo.submit();"<?php if( $TipoGrupo == "S" ){ echo "checked"; }?> > Serviço
				              </td>
				            </tr>
				            <?php if ($TipoGrupo == "M") { ?>
					            <tr>
					              <td class="textonormal" bgcolor="#DCEDF7" width="40%">Tipo de Material*</td>
					              <td class="textonormal">
					              	<input type="radio" name="TipoMaterial" value="C" <?php if( $TipoMaterial == "" or $TipoMaterial == "C" ){ echo "checked"; } ?> > Consumo
					              	<input type="radio" name="TipoMaterial" value="P" <?php if( $TipoMaterial == "P" ){ echo "checked"; }?> > Permanente
					              </td>
					            </tr>
			 		          <?php } else {
			 		          				$TipoMaterial = "";
			 		          } ?>
	      	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Grupo*</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="GrupoDescricao" value="<?php echo $GrupoDescricao; ?>" size="45" maxlength="100" class="textonormal">
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
document.Grupo.GrupoDescricao.focus();
//-->
</script>
</body>
</html>
