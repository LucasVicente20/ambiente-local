<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabSituacaoLotePregaoPresencialExcluir.php
# Autor:    Lucas Baracho
# Data:     08/05/17
# Objetivo: Programa de exclusão da situação do lote / pregão presencial
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabSituacaoLotePregaoPresencialAlterar.php' );
AddMenuAcesso( '/tabelasbasicas/TabSituacaoLotePregaoPresencialSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']  == "POST"){
		$Botao        = $_POST['Botao'];
		$SituacaoLote = $_POST['SituacaoLote'];
		$CodLote      = $_POST['CodLote'];
		
}else{
		$SituacaoLote = $_GET['SituacaoLote'];
		$CodLote      = $_GET['CodLote'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Critica dos Campos #
$db = Conexao();
if( $Botao == "Voltar" ){
		$Url = "TabSituacaoLotePregaoPresencialAlterar.php?SituacaoLote=$SituacaoLote";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit();
}elseif( $Botao == "Excluir" ){
		$Mens     = 0;
    $Mensagem = "Informe: ";

		# Verifica se a situação do lote tem algum lote relacionado #
		$sql    = "SELECT COUNT(cpreslsequ) FROM SFPC.tbpregaopresenciallote WHERE cpreslsequ=".$_SESSION['CodLote'];
		$result = $db->query($sql);
		if (PEAR::isError($result)) {
				$db->query("ROLLBACK");
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha = $result->fetchRow();
				$Qtd   = $Linha[0];
				if( $Qtd > 0 ) {
				    $Mensagem = "Exclusão Cancelada!<br>Situação do lote relacionada com ($Qtd) lote(s)";
				    $Url = "TabSituacaoLotePregaoPresencialSelecionar.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				    header("location: ".$Url);
				    exit();
				    exit;
				}else{
						{ 
						# Exclui a situação do lote #
							$db->query("BEGIN TRANSACTION");
							$sql    = "DELETE FROM SFPC.tbpregaopresencialsituacaolote WHERE cpreslsequ =".$_SESSION['CodLote'];
							$result = $db->query($sql);
							if( PEAR::isError($result) ){
							$db->query("ROLLBACK");
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
							}else{
								$db->query("COMMIT");
								$db->query("END TRANSACTION");
								$db->disconnect();

						# Envia mensagem para página selecionar #
							$Mensagem = urlencode("Situação do lote excluída com sucesso!");
							$Url = "TabSituacaoLotePregaoPresencialSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
								if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
								header("location: ".$Url);
																						exit();
												}
								   	}
								}
						}
				}
		
/*

if( $Botao == "" ){
		$sql    = "SELECT cpreslsequ, epreslnome FROM SFPC.tbpregaopresencialsituacaolote WHERE epreslnome = $SituacaoLote";
		$result = $db->query($sql);
		if (PEAR::isError($result)) {
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
					$SituacaoLote	 = $Linha[0];
				}
		}
} */
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
	document.TabSituacaoLotePregaoPresencialExcluir.Botao.value=valor;
	document.TabSituacaoLotePregaoPresencialExcluir.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabSituacaoLotePregaoPresencialExcluir.php" method="post" name="TabSituacaoLotePregaoPresencialExcluir">
<br><br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Pregão Presencial > Situação Lote > Manter
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
	           EXCLUIR - SITUAÇÃO DO LOTE
          </td>
        </tr>
        <tr>
          <td class="textonormal" >
             <p align="justify">
               Para confirmar a exclusão da situação do lote clique no botão "Excluir", caso contrário clique no botão "Voltar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Situação do lote:</td>
               	<td class="textonormal">
               		<?php echo $SituacaoLote ?>
                	<input type="hidden" name="SituacaoLote" value="<?php echo $SituacaoLote ?>">
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
