<?php
#----------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadMaterialExcluir.php
# Autor:    Roberta Costa
# Data:     25/04/05
# Alterado: Rodrigo Melo
# Data:     01/02/2008 - Alteração para que o material seja excluído da tabela SFPC.TBhistoricomaterial quando o material for excluído.
# Alterado: Rodrigo Melo
# Data:     03/03/2008 - Remoção da integração com a tabela de histórico.
# Objetivo: Programa de Alteração de Material das Classes de Fornecimento
# OBS.:     Tabulação 2 espaços
#----------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/materiais/CadMaterialAlterar.php' );
AddMenuAcesso( '/materiais/CadMaterialManterSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']	== "POST" ){
		$Botao    			= $_POST['Botao'];
		$TipoMaterial 	= $_POST['TipoMaterial'];
		$Grupo   				= $_POST['Grupo'];
		$DescGrupo   		= $_POST['DescGrupo'];
		$Classe    			= $_POST['Classe'];
		$DescClasse    	= $_POST['DescClasse'];
		$Subclasse    	= $_POST['Subclasse'];
		$DescSubclasse  = $_POST['DescSubclasse'];
		$Unidade  			= $_POST['Unidade'];
		$DescUnidade  	= $_POST['DescUnidade'];
		$Material  			= $_POST['Material'];
		$DescMaterial  	= strtoupper2(trim($_POST['DescMaterial']));
		$Observacao   	= strtoupper2(trim($_POST['Observacao']));
}else{
		$Grupo     = $_GET['Grupo'];
		$Classe    = $_GET['Classe'];
		$Subclasse = $_GET['Subclasse'];
		$Material  = $_GET['Material'];
}


# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Voltar" ){
		$Url = "CadMaterialAlterar.php?Material=$Material";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit;
}elseif( $Botao == "Excluir" ){
    # Verifica se a Classe está relacionada com alguma requisição #
		$db     = Conexao();
		$sql    = "SELECT COUNT(CMATEPSEQU) FROM SFPC.TBITEMREQUISICAO ";
		$sql   .= " WHERE CMATEPSEQU = $Material ";
 		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
		    $Linha   = $result->fetchRow();
		    $QtdItem = $Linha[0];
    		if( $QtdItem > 0 ) {
						$Critica  = 0;
			    	$Mens     = 1;
			    	$Tipo     = 2;
						$Mensagem = "Exclusão Cancelada!<br>Material Relacionado com ($QtdItem) Item(ns) da Requisição(ões)";
				}else{
						$sql    = "SELECT COUNT(CMATEPSEQU) FROM SFPC.TBARMAZENAMENTOMATERIAL ";
						$sql   .= " WHERE CMATEPSEQU = $Material ";
			 			$result = $db->query($sql);
						if( PEAR::isError($result) ){
					    	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
					  	  $Linha = $result->fetchRow();
		    				$QtdItem = $Linha[0];
			    			if( $QtdItem > 0 ) {
			    					$Mens = 1;$Tipo = 2;
										$Mensagem = "Exclusão Cancelada!<br>Material Relacionado com ($QtdItem) Armazenamento(s) de Material(ais)";
										$Critica  = 0;
								}else{
										$sql    = "SELECT COUNT(CMATEPSEQU) FROM SFPC.TBMOVIMENTACAOMATERIAL ";
										$sql   .= " WHERE CMATEPSEQU = $Material ";
							 			$result = $db->query($sql);
										if( PEAR::isError($result) ){
									    	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
									  	  $Linha = $result->fetchRow();
		    								$QtdItem = $Linha[0];
							    			if( $QtdItem > 0 ) {
			    									$Mens = 1;$Tipo = 2;
														$Mensagem = "Exclusão Cancelada!<br>Material Relacionado com ($QtdItem) Movimentação(ões)";
														$Critica  = 0;
												}else{
                          $sql    = "SELECT COUNT(CMATEPSEQU) FROM SFPC.TBITEMNOTAFISCAL ";
                          $sql   .= " WHERE CMATEPSEQU = $Material ";
                          $result = $db->query($sql);
                          if( PEAR::isError($result) ){
                              ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                          }else{
                            $Linha = $result->fetchRow();
                            $QtdItem = $Linha[0];
                            if( $QtdItem > 0 ) {
                                $Mens = 1;$Tipo = 2;
                                $Mensagem = "Exclusão Cancelada!<br>Material Relacionado com ($QtdItem) Item(ns) da Nota Fiscal";
                                $Critica  = 0;
                            }else{
                              $sql    = "SELECT COUNT(CMATEPSEQU) FROM SFPC.TBINVENTARIOMATERIAL ";
                              $sql   .= " WHERE CMATEPSEQU = $Material ";
                              $result = $db->query($sql);
                              if( PEAR::isError($result) ){
                                  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                              }else{
                                $Linha = $result->fetchRow();
                                $QtdItem = $Linha[0];
                                if( $QtdItem > 0 ) {
                                    $Mens = 1;$Tipo = 2;
                                    $Mensagem = "Exclusão Cancelada!<br>Material Relacionado com ($QtdItem) Inventário(s)";
                                    $Critica  = 0;
                                }else{
                                  $sql    = "SELECT COUNT(CMATEPSEQU) FROM SFPC.TBPRECOMATERIAL ";
                                  $sql   .= " WHERE CMATEPSEQU = $Material ";
                                  $result = $db->query($sql);
                                  if( PEAR::isError($result) ){
                                      ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                  }else{
                                    $Linha = $result->fetchRow();
                                    $QtdItem = $Linha[0];
                                    if( $QtdItem > 0 ) {
                                        $Mens = 1;$Tipo = 2;
                                        $Mensagem = "Exclusão Cancelada!<br>Preço do Material cadastrado na Tabela Referencial de Preços";
                                        $Critica  = 0;
                                    }else{
                                      $db->query("BEGIN TRANSACTION");
                                      $sql  = "DELETE FROM SFPC.TBMATERIALPORTAL WHERE CMATEPSEQU = $Material";
                                      $res  = $db->query($sql);
                                      if( PEAR::isError($res) ){
                                          $db->query("ROLLBACK");
                                          ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                      }else{
                                          $db->query("COMMIT");
                                          $db->query("END TRANSACTION");
                                          $db->disconnect();
                                          # Redireciona para a tela de Análise #
                                          $Mensagem = urlencode("Material Excluído com Sucesso");
                                          $Url = "CadMaterialManterSelecionar.php?Mens=1&Tipo=1&Mensagem=$Mensagem";
                                          if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
                                          header("location: ".$Url);
                                          exit;
                                      }
                                      $db->query("END TRANSACTION");
                                    }
                                  }
                                }
                              }
                            }
                          }
												}
										}
								}
						}
				}
		}
		$db->disconnect();

}

