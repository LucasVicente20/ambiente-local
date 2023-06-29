<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabCEPIncluir.php
# Autor:    Roberta Costa
# Data:     17/05/05
# Objetivo: Programa de Inclusão de CEP
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao          = $_POST['Botao'];
		$TipoCEP        = $_POST['TipoCEP'];
		$CEP            = $_POST['CEP'];
		$TipoLogradouro = $_POST['TipoLogradouro'];
		$Logradouro     = strtoupper2(trim($_POST['Logradouro']));
		$Bairro         = strtoupper2(trim($_POST['Bairro']));
		$Cidade         = strtoupper2(trim($_POST['Cidade']));
		$TipoLocalidade = $_POST['TipoLocalidade'];
		$Localidade     = strtoupper2(trim($_POST['Localidade']));
		$UF             = strtoupper2(trim($_POST['UF']));
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Critica dos Campos #
if( $Botao == "Incluir" ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
	  if( $TipoCEP == "" ){
		    $Mens      = 1;
		    $Tipo      = 2;
  			$Mensagem .= "Tipo do CEP";
    }
	  if( $CEP == "" ){
	  		if( $Mens == 1 ){ $Mensagem .= ", "; }
		    $Mens      = 1;
		    $Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.TabCEPIncluir.CEP.focus();\" class=\"titulo2\">CEP</a>";
    }
	  if( $TipoCEP == "LOG" ){
			  if( $TipoLogradouro == "" ){
			  		if( $Mens == 1 ){ $Mensagem .= ", "; }
				    $Mens      = 1;
				    $Tipo      = 2;
		  			$Mensagem .= "<a href=\"javascript:document.TabCEPIncluir.TipoLogradouro.focus();\" class=\"titulo2\">Tipo do Logradouro</a>";
		    }
			  if( $Logradouro == "" ){
			  		if( $Mens == 1 ){ $Mensagem .= ", "; }
				    $Mens      = 1;
				    $Tipo      = 2;
		  			$Mensagem .= "<a href=\"javascript:document.TabCEPIncluir.Logradouro.focus();\" class=\"titulo2\">Logradouro</a>";
		    }
			  if( $Bairro == "" ){
			  		if( $Mens == 1 ){ $Mensagem .= ", "; }
				    $Mens      = 1;
				    $Tipo      = 2;
		  			$Mensagem .= "<a href=\"javascript:document.TabCEPIncluir.Bairro.focus();\" class=\"titulo2\">Bairro</a>";
		    }
			  if( $Cidade == "" ){
			  		if( $Mens == 1 ){ $Mensagem .= ", "; }
				    $Mens      = 1;
				    $Tipo      = 2;
		  			$Mensagem .= "<a href=\"javascript:document.TabCEPIncluir.Cidade.focus();\" class=\"titulo2\">Cidade</a>";
		    }
		}else{
			  if( $TipoLocalidade == "" ){
			  		if( $Mens == 1 ){ $Mensagem .= ", "; }
				    $Mens      = 1;
				    $Tipo      = 2;
		  			$Mensagem .= "<a href=\"javascript:document.TabCEPIncluir.TipoLocalidade.focus();\" class=\"titulo2\">Tipo da Localidade</a>";
		    }
			  if( $Localidade == "" ){
			  		if( $Mens == 1 ){ $Mensagem .= ", "; }
				    $Mens      = 1;
				    $Tipo      = 2;
		  			$Mensagem .= "<a href=\"javascript:document.TabCEPIncluir.Localidade.focus();\" class=\"titulo2\">Localidade</a>";
		    }
		}
	  if( $UF == "" ){
	  		if( $Mens == 1 ){ $Mensagem .= ", "; }
		    $Mens      = 1;
		    $Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.TabCEPIncluir.UF.focus();\" class=\"titulo2\">UF</a>";
    }
	  if( $Mens == 0 ) {
	  	  # Verifica a Duplicidade de CEP #
				$db  = Conexao();
		   	$sql = "SELECT COUNT(*) FROM PPDV.TBCEPLOGRADOUROBR WHERE CCEPPOCODI = $CEP ";
		 		$res = $db->query($sql);
				if (PEAR::isError($res)) {
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
		    		$Qtd = $res->fetchRow();
		    		if( $Qtd[0] > 0 ) {
					    	$Mens     = 1;
					    	$Tipo     = 2;
								$Mensagem = "<a href=\"javascript:document.TabCEPIncluir.CEP.focus();\" class=\"titulo2\"> CEP Já Cadastrado - Logradouro</a>";
						}else{
						   	$sql = "SELECT COUNT(*) FROM PPDV.TBCEPLOCALIDADEBR WHERE CCELOCCODI = $CEP ";
						 		$res = $db->query($sql);
								if (PEAR::isError($res)) {
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
						    		$Qtd = $res->fetchRow();
						    		if( $Qtd[0] > 0 ) {
									    	$Mens     = 1;
									    	$Tipo     = 2;
												$Mensagem = "<a href=\"javascript:document.TabCEPIncluir.CEP.focus();\" class=\"titulo2\"> CEP Já Cadastrado - Localidade</a>";
										}else{
												$db->query("BEGIN TRANSACTION");
												$Data = date("Y-m-d H:i:s");
												if( $TipoCEP == "LOG" ){
  			    						   	# Pega a maior Referência e soma mais um #
  			    						   	$sql = "SELECT MAX(CCEPPOREFE) FROM PPDV.TBCEPLOGRADOUROBR";
												 		$res = $db->query($sql);
														if (PEAR::isError($res)) {
														    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
												    		$Max = $res->fetchRow();
												    		if( $Max == 0 ){ $Referencia = 1 ; }else{ $Referencia = $Max[0] + 1; }
														}

														$TipoLog     = explode("_",$TipoLogradouro);
														$NomeTipoLog = $TipoLog[0];
														$Abreviatura = $TipoLog[1];


												    # Insere CEP - Logradouro #
												    $sql  = "INSERT INTO PPDV.TBCEPLOGRADOUROBR (";
												    $sql .= "CCEPPOCODI, NCEPPOLOGR, NCEPPOBAIR, NCEPPOTIPO, CCEPPOESTA, ";
												    $sql .= "TCEPPOULAT, NCEPPOCOMP, NCEPPOCIDA, CCEPPOREFE, CCEPPOTIPL, ";
												    $sql .= "FCEPPOSITU ";
												    $sql .= ") VALUES ( ";
												    $sql .= "$CEP, '$Logradouro', '$Bairro', '$NomeTipoLog', '$UF', ";
												    $sql .= "'$Data', NULL, '$Cidade', $Referencia, '$Abreviatura', ";
												    $sql .= " 'A')";
												    $res  = $db->query($sql);
														if( PEAR::isError($res) ){
																$db->query("ROLLBACK");
														    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}
												}else{
												    # Insere CEP - Logradouro #
												    $sql  = "INSERT INTO PPDV.TBCEPLOCALIDADEBR (";
												    $sql .= "cceloccodi, ncelocloca, ccelocesta, ";
												    $sql .= "cceloctipo, fceppositu, tceppoulat ";
												    $sql .= ") VALUES ( ";
												    $sql .= "$CEP, '$Localidade', '$UF', ";
												    $sql .= "'$TipoLocalidade', 'A',	'$Data' )";
												    $res  = $db->query($sql);
														if( PEAR::isError($res) ){
																$db->query("ROLLBACK");
														    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}
												}
												$db->query("COMMIT");
												$db->query("END TRANSACTION");
												$Mens           = 1;
												$Tipo           = 1;
												$Mensagem       = "CEP Incluído com Sucesso";
												$TipoCEP        = "";
												$CEP            = "";
												$TipoLogradouro = "";
												$Logradouro     = "";
												$Localidade     = "";
												$Bairro         = "";
												$Cidade         = "";
												$UF             = "";
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
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.TabCEPIncluir.Botao.value=valor;
	document.TabCEPIncluir.submit();
}
<?php  MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabCEPIncluir.php" method="post" name="TabCEPIncluir">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > CEP > Incluir
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<tr>
	  <td width="100"></td>
	  <td align="left" colspan="2">
	  	<?php  if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,1); } ?>
	  </td>
	</tr>
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
	            		INCLUIR - CEP
	            	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para incluir um novo CEP, informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.
	        	    	</p>
	          		</td>
		        	</tr>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left" summary="">
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7" width="30%">Tipo*</td>
											<td class="textonormal">
												<input type="radio" name="TipoCEP" value="LOG" <?php if( $TipoCEP == "LOG" ){ echo "checked"; }?> onClick="submit();">Logradouro
												<input type="radio" name="TipoCEP" value="LOC" <?php if( $TipoCEP == "LOC" ){ echo "checked"; }?> onClick="submit();">Localidade
				            	</td>
				            </tr>
					           <tr>
				              <td class="textonormal" bgcolor="#DCEDF7" width="30%">CEP*</td>
											<td class="textonormal">
												<input type="text" name="CEP" size="8" maxlength="8" value="<?php echo $CEP; ?>" class="textonormal">
												<input type="hidden" name="CEPAntes" size="8" maxlength="8" value="<?php echo $CEPAntes;?>" class="textonormal">
				            	</td>
				            </tr>
				            <?php if( $TipoCEP == "LOG" ){ ?>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Tipo do Logradouro*</td>
				              <td class="textonormal">
				              	<select name="TipoLogradouro" class="textonormal">
													<option value="">Selecione um Tipo...</option>
													<?php
													$caminho = "../cep/DNE_GU_TIPOS_LOGRADOURO.TXT";
											   	if( file_exists($caminho) ){
											    		if( !( $fp = fopen($caminho,"r") ) ){
												   				echo "Erro na abertura do Arquivo: $caminho";
												   		}else{
																	$i = 0;
																	while( ! feof ($fp)) {
																	    $Dados = fgets($fp, 1024);
																			if( $i != 0 ){
																					$j = $i - 1;
																					if( $Dados != "" and $Dados != "#" ){
																							$Nome[$j]        = trim(substr($Dados,7,72));
																							$Abreviatura[$j] = trim(substr($Dados,79,15));
																					}
																			}
																			$i++;
																	}
																	fclose($fp);
															}
													}else{
												   		echo "Arquivo não Encontrado";
													}
													for( $i=0;$i< count($Nome);$i++ ){
															echo "<option value=\"$Nome[$i]_$Abreviatura[$i]\" ";
															if( $TipoLogradouro == "$Nome[$i]_$Abreviatura[$i]" ){ echo "selected"; }
															echo ">$Nome[$i]</option>\n";
													}
													?>
												</select>
				              </td>
				            </tr>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Logradouro*</td>
				              <td class="textonormal">
				              	<input type="text" name="Logradouro" size="45" maxlength="100" value="<?php echo $Logradouro; ?>" class="textonormal">
				              </td>
				            </tr>
										<?php
										}
										if( $TipoCEP == "LOC" ){
										?>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Tipo da Localidade*</td>
				              <td class="textonormal">
				              	<select name="TipoLocalidade" class="textonormal">
				              		<option value="">Selecione um Tipo...</option>
				              		<option value="D" <?php if( $TipoLocalidade == "D" ){ echo "selected"; }?>>DISTRITO</option>
				              		<option value="M" <?php if( $TipoLocalidade == "M" ){ echo "selected"; }?>>MUNICÍPIO</option>
				              		<option value="P" <?php if( $TipoLocalidade == "P" ){ echo "selected"; }?>>POVOADO</option>
				              		<option value="R" <?php if( $TipoLocalidade == "R" ){ echo "selected"; }?>>REGIÃO ADMINISTRATIVA</option>
				              	</select>
				              </td>
				            </tr>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Localidade*</td>
				              <td class="textonormal">
				              	<input type="text" name="Localidade" size="45" maxlength="100" value="<?php echo $Localidade; ?>" class="textonormal">
				              </td>
				            </tr>
				            <?php
				          	}
				            if( $TipoCEP == "LOG" ){
				            ?>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Bairro*</td>
				              <td class="textonormal">
				              	<input type="text" name="Bairro" size="33" maxlength="30" value="<?php echo $Bairro; ?>" class="textonormal">
				              </td>
				            </tr>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Cidade*</td>
				              <td class="textonormal">
				              	<input type="text" name="Cidade" size="33" maxlength="30" value="<?php echo $Cidade; ?>" class="textonormal">
				              </td>
				            </tr>
				            <?php } ?>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">UF*</td>
		    	      			<td class="textonormal">
		    	      				<input type="text" name="UF" size="2" maxlength="2" value="<?php echo $UF; ?>" class="textonormal">
		    	      			</td>
				            </tr>
	            		</table>
		          	</td>
		        	</tr>
    	      	<tr>
      	      	<td class="textonormal" align="right">
      	      		<input type="button" value="Incluir" class="botao" onClick="javascript:enviar('Incluir');">
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
<script language="JavaScript">
<!--
document.TabCEPIncluir.CEP.focus();
//-->
</script>
