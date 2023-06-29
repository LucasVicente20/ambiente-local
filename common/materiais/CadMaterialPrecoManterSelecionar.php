<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadMaterialPrecoManterSelecionar.php
# Autor:    Rossana Lira
# Data:     06/06/2007
# Alterado: Rodrigo Melo
# Data:      26/02/2008 - Alteração para não permitir a exibição de itens inativos.
# Alterado: Rodrigo Melo
# Data:      04/03/2008 - Alteração para não permitir a exibição de grupos, classes, subclasses e materiais inativos ao pesquisar por família.
# Objetivo: Programa de Seleção de Manutenção de Preços de Material
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/materiais/CadMaterialPrecoManter.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
		$Botao                    = $_POST['Botao'];
		$TipoMaterial             = $_POST['TipoMaterial'];
		$Grupo                    = $_POST['Grupo'];
		$Classe                   = $_POST['Classe'];
		$Subclasse                = $_POST['Subclasse'];
		$Material                 = $_POST['Material'];
		$ChkMaterial              = $_POST['chkmaterial'];
		$MaterialDescricaoFamilia = strtoupper2(trim($_POST['MaterialDescricaoFamilia']));
		$OpcaoPesquisaMaterial    = $_POST['OpcaoPesquisaMaterial'];
		$OpcaoPesquisaSubClasse   = $_POST['OpcaoPesquisaSubClasse'];
		$SubclasseDescricaoDireta = strtoupper2(trim($_POST['SubclasseDescricaoDireta']));
		$MaterialDescricaoDireta  = strtoupper2(trim($_POST['MaterialDescricaoDireta']));
}else{
		$Mensagem                 = urldecode($_GET['Mensagem']);
		$Mens                     = $_GET['Mens'];
		$Tipo                     = $_GET['Tipo'];
		$exclusaoEfetuada         = $_GET['exclusaoEfetuada'];
}

if ( $exclusaoEfetuada==1 ) {
	$Mens = 1;
	$Tipo = 1;
	$Mensagem .= "Exclusao Efetuada!";
}	


# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Sql para montagem da janela #
$sql    = "SELECT MAT.CMATEPSEQU,MAT.EMATEPDESC ";
$from   = "  FROM SFPC.TBGRUPOMATERIALSERVICO GRU, SFPC.TBCLASSEMATERIALSERVICO CLA, ";
$from  .= "       SFPC.TBSUBCLASSEMATERIAL SUB, SFPC.TBMATERIALPORTAL MAT ";
$where  = " WHERE SUB.CGRUMSCODI = CLA.CGRUMSCODI AND SUB.CCLAMSCODI = CLA.CCLAMSCODI ";
$where .= "   AND CLA.CGRUMSCODI = GRU.CGRUMSCODI AND GRU.FGRUMSSITU = 'A' ";
$where .= "   AND CLA.FCLAMSSITU = 'A' AND SUB.FSUBCLSITU = 'A' AND MAT.CMATEPSITU = 'A' ";
$where .= "   AND MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
$order  = " ORDER BY SUB.ESUBCLDESC, MAT.EMATEPDESC ";

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

# Verifica se o Material foi escolhido #
if( $Material != "" and $Material != 0 and $MaterialDescricaoDireta == "" and $SubclasseDescricaoDireta == "" ){
		$where .= " AND MAT.CMATEPSEQU = $Material ";
}

# Se foi digitado algo na caixa de texto do material em pesquisa familia #
if( $MaterialDescricaoFamilia != "" and $MaterialDescricaoDireta == "" and $SubclasseDescricaoDireta == "" ){
		$where .= " AND ( ";
		$where .= "      TRANSLATE(MAT.EMATEPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($MaterialDescricaoFamilia))."%' OR ";
		$where .= "      TRANSLATE(MAT.EMATEPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '% ".strtoupper2(RetiraAcentos($MaterialDescricaoFamilia))."%' ";
		$where .= "     )";
}