if( $Botao == "" ){
		# Pega os dados do Material de acordo com o código #
		$db   = Conexao();
    $sql  = "SELECT GRU.FGRUMSTIPM, GRU.EGRUMSDESC, CLA.ECLAMSDESC, SUB.ESUBCLDESC, ";
    $sql .= "       MAT.EMATEPDESC, MAT.CUNIDMCODI, UND.EUNIDMDESC, UND.CUNIDMCODI, ";
    $sql .= "       MAT.EMATEPOBSE ";
		$sql .= "  FROM SFPC.TBMATERIALPORTAL MAT, SFPC.TBGRUPOMATERIALSERVICO GRU, SFPC.TBCLASSEMATERIALSERVICO CLA, ";
		$sql .= "       SFPC.TBSUBCLASSEMATERIAL SUB, SFPC.TBUNIDADEDEMEDIDA UND ";
		$sql .= "  WHERE SUB.CGRUMSCODI = CLA.CGRUMSCODI AND SUB.CCLAMSCODI = CLA.CCLAMSCODI ";
		$sql .= "    AND CLA.CGRUMSCODI = GRU.CGRUMSCODI AND MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
		$sql .= "    AND MAT.CUNIDMCODI = UND.CUNIDMCODI AND MAT.CMATEPSEQU = $Material ";
		$res  = $db->query($sql);
	  if( PEAR::isError($res) ){
			  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha         = $res->fetchRow();
      	$TipoMaterial  = $Linha[0];
      	$DescGrupo     = $Linha[1];
				$DescClasse    = $Linha[2];
				$DescSubclasse = $Linha[3];
				$DescMaterial  = $Linha[4];
				$Unidade       = $Linha[5];
				$DescUnidade   = $Linha[6];
				$Unidade       = $Linha[7];
				$Observacao    = $Linha[8];
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
	document.CadMaterialExcluir.Grupo.value  = '';
	document.CadMaterialExcluir.Classe.value = '';
	document.CadMaterialExcluir.submit();
}
function enviar(valor){
	document.CadMaterialExcluir.Botao.value=valor;
	document.CadMaterialExcluir.submit();
}
function ncaracteres(valor){
	document.CadMaterialExcluir.NCaracteres.value = '' +  document.CadMaterialExcluir.Descricao.value.length;
	if( navigator.appName == 'Netscape' && valor ) {  //Netscape Only
		document.CadMaterialExcluir.NCaracteres.focus();
	}
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadMaterialExcluir.php" method="post" name="CadMaterialExcluir">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais > Cadastro > Manter
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
					    					EXCLUIR - CADASTRO DE MATERIAIS
					          	</td>
					        	</tr>
				  	      	<tr>
				    	      	<td class="textonormal">
												<p align="justify">
													Para excluir um Material clique no botão "Excluir". Os dados serão retirados permanentemente do cadastro.
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
										            	<td class="textonormal" bgcolor="#DCEDF7" height="20">Material</td>
									  	        		<td class="textonormal"><?php echo $Material;?></td>
									  	        	</tr>
										        		<tr>
											            <td class="textonormal" bgcolor="#DCEDF7" height="20">Unidade de Medida</td>
									  	        		<td class="textonormal"><?php echo $DescUnidade;?></td>
											        	</tr>
										        		<tr>
										            	<td class="textonormal" bgcolor="#DCEDF7" height="20">Material</td>
									  	        		<td class="textonormal"><?php echo $DescMaterial;?></td>
										        		</tr>
										        		<tr>
										            	<td class="textonormal" bgcolor="#DCEDF7" height="20">Observação</td>
									  	        		<td class="textonormal"><?php echo $Observacao;?></td>
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
			              		<input type="hidden" name="DescMaterial" value="<?php echo $DescMaterial; ?>">
			              		<input type="hidden" name="Observacao" value="<?php echo $Observacao; ?>">
							       		<input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Excluir');">
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
