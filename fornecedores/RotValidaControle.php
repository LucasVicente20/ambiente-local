<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: RotValidaControle.php
# Autor:    Roberta Costa
# Data:     15/10/04
# Objetivo: Programa de Validação do Número de Controle dos Documentos
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------
# Alterado: Lucas André e Daniel Augusto
# Data:		16/05/2023
# Objetivo: Tarefa Redmine 282898
# -----------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Daniel Augusto
# Data:		16/05/2023
# Objetivo: Tarefa Redmine 282903
# -----------------------------------------------------------------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao        = $_POST['Botao'];
		$Critica      = $_POST['Critica'];
		$TipoForn     = $_POST['TipoForn'];
		$TipoCnpjCpf  = $_POST['TipoCnpjCpf'];
		$CPF_CNPJ	    = $_POST['CPF_CNPJ'];
		$DataEmissao	= $_POST['DataEmissao'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Gerar" ){
		$Mens      = 0;
		$Mensagem  = "Informe: ";
		if( $TipoCnpjCpf == 2 ){
		  	$Qtd = strlen($CPF_CNPJ);
		  	if( ($Qtd != 11) and ($Qtd != 0) ){
		      	if ($Mens == 1){$Mensagem.=", ";}
						$Mens = 1;$Tipo = 2;
						$Mensagem .= "<a href=\"javascript:document.RotValidaControle.CPF_CNPJ.focus();\" class=\"titulo2\">CPF com 11 números</a>";
				}elseif( $CPF_CNPJ == "" ){
						if ($Mens == 1){$Mensagem.=", ";}
						$Mens = 1;$Tipo = 2;
				  	$Mensagem .= "<a href=\"javascript:document.RotValidaControle.CPF_CNPJ.focus();\" class=\"titulo2\">CPF Válido</a>";
				}else{
				  	if ($Mens == 1){$Mensagem.=", ";}
						$cpfcnpj = valida_CPF($CPF_CNPJ);
						if( $cpfcnpj === false ){
						  	$Mens = 1;$Tipo = 2;
	  						$Mensagem .= "<a href=\"javascript:document.RotValidaControle.CPF_CNPJ.focus();\" class=\"titulo2\">CPF Válido</a>";
	  				}
		  	}
		}elseif( $TipoCnpjCpf == 1 ){
				$Qtd = strlen($CPF_CNPJ);
		   	if( ($Qtd != 14) and ($Qtd != 0)  ){
						if ($Mens == 1){$Mensagem.=", ";}
						$Mens = 1;$Tipo = 2;
				  	$Mensagem .= "<a href=\"javascript:document.RotValidaControle.CPF_CNPJ.focus();\" class=\"titulo2\">CNPJ com 14 números</a>";
			 	}elseif( $CPF_CNPJ == "" ){
						if ($Mens == 1){$Mensagem.=", ";}
						$Mens = 1;$Tipo = 2;
				  	$Mensagem .= "<a href=\"javascript:document.RotValidaControle.CPF_CNPJ.focus();\" class=\"titulo2\">CNPJ Válido</a>";
			 	}else{
				  	if ($Mens == 1){$Mensagem.=", ";}
						$cpfcnpj = valida_CNPJ($CPF_CNPJ);
						if( $cpfcnpj === false ){
						  	$Mens = 1;$Tipo = 2;
	  						$Mensagem .= "<a href=\"javascript:document.RotValidaControle.CPF_CNPJ.focus();\" class=\"titulo2\">CNPJ Válido</a>";
	  				}
			 	}
		}
		if( $DataEmissao == "" ){
				if ($Mens == 1){$Mensagem.=", ";}
				$Mens      = 1;
		  	$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RotValidaControle.DataEmissao.focus();\" class=\"titulo2\">Data de Emissão</a>";
		}else{
				$MensErro = ValidaData($DataEmissao);
				if( $MensErro != "" ){
						if( $Mens == 1 ){ $Mensagem .= ", "; }
						$Mens = 1;$Tipo = 2;
						$Mensagem .= "<a href=\"javascript:document.RotValidaControle.DataEmissao.focus();\" class=\"titulo2\">Data de Emissão Válida</a>";
				}
		}

		if( $Mens == 0 ){
				# Verificando se o CPF/CNPJ existe no cadastro, se houver pega o sequencial #
				$db  = Conexao();
				if( $TipoForn == "INSC" ){
						$TipoFornecedor = 1;
						$sqlpre  = "SELECT APREFOSEQU FROM SFPC.TBPREFORNECEDOR WHERE ";
						if( $TipoCnpjCpf == 1 ){
								$sqlpre .= "APREFOCCGC = '$CPF_CNPJ'";
						}elseif( $TipoCnpjCpf == 2 ){
								$sqlpre .= "APREFOCCPF = '$CPF_CNPJ'";
						}
						$respre = $db->query($sqlpre);
						if( PEAR::isError($respre) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlpre");
						}else{
								$rows = $respre->numRows();
								if( $rows > 0 ){
										$Linha      = $respre->fetchRow();
										$Sequencial = $Linha[0];
										$Critica    = 1;
								}else{
										$Mens      = 1;
								  	$Tipo      = 2;
										$Mensagem .= "<a href=\"javascript:document.RotValidaControle.CPF_CNPJ.focus();\" class=\"titulo2\">CPF/CNPJ não encontrado em nossos cadastros</a>";
								}
						}
				}elseif( $TipoForn == "FORN" ){
						$TipoFornecedor = 2;
						$sqlfor  = "SELECT AFORCRSEQU FROM SFPC.TBFORNECEDORCREDENCIADO WHERE ";
						if( $TipoCnpjCpf == 1 ){
								$sqlfor .= "AFORCRCCGC = '$CPF_CNPJ'";
						}elseif( $TipoCnpjCpf == 2 ){
								$sqlfor .= "AFORCRCCPF = '$CPF_CNPJ'";
						}
						$resfor = $db->query($sqlfor);
						if( PEAR::isError($resfor) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlfor");
						}else{
								$rows = $resfor->numRows();
								if( $rows > 0 ){
										$Linha      = $resfor->fetchRow();
										$Sequencial = $Linha[0];
										$Critica    = 1;

								}else{
										$Mens      = 1;
								  	$Tipo      = 2;
										$Mensagem .= "<a href=\"javascript:document.RotValidaControle.CPF_CNPJ.focus();\" class=\"titulo2\">CPF/CNPJ não encontrado em nossos cadastros</a>";
								}
						}
				}
				$db->disconnect();

				# Gera o Número de Controle do Fornecedor #
				$DataEmissaoInv = substr($DataEmissao,6,4).substr($DataEmissao,3,2).substr($DataEmissao,0,2);
				$Numero         = $Sequencial.$CPF_CNPJ.$DataEmissaoInv;
				$NumControle    = ControlaDocumento($Numero);
		}
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="JavaScript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.RotValidaControle.Botao.value=valor;
	document.RotValidaControle.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RotValidaControle.php" method="post" name="RotValidaControle">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedores > Validação do Controle
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
      <table  border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0"  bordercolor="#75ADE6" class="textonormal" width="100%" summary="">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" colspan="3" class="titulo3">
		    					VALIDAÇÃO DO NÚMERO DE CONTROLE
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal" colspan="3">
	      	    		<p align="justify">
	        	    		Para geração do número de controle do Fornecedor, informe os dados abaixo e clique no botão "Gerar".
	          	   	</p>
	          		</td>
	          	</tr>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left" width="100%" summary="">
			  	      		<tr>
			    	      		<td class="textonormal" bgcolor="#DCEDF7">Tipo<span style="color: red;">*</span></td>
			      	    		<td class="textonormal">
									<?php
										if( $TipoForn == "FORN" ){
											echo "<input type=\"radio\" name=\"TipoForn\" value=\"INSC\"> Inscrito\n";
											echo "<input type=\"radio\" name=\"TipoForn\" value=\"FORN\" checked> Fornecedor\n";
										}else{
											echo "<input type=\"radio\" name=\"TipoForn\" value=\"INSC\" checked> Inscrito\n";
											echo "<input type=\"radio\" name=\"TipoForn\" value=\"FORN\">Fornecedor\n";
										}
									?>
								</td>
			        			</tr>
								<tr>
									<td class="textonormal" bgcolor="#DCEDF7">
										<input type="radio" name="TipoCnpjCpf" value="2" <?php if( $TipoCnpjCpf == "2" or $TipoCnpjCpf == "" ){ echo "checked"; }?>> CPF<span style="color: red;">*</span>
										<input type="radio" name="TipoCnpjCpf" value="1" <?php if( $TipoCnpjCpf == "1" ){ echo "checked"; }?>>CNPJ<span style="color: red;">*</span>
	          	    				</td>
	          	    				<td class="textonormal">
	          	    					<input type="text" name="CPF_CNPJ" size="15" maxlength="14" value="<?php echo $CPF_CNPJ;?>" class="textonormal">
	          	    				</td>
									<tr>
				  	      				<td class="textonormal" bgcolor="#DCEDF7" width="30%">Data de Emissão<span style="color: red;">*</span></td>
				              			<td class="textonormal">
										  	<?php
            	                    			$DataMes = DataMes();
												if ($DataIni == "" || is_null($DataIni)) {
                                					//$DataIni = $DataMes[0];
                                    				$DataIni = "";
                                    			}

                                    			if ($DataFim == "" || is_null($DataFim)) {
                                        			//$DataFim = $DataMes[1];
                                        			$DataFim = "";
                                    			}

                                    			$URLIni = "../calendario.php?Formulario=ConsPesquisarDFD&Campo=DataIni";
                                    			$URLFim = "../calendario.php?Formulario=ConsPesquisarDFD&Campo=DataFim";
                                			?>

                                			<input class="textonormal" type="date"
                                			name="DataEmissao" size="10"
                               			 	maxlength="10" value="<?php echo $DataEmissao; ?>">
                                                                    
					      				</td>
									</tr>
				      		</table>
		        	</td>
		        </tr>
      	      <tr>
    	      		<td align="right" colspan="3">
  	      				<input type="button" name="Gerar" value="Gerar" class="botao" onclick="javascript:enviar('Gerar');">
            			<input type="hidden" name="Botao" value="">
								</td>
        			</tr>
        			<?php if( $Critica == 1 ){ ?>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left" width="100%" summary="">
			        			<tr>
			    	      		<td class="textonormal" bgcolor="#DCEDF7" width="30%">Número de Controle</td>
			    	      		<td class="textonormal" height="20"><?php echo $TipoFornecedor.$Numero."-".$NumControle; ?></td>
			        			</tr>
			        		</table>
								</td>
							</tr>
        			<?php } ?>
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
