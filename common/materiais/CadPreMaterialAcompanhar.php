<?php
#----------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadPreMaterialAcompanhar.php
# Autor:    Rossana Lira
# Data:     03/05/06
# Alterado: Rodrigo Melo
# Data:     21/11/2007 - Alteração, onde, caso o pré-cadastro tenha sido aprovado exibir na tela de consulta o código e nome do material relacionado na aprovação.
#                                       Isto é, substituir o campo Código “Código do Pré-Material” e Material por  “Material Pré-Cadastrado” e colocar após o campo Data da Situação
#                                       os seguintes campos: “Código do Material” e “Material”.
# Alterado: Rodrigo Melo
# Data:     24/08/2009 - Alteração para realizar o acompanhamento de serviços pré-cadastrados
# Alterado: Rodrigo Melo
# Data:     22/09/2009 - Alterando o nome das tabelas SFPC.TBPREMATERIAL e SFPC.TBPREMATERIALTIPOSITUACAO para SFPC.TBPREMATERIALSERVICO e BPREMATERIALSERVICOTIPOSITUACAO, respectivamente (CR 2749).
# Alterado: Ariston Cordeiro
# Data:     23/10/2009 - Detectando se as variáveis REQUEST estão nulas. Caso sim, ir para tela de seleção
# Objetivo: Programa de Acompanhamento de Pré-Cadastro de Material
# OBS.:     Tabulação 2 espaços
#----------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/materiais/CadPreMaterialAcompanharSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
		$Botao    			   = $_POST['Botao'];
		$TipoMaterial 	   = $_POST['TipoMaterial'];
		$Grupo   				   = $_POST['Grupo'];
		$DescGrupo   			 = $_POST['DescGrupo'];
		$Classe    			   = $_POST['Classe'];
		$DescClasse    		 = $_POST['DescClasse'];
		$Unidade    		   = $_POST['Unidade'];
		$DescUnidade  	   = $_POST['DescUnidade'];
		$CodigoPreMaterial = $_POST['PreMaterial'];
		$DescPreMaterial   = stripslashes(strtoupper2(str_replace("'","",trim($_POST['DescMaterial']))));
		$Observacao    		 = $_POST['Observacao'];
		$SituacaoAtualDesc = $_POST['SituacaoAtualDesc'];
		$SituacaoAtualCodi = $_POST['SituacaoAtualCodi'];
		$DataSituacao      = $_POST['DataSituacao'];
}else{
		$Grupo              = $_GET['Grupo'];
		$Classe             = $_GET['Classe'];
		$CodigoPreMaterial  = $_GET['PreMaterial'];
		$Mens               = $_GET['Mens'];
		$Tipo    	          = $_GET['Tipo'];
		$Mensagem           = $_GET['Mensagem'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# redirecionar para Pesquisar, caso dados necessários para renderizar a página não forem especificados
if( (is_null($Grupo)) && (is_null($Classe)) && (is_null($PreMaterial)) ){
		header("Location: CadPreMaterialAcompanharSelecionar.php");
		exit;
}


if( $Botao == "Voltar" ){
		header("location: CadPreMaterialAcompanharSelecionar.php");
		exit;
}
if( $Botao == "" ){
	# Busca os dados do Pré-Material de acordo com o código #
	$db   = Conexao();
	$sql  = "SELECT GRU.FGRUMSTIPM, GRU.EGRUMSDESC, CLA.ECLAMSDESC, PRE.EPREMADESC, ";
	$sql .= "PRE.CPREMACODI, PRE.CPREMSCODI, PRE.EPREMAOBSE, PRE.TPREMAULAT, UND.EUNIDMDESC, ";
		$sql .= "UND.CUNIDMCODI, PRESIT.EPREMSDESC, ";

	$sql .= "CASE WHEN GRU.FGRUMSTIPO = 'S' THEN SER.CSERVPSEQU ELSE MAT.CMATEPSEQU END, "; //Caso seja Serviço ('S') - Pegar o código do Serviço, senão pegar do Material.
	$sql .= "CASE WHEN GRU.FGRUMSTIPO = 'S' THEN SER.ESERVPDESC ELSE MAT.EMATEPDESC END, "; //Caso seja Serviço ('S') - Pegar a descrição do Serviço, senão pegar do Material.

	$sql .= " GRU.FGRUMSTIPO "; //Tipo de Grupo - Material ou Serviço

 	$sql .= "FROM SFPC.TBPREMATERIALSERVICO PRE ";
    $sql .= "LEFT OUTER JOIN SFPC.TBMATERIALPORTAL MAT ";
    $sql .= "ON PRE.CPREMACODI = MAT.CPREMACODI ";
    $sql .= "LEFT OUTER JOIN SFPC.TBSERVICOPORTAL SER ";
    $sql .= "ON PRE.CPREMACODI = SER.CPREMACODI ";
    $sql .= "LEFT OUTER JOIN SFPC.TBPREMATERIALSERVICOTIPOSITUACAO PRESIT ";
    $sql .= "ON PRE.CPREMSCODI = PRESIT.CPREMSCODI ";
    $sql .= "LEFT OUTER JOIN SFPC.TBUNIDADEDEMEDIDA UND ";
    $sql .= "ON PRE.CUNIDMCODI = UND.CUNIDMCODI ";
    $sql .= "LEFT OUTER JOIN SFPC.TBCLASSEMATERIALSERVICO CLA ";
    $sql .= "ON PRE.CGRUMSCODI = CLA.CGRUMSCODI AND PRE.CCLAMSCODI = CLA.CCLAMSCODI ";
    $sql .= "LEFT OUTER JOIN SFPC.TBGRUPOMATERIALSERVICO GRU ";
    $sql .= "ON CLA.CGRUMSCODI = GRU.CGRUMSCODI ";
    $sql .= "WHERE PRE.CGRUMSCODI = $Grupo ";
    $sql .= "AND PRE.CCLAMSCODI = $Classe ";
    $sql .= "AND PRE.CPREMACODI = $CodigoPreMaterial ";


    // $sql .= "SELECT GRU.FGRUMSTIPM, GRU.EGRUMSDESC, CLA.ECLAMSDESC, PRE.EPREMADESC, ";
		// $sql .= "       PRE.CPREMACODI, PRE.CPREMSCODI, PRE.EPREMAOBSE, PRE.TPREMAULAT, UND.EUNIDMDESC, ";
		// $sql .= "       UND.CUNIDMCODI, PRESIT.CPREMSCODI, PRESIT.EPREMSDESC, MAT.CMATEPSEQU, MAT.EMATEPDESC ";
 	  // $sql .= "  FROM SFPC.TBPREMATERIALSERVICO PRE, SFPC.TBGRUPOMATERIALSERVICO GRU, SFPC.TBCLASSEMATERIALSERVICO CLA, ";
		// $sql .= "       SFPC.TBUNIDADEDEMEDIDA UND, SFPC.TBPREMATERIALSERVICOTIPOSITUACAO PRESIT,  SFPC.TBMATERIALPORTAL MAT ";
		// $sql .= " WHERE PRE.CGRUMSCODI = CLA.CGRUMSCODI AND PRE.CCLAMSCODI = CLA.CCLAMSCODI ";
		// $sql .= "   AND CLA.CGRUMSCODI = GRU.CGRUMSCODI AND PRE.CGRUMSCODI = $Grupo ";
		// $sql .= "   AND PRE.CCLAMSCODI = $Classe AND PRE.CPREMACODI = $CodigoPreMaterial	";
		// $sql .= "   AND PRE.CPREMSCODI = PRESIT.CPREMSCODI ";
		// $sql .= "   AND PRE.CUNIDMCODI = UND.CUNIDMCODI ";

		$res  = $db->query($sql);
	  if( PEAR::isError($res) ){
			  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha               = $res->fetchRow();
      	$TipoMaterial        = $Linha[0];
      	$DescGrupo           = substr($Linha[1],0,60);
				$DescClasse          = substr($Linha[2],0,60);
				$DescPreMaterial     = $Linha[3];
				$CodigoPreMaterial   = $Linha[4];
        $SituacaoAtualCodi   = $Linha[5];
				$Observacao          = $Linha[6];
        $DataSituacao        = $Linha[7];
				$DescUnidade         = $Linha[8];
				$Unidade             = $Linha[9];
				$SituacaoAtualDesc   = $Linha[10];
        		$CodigoMaterialServico      = $Linha[11];
        		$DescMaterialServico        = $Linha[12];
        		$TipoGrupo           = $Linha[13];

        //Variáveis dinâmicas para colocar as informações para material ou serviço.
		if($TipoGrupo == 'M') {
		   $DescricaoTipoGrupo = "Material";
		} else {
			$DescricaoTipoGrupo = "Serviço";
		}

		}
		$db->disconnect();
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
	document.CadPreMaterialAcompanhar.Grupo.value  = '';
	document.CadPreMaterialAcompanhar.Classe.value = '';
	document.CadPreMaterialAcompanhar.submit();
}
function enviar(valor){
	document.CadPreMaterialAcompanhar.Botao.value=valor;
	document.CadPreMaterialAcompanhar.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadPreMaterialAcompanhar.php" method="post" name="CadPreMaterialAcompanhar">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais/Serv > Pré-Cadastro > Acompanhamento
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
      <table border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
				<tr>
					<td class="textonormal">
						<table border="0" cellspacing="0" cellpadding="0" summary="">
							<tr>
				      	<td class="textonormal">
				        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
				          	<tr>
				            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
					    					ACOMPANHAMENTO - PRÉ-CADASTRO DE MATERIAIS/SERVIÇOS
					          	</td>
					        	</tr>
				  	      	<tr>
				    	      	<td class="textonormal">
												<p align="justify">
													Para voltar para a tela de Pesquisa, clique no botão "Voltar".
				          	   	</p>
				          		</td>
					        	</tr>
					        	<tr>
											<td>
												<table border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" width="100%" summary="">
													<tr>
														<td colspan="2">
									      	    <table class="textonormal" border="0" width="100%" summary="">
										            <tr>
														<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Tipo de Grupo</td>
														<td class="textonormal">
															<?php
																echo strtoupper2($DescricaoTipoGrupo);
															?>
														</td>
													</tr>


										            <?php
														//Variáveis dinâmicas para colocar as informações para material ou serviço.
														if($TipoGrupo == 'M') {
													?>
										            <tr>
										              <td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Tipo de Material</td>
										              <td class="textonormal">
										              	<?php if( $TipoMaterial == "C" ){ echo "CONSUMO"; }else{ echo "PERMANENTE"; } ?>
									              	</td>
									            	</tr>
													<?php } //Fecha o if($TipoGrupo == 'M')  ?>

									            	<tr>
									              	<td class="textonormal" bgcolor="#DCEDF7" height="20">Grupo</td>
									              	<td class="textonormal"><?php echo $DescGrupo; ?></td>
									            	</tr>
											        	<tr>
											            <td class="textonormal" bgcolor="#DCEDF7" height="20">Classe</td>
										  	        	<td class="textonormal"><?php echo $DescClasse; ?></td>
											        	</tr>


										        	 <?php
														//Variáveis dinâmicas para colocar as informações para material ou serviço.
														if($TipoGrupo == 'M') {
													?>
										        	<tr>
										            	<td class="textonormal" bgcolor="#DCEDF7" height="20">Unidade</td>
									  	        		<td class="textonormal"><?php echo $DescUnidade;?></td>
													</tr>
													<?php } //Fecha o if($TipoGrupo == 'M')  ?>
										        		<tr>
										            	<td class="textonormal" bgcolor="#DCEDF7" height="20"><?php echo $DescricaoTipoGrupo;?> Pré-Cadastrado</td>
									  	        		<td class="textonormal"><?php echo $DescPreMaterial;?></td>
													</tr>
										        		<tr>
										            	<td class="textonormal" bgcolor="#DCEDF7" height="20">Observação</td>
									  	        		<td class="textonormal"><?php echo $Observacao; ?></td>
										        		</tr>
										        		<tr>
										            	<td class="textonormal" bgcolor="#DCEDF7" height="20">Situação Atual</td>
									  	        		<td class="textonormal"><?php echo $SituacaoAtualDesc; ?></td>
										        		</tr>
										        		<tr>
										            	<td class="textonormal" bgcolor="#DCEDF7" height="20">Data da Situação</td>
									  	        		<td class="textonormal"><?php echo $DataSituacao; ?></td>
										        		</tr>
                                <?php
                                  // Exibe apenas se o pré-cadastro foi aprovado (2 - APROVADO)
                                  if ( $SituacaoAtualCodi == 2 && !($CodigoMaterialServico == null || $CodigoMaterialServico == "") && !($DescMaterialServico == null || $DescMaterialServico == "") ) {
                                    echo " <tr>\n";
                                    echo "   <td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\">Código do $DescricaoTipoGrupo</td>\n";
                                    echo "   <td class=\"textonormal\">$CodigoMaterialServico</td>\n";
                                    echo " </tr>\n";

                                    echo " <tr>\n";
                                    echo "   <td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\">$DescricaoTipoGrupo</td>\n";
                                    echo "   <td class=\"textonormal\">$DescMaterialServico</td>\n";
                                    echo " </tr>\n";
                                  }
                                ?>

									          	</table>
														</td>
													</tr>
												</table>
											</td>
										</tr>
			          		<tr>
				            	<td colspan="2" align="right">
			              		<input type="hidden" name="Grupo" value="<?php echo $Grupo; ?>">
			              		<input type="hidden" name="DescGrupo" value="<?php echo $DescGrupo; ?>">
			              		<input type="hidden" name="Classe" value="<?php echo $Classe; ?>">
			              		<input type="hidden" name="DescClasse" value="<?php echo $DescClasse; ?>">
			              		<input type="hidden" name="PreMaterial" value="<?php echo $CodigoPreMaterial; ?>">
			              		<input type="hidden" name="Unidade" value="<?php echo $Unidade; ?>">
			              		<input type="hidden" name="DescUnidade" value="<?php echo $DescUnidade; ?>">
			              		<input type="hidden" name="Observacao" value="<?php echo $Observacao; ?>">
                        <input type="hidden" name="DataSituacao" value="<?php echo $DataSituacao; ?>">
			              		<input type="hidden" name="SituacaoAtualDesc" value="<?php echo $SituacaoAtualDesc; ?>">
			              		<input type="hidden" name="SituacaoAtualCodi" value="<?php echo $SituacaoAtualCodi; ?>">
							       		<input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
												<input type="hidden" name="Botao" value="">
											</td>
			            	</tr>
		     					</table>
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
</body>
</html>
