<?php
# -------------------------------------------------------------------------
# Portal da DGCO teste
# Programa: CadPregaoPresencialRenegociarPreco.php
# Autor:    Hélio Miranda
# Data:     29/07/2016
# Objetivo: Programa para renegociação de preço do Pregão Presencial
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
}else{
		$Critica       							= $_GET['Critica'];
		$Mensagem      							= urldecode($_GET['Mensagem']);
		$Mens          							= $_GET['Mens'];
		$Tipo          							= $_GET['Tipo'];
}

if($_SESSION['Botao'] == "RenegociarPreco")
{
	$_SESSION['Botao'] = null;
	$CodLoteSelecionado								= $_SESSION['CodLoteSelecionado'];
	$CodFornecedorVencedorLoteSelecionado			= $_SESSION['CodFornecedorVencedorLoteSelecionado'];
	$ValorRenegociadoLoteSelecionado				= $_POST['ValorRenegociadoLoteSelecionado'];
	$ValorLoteSelecionado							= $_SESSION['ValorLoteSelecionado'];
	$RenegociacaoValida 							= True;
	
	$ValorRenegociadoLoteSelecionado  = str_replace(",", ".", $ValorRenegociadoLoteSelecionado);
	$ValorLoteSelecionado  = str_replace(",", ".", $ValorLoteSelecionado);
	
	if(!is_numeric($ValorRenegociadoLoteSelecionado))
	{
		$ValorRenegociadoLoteSelecionado = 0.00;
	}
	
	if($ValorRenegociadoLoteSelecionado == "" or $ValorRenegociadoLoteSelecionado == null)
	{
		$ValorRenegociadoLoteSelecionado = 0.00;
	}	
	
	if($_SESSION['PregaoTipo'] == 'N')
	{
		if($ValorRenegociadoLoteSelecionado >= $ValorLoteSelecionado)
		{
			$RenegociacaoValida = False;
		}
	}
	else
	{
		if($ValorRenegociadoLoteSelecionado <= $ValorLoteSelecionado)
		{
			$RenegociacaoValida = False;
		}
	}
	
	if($ValorRenegociadoLoteSelecionado > 0 and $RenegociacaoValida == True)
	{
		$db     = Conexao();
				
		$sql = "UPDATE sfpc.tbpregaopresenciallote SET vpregtvalr = $ValorRenegociadoLoteSelecionado WHERE cpregtsequ = $CodLoteSelecionado";		
		$res = $db->query($sql);
		if (PEAR::isError($res)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}
		
		$Descricao = "FORNECEDOR VENCEDOR DO LOTE, COM O VALOR RENEGOCIADO. AGUARDANDO, APENAS, ANÁLISE DE DECUMENTAÇÃO PARA QUE SE TORNE VENCEDOR DEFINITIVO";
		
		$sql = "UPDATE sfpc.tbpregaopresencialclassificacao SET epregcmoti = '$Descricao' WHERE cpregtsequ = $CodLoteSelecionado AND cpregfsequ = $CodFornecedorVencedorLoteSelecionado";		
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
		$_SESSION['Mensagem'] .= "- Renegociação CONCLUÍDA com sucesso!";
		$_SESSION['ValorRenegociadoLoteSelecionado'] = $ValorRenegociadoLoteSelecionado;
		
	}
	else if ($ValorRenegociadoLoteSelecionado > 0 and $RenegociacaoValida == False)
	{	
		$_SESSION['Mens'] = 1;
		$_SESSION['Tipo'] = 2;
		
		if($_SESSION['PregaoTipo'] == 'N')
		{
			$_SESSION['Mensagem'] .= "- O Valor para a Renegociação, não pode ser igual nem superior ao Valor Vencedor do Lote selecionado!<br />";		
		}
		else
		{
			$_SESSION['Mensagem'] .= "- A Oferta para a Renegociação, não pode ser igual nem inferior a Oferta Vencedora do Lote selecionado!<br />";		
		}			
	}
	else
	{	
		$_SESSION['Mens'] = 1;
		$_SESSION['Tipo'] = 2;
		$_SESSION['Mensagem'] .= "- Valor INVÁLIDO para a Renegociação!<br />";		 
	}	
}