# Se foi digitado algo na caixa de texto do material em pesquisa direta #
if( $MaterialDescricaoDireta != "" and $SubclasseDescricaoDireta == "" ){
		if( $OpcaoPesquisaMaterial == 0 ){
				if( SoNumeros($MaterialDescricaoDireta) ){
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

# Se foi digitado algo na caixa de texto da subclasse em pesquisa direta #
if( $SubclasseDescricaoDireta != "" and $MaterialDescricaoDireta == "" ){
		if( $OpcaoPesquisaSubClasse == 0 ){
				if(SoNumeros($SubclasseDescricaoDireta)) {
						$where .= " AND SUB.CSUBCLSEQU = '$SubclasseDescricaoDireta' ";
				}
		}elseif($OpcaoPesquisaSubClasse == 1){
				$where .= " AND ( ";
				$where .= "      TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($SubclasseDescricaoDireta))."%' OR ";
				$where .= "      TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') LIKE '% ".strtoupper2(RetiraAcentos($SubclasseDescricaoDireta))."%' ";
				$where .= "     )";
		}else{
				$where .= " AND TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($SubclasseDescricaoDireta))."%' ";
		}
}

# Gera o SQL com a concatenação das variaveis $sql,$from,$where #
$sqlgeral = $sql.$from.$where.$order;

if($Botao == "Limpar"){
		header("location: CadMaterialPrecoManterSelecionar.php");
		exit;
}elseif( $Botao == "Validar" ){
		# Critica dos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( $MaterialDescricaoDireta != "" and $OpcaoPesquisaMaterial == 0 and ! SoNumeros($MaterialDescricaoDireta) ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens = 1;
				$Tipo = 2;
				$Mensagem .= "<a href=\"javascript:document.CadMaterialPrecoManterSelecionar.MaterialDescricaoDireta.focus();\" class=\"titulo2\">O código reduzido do Material</a>";
		}elseif($MaterialDescricaoDireta != "" and ($OpcaoPesquisaMaterial == 1 or $OpcaoPesquisaMaterial == 2) and strlen($MaterialDescricaoDireta)< 2){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens = 1;
				$Tipo = 2;
				$Mensagem .= "<a href=\"javascript:document.CadMaterialPrecoManterSelecionar.MaterialDescricaoDireta.focus();\" class=\"titulo2\">Descrição com no mínimo 2 caracteres</a>";
		}
		if( $SubclasseDescricaoDireta != "" and $OpcaoPesquisaSubClasse == 0 and ! SoNumeros($SubclasseDescricaoDireta) ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens = 1;
				$Tipo = 2;
				$Mensagem .= "<a href=\"javascript:document.CadMaterialPrecoManterSelecionar.SubclasseDescricaoDireta.focus();\" class=\"titulo2\">O código reduzido da Subclasse</a>";
		}elseif($SubclasseDescricaoDireta != "" and ($OpcaoPesquisaSubClasse == 1 or $OpcaoPesquisaSubClasse == 2) and strlen($SubclasseDescricaoDireta)< 2){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens = 1;
				$Tipo = 2;
				$Mensagem .= "<a href=\"javascript:document.CadMaterialPrecoManterSelecionar.SubclasseDescricaoDireta.focus();\" class=\"titulo2\">Descrição com no mínimo 2 caracteres</a>";
		}
}
?>
<html>
<?
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.CadMaterialPrecoManterSelecionar.Botao.value = valor;
	document.CadMaterialPrecoManterSelecionar.submit();
}
function checktodos(){
	document.CadMaterialPrecoManterSelecionar.Material.value = '';
	document.CadMaterialPrecoManterSelecionar.submit();
}
function remeter(){
	document.CadMaterialPrecoManterSelecionar.submit();
}
function validapesquisa(){
	if( (document.CadMaterialPrecoManterSelecionar.MaterialDescricaoDireta.value != '') ||
	    (document.CadMaterialPrecoManterSelecionar.SubclasseDescricaoDireta.value != '') ) {
		if( document.CadMaterialPrecoManterSelecionar.Grupo ){
	   	document.CadMaterialPrecoManterSelecionar.Grupo.value = '';
	 	}
		if (document.CadMaterialPrecoManterSelecionar.Classe ){
	 	  document.CadMaterialPrecoManterSelecionar.Classe.value = '';
	 	}
		if( document.CadMaterialPrecoManterSelecionar.Botao ){
  		 	document.CadMaterialPrecoManterSelecionar.Botao.value = 'Validar';
  	}
	}
	if( document.CadMaterialPrecoManterSelecionar.MaterialDescricaoFamilia ){
	  	if( document.CadMaterialPrecoManterSelecionar.MaterialDescricaoFamilia.value != '' ){
   				document.CadMaterialPrecoManterSelecionar.Material.value = '';
  		}
	}
  document.CadMaterialPrecoManterSelecionar.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadMaterialPrecoManterSelecionar.php" method="post" name="CadMaterialPrecoManterSelecionar">
<br><br><br><br><br>
<table cellpadding="3" border="0" width="100%" summary="">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
  
     	 <a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais > Preço > Manter
    
      
      
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
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" class="textonormal"  bgcolor="#FFFFFF" summary="">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="2">
	           MANTER - PREÇOS DE MATERIAIS
          </td>
        </tr>
        <tr>
          <td class="textonormal" colspan="2">
             <p align="justify">
             Para atualizar/excluir um Preço de Material já cadastrado, selecione os dados abaixo para efetuar a pesquisa.
             </p>
          </td>
        </tr>
        <tr>
           <td align="center" bgcolor="#DCEDF7" class="titulo3" colspan="2">PESQUISA DIRETA</td>
        </tr>
        <tr>
          <td colspan="2">
            <table border="0" width="100%" summary="">
      				<tr>
        				<td class="textonormal" bgcolor="#DCEDF7" width="31%">Subclasse</td>
        				<td class="textonormal" colspan="2">
        					<select name="OpcaoPesquisaSubClasse" class="textonormal">
        						<option value="0">Código Reduzido</option>
        						<option value="1">Descrição contendo</option>
        						<option value="2">Descrição iniciada por</option>
        					</select>
 	        				<input type="text" name="SubclasseDescricaoDireta" size="10" maxlength="10" class="textonormal">
           	      <a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0" alt="0"></a>
        				</td>
        			</tr>
	            <tr>
	              <td class="textonormal" bgcolor="#DCEDF7" width="34%">Material</td>
	              <td class="textonormal" colspan="2">
	              	<select name="OpcaoPesquisaMaterial" class="textonormal">
	              		<option value="0">Código Reduzido</option>
	              		<option value="1">Descrição contendo</option>
	              		<option value="2">Descrição iniciada por</option>
	              	</select>
         	        <input type="text" name="MaterialDescricaoDireta" size="10" maxlength="10" class="textonormal">
           	      <a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0" alt="0"></a>
	              </td>
	            </tr>
            </table>
          </td>
        </tr>
        <tr>
           <td align="center" bgcolor="#DCEDF7" class="titulo3" colspan="2">PESQUISA POR FAMILIA</td>
        </tr>
        <tr>
          <td colspan="2">
            <table border="0" width="100%" summary="">
	            <tr>
	              <td class="textonormal" bgcolor="#DCEDF7" >Tipo de Material</td>
	              <td class="textonormal">
	              	<input type="radio" name="TipoMaterial" value="C" onClick="javascript:remeter();" <?php if( $TipoMaterial == "C" ){ echo "checked"; } ?> > Consumo
	              	<input type="radio" name="TipoMaterial" value="P" onClick="javascript:remeter();" <?php if( $TipoMaterial == "P" ){ echo "checked"; }?> > Permanente
	              </td>
	            </tr>
	            <tr>
	              <td class="textonormal" bgcolor="#DCEDF7">Grupo </td>
	              <td class="textonormal">
	              	<select name="Grupo" class="textonormal" onChange="javascript:remeter();">
	              		<option value="">Selecione um Grupo...</option>
	              		<?php
                	  # Mostra os grupos cadastrados #
										if( $TipoMaterial == "C" or $TipoMaterial == "P") {
			                	$db   = Conexao();
												$sql  = "SELECT CGRUMSCODI,EGRUMSDESC FROM SFPC.TBGRUPOMATERIALSERVICO ";
												$sql .= " WHERE FGRUMSTIPO = 'M' AND FGRUMSTIPM = '$TipoMaterial' AND FGRUMSSITU = 'A' ";
		                		$sql .= " ORDER BY EGRUMSDESC";
		                		$res  = $db->query($sql);
		                		if( PEAR::isError($res) ){
												    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														while( $Linha = $res->fetchRow() ){
				          	      			$Descricao = substr($Linha[1],0,65);
				          	      			if( $Linha[0] == $Grupo ){
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
	            <tr>
	              <td class="textonormal" bgcolor="#DCEDF7">Classe</td>
	              <td class="textonormal">
	              	<select name="Classe" class="textonormal" onChange="javascript:remeter();">
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
           		<?php if( $Grupo != "" and $Classe != "" ){ ?>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Subclasse </td>
                <td class="textonormal">
            	    <select name="Subclasse" class="textonormal" onChange="javascript:remeter();">
            		    <option value="">Selecione uma Subclasse...</option>
            		    <?php
            		   	$db   = Conexao();										
                    $sql   = "  SELECT SUB.CSUBCLSEQU, SUB.ESUBCLDESC ";
                    $sql  .= "  FROM SFPC.TBGRUPOMATERIALSERVICO GRU,SFPC.TBCLASSEMATERIALSERVICO CLA, ";
                    $sql  .= "       SFPC.TBSUBCLASSEMATERIAL SUB ";
                    $sql  .= " WHERE SUB.CGRUMSCODI = CLA.CGRUMSCODI ";
                    $sql  .= "   AND SUB.CCLAMSCODI = CLA.CCLAMSCODI AND CLA.CGRUMSCODI = GRU.CGRUMSCODI ";
                    $sql  .= "   AND GRU.FGRUMSSITU = 'A' AND CLA.FCLAMSSITU = 'A' AND SUB.FSUBCLSITU = 'A' ";
                    $sql  .= "   AND SUB.CGRUMSCODI = '$Grupo' AND SUB.CCLAMSCODI = '$Classe' ";
                    $sql  .= "    ORDER BY ESUBCLDESC ";                    
										$res  = $db->query($sql);
							  		if( PEAR::isError($res) ){
									 		 	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												while( $Linha = $res->fetchRow() ){
          	      					$Descricao = substr($Linha[1],0,75);
          	      					if( $Linha[0] == $Subclasse){
					    	      					echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
			      	      				}else{
																echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
			      	      				}
	                			}
										}
              			$db->disconnect();
	              		?>
            			</select>
                </td>
              </tr>
	            <?php
              }
              if( $Grupo != "" and $Classe != "" and $Subclasse != ""){
              ?>
         			<tr>
                <td class="textonormal" bgcolor="#DCEDF7">Material </td>
                <td class="textonormal">
                 	<select name="Material" class="textonormal" onChange="javascript:remeter();">
             	    	<option value="0">Selecione um Material...</option>
            		    <?php
            						$db  = Conexao();
												$sql = "SELECT CMATEPSEQU, EMATEPDESC FROM SFPC.TBMATERIALPORTAL ";
												$sql.= " WHERE CSUBCLSEQU = $Subclasse ORDER BY EMATEPDESC";
												$res = $db->query($sql);
							  				if( PEAR::isError($res) ){
									  				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														while( $Linha = $res->fetchRow() ){
          	      							$Descricao = substr($Linha[1],0,40);
          	      							if( $Linha[0] == $Material){
					    	      							echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
			      	      						}else{
																		echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
			      	      						}
	                					}
												}
              					$db->disconnect();
            				?>
            			</select>
         	      	<input type="text" name="MaterialDescricaoFamilia" size="10" maxlength="10" onChange="javascript:remeter();" class="textonormal">
           	      <a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0" alt="0"></a>
						    	<input type="checkbox" name="chkmaterial" onClick="javascript:checktodos();">Todos
            		</td>
          		</tr>
              <?php } ?>
            </table>
          </td>
        </tr>
     		<tr>
        	<td colspan="2" align="right">
	       		<input type="button" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
						<input type="hidden" name="Botao" value="">
					</td>
      	</tr>
 				<?php
			  if( $MaterialDescricaoDireta != "" ){
				    if( $OpcaoPesquisaMaterial == 0 ){
						    if( !SoNumeros($MaterialDescricaoDireta) ){ $sqlgeral = ""; }
				    }
			  }
			  if( $SubclasseDescricaoDireta != "" ){
					  if( $OpcaoPesquisaSubClasse == 0 ){
						  	if( !SoNumeros($SubclasseDescricaoDireta) ){ $sqlgeral = ""; }
					  }
			  }
				if($sqlgeral != "" and $Mens == 0){
						if( ( $MaterialDescricaoDireta != "" or $SubclasseDescricaoDireta != "" )
					 			or ( $Material != "" or $MaterialDescricaoFamilia != "" or $ChkMaterial != "" ) ){
	            	$db     = Conexao();
							 	$res    = $db->query($sqlgeral);
	  						$qtdres = $res->numRows();
								if( PEAR::isError($res) ){
		  					  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
									  echo "			   <tr>\n";
								    echo "				   <td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"2\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
										echo "			   </tr>\n";

									  if( $qtdres > 0 ){
										    echo "	       <tr>\n";
									      echo "	         <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"90%\">DESCRIÇÃO DO MATERIAL</td>\n";
												echo "	         <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\" align=\"center\">CÓD.RED.</td>\n";									      									      
												echo "	       </tr>\n";
												while( $row	= $res->fetchRow() ){
														$MaterialSequ      = $row[0];
														$MaterialDescricao = $row[1];
														echo "			<tr>\n";
														echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"90%\">\n";
														$Url = "CadMaterialPrecoManter.php?Material=$MaterialSequ";
														if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
														echo "					<a href=\"$Url\"><font color=\"#000000\">$MaterialDescricao</font></a>";
														echo "				</td>\n";
														echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\" width=\"10%\">\n";
														echo "					$MaterialSequ";
														echo "				</td>\n";
														echo "			</tr>\n";
												}
												$db->disconnect();
										}else{
												echo "<tr>\n";
												echo "	<td valign=\"top\" colspan=\"2\" class=\"textonormal\" bgcolor=\"FFFFFF\">\n";
												echo "		Pesquisa sem Ocorrências.\n";
												echo "	</td>\n";
												echo "</tr>\n";
										}
								}
						}
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
