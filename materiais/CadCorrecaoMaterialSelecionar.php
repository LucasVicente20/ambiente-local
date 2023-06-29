<?php
# -----------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadCorrecaoMaterialSelecionar.php
# Autor:    Carlos Abreu
# Data:     02/06/2006
# OBS.:     Tabulação 2 espaços
# -----------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     29/08/2006 - Opção de pesquisa de material com descrição "iniciada por"
# -----------------------------------------------------------------------------
# Alterado: Wagner Barros
# Data:     29/09/2006 - Exibir o código reduzido do material ao lado da descrição"
# -----------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     04/03/2008 - Alteração para que seja permitido apenas a exibição de grupos, classes, subclasses e materiais ativos.
# Objetivo: Programa de seleção de tipo correção de materiais
# -----------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 73662
# -----------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/materiais/CadTransferenciaSubclasse.php' );
AddMenuAcesso( '/materiais/CadSubstituicaoMaterial.php' );
AddMenuAcesso( '/materiais/CadCorrecaoMaterialSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
		$ProgramaOrigem	           = $_POST['ProgramaOrigem'];
		$Botao    			           = $_POST['Botao'];
		$TipoMaterial 	           = $_POST['TipoMaterial'];
		$Grupo   				           = $_POST['Grupo'];
		$Classe    			           = $_POST['Classe'];
		$Subclasse    	           = $_POST['Subclasse'];
		$SubclasseDescricaoFamilia = strtoupper2(trim($_POST['SubclasseDescricaoFamilia']));

		# Opcao de correcao
		$OpcaoCorrecao             = $_POST['OpcaoCorrecao'];

		# Pesquisa direta #
		$OpcaoPesquisaMaterial     = $_POST['OpcaoPesquisaMaterial'];
		$OpcaoPesquisaSubClasse    = $_POST['OpcaoPesquisaSubClasse'];
		$SubclasseDescricaoDireta  = strtoupper2(trim($_POST['SubclasseDescricaoDireta']));
		$MaterialDescricaoDireta   = strtoupper2(trim($_POST['MaterialDescricaoDireta']));
		$CodigoReduzido 				   = $_POST['CodigoReduzido'];
}
$Tipo     = $_GET['Tipo'];
$Mens     = $_GET['Mens'];
$Mensagem = $_GET['Mensagem'];

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Monta o sql para montagem dinâmica da grade a partir da pesquisa #
$sql    = "SELECT DISTINCT(GRU.CGRUMSCODI),GRU.EGRUMSDESC,CLA.CCLAMSCODI,CLA.ECLAMSDESC,";
$sql   .= "       SUB.CSUBCLSEQU,SUB.ESUBCLDESC,MAT.CMATEPSEQU,MAT.EMATEPDESC,UND.EUNIDMSIGL,GRU.FGRUMSTIPM,MAT.CMATEPSEQU ";
$from   = "  FROM SFPC.TBMATERIALPORTAL MAT,SFPC.TBGRUPOMATERIALSERVICO GRU,SFPC.TBCLASSEMATERIALSERVICO CLA, ";
$from  .= "       SFPC.TBSUBCLASSEMATERIAL SUB, SFPC.TBUNIDADEDEMEDIDA UND ";
$where  = " WHERE MAT.CSUBCLSEQU = SUB.CSUBCLSEQU AND SUB.CGRUMSCODI = CLA.CGRUMSCODI ";
$where .= "   AND SUB.CCLAMSCODI = CLA.CCLAMSCODI AND CLA.CGRUMSCODI = GRU.CGRUMSCODI ";
$where .= "   AND MAT.CUNIDMCODI = UND.CUNIDMCODI AND MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
$where .= "   AND GRU.FGRUMSSITU = 'A' AND CLA.FCLAMSSITU = 'A' AND SUB.FSUBCLSITU = 'A' AND MAT.CMATEPSITU = 'A' ";
$order  = " ORDER BY GRU.FGRUMSTIPM, GRU.EGRUMSDESC, CLA.ECLAMSDESC, SUB.ESUBCLDESC, MAT.EMATEPDESC ";

