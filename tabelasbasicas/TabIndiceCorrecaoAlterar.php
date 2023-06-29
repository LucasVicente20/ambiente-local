<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabIndiceCorrecaoAlterar.php
# Autor:    João Batista Brito
# Data:     01/11/11
# Objetivo: Programa de Alteração do Índice Correção
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabIndiceCorrecaoExcluir.php' );
AddMenuAcesso( '/tabelasbasicas/TabIndiceCorrecaoSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao           = $_POST['Botao'];
		$Critica         = $_POST['Critica'];
		$IndCorrCodigo   = $_POST['IndCorrCodigo'];
		$IndCorrDesc	 = strtoupper2(trim($_POST['IndCorrDesc']));
		$Situacao        = $_POST['Situacao'];
}else{
		$IndCorrCodigo   = $_GET['IndCorrCodigo'];
		
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabIndiceCorrecaoAlterar.php";

# Redireciona para a página de excluir #
if( $Botao == "Excluir" ){
		$Url = "TabIndiceCorrecaoExcluir.php?IndCorrCodigo=$IndCorrCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	  header("location: ".$Url);
	  exit();
}else if( $Botao == "Voltar" ){
		header("location: TabIndiceCorrecaoSelecionar.php");
		exit();
}else{
		# Critica dos Campos #
		if( $Critica == 1 ) {
			  $Mens     = 0;
		    $Mensagem = "Informe: ";
		    if( $IndCorrDesc == "" ) {
			      $Critica   = 1;
		        $LerTabela = 0;
				 	  $Mens      = 1;
				 	  $Tipo      = 2;
				    $Mensagem .= "<a href=\"javascript:document.Indice.IndCorrDesc.focus();\" class=\"titulo2\">Indice</a>";
		    }
		    if( $Mens == 0 ){
						# Verifica a Duplicidade do Índice Correção #
						$db     = Conexao();
				   	$sql    = "SELECT COUNT(CINCORSEQU) FROM SFPC.TBINDICECORRECAO WHERE RTRIM(LTRIM(EINCORNOME)) = '$IndCorrDesc' AND CINCORSEQU <> $IndCorrCodigo ";
				 		$result = $db->query($sql);
						if (PEAR::isError($result)) {
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
				    		$Linha = $result->fetchRow();
								$Qtd = $Linha[0];
				    		if( $Qtd > 0 ) {
						    	$Mens     = 1;
						    	$Tipo     = 2;
									$Mensagem = "<a href=\"javascript:document.Indice.IndCorrDesc.focus();\" class=\"titulo2\"> Índice Já Cadastrado</a>";
								}else{
						        # Atualiza Índice #
						        $Data   = date("Y-m-d H:i:s");
						        $db->query("BEGIN TRANSACTION");
						        $sql    = "UPDATE SFPC.TBINDICECORRECAO ";
						        $sql   .= "   SET EINCORNOME = '$IndCorrDesc', cincorsiti = '$Situacao', ";
						        $sql   .= "       TINCORULAT = '$Data' ";
						        $sql   .= " WHERE CINCORSEQU  = $IndCorrCodigo";
						        $result = $db->query($sql);
										if( PEAR::isError($result) ){
												$db->query("ROLLBACK");
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$db->query("COMMIT");
												$db->query("END TRANSACTION");
								        $db->disconnect();

								        # Envia mensagem para página selecionar #
								        $Mensagem = urlencode("Índice Correção Alterado com Sucesso");
								        $Url = "TabIndiceCorrecaoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1&Critica=0";
												if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
								        header("location: ".$Url);
								        exit();
								     }
							  }
						}
						$db->disconnect();
		    }
		}
}
if( $Critica == 0 ){
		$db     = Conexao();
		$sql    = "SELECT EINCORNOME, CINCORSEQU, cincorsiti FROM SFPC.TBINDICECORRECAO WHERE CINCORSEQU = $IndCorrCodigo";
		$result = $db->query($sql);
		if (PEAR::isError($result)) {
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
					   $IndCorrDesc   = $Linha[0];
					   $IndCorrCodigo = $Linha[1];
					   $Situacao      = $Linha[2];
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
<form action="TabIndiceCorrecaoAlterar.php" method="post" name="Indice">
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
	        	MANTER - ÍNDICE CORREÇÃO
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
             Para atualizar o Índice, preencha os dados abaixo e clique no botão "Alterar". Para apagar o Índice clique no botão "Excluir".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Índice* </td>
               	<td class="textonormal">
               		<input type="text" name="IndCorrDesc" size="40" maxlength="30" value="<?php echo $IndCorrDesc?>" class="textonormal">
                	<input type="hidden" name="Critica" value="1">
                	<input type="hidden" name="IndCorrCodigo" value="<?php echo $IndCorrCodigo?>">
                </td>
              </tr>
              <tr>
              	<td class="textonormal" bgcolor="#DCEDF7">Situação* </td>
	              <td class="textonormal">
	                <?php  if( $Situacao == "1") { $DescSituacao = "ATIVO"; }else{ $DescSituacao = "INATIVO"; }	 ?>
	                <select name="Situacao" value="<?php  echo $DescSituacao; ?>" class="textonormal">
	        	        <option value="1" <?php  if ( $Situacao == "1" ) { echo "selected"; }?>>ATIVO</option>
                    <option value="2" <?php  if ( $Situacao == "2" ) { echo "selected"; }?>>INATIVO</option>
                  </select>
                </td>
	            </tr>
            </table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right">
	          <input type="button" value="Alterar" class="botao" onclick="javascript:enviar('Alterar');">
						<input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Excluir');">
	          <input name="voltar" type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar')">
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
<script language="javascript" type="">
<!--
document.Indice.IndCorrDesc.focus();
//-->
</script>
