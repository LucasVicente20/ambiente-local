<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadComissaoIncluir.php
# Autor:    Rossana Lira
# Data:     07/04/03
# Objetivo: Programa de Inclusão de Comissao
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------
# Alterado: Caio Coutinho - Pitang Agile TI
# Data:     18/03/2019
# Objetivo: Tarefa Redmine 177358
#-------------------------------------------------------------------------
# Alterado: Lucas André
# Data:     27/04/2023
# Objetivo: CR - 282316
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao             = $_POST['Botao'];
		$Critica           = $_POST['Critica'];
		$ComissaoDescricao = strtoupper2(trim($_POST['ComissaoDescricao']));
		$Presidente        = strtoupper2(trim($_POST['Presidente']));
		$Email             = trim($_POST['Email']);
		$Fone              = trim($_POST['Fone']);
		$Fax               = trim($_POST['Fax']);
		$Local             = strtoupper2(trim($_POST['Local']));
		$Situacao          = $_POST['Situacao'];
        $Sigla             = $_POST['Sigla'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadComissaoIncluir.php";

# Critica dos Campos #
if( $Critica == 1 ) {
		$Mens     = 0;
		$Mensagem = "Informe: ";
	if( $ComissaoDescricao == "" ){
	    $Mens = 1;$Tipo = 2;$Virgula=1;
		$Mensagem .= "<a href=\"javascript:document.Comissao.ComissaoDescricao.focus();\" class=\"titulo2\">Comissão</a>";
    }
	if( $Presidente == "" ){
		if ($Mens == 1){
			$Mensagem.=", ";
		}
		$Mens = 1;$Tipo = 2;$Virgula=1;
    	$Mensagem .= "<a href=\"javascript:document.Comissao.Presidente.focus();\" class=\"titulo2\">Nome do Presidente</a>";
	}
	if( $Email != "" and ! strchr($Email, "@") ){
		if ($Mens == 1){
			$Mensagem.=", ";
		}
	    $Mens = 1;$Tipo = 2;$Virgula=1;
		$Mensagem .= "<a href=\"javascript:document.Comissao.Email.focus();\" class=\"titulo2\">E-Mail Válido</a>";
	}
	if( $Local == "" ) {
		if ($Mens == 1){
			$Mensagem.=", ";
		}
		    $Mens = 1;$Tipo = 2;$Virgula=1;
    		$Mensagem .= "<a href=\"javascript:document.Comissao.Local.focus();\" class=\"titulo2\">Localização</a>";
	 	}
	if($Sigla == "") {
		if ($Mens == 1){
			$Mensagem.=", ";
		}
		    $Mens = 1;$Tipo = 2;$Virgula=1;
    		$Mensagem .= "<a href=\"javascript:document.Comissao.Sigla.focus();\" class=\"titulo2\">Sigla</a>";
	 	}
	if( $Mens == 0 ){
		# Verifica a Duplicidade de Comissão
		$db     = Conexao();
		$sql    = "SELECT COUNT(CCOMLICODI) FROM SFPC.TBCOMISSAOLICITACAO WHERE RTRIM(LTRIM(ECOMLIDESC)) = '$ComissaoDescricao'";
		$result = $db->query($sql);
		if(PEAR::isError($result)){
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}
		while($Linha = $result->fetchRow()){
			$Qtd = $Linha[0];
		}
    	if($Qtd > 0) {
			$Mens = 1;$Tipo = 2;
			$Mensagem = "<a href=\"javascript:document.Comissao.ComissaoDescricao.focus();\" class=\"titulo2\">Comissão Já Cadastrada</a>";
		}
		#Verifica Duplicidade da Sigla da Comissão
		$db     = Conexao();
		$sql    = "SELECT COUNT(ecomlisigl) FROM SFPC.TBCOMISSAOLICITACAO WHERE (ecomlisigl) = '$Sigla'";
		$result = $db->query($sql);

		if (PEAR::isError($result)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
			$Linha = $result->fetchRow();
			$Qtd = $Linha[0];
		}
		if($Qtd > 0){
			$Mens = 1;$Tipo = 2;
			$Mensagem = "<a href=\"javascript:document.Comissao.Sigla.focus();\" class=\"titulo2\">Sigla de Comissão já Cadastrada</a>";
		}else{
			# Recupera a última comissão e incrementa mais um #
			$db->query("BEGIN TRANSACTION");
			$sql    = "SELECT MAX(CCOMLICODI) FROM SFPC.TBCOMISSAOLICITACAO";
			$result = $db->query($sql);
			if( PEAR::isError($result) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			}
			while( $Linha = $result->fetchRow() ){
				$Codigo = $Linha[0] + 1;
			}
		    # Insere Comissão #
			if($Situacao == "A"){ 
				$Situacao = "A"; 
			} else { 
				$Situacao = "I"; 
			}
			$Data    = date("Y-m-d H:i:s");
			$sql     = "INSERT INTO SFPC.TBCOMISSAOLICITACAO (";
			$sql    .= "CCOMLICODI, CGREMPCODI, ECOMLIDESC, NCOMLIPRES, ECOMLIMAIL, ";
			$sql    .= "ECOMLILOCA, ACOMLIFONE, ACOMLINFAX, FCOMLISTAT, TCOMLIULAT, ECOMLISIGL ";
			$sql    .= ") VALUES (";
			$sql    .= "$Codigo, ".$_SESSION['_cgrempcodi_'].", '$ComissaoDescricao', '$Presidente', '$Email', ";
			$sql    .= "'$Local', '$Fone', '$Fax', '$Situacao', '$Data', '$Sigla')";
			$result  = $db->query($sql);
			if( PEAR::isError($result) ){
				$db->query("ROLLBACK");
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			}else{
				$Mens = 1;$Tipo = 1;
				$Mensagem          = "Comissão Incluída com Sucesso";
				$ComissaoDescricao = "";
				$Presidente        = "";
				$Email             = "";
				$Local             = "";
				$Fone              = "";
				$Fax               = "";
			}
				$db->query("COMMIT");
				$db->query("END TRANSACTION");
		}
	    $db->disconnect();
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
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadComissaoIncluir.php" method="post" name="Comissao">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Comissão > Incluir
    </td>
  </tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
	  <td width="100"></td>
	  <td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
      <table  border="0" cellspacing="0" cellpadding="3" summary="">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
		    					INCLUIR - COMISSÃO DE LICITAÇÃO
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para incluir uma nova Comissão de Licitação, informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.
	          	   	</p>
	          		</td>
		        	</tr>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left" summary="">
	      	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Comissão*</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="ComissaoDescricao" value="<?php echo $ComissaoDescricao; ?>" size="100" maxlength="200" class="textonormal">
	            	  			<input type="hidden" name="Critica" value="1">
	            	  		</td>
	            			</tr>
                            <tr>
                                <td class="textonormal" bgcolor="#DCEDF7">Sigla*</td>
                                <td class="textonormal">
                                    <input type="text" name="Sigla" value="<?php echo $Sigla; ?>" size="10" maxlength="10" class="textonormal">
                                    <input type="hidden" name="Critica" value="1">
                                </td>
                            </tr>
										<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Presidente*</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="Presidente" value="<?php echo $Presidente; ?>" size="45" maxlength="60" class="textonormal">
	          	    		</td>
	            	  	</tr>
	            	  	<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">E-mail</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="Email" value="<?php echo $Email; ?>" size="45" maxlength="60" class="textonormal">
	          	    		</td>
	            	  	</tr>
	            			<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Localização*</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="Local" value="<?php echo $Local; ?>" size="45" maxlength="100" class="textonormal">
	          	    		</td>
	            	  	</tr>
										<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Telefone</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="Fone" value="<?php echo $Fone; ?>" size="25" maxlength="25" class="textonormal">
	          	    		</td>
	            	  	</tr>
	            	   	<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Fax</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="Fax" value="<?php echo $Fax; ?>" size="25" maxlength="25" class="textonormal">
	          	    		</td>
	            	  	</tr>
	            	  	<tr>
			        				<td class="textonormal" bgcolor="#DCEDF7">Situação</td>
				        			<td class="textonormal">
				        				<?php if( $Situacao == "" ){?>
				        				<input type="radio" name="Situacao" value="A" checked > ATIVA
											  <input type="radio" name="Situacao" value="I"> INATIVA
											  <?php }else{ ?>
						        				<?php if( $Situacao == "A" ){?>
													  <input type="radio" name="Situacao" value="A" checked > ATIVA
													  <input type="radio" name="Situacao" value="I"> INATIVA
														<?php }else{ ?>
												 		<input type="radio" name="Situacao" value="A"> ATIVA
														<input type="radio" name="Situacao" value="I" checked >INATIVA
														<?php } ?>
												<?php } ?>
	        	      		</td>
	            	  	</tr>
	            		</table>
		          	</td>
		        	</tr>
	  	      	<tr>
       	      	<td class="textonormal" align="right">
       	      		<input type="submit" name="Incluir" value="Incluir" class="botao">
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
<script language="javascript" type="">
<!--
document.Comissao.ComissaoDescricao.focus();
//-->
</script>
