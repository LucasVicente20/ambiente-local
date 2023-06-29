<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabAcessoAlterar.php
# Autor:    Roberta Costa
# Data:     15/04/03
# Objetivo: Programa de Inclusão de Acesso
# OBS.:     Tabulação 2 espaços
# Alterado: Carlos Abreu
# Data:     28/06/2007 - Ajuste para quando caminho for vazio entrar NULL e nao string 'NULL'
#-------------------------------------------------------------------------
# Alterado: João Madson
# Data:     04/03/2021
# Objetivo: Tarefa Redmine 244819
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabAcessoExcluir.php' );
AddMenuAcesso( '/tabelasbasicas/TabAcessoSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']  == "POST"){
		$Botao            = $_POST['Botao'];
		$Critica          = $_POST['Critica'];
		$AcessoCodigo     = $_POST['AcessoCodigo'];
		$HierarquiaCodigo = $_POST['HierarquiaCodigo'];
		$AcessoCaminho    = $_POST['AcessoCaminho'];
		$AcessoDescricao  = $_POST['AcessoDescricao'];
		$AcessoOrdem      = $_POST['AcessoOrdem'];
		$AcessoPai        = $_POST['AcessoPai'];
		$CodigoAtual      = $_POST['CodigoAtual'];
}else{
		$AcessoCodigo     = $_GET['AcessoCodigo'];
		$HierarquiaCodigo = $_GET['HierarquiaCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabAcessoAlterar.php";

# Pegar o Codigo Antigo #
$db     = Conexao();
$sql    = "SELECT CACEPOCPAI FROM SFPC.TBACESSOPORTAL WHERE CACEPOCODI = $AcessoCodigo";
$result = $db->query($sql);
if( PEAR::isError($result) ){
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
		while( $Linha = $result->fetchRow() ){
				$CodigoAntigo = $Linha[0];
		}
}
$db->disconnect();

if( $Botao == "Excluir" ){
		$Url = "TabAcessoExcluir.php?AcessoCodigo=$AcessoCodigo&HierarquiaCodigo=$HierarquiaCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
  	header("location: ".$Url);
  	exit;
}elseif( $Botao == "Voltar" ){
  	header("location: TabAcessoSelecionar.php");
  	exit;
}else{
		# Critica dos Campos #
		if( $Critica == 1 ) {
				$Mens     = 0;
				$Mensagem = "Informe: ";
				if( $AcessoDescricao == "" ) {
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.Acesso.AcessoDescricao.focus();\" class=\"titulo2\">Descrição</a>";
				}else{
						if( strlen($AcessoDescricao) > 30) {
							  $Mens     = 1;
							  $Tipo     = 2;
							  $Mensagem = "<a href=\"javascript:document.Acesso.AcessoDescricao.focus();\" class=\"titulo2\">A descrição do Acesso</a> deve conter no máximo 30 caracteres";
						}
				}
				if( $AcessoOrdem == "" || ! SoNumeros($AcessoOrdem) ){
						if ($Mens == 1){$Mensagem.=", ";}
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.Acesso.AcessoOrdem.focus();\" class=\"titulo2\">Ordem Válida</a>";
				}
				if( ( $HierarquiaCodigo == "") and ( strlen($AcessoDescricao) > 13) ){
					  $Mens     = 1;
					  $Tipo     = 1;
					  $Mensagem = "<a href=\"javascript:document.Acesso.AcessoDescricao.focus();\" class=\"titulo2\">Esse Acesso</a> é de primeiro nível, deve conter no máximo 13 caracteres";
				}
				if( $AcessoCaminho != "" and strlen($AcessoCaminho) > 255) {
					  $Mens     = 1;
					  $Tipo     = 2;
					  $Mensagem = "<a href=\"javascript:document.Acesso.AcessoCaminho.focus();\" class=\"titulo2\">O caminho do acesso</a> deve conter no máximo 255 caracteres";
				}
				if( $Mens == 0 ){
						$Data = date("Y-m-d H:i:s");
						if( $AcessoCaminho == "" ){
								$AcessoCaminho = "NULL";
						}else{
								$AcessoCaminho = "'$AcessoCaminho'";
						}
						$db   = Conexao();
						$db->query("BEGIN TRANSACTION");
						if( $CodigoAtual == "" ){ $CodigoAtual = $AcessoCodigo; }
						if( BuscaFilho($AcessoCodigo,$CodigoAtual,$db) == 1 ){
								# Altera a o Código do Pai e de seus respectivos filhos #
								if( $AcessoCodigo == $AcessoPai ){
										$sql    = "UPDATE SFPC.TBACESSOPORTAL ";
										$sql   .= "   SET CACEPOCPAI = CACEPOCODI, TACEPOULAT = '$Data' ";
										$sql   .= " WHERE CACEPOCPAI = $AcessoCodigo";
										$result = $db->query($sql);
										if( PEAR::isError($result) ){
												$db->query("ROLLBACK");
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}
								}else{
										$sql    = "UPDATE SFPC.TBACESSOPORTAL ";
										$sql   .= "   SET CACEPOCPAI = $AcessoPai, TACEPOULAT = '$Data' ";
										$sql   .= " WHERE CACEPOCPAI = $AcessoCodigo";
										$result = $db->query($sql);
										if( PEAR::isError($result) ){
												$db->query("ROLLBACK");
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}
								}
						}
						$sql    = "UPDATE SFPC.TBACESSOPORTAL ";
						$sql   .= "   SET CACEPOCPAI = $CodigoAtual, EACEPODESC = '$AcessoDescricao', ";
						$sql   .= "   	  AACEPOORDE = $AcessoOrdem, TACEPOULAT = '$Data', ";
						$sql   .= "       EACEPOCAMI = $AcessoCaminho ";
						$sql   .= " WHERE CACEPOCODI = $AcessoCodigo ";
						$result = $db->query($sql);
						if( PEAR::isError($result) ){
								$db->query("ROLLBACK");
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$db->query("COMMIT");
								$db->query("END TRANSACTION");
								$db->disconnect();

							  # Envia mensagem para página selecionar #
								$Mensagem = urlencode("Acesso Alterado com Sucesso");
								$Url = "TabAcessoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1&Critica=0";
								if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						    header("location: ".$Url);
						    exit;
						}
				}
		}
}

if( $Critica == 0 ){
		$db     = Conexao();
		$sql    = "SELECT CACEPOCODI, EACEPODESC, CACEPOCPAI, AACEPOORDE, EACEPOCAMI ";
		$sql   .= "  FROM SFPC.TBACESSOPORTAL WHERE CACEPOCODI = $AcessoCodigo";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$AcessoCodigo    = $Linha[0];
						$AcessoDescricao = $Linha[1];
						$AcessoPai       = $Linha[2];
						$AcessoOrdem     = $Linha[3];
						$AcessoCaminho   = $Linha[4];
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
	document.Acesso.Botao.value=valor;
	document.Acesso.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabAcessoAlterar.php" method="post" name="Acesso">
<br><br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Acesso > Manter
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
			<table  border="0" cellspacing="0" cellpadding="3" bgcolor="#FFFFFF">
				<tr>
					<td class="textonormal">
						<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
									MANTER - ACESSO
								</td>
							</tr>
							<tr>
								<td class="textonormal" >
									<p align="justify">
										Para atualizar o acesso, preencha os dados abaixo e clique no botão "Alterar". Para apagar o acesso clique no botão "Excluir". Se desejar que o acesso seja de primeiro nível não selecione Hierarquia. Os itens obrigatórios estão com *.
									</p>
								</td>
							</tr>
		         	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left">
										<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7" width="30%">Descrição*</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="AcessoDescricao" size="35" maxlength="30" value="<?php echo $AcessoDescricao;?>" class="textonormal" style="text-Transform: none; ">
	            	  			<input type="hidden" name="AcessoCodigo" value="<?php echo $AcessoCodigo;?>" size="5">
	            	  		</td>
	            			</tr>
										<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Caminho</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="AcessoCaminho" size="45" maxlength="255" value="<?php echo $AcessoCaminho;?>" class="textonormal" style="text-Transform: none; ">
   		        	  			<input type="hidden" name="Critica" value="1">
   		        	  		</td>
	            			</tr>
	            			<tr>
		              		<td class="textonormal"  bgcolor="#DCEDF7">Hierarquia</td>
		              		<td class="textonormal">
		  				  	      <select name="HierarquiaCodigo" OnChange="javascript:AcessoCod();" class="textonormal">
													<option value="">Selecione uma Ordem - Hierarquia...</option>
													<?php
													$db     = Conexao();
													$sql    = "SELECT CACEPOCODI, EACEPODESC, AACEPOORDE FROM SFPC.TBACESSOPORTAL ";
													$sql   .= " WHERE CACEPOCODI = CACEPOCPAI ORDER BY AACEPOORDE";
													$result = $db->query($sql);
													if( PEAR::isError($result) ){
													    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}else{
															while( $Linha = $result->fetchRow() ){
																	if( $Linha[0] == $HierarquiaCodigo ){
																			echo "<option value=\"$Linha[0]\" selected>$Linha[2] - $Linha[1]</option>\n";
																	}else{
																			echo "<option value=\"$Linha[0]\">$Linha[2] - $Linha[1]</option>\n";
																	}
																	TipoSon($Linha[0], $db, 1, $HierarquiaCodigo);
															}
													}
													$db->disconnect();
													?>
											  </select>
											  <input type="hidden" name="CodigoAtual" value="<?php echo $HierarquiaCodigo;?>" size="5">
		            	  		<input type="hidden" name="AcessoPai" value="<?php echo $AcessoPai;?>" size="5">
										  </td>
	            			</tr>
	      	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Ordem*</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="AcessoOrdem" size="2" maxlength="3" value="<?php echo $AcessoOrdem;?>" class="textonormal">
	          	    		</td>
	            			</tr>
	          			</table>
		          	</td>
		        	</tr>
							<tr>
		   	        <td class="textonormal" align="right">
	                <input type="button" value="Alterar" class="botao" onclick="javascript:enviar('Alterar');">
									<input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Excluir');">
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
	<!-- Fim do Corpo -->
</table>
<?php
# Verifica se um Acesso tem Filhos #
function TipoSon($_AcessoCodigoPai_, $db, $_Nivel_, $_HierarquiaCodigo_) {
	$Endentacao = str_repeat("&nbsp;&nbsp;&nbsp;",$_Nivel_ );
	$sql    = "SELECT CACEPOCODI, EACEPODESC, AACEPOORDE FROM SFPC.TBACESSOPORTAL ";
	$sql   .= " WHERE CACEPOCODI <> CACEPOCPAI AND CACEPOCPAI = $_AcessoCodigoPai_";
	$sql   .= " ORDER BY AACEPOORDE";
	$result = $db->query($sql);
	if( PEAR::isError($result) ){
	    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	}else{
			while( $Linha = $result->fetchRow() ){
					if( $Linha[0] == $_HierarquiaCodigo_ ){
							echo "<option value=\"$Linha[0]\" selected>$Endentacao $Linha[2] - $Linha[1]\n";
					}else{
							echo "<option value=\"$Linha[0]\">$Endentacao $Linha[2] - $Linha[1]\n";
					}
					TipoSon($Linha[0], $db, $_Nivel_+1, $_HierarquiaCodigo_);
			}
	}
	return;
}

?>
</form>
</body>
</html>
<script language="javascript" type="">
<!--
document.Acesso.AcessoDescricao.focus();
function AcessoCod(){
	document.Acesso.CodigoAtual.value = document.Acesso.HierarquiaCodigo.options[document.Acesso.HierarquiaCodigo.selectedIndex].value;
}
//-->
</script>
