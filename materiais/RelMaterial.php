<?php
#-----------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelMaterial.php
# Autor:    Filipe Cavalcanti
# Data:     14/09/2005
# Alterado: Álvaro Faria
# Data:     29/08/2006 - Opção de pesquisa de material com descrição "iniciada por"
# Alterado: Wagner Barros
# Data:     02/10/2006 - Exibir o código reduzido do material ao lado da descrição"
# Alterado: Rossana Lira
# Data:     18/10/2006 - Criar botão "Imprimir p/Família" e "Imprimir p/Material"
#	          com links para relatórios diferentes
# Objetivo: Programa de Impressão dos Relatórios de Materiais
# OBS.:     Tabulação 2 espaços
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
AddMenuAcesso( '/materiais/CadItemDetalhe.php' );
AddMenuAcesso( '/materiais/RelMaterial.php' );
AddMenuAcesso( '/materiais/RelMaterialPdf.php' );
AddMenuAcesso( '/materiais/RelMaterialFamiliaPdf.php' );
AddMenuAcesso( '/materiais/RelMaterialInativoPdf.php' );
AddMenuAcesso( '/materiais/RelMaterialSustentavelPdf.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
		$Botao                     = $_POST['Botao'];
		$TipoMaterial              = $_POST['TipoMaterial'];
		$Grupo                     = $_POST['Grupo'];
		$Classe                    = $_POST['Classe'];
		$Subclasse                 = $_POST['Subclasse'];
		$SubclasseDireta           = $_POST['SubclasseDireta'];
		$CheckDireta               = $_POST['CheckDireta'];
		$CheckSubclasse            = $_POST['CheckSubclasse'];
		$SituacaoInativos		   = $_POST['SituacaoInativos'];
		$SituacaoSustentavel	   = $_POST['SituacaoSustentavel'];
		$SubclasseDescricaoFamilia = strtoupper2(trim($_POST['SubclasseDescricaoFamilia']));
		$OpcaoPesquisaSubClasse    = $_POST['OpcaoPesquisaSubClasse'];
		$SubclasseDescricaoDireta  = strtoupper2(trim($_POST['SubclasseDescricaoDireta']));
		$CodigoReduzido 				   = $_POST['CodigoReduzido'];
}else{
		$Tipo     = $_GET['Tipo'];
		$Mens     = $_GET['Mens'];
		$Mensagem = $_GET['Mensagem'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Monta o sql para montagem dinâmica da grade a partir da pesquisa #
$sql    = "SELECT DISTINCT(GRU.CGRUMSCODI), GRU.EGRUMSDESC, CLA.CCLAMSCODI, CLA.ECLAMSDESC, ";
$sql   .= "       SUB.CSUBCLSEQU, SUB.ESUBCLDESC, MAT.CMATEPSEQU, MAT.EMATEPDESC, ";
$sql   .= "       UND.EUNIDMSIGL, GRU.FGRUMSTIPM, MAT.CMATEPSEQU ";
$from   = "  FROM SFPC.TBMATERIALPORTAL MAT, SFPC.TBGRUPOMATERIALSERVICO GRU, SFPC.TBCLASSEMATERIALSERVICO CLA, ";
$from  .= "       SFPC.TBSUBCLASSEMATERIAL SUB, SFPC.TBUNIDADEDEMEDIDA UND ";
$where  = " WHERE MAT.CSUBCLSEQU = SUB.CSUBCLSEQU AND SUB.CGRUMSCODI = CLA.CGRUMSCODI ";
$where .= "   AND SUB.CCLAMSCODI = CLA.CCLAMSCODI AND CLA.CGRUMSCODI = GRU.CGRUMSCODI ";
$where .= "   AND MAT.CUNIDMCODI = UND.CUNIDMCODI AND MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
$where .= "   AND GRU.FGRUMSSITU = 'A' AND CLA.FCLAMSSITU = 'A' AND SUB.FSUBCLSITU = 'A' ";
$order = " ORDER BY GRU.FGRUMSTIPM, GRU.EGRUMSDESC, CLA.ECLAMSDESC, SUB.ESUBCLDESC, MAT.EMATEPDESC ";

# Verifica se o Tipo de Material foi escolhido #
if( $TipoMaterial != "" and $SubclasseDescricaoDireta == "" ){
  	$where .= " AND GRU.FGRUMSTIPM = '$TipoMaterial' ";
}

# Verifica se o Grupo foi escolhido #
if( $Grupo != "" and $SubclasseDescricaoDireta == "" ){
  	$where .= " AND GRU.CGRUMSCODI = $Grupo ";
}

# Verifica se a Classe foi escolhida #
if( $Classe != "" and $SubclasseDescricaoDireta == "" ){
  	$where .= " AND CLA.CGRUMSCODI = $Grupo AND CLA.CCLAMSCODI = $Classe ";
}

# Verifica se a SubClasse foi escolhida #
if( $Subclasse != "" and $SubclasseDescricaoDireta == "" ){
	  $where .= " AND SUB.CSUBCLSEQU = $Subclasse ";
}

# Se foi digitado algo na caixa de texto da subclasse em pesquisa familia #
if( $SubclasseDescricaoFamilia != "" and $SubclasseDescricaoDireta == "" ){
		$where .= " AND ( ";
		$where .= "      TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($SubclasseDescricaoFamilia))."%' OR ";
		$where .= "      TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '% ".strtoupper2(RetiraAcentos($SubclasseDescricaoFamilia))."%' ";
		$where .= "     )";
}

# Se foi digitado algo na caixa de texto da subclasse em pesquisa direta #
if( $SubclasseDescricaoDireta != "" ) {
		if( $OpcaoPesquisaSubClasse == 0 ){
			  if( SoNumeros($SubclasseDescricaoDireta) ){
	    	  	$where .= " AND SUB.CSUBCLSEQU = $SubclasseDescricaoDireta ";
	    	}
	  }elseif($OpcaoPesquisaSubClasse == 1){
	    	$where .= " AND ( ";
				$where .= "      TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($SubclasseDescricaoDireta))."%' OR ";
				$where .= "      TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '% ".strtoupper2(RetiraAcentos($SubclasseDescricaoDireta))."%' ";
				$where .= "     )";
	  }else{
				$where .= " AND TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($SubclasseDescricaoDireta))."%' ";
		}
}

if($SituacaoInativos == "I"){
	$db = Conexao();
	$sqlInativos = "SELECT GRU.CGRUMSCODI, GRU.EGRUMSDESC, CLA.CCLAMSCODI, CLA.ECLAMSDESC, SUB.CSUBCLSEQU, 
					SUB.ESUBCLDESC, MAT.CMATEPSEQU, MAT.EMATEPDESC, UND.EUNIDMSIGL, GRU.FGRUMSTIPM, MAT.CMATEPSEQU
					FROM SFPC.TBMATERIALPORTAL MAT, SFPC.TBGRUPOMATERIALSERVICO GRU, SFPC.TBCLASSEMATERIALSERVICO CLA, SFPC.TBSUBCLASSEMATERIAL SUB, SFPC.TBUNIDADEDEMEDIDA UND 
					WHERE MAT.CSUBCLSEQU = SUB.CSUBCLSEQU 
					AND SUB.CGRUMSCODI = CLA.CGRUMSCODI
					AND SUB.CCLAMSCODI = CLA.CCLAMSCODI 
					AND CLA.CGRUMSCODI = GRU.CGRUMSCODI 
					AND MAT.CUNIDMCODI = UND.CUNIDMCODI 
					AND MAT.CSUBCLSEQU = SUB.CSUBCLSEQU  
					ORDER BY GRU.FGRUMSTIPM, GRU.EGRUMSDESC, CLA.ECLAMSDESC, SUB.ESUBCLDESC, MAT.EMATEPDESC";
	$resultInativos = $db->query($sqlInativos);
	
}

if($SituacaoSustentavel == 'S'){
	$db = Conexao();
	$sqlSustentavel = "select distinct ematepdesc, csubclsequ
						from sfpc.tbmaterialportal t 
						where fmatepsust = 'S'
						order by ematepdesc";
	$resultSustentavel = $db->query($sqlSustentavel);
}
# Gera o SQL com a concatenação das variaveis $sql,$from,$where #
$sqlgeral = $sql.$from.$where.$order;
			
if( $Botao == "Limpar" ){
		header("location: RelMaterial.php");
		exit;
}elseif( $Botao == "Validar" ){
		# Critica dos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";
		$TipoMaterial = "";
    if( $SubclasseDescricaoDireta != "" and $OpcaoPesquisaSubClasse == 0 and ! SoNumeros($SubclasseDescricaoDireta) ){
    	  if( $Mens == 1 ){ $Mensagem .= ", "; }
      	$Mens      = 1;
      	$Tipo      = 2;
      	$Mensagem .= "<a href=\"javascript:document.RelMaterial.SubclasseDescricaoDireta.focus();\" class=\"titulo2\">Código reduzido da Subclasse</a>";
    }elseif($SubclasseDescricaoDireta != "" and ($OpcaoPesquisaSubClasse == 1 or $OpcaoPesquisaSubClasse == 2) and strlen($SubclasseDescricaoDireta)< 2){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens = 1;
				$Tipo = 2;
				$Mensagem .= "<a href=\"javascript:document.RelMaterial.SubclasseDescricaoDireta.focus();\" class=\"titulo2\">Descrição com no mínimo 2 caracteres</a>";
		}
}else if( $Botao == "Emitir" or $Botao == "ImprimirFamilia" ){
		if( $Subclasse == "" ){ $Subclasse = $SubclasseDireta; }
		$Url = "RelMaterialFamiliaPdf.php?Grupo=$Grupo&Classe=$Classe&Subclasse=$Subclasse&".mktime();
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit;
}else if( $Botao == "ImprimirMaterial" ){
	$Url = "RelMaterialPdf.php?".mktime();
	if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	header("location: ".$Url);
	exit;
}else if( $Botao == "ImprimirMaterialInativo" ){
	$Url = "RelMaterialInativoPdf.php?".mktime();
	if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	header("location: ".$Url);
	exit;
}else if( $Botao == "ImprimirMaterialSustentavel" ){
	$Url = "RelMaterialSustentavelPdf.php?".mktime();
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
	document.RelMaterial.Subclasse.value = '';
	document.RelMaterial.SubclasseDescricaoFamilia.value = '';
	document.RelMaterial.submit();
}
function enviar(valor){
	document.RelMaterial.Botao.value = valor;
	document.RelMaterial.submit();
}
function emitir(valor,subclasse){
	document.RelMaterial.Botao.value=valor;
	if( document.RelMaterial.Subclasse ){
		document.RelMaterial.Subclasse.value=subclasse;
	}else{
		document.RelMaterial.SubclasseDireta.value=subclasse;
	}
	document.RelMaterial.submit();
}
function validapesquisa(){
	if( document.RelMaterial.SubclasseDescricaoDireta.value != "" ){
		if( document.RelMaterial.Grupo ){
     	document.RelMaterial.Grupo.value = '';
  	}
		if( document.RelMaterial.Classe ){
  	  document.RelMaterial.Classe.value = '';
  	}
  	document.RelMaterial.Botao.value = "Validar";
	}
	if( document.RelMaterial.Subclasse ){
	  if( document.RelMaterial.SubclasseDescricaoFamilia.value != "" ){
				document.RelMaterial.Subclasse.value = 0;
    }
  }
  document.RelMaterial.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RelMaterial.php" method="post" name="RelMaterial">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais > Relatórios > Material
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
			    					RELATÓRIO DE MATERIAIS
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
	              				<td class="textonormal" bgcolor="#DCEDF7" width="30%">Subclasse</td>
	              				<td class="textonormal" colspan="2">
	              					<select name="OpcaoPesquisaSubClasse" class="textonormal">
	              						<option value="0" selected>Código Reduzido</option>
	              						<option value="1">Descrição contendo</option>
	              						<option value="2">Descrição iniciada por</option>
	              					</select>
         	        				<input type="text" name="SubclasseDescricaoDireta" size="10" maxlength="10" class="textonormal">
				           	      <a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0"></a>
				           	      <input type="checkbox" name="CheckDireta" onClick="javascript:enviar('');" value="TD">Todas
				           	      <input type="hidden" name="SubclasseDireta" value="">
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
							      	    <table class="textonormal" border="0" width="100%" summary="">
						                	<tr>
								              <td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Tipo de Material</td>
								              <td class="textonormal">
									              <input type="radio" name="TipoMaterial" value="C" onClick="javascript:enviar('');" <?php if( $TipoMaterial == "C" ){ echo "checked"; } ?> > Consumo
									              <input type="radio" name="TipoMaterial" value="P" onClick="javascript:enviar('');" <?php if( $TipoMaterial == "P" ){ echo "checked"; } ?> > Permanente
							              	</td>
							            	</tr>
							            	<?php if( $TipoMaterial != "" ){ ?>
							            	<tr>
							              	<td class="textonormal" bgcolor="#DCEDF7" height="20">Grupo</td>
							              	<td class="textonormal">
							              	  <select name="Grupo" onChange="javascript:enviar('');" class="textonormal">
              	              		<option value="">Selecione um Grupo...</option>
							              	    <?php
							              			$db = Conexao();
																	if( $TipoMaterial == "C" or $TipoMaterial == "P" ){
																			$sql  = "SELECT CGRUMSCODI,EGRUMSDESC FROM SFPC.TBGRUPOMATERIALSERVICO ";
																			$sql .= " WHERE FGRUMSTIPM = '$TipoMaterial' AND FGRUMSSITU = 'A' ";
																			$sql .= " ORDER BY EGRUMSDESC";
										                	$result = $db->query($sql);
		                									if( PEAR::isError($result) ){
																		     	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																			}else{
																					while($Linha = $result->fetchRow()){
																							$Descricao   = substr($Linha[1],0,75);
																							if( $Linha[0] == $Grupo ){
																									echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
																							}else{
																									echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
																							}
																					}
									                    }
					              	        }
							              	    ?>
							              	  </select>
							              	</td>
							            	</tr>
							            	<?php
							            	}
							            	if( $Grupo != "")
							            	{
							            	?>
							              <tr>
								              <td class="textonormal" bgcolor="#DCEDF7">Classe </td>
              								<td class="textonormal">
								              	<select name="Classe" class="textonormal" onChange="javascript:enviar('');">
              										<option value="">Selecione uma Classe...</option>
								              		<?php
          												if( $Grupo != "" ){
																			$sql  = "SELECT CCLAMSCODI, ECLAMSDESC ";
																			$sql .= "  FROM SFPC.TBCLASSEMATERIALSERVICO ";
																			$sql .= " WHERE CGRUMSCODI = $Grupo AND FCLAMSSITU = 'A' ";
																			$sql .= " ORDER BY ECLAMSDESC";
																			$res  = $db->query($sql);
																		  if( PEAR::isError($res) ){
																				  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
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
	            										}
            											?>
              									</select>
              								</td>
            								</tr>
                            <?php
                            }
                            if( $Grupo != "" and $Classe != "" ){
                            ?>
								        		<tr>
									            <td class="textonormal" bgcolor="#DCEDF7" height="20">Subclasse</td>
								  	        	<td class="textonormal">
							              	  <select name="Subclasse" onChange="javascript:enviar();" class="textonormal">
              	              		<option value="">Selecione uma Subclasse...</option>
							              	    <?php
																	$sql    = "SELECT CSUBCLSEQU,ESUBCLDESC FROM SFPC.TBSUBCLASSEMATERIAL ";
																	$sql   .= " WHERE CGRUMSCODI = $Grupo AND CCLAMSCODI = $Classe ";
																	$sql   .= " ORDER BY ESUBCLDESC";
										              $result = $db->query($sql);
		                							if( PEAR::isError($result) ){
																	    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																	}else{
																	    while($Linha = $result->fetchRow()){
				          	      					      $Descricao = substr($Linha[1],0,75);
									          	      	    if( $Linha[0] == $Subclasse ){
									    	      				        echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
											      	            }else{
									    	      						    echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
											      	      		  }
						      	      				    }
									                }
									                $db->disconnect();
							              	    ?>
							              	  </select>
							              	  <input type="text" name="SubclasseDescricaoFamilia" size="10" maxlength="10" class="textonormal">
							              	  <a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0"></a>
							              	  <input type="checkbox" name="CheckSubclasse" onClick="javascript:checktodos();" value="TF" >Todas
								  	        	</td>
									        	</tr>
									        	<?php } ?>
							          	</table>
												</td>
											</tr>
										</table>
									</td>
								</tr>
             		<tr>
             			<td align="right" colspan="4">
             				<?php if( $CheckSubclasse == "TF" or $CheckDireta == "TD" ){ ?>
								<tr>
									<td>
										<input type="checkbox" name="SituacaoInativos" onClick="javascript:enviar('');" value="I">Mostrar materias inativos
										<br>
										<input type="checkbox" name="SituacaoSustentavel" onClick="javascript:enviar('');" value="S">Só exibir itens sustentáveis
									</td>
								</tr>
             				<td>
								<input  type="button" name="Imprimir por Material" value="Imprimir por Material" class="botao" onclick="javascript:enviar('ImprimirMaterial');">
								<input  type="button" name="Imprimir por Família" value="Imprimir por Familia" class="botao" onclick="javascript:enviar('ImprimirFamilia');"> 
								
								<?php } ?>
								<?php if ($SituacaoInativos =="I"){ ?>
									<input  type="button" name="Imprimir por Material" value="Imprimir por Material" class="botao" onclick="javascript:enviar('ImprimirMaterialInativo');">
								<?php } ?>

								<?php if ($SituacaoSustentavel =="S"){ ?>
									<input  type="button" name="Imprimir por Material" value="Imprimir por Material" class="botao" onclick="javascript:enviar('ImprimirMaterialSustentavel');">
								<?php } ?>

							 	<input  type="button" name="Limpar" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
								<input type="hidden" name="Botao" value="">
							
							</td>
             				            				
             				
             			</td>
             		</tr>
						    <?php
								if( $SubclasseDescricaoDireta != "" ){
										if( $OpcaoPesquisaSubClasse == 0 ){
												if( !SoNumeros($SubclasseDescricaoDireta) ){ $sqlgeral = ""; }
										}
								}
								if( $Mens == 0 and $sqlgeral != "" and ( $SubclasseDescricaoDireta != "" or
								 		$Subclasse != "" or $SubclasseDescricaoFamilia != "" or $CheckDireta == "TD" or $CheckSubclasse == "TF" or $SituacaoInativos =="I" or $SituacaoSustentavel =="S" ) ){
										$db     = Conexao();
										$res    = $db->query($sqlgeral);
										$qtdres = $res->numRows();
										if( PEAR::isError($res) ){
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
											echo "<tr>\n";
											echo "  <td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"4\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
											echo "</tr>\n";
											
											if ($SituacaoInativos!=NULL){
												
													$TipoMaterialAntes = "";
													$GrupoAntes        = "";
													$ClasseAntes       = "";
													$MaterialSequenciaAntes    = "";
													while( $rowInativos	= $resultInativos->fetchRow() ){
															$GrupoCodigo        = $rowInativos[0];
															$GrupoDescricao     = $rowInativos[1];
															$ClasseCodigo       = $rowInativos[2];
															$ClasseDescricao    = $rowInativos[3];
															$SubClasseSequ      = $rowInativos[4];
															$SubClasseDescricao = $rowInativos[5];
															$MaterialSequencia  = $rowInativos[6];
															$MaterialDescricao  = $rowInativos[7];
															$UndMedidaSigla     = $rowInativos[8];
															$TipoMaterialCodigo = $rowInativos[9];
															$CodigoReduzido		= $rowInativos[10];
															// var_dump($row);die;
															if( $TipoMaterialAntes != $TipoMaterialCodigo ) {
																
																if($TipoMaterialCodigo == "C"){
																	echo "<tr>\n";
																	echo "  <td class=\"textoabason\" bgcolor=\"#BFDAF2\" colspan=\"4\" align=\"center\">";
																	echo "CONSUMO";
																	echo "  </td>\n";
																	echo "</tr>\n";
																	echo "<tr>\n";
																	echo "  <td class=\"textoabason\" bgcolor=\"#DDECF9\" colspan=\"4\" align=\"center\">$GrupoDescricao / $ClasseDescricao</td>\n";
																	echo "</tr>\n";
																}else{
																	echo "<tr>\n";
																	echo "  <td class=\"textoabason\" bgcolor=\"#BFDAF2\" colspan=\"4\" align=\"center\">";
																	echo "PERMANENTE";
																	echo "  </td>\n";
																	echo "</tr>\n";
																	echo "<tr>\n";
																	echo "  <td class=\"textoabason\" bgcolor=\"#DDECF9\" colspan=\"4\" align=\"center\">$GrupoDescricao / $ClasseDescricao</td>\n";
																	echo "</tr>\n";
																}
															}
															if( ( $GrupoAntes != $GrupoCodigo ) && ( $TipoMaterialAntes == $TipoMaterialCodigo ) ) {
																echo "<tr>\n";
																echo "  <td class=\"textoabason\" bgcolor=\"#DDECF9\" colspan=\"4\" align=\"center\">$GrupoDescricao / $ClasseDescricao</td>\n";
																echo "</tr>\n";
																	}else{
																		if( ( $ClasseAntes != $ClasseCodigo ) && ( $TipoMaterialAntes == $TipoMaterialCodigo ) ) {
																			echo "<tr>\n";
																			echo "  <td class=\"textoabason\" bgcolor=\"#DDECF9\" colspan=\"4\" align=\"center\">$GrupoDescricao / $ClasseDescricao</td>\n";
																			echo "</tr>\n";
																			}
																	}
															if( $MaterialSequenciaAntes != $MaterialSequencia ) {
																echo "<tr>\n";
																echo "  <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"4\">\n";
																echo "    <a href=\"javascript:enviar('ImprimirMaterialInativo');\"><font color=\"#000000\">$MaterialDescricao</font></a>";
																echo "  </td>\n";
																echo "</tr>";
															}
															$TipoMaterialAntes = $TipoMaterialCodigo;
															$GrupoAntes        = $GrupoCodigo;
															$ClasseAntes       = $ClasseCodigo;
															$MaterialSequenciaAntes    = $MaterialSequencia;
														}
												
											}elseif($SituacaoSustentavel != NULL){

												echo "<tr>\n";
												echo "  <td class=\"textoabason\" bgcolor=\"#BFDAF2\" colspan=\"4\" align=\"center\">";
												echo "ITENS SUSTENTAVEIS";
												echo "  </td>\n";
												echo "</tr>\n";
												
												
												while ($Linha = $resultSustentavel->fetchRow()){
												$Sustentaveis = substr($Linha[0],0,150);
												

												echo "<tr>\n";
												echo "  <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"4\">\n";
												echo "    <a href=\"javascript:emitir('ImprimirMaterialSustentavel');\"><font color=\"#000000\">$Sustentaveis</font></a>";
												echo "  </td>\n";
												echo "</tr>";
												}

											}elseif ($qtdres > 0){

												
													$TipoMaterialAntes = "";
													$GrupoAntes        = "";
													$ClasseAntes       = "";
													$SubClasseAntes    = "";
													while( $row	= $res->fetchRow() ){
															$GrupoCodigo        = $row[0];
															$GrupoDescricao     = $row[1];
															$ClasseCodigo       = $row[2];
															$ClasseDescricao    = $row[3];
															$SubClasseSequ      = $row[4];
															$SubClasseDescricao = $row[5];
															$MaterialSequencia  = $row[6];
															$MaterialDescricao  = $row[7];
															$UndMedidaSigla     = $row[8];
															$TipoMaterialCodigo = $row[9];
															$CodigoReduzido		  = $row[10];
															if( $TipoMaterialAntes != $TipoMaterialCodigo ) {
																if($TipoMaterialCodigo == "C"){
																	echo "<tr>\n";
																	echo "  <td class=\"textoabason\" bgcolor=\"#BFDAF2\" colspan=\"4\" align=\"center\">";
																	echo "CONSUMO";
																	echo "  </td>\n";
																	echo "</tr>\n";
																	echo "<tr>\n";
																	echo "  <td class=\"textoabason\" bgcolor=\"#DDECF9\" colspan=\"4\" align=\"center\">$GrupoDescricao / $ClasseDescricao</td>\n";
																	echo "</tr>\n";
																}else{
																	echo "<tr>\n";
																	echo "  <td class=\"textoabason\" bgcolor=\"#BFDAF2\" colspan=\"4\" align=\"center\">";
																	echo "PERMANENTE";
																	echo "  </td>\n";
																	echo "</tr>\n";
																	echo "<tr>\n";
																	echo "  <td class=\"textoabason\" bgcolor=\"#DDECF9\" colspan=\"4\" align=\"center\">$GrupoDescricao / $ClasseDescricao</td>\n";
																	echo "</tr>\n";
																}
															}
															if( ( $GrupoAntes != $GrupoCodigo ) && ( $TipoMaterialAntes == $TipoMaterialCodigo ) ) {
																echo "<tr>\n";
																echo "  <td class=\"textoabason\" bgcolor=\"#DDECF9\" colspan=\"4\" align=\"center\">$GrupoDescricao / $ClasseDescricao</td>\n";
																echo "</tr>\n";
																	}else{
																		if( ( $ClasseAntes != $ClasseCodigo ) && ( $TipoMaterialAntes == $TipoMaterialCodigo ) ) {
																			echo "<tr>\n";
																			echo "  <td class=\"textoabason\" bgcolor=\"#DDECF9\" colspan=\"4\" align=\"center\">$GrupoDescricao / $ClasseDescricao</td>\n";
																			echo "</tr>\n";
																			}
																	}
															if( $SubClasseAntes != $SubClasseSequ ) {
																echo "<tr>\n";
																echo "  <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"4\">\n";
																echo "    <a href=\"javascript:emitir('Emitir',$SubClasseSequ);\"><font color=\"#000000\">$SubClasseDescricao</font></a>";
																echo "  </td>\n";
																echo "</tr>";
															}
															$TipoMaterialAntes = $TipoMaterialCodigo;
															$GrupoAntes        = $GrupoCodigo;
															$ClasseAntes       = $ClasseCodigo;
															$SubClasseAntes    = $SubClasseSequ;
														}
														$db->disconnect();
													}else{
														echo "<tr>\n";
														echo "	<td valign=\"top\" colspan=\"4\" class=\"textonormal\" bgcolor=\"FFFFFF\">\n";
														echo "		Pesquisa sem Ocorrências.\n";
														echo "	</td>\n";
														echo "</tr>\n";
													}
											}
										}
											
											
										
									
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
