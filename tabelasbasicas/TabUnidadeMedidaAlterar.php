<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabUnidadeMedidaAlterar.php
# Autor:    Roberta Costa
# Data:     31/05/05
# Objetivo: Programa de Alteração da Unidade de Medida
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabUnidadeMedidaExcluir.php' );
AddMenuAcesso( '/tabelasbasicas/TabUnidadeMedidaSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao          = $_POST['Botao'];
		$UnidadeMedida  = $_POST['UnidadeMedida'];
		$Sigla		 			= strtoupper2(trim($_POST['Sigla']));
		$Descricao 			= strtoupper2(trim($_POST['Descricao']));
}else{
		$UnidadeMedida = $_GET['UnidadeMedida'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

$db = Conexao();
if( $Botao == "Excluir" ){
		$Url = "TabUnidadeMedidaExcluir.php?UnidadeMedida=$UnidadeMedida";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	  header("location: ".$Url);
	  exit();
}elseif( $Botao == "Voltar" ){
		header("location: TabUnidadeMedidaSelecionar.php");
		exit();
}elseif( $Botao == "Alterar" ) {
	  $Mens     = 0;
    $Mensagem = "Informe: ";
    if( $Sigla == "" ) {
		 	  $Mens      = 1;
		 	  $Tipo      = 2;
		    $Mensagem .= "<a href=\"javascript:document.UnidadeMedida.Sigla.focus();\" class=\"titulo2\">Sigla</a>";
    }
    if( $Descricao == "" ) {
		 	  $Mens      = 1;
		 	  $Tipo      = 2;
		    $Mensagem .= "<a href=\"javascript:document.UnidadeMedida.Descricao.focus();\" class=\"titulo2\">Descrição</a>";
    }
    if( $Mens == 0 ){
				# Verifica a Duplicidade de UnidadeMedida #
				$sql    = "SELECT COUNT(CUNIDMCODI) FROM SFPC.TBUNIDADEDEMEDIDA ";
				$sql   .= " WHERE ( RTRIM(LTRIM(EUNIDMSIGL)) = '$Sigla' ";
				$sql   .= " OR    RTRIM(LTRIM(EUNIDMDESC)) = '$Descricao') AND CUNIDMCODI <> $UnidadeMedida ";
		 		$result = $db->query($sql);
				if( PEAR::isError($result) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
		    		$Linha = $result->fetchRow();
						$Qtd = $Linha[0];
		    		if( $Qtd > 0 ) {
					    	$Mens = 1;$Tipo = 2;
								$Mensagem = "<a href=\"javascript:document.UnidadeMedida.Descricao.focus();\" class=\"titulo2\"> Unidade de Medida Já Cadastrada</a>";
						}else{
				        # Atualiza UnidadeMedida #
				        $Data   = date("Y-m-d H:i:s");
				       	$db->query("BEGIN TRANSACTION");
		   					$sql    = "UPDATE SFPC.TBUNIDADEDEMEDIDA ";
				        $sql   .= "   SET EUNIDMSIGL = '$Sigla', EUNIDMDESC = '$Descricao', TUNIDMULAT = '$Data' ";
				        $sql   .= " WHERE CUNIDMCODI = $UnidadeMedida";
				        $result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
				   			    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
						        $db->query("COMMIT");
						        $db->query("END TRANSACTION");
						        $db->disconnect();

				   			    # Envia mensagem para página selecionar #
						        $Mensagem = urlencode("Unidade de Medida Alterada com Sucesso");
						        $Url = "TabUnidadeMedidaSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						        header("location: ".$Url);
						        exit();
					      }
					  }
				}
    }
}
if( $Botao == "" ){
		$sql    = "SELECT CUNIDMCODI, EUNIDMSIGL, EUNIDMDESC FROM SFPC.TBUNIDADEDEMEDIDA WHERE CUNIDMCODI = $UnidadeMedida";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$UnidadeMedida = $Linha[0];
						$Sigla         = $Linha[1];
						$Descricao		 = $Linha[2];

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
	document.UnidadeMedida.Botao.value=valor;
	document.UnidadeMedida.submit();
}
<?php  MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabUnidadeMedidaAlterar.php" method="post" name="UnidadeMedida">
<br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr><br>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Estoques > Unidade de Medida > Manter
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
	           MANTER - UNIDADE DE MEDIDA
          </td>
        </tr>
        <tr>
          <td class="textonormal" >
             <p align="justify">
             Para atualizar a Unidade de Medida, preencha os dados abaixo e clique no botão "Alterar". Para apagar a Unidade de Medida clique no botão "Excluir".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Sigla*</td>
               	<td class="textonormal">
               		<input type="text" name="Sigla" size="4" maxlength="4" value="<?php echo $Sigla?>" class="textonormal">
                </td>
              </tr>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Descrição*</td>
               	<td class="textonormal">
               		<input type="text" name="Descricao" size="40" maxlength="40" value="<?php echo $Descricao?>" class="textonormal">
                	<input type="hidden" name="UnidadeMedida" value="<?php echo $UnidadeMedida?>">
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
document.UnidadeMedida.Descricao.focus();
//-->
</script>
