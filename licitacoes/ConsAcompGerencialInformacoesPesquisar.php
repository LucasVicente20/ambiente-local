<?php
#-----------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsAcompGerencialInformacoesPesquisa.php
# Autor:    Igor Duarte
# Data:     17/05/13
# Objetivo: Programa de Consulta Acompanhamento Gerencial de Informações com geração de planilha eletrônica
# OBS.:     Tabulação 2 espaços
#-----------------------------------------------------------------------------

$programa = "ConsAcompGerencialInformacoesPesquisar.php";

# Acesso ao arquivo de funções #
require_once("../compras/funcoesCompras.php");

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/licitacoes/ConsAcompGerencialInformacoesPesquisar.php' );
AddMenuAcesso( '/licitacoes/ConsAcompGerencialInformacoesImpressao.php' );
AddMenuAcesso( '/licitacoes/ConsAcompGerencialInformacoesResultado.php' );

# Abrindo Conexão
$db = Conexao();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Critica          	= $_POST['Critica'];
		$Botao            	= $_POST['Botao'];
		//$Orgao				= $_POST['Orgao'];
		$Grupo				= $_POST['Grupo'];
		$Comissao	   		= $_POST['Comissao'];
		$Modalidade			= $_POST['Modalidade'];
		$Ano              	= $_POST['Ano'];
		$Fase             	= $_POST['Fase'];
		$Ordenacao		  	= $_POST['Ordenacao'];
}else{
		//$Orgao				= $_GET['Orgao'];
		$Grupo				= $_GET['Grupo'];
		$Comissao			= $_GET['Comissao'];
		$Modalidade			= $_GET['Modalidade'];
		$Fase             	= $_GET['Fase'];
		$Ordenacao		  	= $_GET['Ordenacao'];
		$Critica          	= $_GET['Critica'];
		$Mensagem         	= $_GET['Mensagem'];
		$Mens             	= $_GET['Mens'];
		$Tipo             	= $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "ConsAcompGerencialInformacoesPesquisar.php";

if( $Botao == "Pesquisar" || $Botao == "Planilha" ){
	$Mens      = 0;
	$Mensagem .= "Informe : ";
	if( $Ano == "" ){
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.Acomp.Ano.focus();\" class=\"titulo2\">Ano</a>";
	}else{
		if( ! SoNumeros($Ano) ){
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.Acomp.Ano.focus();\" class=\"titulo2\">Ano Válido</a>";
		}else{
			if( strlen($Ano) < 4 ){
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.Acomp.Ano.focus();\" class=\"titulo2\">Ano com 4 digítos</a>";
			}else{
				if( $Ano > date("Y") ){
					$Mens      = 1;
					$Tipo      = 2;
					$Mensagem .= "<a href=\"javascript:document.Acomp.Ano.focus();\" class=\"titulo2\">Ano menor ou igual ao ano atual</a>";
				}
			}
		}
	}
	if( $Mens == 0 && $Botao == "Pesquisar" ){
		//$Url = "ConsAcompGerencialInformacoesResultado.php?Orgao=$Orgao&Grupo=$Grupo&Comissao=$Comissao&Modalidade=$Modalidade&Fase=$Fase&Ano=$Ano&Ordenacao=$Ordenacao";
		$Url = "ConsAcompGerencialInformacoesResultado.php?Grupo=$Grupo&Comissao=$Comissao&Modalidade=$Modalidade&Fase=$Fase&Ano=$Ano&Ordenacao=$Ordenacao";
		if (!in_array($Url,$_SESSION['GetUrl'])){
			$_SESSION['GetUrl'][] = $Url;
		}
		header("location: ".$Url);
		exit();
	}
	elseif($Mens == 0 && $Botao == "Planilha"){
		$Url = "ConsAcompGerencialInformacoesImpressao.php?Grupo=$Grupo&Comissao=$Comissao&Modalidade=$Modalidade&Fase=$Fase&Ano=$Ano&Ordenacao=$Ordenacao";
		if (!in_array($Url,$_SESSION['GetUrl'])){
			$_SESSION['GetUrl'][] = $Url;
		}
		header("location: ".$Url);
		exit();
	}
}
elseif( $Botao == "Limpar" ){
	//$Url = "ConsAcompGerencialInformacoesPesquisar.php?Orgao=&Grupo=&Comissao=&Modalidade=&Fase=2&Ano=&Ordenacao=";
	$Url = "ConsAcompGerencialInformacoesPesquisar.php?Grupo=&Comissao=&Modalidade=&Fase=2&Ano=&Ordenacao=";
	
	if (!in_array($Url,$_SESSION['GetUrl'])){
		$_SESSION['GetUrl'][] = $Url;
	}
	header("location: ".$Url );
	exit();
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
	document.Acomp.Botao.value=valor;
	document.Acomp.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="ConsAcompGerencialInformacoesPesquisar.php" method="post" name="Acomp">
		<br><br><br><br>
		<table cellpadding="3" border="0" summary="">
  		<!-- Caminho -->
  			<tr>
    			<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    			<td align="left" class="textonormal" colspan="2"><br>
    				<font class="titulo2">|</font>
      				<a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Consultas > Acompanhamento
    			</td>
	  			<td></td>
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
		        				<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal">
		          					<tr>
		            					<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
			    							CONSULTA DE ACOMPANHAMENTO DE LICITAÇÕES
			          					</td>
			        				</tr>
		  	      					<tr>
		    	      					<td class="textonormal" >
		      	    						<p align="justify">
	        	    						Para gerar a Consulta de Acompanhamento das Licitações, selecione o(s) item(ns) de pesquisa e clique no botão "Pesquisar". 
	        	    						Para limpar a pesquisa, clique no botão "Limpar".
		        	    					</p>
		          						</td>
			        				</tr>
		  	        				<tr>
		  	        					<td>
		    	      						<table class="textonormal" border="0" summary="">
		            							<tr>
	        	      								<td class="textonormal" bgcolor="#DCEDF7">Grupo</td>
	          	    								<td class="textonormal" >
                        								<select name="Grupo" value="" class="textonormal">
			                  								<option value="">Todos os Grupo...</option>
									                  		<!-- Mostra os grupos cadastrados -->
									                  		<?php
									                  		$db  = Conexao();
									                  		$sql = "SELECT CGREMPCODI, EGREMPDESC FROM SFPC.TBGRUPOEMPRESA ORDER BY EGREMPDESC";
															
									                  		$result = $db->query($sql);
																
															if( PEAR::isError($result) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
															}
						
															while( $Linha = $result->fetchRow() ){
									          	      			echo"<option value=\"$Linha[0]\">$Linha[1]</option>\n";
								  	                		}
								    	              		
								  	                		$db->disconnect();
									      	            	?>
									                  	</select>
														<input type="hidden" name="Critica" value="1" size="1">
													</td>
							            		</tr>
		            							<tr>
			              							<td class="textonormal"  bgcolor="#DCEDF7">Comissão </td>
			              							<td class="textonormal">
			  				  	      					<select name="Comissao" class="textonormal">
															<option value="">Todas as Comissões...</option>
														<?php
															$db	= Conexao();
															$sql = "SELECT CCOMLICODI, ECOMLIDESC FROM SFPC.TBCOMISSAOLICITACAO WHERE FCOMLISTAT = 'A' ORDER BY ECOMLIDESC";
															
			                  								$result = $db->query($sql);
															
															if( PEAR::isError($result) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
															}
						
															while( $Linha = $result->fetchRow() ){
									          	      			echo"<option value=\"$Linha[0]\">$Linha[1]</option>\n";
								  	                		}
								  	                		
			    	              							$db->disconnect();
														?>
														</select>
													</td>
												</tr>
												<tr>
		        	      							<td class="textonormal" bgcolor="#DCEDF7">Modalidade</td>
													<td class="textonormal" >
														<select name="Modalidade" class="textonormal">
															<option value="">Todas as Modalidades...</option>
														<?php
												    		$db     = Conexao();
															$sql    = "SELECT CMODLICODI, EMODLIDESC FROM SFPC.TBMODALIDADELICITACAO ORDER BY EMODLIDESC";
															
			                  								$result = $db->query($sql);
														
															if( PEAR::isError($result) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
															}
						
															while( $Linha = $result->fetchRow() ){
									          	      			echo"<option value=\"$Linha[0]\">$Linha[1]</option>\n";
								  	                		}
								  	                		
										     				$db->disconnect();
														?>
												  		</select>
											  		</td>
												</tr>
												<tr>
		        	      							<td class="textonormal" bgcolor="#DCEDF7">Processos em Andamento &nbsp</td>
													<td class="textonormal" >
														<?php if( $Fase == "" ){ $Fase = 1; }?>
														<input type="radio" name="Fase" value="1" <?php if( $Fase == 1 ){ echo "checked "; }?>> Sim
			          	    							<input type="radio" name="Fase" value="2" <?php if( $Fase == 2 ){ echo "checked "; }?>> Não
			  				  	    				</td>
												</tr>
		       									<tr>
												  	<td class="textonormal" bgcolor="#DCEDF7">Ano*</td>
				              						<td class="textonormal">
				              						<?php if( $Ano == "" ){ $Ano = date("Y"); }?>
														<input type="text" name="Ano" size="4" maxlength="4" value="<?php echo $Ano?>" class="textonormal">
												  	</td>
												</tr>
												<tr>
		        	      							<td class="textonormal" bgcolor="#DCEDF7">Ordenação</td>
													<td class="textonormal" >
														<?php if( $Ordenacao == "" ){ $Ordenacao = 1; }?>
														<input type="radio" name="Ordenacao" value="1" <?php if( $Ordenacao == 1 ){ echo "checked "; }?>> Órgão
			          	    							<input type="radio" name="Ordenacao" value="2" <?php if( $Ordenacao == 2 ){ echo "checked "; }?>> Comissão de Licitação
			  				  	    				</td>
												</tr>
		          							</table>
			          					</td>
			        				</tr>
		  	      					<tr>
	   	  	  							<td class="textonormal" align="right">
						        	      	<input type="button" name="Pesquisar" value="Gerar Consulta" class="botao" onclick="javascript:enviar('Pesquisar');">
						        	      	<input type="button" name="Pesquisar" value="Gerar Planilha" class="botao" onclick="javascript:enviar('Planilha');">
						        	      	<input type="button" name="Limpar" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
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
<script language="javascript" type="">
<!--
document.Acomp.GrupoCodigo.focus();
//-->
</script>