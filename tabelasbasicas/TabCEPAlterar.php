<?php
/*
 * Created on 25/05/2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabCEPAlterar.php
# Autor:    Everton Lino
# Data:     25/05/2010
# Objetivo: Programa de Alteração de CEP
#---------------------------------------------
# Alterado:    Ariston Cordeiro
# Data		:    03/11/2010	- Correções gerais
#-------------------------------------
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

if( $_SERVER['REQUEST_METHOD'] == "POST")
{

	 	$Botao          = $_POST['Botao'];
	 	$CEP            = $_POST['CEP'];
		$TipoCEP        = $_POST['TipoCEP'];
		$TipoLogradouro = $_POST['TipoLogradouro'];
		$Logradouro     = strtoupper2(trim($_POST['Logradouro']));
		$Bairro         = strtoupper2(trim($_POST['Bairro']));
		$Cidade         = strtoupper2(trim($_POST['Cidade']));
		$TipoLocalidade = $_POST['TipoLocalidade'];
		$Localidade     = strtoupper2(trim($_POST['Localidade']));
		$UF             = strtoupper2(trim($_POST['UF']));
		$CEPAntes       = $_POST['CEPAntes']; //Guarda o CEP antes da alteração, para detectar se houve mudança de CEP
		$Critica       = $_POST['Critica'];

}else{
		$CEPAntes = $CEP      = $_GET['CEP'];
		$Mensagem     = urldecode($_GET['Mensagem']);
		$Mens         = $_GET['Mens'];
		$Tipo         = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabCEPAlterar.php";

# Critica dos Campos #
if( $Botao == "Voltar" ){
	header("location: TabCEPPesquisa.php");
	exit();
}
if( $Botao == "Alterar" ){
	$Mens     = 0;
	$Mensagem = "Informe: ";
	if( $CEP == "" ){
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.TabCEPAlterar.CEP.focus();\" class=\"titulo2\">CEP</a>";
	}else{
		if( $Mens == 0 ) {
			$db  = Conexao();
			if($CEPAntes != $CEP){ //caso houve mudança no CEP...

				# Verifica se CEP atual está sendo usado por tabelas do Portal
				$sql = "select count(*) from SFPC.TBfornecedorcredenciado where cceppocodi = '$CEPAntes' ";
				$res = $db->query($sql);
				if (PEAR::isError($res)) {
					EmailErroSQL("erro em SQL", __FILE__, __LINE__, "erro em SQL", $sql, $res);
				}
				$Linha = $res->fetchRow();
				$Qtd   = $Linha[0];

				$sql = "select count(*) from SFPC.TBprefornecedor where cceppocodi = '$CEPAntes' ";
				$res = $db->query($sql);
				if (PEAR::isError($res)) {
					EmailErroSQL("erro em SQL", __FILE__, __LINE__, "erro em SQL", $sql, $res);
				}
				$Linha = $res->fetchRow();
				$Qtd   = $Linha[0];

				$Qtd   += $Linha[0];

				if($Qtd>0){
					$Mens     = 1;
					$Tipo     = 2;
					$Mensagem = "<a href=\"javascript:document.TabCEPAlterar.CEP.focus();\" class=\"titulo2\"> CEP atual já está sendo usado por fornecedores ou pré-fornecedores e não pode ser alterado</a>";
					$CEP = $CEPAntes;
				}

				# Verifica a Duplicidade do novo CEP #
				if($Mens!=1){
					$sql = "SELECT COUNT(CCEPPOCODI) FROM PPDV.TBCEPLOGRADOUROBR WHERE CCEPPOCODI = '$CEP' ";
					$res = $db->query($sql);
					if (PEAR::isError($res)) {
						EmailErroSQL("erro em SQL", __FILE__, __LINE__, "erro em SQL", $sql, $res);
					}
					$Linha = $res->fetchRow();
					$Qtd   = $Linha[0];
					if( $Qtd > 0 ){
						$Mens     = 1;
						$Tipo     = 2;
						$Mensagem = "<a href=\"javascript:document.TabCEPAlterar.CEP.focus();\" class=\"titulo2\"> Novo CEP Já Cadastrado em Logradouro</a>";
						$CEP = $CEPAntes;
					}else{
						$sql = "SELECT COUNT(CCELOCCODI) FROM PPDV.TBCEPLOCALIDADEBR WHERE CCELOCCODI = '$CEP' ";
						$res = $db->query($sql);
						if (PEAR::isError($res)) {
							EmailErroSQL("erro em SQL", __FILE__, __LINE__, "erro em SQL", $sql, $res);
						}
						$Linha = $res->fetchRow();
						$Qtd   = $Linha[0];
						if( $Qtd > 0 )
						{
							$Mens     = 1;
							$Tipo     = 2;
							$Mensagem = "<a href=\"javascript:document.TabCEPAlterar.CEP.focus();\" class=\"titulo2\"> Novo CEP Já Cadastrado em Localidade</a>";
							$CEP = $CEPAntes;
						}
					}
				}
			}
			$RowBack = false;
			if($Mens != 1){
				$db->query("BEGIN TRANSACTION");
				if($TipoCEP == "LOG"){
					$Data   = date("Y-m-d H:i:s");
					$sql    = " UPDATE PPDV.TBCEPLOGRADOUROBR";
					$sql   .= "  SET   CCEPPOCODI = '$CEP' , ";
					$sql   .= "        NCEPPOTIPO = '$TipoLogradouro', NCEPPOLOGR = '$Logradouro', ";
					$sql   .= "        NCEPPOBAIR = '$Bairro', NCEPPOCIDA = '$Cidade', ";
					$sql   .= "        CCEPPOESTA = '$UF', TCEPPOULAT = '$Data'  ";
					$sql   .= " WHERE  CCEPPOCODI = '$CEPAntes' ";
					$result = $db->query($sql);
					if( PEAR::isError($result) ){
						$RowBack = true;
						$db->query("ROLLBACK");
						$db->disconnect();
						# Erro de alteração de CEP pode ocorrer quando o CEP já está sendo usado por alguma tabela de algum sistema.
						# Mas, como não da pra recuperar o tipo de erro da versão do PEAR sendo usada, não dá para saber se a causa do erro foi essa.
						EmailErroSQL("erro em SQL", __FILE__, __LINE__, "Erro na tentativa de alterar os dados do CEP. Caso o número do CEP esteja sendo alterado, é possível que o CEP atual já esteja associado a alguma tabela (inclusive fora do sistema do Portal de Compras).", $sql, $res);
					}
				}else if($TipoCEP == "LOC"){

					$Data   = date("Y-m-d H:i:s");
					$sql    = " UPDATE PPDV.TBCEPLOCALIDADEBR";
					$sql   .= "  SET   CCELOCCODI = '$CEP' , ";
					$sql   .= "        CCELOCTIPO = '$TipoLocalidade', NCELOCLOCA = '$Localidade', ";
					$sql   .= "        CCELOCESTA = '$UF', TCEPPOULAT = '$Data'  ";
					$sql   .= " WHERE  CCELOCCODI = '$CEPAntes' ";
					$result = $db->query($sql);
					if( PEAR::isError($result) ){
						$RowBack = true;
						$db->query("ROLLBACK");
						$db->disconnect();
						# Erro de alteração de CEP pode ocorrer quando o CEP já está sendo usado por alguma tabela de algum sistema.
						# Mas, como não da pra recuperar o tipo de erro da versão do PEAR sendo usada, não dá para saber se a causa do erro foi essa.
						EmailErroSQL("erro em SQL", __FILE__, __LINE__, "Erro na tentativa de alterar os dados do CEP. Caso o número do CEP esteja sendo alterado, é possível que o CEP atual já esteja associado a alguma tabela (inclusive fora do sistema do Portal de Compras).", $sql, $res);
					}
				}
				if(!$RowBack){
					$db->query("COMMIT");
					$db->query("END TRANSACTION");
					$db->disconnect();
					# Envia mensagem para página de busca #
					$Mensagem = urlencode("CEP Alterado com Sucesso");
					$Url = "TabCEPPesquisa.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
					if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
					header("location: ".$Url);
					exit();
				}

			}
			$db->disconnect();
	 	}
	}
}


if( $Mens == 0 ){
	$db  = Conexao();

	# Procura e carrega os dados do cep em Logradouro (Tabela de logradouro) #
	$sql    = "SELECT CCEPPOCODI, NCEPPOLOGR, NCEPPOBAIR, NCEPPOCIDA, CCEPPOESTA,NCEPPOTIPO ";
	$sql   .= "FROM PPDV.TBCEPLOGRADOUROBR WHERE CCEPPOCODI = '$CEP' ";
	$result = $db->query($sql);
	if( PEAR::isError($result) ){
    EmailErroSQL("erro em SQL", __FILE__, __LINE__, "erro em SQL", $sql, $result);
	}
	$rows = $result->numRows();
	if($rows >0){
		# CEP foi encontrado na tabela de logradouros. pegando dados.
		while( $Linha = $result->fetchRow() ){
			$CEP            = $Linha[0];
			$TipoCEP = "LOG";
			$Logradouro     = $Linha[1];
			$Bairro         = $Linha[2];
			$Cidade         = $Linha[3];
			$UF             = $Linha[4];
			$TipoLogradouro = $Linha[5];
		}
	}else{
		# CEP não encontrado em logradouro. Carrega os dados em Localidade (Tabela de localidade) #
		$sql   = "SELECT CCELOCCODI, NCELOCLOCA, CCELOCESTA, CCELOCTIPO ";
		$sql   .= "FROM PPDV.TBCEPLOCALIDADEBR WHERE CCELOCCODI = '$CEP' ";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
	    EmailErroSQL("erro em SQL", __FILE__, __LINE__, "erro em SQL", $sql, $result);
		}
		$rows = $result->numRows();
		if($rows >0){
			#CEP encontrado na tabela de localidade
			while( $Linha = $result->fetchRow() ){
				$CEP            = $Linha[0];
				$TipoCEP = "LOC";
				$Localidade     = $Linha[1];
				$UF             = $Linha[2];
				$TipoLocalidade	=	$Linha[3];
			}
		}else{
			#CEP encontrado na tabela de localidade
			# Envia mensagem de CEP inexistente para página de busca
			$Mensagem = urlencode("CEP não encontrado");
			$Url = "TabCEPPesquisa.php?Mensagem=$Mensagem&Mens=1&Tipo=2";
			if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
			header("location: ".$Url);
			exit();

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
function enviar(valor){
	document.TabCEPAlterar.Botao.value=valor;
	document.TabCEPAlterar.submit();
}
<?php  MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabCEPAlterar.php" method="post" name="TabCEPAlterar">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Fornecedores > CEP > Manter
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<tr>
	  <td width="100"></td>
	  <td align="left" colspan="2">
	  	<?php  if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,1); } ?>
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
	            		ALTERAR - CEP
	            	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para Alterar um CEP, informe os dados abaixo e clique no botão "Alterar". Os itens obrigatórios estão com *.
	        	    	</p>
	          		</td>
		        	</tr>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left" summary="">
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7" width="30%">Tipo*</td>
											<td class="textonormal">
												<input  disabled type="radio" name="CCEP" value="" <?php if( $TipoCEP == "LOG" ){ echo "checked"; }?> >Logradouro
												<input  disabled type="radio" name="CCEP" value="" <?php if( $TipoCEP == "LOC" ){ echo "checked"; }?> >Localidade
				            	  <input type="hidden" name="TipoCEP"  value="<?php  echo $TipoCEP ?>" class="textonormal">
				            	</td>
				            </tr>
					           <tr>
				              <td class="textonormal" bgcolor="#DCEDF7" width="30%">CEP*</td>
											<td class="textonormal">
												<input type="text" name="CEP" size="8" maxlength="8" value="<?php echo $CEP; ?>" class="textonormal">
												<input type="hidden" name="CEPAntes" size="8" maxlength="8" value="<?php echo $CEPAntes;?>" class="textonormal">
				            	</td>
				            </tr>
				            <?php if( $TipoCEP == "LOG" ){ ?>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Tipo do Logradouro*</td>
				              <td class="textonormal">
				              	<select name="TipoLogradouro" class="textonormal">
													<option value="<?php  echo $TipoLogradouro; ?>" >Selecione um Tipo...</option>
													<?php
													$caminho = "../cep/DNE_GU_TIPOS_LOGRADOURO.TXT";
											   	if( file_exists($caminho) ){
											    		if( !( $fp = fopen($caminho,"r") ) ){
												   				echo "Erro na abertura do Arquivo: $caminho";
												   		}else{
																	$i = 0;
																	while( ! feof ($fp)) {
																	    $Dados = fgets($fp, 1024);
																			if( $i != 0 ){
																					$j = $i - 1;
																					if( $Dados != "" and $Dados != "#" ){
																							$Nome[$j]        = trim(substr($Dados,7,72));
																							$Abreviatura[$j] = trim(substr($Dados,79,15));
																					}
																			}
																			$i++;
																	}
																	fclose($fp);
															}
													}else{
												   		echo "Arquivo não Encontrado";
													}
													for( $i=0;$i< count($Nome);$i++ ){
															echo "<option value='".$Nome[$i]."' ";
															if( $TipoLogradouro == $Nome[$i] ){ echo "selected"; }
															echo ">$Nome[$i]</option>\n";
													}
													?>
												</select>
				              </td>
				            </tr>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Logradouro*</td>
				              <td class="textonormal">
				              	<input type="text" name="Logradouro" size="45" maxlength="100" value="<?php echo $Logradouro; ?>" class="textonormal">
				              </td>
				            </tr>
										<?php
										}
										if( $TipoCEP == "LOC" ){
										?>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Tipo da Localidade*</td>
				              <td class="textonormal">
				              	<select name="TipoLocalidade" class="textonormal">
				              		<option value="">Selecione um Tipo...</option>
				              		<option value="D" <?php if( $TipoLocalidade == "D" ){ echo "selected"; }?>>DISTRITO</option>
				              		<option value="M" <?php if( $TipoLocalidade == "M" ){ echo "selected"; }?>>MUNICÍPIO</option>
				              		<option value="P" <?php if( $TipoLocalidade == "P" ){ echo "selected"; }?>>POVOADO</option>
				              		<option value="R" <?php if( $TipoLocalidade == "R" ){ echo "selected"; }?>>REGIÃO ADMINISTRATIVA</option>
				              	</select>
				              </td>
				            </tr>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Localidade*</td>
				              <td class="textonormal">
				              	<input type="text" name="Localidade" size="45" maxlength="100" value="<?php echo $Localidade; ?>" class="textonormal">
				              </td>
				            </tr>
				            <?php
				          	}
				            if( $TipoCEP == "LOG" ){
				            ?>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Bairro*</td>
				              <td class="textonormal">
				              	<input type="text" name="Bairro" size="33" maxlength="30" value="<?php echo $Bairro; ?>" class="textonormal">
				              </td>
				            </tr>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Cidade*</td>
				              <td class="textonormal">
				              	<input type="text" name="Cidade" size="33" maxlength="30" value="<?php echo $Cidade; ?>" class="textonormal">
				              </td>
				            </tr>
				            <?php } ?>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">UF*</td>
		    	      			<td class="textonormal">
		    	      				<input type="text" name="UF" size="2" maxlength="2" value="<?php echo $UF; ?>" class="textonormal">
		    	      			</td>
				            </tr>
	            		</table>
		          	</td>
		        	</tr>
    	      	<tr>
      	      	<td class="textonormal" align="right">
      	      		<input type="button" value="Alterar" class="botao" onClick="javascript:enviar('Alterar');">
      	      		<input name="voltar" type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
 									<input type="hidden" name="Botao" value="">
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
document.TabCEPAlterar.CEP.focus();
//-->
</script>
