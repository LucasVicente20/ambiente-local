<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadMaterialIncluirSelecionar.php
# Autor:    Rossana Lira/Altamiro Pedrosa
# Data:     03/08/05
# Alterado: Rodrigo Melo
# Data:     22/09/2009 - Alterando o nome das tabelas SFPC.TBPREMATERIAL e SFPC.TBPREMATERIALTIPOSITUACAO para SFPC.TBPREMATERIALSERVICO e BPREMATERIALSERVICOTIPOSITUACAO, respectivamente (CR 2749).
# Objetivo: Programa de Manutenção de Pré-Material
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/materiais/CadPreMaterialAnalisar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Situacao = $_POST['Situacao'];
		$Botao    = $_POST['Botao'];
}else{
		$Critica     = $_GET['Critica'];
		$Mensagem    = urldecode($_GET['Mensagem']);
		$Mens        = $_GET['Mens'];
		$Tipo        = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Sql para montagem da janela #
$sql  = "SELECT DISTINCT(PRE.CPREMACODI), PRE.EPREMADESC, PRE.DPREMACADA, ";
$sql .= "       GRU.CGRUMSCODI, CLA.CCLAMSCODI, PRESIT.EPREMSDESC, ";
$sql .= "       GRUEMP.EGREMPDESC, USUPOR.EUSUPORESP ";
$sql .= "  FROM SFPC.TBPREMATERIALSERVICO PRE,SFPC.TBGRUPOMATERIALSERVICO GRU,SFPC.TBCLASSEMATERIALSERVICO CLA, ";
$sql .= "       SFPC.TBPREMATERIALSERVICOTIPOSITUACAO PRESIT,SFPC.TBUSUARIOPORTAL USUPOR,SFPC.TBGRUPOEMPRESA GRUEMP ";
$sql .= " WHERE GRU.CGRUMSCODI = PRE.CGRUMSCODI AND CLA.CCLAMSCODI = PRE.CCLAMSCODI ";
$sql .= "   AND PRE.CPREMSCODI = PRESIT.CPREMSCODI AND PRE.CGREMPCODI = GRUEMP.CGREMPCODI ";
$sql .= "   AND PRE.CUSUPOCODI = USUPOR.CUSUPOCODI AND PRE.CGREMPCODI = USUPOR.CGREMPCODI ";

# Verifica qual o tipo da situação #
if( $Situacao != "" ){
		$where .= " AND PRESIT.CPREMSCODI = $Situacao ";
}

# Gera o SQL com a concatenação das variaveis $sql,$from,$where #
$sqlgeral = $sql.$from.$where;

if( $Botao == "Limpar" ){
		header("location: CadPreMaterialSelecionar.php");
		exit;
}
?>
<html>
<?
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
function remeter(){
	document.CadPreMaterialSelecionar.submit();
}
function enviar(valor){
	document.CadPreMaterialSelecionar.Botao.value=valor;
	document.CadPreMaterialSelecionar.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadPreMaterialSelecionar.php" method="post" name="CadPreMaterialSelecionar">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais > Pré-Cadastro > Análise
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
	  <td width="150"></td>
	  <td align="left"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
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
	           ANÁLISE - PRÉ-CADASTRO DE MATERIAIS
          </td>
        </tr>
        <tr>
          <td class="textonormal" colspan="4">
             <p align="justify">
             Para efetuar a análise do pré-cadastro de Material, selecione a Situação e depois clique na descrição do material desejado.
             </p>
          </td>
        </tr>
        <tr>
          <td colspan="4">
            <table width="100%" summary="">
	            <tr>
	              <td class="textonormal" bgcolor="#DCEDF7" colspan="4" width="30%">Situação</td>
	              <td class="textonormal" >
	              	<select name="Situacao" class="textonormal" onChange="javascript:remeter();">
										<?php if( $Situacao == "" ){ ?>
										<option value="">Selecione uma Situação...</option>
										<?php
										}
	                	$db   = Conexao();
										$sql  = "SELECT CPREMSCODI, EPREMSDESC FROM SFPC.TBPREMATERIALSERVICOTIPOSITUACAO ";
										$sql .= " ORDER BY CPREMSCODI";
                		$result = $db->query($sql);
                		if (PEAR::isError($result)) {
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												while( $Linha = $result->fetchRow() ){
	          	      				$Descricao = substr($Linha[1],0,40);
	          	      				if( $Linha[0] == $Situacao ){
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
            </table>
          </td>
        </tr>
	  		<tr>
	      	<td colspan="4" align="right">
	       		<input type="button" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
						<input type="hidden" name="Botao" value="">
					</td>
	    	</tr>
 				<?
 				# Exibe o Resultado da Pesquisa #
 				if( $Situacao != "" ){
				 		if( $sqlgeral != "" ){
            		$db     = Conexao();
						 		$res    = $db->query($sqlgeral);
  							$qtdres = $res->numRows();
								if( PEAR::isError($res) ){
	  					 		 	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
							  		echo "			   <tr>\n";
					    			echo "				   <td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"4\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
										echo "			   </tr>\n";
							  		if( $qtdres > 0 ){
							    			echo "	       <tr>\n";
						      			echo "	         <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"50%\">DESCRIÇÃO DO MATERIAL</td>\n";
						      			echo "	         <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"12%\" align=\"center\">DATA</td>\n";
							    			echo "	         <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"25%\">SOLICITANTE</td>\n";
							    			echo "	         <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"13%\">SITUAÇÃO</td>\n";
							    			echo "	       </tr>\n";
												while( $row	= $res->fetchRow() ){
									  				$MaterialCodigo       = $row[0];
									  				$MaterialDescricao    = $row[1];
									  				$DataCadastro         = DataBarra($row[2]);
									  				$GrupoCodigo          = $row[3];
									  				$ClasseCodigo         = $row[4];
									  				$TipoSitDescricao     = $row[5];
									  				$GrupoEmpDescricao    = substr($row[6],0,30);
									  				$ResponsavelDescricao = substr($row[7],0,30);
   													echo"      <tr>\n";
								    				echo "		     <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"50%\">\n";
								    				if ($Situacao == '1') {
														$Url = "CadPreMaterialAnalisar.php?Grupo=$GrupoCodigo&Classe=$ClasseCodigo&Material=$MaterialCodigo";
														if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
											  				echo "	 		 <a href=\"CadPreMaterialAnalisar.php?Grupo=$GrupoCodigo&Classe=$ClasseCodigo&Material=$MaterialCodigo\"><font color=\"#000000\">$MaterialDescricao</font></a>";
														}else{
											  				echo "	 		 $MaterialDescricao";
									  				}
								    				echo "	       </td>\n";
								    				echo "		     <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\" width=\"12%\">\n";
									  				echo "		       $DataCadastro";
								    				echo "	       </td>\n";
								    				echo "		     <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"25%\">\n";
									  				echo "		       $GrupoEmpDescricao / $ResponsavelDescricao";
								    				echo "	       </td>\n";
								    				echo "		     <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"13%\">\n";
									  				echo "		       $TipoSitDescricao";
								    				echo "	       </td>\n";
								    				echo "	   </tr>\n";
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
