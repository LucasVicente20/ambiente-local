<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabLeiPortalIncluir.php
# Autor:    Luiz Alves
# Data:     27/06/11
# Objetivo: Programa de Inclusão do Tipo de Compra - Demanda Redmine: #3281
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao               = $_POST['Botao'];
		$NdaLei = strtoupper2(trim($_POST['NdaLei']));
		$LeiDescricao        = $_POST['LeiDescricao'];
		$DataLei             = $_POST['DataLei'];
		$Dia                 = $_POST['Dia'];
		$Mes                 = $_POST['Mes'];
		$Ano                 = $_POST['Ano'];
		$NCaracteres         = $_POST['NCaracteres'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabLeiPortalIncluir.php";

# Critica dos Campos #
if( $Botao == "Incluir" ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
	  if( $LeiDescricao == "" ){
		    $Mens      = 1;
		    $Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.Lei.LeiDescricao.focus();\" class=\"titulo2\">Descrição, </a>";
    }
      if( $NdaLei == "" ){
		    $Mens      = 1;
		    $Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.Lei.NdaLei.focus();\" class=\"titulo2\">Nº da Lei</a>";
    }
     if( $DataLei == "" ){
     	$Mens          = 1;
     	$Tipo          = 2;
     	$Mensagem     .= "<a href=\"javascript:document.Lei.DataLei.focus();\" class=\"titulo2\">Data da Lei</a>";
     }
 	if($NCaracteres > "300"){
				if($Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Observação menor que 300 caracteres";
		}
	  if( $Mens == 0 )
	  {

	  	  # Verifica a Duplicidade do Tipo de Compra #
				$db     = Conexao();
		   	$sql    = "SELECT COUNT(CTPLEITIPO) FROM SFPC.TBLEIPORTAL WHERE RTRIM(LTRIM(CLEIPONUME)) = '$NdaLei' ";
		 		$result = $db->query($sql);
				if (PEAR::isError($result)) {
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
		    		$Linha = $result->fetchRow();
				    $Qtd = $Linha[0];
		    		if( $Qtd > 0 ) {
					    	$Mens     = 1;
					    	$Tipo     = 2;
								$Mensagem = "<a href=\"javascript:document.Lei.NdaLei.focus();\" class=\"titulo2\"> Tipo de Compra Já Cadastrado</a>";
						}else{
								# Recupera o último Tipo de Compra e incrementa mais um #
						    $sql    = "SELECT MAX(CLEIPONUME) FROM SFPC.TBLEIPORTAL";
						    $result = $db->query($sql);
								if( PEAR::isError($result) ){
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
				        		while( $Linha = $result->fetchRow() ){
								    		$LeiCodigo = $Linha[0] + 1;
								    }
								}

	  # Valida uma Data #
    function DataLei($DataLei) {
	$Datalei = explode("/",$DataLei);
	$Dia  = $DataLei[0];
	$Mes  = $DataLei[1];
	$Ano  = $DataLei[2];
	if ( (sizeof($DataLei)>3) || (strlen($Dia)!=2) || (strlen($Mes)!=2) || (strlen($Ano)!=4) ){
		$MensErro = "Formato incorreto (deve ser NN/NN/NNNN)";
	}else if ( SoNumeros($Dia) and SoNumeros($Mes) and SoNumeros($Ano) ){
			if( ! checkdate( $Mes, $Dia, $Ano ) ){
					$MensErro = "Data informada não existe";
			}
	} else {
			$MensErro = "Dia, mês e ano devem possuir apenas números";
	}
	return($MensErro);
}

						    # Insere Tipo De Compra #
						    $Data   = date("Y-m-d H:i:s");
						    $db->query("BEGIN TRANSACTION");
						    $sql    = "INSERT INTO SFPC.TBLEIPORTAL(";
						    $sql   .= "CTPLEITIPO, CLEIPONUME, DLEIPODATA, NLEIPONOME, CUSUPOCODI, TLEIPOULAT ";
						    $sql   .= ") VALUES ( ";
						    $sql   .= "$LeiCodigo, $NdaLei, '$DataLei', '$LeiDescricao', ".$_SESSION['_cusupocodi_'].", '$Data')";
						    $result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$db->query("COMMIT");
										$db->query("END TRANSACTION");
										$Mens                = 1;
										$Tipo                = 1;
										$Mensagem            = "Tipo de Compra Incluída com Sucesso";
										$LeiDescricao = "";
										$NdaLei       = "";
										$DataLei      = "";

								}
						}
				}
		    $db->disconnect();
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
	document.Lei.Botao.value=valor;
	document.Lei.submit();
}
function ncaracteres(valor){
	document.Lei.NCaracteres.value = '' +  document.Lei.LeiDescricao.value.length;
	/* if( navigator.appName == 'Netscape' && valor ){ //Netscape Only
		document.Lei.LeiDescricao.focus();
	}*/
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabLeiPortalIncluir.php" method="post" name="Lei">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Lei do Portal > Incluir
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<tr>
	  <td width="100"></td>
	  <td align="left" colspan="2">
	  	<?php if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,1); } ?>
	  </td>
	</tr>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
      <table  border="0" cellspacing="0" cellpadding="3">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	            		INCLUIR - LEI DO PORTAL
	            	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para incluir um novo tipo de compra, informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.
	        	    	</p>
	          		</td>
		        	</tr>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left" summary="">
	    	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Nº da Lei*</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="NdaLei" value="<?php echo $NdaLei; ?>" size="10" maxlength="10" class="textonormal">
	          	    		</td>
	            			</tr>
	            		<tr>
							<td class="textonormal" bgcolor="#DCEDF7">Data da Lei*</td>
							<td class="textonormal">
							  <?php $URL = "../calendario.php?Formulario=TabLeiPortalIncluir&Campo=DataLei";?>
								<input type="text" name="DataLei" size="10" maxlength="10" value="<?php echo $DataLei?>" class="textonormal">
							    <a href="javascript:janela('<?php echo $URL ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
								<font class="textonormal">dd/mm/aaaa</font>
							</td>
							</tr>
	      	      	     <tr>
				            <td class="textonormal" bgcolor="#DCEDF7">Objeto*</td>
				            <td class="textonormal">
				                <font class="textonormal">máximo de 300 caracteres</font>
								<input type="text" name="NCaracteres" disabled size="3" value="<?php echo $NCaracteres ?>" class="textonormal"><br>
								<textarea name="LeiDescricao" cols="39" rows="5" OnKeyUp="javascript:ncaracteres(1)" OnBlur="javascript:ncaracteres(0)" OnSelect="javascript:ncaracteres(1)" class="textonormal"><?php echo $LeiDescricao; ?></textarea>
						    </td>
				           </tr>
	            		</table>
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td>
	   	  	  			<table class="textonormal" border="0" align="right" summary="">
	        	      	<tr>
	          	      	<td>
	          	      		<input type="button" value="Incluir" class="botao" onClick="javascript:enviar('Incluir');">
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
<script language="JavaScript">
<!--
document.Lei.LeiDescricao.focus();
//-->
</script>
