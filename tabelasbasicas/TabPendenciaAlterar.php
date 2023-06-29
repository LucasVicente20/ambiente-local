<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabPendenciaAlterar.php
# Autor:    Roberta Costa
# Data:     27/12/04
# Objetivo: Programa de Alteração da Pendência
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabPendenciaExcluir.php' );
AddMenuAcesso( '/tabelasbasicas/TabPendenciaSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao               = $_POST['Botao'];
		$PendenciaCodigo    = $_POST['PendenciaCodigo'];
		$PendenciaDescricao = strtoupper2(trim($_POST['PendenciaDescricao']));
}else{
		$PendenciaCodigo    = $_GET['PendenciaCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabPendenciaAlterar.php";

$db = Conexao();
if( $Botao == "Excluir" ){
		$Url = "TabPendenciaExcluir.php?PendenciaCodigo=$PendenciaCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	  header("location: ".$Url);
	  exit();
}elseif( $Botao == "Voltar" ){
		header("location: TabPendenciaSelecionar.php");
		exit();
}elseif( $Botao == "Alterar" ) {
	  $Mens     = 0;
    $Mensagem = "Informe: ";
    if( $PendenciaDescricao == "" ) {
		 	  $Mens      = 1;
		 	  $Tipo      = 2;
		    $Mensagem .= "<a href=\"javascript:document.Pendencia.PendenciaDescricao.focus();\" class=\"titulo2\">Pendência</a>";
    }
    if( $Mens == 0 ){
				# Verifica a Duplicidade de Pendencia #
				$sql    = "SELECT COUNT(CTIPPECODI) FROM SFPC.TBTIPOPENDENCIA WHERE RTRIM(LTRIM(ETIPPEDESC)) = '$PendenciaDescricao' AND CTIPPECODI <> $PendenciaCodigo ";
		 		$result = $db->query($sql);
				if( PEAR::isError($result) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
		    		$Linha = $result->fetchRow();
						$Qtd = $Linha[0];
		    		if( $Qtd > 0 ) {
					    	$Mens = 1;$Tipo = 2;
								$Mensagem = "<a href=\"javascript:document.Pendencia.PendenciaDescricao.focus();\" class=\"titulo2\">Pendência Já Cadastrado</a>";
						}else{
				        # Atualiza Pendencia #
				        $Data   = date("Y-m-d H:i:s");
				       	$db->query("BEGIN TRANSACTION");
		   					$sql    = "UPDATE SFPC.TBTIPOPENDENCIA ";
				        $sql   .= "   SET ETIPPEDESC = '$PendenciaDescricao', TTIPPEULAT = '$Data' ";
				        $sql   .= " WHERE CTIPPECODI = $PendenciaCodigo";
				        $result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
				   			    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
						        $db->query("COMMIT");
						        $db->query("END TRANSACTION");
						        $db->disconnect();

				   			    # Envia mensagem para página selecionar #
						        $Mensagem = urlencode("Pendência Alterada com Sucesso");
						        $Url = "TabPendenciaSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						        header("location: ".$Url);
						        exit();
					      }
					  }
				}
    }
}
if( $Botao == "" ){
		$sql    = "SELECT ETIPPEDESC, CTIPPECODI FROM SFPC.TBTIPOPENDENCIA WHERE CTIPPECODI = $PendenciaCodigo";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$PendenciaDescricao = $Linha[0];
						$PendenciaCodigo    = $Linha[1];
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
	document.Pendencia.Botao.value=valor;
	document.Pendencia.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabPendenciaAlterar.php" method="post" name="Pendencia">
<br><br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Pendência > Manter
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
  <tr>
  	<td width="150"></td>
		<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="150"></td>
		<td class="textonormal">
		<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	           MANTER - PENDÊNCIA
          </td>
        </tr>
        <tr>
          <td class="textonormal" >
             <p align="justify">
             Para atualizar a Pendência, preencha os dados abaixo e clique no botão "Alterar". Para apagar a Pendência clique no botão "Excluir".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Pendencia* </td>
               	<td class="textonormal">
               		<input type="text" name="PendenciaDescricao" size="70" maxlength="100" value="<?php echo $PendenciaDescricao?>" class="textonormal">
                	<input type="hidden" name="PendenciaCodigo" value="<?php echo $PendenciaCodigo?>">
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
document.Pendencia.PendenciaDescricao.focus();
//-->
</script>
