<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: RotEnvioEditaisExibir.php
# Autor:    Roberta Costa
# Data:     20/05/03
# Objetivo: Programa de Envio de Editais por Interessados
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/licitacoes/RotEnvioEditaisInteressado.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Critica              = $_POST['Critica'];
		$Botao                = $_POST['Botao'];
		$Processo             = $_POST['Processo'];
		$AnoProcesso          = $_POST['AnoProcesso'];
		$GrupoCodigo          = $_POST['GrupoCodigo'];
		$ComissaoCodigo       = $_POST['ComissaoCodigo'];
		$OrgaoLicitanteCodigo = $_POST['OrgaoLicitanteCodigo'];
		$ModalidadeCodigo     = $_POST['ModalidadeCodigo'];
		$ListaCodigo          = $_POST['ListaCodigo'];
}else{
		$Processo             = $_GET['Processo'];
		$AnoProcesso          = $_GET['AnoProcesso'];
		$GrupoCodigo          = $_GET['GrupoCodigo'];
		$ComissaoCodigo       = $_GET['ComissaoCodigo'];
		$OrgaoLicitanteCodigo = $_GET['OrgaoLicitanteCodigo'];
		$ModalidadeCodigo     = $_GET['ModalidadeCodigo'];
		$ListaCodigo          = $_GET['ListaCodigo'];
		$Rows                 = $_GET['Rows'];
}

if( $Botao == "Voltar" ){
		$Url = "RotEnvioEditaisInteressado.php?Processo=$Processo&AnoProcesso=$AnoProcesso&GrupoCodigo=$GrupoCodigo&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&ModalidadeCodigo=$ModalidadeCodigo&ListaCodigo=$ListaCodigo&Rows=$Rows&Participantes=a";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	  header("location: ".$Url);
	  exit();
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.Rot.Botao.value=valor;
	document.Rot.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RotEnvioEditaisExibir.php" method="post" name="Rot">
<br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2"><br>
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Envio de Editais
		</td>
	</tr>
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
						<table border="1" width="100%" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" class="textonormal" summary="">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
									ENVIO DE EDITAIS - INTERESSADOS
								</td>
							</tr>
							<tr>
								<td class="textonormal">
									<p align="justify">
										Para copiar o(s) interessado(s), posicione o mouse antes da primeira letra do primeiro nome, clique e arraste até o último nome. Com os nomes selecionados use a tecla (CTRL) + C e onde desejar colá-los use a tecla (CTRL) + V.
									</p>
								</td>
							</tr>
							<tr>
								<td>
		    	      	<table class="textonormal" border="0" align="left" summary="">
		    	      		<tr>
					           	<td class="textonormal" bgcolor="#DCEDF7">Interessados</td>
											<td class="textonormal">
		                  	<?php
												# Mostra os participantes da licitação #
												$db     = Conexao();
		                  	$sql    = "SELECT ELISOLNOME FROM SFPC.TBLISTASOLICITAN";
												$sql   .= " WHERE CLICPOPROC = $Processo ";
												$sql   .= " AND ALICPOANOP = $AnoProcesso ";
												$sql   .= " AND CGREMPCODI = $GrupoCodigo ";
												$sql   .= " AND CCOMLICODI = $ComissaoCodigo ";
												$sql   .= " AND CORGLICODI = $OrgaoLicitanteCodigo ";
												$sql   .= " AND FLISOLPART = 'S'" ;
												$sql   .= " ORDER BY ELISOLNOME";
												$result = $db->query($sql);
												if( PEAR::isError($result) ){
												    $CodErroEmail  = $result->getCode();
														$DescErroEmail = $result->getMessage();
											  		ExibeErroBD("RotEnvioEditaisExibir.php\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
												}
												$email = "";
												while( $Linha = $result->fetchRow() ){
														echo "$Linha[0]<br>\n" ;
												}
												$db->disconnect();
												?>
				              </td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
		   	        <td class="textonormal" align="right">
									<input type="hidden" name="Processo" value="<?php echo $Processo; ?>">
							    <input type="hidden" name="AnoProcesso" value="<?php echo $AnoProcesso; ?>">
							    <input type="hidden" name="GrupoCodigo" value="<?php echo $GrupoCodigo; ?>">
							    <input type="hidden" name="ComissaoCodigo" value="<?php echo $ComissaoCodigo; ?>">
							    <input type="hidden" name="OrgaoLicitanteCodigo" value="<?php echo $OrgaoLicitanteCodigo; ?>">
							    <input type="hidden" name="ModalidadeCodigo" value="<?php echo $ModalidadeCodigo; ?>">
								  <input type="hidden" name="ListaCodigo" value="<?php echo $ListaCodigo;?>">
		              <input type="hidden" name="Critica" value="1">
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
