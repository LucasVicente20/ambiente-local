<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabClasseMaterialServicoSelecionar.php
# Autor:    Rossana Lira
# Data:     02/02/05
# Objetivo: Programa de Manutenção de Classe de Material e Serviço
#---------------------
# Alterado:	Ariston Cordeiro
# Data:			30/03/2009	- Trocado botão de procura por imagem de lupa
# Alterado:	Ariston Cordeiro
# Data:			11/03/2010	- Corrigindo código da classe no link que chama para alteração de classe
#-----------------
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/materiais/TabClasseMaterialServicoAlterar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$TipoGrupo	 	   = $_POST['TipoGrupo'];
		$TipoMaterial	   = $_POST['TipoMaterial'];
		$Grupo	 		     = $_POST['Grupo'];
		$Classe          = $_POST['Classe'];
		$Critica     	   = $_POST['Critica'];
		$ClasseDescricao = strtoupper2(trim($_POST['txtclasse']));
		$ChkClasse       = $_POST['chkclasse'];
		/**/
		$sql  = "SELECT GRU.CGRUMSCODI,CLA.CCLAMSCODI,CLA.ECLAMSDESC ";
 	  $from = " FROM SFPC.TBGRUPOMATERIALSERVICO GRU, SFPC.TBCLASSEMATERIALSERVICO CLA ";
		$where = " WHERE CLA.CGRUMSCODI = GRU.CGRUMSCODI AND GRU.FGRUMSSITU = 'A' ";
		/* Verifica se o Tipo de Grupo foi escolhido */
    if ($TipoGrupo != '') {
				$where .= " AND GRU.FGRUMSTIPO = '$TipoGrupo' ";
    }
		/* Verifica se o Tipo de Material foi escolhido */
    if ($TipoMaterial != '') {
		  	$where .= " AND GRU.FGRUMSTIPM = '$TipoMaterial' ";
    }
    /* Verifica se o Grupo foi escolhido */
    if ($Grupo != '') {
 	    	$where .= " AND GRU.CGRUMSCODI = '$Grupo' ";
    }
    /* Verifica se a Classe foi escolhida */
    if ($Classe != '0' and $Classe != '') {
		  	$where .= " AND CLA.CGRUMSCODI = '$Grupo' AND CLA.CCLAMSCODI = '$Classe' ";
    }
    /* Se foi digitado algo na caixa de texto da classe */
    if ($ClasseDescricao != '') {
    		$where .= " AND CLA.ECLAMSDESC LIKE '$ClasseDescricao%' ";
    }
    /* Gera o SQL com a concatenação das variaveis $sql,$from,$where */
		$sqlgeral = $sql.$from.$where;
		/**/
}else{
		$Critica     = $_GET['Critica'];
		$Mensagem    = urldecode($_GET['Mensagem']);
		$Mens        = $_GET['Mens'];
		$Tipo        = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Critica dos Campos #
if( $Critica == 1 ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( $Grupo == "" ){
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.Classe.Grupo.focus();\" class=\"titulo2\">Grupo</a>";
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
function remeter(){
	document.Classe.submit();
}
function checktodos(){
	document.Classe.Classe.value = 0;
	document.Classe.submit();
}
function validapesquisa(){
	if (document.Classe.txtclasse.value != '') {
  	document.Classe.Classe.value = 0;
	  document.Classe.submit();
  }
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabClasseMaterialServicoSelecionar.php" method="post" name="Classe">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais > Classe > Manter
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
	           MANTER - CLASSE DE MATERIAL E SERVIÇO
          </td>
        </tr>
        <tr>
          <td class="textonormal" colspan="2">
             <p align="justify">
             Para atualizar/excluir uma Classe de Material ou Serviço já cadastrada, selecione a Classe e clique no botão "Selecionar".
             </p>
          </td>
        </tr>
        <tr>
          <td colspan="2">
            <table summary="">
	            <tr>
	              <td class="textonormal" bgcolor="#DCEDF7" >Tipo de Grupo</td>
	              <td class="textonormal">
	              	<input type="radio" name="TipoGrupo" value="M" onClick="javascript:document.Classe.Critica.value=0;javascript:document.Classe.Grupo.value=0;document.Classe.submit();" <?php if( $TipoGrupo == "M" ){ echo "checked"; } ?> > Material
	              	<input type="radio" name="TipoGrupo" value="S" onClick="javascript:document.Classe.Critica.value=0;javascript:document.Classe.Grupo.value=0;document.Classe.submit();" <?php if( $TipoGrupo == "S" ){ echo "checked"; }?> > Serviço
	              </td>
	            </tr>
	            <?php if ($TipoGrupo == "M") { ?>
		            <tr>
		              <td class="textonormal" bgcolor="#DCEDF7" >Tipo de Material</td>
		              <td class="textonormal">
		              	<input type="radio" name="TipoMaterial" value="C" onClick="javascript:document.Classe.Critica.value=0;document.Classe.submit();" <?php if( $TipoMaterial == "C" ){ echo "checked"; } ?> > Consumo
		              	<input type="radio" name="TipoMaterial" value="P" onClick="javascript:document.Classe.Critica.value=0;document.Classe.submit();" <?php if( $TipoMaterial == "P" ){ echo "checked"; }?> > Permanente
		              </td>
		            </tr>
 		          <?php } else {
 		          				$TipoMaterial = "";
 		          } ?>
	            <tr>
	              <td class="textonormal" bgcolor="#DCEDF7">Grupo </td>
	              <td class="textonormal">
                  <input type="hidden" name="Critica" value="1">
	              	<select name="Grupo" class="textonormal" onChange="javascript:remeter();">
	              		<option value="">Selecione um Grupo...</option>
	              		<?php
											if( $TipoGrupo == "M" or $TipoGrupo == "S") {
			                	$db     = Conexao();
												if( $TipoMaterial == "C" or $TipoMaterial == "P") {
													$sql 		= "SELECT CGRUMSCODI,EGRUMSDESC FROM SFPC.TBGRUPOMATERIALSERVICO ";
													$sql   .= "WHERE  FGRUMSTIPO = 'M' AND FGRUMSTIPM = '$TipoMaterial' AND FGRUMSSITU = 'A' ";
			                		$sql   .= "ORDER  BY EGRUMSDESC";
			                		$result = $db->query($sql);
			                		if (PEAR::isError($result)) {
													    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}else{
															while( $Linha = $result->fetchRow() ){
				          	      			$Descricao   = substr($Linha[1],0,40);
				          	      			if( $Linha[0] == $Grupo ){
									    	      			echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
							      	      		}else{
									    	      			echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
							      	      		}
							      	      	}
						              }
			                	}	else {
													if( $TipoGrupo == "S" ){
				                	  # Mostra os grupos cadastrados #
				                		$db     = Conexao();
														$sql 		= "SELECT CGRUMSCODI,EGRUMSDESC FROM SFPC.TBGRUPOMATERIALSERVICO ";
														$sql   .= "WHERE FGRUMSTIPO = 'S' AND FGRUMSSITU = 'A' ORDER BY EGRUMSDESC";
				                		$result = $db->query($sql);
				                		if (PEAR::isError($result)) {
														    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
																while( $Linha = $result->fetchRow() ){
					          	      			$Descricao   = substr($Linha[1],0,40);
					          	      			if( $Linha[0] == $Grupo ){
										    	      			echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
								      	      		}else{
										    	      			echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
								      	      		}
							                	}
							              }
				  	              }
				  	            }
				  	            $db->disconnect();
		  	              }
      	            ?>
	              	</select>
	              </td>
	            </tr>
           		<?php if( $Grupo != "" ){ ?>
	            <tr>
	              <td class="textonormal" bgcolor="#DCEDF7">Classe </td>
	              <td class="textonormal">
	              	<select name="Classe" class="textonormal" <?php echo $Classe;?> onChange="javascript:remeter();">
	              		<option value="0">Selecione uma Classe...</option>
	              		<?php
			              		$db  = Conexao();
												$sql = "SELECT CCLAMSCODI,ECLAMSDESC FROM SFPC.TBCLASSEMATERIALSERVICO WHERE CGRUMSCODI = $Grupo ";
												$sql.= "ORDER BY 2";
												$res = $db->query($sql);
											  if( PEAR::isError($res) ){
													  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														while( $Linha = $res->fetchRow() ){
				          	      			$Descricao = substr($Linha[1],0,40);
				          	      			if( $Linha[0] == $Classe){
									    	      			echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
							      	      		}else{
																		echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
							      	      		}
					                	}
												}
		  	              	$db->disconnect();
	              		?>
	              	</select>
             	    <input type="text" name="txtclasse" size="5" maxlength="5" onChange="javascript:remeter();" class="textonormal">
             	    <a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0" alt="0"></a>
								  <input type="checkbox" name="chkclasse" onClick="javascript:checktodos();">Todas
	              </td>
	            </tr>
						  <?php } ?>
            </table>
          </td>
        </tr>

 				<?php
 					 if ($sqlgeral != '') {
 						 if (($Classe != '0' and $Classe != '') or ($ClasseDescricao != '') or ($ChkClasse != '')) {
              	$db = Conexao();           	
							 	$res  = $db->query($sqlgeral);
    						$qtdres = $res->numRows();
								if( PEAR::isError($res) ){
		  					  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
								  if ($qtdres > 0) {
								    echo "			   <tr>\n";
								    echo "				   <td align=\"center\" bgcolor=\"#DCEDF7\" colspan=\"5\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
										echo "			   </tr>\n";
							      echo "	       <tr>\n";
							      echo "	         <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\" align=\"center\">CÓD.RED.</td>\n";
							      echo "	         <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"90%\">DESCRIÇÃO DA CLASSE</td>\n";
								    echo "	       </tr>\n";
										while( $row	= $res->fetchRow() ){
										  $GrupoCodigo     = $row[0];
										  $ClasseCodigo    = $row[1];
										  $ClasseDescricao = $row[2];
									    echo "      <tr>\n";
									    echo "		     <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\" width=\"10%\">\n";
										  echo "		       $ClasseCodigo";
									    echo "	       </td>\n";
									    echo "		     <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"90%\">\n";
									    $Url = "TabClasseMaterialServicoAlterar.php?Grupo=$Grupo&ClasseCodigo=$ClasseCodigo";
											if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
											echo "		   		 <a href=\"$Url\"><font color=\"#000000\">$ClasseDescricao</font></a>";
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
document.Classe.Grupo.focus();
//-->
</script>
</body>
</html>
