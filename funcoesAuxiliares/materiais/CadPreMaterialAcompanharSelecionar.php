<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadMaterialAcompanharSelecionar.php
# Autor:    Rossana Lira
# Data:     03/05/06
# Alterado: Rodrigo Melo
# Data:     21/11/2007 - Ajustes necessários para auxiliar o programa CadPreMaterialAcompanhar.php poder realizar a alteração do dia 21/11/2007.
#                                       E alteração para exibir em maiúsculo os dados do banco de dados.
# Alterado: Rodrigo Melo
# Data:     24/08/2009 - Alteração para realizar o acompanhamento de serviços pré-cadastrados
# Alterado: Rodrigo Melo
# Data:     22/09/2009 - Alterando o nome das tabelas SFPC.TBPREMATERIAL e SFPC.TBPREMATERIALTIPOSITUACAO para SFPC.TBPREMATERIALSERVICO e BPREMATERIALSERVICOTIPOSITUACAO, respectivamente (CR 2749).
# Objetivo: Programa de Seleção de Acompanhamento de Pré-Cadastro de Material
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso("/materiais/CadPreMaterialAcompanhar.php");

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Situacao = $_POST['Situacao'];
		$TipoGrupo = $_POST['TipoGrupo'];
		$Botao    = $_POST['Botao'];
		$Todos    = $_POST['Todos'];
}else{
		$Critica     = $_GET['Critica'];
		$Mensagem    = urldecode($_GET['Mensagem']);
		$Mens        = $_GET['Mens'];
		$Tipo        = $_GET['Tipo'];
}


