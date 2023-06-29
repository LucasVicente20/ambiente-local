<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsDispensaInexigibilidadePesquisar.php
# Autor:    Álvaro Faria
# Data:     20/01/2006
# Alterado: Álvaro Faria
# Data:     24/08/2006 - Período sem data gerava erro para analista 
# Objetivo: Programa de Pesquisa de Dispensa e/ou Inexigibilidade
# OBS.:     Tabulação 2 espaços
# Alterado: Marcos Túlio
# Data:13/09/2011
# Objetivo: Correção do select da Unidade Orçamentária
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/oracle/licitacoes/RotExibeDispensaInexigibilidade.php' );

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao          = $_POST['Botao'];
		$Opcao          = $_POST['Opcao'];
		$ObjetoP        = $_POST['ObjetoP'];
		$OrgaoUnidadeP  = $_POST['OrgaoUnidadeP'];
		$DataIni        = $_POST['DataIni'];
		$DataFim        = $_POST['DataFim'];
}else{
		$Mens           = $_GET['Mens'];
		$Tipo           = $_GET['Tipo'];
		$Mensagem       = urldecode($_GET['Mensagem']);
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

$DataMes = DataMes();
if($DataIni == ""){ $DataIni = "20/02/2006";}
if($DataFim == ""){ $DataFim = $DataMes[1];}

if($Botao == "Limpar"){
		header("location: ConsDispensaInexigibilidadePesquisar.php" );
		exit;
}elseif($Botao == "Pesquisar"){
		# Critica dos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";
		$MensErro = ValidaPeriodo($DataIni,$DataFim,$Mens,"ConsDispensaInexigibilidadePesquisar");
		if($MensErro != ""){
				$Mensagem .= $MensErro; $Mens = 1; $Tipo = 2;
		}elseif(str_replace("-","",DataInvertida($DataIni)) < 20060220){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.ConsDispensaInexigibilidadePesquisar.DataIni.focus();\" class=\"titulo2\">Data superior a 20 de fevereiro de 2006</a>";
		}
		if($Mens == 0){
				$DataIniConv = DataInvertida($DataIni);          // Retorna aaaa-mm-dd
				$DataFimConv = DataInvertida($DataFim);          // Retorna aaaa-mm-dd
				$DataIniConv = str_replace("-","",$DataIniConv); // Retorna aaaammdd
				$DataFimConv = str_replace("-","",$DataFimConv); // Retorna aaaammdd
				$OrgaoUnidadeP = explode("-",$OrgaoUnidadeP);
				$Orgao   = $OrgaoUnidadeP[0];
				$Unidade = $OrgaoUnidadeP[1];
				$Url = "licitacoes/RotExibeDispensaInexigibilidade.php?Botao=Pesquisar&Opcao=$Opcao&ObjetoP=".urlencode($ObjetoP)."&Orgao=$Orgao&Unidade=$Unidade&DataIni=$DataIniConv&DataFim=$DataFimConv&ProgramaOrigem=ConsDispensaInexigibilidadePesquisar";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				Redireciona($Url);
		}
}
?>

<html>
<?
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
function enviar(valor){
	document.ConsDispensaInexigibilidadePesquisar.Botao.value=valor;
	document.ConsDispensaInexigibilidadePesquisar.submit();
}
<?php MenuAcesso(); ?>
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" bgcolor="#FFFFFF" text="#000000" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="ConsDispensaInexigibilidadePesquisar.php" method="post" name="ConsDispensaInexigibilidadePesquisar">
<br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif"></td>
		<td align="left" class="textonormal" colspan="2"><br>
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Dispensa/Inexigibilidade
		</td>
	</tr>
	<!-- Fim do Caminho-->
	<!-- Erro -->
	<?php if($Mens == 1){?>
	<tr>
		<td width="100"></td>
		<td align="left" colspan="2"><?php if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,1); } ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->
	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
			<table border="0" cellspacing="0" cellpadding="3" bgcolor="#FFFFFF">
				<tr>
					<td class="textonormal">
						<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
									DISPENSA/INEXIGIBILIDADE
								</td>
							</tr>
							<tr>
								<td class="textonormal" >
									<p align="justify">
										Para visualizar os dados de Dispensa/Inexigibilidade, selecione o(s) item(ns) de pesquisa e clique no botão "Pesquisar".
									</p>
								</td>
							<tr>
								<td>
									<table class="textonormal" border="0" align="left">
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7">Opção de Exibição</td>
											<td class="textonormal">
												<select name="Opcao" class="textonormal">
													<option value="">Todas</option>
													<option value="D">DISPENSA</option>
													<option value="I">INEXIGIBILIDADE</option>
												</select>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" width=100>Objeto</td>
											<td class="textonormal">
												<input type="text" name="ObjetoP" size="45" maxlength="60" value="<?php echo $ObjetoP;?>" class="textonormal">
												<input type="hidden" name="Critica" value="1" size="1">
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7">Unidade Orçamentária</td>
											<td class="textonormal" >
												<select name="OrgaoUnidadeP" class="textonormal">
													<option value="">Todas as Unidades Orçamentárias...</option>
													<?php
													$Ano  = date("Y");
													$db   = Conexao();
													$sql  = "SELECT DISTINCT CUNIDOORGA, CUNIDOCODI, EUNIDODESC ";
													$sql .= "  FROM SFPC.TBUNIDADEORCAMENTPORTAL ";
													/*$sql .= " WHERE TUNIDOEXER = 2005 ";*/
													$sql .= " WHERE TUNIDOEXER = $Ano ";
													$sql .= " ORDER BY EUNIDODESC";
													$result = $db->query($sql);
													if( PEAR::isError($result) ){
															ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}else{
															while( $Linha = $result->fetchRow() ){
																	if("$Linha[0]-$Linha[1]" == $OrgaoUnidadeP){
																			echo "<option value=\"$Linha[0]-$Linha[1]\" selected>".substr($Linha[2], 0, 59)."</option>\n";
																	}else{
																			echo "<option value=\"$Linha[0]-$Linha[1]\">".substr($Linha[2], 0, 59)."</option>\n";
																	}
															}
													}
													$db->disconnect();
													?>
												</select>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Publicação*</td>
											<td class="textonormal">
												<?php
												$URLIni = "../calendario.php?Formulario=ConsDispensaInexigibilidadePesquisar&Campo=DataIni";
												$URLFim = "../calendario.php?Formulario=ConsDispensaInexigibilidadePesquisar&Campo=DataFim";
												?>
												<input type="text" name="DataIni" size="10" maxlength="10" value="<?php echo $DataIni;?>" class="textonormal">
												<a href="javascript:janela('<?php echo $URLIni ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
												&nbsp;a&nbsp;
												<input type="text" name="DataFim" size="10" maxlength="10" value="<?php echo $DataFim;?>" class="textonormal">
												<a href="javascript:janela('<?php echo $URLFim ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td class="textonormal" align="right">
									<input type="button" name="Pesquisar" value="Pesquisar" class="botao" onclick="javascript:enviar('Pesquisar');">
									<input type="button" name="Limpar" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
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
