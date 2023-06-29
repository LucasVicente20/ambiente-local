<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabLeiExcluir.php
# Autor:    Marcos Túlio de Almeida Alves
# Data:     21/09/11
# Objetivo: Programa de Exclusão do Tipo de Lei
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabTipoLeiAlterar.php' );
AddMenuAcesso( '/tabelasbasicas/TabTipoLeiSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']  == "POST"){
		$Botao        = $_POST['Botao'];
		$TipoLeiCodigo = $_POST['TipoLeiCodigo'];
}else{
		$TipoLeiCodigo = $_GET['TipoLeiCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabTipoLeiExcluir.php";

# Critica dos Campos #
$db = Conexao();
if( $Botao == "Voltar" ){
		$Url = "TabTipoLeiAlterar.php?TipoLeiCodigo=$TipoLeiCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit();
}elseif( $Botao == "Excluir" ){
		$Mens     = 0;
        $Mensagem = "Informe: ";
                        #VERIFICA SE TIPO DE LEI TEM ALGUMA RELAÇÃO COM LEI#
						$db     = Conexao();
						$sql    = "SELECT COUNT(*) FROM SFPC.TBTIPOLEIPORTAL T, SFPC.TBLEIPORTAL B 
						           WHERE T.CTPLEITIPO = B.CTPLEITIPO AND T.CTPLEITIPO = $TipoLeiCodigo";
								 $result = $db->query($sql);
									if( PEAR::isError($result) ){
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
									}else{
											while( $Linha = $result->fetchRow() ){
													$QtdLei = $Linha[0];
											}
											if( $QtdLei > 0 ){
											   $Mens     = 1;
											   $Tipo     = 2;
											  
									                   # Envia mensagem para página selecionar #
														$Mensagem = urlencode("Exclusão cancelada! Tipo de Lei está relacionado com alguma Lei");
														$Url = "TabTipoLeiSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
														if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
														header("location: ".$Url);
														exit();
                                                    }			
											}          
                                      							   


                                    
									if( $QtdLei == 0){
										   # Exclui Ocorrência #
										   $db->query("BEGIN TRANSACTION");
										   $sql    = "DELETE FROM SFPC.TBTIPOLEIPORTAL WHERE CTPLEITIPO = $TipoLeiCodigo";
										   $result = $db->query($sql);
												if( PEAR::isError($result) ){
														$db->query("ROLLBACK");
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$db->query("COMMIT");
														$db->query("END TRANSACTION");
														$db->disconnect();

														# Envia mensagem para página selecionar #
														$Mensagem = urlencode("Tipo de lei Excluído com Sucesso");
														$Url = "TabTipoLeiSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
														if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
														header("location: ".$Url);
														exit();
												}
									}



}
if( $Botao == "" ){
		$sql    = "SELECT ETPLEITIPO FROM SFPC.TBTIPOLEIPORTAL WHERE CTPLEITIPO = $TipoLeiCodigo";
		$result = $db->query($sql);
		if (PEAR::isError($result)) {
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$TipoLeiDescricao = $Linha[0];
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
	document.TipoLei.Botao.value=valor;
	document.TipoLei.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabTipoLeiExcluir.php" method="post" name="TipoLei">
<br><br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Tipo de Lei > Excluir
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
	           EXCLUIR - TIPO DE LEI
	                    </td>
        </tr>
        <tr>
          <td class="textonormal" >
             <p align="justify">
               Para confirmar a exclusão do Tipo de lei clique no botão "Excluir", caso contrário clique no botão "Voltar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo de Lei </td>
               	<td class="textonormal">
               		<?php echo $TipoLeiDescricao?>
                	<input type="hidden" name="TipoLeiCodigo" value="<?php echo $TipoLeiCodigo ?>">
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
