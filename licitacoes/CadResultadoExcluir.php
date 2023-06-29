<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadResultadoExcluir.php
# Autor:    Rossana Lira
# Data:     02/05/03
# Objetivo: Programa de Exclusão do Resultado da Licitação
#----
# Alterado: Ariston
# Data:     26/05/2011 - Salvar usuário responsável pela exclusão da fase
#----
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/licitacoes/CadResultadoAlterar.php' );
AddMenuAcesso( '/licitacoes/CadResultadoSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']  == "POST"){
		$Botao                = $_POST['Botao'];
		$Critica              = $_POST['Critica'];
		$Processo             = $_POST['Processo'];
		$ProcessoAno          = $_POST['ProcessoAno'];
		$ComissaoCodigo       = $_POST['ComissaoCodigo'];
		$OrgaoLicitanteCodigo = $_POST['OrgaoLicitanteCodigo'];
}else{
		$Processo             = $_GET['Processo'];
		$ProcessoAno          = $_GET['ProcessoAno'];
		$ComissaoCodigo       = $_GET['ComissaoCodigo'];
		$OrgaoLicitanteCodigo = $_GET['OrgaoLicitanteCodigo'];
}

# Critica dos Campos #
if( $Botao == "Voltar" ){
		$Url = "CadResultadoAlterar.php?Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit();
}else{
		$Mens = 0;
		if( $Critica == 1 ) {
			  # Exclui Resultado #
				$db     = Conexao();
				$db->query("BEGIN TRANSACTION");
	     	$sql    = "DELETE FROM SFPC.TBRESULTADOLICITACAO ";
				$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
				$sql   .= "   AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ";
				$sql   .= "   AND CORGLICODI = $OrgaoLicitanteCodigo ";
				$result = $db->query($sql);
				if( PEAR::isError($result) ){
						$db->query("ROLLBACK");
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
	     			# Adiciona Usuário no último registro da tabela de log #
			    	$sql    = "
							UPDATE SFPC.TBLICITACAO_LOG
							SET
								cusupocodi = ".$_SESSION['_cusupocodi_']."
							WHERE
								cusupocodi is NULL AND
								clplogcodi = (
									select last_value from SFPC.TBlicitacao_log_clplogcodi_sequ
								)
						";
						$result = $db->query($sql);
						if( PEAR::isError($result) ){
								$db->query("ROLLBACK");
								EmailErroSQL("Erro no SQL", __FILE__, __LINE__, "Erro no SQL", $sql, $result);
						}

						$db->query("COMMIT");
						$db->query("END TRANSACTION");
			     	$db->disconnect();

			     	# Envia mensagem para página selecionar #
			     	$Mensagem = "Resultado(s) da Licitação Excluído(s) com Sucesso";
			     	$Url = "CadResultadoSelecionar.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
			     	header("location: ".$Url);
			     	exit();
			  }
		}
}
if( $Critica == 0 ){
		# Busca descrição da comissão #
		$db     = Conexao();
		$sql    = "SELECT A.ECOMLIDESC FROM SFPC.TBCOMISSAOLICITACAO A WHERE A.CCOMLICODI = $ComissaoCodigo";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha = $result->fetchRow();
				$ComissaoDescricao = $Linha[0];
		}

		# Busca o(s) resultado(s) da Licitação
		$sql    = "SELECT ERESLIHABI, ERESLIINAB, ERESLIJULG, ERESLIREVO, ERESLIANUL ";
		$sql   .= "  FROM SFPC.TBRESULTADOLICITACAO ";
		$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
		$sql   .= "   AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ";
		$sql   .= "   AND CORGLICODI = $OrgaoLicitanteCodigo";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha = $result->fetchRow();
				$ResultadoHabi = $Linha[0];
				$ResultadoInab = $Linha[1];
				$ResultadoJulg = $Linha[2];
				$ResultadoRevo = $Linha[3];
				$ResultadoAnul = $Linha[4];
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
	document.Resultado.Botao.value=valor;
	document.Resultado.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" bgcolor="#FFFFFF" text="#000000" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadResultadoExcluir.php" method="post" name="Resultado">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Resultados > Manter
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
		<td class="textonormal"><br>
		<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	           EXCLUIR - RESULTADO DE LICITAÇÃO
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
               Para confirmar a exclusão do(s) Resultado(s) da Licitação clique no botão "Excluir", caso contrário clique no botão "Voltar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" summary="">
              <tr>
	              <td class="textonormal" bgcolor="#DCEDF7" height="20">Comissão </td>
	              <td class="textonormal"> <?php echo $ComissaoDescricao; ?></td>
	            </tr>
 							<tr>
	              <td class="textonormal" bgcolor="#DCEDF7" height="20">Processo </td>
	              <td class="textonormal"> <?php echo $Processo ?></td>
	            </tr>
							<tr>
	              <td class="textonormal" bgcolor="#DCEDF7" height="20">Ano </td>
	              <td class="textonormal">
	              <?php echo $ProcessoAno ?>
								<input type="hidden" name="Processo" value="<?echo $Processo?>"></td>
								<td class="textonormal"><input type="hidden" name="ProcessoAno" value="<?echo $ProcessoAno?>"></td>
								<td class="textonormal"><input type="hidden" name="ComissaoCodigo" value="<?echo $ComissaoCodigo?>"></td>
								<td class="textonormal"><input type="hidden" name="OrgaoLicitanteCodigo" value="<?echo $OrgaoLicitanteCodigo?>"></td>
								<td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
	            </tr>
	            <tr>
	              <td class="textonormal" bgcolor="#DCEDF7"> Empresas Habilitadas </td>
								<td class="textonormal"><?php echo $ResultadoHabi ?></td>
	            </tr>
	            <tr>
	              <td class="textonormal" bgcolor="#DCEDF7" width="25%"> Empresas Inabilitadas </td>
	              <td class="textonormal"><?php echo $ResultadoInab ?></td>
	            </tr>
	            <tr>
	              <td class="textonormal" bgcolor="#DCEDF7"> Julgamento </td>
								<td class="textonormal"><?php echo $ResultadoJulg ?></td>
							</tr>
	            <tr>
	              <td class="textonormal" bgcolor="#DCEDF7"> Revogação </td>
	              <td class="textonormal"><?php echo $ResultadoRevo ?></td>
	            </tr>
	            <tr>
	              <td class="textonormal" bgcolor="#DCEDF7"> Anulação </td>
								<td class="textonormal"><?php echo $ResultadoAnul ?></td>
	            </tr>
  	      	</table>
        	</td>
      	</tr>
        <tr>
        	<td class="textonormal" align="right">
          	<input type="submit" value="Excluir" class="botao" onclick="javascript:enviar('Excluir')">
            <input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar')">
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
