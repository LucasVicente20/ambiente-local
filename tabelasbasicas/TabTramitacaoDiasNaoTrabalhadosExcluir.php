<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabTramitacaoDiasNaoTrabalhadosExcluir.php
# Autor:    Ernesto Ferreira - PITANG AGILE IT	
# Data:     23/07/2018
# Objetivo: Tarefa Redmine 199105
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabTramitacaoDiasNaoTrabalhadosAlterar.php' );
AddMenuAcesso( '/tabelasbasicas/TabTramitacaoDiasNaoTrabalhadosSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']  == "POST"){
		$Botao			= $_POST['Botao'];
		$Critica		= $_POST['Critica'];
		$DianCodigo	    = $_POST['DianCodigo'];
}else{
		$DianCodigo = $_GET['DianCodigo'];
}

# Critica dos Campos #
if( $Botao == "Voltar" ){
	$Url = "TabTramitacaoDiasNaoTrabalhadosAlterar.php?DianCodigo=$DianCodigo";
	if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	header("location: ".$Url);
	exit();
}else{
	if( $Critica == 1 ) {
		$Mens     = 0;	
					
			if($Mens == 0){
				# Exclui ações #
				$db = Conexao();
				$db->query("BEGIN TRANSACTION");
				$sql    = "DELETE FROM SFPC.TBTRAMITACAODIASNAOTRABALHADOS WHERE CTDIANSEQU = $DianCodigo";
				
				$result = $db->query($sql);
				
				if( PEAR::isError($result) ){
					$db->query("ROLLBACK");
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
					$db->query("COMMIT");
					$db->query("END TRANSACTION");
					$db->disconnect();

					# Envia mensagem para página selecionar #
					$Mensagem = urlencode("Dias não trabalhados excluída com sucesso");
					$Url = "TabTramitacaoDiasNaoTrabalhadosSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
					
					if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
					header("location: ".$Url);
					exit();
				}
				$db->disconnect();
				
			}
		
	}
}
	if( $Critica == 0 ){
		$db     = Conexao();
		$sql    = "SELECT CTDIANSEQU, ATDIANANOT, ATDIANMEST, ATDIANDIAT, ETDIANDESC FROM SFPC.TBTRAMITACAODIASNAOTRABALHADOS WHERE CTDIANSEQU = $DianCodigo";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$dianDia		 = $Linha[1];
						$dianMes 		 = $Linha[2];
						$dianAno         = $Linha[3];
						$dianDescricao   = $Linha[4];
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
	document.DiasNaoTrabalhados.Botao.value=valor;
	document.DiasNaoTrabalhados.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabTramitacaoDiasNaoTrabalhadosExcluir.php" method="post" name="DiasNaoTrabalhados">
<br><br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    	<td align="left" class="textonormal">
      		<font class="titulo2">|</font>
      			<a href="../index.php">
			<font color="#000000">Página Principal</font></a> > Tabelas > Licitações > Tramitação > Dias não trabalhados > Manter > Excluir
		</td>
	</tr>
	<!-- Fim do Caminho-->
	<input type="hidden" name="DianCodigo" id="DianCodigo" value="<?php echo $DianCodigo ?>">
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
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#ffffff">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">EXCLUIR - Dias não trabalhados</td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
               Para confirmar a exclusão da Dias não trabalhados, clique no botão "Excluir", caso contrário clique no botão "Voltar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0">
			<tr>
				<td class="textonormal" bgcolor="#DCEDF7">Ano</td>
				<td class="textonormal"><?php echo $dianAno; ?></td>
			</tr>

			<tr>
				<td class="textonormal" bgcolor="#DCEDF7">Mês</td>
				<td class="textonormal"><?php echo $dianMes; ?></td>
			</tr>

			<tr>
				<td class="textonormal" bgcolor="#DCEDF7">Dia</td>
				<td class="textonormal"><?php echo $dianDia; ?>
			</tr> 

			<tr>
				<td class="textonormal" bgcolor="#DCEDF7">Descrição</td>
				<td class="textonormal"><?php echo $dianDescricao; ?></td>
				<input type="hidden" name="Critica" value="1">
			</tr>
            </table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right">
          	<input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Excluir');">
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