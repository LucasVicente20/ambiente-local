<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabSubclasseMaterialSelecionar.php
# Autor:    Roberta Costa/Altamiro Pedrosa
# Data:     06/06/05
# Objetivo: Programa de Manutenção de Subclasse de Material
# Alterado: Rossana Lira
# Data    : 31/10/2007 - Aumentar exibição do grupo e da classe
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/materiais/TabSubclasseMaterialAlterar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
		$Botao    	 	      = $_POST['Botao'];
		$TipoMaterial	      = $_POST['TipoMaterial'];
		$Grupo	 		        = $_POST['Grupo'];
		$Classe             = $_POST['Classe'];
		$Subclasse          = $_POST['Subclasse'];
		$SubclasseDescricao = strtoupper2(trim($_POST['SubclasseDescricao']));
		$ChkSubclasse       = $_POST['chksubclasse'];
		$Critica            = $_POST['Critica'];
}else{
		$Mensagem    = urldecode($_GET['Mensagem']);
		$Mens        = $_GET['Mens'];
		$Tipo        = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Faz a pesquisa da Subclasse #
$sql    = "SELECT GRU.CGRUMSCODI,CLA.CCLAMSCODI,SUB.CSUBCLCODI,SUB.ESUBCLDESC ";
$from   = "  FROM SFPC.TBGRUPOMATERIALSERVICO GRU, SFPC.TBCLASSEMATERIALSERVICO CLA, SFPC.TBSUBCLASSEMATERIAL SUB ";
$where  = " WHERE SUB.CGRUMSCODI = CLA.CGRUMSCODI AND SUB.CCLAMSCODI = CLA.CCLAMSCODI ";
$where .= "   AND CLA.CGRUMSCODI = GRU.CGRUMSCODI AND GRU.FGRUMSSITU = 'A' ";
$where .= "   AND CLA.FCLAMSSITU = 'A' ";

# Verifica se o Tipo de Material foi escolhido #
if( $TipoMaterial != "" ){
  	$where .= " AND GRU.FGRUMSTIPM = '$TipoMaterial' ";
}

# Verifica se o Grupo foi escolhido #
if( $Grupo != "" ){
  	$where .= " AND GRU.CGRUMSCODI = $Grupo ";
}

# Verifica se a Classe foi escolhida #
if( $Classe != "" ){
  	$where .= " AND CLA.CGRUMSCODI = $Grupo AND CLA.CCLAMSCODI = $Classe ";
}

# Verifica se a SubClasse foi escolhida #
if( $Subclasse != 0 and $Subclasse != "" ){
  	$where .= " AND SUB.CSUBCLSEQU = $Subclasse ";
}

# Se foi digitado algo na caixa de texto da subclasse #
if( $SubclasseDescricao != "" ){
		$where .= " AND SUB.ESUBCLDESC LIKE '$SubclasseDescricao%' ";
}

# Gera o SQL com a concatenação das variaveis $sql,$from,$where #
$sqlgeral = $sql.$from.$where;

if( $Botao == "Limpar" ){
		header("location: TabSubclasseMaterialSelecionar.php");
		exit;
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
	document.Subclasse.Botao.value = valor;
	document.Subclasse.submit();
}
function remeter(){
	document.Subclasse.submit();
}
function validapesquisa(){
	if( document.TabSubclasseMaterialSelecionar.SubclasseDescricao ){
			if( document.Subclasse.SubclasseDescricao.value != '' ){
  				document.Subclasse.Subclasse.value = '';
	  			document.Subclasse.submit();
	  	}
	}
}
function checktodos(){
	document.Subclasse.Subclasse.value = 0;
	document.Subclasse.submit();
}

<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabSubclasseMaterialSelecionar.php" method="post" name="Subclasse">
<br><br><br><br><br>
<table cellpadding="3" width="100%" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais > Subclasse > Manter
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
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="2">
	           MANTER - SUBCLASSE DE MATERIAL
          </td>
        </tr>
        <tr>
          <td class="textonormal" colspan="2">
             <p align="justify">
             Para atualizar/excluir uma Subclasse de Material já cadastrada, selecione os dados abaixo e clique no botão "Selecionar".
             </p>
          </td>
        </tr>
			  <?php if( $Critica == 0 ){ $TipoMaterial = "C"; } ?>
        <tr>
          <td colspan="2">
            <table border="0" width="100%" summary="">
	            <tr>
	              <td class="textonormal" bgcolor="#DCEDF7" >Tipo de Material</td>
	              <td class="textonormal">
	              	<input type="radio" name="TipoMaterial" value="C" onClick="javascript:document.Subclasse.Grupo.value = 0;javascript:document.Subclasse.Classe.value = 0;javascript:remeter(); " <?php if( $TipoMaterial == "C" ){ echo "checked"; } ?> > Consumo
	              	<input type="radio" name="TipoMaterial" value="P" onClick="javascript:document.Subclasse.Grupo.value = 0;javascript:document.Subclasse.Classe.value = 0;javascript:remeter(); " <?php if( $TipoMaterial == "P" ){ echo "checked"; }?> > Permanente
         	  			<input type="hidden" name="Critica" value="1">
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
												$sql .= " WHERE FGRUMSTIPO = 'M' AND FGRUMSTIPM = '$TipoMaterial' ";
												$sql .= "   AND FGRUMSSITU = 'A' ";
		                		$sql .= " ORDER BY EGRUMSDESC";
		                		$res  = $db->query($sql);
		                		if( PEAR::isError($res) ){
												    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														while( $Linha = $res->fetchRow() ){
				          	      			$Descricao = substr($Linha[1],0,80);
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
	              <td class="textonormal" bgcolor="#DCEDF7">Classe </td>
	              <td class="textonormal">
	              	<select name="Classe" class="textonormal" onChange="javascript:remeter();">
	              		<option value="">Selecione uma Classe...</option>
	              		<?php
	              		if( $Grupo != "" ){
			              		$db   = Conexao();
												$sql  = "SELECT CCLAMSCODI, ECLAMSDESC ";
												$sql .= "  FROM SFPC.TBCLASSEMATERIALSERVICO ";
												$sql .= " WHERE CGRUMSCODI = $Grupo AND FCLAMSSITU = 'A' ";
												$sql .= " ORDER BY ECLAMSDESC";
												$res  = $db->query($sql);
											  if( PEAR::isError($res) ){
													  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														while( $Linha = $res->fetchRow() ){
				          	      			$Descricao = substr($Linha[1],0,100);
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
	              <td class="textonormal" valign="bottom">
	              	<select name="Subclasse" class="textonormal" onChange="javascript:remeter();">
	              		<option value="0">Selecione uma Subclasse...</option>
	              		<?php
	              		$db   = Conexao();
										$sql  = "SELECT CSUBCLSEQU, ESUBCLDESC FROM SFPC.TBSUBCLASSEMATERIAL";
										$sql .= " WHERE CGRUMSCODI = '$Grupo' and CCLAMSCODI = '$Classe' ";
										$sql .= " ORDER BY ESUBCLDESC";
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
             	    <input type="text" name="SubclasseDescricao" size="5" maxlength="5" onChange="javascript:remeter();" class="textonormal">
             	    <a href="javascript:enviar('Enviar');"><img src="../midia/lupa.gif" border="0"></a>
								  <input type="checkbox" name="chksubclasse" onClick="javascript:checktodos();">Todas
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
				# Exibe o Resultado da Pesquisa #
				if( $sqlgeral != "" ){
				 		if( ( $Subclasse != 0 and $Subclasse != "" ) or ( $SubclasseDescricao != "" ) or ( $ChkSubclasse != "" ) ){
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
									      echo "	         <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\" align=\"center\">CÓD.RED.</td>\n";
									      echo "	         <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"90%\">DESCRIÇÃO DA SUBCLASSE</td>\n";
										    echo "	       </tr>\n";
												while( $row	= $res->fetchRow() ){
													  $GrupoCodigo     = $row[0];
													  $ClasseCodigo    = $row[1];
													  $SubClasseCodigo = $row[2];
													  $SubClasseDesc   = $row[3];
												    echo "      <tr>\n";
												    echo "		     <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\" width=\"10%\">\n";
													  echo "		       $SubClasseCodigo";
												    echo "	       </td>\n";
												    echo "		     <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"90%\">\n";
												    $Url = "TabSubclasseMaterialAlterar.php?Grupo=$GrupoCodigo&Classe=$ClasseCodigo&Subclasse=$SubClasseCodigo";
														if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
														echo "		   		 <a href=\"$Url\"><font color=\"#000000\">$SubClasseDesc</font></a>";
												    echo "	       </td>\n";
												    echo "	    </tr>\n";
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
<script language="javascript" type="">
<!--
document.Subclasse.Grupo.focus();
//-->
</script>
</body>
</html>
