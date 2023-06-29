<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabUnidadeMedidaAlterar.php
# Autor:    Lucas Baracho
# Data:     04/05/17
# Objetivo: Programa de alteração da situação do lote / pregão presencial
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabSituacaoLotePregaoPresencialExcluir.php' );
AddMenuAcesso( '/tabelasbasicas/TabSituacaoLotePregaoPresencialSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao          = $_POST['Botao'];
	    $SituacaoLote	= strtoupper2(trim($_POST['SituacaoLote']));
		$CodLote      = $_POST['CodLote'];
}else{
		$CodLote   = $_GET['CodLote'];
		$_SESSION['CodLote'] = $CodLote;
}

if($CodLote > 0)
{
	$db     = Conexao();
	$sql    = "SELECT epreslnome FROM SFPC.tbpregaopresencialsituacaolote WHERE cpreslsequ = $CodLote";
	$result = $db->query($sql);

	if( PEAR::isError($result) )
	{
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	}
	else
	{
		$Linha = $result->fetchRow();
	}

	$_SESSION['SituacaoLote'] = $Linha[0];
	
	$db->disconnect();
}
# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

$db = Conexao();

$CodigoUsuario = $_SESSION['_cusupocodi_'];

if( $Botao == "Excluir" ){
		$Url = "TabSituacaoLotePregaoPresencialExcluir.php?SituacaoLote=$SituacaoLote&CodLote=".$_SESSION['CodLote'];
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	  header("location: ".$Url);
	  exit();
}elseif( $Botao == "Voltar" ){
		header("location: TabSituacaoLotePregaoPresencialSelecionar.php");
		exit();
}elseif( $Botao == "Alterar" ) {
	  $Mens     = 0;
    $Mensagem = "Informe: ";
    
    if( $SituacaoLote == "" ) {
		 	  $Mens      = 1;
		 	  $Tipo      = 2;
		    $Mensagem .= "<a href=\"javascript:document.SituacaoLote.Descricao.focus();\" class=\"titulo2\">Situacao</a>";
    }
    if( $Mens == 0 ){
				# Verifica a Duplicidade de SituacaoLote #
				$sql    = " SELECT COUNT(cpreslsequ) FROM SFPC.tbpregaopresencialsituacaolote ";
				$sql   .= " WHERE (RTRIM(LTRIM(epreslnome))) = '$SituacaoLote' ";
				$result = $db->query($sql);
				if( PEAR::isError($result) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
		    		$Linha = $result->fetchRow();
						$Qtd = $Linha[0];
		    		if( $Qtd > 0 ) {
					    	$Mens = 1;$Tipo = 2;
							$Mensagem = "<a href=\"javascript:document.SituacaoLote.Descricao.focus();\" class=\"titulo2\">Situação do lote já cadastrada!</a>";
						}else{
				        # Atualiza SituacaoLote #
				        $Data   = date("Y-m-d H:i:s");
				       	$db->query("BEGIN TRANSACTION");
		   				$sql    = " UPDATE SFPC.tbpregaopresencialsituacaolote ";
				        $sql   .= " SET epreslnome = '$SituacaoLote', cusupocodi = '$CodigoUsuario', tpreslulat = '$Data' ";
				        $sql   .= " WHERE cpreslsequ =".$_SESSION['CodLote'];
				        $result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
				   			    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
						        $db->query("COMMIT");
						        $db->query("END TRANSACTION");
						        $db->disconnect();

				   			    # Envia mensagem para página selecionar #
						        $Mensagem = urlencode("Situação do lote alterada com sucesso!");
						        $Url = "TabSituacaoLotePregaoPresencialSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						        header("location: ".$Url);
						        exit();
					      }
					  }
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
	document.SituacaoLote.Botao.value=valor;
	document.SituacaoLote.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabSituacaoLotePregaoPresencialAlterar.php" method="post" name="SituacaoLote">
<br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr><br>
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
	           MANTER - SITUAÇÃO DO LOTE
          </td>
        </tr>
        <tr>
          <td class="textonormal" >
             <p align="justify">
			 Para atualizar a situação do lote preencha o campo abaixo e clique no botão "Alterar".
			 Para apagar a situação do lote clique no botão "Excluir".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Situação do lote:</td>
               	<td class="textonormal">
               		<input type="text" name="SituacaoLote" size="40" maxlength="40" value="<?php echo $_SESSION['SituacaoLote']?>" class="textonormal">
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
document.Lote.SituacaoLote.focus();
//-->
</script>
