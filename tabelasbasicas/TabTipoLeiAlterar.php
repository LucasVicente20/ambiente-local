<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabLeiAlterar.php
# Autor:    Marcos Túlio de Almeida Alves
# Data:     21/09/11
# Objetivo: Programa de Alteração do Tipo de Lei
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabTipoLeiExcluir.php' );
AddMenuAcesso( '/tabelasbasicas/TabTipoLeiSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao               = $_POST['Botao'];
		$TipoLeiCodigo       = $_POST['TipoLeiCodigo'];
		$TipoLeiDescricao    = strtoupper2(trim($_POST['TipoLeiDescricao']));
}else{
		$TipoLeiCodigo    = $_GET['TipoLeiCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabTipoLeiAlterar.php";

$db = Conexao();
if( $Botao == "Excluir" ){
		$Url = "TabTipoLeiExcluir.php?TipoLeiCodigo=$TipoLeiCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	  header("location: ".$Url);
	  exit();
}elseif( $Botao == "Voltar" ){
		header("location: TabTipoLeiSelecionar.php");
		exit();
}elseif( $Botao == "Alterar" ) {
	  $Mens     = 0;
    $Mensagem = "Informe: ";
    if( $TipoLeiDescricao == "" ) {
		 	  $Mens      = 1;
		 	  $Tipo      = 2;
		    $Mensagem .= "<a href=\"javascript:document.TipoLei.TipoLeiDescricao.focus();\" class=\"titulo2\">Tipo de Lei</a>";
    }
	/*else if (!preg_match("/^[a-zA-ZãÃáÁàÀêÊéÉèÈíÍìÌôÔõÕóÓòÒúÚùÙûÛçÇºª' ']+$/", $TipoLeiDescricao) ){
	    $Mens      = 1;
		    $Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.TipoLei.TipoLeiDescricao.focus();\" class=\"titulo2\">Prencha o campo Tipo de Lei corretamente ex: Municipal</a>";
    }
	*/
    if( $Mens == 0 ){
				# Verifica a Duplicidade do Tipo de Lei #
				$sql    = "SELECT COUNT(CTPLEITIPO) FROM SFPC.TBTIPOLEIPORTAL WHERE RTRIM(LTRIM(ETPLEITIPO)) = '$TipoLeiDescricao' AND CTPLEITIPO <> $TipoLeiCodigo ";
		 		$result = $db->query($sql);
				if( PEAR::isError($result) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
		    		$Linha = $result->fetchRow();
						$Qtd = $Linha[0];
		    		if( $Qtd > 0 ) {
					    	$Mens = 1;$Tipo = 2;
								$Mensagem = "<a href=\"javascript:document.TipoLei.TipoLeiDescricao.focus();\" class=\"titulo2\"> Tipo de Lei Já Cadastrado</a>";
						}else{
				        # Atualiza o Tipo de Lei #
				        $Data   = date("Y-m-d H:i:s");
				       	$db->query("BEGIN TRANSACTION");
		   			    $sql    = "UPDATE SFPC.TBTIPOLEIPORTAL ";
				        $sql   .= " SET ETPLEITIPO = '$TipoLeiDescricao',CUSUPOCODI = ".$_SESSION['_cusupocodi_'].",TTPLEIULAT = '$Data' ";
				        $sql   .= " WHERE CTPLEITIPO = $TipoLeiCodigo";
				        $result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
				   			    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
						        $db->query("COMMIT");
						        $db->query("END TRANSACTION");
						        $db->disconnect();

				   			    # Envia mensagem para página selecionar #
						        $Mensagem = urlencode("Tipo de lei Alterado com Sucesso");
						        $Url = "TabTipoLeiSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						        header("location: ".$Url);
					      }
					  }
				}
    }
}
if( $Botao == "" ){
		$sql    = "SELECT ETPLEITIPO, CTPLEITIPO FROM SFPC.TBTIPOLEIPORTAL WHERE CTPLEITIPO = $TipoLeiCodigo";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$TipoLeiDescricao = $Linha[0];
						$TipoLeiCodigo    = $Linha[1];
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
<form action="TabTipoLeiAlterar.php" method="post" name="TipoLei">
<br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr><br>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Tipo de Lei > Alterar
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
	           MANTER - TIPO DE LEI
          </td>
        </tr>
        <tr>
          <td class="textonormal" >
             <p align="justify">
             Para atualizar o Tipo de Lei, preencha os dados abaixo e clique no botão "Alterar". Para apagar o Tipo de Lei clique no botão "Excluir".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Tipo de Lei* </td>
               	<td class="textonormal">
               		<input type="text" name="TipoLeiDescricao" size="40" maxlength="60" value="<?php echo $TipoLeiDescricao?>" class="textonormal">
                	<input type="hidden" name="TipoLeiCodigo" value="<?php echo $TipoLeiCodigo?>">
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
document.TipoLei.TipoLeiDescricao.focus();
//-->
</script>
