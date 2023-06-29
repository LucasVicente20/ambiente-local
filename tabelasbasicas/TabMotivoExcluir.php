<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabMotivoExcluir.php
# Autor:    Marcos Túlio de Almeida Alves
# Data:     27/10/04
# Objetivo: Programa de Exclusão do Motivo
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabMotivoAlterar.php' );
AddMenuAcesso( '/tabelasbasicas/TabMotivoSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']  == "POST"){
		$Botao        = $_POST['Botao'];
		$MotivoCodigo = $_POST['MotivoCodigo'];
}else{
		$MotivoCodigo = $_GET['MotivoCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabMotivoExcluir.php";

# Critica dos Campos #
$db = Conexao();
if( $Botao == "Voltar" ){
		$Url = "TabMotivoAlterar.php?MotivoCodigo=$MotivoCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit();
}elseif( $Botao == "Excluir" ){
		$Mens     = 0;
    $Mensagem = "Informe: ";
                                 # Exclui o Motivo #
								$db->query("BEGIN TRANSACTION");
								$sql    = "DELETE FROM SFPC.TBMOTIVOITEMNAOLOGRADO WHERE CMOTNLSEQU = $MotivoCodigo";
								$result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$db->query("COMMIT");
										$db->query("END TRANSACTION");
										$db->disconnect();

										# Envia mensagem para página selecionar #
										$Mensagem = urlencode("Motivo Excluído com Sucesso");
										$Url = "TabMotivoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
										header("location: ".$Url);
										exit();
								}
				   	}




if( $Botao == "" ){
		$sql    = "SELECT EMOTNLNOME FROM SFPC.TBMOTIVOITEMNAOLOGRADO  WHERE CMOTNLSEQU = $MotivoCodigo";
		$result = $db->query($sql);
		if (PEAR::isError($result)) {
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$MotivoNome = $Linha[0];
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
<script language="javascript" type="">
<!--
function enviar(valor){
	document.Motivo.Botao.value=valor;
	document.Motivo.submit();
}
<?php  MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabMotivoExcluir.php" method="post" name="Motivo">
<br><br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Motivo > Excluir
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
	           EXCLUIR - MOTIVO DE ITEM NÃO LOGRADO
	                    </td>
        </tr>
        <tr>
          <td class="textonormal" >
             <p align="justify">
               Para confirmar a exclusão do Motivo clique no botão "Excluir", caso contrário clique no botão "Voltar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Motivo</td>
               	<td class="textonormal">
               		<?php  echo $MotivoNome; ?>
                	<input type="hidden" name="MotivoCodigo" value="<?php  echo $MotivoCodigo ?>">
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td align="right">
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
