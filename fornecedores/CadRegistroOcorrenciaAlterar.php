<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadRegistroOcorrenciaAlterar.php
# Autor:    Roberta Costa
# Data:     29/09/04
# Objetivo: Programa de Inclusão de Ocorrências dos Fornecedores
# Alterado: Rossana Lira
# Data:     18/05/07 - Correção da chamada do CadRegistroOcorrenciaSelecionarManter
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/fornecedores/CadRegistroOcorrencia.php' );
AddMenuAcesso( '/fornecedores/CadRegistroOcorrenciaExcluir.php' );
AddMenuAcesso( '/fornecedores/CadRegistroOcorrenciaSelecionarManter.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']	== "POST" ){
		$Botao          = $_POST['Botao'];
		$DataOcorrencia	= $_POST['DataOcorrencia'];
		$Detalhamento   = strtoupper2(trim($_POST['Detalhamento']));
		$NCaracteres    = $_POST['NCaracteres'];
		$Sequencial	    = $_POST['Sequencial'];
		$DataInscricao  = $_POST['DataInscricao'];
		$Ocorrencia	    = $_POST['Ocorrencia'];
}else{
		$Sequencial	= $_GET['Sequencial'];
		$Ocorrencia	= $_GET['Ocorrencia'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

$db	= Conexao();
if( $Botao == "Voltar" ){
		$Url = "CadRegistroOcorrencia.php?Sequencial=$Sequencial";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit;
}elseif( $Botao == "Excluir" ){
		$Url = "CadRegistroOcorrenciaExcluir.php?Sequencial=$Sequencial&Ocorrencia=$Ocorrencia";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit;
}elseif( $Botao == "Alterar" ){
		$Mens      = 0;
		$Mensagem  = "Informe: ";
		if( $DataOcorrencia == "" ){
				if ($Mens == 1){$Mensagem .= ", ";}
				$Mens      = 1;
				$Tipo      = 2;
		  	$Mensagem .= "<a href=\"javascript:document.CadRegistroOcorrenciaAlterar.DataOcorrencia.focus();\" class=\"titulo2\">Data da Ocorrência</a>";
		}else{
				$ValidaData = ValidaData($DataOcorrencia);
				if( ($ValidaData != "" ) or (DataInvertida($DataOcorrencia) > date("Y-m-d") )){
						if ( $Mens == 1 ) { $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.CadRegistroOcorrenciaAlterar.DataOcorrencia.focus();\" class=\"titulo2\">Data Válida</a>";
				}else{
						if( DataInvertida($DataOcorrencia) <= $DataInscricao ){
								if ( $Mens == 1 ) { $Mensagem .= ", "; }
								$Mens      = 1;
								$Tipo      = 2;
								$Mensagem .= "<a href=\"javascript:document.CadRegistroOcorrenciaAlterar.DataOcorrencia.focus();\" class=\"titulo2\">Data da Ocorrência maior que a data de Inscrição</a>";
						}
				}
		}
		if( $Detalhamento == "" ){
				if ($Mens == 1){$Mensagem .= ", ";}
				$Mens      = 1;
				$Tipo      = 2;
		  	$Mensagem .= "<a href=\"javascript:document.CadRegistroOcorrenciaAlterar.Detalhamento.focus();\" class=\"titulo2\">Detalhamento da Ocorrência</a>";
		}elseif( strlen($Detalhamento) > 200 ) {
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadRegistroOcorrenciaAlterar.Detalhamento.focus();\" class=\"titulo2\">Detalhamento da Ocorrência com até 200 Caracteres</a>";
		}
		if( $Mens == 0 ){
	      # Insere Ocorrencia #
		    $db->query("BEGIN TRANSACTION");
		    $sql    = "UPDATE SFPC.TBFORNECEDOROCORRENCIA ";
		    $sql   .= "   SET EFOROCDETA = '$Detalhamento', DFOROCDATA = '".DataInvertida($DataOcorrencia)."', ";
		    $sql   .= "       CGREMPCODI = ".$_SESSION['_cgrempcodi_'].", CUSUPOCODI = ".$_SESSION['_cusupocodi_'].", ";
		    $sql   .= "       TFOROCULAT = '".DataAtual()."' ";
		    $sql   .= " WHERE AFORCRSEQU = $Sequencial AND CFORTOCODI = $Ocorrencia ";
		    $result = $db->query($sql);
				if( PEAR::isError($result) ) {
				    $db->query("ROLLBACK");
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$db->query("COMMIT");
						$db->query("END TRANSACTION");
						$db->disconnect();

						# Redireciona para página de Selecionar #
						$Mensagem = "Ocorrência Alterada com Sucesso";
			      $Url = "CadRegistroOcorrenciaSelecionarManter.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
			      header("location: ".$Url);
						exit;
				}
		}
		$Botao = "";
}
if( $Botao == "" ){
		# Pega os Dados do Fornecedor Cadastrado #
		$sql    = " SELECT AFORCRCCGC, AFORCRCCPF, NFORCRRAZS, DFORCRGERA ";
		$sql   .= "   FROM SFPC.TBFORNECEDORCREDENCIADO ";
		$sql   .= "  WHERE AFORCRSEQU = $Sequencial";
	  $result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha         = $result->fetchRow();
				$CNPJ				   = $Linha[0];
				$CPF				   = $Linha[1];
				$RazaoSocial   = $Linha[2];
				$DataInscricao = $Linha[3];
		}

		# Pega os Dados da Ocorrência #
		$sql    = " SELECT EFOROCDETA, DFOROCDATA ";
		$sql   .= "   FROM SFPC.TBFORNECEDOROCORRENCIA ";
		$sql   .= "  WHERE AFORCRSEQU = $Sequencial AND CFORTOCODI = $Ocorrencia";
	  $result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha          = $result->fetchRow();
				$Detalhamento	  = $Linha[0];
				$DataOcorrencia	= DataBarra($Linha[1]);
				$NCaracteres    = strlen($Detalhamento);
		}
}
$db->disconnect();
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.CadRegistroOcorrenciaAlterar.Botao.value=valor;
	document.CadRegistroOcorrenciaAlterar.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadRegistroOcorrenciaAlterar.php" method="post" name="CadRegistroOcorrenciaAlterar">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedores > Registro das Ocorrências > Manter
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<tr>
	  <td width="100"></td>
	  <td align="left" colspan="2">
	  	<?php if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,1); } ?>
	  </td>
	</tr>
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
		    					REGISTRO DAS OCORRÊNCIAS - ALTERAR
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
									<p align="justify">
										Para atualizar uma Ocorrência, preencha os campos obrigatórios e clique no botão "Alterar". Para apagar uma Ocorrência clique no botão "Excluir".Para retornar para a tela anterior clique no botão "Voltar".
	          	   	</p>
	          		</td>
		        	</tr>
		        	<tr>
								<td>
									<table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" summary="">
										<tr>
											<td>
						  					<table class="textonormal" border="0" cellpadding="0" cellspacing="2" width="100%" summary="">
							            <tr>
							              <td class="textonormal" bgcolor="#DCEDF7">Razão Social/Nome</td>
							              <td class="textonormal" height="20"><?php echo $RazaoSocial;?></td>
							            </tr>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7">
															<?php if( $CNPJ != "" ){ echo "CNPJ\n"; } else { echo "CPF\n"; }?>
			          	    			</td>
														<td class="textonormal" height="20">
					          	    		<?php
															if( $CNPJ <> 0 ){
			    												$CNPJCPFForm	= substr($CNPJ,0,2).".".substr($CNPJ,2,3).".".substr($CNPJ,5,3)."/".substr($CNPJ,8,4)."-".substr($CNPJ,12,2);
						          	    			echo $CNPJCPFForm;
			  											}else{
				    											$CNPJCPFForm  = substr($CPF,0,3).".".substr($CPF,3,3).".".substr($CPF,6,3)."-".substr($CPF,9,2);
						          	    			echo $CNPJCPFForm;
			    										}
															?>
				          	    		</td>
				            			</tr>
							            <tr>
							              <td class="textonormal" bgcolor="#DCEDF7">Data de Cadastramento</td>
							              <td class="textonormal" height="20">
							              	<?php echo substr($DataInscricao,8,2)."/".substr($DataInscricao,5,2)."/".substr($DataInscricao,0,4);?>
							              </td>
							            </tr>
									  			<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo de Ocorrência</td>
												    <td class="textonormal">
					                  	<!-- Mostra os perfis cadastrados -->
					                  	<?php
					                		$db     = Conexao();
					                		$sql    = "SELECT B.EFORTODESC ";
					                		$sql   .= "  FROM SFPC.TBFORNECEDOROCORRENCIA A, SFPC.TBFORNTIPOOCORRENCIA B";
					                		$sql   .= " WHERE A.CFORTOCODI = B.CFORTOCODI  AND A.CFORTOCODI = $Ocorrencia ";
					                		$sql   .= "   AND A.AFORCRSEQU = $Sequencial";
					                		$result = $db->query($sql);
					                		if (PEAR::isError($result)) {
															    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
															}else{
															    $Linha = $result->fetchRow();
			          	      					echo "$Linha[0]\n";
						                	}
					  	              	$db->disconnect();
					    	     	       ?>
						                </td>
						              </tr>
									  			<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Data da Ocorrência*</td>
							              <td class="textonormal">
															<?php $URL = "../calendario.php?Formulario=CadRegistroOcorrenciaAlterar&Campo=DataOcorrencia"; ?>
															<input type="text" name="DataOcorrencia" size="10" maxlength="10" value="<?php echo $DataOcorrencia;?>" class="textonormal">
															<a href="javascript:janela('<?php echo $URL ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
								      			</td>
									  			</tr>
									  			<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20" width="35%" valign="top">Detalhamento da Ocorrência*</td>
							              <td class="textonormal">
							                máximo de 200 caracteres
															<input type="text" name="NCaracteres" disabled size="3" value="<?php echo $NCaracteres ?>" class="textonormal"><br>
															<textarea name="Detalhamento" cols="39" rows="5" OnKeyUp="javascript:ncaracteres(1)" OnBlur="javascript:ncaracteres(0)" OnSelect="javascript:ncaracteres(1)" class="textonormal"><?php echo $Detalhamento; ?></textarea>
									          </td>
									  			</tr>
												</table>
						  				</td>
						  			</tr>
									</table>
								</td>
		        	</tr>
  						<tr>
								<td class="textonormal" align="right">
									<input type="hidden" name="DataInscricao" value="<?php echo $DataInscricao; ?>">
	            		<input type="hidden" name="Sequencial" value="<?php echo $Sequencial; ?>">
	            		<input type="hidden" name="Ocorrencia" value="<?php echo $Ocorrencia; ?>">
			          	<input type="button" value="Alterar" class="botao" onclick="javascript:enviar('Alterar');">
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
	<!-- Fim do Corpo -->
</table>
</form>
<script language="javascript" type="">
<!--
function ncaracteres(valor){
	document.CadRegistroOcorrenciaAlterar.NCaracteres.value = '' +  document.CadRegistroOcorrenciaAlterar.Detalhamento.value.length;
	if( navigator.appName == 'Netscape' && valor ) {  //Netscape Only
		document.CadRegistroOcorrenciaAlterar.NCaracteres.focus();
	}
}
//-->
</script>
</body>
</html>
