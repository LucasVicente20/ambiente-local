<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadLocalizacaoSelecionar.php
# Autor:    Franklin Alves
# Data:     06/07/05
# Alterado:	Marcus Thiago
# Data:			04/01/2006
# Objetivo: Programa de Seleção de Localização
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/CadLocalizacaoAlterar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Almoxarifado	= $_POST['Almoxarifado'];
		$CarregaAlmoxarifado = $_POST['CarregaAlmoxarifado'];
		$Area				  = $_POST['Area'];
		$Localizacao  = $_POST['Localizacao'];
		$Botao        = $_POST['Botao'];
}else{
		$Botao        = $_GET['Botao'];
		$Mensagem     = urldecode($_GET['Mensagem']);
		$Mens         = $_GET['Mens'];
		$Tipo         = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Critica dos Campos #
if ($Botao == ""){
		$db     = Conexao();
		$sql    = " SELECT A.CUSUPOCODI, A.CCENPOSEQU, ";
		$sql   .= "				 B.CCENPOSEQU, B.ECENPODESC, B.CORGLICODI, ";
		$sql   .= "				 C.CORGLICODI, C.CALMPOCODI, ";
		$sql   .=	"				 D.CALMPOCODI, D.EALMPODESC, D.FALMPOTIPO ";
		$sql   .=	"   FROM SFPC.TBUSUARIOCENTROCUSTO A, ";
		$sql   .=	"			   SFPC.TBCENTROCUSTOPORTAL B, ";
		$sql   .=	"			   SFPC.TBALMOXARIFADOORGAO C, ";
		$sql   .=	"			   SFPC.TBALMOXARIFADOPORTAL D ";
		$sql   .=	"  WHERE A.CCENPOSEQU = B.CCENPOSEQU AND A.FUSUCCTIPO IN ('T','R') ";
		$sql   .=	"		 AND B.CORGLICODI = C.CORGLICODI ";
		$sql   .=	"		 AND C.CALMPOCODI = D.CALMPOCODI ";
		$sql   .=	"		 AND A.CUSUPOCODI = ".$_SESSION['_cusupocodi_']."";
		$sql	 .= "    AND B.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$Aux = $Linha[0];
			  }
   }
   if( $Aux == "" ){
				$Mens     = 1;
				$Tipo     = 2;
				$Mensagem = "Almoxarifado não cadastrado para o Centro de Custo/Orgão deste Usuário";
	 }
}
if( $Botao == "Selecionar" ) {
 	  $LocalizacaoSplit  = explode("_",$Localizacao);
 	  $TipoEquipamento	 = $LocalizacaoSplit[0];
 	  $NumeroEquipamento = $LocalizacaoSplit[1];
 	  //echo "NUMERO EQUI: ".$Localizacao;
    if( ($Almoxarifado == "") && ($CarregaAlmoxarifado == 'N') ){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Almoxarifado";
		} elseif ($Almoxarifado == "") {
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadLocalizacaoSelecionar.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		}
    if( $Area == "" ) {
	      if( $Mens == 1 ){ $Mensagem .= ", "; }
	      $Mens      = 1;
	      $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript: document.CadLocalizacaoSelecionar.Area.focus();\" class=\"titulo2\">Área</a>";
    }
    if( $Localizacao == "" ) {
	      if( $Mens == 1 ){ $Mensagem .= ", "; }
	      $Mens      = 1;
	      $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript: document.CadLocalizacaoSelecionar.Localizacao.focus();\" class=\"titulo2\">Localização</a>";
    }else{
				if( $Mens == 0 ){
						$Url = "CadLocalizacaoAlterar.php?TipoEquipamento=$TipoEquipamento&Almoxarifado=$Almoxarifado&Area=$Area&NumeroEquipamento=$NumeroEquipamento";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
			  		header("location: ".$Url);
	      		exit;
	      }
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
	document.CadLocalizacaoSelecionar.Botao.value = valor;
	document.CadLocalizacaoSelecionar.submit();
}
function remeter(){
	document.CadLocalizacaoSelecionar.submit();
}

<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadLocalizacaoSelecionar.php" method="post" name="CadLocalizacaoSelecionar">
<br><br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Localização > Manter
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
		<td width="100"></td>
		<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
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
									MANTER - LOCALIZAÇÃO
								</td>
							</tr>
							<tr>
								<td class="textonormal">
									<p align="justify">
										Para atualizar/excluir uma Localização já cadastrada, Selecione o Almoxarifado, Área e Localização desejados e clique no botão "Selecionar".
									</p>
								</td>
							</tr>
							<tr>
								<td>
									<table class="textonormal" border="0" align="left" class="caixa">
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
														$sql .= "   AND B.CORGLICODI in ";
											    	$sql .= "       ( SELECT DISTINCT CEN.CORGLICODI ";
											    	$sql .= "           FROM SFPC.TBCENTROCUSTOPORTAL CEN, SFPC.TBUSUARIOCENTROCUSTO USU ";
												    $sql .= "          WHERE USU.CCENPOSEQU = CEN.CCENPOSEQU AND USU.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." AND CEN.FCENPOSITU <> 'I' AND USU.FUSUCCTIPO IN ('T','R')) ";
			                  }
												$sql .= " ORDER BY A.EALMPODESC ";
			              		$res  = $db->query($sql);
												if( PEAR::isError($res) ){
												    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
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
					            <td class="textonormal" bgcolor="#DCEDF7">Área*</td>
					            <td class="textonormal">
					              <select name="Area" OnChange="javascript:remeter();" class="textonormal">
					    	           <option value="">Selecione uma Área...</option>
					              	<!-- Mostra as Áreas cadastradas -->
					                <?php
					                  if($Almoxarifado != ""){
							                  $db     = Conexao();
								            		$sql    = "SELECT CARLOCCODI, CALMPOCODI, EARLOCDESC ";
																$sql   .= "  FROM SFPC.TBAREAALMOXARIFADO ";
																$sql   .= " WHERE CALMPOCODI = $Almoxarifado ORDER BY EARLOCDESC";
															  $result = $db->query($sql);
								            		if( PEAR::isError($result) ){
																    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																}else{
																		while( $Linha = $result->fetchRow() ){
								          	      		 if( $Linha[0] == $Area ){
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
											<td class="textonormal" bgcolor="#DCEDF7">Localização*</td>
											<td class="textonormal">
		  				  	      <select name="Localizacao" class="textonormal">
													<option value="">Selecione uma Localização...</option>
													<?php
													if($Area){
															$db     = Conexao();
															$sql    = "SELECT DISTINCT FLOCMAEQUI, ALOCMANEQU, CARLOCCODI  ";
															$sql   .= "  FROM SFPC.TBLOCALIZACAOMATERIAL ";
															$sql   .= " WHERE CARLOCCODI = $Area ORDER BY FLOCMAEQUI, ALOCMANEQU ";
															$result = $db->query($sql);
															if( PEAR::isError($result) ){
															    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
															}else{
																	while( $Linha = $result->fetchRow() ){
																			if ($Linha[0] == 'E'){
																				$Equipamento = "ESTANTE";
																			}else
																			if ($Linha[0] == 'P'){
																				$Equipamento = "PALETE";
																			}else
																			if ($Linha[0] == 'A'){
																				$Equipamento = "ARMÁRIO";
																			}
																		  //echo "<option value=\"$Linha[0]_$Linha[1]\">$Equipamento - $Linha[1]</option>\n";
																		  echo "<option value=\"$Linha[0]_$Linha[1]\">$Equipamento - $Linha[1]</option>\n";
																	}
															}
															$db->disconnect();
														  }
												  ?>
												  </option>
											    </select>
											  </td>
	  		               </tr>
									</table>
							  </td>
						  </tr>
							<tr>
			 	        <td class="textonormal" align="right">
			          	<input type="button" name="Selecionar" value="Selecionar" class="botao" onClick="javascript:enviar('Selecionar');">
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
