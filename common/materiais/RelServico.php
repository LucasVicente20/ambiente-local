<?php
#-----------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelServiço.php
# Autor:    Marcos Túlio
# Data:     07/02/2012
# Objetivo: Programa de Impressão dos Relatórios de Serviço
#-----------------------------------------------------------------------------
# Alterado: João Batista Brito
# Data:     13/06/12
# Objetivo: Correção dos erros - Demanda Redmine: #11015
#-----------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     09/07/2018
# Objetivo: Tarefa Redmine 76718
#-----------------------------------------------------------------------------
# Alterado: Lucas Vicente
# Data: 28/12/2022
# Objetivo: CR 235027
#------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança#
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/materiais/RelServicoPdf.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
	$Botao                     = $_POST['Botao'];
	$GrupoEmitir               = $_POST['GrupoEmitir'];
	$ClasseEmitir              = $_POST['ClasseEmitir'];
	$GrupoCodigo               = $_POST['GrupoCodigo'];
	$GrupoDescricao            = $_POST['GrupoDescricao'];
	$ClasseCodigo              = $_POST['ClasseCodigo'];
	$Classe                    = $_POST['Classe'];
	$ClasseDireta              = $_POST['ClasseDireta'];
	$CheckDireta               = $_POST['CheckDireta'];
	$CheckClasse               = $_POST['CheckClasse'];
	$SituacaoInativos		   = $_POST['SituacaoInativos'];
	$ClasseDescricaoFamilia    = strtoupper2(trim($_POST['ClasseDescricaoFamilia']));
	$OpcaoPesquisaClasse       = $_POST['OpcaoPesquisaClasse'];
	$ClasseDescricaoDireta     = strtoupper2(trim($_POST['ClasseDescricaoDireta']));
		 
}else{
		$Tipo     = $_GET['Tipo'];
		$Mens     = $_GET['Mens'];
		$Mensagem = $_GET['Mensagem'];
}
# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Monta o sql para montagem dinâmica da grade a partir da pesquisa #
$sqlgeral = "
SELECT DISTINCT
	GRU.CGRUMSCODI, GRU.EGRUMSDESC, CLA.CCLAMSCODI, CLA.ECLAMSDESC
FROM
	SFPC.TBGRUPOMATERIALSERVICO GRU, SFPC.TBCLASSEMATERIALSERVICO CLA
WHERE
	CLA.CGRUMSCODI = GRU.CGRUMSCODI
	AND GRU.FGRUMSSITU = 'A'
	AND CLA.FCLAMSSITU = 'A'
	AND GRU.FGRUMSTIPO = 'S'
";

# Verifica se o GrupoCodigo foi escolhido #
if( $GrupoCodigo != "" and $ClasseDescricaoDireta == "" ){
  	$sqlgeral .= " AND GRU.CGRUMSCODI = $GrupoCodigo ";
}

# Verifica se a Classe foi escolhida #
if( $Classe != "" and $ClasseDescricaoDireta == "" ){
  	$sqlgeral .= " AND CLA.CGRUMSCODI = $GrupoCodigo AND CLA.CCLAMSCODI = $Classe ";
}

# Se foi digitado algo na caixa de texto da subclasse em pesquisa familia #
if( $ClasseDescricaoFamilia != "" and $ClasseDescricaoDireta == "" ){
	$sqlgeral .= " AND ( ";
	//$sqlgeral .= "    TRANSLATE(CLA.ECLAMSDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($ClasseDescricaoFamilia))."%' OR ";
	$sqlgeral .= "      TRANSLATE(CLA.ECLAMSDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '%".strtoupper2(RetiraAcentos($ClasseDescricaoFamilia))."%' ";
	$sqlgeral .= "     )";
}

