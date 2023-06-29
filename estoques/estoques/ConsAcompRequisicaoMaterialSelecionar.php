<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsAcompRequisicaoMaterialSelecionar.php
# Objetivo: Programa que Seleciona a Requisição de Material
# Autor:    Roberta Costa
# Data:     09/08/2005
# Alterado: Marcus Thiago
# Data:     12/01/2006
# Alterado: Álvaro Faria
# Data:     10/03/2006
# Alterado: Carlos Abreu
# Data:     04/06/2007 - Filtro no combo do almoxarifado para que quando usuario for do tipo
#                        atendimento apareça apenas o almox. que ele esteja relacionado
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/CadItemDetalhe.php' );
AddMenuAcesso( '/estoques/ConsAcompRequisicaoMaterial.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
		$Botao        = $_POST['Botao'];
		$Situacao     = $_POST['Situacao'];
		$Todas        = $_POST['Todas'];
		$DataIni      = $_POST['DataIni'];
		if( $DataIni != "" ){ $DataIni = FormataData($DataIni); }
		$DataFim      = $_POST['DataFim'];
		if( $DataFim != "" ){ $DataFim = FormataData($DataFim); }
		$Programa	    = $_POST['Programa'];
		$Almoxarifado = $_POST['Almoxarifado'];
		$CarregaAlmoxarifado = $_POST['CarregaAlmoxarifado'];
}else{
		$Mensagem = urldecode($_GET['Mensagem']);
		$Mens     		= $_GET['Mens'];
		$Tipo     		= $_GET['Tipo'];
		$Programa			= $_GET['Programa'];
		$Situacao 		= $_GET['Situacao'];
		$Todas				= $_GET['Todas'];
		$DataIni			= $_GET['DataIni'];
		$DataFim			= $_GET['DataFim'];
		$Botao				= $_GET['Botao'];
		$Almoxarifado	=	$_GET['Almoxarifado'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Limpar" ){
	  if( $Programa == "A" ){
	  		$Url = "ConsAcompRequisicaoMaterialSelecionar.php?Programa=A";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
			  header("location: ".$Url);
			  exit();
		}else{
	  		header("location: ConsAcompRequisicaoMaterialSelecionar.php");
	  		exit();
	  }
	  exit;
}elseif( $Botao == "Pesquisar" ){
		# Critica dos Campos #
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
				$Mensagem .= "<a href=\"javascript:document.ConsAcompRequisicaoMaterialSelecionar.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		}
		$MensErro = ValidaPeriodo($DataIni,$DataFim,$Mens,"ConsAcompRequisicaoMaterialSelecionar");
		if( $MensErro != "" ){ $Mensagem .= $MensErro; $Mens = 1; $Tipo = 2; }
}
?>
<html>
<?
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.ConsAcompRequisicaoMaterialSelecionar.Botao.value = valor;
	document.ConsAcompRequisicaoMaterialSelecionar.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="ConsAcompRequisicaoMaterialSelecionar.php" method="post" name="ConsAcompRequisicaoMaterialSelecionar">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Requisição > Acompanhamento
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
	  <td width="150"></td>
	  <td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="150"></td>
		<td class="textonormal">
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
        		 ACOMPANHAMENTO - REQUISIÇÃO DE MATERIAL
          </td>
        </tr>
        <tr>
          <td class="textonormal" colspan="4">
             <p align="justify">
             Para acompanhar uma Requisição de Material cadastrada, informe os campos abaixo, clique no botão "Pesquisar" e clique no número da Requisição desejada.
             </p>
          </td>
        </tr>
        <tr>
          <td colspan="4">
            <table border="0" width="100%" summary="">
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
									    $sql .= "          WHERE USU.CCENPOSEQU = CEN.CCENPOSEQU AND USU.CUSUPOCODI = ". $_SESSION['_cusupocodi_'] ." AND CEN.FCENPOSITU <> 'I' AND USU.FUSUCCTIPO IN ('T','R') ";

									    # restringir almoxarifado quando requisitante
										$sql .= "            AND CASE WHEN USU.FUSUCCTIPO = 'T' THEN B.CALMPOCODI = USU.CALMPOCODI ELSE CEN.FCENPOSITU <> 'I' END";

										$sql .= "       ) ";
                  }
									$sql .= " ORDER BY A.EALMPODESC ";
              		$res  = $db->query($sql);
									if( db::isError($res) ){
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
													echo "<select name=\"Almoxarifado\" class=\"textonormal\">\n";
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
				              }else{
				            			echo "ALMOXARIFADO NÃO CADASTRADO OU INATIVO";
		  	          	   	  echo "<input type=\"hidden\" name=\"CarregaAlmoxarifado\" value=\"N\">";
				            	}
		              }
           			 	$db->disconnect();
    	            ?>
	              </td>
	            </tr>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Situação*</td>
                <td class="textonormal">
                  <select name="Situacao" class="textonormal">
                  	<option value="">Selecione uma Situação...</option>
                  	<?
                  	# Mostra os órgãos cadastrados #
                		$db     = Conexao();
                		$sql    = "SELECT CTIPSRCODI, ETIPSRDESC FROM SFPC.TBTIPOSITUACAOREQUISICAO ";
                		$sql   .= " WHERE CTIPSRCODI <> 2 ORDER BY ETIPSRDESC ";
                		$result = $db->query($sql);
                		if( db::isError($result) ){
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												while( $Linha = $result->fetchRow() ){
		          	      			if( $Situacao == $Linha[0] ){
		          	      					echo"<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
		          	      			}else{
		          	      					echo"<option value=\"$Linha[0]\">$Linha[1]</option>\n";
		          	      			}
			                	}
			              }
  	              	$db->disconnect();
      	            ?>
                  </select>
							    <input type="checkbox" <?php if($Todas == S){ echo "checked"; } ?> name="Todas" value="S" onClick="javascript:enviar('Pesquisar');">Todas
                </td>
              </tr>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Período*</td>
                <td class="textonormal">
									<?php
    	      			$DataMes = DataMes();
    	      			if( $DataIni == "" ){ $DataIni = $DataMes[0]; }
									if( $DataFim == "" ){ $DataFim = $DataMes[1]; }
									$URLIni = "../calendario.php?Formulario=ConsAcompRequisicaoMaterialSelecionar&Campo=DataIni";
									$URLFim = "../calendario.php?Formulario=ConsAcompRequisicaoMaterialSelecionar&Campo=DataFim";
									?>
									<input type="text" name="DataIni" size="10" maxlength="10" value="<?php echo $DataIni;?>" class="textonormal">
									<a href="javascript:janela('<?php echo $URLIni ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
									&nbsp;a&nbsp;
									<input type="text" name="DataFim" size="10" maxlength="10" value="<?php echo $DataFim;?>" class="textonormal">
									<a href="javascript:janela('<?php echo $URLFim ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right" colspan="4">
   	      	<input type="hidden" name="Programa" value="<?php echo $Programa; ?>">
   	      	<input type="button" name="Pesquisar" value="Pesquisar" class="botao" onClick="javascript:enviar('Pesquisar')">
   	      	<input type="button" name="Limpar" value="Limpar" class="botao" onClick="javascript:enviar('Limpar')">
   	      	<input type="hidden" name="Botao" value="">
          </td>
        </tr>
				<?php
				if( $Botao == "Pesquisar" and $Mens == 0 ){
						# Busca os Dados da Tabela de Requisição de Material de Acordo com o Argumento da Pesquisa #
						$db	  = Conexao();
						$sql  = "SELECT A.CREQMASEQU, A.AREQMAANOR, A.CREQMACODI, A.FREQMATIPO, ";
						$sql .= "       A.DREQMADATA, C.CTIPSRCODI, C.ETIPSRDESC, D.ECENPODESC, ";
						$sql .= "       E.EORGLIDESC, D.ECENPODETA ";
						$sql .= "  FROM SFPC.TBREQUISICAOMATERIAL A, SFPC.TBSITUACAOREQUISICAO B, SFPC.TBTIPOSITUACAOREQUISICAO C, ";
						$sql .= "       SFPC.TBCENTROCUSTOPORTAL D, SFPC.TBORGAOLICITANTE E ";
						$sql .= " WHERE A.CREQMASEQU = B.CREQMASEQU AND B.CTIPSRCODI = C.CTIPSRCODI ";
						$sql .= "   AND A.CORGLICODI = D.CORGLICODI AND D.CORGLICODI = E.CORGLICODI ";
						$sql .= "   AND A.CCENPOSEQU = D.CCENPOSEQU ";
						$sql .= "   AND A.CALMPOCODI = $Almoxarifado ";
						$sql .= "   AND A.FREQMATIPO = 'R' ";
	   				$sql .= "   AND B.TSITREULAT IN ";
	   				$sql .= "       (SELECT MAX(TSITREULAT) FROM SFPC.TBSITUACAOREQUISICAO SIT";
						$sql .= "           WHERE SIT.CREQMASEQU = A.CREQMASEQU) ";
						if( $Situacao != "" and $Todas == "" ){
							  # Verifica se é ANÁLISE ou BAIXADO #
							  if( $Situacao == 1 ){
 			   				   $sql .= "AND B.CTIPSRCODI = $Situacao AND B.CREQMASEQU NOT IN(SELECT CREQMASEQU FROM SFPC.TBSITUACAOREQUISICAO WHERE CTIPSRCODI NOT IN(1,5)) ";
 			   				}else{
 			   				   $sql .= "AND B.CTIPSRCODI = $Situacao ";
 			   			  }
					  }
						if( $DataIni != "" and $DataFim != "" ){
			   				$sql .= "AND A.DREQMADATA >= '".DataInvertida($DataIni)."' AND A.DREQMADATA <= '".DataInvertida($DataFim)."' ";
						}
						# Verifica se o perfil do usuário é requisitante, se for só exibe o centro de custo dele
						if( $_SESSION['_cperficodi_'] == "14"){
								$usuario = $_SESSION['_cusupocodi_'];
								$ccUsuario = "SELECT A.CCENPOSEQU FROM SFPC.TBUSUARIOCENTROCUSTO A	WHERE A.CUSUPOCODI = $usuario AND A.FUSUCCTIPO IN ('T','R')";
								$ccResposta  = $db->query($ccUsuario);
								if( db::isError($ccResposta) ){
				    				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $ccUsuario");
								} else {
										$NumRows = $ccResposta->numRows();

										for( $i=0;$i< $NumRows; $i++ ){

											$Resposta	= $ccResposta->fetchRow();
											foreach($Resposta as $Varios){
												if ($Exec == ""){
													$Exec .= "$Varios";
												}else{
													$Exec .= "','$Varios";
												}
											}

										}
			   						$sql .= "AND A.CCENPOSEQU IN ('$Exec') ";
			   				}
						}
			 			$sql .= " ORDER BY E.EORGLIDESC, D.ECENPODESC, A.AREQMAANOR, A.CREQMASEQU DESC ";
			 			$res  = $db->query($sql);
						if( db::isError($res) ){
				    		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$Qtd = $res->numRows();
								echo "<tr>\n";
								echo "	<td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"4\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
								echo "</tr>\n";
								if( $Qtd > 0 ){
										$DescOrgaoAntes  = "";
										$DescCentroAntes = "";

										while( $Linha	= $res->fetchRow() ){
												$Sequencial	   = $Linha[0];
												$AnoRequisicao = $Linha[1];
												$Requisicao 	 = $Linha[2];
												$Data          = DataBarra($Linha[4]);
												$TipoSituacao  = $Linha[5];
												$DescSituacao  = $Linha[6];
												$DescCentro    = $Linha[7];
												$DescOrgao     = $Linha[8];
												$Detalhamento  = $Linha[9];
												if( $DescOrgaoAntes != $DescOrgao ){
														echo "<tr>\n";
														echo "	<td align=\"center\" bgcolor=\"#BFDAF2\" colspan=\"4\" class=\"titulo3\">$DescOrgao</td>\n";
														echo "</tr>\n";
												}
												if( $DescCentroAntes != $DescCentro ){
														echo "<tr>\n";
														echo "	<td align=\"center\" bgcolor=\"#DDECF9\" colspan=\"4\" class=\"titulo3\">$DescCentro</td>\n";
														echo "</tr>\n";
														echo "<tr>\n";
														echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\">REQUISIÇÃO</td>\n";
														echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\">DETALHAMENTO</td>\n";
														echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\">DATA</td>\n";
														echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\">SITUAÇÃO</td>\n";
														echo "</tr>\n";
												}
												echo "<tr>\n";
												echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">";
												$Url = "ConsAcompRequisicaoMaterial.php?Sequencial=$Sequencial&AnoRequisicao=$AnoRequisicao&Almoxarifado=$Almoxarifado&Situacao=$Situacao&Todas=$Todas&DataIni=$DataIni&DataFim=$DataFim";
												if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
												echo "		<a href=\"$Url\"><font color=\"#000000\">".substr($Requisicao+100000,1)."/$AnoRequisicao</font></a>";
												echo "	</td>\n";
												echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Detalhamento</td>\n";
												echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Data</td>\n";
												echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$DescSituacao</td>\n";
												echo "</tr>\n";
												$DescOrgaoAntes  = $DescOrgao;
												$DescCentroAntes = $DescCentro;
										}
								}else{
										echo "<tr>\n";
										echo "	<td valign=\"top\" colspan=\"4\" class=\"textonormal\" bgcolor=\"FFFFFF\">\n";
										echo "	Pesquisa sem Ocorrências.\n";
										echo "	</td>\n";
										echo "</tr>\n";
								}
								echo "</table>\n";
						}
						$db->disconnect();
				}
				?>
      </table>
		</td>
	</tr>
	<!-- Fim do Corpo -->
</table>
</form>
</body>
</html>
