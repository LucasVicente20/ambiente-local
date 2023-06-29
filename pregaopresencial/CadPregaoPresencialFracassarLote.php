<?php
# -------------------------------------------------------------------------
# Portal da DGCO teste
# Programa: CadPregaoPresencialFracassarLote.php
# Autor:    Hélio Miranda
# Data:     29/07/2016
# Objetivo: Mudar a situação do Lote para fracessado
# OBS.:     Tabulação 2 espaços   
# -------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 73662
# -------------------------------------------------------------------------

header("Content-Type: text/html; charset=UTF-8",true);


# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabUsuarioAlterar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$UsuarioCodigo = $_POST['UsuarioCodigo'];
		$Critica       = $_POST['Critica'];
		
		$_SESSION['Botao']						= $_POST['Botao'];
		$NCaracteresO        					= $_POST['NCaracteresO'];
		$_SESSION['MotivoFracasso'] 			= strtoupper( $_POST['MotivoFracasso']);

		
}else{
		$Critica       							= $_GET['Critica'];
		$Mensagem      							= urldecode($_GET['Mensagem']);
		$Mens          							= $_GET['Mens'];
		$Tipo          							= $_GET['Tipo'];
}

$TamanhoMaximoParagrafos  = 4999;

if($_SESSION['Botao'] == "FracassarLote")
{
	$_SESSION['Botao'] = null;
	
	$CodLoteSelecionado								= $_SESSION['CodLoteSelecionado'];
	$CodFornecedorVencedorLoteSelecionado			= $_SESSION['CodFornecedorVencedorLoteSelecionado'];
	$MotivoFracasso									= $_SESSION['MotivoFracasso'];
	
	if($MotivoFracasso != "")
	{
		if($CodLoteSelecionado > 0 and $CodFornecedorVencedorLoteSelecionado > 0)
		{
			$db     = Conexao();
					
			$sql = "UPDATE sfpc.tbpregaopresenciallote SET cpreslsequ = 5, epregtdess = '$MotivoFracasso' WHERE cpregtsequ = $CodLoteSelecionado";		
			$res = $db->query($sql);
			if (PEAR::isError($res)) {
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			}	
			
			$sql = "UPDATE sfpc.tbpregaopresencialclassificacao SET epregcmoti = '$MotivoFracasso', cpresfsequ = 2 WHERE cpregtsequ = $CodLoteSelecionado AND cpregfsequ = $CodFornecedorVencedorLoteSelecionado";		
			$res = $db->query($sql);
			if (PEAR::isError($res)) {
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			}		

			$db->disconnect();
			
						
			echo "<script>opener.document.CadPregaoPresencialSessaoPublica.Destino.value = 'E';</script>";
			echo "<script>opener.document.CadPregaoPresencialSessaoPublica.CodLoteSelecionado.value = $CodLoteSelecionado;</script>";
			echo "<script>opener.document.CadPregaoPresencialSessaoPublica.Botao.value = 'SelecionarLote';</script>";
			echo "<script>opener.document.CadPregaoPresencialSessaoPublica.submit()</script>";			
			
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 1;
			$_SESSION['Mensagem'] .= "- Lote FRACASSADO com sucesso! <br />";		
			$_SESSION['Mensagem'] .= "- Motivo:  $MotivoFracasso <br />";
			$_SESSION['CodSituacaoLoteSelecionado'] = 5;
		}
		else if ($CodLoteSelecionado > 0 and $CodFornecedorVencedorLoteSelecionado == 0)
		{
			$db     = Conexao();
			
			$sql = "UPDATE sfpc.tbpregaopresenciallote SET cpreslsequ = 5, epregtdess = '$MotivoFracasso' WHERE cpregtsequ = $CodLoteSelecionado";		
			$res = $db->query($sql);
			if (PEAR::isError($res)) {
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			}			

			$db->disconnect();
			
						
			echo "<script>opener.document.CadPregaoPresencialSessaoPublica.Destino.value = 'E';</script>";
			echo "<script>opener.document.CadPregaoPresencialSessaoPublica.CodLoteSelecionado.value = $CodLoteSelecionado;</script>";
			echo "<script>opener.document.CadPregaoPresencialSessaoPublica.Botao.value = 'SelecionarLote';</script>";
			echo "<script>opener.document.CadPregaoPresencialSessaoPublica.submit()</script>";			
			
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 1;
			$_SESSION['Mensagem'] .= "- Lote FRACASSADO com sucesso! <br />";		
			$_SESSION['Mensagem'] .= "- Motivo:  $MotivoFracasso <br />";
			$_SESSION['CodSituacaoLoteSelecionado'] = 5;			
		}
	}
	else
	{
		$_SESSION['Mens'] = 1;
		$_SESSION['Tipo'] = 2;
		$_SESSION['Mensagem'] .= "- Para fracassar um Lote, deve-se informar o motivo!";		
	}
}

