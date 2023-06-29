<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadMaterialIncluirSelecionar.php
# Autor:    Rossana Lira/Altamiro Pedrosa
# Data:     03/08/05
# Alterado: Rodrigo Melo
# Data:     09/04/2008 - Alteração do texto informativo para o usuário.
# Alterado: Rodrigo Melo
# Data:     20/08/2009 - Alteração para inserir o pré-cadastro de serviços
# Objetivo: Programa de Manutenção de Pré-Material
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso("/materiais/CadPreMaterialIncluir.php");
AddMenuAcesso("/materiais/CadPreMaterialAnalisar.php");

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao    	 	   = $_POST['Botao'];
		$TipoMaterial	   = $_POST['TipoMaterial'];
		$TipoGrupo	          = $_POST['TipoGrupo'];
		$Grupo	 		     = $_POST['Grupo'];
		$Classe          = $_POST['Classe'];
		$Critica     	   = $_POST['Critica'];
		$ClasseDescricao = strtoupper2(trim($_POST['txtclasse']));
		$ChkClasse       = $_POST['chkclasse'];
}else{
		$Critica     = $_GET['Critica'];
		$Mensagem    = urldecode($_GET['Mensagem']);
		$Mens        = $_GET['Mens'];
		$Tipo        = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

//Função para limpar variárveis
function limparVariaveis(){
	$Grupo = "";
	$Classe = "";
}

# Sql para montagem da janela #
$sql    = "SELECT GRU.CGRUMSCODI, CLA.CCLAMSCODI, CLA.ECLAMSDESC ";
$from   = "  FROM SFPC.TBGRUPOMATERIALSERVICO GRU, SFPC.TBCLASSEMATERIALSERVICO CLA ";
$where  = " WHERE CLA.CGRUMSCODI = GRU.CGRUMSCODI AND GRU.FGRUMSSITU = 'A' ";
$where .= "   AND CLA.FCLAMSSITU = 'A' ";


# Verifica se o Tipo do grupo (Material ou Serviço) foi escolhido #
if( $TipoGrupo != "" ){
  	$where .= " AND GRU.FGRUMSTIPO = '$TipoGrupo' ";
}

# Verifica se o Tipo de Material (Consumo ou Permanente) foi escolhido #
if( $TipoMaterial != "" ){
  	$where .= " AND GRU.FGRUMSTIPM = '$TipoMaterial' ";
}

# Verifica se o Grupo foi escolhido #
if( $Grupo != "" ){
  	$where .= " AND GRU.CGRUMSCODI = '$Grupo' ";
}

# Verifica se a Classe foi escolhida #
if( $Classe != "" ){
  	$where .= " AND CLA.CGRUMSCODI = '$Grupo' AND CLA.CCLAMSCODI = '$Classe' ";
}

# Se foi digitado algo na caixa de texto da classe #
if( $ClasseDescricao != "" ){
		$where .= " AND CLA.ECLAMSDESC LIKE '$ClasseDescricao%' ";
}

# Gera o SQL com a concatenação das variaveis $sql,$from,$where #
$sqlgeral = $sql.$from.$where;

if( $Botao == "Limpar" ){
		header("location: CadPreMaterialIncluirSelecionar.php");
		exit;
}
if( $Critica == 1 and $Botao != "Limpar" ){
		# Critica dos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( $Grupo == "" ){
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadPreMaterialIncluirSelecionar.Grupo.focus();\" class=\"titulo2\">Grupo</a>";
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
	document.CadPreMaterialIncluirSelecionar.submit();
}
function enviar(valor){
	document.CadPreMaterialIncluirSelecionar.Botao.value=valor;
	document.CadPreMaterialIncluirSelecionar.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadPreMaterialIncluirSelecionar.php" method="post" name="CadPreMaterialIncluirSelecionar">
<br><br><br><br><br>
<table cellpadding="3" border="0" width="100%" summary="">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais/Serv > Pré-Cadastro > Incluir
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
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" class="textonormal" bgcolor="#FFFFFF" summary="">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="2">
	           INCLUIR - PRÉ-CADASTRO DE MATERIAIS
          </td>
        </tr>
          <tr>
          <td class="textonormal" colspan="2">
             <p align="justify">

             Para incluir o pré-cadastro de material/serviço, selecione o tipo de grupo, o tipo de material (caso o tipo de grupo seja material), o grupo e a classe desejada.
             <BR>
             <BR>
             Antes de efetuar o pré-cadastro, o usuário deverá tomar as seguintes precauções:
             <BR>
             <ol>
               <li>Verificar se o material/serviço já está cadastrado no portal de compras;</li>
               <li>Pesquisar no portal de compras qual é a classificação correta para materiais/serviços similares;</li>
               <li>Procurar fornecer descrição técnica clara e exata.</li>
             </ol>

             </p>
          </td>
        </tr>
        <tr>
          <td colspan="2">
            <table width="100%" summary="">



	            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7" width="40%">Tipo de Grupo*</td>
				              <td class="textonormal">

				              	<input type="radio" name="TipoGrupo" value="M" onClick="javascript:document.CadPreMaterialIncluirSelecionar.Grupo.value='';javascript:document.CadPreMaterialIncluirSelecionar.Classe.value='';javascript:document.CadPreMaterialIncluirSelecionar.Critica.value=0;document.CadPreMaterialIncluirSelecionar.submit();" <?php if( $TipoGrupo == "M" ){ echo "checked"; }?> > Material
				              	<input type="radio" name="TipoGrupo" value="S" onClick="javascript:document.CadPreMaterialIncluirSelecionar.Grupo.value='';javascript:document.CadPreMaterialIncluirSelecionar.Classe.value='';javascript:document.CadPreMaterialIncluirSelecionar.Critica.value=0;document.CadPreMaterialIncluirSelecionar.submit();" <?php if( $TipoGrupo == "S" ){ echo "checked"; }?> > Serviço
				              </td>
				            </tr>
				            <?php if ($TipoGrupo == "M") { ?>
					            <tr>
					              <td class="textonormal" bgcolor="#DCEDF7" width="40%">Tipo de Material*</td>
					              <td class="textonormal">
					              	<input type="radio" name="TipoMaterial" value="C" onClick="javascript:document.CadPreMaterialIncluirSelecionar.Grupo.value='';javascript:document.CadPreMaterialIncluirSelecionar.Classe.value='';javascript:document.CadPreMaterialIncluirSelecionar.Critica.value=0;document.CadPreMaterialIncluirSelecionar.submit();" <?php if( $TipoMaterial == "C" ){ echo "checked"; } ?> > Consumo
					              	<input type="radio" name="TipoMaterial" value="P" onClick="javascript:document.CadPreMaterialIncluirSelecionar.Grupo.value='';javascript:document.CadPreMaterialIncluirSelecionar.Classe.value='';javascript:document.CadPreMaterialIncluirSelecionar.Critica.value=0;document.CadPreMaterialIncluirSelecionar.submit();" <?php if( $TipoMaterial == "P" ){ echo "checked"; }?> > Permanente
					              </td>
					            </tr>
			 		          <?php } ?>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Grupo* </td>
				              <td class="textonormal">
				                <input type="hidden" name="Critica" value="1">
				              	<select name="Grupo" class="textonormal" onChange="javascript:remeter();">
				              		<option value="">Selecione um Grupo...</option>
				              		<?php
				              		$db   = Conexao();
													if($TipoGrupo == "S") { //Obtem os grupos de serviços
				              			 # Mostra os grupos cadastrados #
																$sql 		= "SELECT CGRUMSCODI,EGRUMSDESC FROM SFPC.TBGRUPOMATERIALSERVICO ";
																$sql   .= "WHERE FGRUMSTIPO = 'S' ORDER BY EGRUMSDESC";
						                		$result = $db->query($sql);
						                		if (PEAR::isError($result)) {
																    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																}else{
																		while( $Linha = $result->fetchRow() ){
							          	      			$Descricao   = substr($Linha[1],0,75);
							          	      			if( $Linha[0] == $Grupo ){
												    	      			echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
										      	      		}else{
												    	      			echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
										      	      		}
									                	}
									              }
						  	          } else { //Obtem os grupos de materiais (Consumo e permanente)
						  	          		$sql 		= "SELECT CGRUMSCODI,EGRUMSDESC FROM SFPC.TBGRUPOMATERIALSERVICO ";
															$sql   .= "WHERE  FGRUMSTIPO = 'M' AND FGRUMSSITU = 'A' AND FGRUMSTIPM = '$TipoMaterial' ";
					                		$sql   .= "ORDER  BY EGRUMSDESC";
					                		$result = $db->query($sql);
					                		if (PEAR::isError($result)) {
															    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
															}else{
																	while( $Linha = $result->fetchRow() ){
						          	      			$Descricao   = substr($Linha[1],0,75);
						          	      			if( $Linha[0] == $Grupo ){
											    	      			echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
									      	      		}else{
											    	      			echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
									      	      		}
									      	      	}
								              }
									  }
						  	          $db->disconnect();
				              		?>
				              	</select>
				              </td>
				            </tr>


				            <tr>



	              <td class="textonormal" bgcolor="#DCEDF7">Classe* </td>
	              <td class="textonormal">
	              	<select name="Classe" class="textonormal" onChange="javascript:remeter();">
	              		<option value="">Selecione uma Classe...</option>
	              		<?php
	              		if( $Grupo != "" && $Grupo != null ){
			              		$db  = Conexao();
												$sql = "SELECT CCLAMSCODI,ECLAMSDESC FROM SFPC.TBCLASSEMATERIALSERVICO WHERE CGRUMSCODI = $Grupo AND FCLAMSSITU = 'A' ";
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
		  	            }
	              		?>
	              	</select>
	              	<?php if( $Grupo != "" and $Classe == "" ){ ?>
             	      <input type="text" name="txtclasse" size="5" maxlength="5" onChange="javascript:remeter();" class="textonormal">
	           	      <a href="javascript:remeter();"><img src="../midia/lupa.gif" border="0"></a>
								    <input type="checkbox" name="chkclasse" onClick="javascript:remeter();">Todas
								  <?php } ?>
	              </td>
	            </tr>
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
						if( ( $Classe != "" and $Classe != null and $Grupo != "" and $Grupo != null) or ( $ClasseDescricao != "" ) or ( $ChkClasse != "" ) ){
								$db     = Conexao();
								$res    = $db->query($sqlgeral);
								$qtdres = $res->numRows();
								if( PEAR::isError($res) ){
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										echo "			   <tr>\n";
										echo "				   <td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"5\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
										echo "			   </tr>\n";
										if( $qtdres > 0 ){
												echo "	       <tr>\n";
												echo "	         <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\" align=\"center\">CÓDIGO</td>\n";
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
														$Url = "CadPreMaterialIncluir.php?Grupo=$GrupoCodigo&Classe=$ClasseCodigo&TipoGrupo=$TipoGrupo";
														if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
														echo "		   		 <a href=\"CadPreMaterialIncluir.php?Grupo=$GrupoCodigo&Classe=$ClasseCodigo&TipoGrupo=$TipoGrupo\"><font color=\"#000000\">$ClasseDescricao</font></a>";
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
</script>
</body>
</html>
