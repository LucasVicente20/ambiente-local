<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabModalidadeExcluir.php
# Autor:    Rossana Lira
# Data:     03/04/03
# Objetivo: Programa de Exclusão da Modalidade
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabModalidadeAlterar.php' );
AddMenuAcesso( '/tabelasbasicas/TabModalidadeSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']  == "POST"){
		$Botao      = $_POST['Botao'];
		$Modalidade = $_POST['Modalidade'];
}else{
		$Modalidade = $_GET['Modalidade'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Voltar" ){
		$Url = "TabModalidadeAlterar.php?Modalidade=$Modalidade";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit();
}elseif( $Botao == "Excluir" ){
		# Critica dos Campos #
		$Mens     = 0;
    $Mensagem = "Informe: ";

    # Verifica se a Modalidade está relacionada com alguma licitação #
    $db     = Conexao();
    $sql    = "SELECT COUNT(*) FROM SFPC.TBLICITACAOPORTAL WHERE CMODLICODI = $Modalidade";
		$result = $db->query($sql);
		if (PEAR::isError($result)) {
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Qtd = $result->fetchRow();
		    if( $Qtd[0] > 0 ){
		        $Mensagem = urlencode("Exclusão Cancelada!<br>Modalidade Relacionada com ($QtdLicitacao) Licitação(ões)");
		        $Url = "TabModalidadeSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=2";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		        header("location: ".$Url);
		        exit();
		    }
		    if( $Mens == 0 ){
						# Exclui Modalidade #
						$db->query("BEGIN TRANSACTION");
						$sql = "DELETE FROM SFPC.TBMODALIDADELICITACAO WHERE CMODLICODI = $Modalidade";
						$res = $db->query($sql);
						if( PEAR::isError($result) ){
								$db->query("ROLLBACK");
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$db->query("COMMIT");
								$db->query("END TRANSACTION");
				        $db->disconnect();

								# Envia mensagem para página selecionar #
								$Mensagem = urlencode("Modalidade Excluída com Sucesso");
								$Url = "TabModalidadeSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
								if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
								header("location: ".$Url);
								exit();
						}
		    }
		}
		$db->disconnect();
}
if( $Botao == "" ){
		$db     = Conexao();
		$sql    = "SELECT EMODLIDESC, AMODLIORDE FROM SFPC.TBMODALIDADELICITACAO WHERE CMODLICODI = $Modalidade";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha = $result->fetchRow();
				$ModalidadeDescricao = $Linha[0];
				$Ordem               = $Linha[1];
		}
		$db->disconnect();
}
?>
<html>
<?
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.Modalidade.Botao.value=valor;
	document.Modalidade.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabModalidadeExcluir.php" method="post" name="Modalidade">
<br><br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Modalidade > Manter
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
	        	EXCLUIR - MODALIDADE
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
               Para confirmar a exclusão da Modalidade clique no botão "Excluir", caso contrário clique no botão "Voltar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" width="100%" summary="">
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Modalidade</td>
               	<td class="textonormal">
               		<?php echo $ModalidadeDescricao; ?>
                	<input type="hidden" name="Modalidade" value="<?php echo $Modalidade; ?>">
                </td>
              </tr>
              <tr>
								<td class="textonormal"  bgcolor="#DCEDF7" height="20">Ordem de Exibição</td>
								<td class="textonormal"><?php echo $Ordem; ?></td>
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
