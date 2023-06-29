<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabIndiceCorrecaoExcluir.php
# Autor:    João Batista Brito
# Data:     03/11/11
# Objetivo: Programa de Exclusão do Índice Correção
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabIndiceCorrecaoAlterar.php' );
AddMenuAcesso( '/tabelasbasicas/TabIndiceCorrecaoSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']  == "POST"){
		$Botao         = $_POST['Botao'];
		$Critica       = $_POST['Critica'];
		$IndCorrCodigo = $_POST['IndCorrCodigo'];
}else{
		$IndCorrCodigo = $_GET['IndCorrCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabIndiceCorrecaoExcluir.php";

if( $Botao == "Voltar" ){
		$Url = "TabIndiceCorrecaoAlterar.php?IndCorrCodigo=$IndCorrCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit();
}else{
		# Critica dos Campos #
		if( $Critica == 1 ) {
				$Mens     = 0;
		    $Mensagem = "Informe: ";

		    
		# Verifica se o Índice Correção tem algum Usuário relacionado #
		if ($IndCorrCodigo == 1) {
		    $Mensagem = urlencode("Exclusão Cancelada!<br>O Índice Correção não pode ser excluído");
		       	$Url = "TabIndiceCorrecaoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=2";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		        header("location: ".$Url);
		        exit();
		    }else{
				    # Verifica se o Índice tem algum Usuário relacionado #
				    $db     = Conexao();
				    $sql    = "SELECT COUNT(*) FROM SFPC.TBVALORINDICE WHERE CINCORSEQU  = $IndCorrCodigo";
				    $result = $db->query($sql);
						if (PEAR::isError($result)) {
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$Linha = $result->fetchRow();
						    $Qtd = $Linha[0];
						    if( $Qtd > 0 ) {
						    		$db->disconnect();

		# Envia mensagem para página selecionar #
		    $Mensagem = urlencode("Exclusão Cancelada!<br>Índice de Correção com percentuais associados");
			     $Url = "TabIndiceCorrecaoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=2";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						        header("location: ".$Url);
						        exit();
						    }else{
							     	# Verifica se o Índice Correção tem algum acesso relacionado #
								    $sql    = "SELECT COUNT(*) FROM SFPC.TBVALORINDICE WHERE CINCORSEQU = $IndCorrCodigo";
								    $result = $db->query($sql);
								    if (PEAR::isError($result)) {
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$Linha = $result->fetchRow();
										    $Qtd = $Linha[0];
										    if( $Qtd > 0 ) {
										        if ($Mens == 1){ $Mensagem .= "<br>"; }else{ $Mensagem .= "Exclusão Cancelada!<br>"; }
														$db->disconnect();

										# Envia mensagem para página selecionar #
										        $Mensagem .= urlencode("Índice Correção Relacionado com ($Qtd) Acesso(s)");
										        $Url = "TabIndiceCorrecaoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=2";
														if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
										        header("location: ".$Url);
										        exit();
										    }
										}
								}
						}
						$db->disconnect();
			  }
			  
			  
				if( $Mens == 0 ){
		    	  # Exclui Índice #
		    	  $db = Conexao();
		        $db->query("BEGIN TRANSACTION");
		        # Apaga todos os Acesso relacionados com o Índice Correção selecionado
		        $sql    = "DELETE FROM SFPC.TBINDICECORRECAO WHERE CINCORSEQU = $IndCorrCodigo";
					  $result = $db->query($sql);
				    if( PEAR::isError($result) ){
				    		$db->query("ROLLBACK");
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								# Apaga o Índice Correção selecionado
				        $sql    = "DELETE FROM SFPC.TBINDICECORRECAO WHERE CINCORSEQU = $IndCorrCodigo";
							  $result = $db->query($sql);
						    if( PEAR::isError($result) ){
						    		$db->query("ROLLBACK");
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$db->query("COMMIT");
										$db->query("END TRANSACTION");
										$db->disconnect();
										# Envia mensagem para página selecionar #
										$Mensagem = urlencode("Índice Correção Excluído com Sucesso");
										$Url = "TabIndiceCorrecaoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
										header("location: ".$Url);
										exit();
								}
						}
						$db->disconnect();
				}
		}
}
		if( $Critica == 0){
		$db     = Conexao();
		$sql    = "SELECT EINCORNOME, cincorsiti FROM SFPC.TBINDICECORRECAO WHERE CINCORSEQU = $IndCorrCodigo";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$IndCorrDesc = $Linha[0];
						$Situacao    = $Linha[1];
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
	document.Indice.Botao.value=valor;
	document.Indice.submit();
}
<?php  MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabIndiceCorrecaoExcluir.php" method="post" name="Indice">
<br><br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Índice > Manter
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php  if ( $Mens == 1 ) {?>
  <tr>
  	<td width="150"></td>
		<td align="left" colspan="2"><?php  ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php  } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="150"></td>
		<td class="textonormal">
		<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	        	EXCLUIR - ÍNDICE
          </td>
        </tr>
        <tr>
          <td class="textonormal" >
             <p align="justify">
               Para confirmar a exclusão do Índice Correção clique no botão "Excluir", caso contrário clique no botão "Voltar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Índice </td>
               	<td class="textonormal">
               		<?php echo $IndCorrDesc?>
                	<input type="hidden" name="Critica" value="1">
                	<input type="hidden" name="IndCorrCodigo" value="<?php  echo $IndCorrCodigo; ?>">
                </td>
              </tr>
							<tr>
              	<td class="textonormal" bgcolor="#DCEDF7" height="20">Situação </td>
	              <td class="textonormal">
	                <?php  if( $Situacao == "1" ){ echo "ATIVO"; }else{ echo "INATIVO"; } ?>
                </td>
	            </tr>
            </table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right">
	          <input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Excluir')">
	          <input type="button" value="Voltar"  class="botao" onclick="javascript:enviar('Voltar')">
						<input type="hidden" name="Botao" value="">
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
