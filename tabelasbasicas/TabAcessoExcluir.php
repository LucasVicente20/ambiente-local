<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabAcessoIncluir.php
# Autor:    Roberta Costa
# Data:     15/04/03
# Objetivo: Programa de Inclusão de Acesso
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabAcessoAlterar.php' );
AddMenuAcesso( '/tabelasbasicas/TabAcessoSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']  == "POST"){
		$Botao            = $_POST['Botao'];
		$Critica          = $_POST['Critica'];
		$AcessoCodigo     = $_POST['AcessoCodigo'];
		$HierarquiaCodigo = $_POST['HierarquiaCodigo'];
}else{
		$AcessoCodigo     = $_GET['AcessoCodigo'];
		$HierarquiaCodigo = $_GET['HierarquiaCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabAcessoExcluir.php";

# Critica dos Campos #
if( $Botao == "Voltar" ){
		$Url = "TabAcessoAlterar.php?AcessoCodigo=$AcessoCodigo&HierarquiaCodigo=$HierarquiaCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit();
}else{
		# Critica dos Campos #
		if( $Critica == 1 ) {
				$Mens = 0;

				# Não deixa excluir se um acesso estiver ligado a tabela de perfil acesso #
		    $db     = Conexao();
		    $sql    = "SELECT COUNT(*) AS Qtd FROM SFPC.TBPERFILACESSO WHERE CACEPOCODI = $AcessoCodigo";
		    $result = $db->query($sql);
				if( PEAR::isError($result) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$Linha = $result->fetchRow();
				    $QtdLicitacao = $Linha[0];
				    if( $QtdLicitacao > 0 ) {
				        $Mensagem = urlencode("Exclusão Cancelada!<br>Acesso Relacionado com ($QtdLicitacao) Perfil(is)");
				        $Url = "TabAcessoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=2";
								if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				       	header("location: ".$Url);
				       	exit();
				    }else{
						    # Não deixa excluir se um acesso tiver subacessos #
						    $sql    = "SELECT COUNT(*) FROM SFPC.TBACESSOPORTAL WHERE CACEPOCODI <> CACEPOCPAI AND CACEPOCPAI = $AcessoCodigo";
						    $result = $db->query($sql);
								if( PEAR::isError($result) ){
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$Linha = $result->fetchRow();
								    $QtdGrupo = $Linha[0];
								    if( $QtdGrupo > 0 ) {
								        if ($Mens == 1){ $Mensagem .= "<br>"; }else{ $Mensagem .= "Exclusão Cancelada!<br>"; }

								        # Envia mensagem para página selecionar #
								        $Mensagem .= urlencode("Acesso Relacionado com ($QtdGrupo) Subacesso(s)");
								        $Url = "TabAcessoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=2";
												if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
								        header("location: ".$Url);
								        exit();
								    }
										if( $Mens == 0 ){
												# Exclui Acesso #
												$db->query("BEGIN TRANSACTION");
												$sql    = "DELETE FROM SFPC.TBACESSOPORTAL WHERE CACEPOCODI = $AcessoCodigo ";
												$result = $db->query($sql);
												if( PEAR::isError($result) ){
														$db->query("ROLLBACK");
												    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$db->query("COMMIT");
														$db->query("END TRANSACTION");
														$db->disconnect();

														# Envia mensagem para página selecionar #
														$Mensagem = urlencode("Acesso Excluído com Sucesso");
														$Url = "TabAcessoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
														if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
														header("location: ".$Url);
														exit();
												}
								    }
								}
						}
				}
				$db->disconnect();
		}
}

if( $Critica == 0 ){
		$db     = Conexao();
		$sql    = "SELECT EACEPODESC, AACEPOORDE, EACEPOCAMI FROM SFPC.TBACESSOPORTAL WHERE CACEPOCODI = $AcessoCodigo";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$AcessoDescricao = $Linha[0];
						$AcessoOrdem     = $Linha[1];
						$AcessoCaminho   = $Linha[2];
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
<form action="TabAcessoExcluir.php" method="post" name="Acesso">
<br><br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Acesso > Manter
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
		<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#ffffff">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	        	MANTER - ACESSO
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
               Para confirmar a exclusão de um Acesso clique no botão "Excluir", caso contrário clique no botão "Voltar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" summary="">
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Acesso</td>
               	<td class="textonormal">
               		<?php echo $AcessoDescricao; ?>
                	<input type="hidden" name="Critica" value="1">
                	<input type="hidden" name="HierarquiaCodigo" value="<?php echo $HierarquiaCodigo ?>">
                	<input type="hidden" name="AcessoCodigo" value="<?php echo $AcessoCodigo ?>">
                </td>
              </tr>
              <tr>
              	<td class="textonormal" bgcolor="#DCEDF7" height="20">Caminho</td>
	              <td class="textonormal"><?php echo $AcessoCaminho; ?></td>
	            </tr>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Hierarquia</td>
	              <td class="textonormal"><?php echo $AcessoOrdem." - ".$AcessoDescricao; ?></td>
	            </tr>
              <tr>
              	<td class="textonormal" bgcolor="#DCEDF7" height="20">Caminho</td>
	              <td class="textonormal"><?php echo $AcessoOrdem; ?></td>
	            </tr>
            </table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right">
            <input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Excluir');">
            <input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
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