if($_SESSION['Botao'] == "DesfazerFracassarLote")
{
	$_SESSION['Botao'] = null;
	$CodLoteSelecionado								= $_SESSION['CodLoteSelecionado'];
	$CodFornecedorVencedorLoteSelecionado			= $_SESSION['CodFornecedorVencedorLoteSelecionado'];
	
	if($CodLoteSelecionado > 0 and $CodFornecedorVencedorLoteSelecionado > 0)
	{
		$db     = Conexao();
				
		$sql = "UPDATE sfpc.tbpregaopresenciallote SET cpreslsequ = 2, epregtdess = '' WHERE cpregtsequ = $CodLoteSelecionado";		
		$res = $db->query($sql);
		if (PEAR::isError($res)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}
		
		$sql = "UPDATE sfpc.tbpregaopresencialclassificacao SET epregcmoti = '', cpresfsequ = 1 WHERE cpregtsequ = $CodLoteSelecionado AND cpregfsequ = $CodFornecedorVencedorLoteSelecionado";		
		$res = $db->query($sql);
		if (PEAR::isError($res)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}		

		$db->disconnect();
		
					
		echo "<script>opener.document.CadPregaoPresencialSessaoPublica.Destino.value = 'E';</script>";
		echo "<script>opener.document.CadPregaoPresencialSessaoPublica.CodLoteSelecionado.value = $CodLoteSelecionado;</script>";
		echo "<script>opener.document.CadPregaoPresencialSessaoPublica.Botao.value = 'SelecionarLote';</script>";
		echo "<script>opener.document.CadPregaoPresencialSessaoPublica.submit()</script>";			
		
		$_SESSION['Mens'] = 1;
		$_SESSION['Tipo'] = 1;
		$_SESSION['Mensagem'] .= "- Fracasso de Lote DESFEITO com sucesso!";
		$_SESSION['CodSituacaoLoteSelecionado'] = 2;
	}
	else if($CodLoteSelecionado > 0 and $CodFornecedorVencedorLoteSelecionado == 0)
	{
		$db     = Conexao();
				
		$sql = "UPDATE sfpc.tbpregaopresenciallote SET cpreslsequ = 2, epregtdess = '' WHERE cpregtsequ = $CodLoteSelecionado";		
		$res = $db->query($sql);
		if (PEAR::isError($res)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}		

		$db->disconnect();
		
					
		echo "<script>opener.document.CadPregaoPresencialSessaoPublica.Destino.value = 'E';</script>";
		echo "<script>opener.document.CadPregaoPresencialSessaoPublica.CodLoteSelecionado.value = $CodLoteSelecionado;</script>";
		echo "<script>opener.document.CadPregaoPresencialSessaoPublica.Botao.value = 'SelecionarLote';</script>";
		echo "<script>opener.document.CadPregaoPresencialSessaoPublica.submit()</script>";			
		
		$_SESSION['Mens'] = 1;
		$_SESSION['Tipo'] = 1;
		$_SESSION['Mensagem'] .= "- Fracasso de Lote DESFEITO com sucesso!";
		$_SESSION['CodSituacaoLoteSelecionado'] = 2;		
	}
}

?>

<html>
<head>
<title>Portal de Compras - Incluir Fornecedor</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<script language="javascript" type="">
<!--
function checktodos(){
	document.CadPregaoPresencialFracassarLote.Subclasse.value = '';
	document.CadPregaoPresencialFracassarLote.submit();
}
function enviar(valor){
	document.CadPregaoPresencialFracassarLote.Botao.value = valor;
	document.CadPregaoPresencialFracassarLote.submit();
}
function validapesquisa(){
	if( ( document.CadPregaoPresencialFracassarLote.MaterialDescricaoDireta.value != '' ) || ( document.CadPregaoPresencialFracassarLote.SubclasseDescricaoDireta.value != '') ) {
		if(document.CadPregaoPresencialFracassarLote.Grupo){
			document.CadPregaoPresencialFracassarLote.Grupo.value = '';
		}
		if(document.CadPregaoPresencialFracassarLote.Classe){
			document.CadPregaoPresencialFracassarLote.Classe.value = '';
		}
		document.CadPregaoPresencialFracassarLote.Botao.value = 'Validar';
	}
	if(document.CadPregaoPresencialFracassarLote.Subclasse){
		if(document.CadPregaoPresencialFracassarLote.SubclasseDescricaoFamilia.value != "") {
			document.CadPregaoPresencialFracassarLote.Subclasse.value = '';
		}
	}
	document.CadPregaoPresencialFracassarLote.submit();
}
function AbreJanela(url,largura,altura) {
	window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=45,top=150,width='+largura+',height='+altura);
}
function voltar(){
	self.close();
}
function remeter(){
	document.CadPregaoPresencialFracassarLote.submit();
}

