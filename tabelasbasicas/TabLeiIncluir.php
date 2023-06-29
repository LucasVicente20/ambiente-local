<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabLeiIncluir.php
# Autor:    Luiz Alves
# Data:     27/06/11
# Objetivo: Programa de Criação de leis - Demanda Redmine: #3281
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------
# Alterado: Luiz Alves
# Data:     20/09/2011
# Objetivo: Correção dos erros - Demanda Redmine: #3640
#-------------------------------------------------------------------------
# Alterado: João Batista Brito
# Data:     28/03/12
# Objetivo: Correção dos erros - Demanda Redmine: #4506
#-------------------------------------------------------------------------
# Alterado: João Batista Brito
# Data:     25/06/12
# Objetivo: Correção dos erros - Demanda Redmine: #11725
#-------------------------------------------------------------------------
# Alterado: Osmar Celestino
# Data:     06/05/2021
# Objetivo: #247328 Tirar o erro: Número da Lei deve ser menor que 100000
#-------------------------------------------------------------------------
# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao               = $_POST['Botao'];
		$TipoLei			 = strtoupper2(trim($_POST['TipoLei']));
		$NumerodaLei	     = $_POST['NumerodaLei'];
		$NomeLei	         = strtoupper2(trim($_POST['NomeLei']));
		$LicitacaoDtAbertura = trim($_POST['LicitacaoDtAbertura']);
		$DataLei             = $_POST['DataLei'];
		$Dia                 = $_POST['Dia'];
		$Mes                 = $_POST['Mes'];
		$Ano                 = $_POST['Ano'];
		$NCaracteres         = $_POST['NCaracteres'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabLeiIncluir.php";

# Critica dos Campos #
if( $Botao == "Incluir" ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
	  if( $TipoLei == "" or $TipoLei == 0){
		    if($Mens == 1){ $Mensagem.=", "; }
			$Mens     = 1;
		    $Tipo     = 2;
  			$Mensagem .= "<a href=\"javascript:document.TabLeiIncluir.TipoLei.focus();\" class=\"titulo2\">Selecione o Tipo da Lei</a>";
		}
		if( $NumerodaLei == ""){
		    if($Mens == 1){ $Mensagem.=", "; }
			$Mens      = 1;
		    $Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.TabLeiIncluir.NumerodaLei.focus();\" class=\"titulo2\">Digite o Número da Lei (só números)</a>";
		}
		else if( $NumerodaLei == 0){
		    if($Mens == 1){ $Mensagem.=", "; }
			$Mens      = 1;
		    $Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.TabLeiIncluir.NumerodaLei.focus();\" class=\"titulo2\">Número da Lei deve ser númerico e maior que zero</a>";
		}
		else if (!ereg("^([0-9]){1,}$",$NumerodaLei) ) {
		  if($Mens == 1){ $Mensagem.=", "; }
		    $Mens      = 1;
			$Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.TabLeiAlterar.NumerodaLei.focus();\" class=\"titulo2\">O Número da Lei só deve conter números</a>";
		}/*else if( $NumerodaLei > 100000 ){
		    if($Mens == 1){ $Mensagem.=", "; }
			$Mens      = 1;
		    $Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.TabLeiIncluir.NumerodaLei.focus();\" class=\"titulo2\">Número da Lei deve ser menor que 100000</a>";
		}*/
		if( $DataLei == ""){
				if( $Mens == 1 ){ $Mensagem.=", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript: document.TabLeiIncluir.DataLei.focus();\" class=\"titulo2\">Digite a Data da Lei</a>";
		}else{
				$MensErro = ValidaData($DataLei);
				if( $MensErro != "" ){
					if( $Mens == 1 ){ $Mensagem.=", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.TabLeiIncluir.DataLei.focus();\" class=\"titulo2\">Data da Lei Válida</a>";
				}else {
				$Hoje = date("Ymd");

				$Data = substr($DataLei,-4).substr($DataLei,3,2).substr($DataLei,0,2);
				if( $Data > $Hoje ){
					if( $Mens == 1 ){ $Mensagem.=", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.TabLeiIncluir.DataLei.focus();\" class=\"titulo2\">Data Menor ou Igual a Data Atual</a>";
				}
			}

		}
		if( $NomeLei == "" ){
			if($Mens == 1 ){ $Mensagem .= ", "; }
			$Mens      = 1;
		    $Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.TabLeiIncluir.NomeLei.focus();\" class=\"titulo2\">a Descrição</a>";
		}
		if($NCaracteres > "300"){
				if($Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Observação menor que 300 caracteres";
		}
	  if( $Mens == 0 ) {

		# Verifica a Duplicidade da Lei #
				$db     = Conexao();
		   	$sql    = "SELECT COUNT(CTPLEITIPO) FROM SFPC.TBLEIPORTAL WHERE RTRIM(LTRIM(CLEIPONUME)) = '$NumerodaLei' AND RTRIM(LTRIM(CTPLEITIPO)) = '$TipoLei'  ";
		 		$result = $db->query($sql);
				if (PEAR::isError($result)) {
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
		    		$Linha = $result->fetchRow();
				    $Qtd = $Linha[0];
		    		if( $Qtd > 0 ) {
					    	$Mens     = 1;
					    	$Tipo     = 2;
							$Mensagem = "<a href=\"javascript:document.TabLeiIncluir.NumerodaLei.focus();\" class=\"titulo2\"> Lei já Cadastrada</a>";
						}else{

								# Recupera a última Situacao Solicitação e incrementa mais um #
						    $sql    = "SELECT MAX(CLEIPONUME) FROM SFPC.TBLEIPORTAL";
						    $result = $db->query($sql);
								if( PEAR::isError($result) ){
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
				        		while( $Linha = $result->fetchRow() ){
								    		$Codigo = $Linha[0] + 1;
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
										$MensErro = "Dia, Mês e Ano devem possuir apenas números";
								}
								return($MensErro);
							}

						    # Insere Lei #
						    $Data   = date("Y-m-d H:i:s");
						    $db->query("BEGIN TRANSACTION");
						    $sql    = "INSERT INTO SFPC.TBLEIPORTAL(";
						    $sql   .= "CTPLEITIPO, CLEIPONUME, DLEIPODATA, NLEIPONOME, CUSUPOCODI, TLEIPOULAT ";
						    $sql   .= ") VALUES ( ";
						    $sql   .= "$TipoLei, $NumerodaLei, '".DataInvertida($DataLei)."', '$NomeLei', ".$_SESSION['_cusupocodi_'].", '$Data')";
						    $result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$db->query("COMMIT");
										$db->query("END TRANSACTION");
										$Mens                = 1;
										$Tipo                = 1;
										$Mensagem            = "Lei Incluída com Sucesso";
										$TipoLei	  		 = "";
										$NumerodaLei    	 = "";
										$DataLei      		 = "";
										$NomeLei 			 = "";
								}
						}
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
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.TabLeiIncluir.Botao.value=valor;
	document.TabLeiIncluir.submit();
}

function ncaracteres(valor){
	document.TabLeiIncluir.NCaracteres.value = '' +  document.TabLeiIncluir.NomeLei.value.length;
}

function AbreJanela(url,largura,altura) {
	window.open(url,'pagina','status=no,scrollbars=no,left=270,top=150,width='+largura+',height='+altura);
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabLeiIncluir.php" method="post" name="TabLeiIncluir">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Lei > Incluir
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
	            		INCLUIR - LEI
	            	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para incluir uma nova Lei, informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.
	        	    	</p>
	          		</td>
		        	</tr>
		        	<tr>
	  	        	<td>
	    	      	<table class="textonormal" border="0" align="left" summary="">
						<tr>
						    <td class="textonormal" bgcolor="#DCEDF7">Tipo de Lei*</td>
                <td class="textonormal">
                  <select name="TipoLei" class="textonormal">
                  	<option value="">Selecione o Tipo de Lei...</option>
                  	<!-- Mostra os códigos das leis cadastrados -->
                  	<?php
							$db     = Conexao();
							$sql    = "SELECT CTPLEITIPO,ETPLEITIPO FROM SFPC.TBTIPOLEIPORTAL ORDER BY ETPLEITIPO";
								$result = $db->query($sql);
								if( PEAR::isError($result) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");

									}else{
												while( $Linha = $result->fetchRow() ){
															   	if( $Linha[0] == $TipoLei ){
															    		echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
															   	}else{
															      	echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
															   	}
											     		}
											    }
		    	              	$db->disconnect();
													?>
	          	    		</select>
	            	  		</td>
	            			</tr>
	    	      		<tr>
	        	      		<td class="inteiropositivo" bgcolor="#DCEDF7">Número da Lei*</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="NumerodaLei" value="<?php echo $NumerodaLei; ?>" size="10" maxlength="10" class="textonormal">
	          	    		</td>
	            			</tr>
	            		<tr>
							<td class="textonormal" bgcolor="#DCEDF7">Data da Lei*</td>
							<td class="textonormal">
							  <input name="DataLei" id="DataLei" class="data" size="10" maxlength="10" value="<?php echo $DataLei;?>" class="textonormal" type="text">
							   <a href="javascript:janela('../calendario.php?Formulario=TabLeiIncluir&amp;Campo=DataLei','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" alt="" border="0"></a>

							</td>
							</tr>
	      	      	     <tr>
				            <td class="textonormal" bgcolor="#DCEDF7">Descrição*</td>
				            <td class="textonormal">
				                <font class="textonormal">máximo de 300 caracteres</font>
								<input type="text" name="NCaracteres" disabled size="3" value="<?php echo $NCaracteres; ?>" class="textonormal"><br>
								<textarea name="NomeLei" cols="39" rows="5" OnKeyUp="javascript:ncaracteres(1)" OnBlur="javascript:ncaracteres(0)" OnSelect="javascript:ncaracteres(1)" class="textonormal"><?php echo $NomeLei; ?></textarea>
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
// document.TabLeiIncluir.NumerodaLei.focus();
//-->
</script>
