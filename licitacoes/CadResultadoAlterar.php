<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadResultadoAlterar.php
# Autor:    Rossana Lira
# Data:     02/05/03
# Objetivo: Programa de Alteração do Resultado da Licitação
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/licitacoes/CadResultadoExcluir.php' );
AddMenuAcesso( '/licitacoes/CadResultadoSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao                = $_POST['Botao'];
		$Critica              = $_POST['Critica'];
		$Processo             = $_POST['Processo'];
		$ProcessoAno          = $_POST['ProcessoAno'];
		$ComissaoCodigo       = $_POST['ComissaoCodigo'];
		$ComissaoDescricao    = $_POST['ComissaoDescricao'];
		$OrgaoLicitanteCodigo = $_POST['OrgaoLicitanteCodigo'];
		$ResultadoHabi        = strtoupper2(trim($_POST['ResultadoHabi']));
		$ResultadoInab        = strtoupper2(trim($_POST['ResultadoInab']));
		$ResultadoJulg        = strtoupper2(trim($_POST['ResultadoJulg']));
		$ResultadoRevo        = strtoupper2(trim($_POST['ResultadoRevo']));
		$ResultadoAnul        = strtoupper2(trim($_POST['ResultadoAnul']));
		$Resultados           = $_POST['Resultados'];
		$NCaracteres1         = $_POST['NCaracteres1'];
		$NCaracteres2         = $_POST['NCaracteres2'];
		$NCaracteres3         = $_POST['NCaracteres3'];
		$NCaracteres4         = $_POST['NCaracteres4'];
		$NCaracteres5         = $_POST['NCaracteres5'];
}else{
		$Processo             = $_GET['Processo'];
		$ProcessoAno          = $_GET['ProcessoAno'];
		$ComissaoCodigo       = $_GET['ComissaoCodigo'];
		$OrgaoLicitanteCodigo = $_GET['OrgaoLicitanteCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Redireciona para a página de excluir #
if( $Botao == "Excluir" ){
		$Url = "CadResultadoExcluir.php?Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	  header("location: ".$Url);
	  exit;
}elseif( $Botao == "Voltar" ){
	  header("location: CadResultadoSelecionar.php");
	  exit;
}else{
		# Critica dos Campos #
		if( $Critica == 1 ) {
				$Mens     = 0;
		    $Mensagem = "Informe: ";
				if( strlen($ResultadoHabi) > 8192 ){
						if ( $Mens == 1 ) { $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript: document.Resultado.ResultadoHabi.focus();\" class=\"titulo2\">Resultado das Empresas Habilitadas com até 8000 Caracteres ( atualmente com ". strlen($ResultadoHabi) ." )</a>";
				}
				if( strlen($ResultadoInab) > 8192 ){
						if ( $Mens == 1 ) { $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript: document.Resultado.ResultadoInabi.focus();\" class=\"titulo2\">Resultado das Empresas Inabilitadas com até 8000 Caracteres ( atualmente com ". strlen($ResultadoInab) ." )</a>";
				}
				if( strlen($ResultadoJulg) > 8192 ){
						if ( $Mens == 1 ) { $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript: document.Resultado.ResultadoJulg.focus();\" class=\"titulo2\">Resultado do Julgamento com até 8000 Caracteres ( atualmente com ". strlen($ResultadoJulg) ." )</a>";
				}
				if( strlen($ResultadoRevo) > 8192 ){
						if ( $Mens == 1 ) { $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript: document.Resultado.ResultadoRevo.focus();\" class=\"titulo2\">Resultado da Revogação com até 8000 Caracteres ( atualmente com ". strlen($ResultadoRevo) ." )</a>";
				}
				if( strlen($ResultadoAnul) > 8192 ){
						if ( $Mens == 1 ) { $Mensagem .= ", "; }
						$Mens = 1;$Tipo = 2;
						$Mensagem .= "<a href=\"javascript: document.Resultado.ResultadoAnul.focus();\" class=\"titulo2\">Resultado da Anulação com até 8000 Caracteres ( atualmente com ". strlen($ResultadoAnul) ." )</a>";
				}
			  if( $Mens == 0 ) {
			  		$Data = date("Y-m-d H:i:s");
				  	if( $Resultados == 0 ){
					  		if( $ResultadoHabi == "" ){
					  				$ResultadoHabiI = "NULL";
					  		}else{
					  				$ResultadoHabiI = str_replace("”"," ",$ResultadoHabi);
					  				$ResultadoHabiI = "'".$ResultadoHabiI."'";
					  		}
					  		if( $ResultadoInab == "" ){
					  				$ResultadoInabI = "NULL";
					  		}else{
					  				$ResultadoInabI = str_replace("”"," ",$ResultadoInab);
					  				$ResultadoInabI = "'".$ResultadoInabI."'";
					  		}
					  		if( $ResultadoJulg == "" ){
					  				$ResultadoJulgI = "NULL";
					  		}else{
					  				$ResultadoJulgI = str_replace("”"," ",$ResultadoJulg);
					  				$ResultadoJulgI = "'".$ResultadoJulgI."'";
					  		}
					  		if( $ResultadoRevo == "" ){
					  				$ResultadoRevoI = "NULL";
					  		}else{
					  				$ResultadoRevoI = str_replace("”"," ",$ResultadoRevo);
					  				$ResultadoRevoI = "'".$ResultadoRevoI."'";
					  		}
					  		if( $ResultadoAnul == "" ){
					  				$ResultadoAnulI = "NULL";
					  		}else{
					  				$ResultadoAnulI = str_replace("”"," ",$ResultadoAnul);
					  				$ResultadoAnulI = "'".$ResultadoAnulI."'";
					  		}

					  		# Insere Resultado #
					  		$db = Conexao();
					  		$db->query("BEGIN TRANSACTION");
								$sql  = "INSERT INTO SFPC.TBRESULTADOLICITACAO( ";
								$sql .= "CLICPOPROC, ALICPOANOP, CGREMPCODI, CCOMLICODI, ";
								$sql .= "CORGLICODI, ERESLIHABI, ERESLIINAB, ERESLIJULG, ";
								$sql .= "ERESLIREVO, ERESLIANUL, CUSUPOCODI, TRESLIULAT ";
								$sql .= ") VALUES ( ";
								$sql .= "$Processo, $ProcessoAno, ".$_SESSION['_cgrempcodi_'].", $ComissaoCodigo, ";
								$sql .= "$OrgaoLicitanteCodigo, $ResultadoHabiI, $ResultadoInabI, $ResultadoJulgI, ";
								$sql .= "$ResultadoRevoI, $ResultadoAnulI, ".$_SESSION['_cusupocodi_'].", '$Data')";
								$res  = $db->query($sql);
								if( PEAR::isError($res) ){
										$db->query("ROLLBACK");
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$db->query("COMMIT");
										$db->query("END TRANSACTION");

										# Envia mensagem para página selecionar #
						        $Mensagem = urlencode("Resultado(s) da Licitação Incluído(s) com Sucesso");
						        $Url = "CadResultadoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1&Critica=0";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						        header("location: ".$Url);
						        exit;
								}
						}else{
								# Atualiza Resultado #
								$db     = Conexao();
								$db->query("BEGIN TRANSACTION");
								$sql    = "UPDATE SFPC.TBRESULTADOLICITACAO ";
								$sql   .= "   SET ERESLIHABI = '$ResultadoHabi', ERESLIINAB = '$ResultadoInab', ";
								$sql   .= "       ERESLIJULG = '$ResultadoJulg', ERESLIREVO = '$ResultadoRevo', ";
								$sql   .= "       ERESLIANUL = '$ResultadoAnul', CUSUPOCODI = ".$_SESSION['_cusupocodi_'].", ";
								$sql   .= "       TRESLIULAT = '$Data' ";
								$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
								$sql   .= "   AND CCOMLICODI= $ComissaoCodigo AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ";
								$sql   .= "   AND CORGLICODI = $OrgaoLicitanteCodigo ";
								$result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$db->query("COMMIT");
										$db->query("END TRANSACTION");

										# Envia mensagem para página selecionar #
						        $Mensagem = urlencode("Resultado(s) da Licitação Alterado(s) com Sucesso");
						        $Url = "CadResultadoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1&Critica=0";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						        header("location: ".$Url);
						        exit;
						    }
			      }
		      	$db->disconnect();
				}
		}
}
if( $Critica <> 1 ){
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

		# Busca o(s) resultado(s) da Licitação #
		$sql    = "SELECT ERESLIHABI, ERESLIINAB, ERESLIJULG, ERESLIREVO, ERESLIANUL ";
		$sql   .= "  FROM SFPC.TBRESULTADOLICITACAO ";
		$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
		$sql   .= "   AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ";
		$sql   .= "   AND CORGLICODI = $OrgaoLicitanteCodigo";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Rows = $result->numRows();
				if( $Rows > 0 ){
						while( $Linha = $result->fetchRow() ){
								$Resultados    = 1;
								$ResultadoHabi = $Linha[0];
								$NCaracteres1  = strlen($Linha[0]);
								$ResultadoInab = $Linha[1];
								$NCaracteres2  = strlen($Linha[1]);
								$ResultadoJulg = $Linha[2];
								$NCaracteres3  = strlen($Linha[2]);
								$ResultadoRevo = $Linha[3];
								$NCaracteres4  = strlen($Linha[3]);
								$ResultadoAnul = $Linha[4];
								$NCaracteres5  = strlen($Linha[4]);
						}
				}else{
						$Resultados = 0;
				}
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

function ncaracteres(valor,tipo){
		if( tipo == 1 ){
				document.Resultado.NCaracteres1.value = '' +  document.Resultado.ResultadoHabi.value.length;
				if( navigator.appName == 'Netscape' && valor ) {  //Netscape Only
					document.Resultado.NCaracteres1.focus();
				}
		}
		if( tipo == 2 ){
				document.Resultado.NCaracteres2.value = '' +  document.Resultado.ResultadoInab.value.length;
				if( navigator.appName == 'Netscape' && valor ) {  //Netscape Only
					document.Resultado.NCaracteres2.focus();
				}
		}
		if( tipo == 3 ){
				document.Resultado.NCaracteres3.value = '' +  document.Resultado.ResultadoJulg.value.length;
				if( navigator.appName == 'Netscape' && valor ) {  //Netscape Only
					document.Resultado.NCaracteres3.focus();
				}
		}
		if( tipo == 4 ){
				document.Resultado.NCaracteres4.value = '' +  document.Resultado.ResultadoRevo.value.length;
				if( navigator.appName == 'Netscape' && valor ) {  //Netscape Only
					document.Resultado.NCaracteres4.focus();
				}
		}
		if( tipo == 5 ){
				document.Resultado.NCaracteres5.value = '' +  document.Resultado.ResultadoAnul.value.length;
				if( navigator.appName == 'Netscape' && valor ) {  //Netscape Only
					document.Resultado.NCaracteres5.focus();
				}
		}
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadResultadoAlterar.php" method="post" name="Resultado">
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
	           MANTER - RESULTADO DE LICITAÇÃO
          </td>
        </tr>
        <tr>
          <td class="textonormal" >
             <p align="justify">
             Para atualizar o(s) Resultado(s) da Licitação, preencha os dados abaixo e clique no botão "Alterar". Para apagar os Resultados da Licitação clique no botão "Excluir".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" summary="">
              <tr>
	              <td class="textonormal" bgcolor="#DCEDF7" height="20">Comissão </td>
	              <td class="textonormal"><?php echo $ComissaoDescricao; ?></td>
	            </tr>
 							<tr>
	              <td class="textonormal" bgcolor="#DCEDF7" height="20">Processo </td>
	              <td class="textonormal"><?php echo $Processo = substr($Processo + 10000,1); ?></td>
	            </tr>
	            <tr>
	              <td class="textonormal" bgcolor="#DCEDF7" height="20">Ano </td>
	              <td class="textonormal">
	              	<?php echo $ProcessoAno ?>
								</td>
	            </tr>
	            <tr>
	              <td class="textonormal" bgcolor="#DCEDF7"> Empresas Habilitadas </td>
	              <td class="textonormal">
	                máximo de 8000 caracteres
									<input type="text" name="NCaracteres1" disabled size="3" value="<?php echo $NCaracteres1 ?>" class="textonormal"><br>
									<textarea name="ResultadoHabi" cols="60" rows="3" OnKeyUp="javascript:ncaracteres(1,1)" OnBlur="javascript:ncaracteres(0,1)" OnSelect="javascript:ncaracteres(1,1)" class="textonormal"><?php echo $ResultadoHabi; ?></textarea>
			          </td>
	            </tr>
	            <tr>
	              <td class="textonormal" bgcolor="#DCEDF7"> Empresas Inabilitadas </td>
	              <td class="textonormal">
	                máximo de 8000 caracteres
									<input type="text" name="NCaracteres2" disabled size="3" value="<?php echo $NCaracteres2 ?>" class="textonormal"><br>
									<textarea name="ResultadoInab" cols="60" rows="3" OnKeyUp="javascript:ncaracteres(1,2)" OnBlur="javascript:ncaracteres(0,2)" OnSelect="javascript:ncaracteres(1,2)" class="textonormal"><?php echo $ResultadoInab; ?></textarea>
			          </td>
	            </tr>
	            <tr>
	              <td class="textonormal" bgcolor="#DCEDF7"> Julgamento </td>
	              <td class="textonormal">
	                máximo de 8000 caracteres
									<input type="text" name="NCaracteres3" disabled size="3" value="<?php echo $NCaracteres3 ?>" class="textonormal"><br>
									<textarea name="ResultadoJulg" cols="60" rows="3" OnKeyUp="javascript:ncaracteres(1,3)" OnBlur="javascript:ncaracteres(0,3)" OnSelect="javascript:ncaracteres(1,3)" class="textonormal"><?php echo $ResultadoJulg; ?></textarea>
			          </td>
	            </tr>
	            <tr>
	              <td class="textonormal" bgcolor="#DCEDF7"> Revogação </td>
	              <td class="textonormal">
	                máximo de 8000 caracteres
									<input type="text" name="NCaracteres4" disabled size="3" value="<?php echo $NCaracteres4 ?>" class="textonormal"><br>
									<textarea name="ResultadoRevo" cols="60" rows="3" OnKeyUp="javascript:ncaracteres(1,4)" OnBlur="javascript:ncaracteres(0,4)" OnSelect="javascript:ncaracteres(1,4)" class="textonormal"><?php echo $ResultadoRevo; ?></textarea>
			          </td>
	            </tr>
	            <tr>
	              <td class="textonormal" bgcolor="#DCEDF7"> Anulação </td>
	              <td class="textonormal">
	                máximo de 8000 caracteres
									<input type="text" name="NCaracteres5" disabled size="3" value="<?php echo $NCaracteres5 ?>" class="textonormal"><br>
									<textarea name="ResultadoAnul" cols="60" rows="3" OnKeyUp="javascript:ncaracteres(1,5)" OnBlur="javascript:ncaracteres(0,5)" OnSelect="javascript:ncaracteres(1,5)" class="textonormal"><?php echo $ResultadoAnul; ?></textarea>
			          </td>
	            </tr>
  	      	</table>
        	</td>
      	</tr>
        <tr>
 	        <td class="textonormal" align="right">
						<input type="hidden" name="ComissaoDescricao" value="<?php echo $ComissaoDescricao ?>">
						<input type="hidden" name="Processo" value="<?php  echo $Processo ?>">
						<input type="hidden" name="ProcessoAno" value="<?php echo $ProcessoAno ?>">
						<input type="hidden" name="ComissaoCodigo" value="<?php echo $ComissaoCodigo ?>">
						<input type="hidden" name="OrgaoLicitanteCodigo" value="<?php echo $OrgaoLicitanteCodigo ?>">
						<input type="hidden" name="Critica" value="1">
						<input type="hidden" name="Resultados" value="<?php echo $Resultados ?>">
            <input type="button" value="Alterar" class="botao" onclick="javascript:enviar('Alterar');">
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
<script language="javascript" type="">
<!--
document.Resultado.ResultadoHabi.focus();
//-->
</script>
</body>
</html>
