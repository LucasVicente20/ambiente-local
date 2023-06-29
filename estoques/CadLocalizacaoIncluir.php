<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabLocalizacaoIncluir.php
# Autor:    Franklin Alves
# Data:     01/07/05
# Objetivo: Programa de Inclusão de Localização
#---------------------------------
# Alterado:	Marcus Thiago
# Data:			04/01/2006
# Alterado: Ariston
# Data:     05/04/2009	- CR800- Não permitir que o usuário cadastre mais de uma localização
#------------------------------
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao			  		 = $_POST['Botao'];
		$Almoxarifado  		 = $_POST['Almoxarifado'];
		$CarregaAlmoxarifado = $_POST['CarregaAlmoxarifado'];
		$Area		      		 = $_POST['Area'];
    $TipoEquipamento	 = $_POST['TipoEquipamento'];
    $Prateleira				 = $_POST['Prateleira'];
    $Coluna					   = $_POST['Coluna'];
    $NumeroEquipamento = $_POST['NumeroEquipamento'];
    $Coluna					   = $_POST['Coluna'];
    $Situacao					 = $_POST['Situacao'];
    $Critica         	 = $_POST['Critica'];
}
# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Selecionar" ) {
		$Mens     = 0;
		$Mensagem = "Informe: ";
    if( ($Almoxarifado == "") && ($CarregaAlmoxarifado == 'N') ){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Almoxarifado";
		} elseif ($Almoxarifado == "") {
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadLocalizacaoIncluir.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		}
		if( $Area == "" ) {
	      if( $Mens == 1 ){ $Mensagem .= ", "; }
	      $Mens      = 1;
	      $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript: document.CadLocalizacaoIncluir.Area.focus();\" class=\"titulo2\">Área</a>";
    }
		if( $NumeroEquipamento == "" ){
	      if( $Mens == 1 ){ $Mensagem .= ", "; }
	      $Mens      = 1;
	      $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript: document.CadLocalizacaoIncluir.NumeroEquipamento.focus();\" class=\"titulo2\">Número do Equipamento</a>";
    }else{
  			if( ! SoNumeros($NumeroEquipamento) ){
			      if( $Mens == 1 ){ $Mensagem .= ", "; }
			      $Mens      = 1;
			      $Tipo      = 2;
		        $Mensagem .= "<a href=\"javascript: document.CadLocalizacaoIncluir.NumeroEquipamento.focus();\" class=\"titulo2\">Número do Equipamento Válido</a>";
    		}
  	}
    if( $Coluna == "" ){
	      if( $Mens == 1 ){ $Mensagem .= ", "; }
	      $Mens      = 1;
	      $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript: document.CadLocalizacaoIncluir.Coluna.focus();\" class=\"titulo2\">Coluna</a>";
    }else{
  			if( ! SoNumeros($Coluna) ){
			      if( $Mens == 1 ){ $Mensagem .= ", "; }
			      $Mens      = 1;
			      $Tipo      = 2;
		        $Mensagem .= "<a href=\"javascript: document.CadLocalizacaoIncluir.Coluna.focus();\" class=\"titulo2\">Coluna Válida</a>";
    		}
  	}

		if( $Mens == 0 ) {
				$db = Conexao();
				/*
				$sql  = "SELECT COUNT(*) FROM SFPC.TBLOCALIZACAOMATERIAL ";
				$sql .= " WHERE CALMPOCODI = $Almoxarifado AND CARLOCCODI = $Area ";
				$sql .= "   AND FLOCMAEQUI = '$TipoEquipamento' AND ALOCMANEQU = $NumeroEquipamento";
				*/
				$sql ="
					SELECT COUNT(*)
					FROM SFPC.TBLOCALIZACAOMATERIAL
					WHERE CALMPOCODI = $Almoxarifado
				";
				$res  = $db->query($sql);
	  		if( PEAR::isError($res) ){
			 		 //ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			 		 EmailErroDB("Erro em SQL", "Ocorreu um erro na execução de um SQL", $res);
				}else{
						$Qtd = $res->fetchRow();
						if( $Qtd[0] > 0 ){
								$Mens     = 1;
								$Tipo     = 2;
								//$Mensagem = "Este Equipamento já foi cadastrado para esta Área";
								$Mensagem = "Almoxarifado não pode possuir mais de uma localização";
				    }else{
							  $db = Conexao();
								$db->query("BEGIN TRANSACTION");
								for( $i=1;$i<=NumAlfabeto($Prateleira);$i++ ){
										for( $j=1; $j<= $Coluna; $j++ ){
											$sql = "select max(clocmacodi) from SFPC.TBLOCALIZACAOMATERIAL";
											$result = $db->query($sql);
											if( PEAR::isError($result) ){
												$db->query("ROLLBACK");
												//ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												EmailErroDB("Erro em SQL", "Ocorreu um erro na execução de um SQL", $result);	
											}					
											$Linha = $result->fetchRow();
											$codigoLocalizacao = $Linha[0]+1;
																
							  		  $sql    = "INSERT INTO SFPC.TBLOCALIZACAOMATERIAL ( ";
											$sql   .= "CALMPOCODI, FLOCMAEQUI, ALOCMANEQU, CARLOCCODI, ";
											$sql   .= "ALOCMAPRAT, ALOCMACOLU, FLOCMASITU, CGREMPCODI, ";
											$sql   .= "CUSUPOCODI, TLOCMAULAT, CLOCMACODI ";
											$sql   .= " ) VALUES ( ";
											$sql   .= "$Almoxarifado, '$TipoEquipamento', $NumeroEquipamento, $Area,";
											$sql   .= "'".AlfaNumero($i)."', $j, 'A', ".$_SESSION['_cgrempcodi_'].", ";
					            $sql   .= "".$_SESSION['_cusupocodi_'].", '".date("Y-m-d H:i:s")."', ".$codigoLocalizacao.") ";
											$result = $db->query($sql);
											if( PEAR::isError($result) ){
													$db->query("ROLLBACK");
											    //ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
											    EmailErroDB("Erro em SQL", "Ocorreu um erro na execução de um SQL", $result);
											    
											}
								    }
										$Inseriu = "S";
							  }
								$db->query("COMMIT");
								$db->query("END TRANSACTION");
								$db->disconnect();

								if( $Inseriu == "S" ){
										# Limpando Variáveis #
										$Mens              = 1;
										$Tipo              = 1;
										$Mensagem          = "Localização Incluída com Sucesso";
										$Almoxarifado  		 = "";
								    $TipoEquipamento   = "";
								    $NumeroEquipamento = "";
								    $Prateleira				 = "";
								    $Coluna					   = "";
								    $Coluna					   = "";
								    $Situacao					 = "";
								}
	      		}
				}
		}
}