function ncaracteresO(valor){
	document.CadPregaoPresencialFracassarLote.NCaracteresO.value = '' +  (document.CadPregaoPresencialFracassarLote.TamanhoMaximoParagrafos.value - document.CadPregaoPresencialFracassarLote.MotivoFracasso.value.length);
	if( navigator.appName == 'Netscape' && valor ){ //Netscape Only
		document.CadPregaoPresencialFracassarLote.NCaracteresO.focus();
	}
}

//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">

</head>
<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
<form action="CadPregaoPresencialFracassarLote.php" method="post" name="CadPregaoPresencialFracassarLote">
<table cellpadding="3" border="0" summary="" width="100%">
	<!-- Erro -->
	<tr>
		<td>
			<?php if( $_SESSION['Mens'] != 0 ){ ExibeMens($_SESSION['Mensagem'],$_SESSION['Tipo'],$_SESSION['Virgula']); }
			
			$_SESSION['Mens'] = null;
			$_SESSION['Tipo'] = null;
			$_SESSION['Mensagem'] = null	
			
			?>
		</td>
	</tr>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td class="textonormal">
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF" >
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	        	FRACASSAR LOTE
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
             Para fracassar um lote deve-se informar o motivo e clicar no botão: Fracassar Lote.
             </p>
          </td>
        </tr>
			
        <tr>
          <td>
            <table border="0" summary="" width="100%">
              <tr>
                <td class="textonormal" bgcolor="#FFFFFF">
					<table border="0" width="100%" summary="">
					  <tr>
							<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Nº Lote: </td>
							<td align="left" class="textonormal" colspan="3" >
							  <label><?php echo $_SESSION['NumeroLoteSelecionado']; ?></label>
							</td>							
					  </tr>
					  
					  <tr>
							<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Descrição Lote: </td>
							<td align="left" class="textonormal" colspan="3" >
							  <label><?php echo $_SESSION['DescricaoLoteSelecionado']; ?></label>
							</td>							
					  </tr>						  
					  
					  <tr>
							<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Tipo: </td>
							<td align="left" class="textonormal" colspan="3" >
							  <label><?php echo ($_SESSION['PregaoTipo'] == 'N' ? 'MENOR PREÇO' : 'MAIOR PREÇO');?></label>
							</td>							
					  </tr>					  
					  
					  <tr>
						<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold; cursor:help;" title="Preencher, apenas, em situação de fracasso." >Motivo do Fracasso: *</td>
						<td align="left" class="textonormal" colspan="3" >
							<input type="hidden" name="TamanhoMaximoParagrafos" value="<?=$TamanhoMaximoParagrafos?>" /> 
							
							<textarea
								name="MotivoFracasso"
								cols="80"
								rows="12"
								maxlength="<?=$TamanhoMaximoParagrafos?>"
								OnKeyUp="javascript:ncaracteresO(1)"
								OnBlur="javascript:ncaracteresO(0)"
								OnSelect="javascript:ncaracteresO(1)"
								class="textonormal"><?= $_SESSION['MotivoFracasso'] ?></textarea>							
							
							<br /> 
							
							<font class="textonormal">máximo de <?=$TamanhoMaximoParagrafos?> caracteres (Restantes: </font>
							
							<input
								disabled
								type="text"
								name="NCaracteresO"
								OnFocus="javascript:document.CadPregaoPresencialFiltroAta.MotivoFracasso.focus();"
								size="3"
								value="<?php echo (($NCaracteresO == "" or $NCaracteresO == null) ? $TamanhoMaximoParagrafos : $NCaracteresO) ?>"
								class="textonormal" />							
							)
						</td>						
					  </tr>					  
					  
					</table>				
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right">
			<?
				if($_SESSION['CodLoteSelecionado'] > 0 and $_SESSION['CodSituacaoLoteSelecionado'] < 3)
				{
			?>			
					<input type="submit" value="Fracassar Lote" class="botao" onclick="javascript:enviar('FracassarLote');">	
			<?
				}
				else if ($_SESSION['CodLoteSelecionado'] > 0 and $_SESSION['CodSituacaoLoteSelecionado'] == 5)
				{
			?>
					<input type="submit" value="Desfazer Fracasso do Lote" class="botao" onclick="javascript:enviar('DesfazerFracassarLote');">				
			<?
				}
			?>
			<input type="button" value="Voltar" class="botao" onclick="javascript:voltar();">
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
<script language="javascript" type="">
<!--
document.Usuario.UsuarioCodigo.focus();
//-->
</script>
