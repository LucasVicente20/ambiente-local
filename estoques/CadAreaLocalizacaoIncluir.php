<?php
#----------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadAreaLocalizacaoIncluir.php
# Autor:    Franklin Alves
# Data:     14/07/05
# Objetivo: Programa de Inclusão de Área de Localização
# OBS.:     Tabulação 2 espaços
#----------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/CadAreaLocalizacaoSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']	== "POST" ){
		$Botao      	= $_POST['Botao'];
		$Almoxarifado = $_POST['Almoxarifado'];
		$Descricao	 	= strtoupper2(trim($_POST['Descricao']));

}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

$db  = Conexao();
if( $_SESSION['_cgrempcodi_'] == 0 ){
		$sqlAlmo    = "SELECT A.CALMPOCODI, A.EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL A ";
		if ($Almoxarifado) {
				$sql   .= " WHERE A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A'";
		}
} else {
		$sqlAlmo    = "SELECT A.CALMPOCODI, A.EALMPODESC, B.CORGLICODI ";
		$sqlAlmo   .= "  FROM SFPC.TBALMOXARIFADOPORTAL A, SFPC.TBALMOXARIFADOORGAO B ";
		$sqlAlmo   .= " WHERE A.CALMPOCODI = B.CALMPOCODI ";
		if ($Almoxarifado) {
				$sql   .= " AND A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A'";
		}
		$sqlAlmo  .= "   AND B.CORGLICODI = ";
  	$sqlAlmo  .= "       ( SELECT DISTINCT CEN.CORGLICODI ";
  	$sqlAlmo  .= "           FROM SFPC.TBCENTROCUSTOPORTAL CEN, SFPC.TBUSUARIOCENTROCUSTO USU ";
    $sqlAlmo  .= "          WHERE USU.CCENPOSEQU = CEN.CCENPOSEQU AND USU.CUSUPOCODI = ". $_SESSION['_cusupocodi_'] ." AND USU.FUSUCCTIPO IN ('T','R')) ";
}
$sqlAlmo .= " ORDER BY A.EALMPODESC ";
$resAlmo  = $db->query($sqlAlmo);
if( PEAR::isError($resAlmo) ){
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlAlmo");
}else{
		$RowsAlmo = $resAlmo->numRows();
}
$db->disconnect();

