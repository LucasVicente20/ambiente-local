<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabTipoCompraAlterar.php
# Autor:    Luiz Alves
# Data:     16/06/11
# Objetivo: Programa de Alteração do Tipo de Compra - Demanda Redmine: #3281
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------
# Alterado: Luiz Alves
# Data:     20/09/2011
# Objetivo: Correção dos erros - Demanda Redmine: #3651
# Acesso ao arquivo de funções #
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabTipoCompraExcluir.php' );
AddMenuAcesso( '/tabelasbasicas/TabTipoCompraSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao               = $_POST['Botao'];
		$CompraCodigo    = $_POST['CompraCodigo'];
		$CompraDescricao = strtoupper2(trim($_POST['CompraDescricao']));
}else{
	$CompraCodigo    = $_GET['CompraCodigo'];
	$CompraDescricao = $_GET['CompraDescricao'];
	}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabTipoCompraAlterar.php";

$db = Conexao();
if( $Botao == "Excluir" ){
		$Url = "TabTipoCompraExcluir.php?CompraCodigo=$CompraCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	  header("location: ".$Url);
	  exit();
}elseif( $Botao == "Voltar" ){
		header("location: TabTipoCompraSelecionar.php");
		exit();
}elseif( $Botao == "Alterar" ){
	  $Mens     = 0;
    $Mensagem = "Informe: ";
    if( $CompraDescricao == "" ) {
		 	  $Mens      = 1;
		 	  $Tipo      = 2;
		    $Mensagem .= "<a href=\"javascript:document.TabTipoCompraAlterar.CompraDescricao.focus();\" class=\"titulo2\">Compra</a>";
    }
	if( $CompraCodigo <= 5 ){

	        /*$Mens      = 1;
			$Tipo      = 2;
			*/
		    $Mensagem = urlencode("Alteração cancelada, este Tipo de Compra é padrão do portal e não pode ser alterado");
			$Url = "TabTipoCompraSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
		    if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	        header("location: ".$Url);
	        exit();
	} 
	
    if( $Mens == 0 ){

				# Verifica a Duplicidade do Tipo de Compra#
				$sql    = "SELECT COUNT(CTPCOMCODI) FROM SFPC.TBTIPOCOMPRA WHERE RTRIM(LTRIM(ETPCOMNOME)) = '$CompraDescricao' AND CTPCOMCODI <> $CompraCodigo ";
		 		$result = $db->query($sql);
				if( PEAR::isError($result) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
		    		$Linha = $result->fetchRow();
						$Qtd = $Linha[0];
		    		if( $Qtd > 0 ) {
					    	$Mens = 1;$Tipo = 2;
								$Mensagem = "<a href=\"javascript:document.TabTipoCompraAlterar.CompraDescricao.focus();\" class=\"titulo2\"> Tipo de Compra Já Cadastrado</a>";
						}else{

				        # Atualiza Tipo de Compra#
				        $Data   = date("Y-m-d H:i:s");
				       	$db->query("BEGIN TRANSACTION");
		   					$sql    = "UPDATE SFPC.TBTIPOCOMPRA";
				        $sql   .= "   SET ETPCOMNOME = '$CompraDescricao', TTPCOMULAT = '$Data', CUSUPOCODI = ".$_SESSION['_cusupocodi_']." ";
				        $sql   .= " WHERE CTPCOMCODI = $CompraCodigo";
				        $result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
				   			    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
						        $db->query("COMMIT");
						        $db->query("END TRANSACTION");
						        $db->disconnect();

				   			    # Envia mensagem para página selecionar #
						        $Mensagem = urlencode("Tipo de Compra Alterada com Sucesso");
						        $Url = "TabTipoCompraSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						        header("location: ".$Url);
					      }
					  }
				}
		}
		
}


if( $Botao == "" ){
		$sql    = "SELECT ETPCOMNOME, CTPCOMCODI FROM SFPC.TBTIPOCOMPRA WHERE CTPCOMCODI = $CompraCodigo";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$CompraDescricao = $Linha[0];
						$CompraCodigo    = $Linha[1];
				}
		}
}
$db->disconnect();
?>
<html>
<?php 
# Carrega o layout padrão #
layout();
?>
<script language="javascript">
<!--
function enviar(valor){
	document.TabTipoCompraAlterar.Botao.value=valor;
	document.TabTipoCompraAlterar.submit();
}
<?php  MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabTipoCompraAlterar.php" method="post" name="TabTipoCompraAlterar">
<br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr><br>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Tipo de Compra > Manter
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
	           MANTER - TIPO DE COMPRA
          </td>
        </tr>
        <tr>
          <td class="textonormal" >
             <p align="justify">
             Para atualizar o Tipo de Compra, preencha os dados abaixo e clique no botão "Alterar". Para apagar o Tipo de Compra clique no botão "Excluir".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Tipo de Compra* </td>
               	<td class="textonormal">
               		<input type="text" name="CompraDescricao" size="40" maxlength="60" value="<?php echo $CompraDescricao?>" class="textonormal">
                	<input type="hidden" name="CompraCodigo" value="<?php echo $CompraCodigo?>">
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr align="right">
          <td>
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
<script language="javascript">
<!--
document.TabTipoCompraAlterar.CompraDescricao.focus();
//-->
</script>
