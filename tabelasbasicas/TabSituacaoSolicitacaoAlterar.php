<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabSituacaoSolicitacaoAlterar.php
# Autor:    Luiz Alves
# Data:     22/10/04
# Objetivo: Programa de Alteração da Situação Solicitação - Demanda Redmine: #3281
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabSituacaoSolicitacaoExcluir.php' );
AddMenuAcesso( '/tabelasbasicas/TabSituacaoSolicitacaoSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao               = $_POST['Botao'];
		$SituacaoCodigo    = $_POST['SituacaoCodigo'];
		$SituacaoDescricao = strtoupper2(trim($_POST['SituacaoDescricao']));
}else{
		$SituacaoCodigo    = $_GET['SituacaoCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabSituacaoSolicitacaoAlterar.php";

$db = Conexao();
if( $Botao == "Excluir" ){
		$Url = "TabSituacaoSolicitacaoExcluir.php?SituacaoCodigo=$SituacaoCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	  header("location: ".$Url);
	  exit();
}elseif( $Botao == "Voltar" ){
		header("location: TabSituacaoSolicitacaoSelecionar.php");
		exit();
}elseif( $Botao == "Alterar" ) {
	  $Mens     = 0;
    $Mensagem = "Informe: ";
    if( $SituacaoDescricao == "" ) {
		 	  $Mens      = 1;
		 	  $Tipo      = 2;
		    $Mensagem .= "<a href=\"javascript:document.TabSituacaoSolicitacaoAlterar.SituacaoDescricao.focus();\" class=\"titulo2\">Situacao Solicitação</a>";
    }
	if( $SituacaoDescricao == 0 ) {
		 	  $Mens      = 1;
		 	  $Tipo      = 2;
		    $Mensagem .= "<a href=\"javascript:document.TabSituacaoSolicitacaoAlterar.SituacaoDescricao.focus();\" class=\"titulo2\">Situacao Solicitação deve ser maior que zero</a>";
    }
	if( $SituacaoCodigo <= 10 ){

		    $Mensagem = urlencode("Alteração cancelada, esta situação é padrão do portal e não pode ser alterada");
			$Url = "TabSituacaoSolicitacaoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
		    if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	        header("location: ".$Url);
	        exit();
	} 
    if( $Mens == 0 ){
				# Verifica a Duplicidade de Ocorrencia #
				$sql    = "SELECT COUNT(CSITSOCODI) FROM SFPC.TBSITUACAOSOLICITACAO WHERE RTRIM(LTRIM(ESITSONOME)) = '$SituacaoDescricao' AND CSITSOCODI <> $SituacaoCodigo ";
		 		$result = $db->query($sql);
				if( PEAR::isError($result) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
		    		$Linha = $result->fetchRow();
						$Qtd = $Linha[0];
		    		if( $Qtd > 0 ) {
					    	$Mens = 1;$Tipo = 2;
								$Mensagem = "<a href=\"javascript:document.TabSituacaoSolicitacaoAlterar.SituacaoDescricao.focus();\" class=\"titulo2\"> Situação Solicitação Já Cadastrado</a>";
						}
						
				        # Atualiza Situacao Solicitação  #
				        $Data   = date("Y-m-d H:i:s");
				       	$db->query("BEGIN TRANSACTION");
		   					$sql    = "UPDATE SFPC.TBSITUACAOSOLICITACAO ";
				        $sql   .= "   SET ESITSONOME = '$SituacaoDescricao', TSITSOULAT = '$Data', CUSUPOCODI = ".$_SESSION['_cusupocodi_']." ";
				        $sql   .= " WHERE CSITSOCODI = $SituacaoCodigo";
				        $result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
				   			    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
						        $db->query("COMMIT");
						        $db->query("END TRANSACTION");
						        $db->disconnect();

				   			    # Envia mensagem para página selecionar #
						        $Mensagem = urlencode("Situação Alterada com Sucesso");
						        $Url = "TabSituacaoSolicitacaoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						        header("location: ".$Url);
					      }
					  }
				}
    }
if( $Botao == "" ){
		$sql    = "SELECT ESITSONOME, CSITSOCODI FROM SFPC.TBSITUACAOSOLICITACAO WHERE CSITSOCODI = $SituacaoCodigo";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$SituacaoDescricao = $Linha[0];
						$SituacaoCodigo    = $Linha[1];
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
	document.TabSituacaoSolicitacaoAlterar.Botao.value=valor;
	document.TabSituacaoSolicitacaoAlterar.submit();
}
<?php  MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabSituacaoSolicitacaoAlterar.php" method="post" name="TabSituacaoSolicitacaoAlterar">
<br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr><br>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Situação Solicitação > Manter
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
	           MANTER - SITUAÇÃO SOLICITAÇÃO
          </td>
        </tr>
        <tr>
          <td class="textonormal" >
             <p align="justify">
             Para atualizar a Situação Solicitação, preencha os dados abaixo e clique no botão "Alterar". Para apagar a Situação Solicitação clique no botão "Excluir".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Situação Solicitação* </td>
               	<td class="textonormal">
               		<input type="text" name="SituacaoDescricao" size="40" maxlength="60" value="<?php echo $SituacaoDescricao?>" class="textonormal">
                	<input type="hidden" name="SituacaoCodigo" value="<?php echo $SituacaoCodigo?>">
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
document.TabSituacaoSolicitacaoAlterar.SituacaoDescricao.focus();
//-->
</script>