# Verifica se o Tipo de Material foi escolhido #
if( $TipoMaterial != "" and $MaterialDescricaoDireta == "" and $SubclasseDescricaoDireta == "" ){
		$where .= " AND GRU.FGRUMSTIPM = '$TipoMaterial' ";
}

# Verifica se o Grupo foi escolhido #
if( $Grupo != "" and $MaterialDescricaoDireta == "" and $SubclasseDescricaoDireta == "" ){
		$where .= " AND GRU.CGRUMSCODI = $Grupo ";
}

# Verifica se a Classe foi escolhida #
if( $Classe != "" and $MaterialDescricaoDireta == "" and $SubclasseDescricaoDireta == "" ){
		$where .= " AND CLA.CGRUMSCODI = $Grupo AND CLA.CCLAMSCODI = $Classe ";
}

# Verifica se a SubClasse foi escolhida #
if( $Subclasse != "" and $MaterialDescricaoDireta == "" and $SubclasseDescricaoDireta == "" ){
		$where .= " AND SUB.CSUBCLSEQU = $Subclasse ";
}

# Se foi digitado algo na caixa de texto da subclasse em pesquisa familia #
if( $SubclasseDescricaoFamilia != "" and $MaterialDescricaoDireta == "" and $SubclasseDescricaoDireta == "" ){
		$where .= " AND ( ";
  	$where .= "      TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($SubclasseDescricaoFamilia))."%' OR ";
  	$where .= "      TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '% ".strtoupper2(RetiraAcentos($SubclasseDescricaoFamilia))."%' ";
  	$where .= "     )";
}

