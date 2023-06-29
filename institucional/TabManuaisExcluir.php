<?php
/**
 * Portal de Compras
 * Prefeitura do Recife
 * 
 * Programa: TabManuaisExcluir.php
 * Autor:	 Ariston
 * Data:	 05/10/2008
 * -----------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     08/01/2023
 * Objetivo: Tarefa Redimine 277360
 * -----------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Aumenta o tempo de espera do servidor web para término de execução da página #
set_time_limit(3000);

$ManCodSelecionado=null;

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Botao       = $_POST['Botao'];
	$ExcluirItem = $_POST['ExcluirItem'];
	$NoItens	 = $_POST['NoItens'];
} else {
	$Critica  = $_GET['Critica'];
	$Mensagem = $_GET['Mensagem'];
	$Mens     = $_GET['Mens'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;
$NomePrograma = "TabManuaisExcluir.php";

$Mens      = 0;
$Tipo      = 0;
$Mensagem .= "";

if ($Botao=="Excluir") {
	if (count($ExcluirItem) == 0) {
		$Mens     = 1;
		$Tipo     = 2;
		$Mensagem = "Selecione pelo menos um manual para ser excluído";
		$Botao="";
	}
}elseif ($Botao=="ConfirmarExcluir") {
	$db = Conexao();
	$db->query("BEGIN TRANSACTION");
	$sqlCodItens="";
	$mostraSeparador=false;

	for ($itr=0;$itr<count($ExcluirItem);$itr++) {
		if(!$mostraSeparador) {
			$mostraSeparador=true;
		} else {
			$sqlCodItens.=" AND ";
		}

		$sqlCodItens.=" CDOCMACODI = $ExcluirItem[$itr] ";
	}

	$sqlCodItensOr="";
	$mostraSeparador=false;

	for ($itr=0;$itr<count($ExcluirItem);$itr++) {
		if (!$mostraSeparador) {
			$mostraSeparador=true;
		} else {
			$sqlCodItensOr.=" OR ";
		}

		$sqlCodItensOr.=" CDOCMACODI = $ExcluirItem[$itr] ";
	}

	$sqlDeletarItens  = "
		SELECT
			CDOCMACODI, EDOCMAARQU, EDOCMAARQS,EDOCMATITU, EDOCMADESC,
			".$_SESSION['_cusupocodi_'].", TDOCMAULAT
		FROM
			SFPC.TBDOCUMENTOMANUALPORTAL
		WHERE
	".$sqlCodItens."
	";
	$resDelItens = $db->query($sqlDeletarItens);
	if( PEAR::isError($resDelItens) ){
		$db->query("ROLLBACK");
		$db->query("END");
		$db->disconnect();
		EmailErroSQL("Erro de SQL em ".$NomePrograma, __FILE__, __LINE__, "SQL falhou.",$sqlDeletarItens, $resDelItens);
		exit(0);
	}

	$sqlDelete="
		DELETE FROM SFPC.TBDOCUMENTOMANUALPORTAL WHERE
	".$sqlCodItensOr."
	";
	$resDelete = $db->query($sqlDelete);
	if( PEAR::isError($resDelete) ){
		$db->query("ROLLBACK");
		$db->query("END");
		$db->disconnect();
		EmailErroSQL("Erro de SQL em ".$NomePrograma, __FILE__, __LINE__, "SQL falhou.",$sqlDelete, $resDelete);
		exit(0);
	}

	$db->query("COMMIT");
	$db->query("END");
	$db->disconnect();
	while($linha = $resDelItens->fetchRow()){
		$nomeArqu=$GLOBALS["CAMINHO_UPLOADS"]."institucional/".$linha[2];
		unlink($nomeArqu);
	}
	$Mens     = 1;
	$Tipo     = 1;
	$Mensagem = "Documento(s) excluido(s) com sucesso";
	$Botao="";
}

$db      = Conexao();
$sql  = "
	SELECT
		CDOCMACODI, EDOCMAARQU, EDOCMAARQS ,EDOCMATITU, EDOCMADESC, ".$_SESSION['_cusupocodi_'].",  
	    TDOCMAULAT
	FROM
		SFPC.TBDOCUMENTOMANUALPORTAL
";
$mostraSeparador=false;
if(($Botao=="Excluir") and (count($ExcluirItem)>0) ){ //selecionar arquivo a ser excluído
	$sql  .= " WHERE ";
	for($itr=1;$itr<=$NoItens;$itr++){
		if(!is_null($ExcluirItem[$itr])){
			if(!$mostraSeparador){
				$mostraSeparador=true;
			}else{
				$sql  .= " OR ";
			}
			$sql  .= " CDOCMACODI = ".$ExcluirItem[$itr]." ";	
		}
	}
}
$sql  .= "
	ORDER BY
		EDOCMAARQU
";

$resMan = $db->query($sql);
if( PEAR::isError($resMan) ){
	EmailErroSQL("Erro de SQL em ".$NomePrograma, __FILE__, __LINE__, "SQL falhou.", $sql, $resMan);
	exit(0);
}

if($resMan->numRows()==0 and $Botao!=""){
	$Botao="";
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
	document.Manuais.Botao.value=valor;
	document.Manuais.submit();
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
<form action="TabManuaisExcluir.php" method="POST" name="Manuais">
<br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2"><br>
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Institucional > Manuais > Excluir
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
					<?php
					if($Botao==""){
					?>
						<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
									LISTA DE MANUAIS
									<input type="hidden" name="NoItens" value="<?=$resMan->numRows()?>">
								</td>
							</tr>
							<?php
							if($resMan->numRows()>0){
							?>
								<tr>
									<td class="textonormal" colspan="4">
										<p align="justify">
											Escolha o manual desejado para exclusão clicando no botão 'Excluir' ao lado do arquivo. 
										</p>
									</td>
								</tr>
								<tr>
									<td align="center" class="titulo3" bgcolor="#F7F7F7">
										&nbsp;
									</td>
									<td align="center" class="titulo3" bgcolor="#F7F7F7">
										ARQUIVO
									</td>
									<td align="center" class="titulo3" bgcolor="#F7F7F7">
										TÍTULO
									</td>
									<td align="center" class="titulo3" bgcolor="#F7F7F7">
										DATA
									</td>
								</tr>
								<?php
								$linha = 0;
								$itr = 0;
								while($linha = $resMan->fetchRow()){
									$itr++;
									
									$macod  =  $linha[0];
									$maarqu =  $linha[1];
									$maarqs =  $linha[2];
									$matit  =  $linha[3];
									$madesc =  $linha[4];
									$mausr  =  $linha[5];
									$madata =  $linha[6];
									
								?>
									<tr>
										<td valign="top" bgcolor="#F7F7F7" class="textonormal">
											 <input type="checkbox" name="ExcluirItem[<?=$itr?>]" value="<?=$macod?>" class="botao">
										</td>
										<td valign="top" bgcolor="#F7F7F7" class="textonormal">
											<?/*a href="./documentos/<?=$maarqs?>"*/?><?=$maarqu?>
										</td>
										<td valign="top" bgcolor="#F7F7F7" class="textonormal">
											<?=$matit?>
										</td>
										<td valign="top" bgcolor="#F7F7F7" class="textonormal">
											<?=$madata?>
										</td>
									</tr>
							<?php
								}
							?>
								<tr>
									<td valign="top" align="right" bgcolor="#F7F7F7" class="textonormal" colspan="4">
										 <input type="button" name="Excluir" value="Excluir" class="botao" onclick="javascript:enviar('Excluir');">										
									</td>
								</tr>
							<?php
							}else{
							?>
								<td valign="top" bgcolor="#F7F7F7" class="textonormal" colspan="4" width="500">
									 Nenhum manual encontrado.
								</td>
							<?php
							}
							?>
						</table>
					<?php
					
					# Confirmação de exclusão
					
					}else if($Botao=="Excluir"){
					?>
						<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
									CONFIRMAR EXCLUSÃO
								</td>
							</tr>
							<tr>
								<td class="textonormal" colspan="4">
									<p align="justify">
										Confirme o(s) manual(ais) a ser(em) excluído(s) 
									</p>
								</td>
							</tr>
							<tr>
								<td align="center" class="titulo3" bgcolor="#F7F7F7">
									ARQUIVO
								</td>
								<td align="center" class="titulo3" bgcolor="#F7F7F7">
									TÍTULO
								</td>
								<td align="center" class="titulo3" bgcolor="#F7F7F7">
									DESCRIÇÃO
								</td>
								<td align="center" class="titulo3" bgcolor="#F7F7F7">
									DATA
								</td>
							</tr>
						<?php
						while($linha = $resMan->fetchRow()){
								$macod  = $linha[0];
								$maarqu = $linha[1];
								$maarqs = $linha[2];
								$matit  = $linha[3];
								$madesc = $linha[4];
								$mausr  = $linha[5];
								$madata = $linha[6];
							?>
								<tr>
									<td valign="top" bgcolor="#F7F7F7" class="textonormal">
										<?=$maarqu?>
									</td>
									<td valign="top" bgcolor="#F7F7F7" class="textonormal">
										<?=$matit?>
									</td>
									<td valign="top" bgcolor="#F7F7F7" class="textonormal">
										<?=$madesc?>
									</td>
									<td valign="top" bgcolor="#F7F7F7" class="textonormal">
										<?=$madata?>
									</td>
								</tr>
						<?php
						}
						?>
							<tr >
								<td class="textonormal" align="right" colspan="4">
									<input type="button" value="Confirmar" class="botao" onclick="javascript:enviar('ConfirmarExcluir');">
									<input type="button" value="Cancelar" class="botao" onclick="javascript:enviar('');">
									<input type="hidden" name="NoItens" value="<?=$NoItens?>">
									
									<?php
										# guardar codigos dos documentos a serem excluidos
										$cnt=0;
										for($itr=1;$itr<=$NoItens;$itr++){
											if(!is_null($ExcluirItem[$itr])){
									?>
												<input type="hidden" name="ExcluirItem[<?=$cnt?>]" value="<?=$ExcluirItem[$itr]?>">
									<?php
												$cnt++;
											}
										}
									?>
								</td>
							</tr>
						</table>

					<?php
					}
					?>
					</td>
				</tr>
				<tr>
					<td>
						<input type="hidden" name="Botao" value="" />
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