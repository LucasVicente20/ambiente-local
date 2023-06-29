<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabSubclasseMaterialIncluir.php
# Autor:    Roberta Costa
# Data:     06/06/05
# Objetivo: Programa de Inclusão de Subclasse de Material
# Alterado: Rossana Lira
# Data    : 31/10/2007 - Aumentar exibição do grupo e da classe
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
		$Botao              = $_POST['Botao'];
		$TipoMaterial	      = $_POST['TipoMaterial'];
		$Grupo	 				    = $_POST['Grupo'];
		$Classe	 				    = $_POST['Classe'];
		$SubclasseDescricao = strtoupper2(trim($_POST['SubclasseDescricao']));
		$Situacao           = $_POST['Situacao'];
		$Critica            = $_POST['Critica'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Critica dos Campos #
if( $Botao == "Incluir" ) {
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if(( $TipoMaterial != 'C' )&&($TipoMaterial != 'P')){
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Tipo Material";
		}
		if( $Grupo == "" ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.Subclasse.Grupo.focus();\" class=\"titulo2\">Grupo</a>";
		}
		if( $Classe == "" ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.Subclasse.Classe.focus();\" class=\"titulo2\">Classe</a>";
		}
	  if( $SubclasseDescricao == "" ) {
				if( $Mens == 1 ){ $Mensagem .= ", "; }
		    $Mens      = 1;
		    $Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.Subclasse.SubclasseDescricao.focus();\" class=\"titulo2\">Subclasse</a>";
    }
	  if( $Mens == 0 ) {
	  	  # Verifica a Duplicidade de Subclasse #
				$db     = Conexao();
		   	$sql    = "SELECT COUNT(CSUBCLCODI) FROM SFPC.TBSUBCLASSEMATERIAL ";
		   	$sql   .= " WHERE RTRIM(LTRIM(ESUBCLDESC)) = '$SubclasseDescricao' ";
		   	$sql   .= "   AND CGRUMSCODI = $Grupo AND CCLAMSCODI = $Classe ";
		 		$result = $db->query($sql);
				if( PEAR::isError($result) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
				    $Linha = $result->fetchRow();
				    $Qtd = $Linha[0];
		    		if( $Qtd > 0 ) {
					    	$Mens     = 1;
					    	$Tipo     = 2;
								$Mensagem = "<a href=\"javascript:document.Subclasse.SubclasseDescricao.focus();\" class=\"titulo2\">Subclasse de Material Já Cadastrada</a>";
						}else{
								# Recupera o último Subclasse e incrementa mais um #
						    $sql    = "SELECT MAX(CSUBCLCODI) FROM SFPC.TBSUBCLASSEMATERIAL ";
		   					$sql   .= "WHERE	CGRUMSCODI = $Grupo AND CCLAMSCODI = $Classe ";
						    $result = $db->query($sql);
								if( PEAR::isError($result) ){
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
						    		$Linha = $result->fetchRow();
						 		   	$Codigo = $Linha[0] + 1;

								    # Insere Subclasse #
								    $Data   = date("Y-m-d H:i:s");
								    $db->query("BEGIN TRANSACTION");
								    $sql    = "INSERT INTO SFPC.TBSUBCLASSEMATERIAL ( ";
								    $sql   .= "CSUBCLSEQU, CGRUMSCODI, CCLAMSCODI, CSUBCLCODI, ";
								    $sql   .= "ESUBCLDESC, FSUBCLSITU, TSUBCLULAT ";
								    $sql   .= ") VALUES ( ";
										$sql   .= "nextval('sfpc.tbsubclassematerial_csubclsequ_seq'), $Grupo, $Classe, $Codigo, ";
										$sql   .= "'$SubclasseDescricao', '$Situacao', '$Data')";
								    $result = $db->query($sql);
										if( PEAR::isError($result) ){
												$db->query("ROLLBACK");
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$Mens                 = 1;
												$Tipo                 = 1;
												$Mensagem             = "Subclasse Incluída com Sucesso";
												$SubclasseDescricao   = "";
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
function enviar(valor){
	document.Subclasse.Botao.value = valor;
	document.Subclasse.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabSubclasseMaterialIncluir.php" method="post" name="Subclasse">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais > Subclasse > Incluir
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
		    					INCLUIR - SUBCLASSE DE MATERIAL
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para incluir uma nova Subclasse de Material, selecione o Tipo, o Grupo, a Classe, informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.
	          	   	</p>
	          		</td>
		        	</tr>
						  <?php
							if ($Critica == 0) {
								$TipoMaterial = 'C';
							}
							?>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left" summary="">
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7" width="40%">Tipo de Material
	            	  			<input type="hidden" name="Critica" value="1">
				              </td>
				              <td class="textonormal">
				              	<input type="radio" name="TipoMaterial" value="C" onClick="document.Subclasse.submit();" <?php if( $TipoMaterial == "C" ){ echo "checked"; } ?> > Consumo
				              	<input type="radio" name="TipoMaterial" value="P" onClick="document.Subclasse.submit();" <?php if( $TipoMaterial == "P" ){ echo "checked"; }?> > Permanente
				              </td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Grupo* </td>
				              <td class="textonormal">
				              	<select name="Grupo" class="textonormal" onChange="submit();">
				              		<option value="">Selecione um Grupo...</option>
				              		<?php
			                	  # Mostra os grupos cadastrados #
													if( $TipoMaterial == "C" or $TipoMaterial == "P") {
						                	$db   = Conexao();
															$sql  = "SELECT CGRUMSCODI,EGRUMSDESC FROM SFPC.TBGRUPOMATERIALSERVICO ";
															$sql .= " WHERE FGRUMSTIPO = 'M' AND FGRUMSSITU = 'A' AND FGRUMSTIPM = '$TipoMaterial' ";
					                		$sql .= " ORDER BY EGRUMSDESC";
					                		$res  = $db->query($sql);
					                		if( PEAR::isError($res) ){
															    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
															}else{
																	while( $Linha = $res->fetchRow() ){
							          	      			$Descricao = substr($Linha[1],0,80);
							          	      			if( $Linha[0] == $Grupo ){
												    	      			echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
										      	      		}else{
												    	      			echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
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
			    	      			<select name="Classe" class="textonormal">
													<?php if ($Classe == ""){ ?>
														 <option value="">Selecione uma Classe...</option>
													<?php } ?>	 

													<?php
													if( $Grupo != "" ){
															$db   = Conexao();
															$sql  = "SELECT CCLAMSCODI, ECLAMSDESC ";
															$sql .= "  FROM SFPC.TBCLASSEMATERIALSERVICO ";
															$sql .= " WHERE CGRUMSCODI = $Grupo AND FCLAMSSITU = 'A' ";
															$sql .= " ORDER BY ECLAMSDESC";
															$result = $db->query($sql);
															if (PEAR::isError($result)) {
															    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
															}else{
																	while( $Linha = $result->fetchRow() ){
																			$DescClasse = substr($Linha[1],0,100);
																			if( $Linha[0]== $Classe ){
																					echo "<option value=\"$Linha[0]\" selected>$DescClasse</option>\n";
																			}else{
																					echo "<option value=\"$Linha[0]\">$DescClasse</option>\n";
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
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Subclasse*</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="SubclasseDescricao" value="<?php echo $SubclasseDescricao; ?>" size="45" maxlength="100" class="textonormal">
	            	  		</td>
	            			</tr>
	            			<tr>
		              		<td class="textonormal"  bgcolor="#DCEDF7">Situação*</td>
		              		<td class="textonormal">
	  	              		<select name="Situacao" class="textonormal">
				        	        <option value="A" <?php if ( $Situacao == "A" ) { echo "selected"; }?>>ATIVO</option>
			                    <option value="I" <?php if ( $Situacao == "I" ) { echo "selected"; }?>>INATIVO</option>
	        	        		</select>
	          	    		</td>
	            			</tr>
	           		</table>
		        	</tr>
	  	      	<tr>
   	  	  			<td class="textonormal" align="right">
         	      	<input type="button" name="Incluir" value="Incluir" class="botao" onClick="javascript:enviar('Incluir');">
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
<script language="javascript" type="">
<!--
document.Subclasse.Grupo.focus();
//-->
</script>
</body>
</html>