# Se foi digitado algo na caixa de texto da subclasse em pesquisa direta #
if( $SubclasseDescricaoDireta != "" and $MaterialDescricaoDireta == "" ){
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

# Se foi digitado algo na caixa de texto do material em pesquisa direta #
if( $MaterialDescricaoDireta != "" and $SubclasseDescricaoDireta == "" ){
		if( $OpcaoPesquisaMaterial == 0 ){
			  if (SoNumeros($MaterialDescricaoDireta)) {
	    	  $where .= " AND MAT.CMATEPSEQU = $MaterialDescricaoDireta ";
	    	}
	  }elseif($OpcaoPesquisaMaterial == 1){
	    	$where .= " AND ( ";
		  	$where .= "      TRANSLATE(MAT.EMATEPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($MaterialDescricaoDireta))."%' OR ";
		  	$where .= "      TRANSLATE(MAT.EMATEPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '% ".strtoupper2(RetiraAcentos($MaterialDescricaoDireta))."%' ";
		  	$where .= "     )";
	  }else{
				$where .= " AND TRANSLATE(MAT.EMATEPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($MaterialDescricaoDireta))."%' ";
		}
}

# Gera o SQL com a concatenação das variaveis $sql,$from,$where,$order #
$sqlgeral = $sql.$from.$where.$order;

if( $Botao == "Limpar" ){
		header("location: CadCorrecaoMaterialSelecionar.php");
		exit;
}elseif( $Botao == "Validar" ){
		# Critica dos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";
    if( $SubclasseDescricaoDireta != "" and $OpcaoPesquisaSubClasse == 0 and ! SoNumeros($SubclasseDescricaoDireta) ){
    	  if( $Mens == 1 ){ $Mensagem .= ", "; }
      	$Mens = 1;
      	$Tipo = 2;
      	$Mensagem .= "<a href=\"javascript:document.CadCorrecaoMaterialSelecionar.SubclasseDescricaoDireta.focus();\" class=\"titulo2\">Código reduzido da Subclasse</a>";
    }elseif($SubclasseDescricaoDireta != "" and ($OpcaoPesquisaSubClasse == 1 or $OpcaoPesquisaSubClasse == 2) and strlen($SubclasseDescricaoDireta)< 2){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens = 1;
				$Tipo = 2;
				$Mensagem .= "<a href=\"javascript:document.CadCorrecaoMaterialSelecionar.SubclasseDescricaoDireta.focus();\" class=\"titulo2\">Descrição com no mínimo 2 caracteres</a>";
		}
    if( $MaterialDescricaoDireta != "" and $OpcaoPesquisaMaterial == 0 and ! SoNumeros($MaterialDescricaoDireta) ){
    	  if( $Mens == 1 ){ $Mensagem .= ", "; }
      	$Mens = 1;
      	$Tipo = 2;
      	$Mensagem .= "<a href=\"javascript:document.CadCorrecaoMaterialSelecionar.MaterialDescricaoDireta.focus();\" class=\"titulo2\">Código reduzido do Material</a>";
    }elseif($MaterialDescricaoDireta != "" and ($OpcaoPesquisaMaterial == 1 or $OpcaoPesquisaMaterial == 2) and strlen($MaterialDescricaoDireta)< 2){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens = 1;
				$Tipo = 2;
				$Mensagem .= "<a href=\"javascript:document.CadCorrecaoMaterialSelecionar.MaterialDescricaoDireta.focus();\" class=\"titulo2\">Descrição com no mínimo 2 caracteres</a>";
		}
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>

<head>
<title>Portal de Compras - Correção de Material</title>
<script language="javascript" type="">
function enviar(valor){
	document.CadCorrecaoMaterialSelecionar.Botao.value = valor;
	document.CadCorrecaoMaterialSelecionar.submit();
}
function validapesquisa(){
	if( ( document.CadCorrecaoMaterialSelecionar.MaterialDescricaoDireta.value != '' ) ||
      ( document.CadCorrecaoMaterialSelecionar.SubclasseDescricaoDireta.value != '') ) {
		if ( document.CadCorrecaoMaterialSelecionar.Grupo ) {
     	document.CadCorrecaoMaterialSelecionar.Grupo.value = '';
  	}
		if (document.CadCorrecaoMaterialSelecionar.Classe) {
  	  document.CadCorrecaoMaterialSelecionar.Classe.value = '';
  	}
  	document.CadCorrecaoMaterialSelecionar.Botao.value = 'Validar';
	}
	if( document.CadCorrecaoMaterialSelecionar.Subclasse ){
	  if (document.CadCorrecaoMaterialSelecionar.SubclasseDescricaoFamilia.value != "") {
   	    document.CadCorrecaoMaterialSelecionar.Subclasse.value = 0;
    }
  }
  document.CadCorrecaoMaterialSelecionar.submit();
}
function remeter(){
	document.CadCorrecaoMaterialSelecionar.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
</head>
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadCorrecaoMaterialSelecionar.php" method="post" name="CadCorrecaoMaterialSelecionar">
<br><br><br><br><br>
	<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais > Correção
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
	  <td width="100"></td>
	  <td align="left">
	  	<?php if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,1); } ?>
	  </td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

		<!-- Corpo -->
		<tr>
  		<td width="100"></td>
			<td class="textonormal">
				<table border="0" width="100%" cellspacing="0" cellpadding="3" summary="">
					<tr>
		      	<td class="textonormal">
		        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
             		<input type="hidden" name="ProgramaOrigem" value="<?php echo $ProgramaOrigem; ?>">
		          	<tr>
		            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
			    					CORREÇÃO - MATERIAIS
			          	</td>
			        	</tr>
			        	<tr>
		    	      	<td class="textonormal" colspan="4">
										<p align="justify">
											Selecione o tipo de correção desejado.
		          	   	</p>
		          		</td>
			        	</tr>
								<tr>
		    	      	<td colspan="4">
		    	      		<table class="textonormal" border="0" width="100%" summary="">
			                <tr>
					              <td class="textonormal" bgcolor="#DCEDF7" height="20" width="150">Tipo de Correção</td>
					              <td class="textonormal">
					              	<input type="radio" name="OpcaoCorrecao" value="transferencia" onClick="javascript:document.CadCorrecaoMaterialSelecionar.submit();" <?php if( $OpcaoCorrecao == "transferencia" ){ echo "checked"; } ?>> Transferência de uma subclasse para outra<br>
													<input type="radio" name="OpcaoCorrecao" value="substituicao" onClick="javascript:document.CadCorrecaoMaterialSelecionar.submit();" <?php if( $OpcaoCorrecao == "substituicao" ){ echo "checked"; } ?>> Substituição de material
						           	</td>
				            	</tr>
				            </table>
		          		</td>
			        	</tr>
			        	<?php if ( $OpcaoCorrecao != "" ){ ?>
		  	      	<tr>
		    	      	<td class="textonormal" colspan="4">
										<p align="justify">
											Para pesquisar um item já cadastrado, preencha o argumento da pesquisa.
											Depois, clique no item desejado.
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
	              				<td class="textonormal" bgcolor="#DCEDF7" width="150">Subclasse</td>
	              				<td class="textonormal">
	              					<select name="OpcaoPesquisaSubClasse" class="textonormal">
	              						<option value="0">Código Reduzido</option>
	              						<option value="1">Descrição contendo</option>
	              						<option value="2">Descrição iniciada por</option>
	              					</select>
         	        				<input type="text" name="SubclasseDescricaoDireta" size="10" maxlength="10" class="textonormal">
				           	      <a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0"></a>
	              				</td>
	              			</tr>
	            				<tr>
	              				<td class="textonormal" bgcolor="#DCEDF7" width="150">Material</td>
	              				<td class="textonormal">
	              					<select name="OpcaoPesquisaMaterial" class="textonormal">
	              						<option value="0">Código Reduzido</option>
	              						<option value="1">Descrição contendo</option>
	              						<option value="2">Descrição iniciada por</option>
	              					</select>
         	        				<input type="text" name="MaterialDescricaoDireta" size="10" maxlength="10" class="textonormal">
				           	      <a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0"></a>
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
												<td>
							      	    <table class="textonormal" border="0" width="100%" summary="">
						                <tr>
								              <td class="textonormal" bgcolor="#DCEDF7" height="20" width="150">Tipo de Material</td>
								              <td class="textonormal">
									              <input type="radio" name="TipoMaterial" value="C" onClick="javascript:<?php
									              if ($Grupo!=""){echo "Grupo.selectedIndex=0;";}
									              if ($Classe!=""){echo "Classe.selectedIndex=0;";}
									              if ($Subclasse!=""){echo "Subclasse.selectedIndex=0;";}
									              echo "document.CadCorrecaoMaterialSelecionar.submit();\"";
									              if( $TipoMaterial == "C" ){ echo "checked"; }
									              ?>> Consumo
									              <input type="radio" name="TipoMaterial" value="P" onClick="javascript:<?php
									              if ($Grupo!=""){echo "Grupo.selectedIndex=0;";}
									              if ($Classe!=""){echo "Classe.selectedIndex=0;";}
									              if ($Subclasse!=""){echo "Subclasse.selectedIndex=0;";}
									              echo "document.CadCorrecaoMaterialSelecionar.submit();\"";
									              if( $TipoMaterial == "P" ){ echo "checked"; }
									              ?>> Permanente
							              	</td>
							            	</tr>
							            	<?php if( $TipoMaterial != "" ){ ?>
							            	<tr>
							              	<td class="textonormal" bgcolor="#DCEDF7" height="20" width="150">Grupo</td>
							              	<td class="textonormal">
							              	  <select name="Grupo" onChange="javascript:<?php
							              	  if ($Classe){echo "Classe.selectedIndex=0;";}
							              	  if ($Subclasse){echo "Subclasse.selectedIndex=0;";}
							              	  ?>remeter();" class="textonormal">
              	              		<option value="">Selecione um Grupo...</option>
							              	    <?php
							              			$db = Conexao();
																	if( $TipoMaterial == "C" or $TipoMaterial == "P" ){
																			$sql = "SELECT CGRUMSCODI,EGRUMSDESC FROM SFPC.TBGRUPOMATERIALSERVICO ";
																			$sql .= "WHERE FGRUMSTIPM = '$TipoMaterial' AND FGRUMSSITU = 'A' ORDER BY EGRUMSDESC";
										                	$result = $db->query($sql);
		                									if (PEAR::isError($result)) {
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
                       		  if( $Grupo != ""){
                       		  ?>
							              <tr>
								              <td class="textonormal" bgcolor="#DCEDF7" width="150">Classe </td>
              								<td class="textonormal">
								              	<select name="Classe" class="textonormal" onChange="javascript:<?php if ($Subclasse){echo "Subclasse.selectedIndex=0;";}?>remeter();">
              										<option value="">Selecione uma Classe...</option>
									              		<?php
            												if( $Grupo != "" ){
											              		$db   = Conexao();
                                        $sql  = "SELECT CLA.CCLAMSCODI, CLA.ECLAMSDESC ";
                                        $sql .= "  FROM SFPC.TBCLASSEMATERIALSERVICO CLA, SFPC.TBGRUPOMATERIALSERVICO GRU ";
                                        $sql .= " WHERE GRU.CGRUMSCODI = CLA.CGRUMSCODI AND CLA.CGRUMSCODI = $Grupo AND CLA.FCLAMSSITU = 'A' AND GRU.FGRUMSSITU = 'A' ";
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
										  	              	$db->disconnect();
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
									            <td class="textonormal" bgcolor="#DCEDF7" height="20" width="150">Subclasse</td>
								  	        	<td class="textonormal">
							              	  <select name="Subclasse" onChange="javascript:remeter();" class="textonormal">
              	              		<option value="">Selecione uma Subclasse...</option>
							              	    <?php
							              			$db = Conexao();
                                  $sql   = "  SELECT SUB.CSUBCLSEQU, SUB.ESUBCLDESC ";
                                  $sql  .= "  FROM SFPC.TBGRUPOMATERIALSERVICO GRU, SFPC.TBCLASSEMATERIALSERVICO CLA, ";
                                  $sql  .= "       SFPC.TBSUBCLASSEMATERIAL SUB ";
                                  $sql  .= " WHERE SUB.CGRUMSCODI = CLA.CGRUMSCODI ";
                                  $sql  .= "   AND SUB.CCLAMSCODI = CLA.CCLAMSCODI AND CLA.CGRUMSCODI = GRU.CGRUMSCODI ";
                                  $sql  .= "   AND GRU.FGRUMSSITU = 'A' AND CLA.FCLAMSSITU = 'A' AND SUB.FSUBCLSITU = 'A' ";
                                  $sql  .= "   AND SUB.CGRUMSCODI = '$Grupo' AND SUB.CCLAMSCODI = '$Classe' ";
                                  $sql  .= "    ORDER BY ESUBCLDESC ";
										              $result = $db->query($sql);
		                							if (PEAR::isError($result)) {
																	   ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																	}else{
																	   while($Linha = $result->fetchRow()){
			          	      					      $Descricao   = substr($Linha[1],0,75);
								          	      	    if( $Linha[0] == $Subclasse ){
								    	      				       echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
										      	            }else{
								    	      						   echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
										      	      		  }
						      	      				   }
									                }
							              	    ?>
							              	  </select>
							              	  <input type="text" name="SubclasseDescricaoFamilia" size="10" maxlength="10" class="textonormal">
				           	      			<a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0"></a>
								  	        	</td>
									        	</tr>
									        	<?php } ?>
							          	</table>
												</td>
											</tr>
										</table>
									</td>
								</tr>
						    <?php
								if( $MaterialDescricaoDireta != "" ) {
										if( $OpcaoPesquisaMaterial == 0 ){
												if( !SoNumeros($MaterialDescricaoDireta) ){ $sqlgeral = ""; }
									 	}
								}
								if( $SubclasseDescricaoDireta != "" ){
										if( $OpcaoPesquisaSubClasse == 0 ){
												if( !SoNumeros($SubclasseDescricaoDireta) ){ $sqlgeral = ""; }
										}
								}
								if( $sqlgeral != "" and $Mens == 0) {
										if( ( $MaterialDescricaoDireta != "" or $SubclasseDescricaoDireta != "" ) or
								 				( $Subclasse != "" or $SubclasseDescricaoFamilia != "" or $ChkSubclasse != "" ) ){
												$db     = Conexao();
												$res    = $db->query($sqlgeral);
												
												
												if( PEAR::isError($res) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlgeral");
												}else{
														$qtdres = $res->numRows();
														echo "<tr>\n";
														echo "  <td align=\"center\" bgcolor=\"#75ADE6\" class=\"titulo3\" colspan=\"4\">RESULTADO DA PESQUISA</td>\n";
														echo "</tr>\n";
														if( $qtdres > 0 ) {
						  									$TipoMaterialAntes  = "";
						  									$GrupoAntes         = "";
						  									$ClasseAntes        = "";
						  									$SubClasseAntes     = "";
						  									$SubClasseSequAntes = "";
																$irow = 1;
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
							    											echo "<tr>\n";
							    											echo "  <td class=\"textoabason\" bgcolor=\"#BFDAF2\" align=\"center\" colspan=\"4\">";
						         										if($TipoMaterialCodigo == "C"){ echo "CONSUMO"; }else{ echo "PERMANENTE";}
						  		    									echo "  </td>\n";
						  				    							echo "</tr>\n";
						    										}
				    						            if( $ClasseAntes != $ClasseDescricao ) {
				    						            		echo "<tr>\n";
				      											    echo "  <td class=\"textoabason\" bgcolor=\"#DDECF9\" align=\"center\" colspan=\"4\">$GrupoDescricao / $ClasseDescricao</td>\n";
				      											    echo "</tr>\n";
				      												  echo "<tr>\n";
						      											echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"30%\">SUBCLASSE</td>\n";
						      											echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"50%\">DESCRIÇÃO DO MATERIAL</td>\n";
						      											echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\" align=\"center\">CÓD.RED.</td>\n";
						      											echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\" align=\"center\">UNIDADE</td>\n";
						      											echo "</tr>\n";
						    										}
						    										echo "<tr>\n";
						    										if( $SubClasseAntes != $SubClasseDescricao or $SubClasseSequAntes != $SubClasseSequ ) {
						    											  $flg = "S";
						      											echo "  <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"30%\">\n";
						      											echo "    $SubClasseDescricao";
						      											echo "  </td>\n";
						    										} else {
						      											echo "  <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"30%\">\n";
						      											echo "&nbsp;";
						      											echo "  </td>\n";
						      											$flg = "";
						      									}
				    												echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"60%\">\n";
				    												if ( $OpcaoCorrecao == "transferencia" ){
				    														$Url = "CadTransferenciaSubclasse.php?Material=$MaterialSequencia";
				    														echo "	  <a href=\"$Url\"><font color=\"#000000\">$MaterialDescricao</font></a>";
				    												} elseif ( $OpcaoCorrecao == "substituicao" ) {
				    														$Url = "CadSubstituicaoMaterial.php?Material=$MaterialSequencia";
				    														echo "	  <a href=\"$Url\"><font color=\"#000000\">$MaterialDescricao</font></a>";
				    												}
																		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				    												echo "	</td>\n";
				    												echo "  <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\" width=\"30%\">\n";
																		echo "    $CodigoReduzido";
																		echo "  </td>\n";
				    												echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\"  width=\"10%\">\n";
				    												echo "	  $UndMedidaSigla";
				    												echo "	</td>\n";
						    										echo "</tr>\n";
						    										$TipoMaterialAntes  = $TipoMaterialCodigo;
						    										$GrupoAntes         = $GrupoDescricao;
						    										$ClasseAntes        = $ClasseDescricao;
						    										$SubClasseAntes     = $SubClasseDescricao;
						    										$SubClasseSequAntes = $SubClasseSequ;
						  									}
																$db->disconnect();
										        }else{
																echo "<tr>\n";
																echo "	<td valign=\"top\" class=\"textonormal\" bgcolor=\"FFFFFF\">\n";
																echo "		Pesquisa sem Ocorrências.\n";
																echo "	</td>\n";
																echo "</tr>\n";
														}
												}
										}
								}
						}
						?>
							<tr>
								<td class="textonormal" align="right" colspan="4">
									<input type="button" name="Limpar" value="Limpar" onClick="javascript:enviar('Limpar');" class="botao">
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
window.focus();
//-->
</script>
</body>
</html>