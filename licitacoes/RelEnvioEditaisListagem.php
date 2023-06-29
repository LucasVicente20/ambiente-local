<?php
# ----------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelEnvioEditaisListagem.php
# Autor:    Roberta Costa
# Data:     24/05/03
# Objetivo: Programa de Relatório de Envio de Editais Via Correio Eletrônico
# OBS.:     Tabulação 2 espaços
# ----------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 73662
# ----------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/licitacoes/RelEnvioEditaisImpressao.php' );
AddMenuAcesso( '/licitacoes/RelEnvioEditaisCorreio.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Critica           = $_POST['Critica'];
		$Botao             = $_POST['Botao'];
		$Processo          = $_POST['Processo'];
		$AnoProcesso       = $_POST['AnoProcesso'];
		$GrupoCodigo       = $_POST['GrupoCodigo'];
		$ComissaoCodigo    = $_POST['ComissaoCodigo'];
		$OrgaoCodigo       = $_POST['OrgaoCodigo'];
		$ModalidadeCodigo  = $_POST['ModalidadeCodigo'];
}else{
		$Processo          = $_GET['Processo'];
		$AnoProcesso       = $_GET['AnoProcesso'];
		$GrupoCodigo       = $_GET['GrupoCodigo'];
		$ComissaoCodigo    = $_GET['ComissaoCodigo'];
		$OrgaoCodigo       = $_GET['OrgaoCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "RelEnvioEditaisListagem.php";

# Verifica dados na tabela de Lista de Solicitantes #
$db     = Conexao();
$sql    = "SELECT CLICPOPROC FROM SFPC.TBLISTASOLICITAN ";
$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $AnoProcesso ";
$sql   .= "   AND CGREMPCODI = $GrupoCodigo AND CCOMLICODI = $ComissaoCodigo ";
$sql   .= "   AND CORGLICODI = $OrgaoCodigo AND FLISOLENVI = 'S' ";
$sql   .= " ORDER BY ELISOLNOME";
$result = $db->query($sql);
if( PEAR::isError($result) ){
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
		$Rows = $result->numRows();
}
if( $Rows == 0 ) {
		$Mensagem .= urlencode("Não foi enviado nenhum documento por e-mail deste processo");
		$Url = "RelEnvioEditaisCorreio.php?Mensagem=$Mensagem&Mens=1&Tipo=1&Critica=0";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit();
}
if( $Botao == "Voltar" ) {
    header("location: RelEnvioEditaisCorreio.php");
    exit();
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
	document.Relatorio.Botao.value=valor;
	document.Relatorio.submit();
}
function janela( pageToLoad, winName, width, height, center) {
	xposition=0;
	yposition=0;
	if ((parseInt(navigator.appVersion) >= 4 ) && (center)){
		xposition = (screen.width - width) / 2;
		yposition = (screen.height - height) / 2;
	}
	args = "width=" + width + ","
	+ "height=" + height + ","
	+ "location=0,"
	+ "menubar=0,"
	+ "resizable=0,"
	+ "scrollbars=0,"
	+ "status=0,"
	+ "titlebar=no,"
	+ "toolbar=0,"
	+ "hotkeys=0,"
	+ "z-lock=1," //Netscape Only
	+ "screenx=" + xposition + "," //Netscape Only
	+ "screeny=" + yposition + "," //Netscape Only
	+ "left=" + xposition + "," //Internet Explore Only
	+ "top=" + yposition; //Internet Explore Only
	window.open( pageToLoad,winName,args );
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RelEnvioEditaisListagem.php" method="post" name="Relatorio">
<br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2"><br>
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Relatórios > Envio de Editais
    </td>
  </tr>
	<td></td>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
	  <td width="100"></td>
	  <td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
      <table  border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
		    					RELATÓRIO DE ENVIO DE EDITAIS - GERAR RELATÓRIO
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para emitir o relatório clique no botão "Imprimir". Para retornar a tela anterior, clique no botão "Voltar".
	          	   	</p>
	          		</td>
		        	</tr>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left" class="caixa">
	      	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Comissão</td>
				              <td class="textonormal">
												<?
				              	$db     = Conexao();
												$sql    = "SELECT ECOMLIDESC FROM SFPC.TBCOMISSAOLICITACAO ";
												$sql   .= " WHERE CCOMLICODI = ".$ComissaoCodigo;
												$result = $db->query($sql);
												if( PEAR::isError($result) ){
												    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														while( $Linha = $result->fetchRow() ){
									              echo $Linha[0];
							              }
							          }
					              ?>
				              </td>
	            			</tr>
	      	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7" height="20">Processo</td>
   		                <td class="textonormal"><?php echo substr($Processo + 10000,1);?></td>
	            			</tr>
	      	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7" height="20">Ano</td>
   		                <td class="textonormal"><?php echo $AnoProcesso;?></td>
	            			</tr>
	      	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Participantes</td>
	        	      	  <td class="textonormal" bgcolor="#FFFFFF" colspan="3">
				                <?
												# Mostra os participantes #
			                  $sql    = "SELECT ELISOLNOME, CLISOLCNPJ, CLISOLCCPF ";
												$sql   .= "  FROM SFPC.TBLISTASOLICITAN ";
												$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $AnoProcesso ";
												$sql   .= "   AND CGREMPCODI = $GrupoCodigo AND CCOMLICODI = $ComissaoCodigo ";
												$sql   .= "   AND CORGLICODI = $OrgaoCodigo AND FLISOLENVI = 'S' ";
												$sql   .= " ORDER BY ELISOLNOME";
												$result = $db->query($sql);
												if( PEAR::isError($result) ){
												    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Rows = $result->numRows();
														if( $Rows == 0 ){
																$Valor = 1;
														}else{
																while( $Linha = $result->fetchRow() ){
																		echo $Linha[0]." - ";
																		if( $Linha[2] == "" ){
																				echo "CNPJ: ".$Linha[1]."<br>\n";
																		}else{
																				echo "CPF: ".$Linha[2]."<br>\n";
																		}
																}
														}
												}
											 	$db->disconnect();
   		                	?>
											</select>
   		                </td>
	            			</tr>
	                </table>
	  	      	  </td>
	  	      	</tr>
	  	      	<tr>
   	  	  			<td class="textonormal" align="right">
							 		<input type="hidden" name="Processo" value="<?echo $Processo;?>">
							    <input type="hidden" name="AnoProcesso" value="<?echo $AnoProcesso;?>">
							    <input type="hidden" name="GrupoCodigo" value="<?echo $GrupoCodigo;?>">
							    <input type="hidden" name="ComissaoCodigo" value="<?echo $ComissaoCodigo;?>">
							    <input type="hidden" name="OrgaoCodigo" value="<?echo $OrgaoCodigo;?>">
							    <input type="hidden" name="ModalidadeCodigo" value="<?echo $ModalidadeCodigo;?>">
							    <input type="hidden" name="ListaCodigo" value="<?echo $ListaCodigo;?>">
           				<input type="hidden" name="Critica" value="1">
									<?
									$url = "RelEnvioEditaisImpressao.php?Processo=$Processo&AnoProcesso=$AnoProcesso&GrupoCodigo=$GrupoCodigo&ComissaoCodigo=$ComissaoCodigo&OrgaoCodigo=$OrgaoCodigo&ModalidadeCodigo=$ModalidadeCodigo&ListaCodigo=$ListaCodigo";
									if (!in_array($url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $url; }
									?>
        	      	<input type="button" value="Imprimir" class="botao" onclick="javascript :janela('<?echo $url?>','PortalCompras',700,300,1)">
        	      	<input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
        	      	<input type="hidden" name="Botao" value="">
		          	</td>
		        	</tr>
    	  	  </table>
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
