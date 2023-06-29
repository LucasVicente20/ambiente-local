<?php
# ----------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadAlmoxarifadoIncluir.php
# Objetivo: Programa de Inclusão de Almoxarifado
# Autor:    Franklin Alves
# Data:     27/06/05
# OBS.:     Tabulação 2 espaços
# ----------------------------------------------------------------------------
# Alterado: Carlos Abreu 
# Data:     12/03/2007 - Inclusão do campo RPA
# ----------------------------------------------------------------------------
# Alterado: Carlos Abreu 
# Data:     01/06/2007 - Inclusão da critica para pelo menos 1 orgao
# ----------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		09/08/2018
# Objetivo: Tarefa Redmine 200989
# ----------------------------------------------------------------------------
# Alterado: Caio Coutinho - Pitang Agile TI
# Data:     18/03/2019
# Objetivo: Tarefa Redmine 177358
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/CadAlmoxarifadoSelecionar.php' );

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD']	== "POST" ) {
	$Botao          = $_POST['Botao'];
	$Descricao      = strtoupper2(trim($_POST['Descricao']));
	$Endereco 	    = strtoupper2(trim($_POST['Endereco']));
	$Abre 	        = strtoupper2(trim($_POST['Abre']));
	$Fone           = strtoupper2(trim($_POST['Fone']));
	$RPA            = $_POST['RPA'];
	$TipoAlmo       = $_POST['TipoAlmo'];
	$Situacao       = $_POST['Situacao'];
	$Orgao          = $_POST['Orgao'];
	$EstoqueVirtual = $_POST['EstoqueVirtual'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if ($Botao == "Voltar") {
	header("location: CadAlmoxarifadoSelecionar.php");
	exit;
} elseif ($Botao == "Incluir") {
	$Mens     = 0;
	$Mensagem = "Informe: ";
	
	if ($Descricao == "") {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadAlmoxarifadoIncluir.Descricao.focus();\" class=\"titulo2\">Descrição</a>";
	}
	
	if ($Abre == "") {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadAlmoxarifadoIncluir.Abre.focus();\" class=\"titulo2\">Abreviatura da Descrição</a>";
	}
	
	if ($Endereco == "") {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadAlmoxarifadoIncluir.Endereco.focus();\" class=\"titulo2\">Endereço</a>";
	}
	
	if ($Fone == "") {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadAlmoxarifadoIncluir.Fone.focus();\" class=\"titulo2\">Fone</a>";
	}
	
	if (count($Orgao) == 0) {
	    if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<font class=\"titulo2\">Pelo menos 1 orgão</font>";
	}

	if ($Mens == 0) {
		$db = Conexao();
		
		$sql = "SELECT	COUNT(*) FROM SFPC.TBALMOXARIFADOPORTAL 
				WHERE	EALMPODESC = '$Descricao'";
		
		$res = $db->query($sql);
		
		if (PEAR::isError($res)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		} else {
			$Qtd = $res->fetchRow();
			  
			if ($Qtd[0] > 0) {
				$Mens     = 1;
				$Tipo     = 2;
				$Mensagem = "<a href=\"javascript:document.CadAlmoxarifadoIncluir.Descricao.focus();\" class=\"titulo2\">Descrição já Cadastrada</a>";
			} else {
				$sql = "SELECT	MAX(CALMPOCODI)
						FROM	SFPC.TBALMOXARIFADOPORTAL ";
				
				$res = $db->query($sql);
				   
				if (PEAR::isError($res)) {
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				} else {
					$Max = $res->fetchRow();
                	$Codigo = $Max[0]+1;
              	}
				
				# Inclui na Tabela de Almoxarifado #
			  	$db->query("BEGIN TRANSACTION");
				
				$sql = "INSERT INTO SFPC.TBALMOXARIFADOPORTAL (CALMPOCODI, EALMPODESC, EALMPOABRE, EALMPOENDE, AALMPOFONE, FALMPOTIPO, FALMPOSITU, TALMPOULAT, CALMPONRPA, FALMPOESTV) 
						VALUES ($Codigo, '$Descricao', '$Abre', '$Endereco', '$Fone', '$TipoAlmo', '$Situacao','".date("Y-m-d H:i:s")."', $RPA, '$EstoqueVirtual')";
							
				$res = $db->query($sql);

				if (PEAR::isError($res)) {
					$db->query("ROLLBACK");
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				} else {
					# Grava os órgãos marcados na tabela de AlmoxarifadoOrgao #

					$countOrgao = (is_null($Orgao)) ? 0 : count($Orgao);

					for ($P = 0; $P < $countOrgao; $P++) {
						$OrgaoCod =  $Orgao[$P];
						$Data   = date("Y-m-d H:i:s");
						$sql = "INSERT INTO SFPC.TBALMOXARIFADOORGAO (CALMPOCODI, CORGLICODI, TALMORULAT) 
								VALUES ($Codigo, $OrgaoCod, '$Data') ";
						
						$result = $db->query($sql);
						
						if (PEAR::isError($result)) {
							$db->query("ROLLBACK");
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}
					}
				}
				
				$Mens      = 1;
				$Tipo      = 1;
				$Mensagem  = "Almoxarifado Incluído com Sucesso";
				
				$Descricao = "";
				$Abre      = "";
				$Endereco  = "";
				$Fone      = "";
				$TipoAlmo  = "";
				$Situacao  = "";
				$Orgao	   = "";
				$EstoqueVirtual = "";

				$db->query("COMMIT");
				$db->query("END TRANSACTION");
			}
		}
		$db->disconnect();
	}
}

if ($Botao == "") {
	$NCaracteres = strlen($Descricao);
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
	document.CadAlmoxarifadoIncluir.Grupo.value  = '';
	document.CadAlmoxarifadoIncluir.Classe.value = '';
	document.CadAlmoxarifadoIncluir.submit();
}
function enviar(valor){
  document.CadAlmoxarifadoIncluir.Botao.value = valor;
	document.CadAlmoxarifadoIncluir.submit();
}
function ncaracteres(valor){
	document.CadAlmoxarifadoIncluir.NCaracteres.value = '' +  document.CadAlmoxarifadoIncluir.Descricao.value.length;
	if( navigator.appName == 'Netscape' && valor ) {  //Netscape Only
		document.CadAlmoxarifadoIncluir.NCaracteres.focus();
	}
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadAlmoxarifadoIncluir.php" method="post" name="CadAlmoxarifadoIncluir">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  	<!-- Caminho -->
  	<tr>
    	<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    	<td align="left" class="textonormal" colspan="2">
      		<font class="titulo2">|</font>
      		<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Almoxarifado > Incluir
    	</td>
  	</tr>
  	<!-- Fim do Caminho-->
	<!-- Erro -->
	<?php if ($Mens == 1) { ?>
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
					    						INCLUIR - ALMOXARIFADO
					          				</td>
					        			</tr>
				  	      				<tr>
				    	      				<td class="textonormal">
												<p align="justify">
													Para incluir um novo Almoxarifado informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.
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
	        	      												<td class="textonormal" bgcolor="#DCEDF7">Descrição*</td>
	          	    												<td class="textonormal">
	          	    								 					<input type="text" name="Descricao" value="<?php echo $Descricao; ?>" size="100" maxlength="200" class="textonormal">
	            	  												</td>
	            												</tr>
										            			<tr>
	        	      												<td class="textonormal" bgcolor="#DCEDF7">Abreviatura da Descrição*</td>
	          	    												<td class="textonormal">
	          	    								 					<input type="text" name="Abre" value="<?php echo $Abre; ?>" size="20" maxlength="20" class="textonormal">
	            	  												</td>
	            												</tr>
										            			<tr>
	        	      												<td class="textonormal" bgcolor="#DCEDF7">Endereço*</td>
	          	    												<td class="textonormal">
	          	    								 					<input type="text" name="Endereco" value="<?php echo $Endereco; ?>" size="60" maxlength="60" class="textonormal">
	            	  												</td>
	            												</tr>
										            			<tr>
	        	      												<td class="textonormal" bgcolor="#DCEDF7">Fone*</td>
	          	    												<td class="textonormal">
	          	    								 					<input type="text" name="Fone" value="<?php echo $Fone; ?>" size="30" maxlength="25" class="textonormal">
	            	  												</td>
	            												</tr>
	            												<tr>
	        	      												<td class="textonormal" bgcolor="#DCEDF7">RPA</td>
	          	    												<td class="textonormal">
	          	    								 					<select name="RPA" class="textonormal">
	          	    								 						<option value="1"<?php if ($RPA==1) echo " selected";?>>RPA 1
	          	    								 						<option value="2"<?php if ($RPA==2) echo " selected";?>>RPA 2
	          	    								 						<option value="3"<?php if ($RPA==3) echo " selected";?>>RPA 3
	          	    								 						<option value="4"<?php if ($RPA==4) echo " selected";?>>RPA 4
	          	    								 						<option value="5"<?php if ($RPA==5) echo " selected";?>>RPA 5
	          	    								 						<option value="6"<?php if ($RPA==6) echo " selected";?>>RPA 6
	          	    								 					</select>
	            	  								 					<input type="hidden" name="Critica" value="1">
	            	  												</td>
	            												</tr>
									            				<tr>
		              												<td class="textonormal"  bgcolor="#DCEDF7">Tipo de Almoxarifado</td>
		              												<td class="textonormal" >
		  	              												<select name="TipoAlmo" size="1" value="A" class="textonormal">
		    	              												<option value="A">ALMOXARIFADO</option>
		      	            												<option value="C">CENTRAL </option>
		    	              												<option value="S">SUBALMOXARIFADO</option>
		        	        											</select>
	          	    												</td>
	            												</tr>
	            												<tr>
		              												<td class="textonormal"  bgcolor="#DCEDF7">Situação</td>
		              												<td class="textonormal" >
																		<select name="Situacao" size="1" value="A"  class="textonormal">
																			<option value="A">ATIVO </option>
																		 	<option value="I">INATIVO</option>
																		</select>
	          	    												</td>
	            												</tr>
																<tr>
		              												<td class="textonormal"  bgcolor="#DCEDF7">Liberação do estoque virtual</td>
		              												<td class="textonormal" >
																		<select name="EstoqueVirtual" size="1" value="N"  class="textonormal">
																			<option value="N">NÃO</option>
																			<option value="S">SIM</option>
																		</select>
	          	    												</td>
	            												</tr>
									        	   				<tr>
										        	   				<td class="textonormal" bgcolor="#DCEDF7">Órgãos</td>
							            	   						<td class="textonormal">
																		<select name="Orgao[]" multiple size="8" value="" class="textonormal">
																		<?php	$db = Conexao();
																			$sql = "SELECT CORGLICODI, EORGLIDESC
																					FROM SFPC.TBORGAOLICITANTE
																					WHERE FORGLISITU = 'A'
																					ORDER BY EORGLIDESC";
																			
																			$result = $db->query($sql);
																			
																			if (PEAR::isError($result)) {
																			    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																			} else {
																				while ($Linha = $result->fetchRow()) {
																					if (FindArray($Linha[0],$Orgao)) {
																						echo "<option value=\"$Linha[0]\" selected>$Linha[1]\n";
																					} else {
																						echo "<option value=\"$Linha[0]\">$Linha[1]\n";
																					}
																				}
																			}
																			$db->disconnect(); ?>
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
												<input type="button" value="Incluir" class="botao" onclick="javascript:enviar('Incluir');">
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
    	</td>
  	</tr>
</table>
</form>
</body>
</html> 