# Se foi digitado algo na caixa de texto da classe em pesquisa direta #
if( $ClasseDescricaoDireta != "" ) {
	if( $OpcaoPesquisaClasse == 0 ){
		if( SoNumeros($ClasseDescricaoDireta) ){
	    	$sqlgeral .= " AND CLA.CCLAMSCODI = $ClasseDescricaoDireta ";
	    }
	}elseif($OpcaoPesquisaClasse == 1){
		$sqlgeral .= " AND ( ";
		//$sqlgeral .= "    TRANSLATE(CLA.ECLAMSDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($ClasseDescricaoDireta))."%' OR ";
		$sqlgeral .= "      TRANSLATE(CLA.ECLAMSDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '%".strtoupper2(RetiraAcentos($ClasseDescricaoDireta))."%' ";
		$sqlgeral .= "     )";
	}else{
		$sqlgeral .= " AND TRANSLATE(CLA.ECLAMSDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($ClasseDescricaoDireta))."%' ";
	}
}

if ($SituacaoInativos == 'I'){
	$db = Conexao();
	$sqlInativos = "SELECT distinct (GRU.CGRUMSCODI), GRU.EGRUMSDESC, CLA.CCLAMSCODI, CLA.ECLAMSDESC, SERV.cservpsequ
					FROM SFPC.TBGRUPOMATERIALSERVICO GRU, SFPC.TBCLASSEMATERIALSERVICO CLA, SFPC.TBSERVICOPORTAL SERV 
					WHERE CLA.CGRUMSCODI = GRU.CGRUMSCODI 
					and SERV.cgrumscodi = GRU.cgrumscodi 
					AND GRU.FGRUMSTIPO = 'S' 
					ORDER BY GRU.EGRUMSDESC, CLA.ECLAMSDESC";
	$resultInativos = $db->query($sqlInativos);
}

$sqlgeral .= " ORDER BY GRU.EGRUMSDESC, CLA.ECLAMSDESC";
 
