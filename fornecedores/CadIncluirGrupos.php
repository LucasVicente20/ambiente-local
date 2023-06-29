<?php
# -----------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadIncluirGrupos.php
# Autor:    Roberta Costa/ Rossana Lira
# Data:     05/08/04
# Objetivo: Programa de Inclusão de Classes de Fornecimento
# OBS.:     Tabulação 2 espaços
# -----------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     25/05/2011 - Tarefa Redmine: 2203 - Fornecedores devem estar associados a grupos de materiais
#                      - Alteração do nome do arquivo de "CadIncluirClasses.php" para "CadIncluirGrupos.php"
#                      - Este programa agora terá o objetivo de inclusão de Grupos de Fornecimento
# -----------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 73662
# -----------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']	== "POST" ){
		$Botao    			= $_POST['Botao'];
		$TipoGrupo 			= $_POST['TipoGrupo'];
		$TipoMaterial 	= $_POST['TipoMaterial'];
		$Grupo   				= $_POST['Grupo'];
		$ProgramaOrigem = $_POST['ProgramaOrigem'];
}else{
		$ProgramaOrigem	= $_GET['ProgramaOrigem'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Incluir" ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( $TipoGrupo == ""  ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Tipo de Grupo";
		}else{
				if( $TipoGrupo == "M" and $TipoMaterial == "" ){
						if( $Mens == 1 ){ $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "Tipo de Material";
				}
		}
		if( $Grupo == "" ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.FornecedorGrupos.Grupo.focus();\" class=\"titulo2\">Grupo</a>";
		}
		if( $Mens == 0 ){
				if (!$_SESSION['Servicos']) {$Servicos = array(); } else {$Servicos = $_SESSION['Servicos']; }
				if (!$_SESSION['Materiais']) { $Materiais = array(); } else { $Materiais = $_SESSION['Materiais']; }
				for( $i=0; $i<count($Grupo); $i++ ){
				    $Item = $TipoGrupo."#".$Grupo[$i];
						if( $TipoGrupo == "S" and ( ! in_array($Item,$Servicos) ) ){
								$_SESSION['Servicos'][count($_SESSION['Servicos'])] = $Item;
						}elseif( $TipoGrupo == "M" and ( ! in_array($Item,$Materiais) ) ){
								$_SESSION['Materiais'][count($_SESSION['Materiais'])] = $Item;
						}
				}
				$Enviar = "S";
		}
}
?>
<html>
<head>
<title>Portal de Compras - Incluir Grupos</title>
<script language="javascript" type="">
<?php if( $Enviar == "S" ){ ?>
	opener.document.<?php echo $ProgramaOrigem; ?>.Origem.value  = 'D';
	opener.document.<?php echo $ProgramaOrigem; ?>.Destino.value = 'D';
	opener.document.<?php echo $ProgramaOrigem; ?>.submit();
	self.close();
<?php } ?>
function enviar(valor){
	document.FornecedorGrupos.Botao.value = valor;
	document.FornecedorGrupos.submit();
}
function remeter(){
	document.FornecedorGrupos.submit();
}
function voltar(){
	self.close();
}
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
</head>
<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
<form action="CadIncluirGrupos.php" method="post" name="FornecedorGrupos">
	<table cellpadding="0" border="0" summary="">
		<?php if( $Botao == "" ){ echo "<br><br>"; }?>
		<!-- Erro -->
		<tr>
		  <td align="left" colspan="2">
				<?php if( $Mens != 0 ){ ExibeMens($Mensagem,$Tipo,1);	}?>
		 	</td>
		</tr>
		<!-- Fim do Erro -->

		<!-- Corpo -->
		<tr>
			<td class="textonormal">
				<table border="0" cellspacing="0" cellpadding="3" summary="">
					<tr>
		      	<td class="textonormal">
		        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
		          	<tr>
		            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
			    					INCLUIR - GRUPOS DE FORNECIMENTO (OBJETO SOCIAL)
			          	</td>
			        	</tr>
		  	      	<tr>
		    	      	<td class="textonormal" >
										<p align="justify">
											Para incluir um grupo de fornecimento selecione o tipo do grupo e o grupo de fornecimento desejado e clique no botão "Incluir". Para voltar para a tela anterior clique no botão "Voltar".
		          	   	</p>
		          		</td>
			        	</tr>
			        	<tr>
								<td>
									<table border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" width="100%" summary="">
										<tr>
											<td colspan="2">
							          <table class="textonormal" border="0" width="100%" summary="">
							            <tr>
							              <td class="textonormal" bgcolor="#DCEDF7" width="15%">Tipo de Grupo*</td>
							              <td class="textonormal">
							              	<input type="radio" name="TipoGrupo" value="M" onClick="remeter();" <?php if( $TipoGrupo == "M" ){ echo "checked"; } ?> > Material
							              	<input type="radio" name="TipoGrupo" value="S" onClick="remeter();" <?php if( $TipoGrupo == "S" ){ echo "checked"; }?> > Serviço
							              </td>
							            </tr>
							            <?php if( $TipoGrupo == "M" ){ ?>
							            <tr>
							              <td class="textonormal" bgcolor="#DCEDF7" width="15%">Tipo de Material</td>
							              <td class="textonormal">
							              	<input type="radio" name="TipoMaterial" value="C" onClick="remeter();" <?php if( $TipoMaterial == "C" ){ echo "checked"; } ?> > Consumo
							              	<input type="radio" name="TipoMaterial" value="P" onClick="remeter();" <?php if( $TipoMaterial == "P" ){ echo "checked"; }?> > Permanente
							              </td>
							            </tr>
						 		          <?php } ?>
							            <tr>
							              <td class="textonormal" bgcolor="#DCEDF7" width="15%">Grupo* </td>
							              <td class="textonormal">
							              	<select name="Grupo[]" multiple size="8" class="textonormal">
							              		<?php
							              		if( $TipoGrupo == "S" or ( $TipoGrupo == "M" and $TipoMaterial != "" ) ){
									              		$db   = Conexao();
					  												$sql  = "SELECT CGRUMSCODI,EGRUMSDESC FROM SFPC.TBGRUPOMATERIALSERVICO WHERE FGRUMSSITU = 'A' AND ";
					  												if( $TipoGrupo == "M" ){
					  														$sql .= "FGRUMSTIPO = 'M'";
					  														if( $TipoMaterial != "" ){
					  																$sql .= " AND FGRUMSTIPM = '$TipoMaterial' ";
					  														}
					  												}else{
					  														$sql .= "FGRUMSTIPO = 'S'";
					  												}
					  												$sql .= "ORDER BY 2";
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
								  	            }
							              		?>
							              	</select>
							              </td>
							            </tr>
							          </table>
											</td>
										</tr>
									</table>
								</td>
							</tr>
	          	<tr>
		            <td colspan="2" align="right">
									<input type="hidden" name="ProgramaOrigem" value="<?php echo $ProgramaOrigem; ?>">
					       	<input type="button" value="Incluir" class="botao" onclick="javascript:enviar('Incluir');">
		            	<input type="button" value="Voltar" class="botao" onclick="javascript:voltar();">
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
window.focus();
//-->
</script>
</body>
</html>