if( $Botao == "Voltar" ){
		header("location: CadAreaLocalizacaoSelecionar.php");
		exit;
}elseif( $Botao == "Incluir" ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( ($Almoxarifado == "") && ($RowsAlmo >= 1) ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadAreaLocalizacaoIncluir.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		} elseif ($Almoxarifado == "") {
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Almoxarifado";
		}
		if( $Descricao == "" ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadAreaLocalizacaoIncluir.Descricao.focus();\" class=\"titulo2\">Descrição</a>";
		}
    if( $Mens == 0 ){
				$db = Conexao();
				$sql  = "SELECT COUNT(*) FROM SFPC.TBAREAALMOXARIFADO ";
				$sql .= " WHERE EARLOCDESC = '$Descricao' AND CALMPOCODI = $Almoxarifado ";
				$res  = $db->query($sql);
			  if( PEAR::isError($res) ){
					  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$Qtd = $res->fetchRow();
      			if( $Qtd[0] > 0 ){
								$Mens     = 1;
								$Tipo     = 2;
								$Mensagem = "<a href=\"javascript:document.CadAreaLocalizacaoIncluir.Descricao.focus();\" class=\"titulo2\">Descrição já Cadastrada</a>";
						}else{
						$sql  = "SELECT MAX(CARLOCCODI) FROM SFPC.TBAREAALMOXARIFADO ";
				    $res  = $db->query($sql);
			   			if( PEAR::isError($res) ){
					  		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
							}else{
								$Max = $res->fetchRow();
                $Codigo = $Max[0]+1;
              }
								# Inclui na Tabela de Almoxarifado #
			  			  $db->query("BEGIN TRANSACTION");
						    $sql  = "INSERT INTO SFPC.TBAREAALMOXARIFADO( ";
						    $sql .= "CARLOCCODI, CALMPOCODI , CGREMPCODI, CUSUPOCODI, EARLOCDESC, TARLOCULAT";
						    $sql .= ") VALUES ( ";
								$sql .= "$Codigo, '$Almoxarifado', ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].", ";
								$sql .= "'$Descricao', '".date("Y-m-d H:i:s")."')";
						    $res  = $db->query($sql);
								if( PEAR::isError($res) ){
										$db->query("ROLLBACK");
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$Mens         = 1;
										$Tipo         = 1;
										$Mensagem     = "Área Incluída com Sucesso";
										$Amoxarifado	= "";
										$Descricao	  = "";
								}
								$db->query("COMMIT");
								$db->query("END TRANSACTION");
						}
				}
				$db->disconnect();
		}
}
 if( $Botao == "" ){ $NCaracteres = strlen($Descricao); }
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
function remeter(){
	document.CadAreaLocalizacaoIncluir.Grupo.value  = '';
	document.CadAreaLocalizacaoIncluir.Classe.value = '';
	document.CadAreaLocalizacaoIncluir.submit();
}
function enviar(valor){
  document.CadAreaLocalizacaoIncluir.Botao.value = valor;
	document.CadAreaLocalizacaoIncluir.submit();
}
function ncaracteres(valor){
	document.CadAreaLocalizacaoIncluir.NCaracteres.value = '' +  document.CadAreaLocalizacaoIncluir.Descricao.value.length;
	if( navigator.appName == 'Netscape' && valor ) {  //Netscape Only
		document.CadAreaLocalizacaoIncluir.NCaracteres.focus();
	}
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadAreaLocalizacaoIncluir.php" method="post" name="CadAreaLocalizacaoIncluir">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Área > Incluir
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
						<table border="0" cellspacing="0" cellpadding="0" summary="" >
							<tr>
				      	<td class="textonormal">
				        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
				          	<tr>
				            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
					    					INCLUIR - ÁREA
					          	</td>
					        	</tr>
				  	      	<tr>
				    	      	<td class="textonormal">
												<p align="justify">
													Para incluir uma nova Área informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.
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
										              <td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Almoxarifado</td>
										              <td class="textonormal">
									                	<?php
									              		# Mostra o(s) Almoxarifado(s) de Acordo com o Usuário Logado #
																				if( $RowsAlmo == 1 ){
																						$LinhaAlmo = $resAlmo->fetchRow();
										          	      			$Almoxarifado = $LinhaAlmo[0];
									        	   	      			echo "$LinhaAlmo[1]<br>";
									        	   	      			echo "<input type=\"hidden\" name=\"Almoxarifado\" value=\"$Almoxarifado\">";
									        	   	      			echo $DescAlmoxarifado;
													            	}elseif( $RowsAlmo > 1 ){
																						$DescGrupoAntes       = "";
																						echo "<select name=\"Almoxarifado\" class=\"textonormal\">\n";
													                  echo "	<option value=\"\">Selecione um Almoxarifado...</option>\n";
																						for( $i=0;$i< $RowsAlmo; $i++ ){
																								$LinhaAlmo = $resAlmo->fetchRow();
																								$DescAlmoxarifado = $LinhaAlmo[1];
																								$Orgao            = $LinhaAlmo[2];
																		            $DescGrupo        = $LinhaAlmo[3];
												          	      			if( $_SESSION['_cgrempcodi_'] == 0 ){
												          	   	      			if( $DescGrupoAntes != $DescGrupo ){
														          	   	      			echo"<option value=\"\">$DescGrupo</option>\n";
														          	   	      	}
														          	   	    }
										          	   	      			if( $LinhaAlmo[0] == $Almoxarifado ){
										          	   	      					echo"<option value=\"$LinhaAlmo[0]\" selected>$DescAlmoxarifado</option>\n";
												          	      			}else{
												          	      					echo"<option value=\"$LinhaAlmo[0]\">$DescAlmoxarifado</option>\n";
												          	      			}
												          	      			$DescGrupoAntes = $DescGrupo ;
													                	}
													                	echo "</select>\n";
											              }else{
											            			echo "ALMOXARIFADO NÃO CADASTRADO OU INATIVO";
											            	}
									    	            ?>
										              </td>
										            </tr>
										            <tr>
	        	      								<td class="textonormal" bgcolor="#DCEDF7">Descrição*</td>
	          	    								<td class="textonormal">
	          	    								 <input type="text" name="Descricao" value="<?php echo $Descricao; ?>" size="45" maxlength="60" class="textonormal">
	            	  								 <input type="hidden" name="Critica" value="1">
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