if ($Botao == ""){
		# Seleciona os almoxarifados disponíveis de acordo com o centro de custo do usuário #
		$db     = Conexao();
		if( $_SESSION['_cgrempcodi_'] == 0 ){
  			$sql    = "SELECT A.CALMPOCODI, A.EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL A ";
				if ($Almoxarifado) {
						$sql   .= " WHERE A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A'";
				}
		} else {
  			$sql    = "SELECT A.CALMPOCODI, A.EALMPODESC, B.CORGLICODI ";
				$sql   .= "  FROM SFPC.TBALMOXARIFADOPORTAL A, SFPC.TBALMOXARIFADOORGAO B ";
				$sql   .= " WHERE A.CALMPOCODI = B.CALMPOCODI ";
				if ($Almoxarifado) {
						$sql   .= " AND A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A'";
				}
				$sql .= "   AND B.CORGLICODI = ";
	    	$sql .= "       ( SELECT DISTINCT CEN.CORGLICODI ";
	    	$sql .= "           FROM SFPC.TBCENTROCUSTOPORTAL CEN, SFPC.TBUSUARIOCENTROCUSTO USU ";
		    $sql .= "          WHERE USU.CCENPOSEQU = CEN.CCENPOSEQU AND USU.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." AND CEN.FCENPOSITU <> 'I' AND USU.FUSUCCTIPO IN ('T','R')) ";
    }
		$sql .= " ORDER BY A.EALMPODESC ";

		$result = $db->query($sql);
		if( PEAR::isError($result) ){
				//ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				EmailErroDB("Erro em SQL", "Ocorreu um erro na execução de um SQL", $result);
		}else{
				while( $Linha = $result->fetchRow() ){
						$Aux = $Linha[0];
			  }
   }
   if( $Aux == "" ){
				$Mens     = 1;
				$Tipo     = 2;
				$Mensagem = "Nenhum Almoxarifado cadastrado p/o Centro de Custo/Orgão deste Usuário";
	 }
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript">
<!--
function enviar(valor){
	document.CadLocalizacaoIncluir.Botao.value=valor;
	document.CadLocalizacaoIncluir.submit();
}
function remeter(){
	document.CadLocalizacaoIncluir.submit();
}

