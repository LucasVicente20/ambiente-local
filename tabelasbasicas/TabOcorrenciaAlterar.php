<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabOcorrenciaAlterar.php
# Autor:    Roberta Costa
# Data:     22/10/04
# Objetivo: Programa de Alteração da Ocorrência
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabOcorrenciaExcluir.php' );
AddMenuAcesso( '/tabelasbasicas/TabOcorrenciaSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao               = $_POST['Botao'];
		$OcorrenciaCodigo    = $_POST['OcorrenciaCodigo'];
		$OcorrenciaDescricao = strtoupper2(trim($_POST['OcorrenciaDescricao']));
}else{
		$OcorrenciaCodigo    = $_GET['OcorrenciaCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabOcorrenciaAlterar.php";

$db = Conexao();
if( $Botao == "Excluir" ){
		$Url = "TabOcorrenciaExcluir.php?OcorrenciaCodigo=$OcorrenciaCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	  header("location: ".$Url);
	  exit();
}elseif( $Botao == "Voltar" ){
		header("location: TabOcorrenciaSelecionar.php");
		exit();
}elseif( $Botao == "Alterar" ) {
	  $Mens     = 0;
    $Mensagem = "Informe: ";
    if( $OcorrenciaDescricao == "" ) {
		 	  $Mens      = 1;
		 	  $Tipo      = 2;
		    $Mensagem .= "<a href=\"javascript:document.Ocorrencia.OcorrenciaDescricao.focus();\" class=\"titulo2\">Ocorrencia</a>";
    }
    if( $Mens == 0 ){
				# Verifica a Duplicidade de Ocorrencia #
				$sql    = "SELECT COUNT(CFORTOCODI) FROM SFPC.TBFORNTIPOOCORRENCIA WHERE RTRIM(LTRIM(EFORTODESC)) = '$OcorrenciaDescricao' AND CFORTOCODI <> $OcorrenciaCodigo ";
		 		$result = $db->query($sql);
				if( PEAR::isError($result) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
		    		$Linha = $result->fetchRow();
						$Qtd = $Linha[0];
		    		if( $Qtd > 0 ) {
					    	$Mens = 1;$Tipo = 2;
								$Mensagem = "<a href=\"javascript:document.Ocorrencia.OcorrenciaDescricao.focus();\" class=\"titulo2\"> Ocorrencia Já Cadastrado</a>";
						}else{
				        # Atualiza Ocorrencia #
				        $Data   = date("Y-m-d H:i:s");
				       	$db->query("BEGIN TRANSACTION");
		   					$sql    = "UPDATE SFPC.TBFORNTIPOOCORRENCIA ";
				        $sql   .= "   SET EFORTODESC = '$OcorrenciaDescricao', TFORTOULAT = '$Data' ";
				        $sql   .= " WHERE CFORTOCODI = $OcorrenciaCodigo";
				        $result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
				   			    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
						        $db->query("COMMIT");
						        $db->query("END TRANSACTION");
						        $db->disconnect();

				   			    # Envia mensagem para página selecionar #
						        $Mensagem = urlencode("Ocorrência Alterada com Sucesso");
						        $Url = "TabOcorrenciaSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						        header("location: ".$Url);
					      }
					  }
				}
    }
}
if( $Botao == "" ){
		$sql    = "SELECT EFORTODESC, CFORTOCODI FROM SFPC.TBFORNTIPOOCORRENCIA WHERE CFORTOCODI = $OcorrenciaCodigo";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$OcorrenciaDescricao = $Linha[0];
						$OcorrenciaCodigo    = $Linha[1];
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
	document.Ocorrencia.Botao.value=valor;
	document.Ocorrencia.submit();
}
<?php  MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabOcorrenciaAlterar.php" method="post" name="Ocorrencia">
<br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr><br>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Ocorrência > Manter
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
	           MANTER - OCORRÊNCIA
          </td>
        </tr>
        <tr>
          <td class="textonormal" >
             <p align="justify">
             Para atualizar a Ocorrência, preencha os dados abaixo e clique no botão "Alterar". Para apagar o Ocorrência clique no botão "Excluir".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Ocorrência* </td>
               	<td class="textonormal">
               		<input type="text" name="OcorrenciaDescricao" size="40" maxlength="60" value="<?php echo $OcorrenciaDescricao?>" class="textonormal">
                	<input type="hidden" name="OcorrenciaCodigo" value="<?php echo $OcorrenciaCodigo?>">
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
document.Ocorrencia.OcorrenciaDescricao.focus();
//-->
</script>