if( $Botao == "Limpar" ){
		header("location: RelServico.php");
		exit;
}elseif( $Botao == "Validar" ){
		# Critica dos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";
		$TipoServico = "";
    if( $ClasseDescricaoDireta != "" and $OpcaoPesquisaClasse == 0 and ! SoNumeros($ClasseDescricaoDireta) ){
   	  if( $Mens == 1 ){ $Mensagem .= ", "; }
       	  $Mens      = 1;
      	  $Tipo      = 2;
      	  $Mensagem .= "<a href=\"javascript:document.RelServico.ClasseDescricaoDireta.focus();\" class=\"titulo2\">Código reduzido da Classe</a>";
    }elseif($ClasseDescricaoDireta != "" and ($OpcaoPesquisaClasse == 1 or $OpcaoPesquisaClasse == 2) and strlen($ClasseDescricaoDireta)< 2){
	  	if($Mens == 1){ $Mensagem .= ", "; }
		   $Mens = 1;
		   $Tipo = 2;
		   $Mensagem .= "<a href=\"javascript:document.RelServico.ClasseDescricaoDireta.focus();\" class=\"titulo2\">Descrição com no mínimo 2 caracteres</a>";
	}
}else if( $Botao == "Emitir" or $Botao == "ImprimirFamilia" ){
		if( $Classe == "" ){ $Classe = $ClasseDireta; }
		$Url = "RelServicoPdf.php?Grupo=$GrupoEmitir&Classe=$ClasseEmitir&Tipo=Familia";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit;
}else if( $Botao == "ImprimirServico" ){
		$Url = "RelServicoPdf.php?Tipo=Item";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit;
}else if( $Botao == "ImprimirServicoInativo" ){
		$Url = "RelServicoInativoPdf.php?Tipo=Item";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit;
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
function checktodos(){
	document.RelServico.Classe.value = '';
	document.RelServico.ClasseDescricaoFamilia.value = '';
	document.RelServico.submit();
}
function enviar(valor){
	document.RelServico.Botao.value = valor;
	document.RelServico.submit();
}
function emitir(classe, grupo){
	if( document.RelServico.Classe ){
		document.RelServico.Classe.value=classe;
	}else{
		document.RelServico.ClasseDireta.value=classe;
	}
  	document.RelServico.GrupoEmitir.value = grupo;
  	document.RelServico.ClasseEmitir.value = classe;
	enviar('Emitir');
}
function validapesquisa(){
	if( document.RelServico.ClasseDescricaoDireta.value != "" ){
		if( document.RelServico.GrupoCodigo ){
     	document.RelServico.GrupoCodigo.value = '';
  	}
		if( document.RelServico.Classe ){
  		document.RelServico.Classe.value = '';
  	}
  		document.RelServico.Botao.value = "Validar";
	}
	if( document.RelServico.Classe ){
	  if(document.RelServico.ClasseDescricaoFamilia.value != "" ){
		 document.RelServico.Classe.value = 0;
    }
  }
  	document.RelServico.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RelServico.php" method="post" name="RelServico">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais > Relatórios > Serviço
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
				<table border="0" cellspacing="0" cellpadding="3" summary="">
					<tr>
		      	<td class="textonormal">
		        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
		          	<tr>
		            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
			    					RELATÓRIO DE SERVIÇO
			          	</td>
			        	</tr>
		  	      	<tr>
		    	      	<td class="textonormal" colspan="4">
										<p align="justify">
											Para pesquisar um item já cadastrado, preencha o argumento da pesquisa e clique no botão "Pesquisar".
											Depois, clique na subclasse desejada.<br><br>
								        	Se você não possui o Acrobat Reader, clique <a href="javascript:janela('../pdf.php','Relatorio',400,400,1,0)" class="titulo2">AQUI</a> para fazer o download.
		          	   	</p>
		          		</td>
			        	</tr>
				        <tr>
        				  <td align="center" bgcolor="#DCEDF7" class="titulo3" colspan="4">PESQUISA DIRETA</td>
        				</tr>
        				<tr>
          				<td colspan="4">
            				<table border="0" width="100%" summary="">
	            				<tr>
	              				<td class="textonormal" bgcolor="#DCEDF7" width="5%">Serviço:</td>
	              				<td class="textonormal" colspan="2">
	              					<select name="OpcaoPesquisaClasse" class="textonormal">
	              						<option value="0" selected>Código Reduzido</option>
	              						<option value="1">Descrição contendo</option>
	              						<option value="2">Descrição iniciada por</option>
	              					</select>
         	        			<input type="text" name="ClasseDescricaoDireta" size="10" maxlength="10" class="textonormal">
								  <a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0"></a>
								  <input type="checkbox" name="CheckDireta" onClick="javascript:enviar('');" value="TD" >Todas
								  <input type="hidden" name="ClasseDireta" value="">
								  <input type="hidden" name="ClasseEmitir" value="">
								  <input type="hidden" name="GrupoEmitir" value="">
	              				</td>
	              			</tr>
							
            				</table>
          				</td>
        				</tr>
				        <tr>
        				  <td align="center" bgcolor="#DCEDF7" class="titulo3" colspan="4">PESQUISA POR FAMILIA</td>
        				</tr>
			        	<tr>
									<td colspan="4">
										<table border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" width="100%" summary="">
											<tr>
												<td colspan="4">
							      	    <table class="textonormal" border="0" width="85%" summary="">
						                <tr>
							              	<td class="textonormal" bgcolor="#DCEDF7" height="20">Grupo:</td>
							              	<td class="textonormal">
							              	<select name="GrupoCodigo" onChange="javascript:enviar('');" class="textonormal">
              	              		        <option value="">Selecione um Grupo...</option>
							              	    <?php
							              			$db = Conexao();
																	
												$sql=" 
													SELECT distinct
														gru.CGRUMSCODI, gru.EGRUMSDESC 
													FROM 
														SFPC.TBGRUPOMATERIALSERVICO gru, SFPC.TBSERVICOPORTAL SERV
                        	WHERE
                        		gru.FGRUMSSITU = 'A' 
                        		AND gru.FGRUMSTIPO = 'S'
                        		and gru.cGRUMScodi = serv.cGRUMScodi
                        	ORDER BY gru.EGRUMSDESC 
                       	";
												$result = $db->query($sql);
												if( PEAR::isError($result) ){
													EmailErroDB("Falha de SQL", "Falha ao executar o SQL", $res);										
												}
												else{
													while($Linha = $result->fetchRow()){
															$Descricao   = substr($Linha[1],0,75);
															if( $Linha[0] == $GrupoCodigo ){
																	echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
															}else{
																	echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
															}
													}
												}
					              	        
							              	    ?>
							              	  </select>
							              	</td>
							            </tr>
							              <?php if( $GrupoCodigo != "" ){ ?>
										  <tr>
								              <td class="textonormal" bgcolor="#DCEDF7">Classe </td>
            	  							  <td class="textonormal">
								              	<select name="Classe" class="textonormal" onChange="javascript:enviar('');">
              										<option value="">Selecione uma Classe...</option>
								              		   <?php
          												
																			$sql  = "
																				SELECT distinct cla.CCLAMSCODI, cla.ECLAMSDESC
																				FROM
																					SFPC.TBCLASSEMATERIALSERVICO cla, SFPC.TBSERVICOPORTAL SERV
																				WHERE
																					cla.CGRUMSCODI = $GrupoCodigo
																					AND cla.FCLAMSSITU = 'A' 
																					and serv.CGRUMSCODI = cla.CGRUMSCODI
																				ORDER BY cla.ECLAMSDESC
																			";
																			$res  = $db->query($sql);
																		  if( PEAR::isError($res) ){
																				EmailErroDB("Falha de SQL", "Falha ao executar o SQL", $res);																											  	
																			}else{
																				while( $Linha = $res->fetchRow() ){
																					$Descricao = substr($Linha[1],0,75);
																					if( $Linha[0] == $Classe){
																									echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
																						}else{
																									echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
																						}
			            								    		            }
																			}
														?>
              									</select>
													<input type="text" name="ClasseDescricaoFamilia" size="10" maxlength="10" class="textonormal">
													  <a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0"></a>
													  <input type="checkbox" name="CheckClasse" onClick="javascript:checktodos();" value="TF" >Todas
												</td>
											 </tr>
											 <?php  }?>
                                            </table>
												</td>
											</tr>
										</table>
									</td>
								</tr>
             		<tr>
             			<td align="right" colspan="4">
             				<?php if( $CheckClasse == "TF" or $CheckDireta == "TD" ){ ?>
								<tr>
									<td>
										<input type="checkbox" name="SituacaoInativos" onClick="javascript:enviar('');" value="I">Mostrar serviços inativos
									</td>
								</tr>
							<td>
								<input  type="button" name="Imprimir por Material" value="Imprimir por Serviço" class="botao" onclick="javascript:enviar('ImprimirServico');">
								<input  type="button" name="Imprimir por Família" value="Imprimir por Familia" class="botao" onclick="javascript:enviar('ImprimirFamilia');">             				
								<?php } ?>
								<?php if ($SituacaoInativos =="I"){ ?>
									<input  type="button" name="Imprimir por Servico" value="Imprimir por Servico" class="botao" onclick="javascript:enviar('ImprimirServicoInativo');">
								<?php } ?>
								<input  type="button" name="Limpar" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
											<input type="hidden" name="Botao" value="">
             				</td>
						</td>
             		</tr>
						    <?php
								if( $ClasseDescricaoDireta != "" ){
										if( $OpcaoPesquisaClasse == 0 ){
												if( !SoNumeros($ClasseDescricaoDireta) ){ $sqlgeral = ""; }
										}
								}
								if( $Mens == 0 and $sqlgeral != "" and ( $ClasseDescricaoDireta != "" or
								 		$Classe != "" or $ClasseDescricaoFamilia != "" or $CheckDireta == "TD" or $CheckClasse == "TF" or $SituacaoInativos == 'I' ) ){
										// var_dump($sqlgeral);die;	
										$db     = Conexao();
										$res    = $db->query($sqlgeral);
                    if( PEAR::isError($res) ){
													EmailErroDB("Falha de SQL", "Falha ao executar o SQL", $res);										
 										}
										
										else{
										    $qtdres = $res->numRows();
													echo "<tr>\n";
													echo "  <td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"4\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
													echo "</tr>\n";

											if ($SituacaoInativos == 'I'){
												$GrupoAntes        = "";
												$ClasseAntes       = "";
								                $SequServicoAntes  = "";
												while( $row	= $resultInativos->fetchRow() ){
														$GrupoCodigo        = $row[0];
														$GrupoDescricao     = $row[1];
														$ClasseCodigo       = $row[2];
														$ClasseDescricao    = $row[3];														
														$SequServico 		= $row[4];
														if( $SequServico != $SequServicoAntes ) {
															if( $GrupoAntes != $GrupoCodigo ) {
															
																echo "<tr>\n";
																echo "  <td class=\"textoabason\" bgcolor=\"#BFDAF2\" colspan=\"4\" align=\"center\">";
																echo "  </td>\n";
																echo "</tr>\n";
																echo "<tr>\n";
																echo "  <td class=\"textoabason\" bgcolor=\"#DDECF9\" colspan=\"4\" align=\"center\">$GrupoDescricao / $ClasseDescricao</td>\n";
																echo "</tr>\n";
															
															}
															
															echo "<tr>\n";
															echo "  <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"4\">\n";
															echo "    <a href=\"javascript:emitir($ClasseCodigo, $GrupoCodigo );\"><font color=\"#000000\">$ClasseDescricao</font></a>";
															echo "  </td>\n";
															echo "</tr>";
														}
														$ServicoAntes  = $ServicoCodigo;
														$GrupoAntes    = $GrupoCodigo;
														$ClasseAntes   = $ClasseCodigo;
														$SequServicoAntes = $SequServico;
												}
											}elseif( $qtdres > 0 ){
												$GrupoAntes        = "";
												$ClasseAntes       = "";
								                
												while( $row	= $res->fetchRow() ){
														$GrupoCodigo        = $row[0];
														$GrupoDescricao     = $row[1];
														$ClasseCodigo       = $row[2];
														$ClasseDescricao    = $row[3];
														$ServicoCodigo      = $row[4];
														$ServicoDescricao   = $row[5];														
															
														if( $ClasseAntes != $ClasseCodigo ) {
															if( $GrupoAntes != $GrupoCodigo ) {
															
																echo "<tr>\n";
																echo "  <td class=\"textoabason\" bgcolor=\"#BFDAF2\" colspan=\"4\" align=\"center\">";
																echo "  </td>\n";
																echo "</tr>\n";
																echo "<tr>\n";
																echo "  <td class=\"textoabason\" bgcolor=\"#DDECF9\" colspan=\"4\" align=\"center\">$GrupoDescricao / $ClasseDescricao</td>\n";
																echo "</tr>\n";
															
															}
															
															echo "<tr>\n";
															echo "  <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"4\">\n";
															echo "    <a href=\"javascript:emitir($ClasseCodigo, $GrupoCodigo );\"><font color=\"#000000\">$ClasseDescricao</font></a>";
															echo "  </td>\n";
															echo "</tr>";
														}
														$ServicoAntes  = $ServicoCodigo;
														$GrupoAntes    = $GrupoCodigo;
														$ClasseAntes   = $ClasseCodigo;
														
												}
											}
											else{
														echo "<tr>\n";
														echo "	<td valign=\"top\" colspan=\"4\" class=\"textonormal\" bgcolor=\"FFFFFF\">\n";
														echo "		Pesquisa sem Ocorrências.\n";
														echo "	</td>\n";
														echo "</tr>\n";
											}
									    }
								}
													$db->disconnect();
								
                ?>
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