<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadLocalizacaoIncluir.php" method="post" name="CadLocalizacaoIncluir">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Localização > Incluir
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
      <table  border="0" cellspacing="0" cellpadding="3" bgcolor="#FFFFFF">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
		    					INCLUIR - LOCALIZAÇÃO
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal" >
	      	    		<p align="justify">
	        	    		Para incluir uma nova Localização, informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.
	          	   	</p>
	          		</td>
		        	</tr>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left">
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Almoxarifado</td>
				              <td class="textonormal">
			                	<?php
			              		# Mostra o(s) Almoxarifado(s) de Acordo com o Usuário Logado #
												$db  = Conexao();
			                  if( $_SESSION['_cgrempcodi_'] == 0 ){
				              			$sql    = "SELECT A.CALMPOCODI, A.EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL A ";
														if ($Almoxarifado) {
																$sql   .= " WHERE A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A'";
														}
												} else {
				              			$sql    = "SELECT A.CALMPOCODI, A.EALMPODESC ";
														$sql   .= "  FROM SFPC.TBALMOXARIFADOPORTAL A, SFPC.TBALMOXARIFADOORGAO B ";
														$sql   .= " WHERE A.CALMPOCODI = B.CALMPOCODI ";
														if ($Almoxarifado) {
																$sql   .= " AND A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A'";
														}
														$sql .= "   AND B.CORGLICODI = ";
											    	$sql .= "       ( SELECT DISTINCT CEN.CORGLICODI ";
											    	$sql .= "           FROM SFPC.TBCENTROCUSTOPORTAL CEN, SFPC.TBUSUARIOCENTROCUSTO USU ";
												    $sql .= "          WHERE USU.CCENPOSEQU = CEN.CCENPOSEQU AND USU.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." AND CEN.FCENPOSITU <> 'I' AND USU.FUSUCCTIPO IN ('T','R')) ";
			                  }
												$sql .= " ORDER BY A.EALMPODESC ";
			              		$res  = $db->query($sql);
												if( PEAR::isError($res) ){
												    //ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												    EmailErroDB("Erro em SQL", "Ocorreu um erro na execução de um SQL", $res);
												}else{
														$Rows = $res->numRows();
														if( $Rows == 1 ){
																$Linha = $res->fetchRow();
				          	      			$Almoxarifado = $Linha[0];
			        	   	      			echo "$Linha[1]<br>";
			        	   	      			echo "<input type=\"hidden\" name=\"Almoxarifado\" value=\"$Almoxarifado\">";
			        	   	      			echo $DescAlmoxarifado;
							            	}elseif( $Rows > 1 ){
																echo "<select name=\"Almoxarifado\" class=\"textonormal\" onChange=\"submit();\">\n";
							                  echo "	<option value=\"\">Selecione um Almoxarifado...</option>\n";
																for( $i=0;$i< $Rows; $i++ ){
																		$Linha = $res->fetchRow();
																		$DescAlmoxarifado = $Linha[1];
				          	   	      			if( $Linha[0] == $Almoxarifado ){
				          	   	      					echo"<option value=\"$Linha[0]\" selected>$DescAlmoxarifado</option>\n";
						          	      			}else{
						          	      					echo"<option value=\"$Linha[0]\">$DescAlmoxarifado</option>\n";
						          	      			}
							                	}
							                	echo "</select>\n";
							                	$CarregaAlmoxarifado = "";
							              } else {
							            			echo "ALMOXARIFADO NÃO CADASTRADO OU INATIVO";
		  	          	   	    		echo "<input type=\"hidden\" name=\"CarregaAlmoxarifado\" value=\"N\">";
							            	}
					              }
			           			 	$db->disconnect();
			    	            ?>
				              </td>
				            </tr>
				        	 <tr>
					            <td class="textonormal" bgcolor="#DCEDF7"> Área*</td>
					            <td class="textonormal">
					              <select name="Area" class="textonormal">
					    	           <option value="">Selecione uma Área...</option>
					              	<!-- Mostra as Áreas cadastradas -->
					                <?php
					                if ($Almoxarifado != ""){
							                $db     = Conexao();
							            		$sql    = "SELECT CARLOCCODI, CALMPOCODI, EARLOCDESC ";
															$sql   .= "  FROM SFPC.TBAREAALMOXARIFADO ";
															$sql   .= " WHERE CALMPOCODI = $Almoxarifado ORDER BY EARLOCDESC";
														  $result = $db->query($sql);
							            		if( PEAR::isError($result) ){
															    //ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
															    EmailErroDB("Erro em SQL", "Ocorreu um erro na execução de um SQL", $result);
															}else{
																	while( $Linha = $result->fetchRow() ){
							          	      			if( $Area == $Linha[0] ){
							          	      					echo"<option value=\"$Linha[0]\" selected> $Linha[2] </option>\n";
							          	      			}else{
							          	      					echo"<option value=\"$Linha[0]\"> $Linha[2] </option>\n";
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
											<td class="textonormal"  bgcolor="#DCEDF7">Tipo de Equipamento*</td>
											<td class="textonormal" >
												<select name="TipoEquipamento" size="1" value="A" class="textonormal">
													<option value="E">ESTANTE </option>
													<option value="P">PALETE</option>
													<option value="A">ARMÁRIO</option>
												</select>
											</td>
										</tr>
										<tr>
			      	        <td class="textonormal" bgcolor="#DCEDF7">Número do Equipamento*</td>
			        	    	<td class="textonormal">
			        	    		<input type="text" name="NumeroEquipamento" size="2" maxlength="2" value="<?php echo $NumeroEquipamento;?>" class="textonormal" >
			        	    	</td>
			          		</tr>
										<tr>
											<td class="textonormal"  bgcolor="#DCEDF7">Prateleira Máxima*</td>
											<td class="textonormal" >
												<select name="Prateleira" size="1" value="A" class="textonormal">
													<?php
													$Prateleiras = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
													for ($i = 0; $i < count($Prateleiras); $i++) {
															if ($Prateleira == $Prateleiras[$i]) {echo"<option selected value=\"$Prateleiras[$i]\">$Prateleiras[$i]</option>"; }else{ echo"<option value=\"$Prateleiras[$i]\">$Prateleiras[$i]</option>";}
													}
													?>
												</select>
											</td>
										</tr>
			          		<tr>
		      	      		<td class="textonormal" bgcolor="#DCEDF7">Coluna Máxima*</td>
		        	    		<td class="textonormal">
		        	    			<input type="text" name="Coluna" size="2" maxlength="3" value="<?php echo $Coluna;?>" class="textonormal">
		        	    		</td>
			          		</tr>
          		    </table>
		          	</td>
		        	</tr>
	  	      	<tr>
	    	  			<td class="textonormal" align="right">
	          	  	<input type="button" value="Incluir" class="botao" onClick="javascript:enviar('Selecionar');">
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