if($_SESSION['Botao'] == "DesfazerRenegociacao")
{
	$_SESSION['Botao'] = null;
	$CodLoteSelecionado								= $_SESSION['CodLoteSelecionado'];
	$CodFornecedorVencedorLoteSelecionado			= $_SESSION['CodFornecedorVencedorLoteSelecionado'];
	
	if($CodLoteSelecionado > 0 and $CodFornecedorVencedorLoteSelecionado > 0)
	{
		$db     = Conexao();
				
		$sql = "UPDATE sfpc.tbpregaopresenciallote SET vpregtvalr = 0.00 WHERE cpregtsequ = $CodLoteSelecionado";		
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
		$_SESSION['Mensagem'] .= "- Renegociação DESFEITA com sucesso!";
		$_SESSION['ValorRenegociadoLoteSelecionado'] = "0,00";
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
	document.CadPregaoPresencialRenegociarPreco.Subclasse.value = '';
	document.CadPregaoPresencialRenegociarPreco.submit();
}
function enviar(valor){
	document.CadPregaoPresencialRenegociarPreco.Botao.value = valor;
	document.CadPregaoPresencialRenegociarPreco.submit();
}
function validapesquisa(){
	if( ( document.CadPregaoPresencialRenegociarPreco.MaterialDescricaoDireta.value != '' ) || ( document.CadPregaoPresencialRenegociarPreco.SubclasseDescricaoDireta.value != '') ) {
		if(document.CadPregaoPresencialRenegociarPreco.Grupo){
			document.CadPregaoPresencialRenegociarPreco.Grupo.value = '';
		}
		if(document.CadPregaoPresencialRenegociarPreco.Classe){
			document.CadPregaoPresencialRenegociarPreco.Classe.value = '';
		}
		document.CadPregaoPresencialRenegociarPreco.Botao.value = 'Validar';
	}
	if(document.CadPregaoPresencialRenegociarPreco.Subclasse){
		if(document.CadPregaoPresencialRenegociarPreco.SubclasseDescricaoFamilia.value != "") {
			document.CadPregaoPresencialRenegociarPreco.Subclasse.value = '';
		}
	}
	document.CadPregaoPresencialRenegociarPreco.submit();
}
function AbreJanela(url,largura,altura) {
	window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=45,top=150,width='+largura+',height='+altura);
}
function voltar(){
	self.close();
}
function remeter(){
	document.CadPregaoPresencialRenegociarPreco.submit();
}

function ncaracteresO(valor){
	document.CadPregaoPresencialRenegociarPreco.NCaracteresO.value = '' +  (document.CadPregaoPresencialRenegociarPreco.TamanhoMaximoParagrafos.value - document.CadPregaoPresencialRenegociarPreco.MotivoFracasso.value.length);
	if( navigator.appName == 'Netscape' && valor ){ //Netscape Only
		document.CadPregaoPresencialRenegociarPreco.NCaracteresO.focus();
	}
}

//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">

</head>
<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
<form action="CadPregaoPresencialRenegociarPreco.php" method="post" name="CadPregaoPresencialRenegociarPreco">
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
	        	RENEGOCIAR PREÇO - FORNECEDOR VENCEDOR
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
             Para renegociar o preço do Fornecedor vencedor do Lote, informe um valor diferente do valor vencedor, 
			 porém dentro do contexto do tipo Pregão Presencial(Menor Preço ou Maior Preço) e clique no botão "Aplicar Renegociação".<br />
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
							<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Fornecedor: </td>
							<td align="left" class="textonormal" colspan="3" >
							  <label> <?php echo $_SESSION['FornecedorVencedorLoteSelecionado']; ?> </label>
							</td>							
					  </tr>
					  
					  <tr>
						<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;"><?=(($_SESSION['PregaoTipo'] == 'N') ? ("Preço Vencedor (R$): ") : ("Oferta Vencedora (%): "))?></td>
							<td align="left" class="textonormal" colspan="3" >
							  <label><?php echo $_SESSION['ValorLoteSelecionado']; ?></label>
							</td>
					  </tr>
					  
						<?
							if($_SESSION['CodLoteSelecionado'] > 0 and $_SESSION['CodFornecedorVencedorLoteSelecionado'] > 0 and $_SESSION['CodSituacaoLoteSelecionado'] == 2)
							{
						?>					  
					  
					  <tr>
						<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;"><?=(($_SESSION['PregaoTipo'] == 'N') ? ("Preço Renegociado (R$): ") : ("Oferta Renegociada (%): "))?></td>
						<td align="left" class="textonormal" colspan="3" >	
							<input type="text" name="ValorRenegociadoLoteSelecionado" size="15" maxlength="15" 
							value="<?=(($_SESSION['ValorRenegociadoLoteSelecionado'] > 0) ? ($_SESSION['ValorRenegociadoLoteSelecionado']) : ("0,00"));?>" 
							class="textonormal"/> 
							</td>
					  </tr>					  
					  
					  <?
							}
					  ?>				  
					  
					</table>				
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right">
			<?
				if($_SESSION['CodLoteSelecionado'] > 0 and $_SESSION['CodFornecedorVencedorLoteSelecionado'] > 0 and $_SESSION['CodSituacaoLoteSelecionado'] == 2)
				{
					if($_SESSION['ValorRenegociadoLoteSelecionado'] == 0)
					{
			?>
				<input type="submit" value="Aplicar Renegociação" class="botao" onclick="javascript:enviar('RenegociarPreco');">				
			<?
					}
					if($_SESSION['ValorRenegociadoLoteSelecionado'] > 0)
					{
			?>
				<input type="submit" value="Desfazer Renegociação" class="botao" onclick="javascript:enviar('DesfazerRenegociacao');">	
			<?
					}
			?>
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