if($Mens == '' || $Mens == null){
	$Mens = 0;
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Limpar" ){
		header("location: CadPreMaterialAcompanharSelecionar.php");
		exit;
} else {
	if($Botao == "Exibir"){
		//Valida Tipo de Grupo
		$Mensagem = "Informe: ";

		if ($TipoGrupo == '' || $TipoGrupo == null){
			if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadPreMaterialAcompanharSelecionar.TipoGrupo.focus();\" class=\"titulo2\">TipoGrupo</a>";
		}

		if($Mens == 0){

			//$db   = Conexao();
			# Sql para montagem da janela #
			$sqlPre  = "SELECT DISTINCT(PRE.CPREMACODI), PRE.EPREMADESC, PRE.DPREMACADA,PRE.TPREMAULAT, ";
			$sqlPre .= "       GRU.CGRUMSCODI, CLA.CCLAMSCODI, PRESIT.EPREMSDESC, ";
			$sqlPre .= "       GRUEMP.EGREMPDESC, USUPOR.EUSUPORESP, GRU.FGRUMSTIPO ";
			$sqlPre .= "  FROM SFPC.TBPREMATERIALSERVICO PRE, SFPC.TBGRUPOMATERIALSERVICO GRU, SFPC.TBCLASSEMATERIALSERVICO CLA, ";
			$sqlPre .= "       SFPC.TBPREMATERIALSERVICOTIPOSITUACAO PRESIT, SFPC.TBUSUARIOPORTAL USUPOR,SFPC.TBGRUPOEMPRESA GRUEMP ";
			$sqlPre .= " WHERE GRU.CGRUMSCODI = PRE.CGRUMSCODI AND CLA.CCLAMSCODI = PRE.CCLAMSCODI ";
			$sqlPre .= "   AND PRE.CPREMSCODI = PRESIT.CPREMSCODI AND PRE.CGREMPCODI = GRUEMP.CGREMPCODI ";
			$sqlPre .= "   AND PRE.CUSUPOCODI = USUPOR.CUSUPOCODI AND PRE.CGREMPCODI = USUPOR.CGREMPCODI ";
			$sqlPre .= "   AND PRE.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." AND PRE.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." ";

			# Verifica qual o tipo da situação #
			if( ($Situacao != "") and ($Todos <> 1)){
					$sqlPre .= " AND PRESIT.CPREMSCODI = $Situacao ";
					$Todos   = 0;
			}

			# Verifica qual o tipo do grupo #
			if( $TipoGrupo != "" && $TipoGrupo != null && $TipoGrupo != 'T'){
					$sqlPre .= " AND GRU.FGRUMSTIPO = '$TipoGrupo' ";
			}

			$sqlPre .= " ORDER BY PRE.TPREMAULAT ";

			//$db->disconnect();

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
function remeter(){
	document.CadPreMaterialAcompanharSelecionar.submit();
}
function enviar(valor){
	document.CadPreMaterialAcompanharSelecionar.Botao.value=valor;
	document.CadPreMaterialAcompanharSelecionar.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadPreMaterialAcompanharSelecionar.php" method="post" name="CadPreMaterialAcompanharSelecionar">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais/Serv > Pré-Cadastro > Acompanhamento
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
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="5">
	           ACOMPANHAMENTO - PRÉ-CADASTRO DE MATERIAIS/SERVIÇOS
          </td>
        </tr>
        <tr>
          <td class="textonormal" colspan="5">
             <p align="justify">
             Para Acompanhar o pré-cadastro de materiais/serviços efetuado pelo usuário logado, selecione a situação e depois clique na descrição do material/serviço desejado.
             </p>
          </td>
        </tr>
        <tr>
          <td colspan="5">
            <table width="100%" summary="">
            	<tr>
					<td class="textonormal" bgcolor="#DCEDF7" width="30%">Tipo de Grupo*</td>
					<td class="textonormal">
						<select name="TipoGrupo" class="textonormal" onChange="javascript:if(document.CadPreMaterialAcompanharSelecionar.Situacao.value != null || document.CadPreMaterialAcompanharSelecionar.Todos.checked) {enviar('Exibir');}">
							<option value="">Selecione um Tipo de Grupo...</option>
							<option value="M" <?php if( $TipoGrupo == "M" ){ echo "selected"; }?>>MATERIAL</option>
							<option value="S" <?php if( $TipoGrupo == "S" ){ echo "selected"; }?>>SERVIÇO</option>
							<option value="T" <?php if( $TipoGrupo == "T" ){ echo "selected"; }?>>TODOS</option>
						</select>
					</td>
				</tr>

	            <tr>
	              <td class="textonormal" bgcolor="#DCEDF7"  width="30%">Situação</td>
	              <td class="textonormal" >
	              	<select name="Situacao" class="textonormal" onChange="javascript:document.CadPreMaterialAcompanharSelecionar.Todos.checked = false; enviar('Exibir');">

										  <option value="">Selecione uma Situação...</option>
										<?php
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
	              <td class="textonormal">
	              	<input type="checkbox" name="Todos" value="1" onClick="javascript:document.CadPreMaterialAcompanharSelecionar.Situacao.value='';enviar('Exibir');" <?php if( $Todos == "1" ){ echo "checked";	} ?> > Todas as Situações
	              </td>
	            </tr>

            </table>
          </td>
        </tr>
	  		<tr>
	      	<td colspan="5" align="right">
	       		<input type="button" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
						<input type="hidden" name="Botao" value="">
					</td>
	    	</tr>
 				<?php
 				# Exibe o Resultado da Pesquisa #
 				if( (($Situacao != "" ) or ($Todos == 1)) and ($TipoGrupo != "" and $TipoGrupo != null)){
				 		if( $sqlPre != "" ){
            		$db     = Conexao();
						 		$res    = $db->query($sqlPre);
  							$qtdres = $res->numRows();
								if( PEAR::isError($res) ){
	  					 		 	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlPre");
								}else{
							  		echo "			   <tr>\n";
					    			echo "				   <td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"5\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
										echo "			   </tr>\n";
							  		if( $qtdres > 0 ){
							    			echo "	       <tr align=\"center\">\n";
							    			echo "			 <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"15%\">TIPO DE GRUPO</td>\n";
						      				echo "	         <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"50%\">DESCRIÇÃO DO MATERIAL/SERVIÇO</td>\n";
						      				echo "	         <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"12%\" align=\"center\">DATA</td>\n";
							    			echo "	         <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"25%\">SOLICITANTE</td>\n";
							    			echo "	         <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"13%\">SITUAÇÃO</td>\n";
							    			echo "	       </tr>\n";
												while( $row	= $res->fetchRow() ){
									  				$PreMaterialCodigo    = $row[0];
									  				$PreMaterialDescricao = strtoupper2($row[1]);
									  				$DataCadastro         = DataBarra($row[2]);
									  				$DataAtualizacao      = $row[3];
									  				$GrupoCodigo          = $row[4];
									  				$ClasseCodigo         = $row[5];
									  				$TipoSitDescricao     = $row[6];
									  				$GrupoEmpDescricao    = substr($row[7],0,30);
									  				$ResponsavelDescricao = substr($row[8],0,30);
									  				$TipoGrupoBanco     = $row[9];

   													echo"      <tr>\n";
   													echo "	  <td valign=\"top\" bgcolor=\"#F7F7F7\" align=\"center\" class=\"textonormal\" width=\"15%\">\n";

													if($TipoGrupoBanco == "M" ){ //CASO SEJA MATERIAL (M)
														echo "MATERIAL";
													} else { //CASO SEJA SERVIÇO (S)
														echo "SERVIÇO";
													}

													echo "					</td>\n";

								    				echo "		     <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"50%\">\n";
														$Url = "CadPreMaterialAcompanhar.php?Grupo=$GrupoCodigo&Classe=$ClasseCodigo&PreMaterial=$PreMaterialCodigo";
														if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
									  				echo "	 		 <a href=\"CadPreMaterialAcompanhar.php?Grupo=$GrupoCodigo&Classe=$ClasseCodigo&PreMaterial=$PreMaterialCodigo\"><font color=\"#000000\">$PreMaterialDescricao</font></a>";
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
