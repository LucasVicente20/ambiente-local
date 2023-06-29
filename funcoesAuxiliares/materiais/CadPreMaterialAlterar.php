<?php
#----------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadPreMaterialAlterar.php
# Autor:    Roberta Costa
# Data:     25/04/05
# Alterado: Rodrigo Melo
# Data:     22/09/2009 - Alterando o nome das tabelas SFPC.TBPREMATERIAL e SFPC.TBPREMATERIALTIPOSITUACAO para SFPC.TBPREMATERIALSERVICO e BPREMATERIALSERVICOTIPOSITUACAO, respectivamente (CR 2749).
# Objetivo: Programa de Pré-Inclusão de Material das Classes de Fornecimento
# OBS.:     Tabulação 2 espaços
#----------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança	#
session_start();
Seguranca();
$_SESSION['GetUrl'] = array();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/materiais/CadPreMaterialAnalisar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
		$Botao    			    = $_POST['Botao'];
		$TipoMaterial 	        = $_POST['TipoMaterial'];
		$Grupo   				= $_POST['Grupo'];
		$DescGrupo   		    = $_POST['DescGrupo'];
		$Classe    			    = $_POST['Classe'];
		$DescClasse    	        = $_POST['DescClasse'];
		$Subclasse    	        = $_POST['Subclasse'];
		$DescSubclasse          = $_POST['DescSubclasse'];
		$Unidade  			    = $_POST['Unidade'];
		$Material  			    = $_POST['Material'];
		$NCaracteres            = $_POST['NCaracteres'];
		$DescMaterial  	        = strtoupper2(trim($_POST['DescMaterial']));
		$NCaracteresO           = $_POST['NCaracteresO'];
		$Observacao   	        = strtoupper2(trim($_POST['Observacao']));
}else{
		$Grupo     = $_GET['Grupo'];
		$Classe    = $_GET['Classe'];
		$Subclasse = $_GET['Subclasse'];
		$Material  = $_GET['Material'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Voltar" ){
		$Url = "CadPreMaterialAnalisar.php?Grupo=$Grupo&Classe=$Classe&Subclasse=$Subclasse&Material=$Material";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit;
}elseif( $Botao == "Alterar" ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( $Unidade == "" ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadPreMaterialAlterar.Unidade.focus();\" class=\"titulo2\">Unidade de Medida</a>";
		}
		if( $DescMaterial == "" ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadPreMaterialAlterar.DescMaterial.focus();\" class=\"titulo2\">Material</a>";
		}else{
				if( strlen($DescMaterial) > 200 ){
						if( $Mens == 1 ){ $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.CadPreMaterialAlterar.DescMaterial.focus();\" class=\"titulo2\">Material no Máximo com 200 Caracteres</a>";
				}
		}
		if( $Observacao == "" ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadPreMaterialAlterar.Observacao.focus();\" class=\"titulo2\">Observação</a>";
		}
		if( $Mens == 0 ){
				$db   = Conexao();
				$sql  = "SELECT COUNT(CPREMACODI) FROM SFPC.TBPREMATERIALSERVICO ";
				$sql .= " WHERE EPREMADESC = '$DescClasse' AND CGRUMSCODI = $Grupo ";
				$sql .= "   AND CCLAMSCODI = $Classe AND CSUBCLCODI = $Subclasse";
				$res  = $db->query($sql);
			  if( PEAR::isError($res) ){
					  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$Qtd = $res->fetchRow();
      			if( $Qtd[0] > 0 ){
								$Mens     = 1;
								$Tipo     = 2;
								$Mensagem = "<a href=\"javascript:document.CadPreMaterialAlterar.DescMaterial.focus();\" class=\"titulo2\">Descrição já Cadastrada</a>";
						}else{
								# Inclui na Tabela de Materiais/Serviços #
						    $db->query("BEGIN TRANSACTION");
						    $sql  = "UPDATE SFPC.TBPREMATERIALSERVICO ";
						    $sql .= "   SET EPREMADESC = '$DescMaterial', epremsobse = '$Observacao', ";
						    $sql .= "       CUNIDMCODI =  $Unidade, TPREMAULAT = '".date("Y-m-d H:i:s")."'";
						    $sql .= " WHERE CGRUMSCODI = $Grupo AND CCLAMSCODI = $Classe ";
						    $sql .= "   AND CSUBCLCODI = $Subclasse AND CPREMACODI = $Material";
						    $res  = $db->query($sql);
								if( PEAR::isError($res) ){
										$db->query("ROLLBACK");
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$db->query("COMMIT");
										$db->query("END TRANSACTION");

										# Redireicona para a tela de Análise #
										$Mensagem = urlencode("Material Alterado com Sucesso");
										$Url = "CadPreMaterialAnalisar.php?Mens=1&Tipo=1&Mensagem=$Mensagem&Grupo=$Grupo&Classe=$Classe&Subclasse=$Subclasse&Material=$Material";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
										header("location: ".$Url);
										exit;
								}
								$db->query("COMMIT");
								$db->query("END TRANSACTION");
						}
				}
				$db->disconnect();
		}
}
if( $Botao == "" ){
		# Pega os dados do Pré-Material de acordo com o código #
		$db   = Conexao();
		$sql  = "SELECT B.FGRUMSTIPM, B.EGRUMSDESC, C.ECLAMSDESC, D.ESUBCLDESC, ";
		$sql .= "       A.EPREMADESC, A.CPREMSCODI, A.EPREMSOBSE, A.CUNIDMCODI, ";
		$sql .= "       E.EUNIDMDESC ";
		$sql .= "  FROM SFPC.TBPREMATERIALSERVICO A, SFPC.TBGRUPOMATERIALSERVICO B, SFPC.TBCLASSEMATERIALSERVICO C, ";
		$sql .= "       SFPC.TBSUBCLASSEMATERIAL D, SFPC.TBUNIDADEDEMEDIDA E ";
		$sql .= " WHERE A.CGRUMSCODI = B.CGRUMSCODI AND A.CGRUMSCODI = C.CGRUMSCODI ";
		$sql .= "   AND A.CCLAMSCODI = A.CCLAMSCODI AND A.CGRUMSCODI = D.CGRUMSCODI ";
		$sql .= "   AND A.CCLAMSCODI = D.CCLAMSCODI AND A.CSUBCLCODI = D.CSUBCLCODI ";
		$sql .= "   AND A.CUNIDMCODI = E.CUNIDMCODI AND A.CGRUMSCODI = $Grupo ";
		$sql .= "   AND A.CCLAMSCODI = $Classe AND A.CPREMACODI = $Material ";
		$sql .= "   AND A.CSUBCLCODI = $Subclasse ";
		$res  = $db->query($sql);
	  if( PEAR::isError($res) ){
			  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha         = $res->fetchRow();
      	$TipoMaterial  = $Linha[0];
      	$DescGrupo     = substr($Linha[1],0,60);
				$DescClasse    = substr($Linha[2],0,60);
				$DescSubclasse = substr($Linha[3],0,60);
				$DescMaterial  = $Linha[4];
				$NCaracteres  = strlen($DescMaterial);
				$Situacao      = $Linha[5];
				$Observacao    = $Linha[6];
				$NCaracteresO = strlen($Observacao);
				$Unidade       = $Linha[7];
				$DescUnidade   = $Linha[8];
		}
		$db->disconnect();
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
function remeter(){
	document.CadPreMaterialAlterar.Grupo.value  = '';
	document.CadPreMaterialAlterar.Classe.value = '';
	document.CadPreMaterialAlterar.submit();
}
function enviar(valor){
	document.CadPreMaterialAlterar.Botao.value=valor;
	document.CadPreMaterialAlterar.submit();
}
function ncaracteres(valor){
	document.CadPreMaterialAlterar.NCaracteres.value = '' +  document.CadPreMaterialAlterar.DescMaterial.value.length;
	if( navigator.appName == 'Netscape' && valor ) {  //Netscape Only
		document.CadPreMaterialAlterar.NCaracteres.focus();
	}
}
function ncaracteresO(valor){
	document.CadPreMaterialAlterar.NCaracteresO.value = '' +  document.CadPreMaterialAlterar.Observacao.value.length;
	if( navigator.appName == 'Netscape' && valor ) {  //Netscape Only
		document.CadPreMaterialAlterar.NCaracteresO.focus();
	}
}

<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadPreMaterialAlterar.php" method="post" name="CadPreMaterialAlterar">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais > Pré-Cadastro > Análise
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
      <table border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
				<tr>
					<td class="textonormal">
						<table border="0" cellspacing="0" cellpadding="0" summary="">
							<tr>
				      	<td class="textonormal">
				        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
				          	<tr>
				            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
					    					ANÁLISE - PRÉ-CADASTRO DE MATERIAIS
					          	</td>
					        	</tr>
				  	      	<tr>
				    	      	<td class="textonormal">
												<p align="justify">
													Para incluir um novo material informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.
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
										              <td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo de Material</td>
										              <td class="textonormal">
										              	<?php if( $TipoMaterial == "C" ){ echo "CONSUMO"; }else{ echo "PERMANENTE"; } ?>
									              	</td>
									            	</tr>
									            	<tr>
									              	<td class="textonormal" bgcolor="#DCEDF7" height="20">Grupo</td>
									              	<td class="textonormal"><?php echo $DescGrupo; ?></td>
									            	</tr>
											        	<tr>
											            <td class="textonormal" bgcolor="#DCEDF7" height="20">Classe</td>
										  	        	<td class="textonormal"><?php echo $DescClasse; ?></td>
											        	</tr>
											        	<tr>
											            <td class="textonormal" bgcolor="#DCEDF7" height="20">Subclasse</td>
										  	        	<td class="textonormal"><?php echo $DescSubclasse; ?></td>
											        	</tr>
										        		<tr>
										            	<td class="textonormal" bgcolor="#DCEDF7" height="20">Código</td>
									  	        		<td class="textonormal"><?php echo $Material;?>
									  	        	</tr>
											        	<tr>
											            <td class="textonormal" bgcolor="#DCEDF7">Unidade de Medida*</td>
										  	        	<td class="textonormal">
									    	      			<select name="Unidade" class="textonormal">
																			<option value="">Selecione uma Unidade de Medida...</option>
																			<?
																			$db   = Conexao();
																			$sql  = "SELECT CUNIDMCODI, EUNIDMDESC ";
																			$sql .= "  FROM SFPC.TBUNIDADEDEMEDIDA ";
																			$sql .= " ORDER BY EUNIDMDESC";
																			$result = $db->query($sql);
																			if (PEAR::isError($result)) {
																			    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																			}else{
																					while( $Linha = $result->fetchRow() ){
																							$DescUnidade = substr($Linha[1],0,60);
																							if( $Linha[0]== $Unidade ){
																									echo "<option value=\"$Linha[0]\" selected>$DescUnidade</option>\n";
																							}else{
																									echo "<option value=\"$Linha[0]\">$DescUnidade</option>\n";
																							}
																					}
																			}
																			$db->disconnect();
																			?>
																		</select>
																	</td>
											        	</tr>
										        		<tr>
										            	<td class="textonormal" bgcolor="#DCEDF7">Material*</td>
									  	        		<td class="textonormal">
																		<font class="textonormal">máximo de 200 caracteres</font>
																		<input type="text" name="NCaracteres" disabled size="3" value="<?phpecho $NCaracteres ?>" class="textonormal"><br>
																		<textarea name="DescMaterial" cols="39" rows="5" OnKeyUp="javascript:ncaracteres(1)" OnBlur="javascript:ncaracteres(0)" OnSelect="javascript:ncaracteres(1)" class="textonormal"><?phpecho $DescMaterial; ?></textarea>
																	</td>
										        		</tr>
										        		<tr>
										            	<td class="textonormal" bgcolor="#DCEDF7">Observação*</td>
									  	        		<td class="textonormal">
																		<font class="textonormal">máximo de 100 caracteres</font>
																		<input type="text" name="NCaracteresO" disabled size="3" value="<?phpecho $NCaracteresO ?>" class="textonormal"><br>
																		<textarea name="Observacao" cols="39" rows="5" OnKeyUp="javascript:ncaracteresO(1)" OnBlur="javascript:ncaracteresO(0)" OnSelect="javascript:ncaracteresO(1)" class="textonormal"><?phpecho $Observacao; ?></textarea>
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
			              		<input type="hidden" name="TipoMaterial" value="<?php echo $TipoMaterial; ?>">
			              		<input type="hidden" name="Grupo" value="<?php echo $Grupo; ?>">
			              		<input type="hidden" name="DescGrupo" value="<?php echo $DescGrupo; ?>">
			              		<input type="hidden" name="Classe" value="<?php echo $Classe; ?>">
			              		<input type="hidden" name="DescClasse" value="<?php echo $DescClasse; ?>">
			              		<input type="hidden" name="Subclasse" value="<?php echo $Subclasse; ?>">
			              		<input type="hidden" name="DescSubclasse" value="<?php echo $DescSubclasse; ?>">
			              		<input type="hidden" name="Material" value="<?php echo $Material; ?>">
							       		<input type="button" value="Alterar" class="botao" onclick="javascript:enviar('Alterar');">
							       		<input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
												<input type="hidden" name="Botao" value="">
											</td>
			            	</tr>
		     					</table>
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
