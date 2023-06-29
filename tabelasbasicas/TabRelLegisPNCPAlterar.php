<?php

/*
Arquivo: TabRelLegisPNCPAlterar.php
Nome: Lucas Vicente
Data: 01/03/2023
Tarefa: 279688 

*/

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/tabelasbasicas/TabRelLegisPNCPSelecionarExcluir.php');
AddMenuAcesso('/tabelasbasicas/TabRelLegisPNCPSelecionar.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Botao        = $_POST['Botao'];
	$CodigoPNCP   = $_POST['CodigoPNCP'];
	$CodigoPNCP   = $_SESSION['CodigoPNCP'];
	$Lei   		  = $_POST['Lei'];
	$Artigo   	  = $_POST['Artigo'];
	$Inciso   	  = $_POST['Inciso'];
	
} else {
	$CodigoPNCP   = $_GET['CodigoPNCP'];
	$CodigoPNCP   = $_SESSION['CodigoPNCP'];
}

$db = Conexao();

$sql = "SELECT * FROM SFPC.tblegislacaocompraspncp WHERE clcpnccodi = " .$CodigoPNCP;
$result = $db->query($sql);

if (db::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
} else {
    $Linha = $result->fetchRow();
}
$CodigoPNCP    = $Linha[0];
$CodigoTipoLei = $Linha[1];
$Lei           = empty($_POST['Lei'])?$Linha[2]:$_POST['Lei'];
$Artigo        = empty($_POST['Artigo'])?$Linha[3]:$_POST['Artigo'];
$Inciso        = empty($_POST['Inciso'])?$Linha[4]:$_POST['Inciso'];

$sqlInciso = "SELECT NINCPANUME FROM SFPC.TBINCISOPARAGRAFOPORTAL WHERE CINCPAINCI = " .$Inciso;
$resultInsico = $db->query($sqlInciso);
$LinhaInciso = $resultInsico->fetchRow();
$IncisoConvertido = $LinhaInciso[0];
$db->disconnect();

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

$db = Conexao();

$CodigoUsuario = $_SESSION['_cusupocodi_'];

if ($Botao == "Excluir") {
	$_SESSION['CodigoPNCP']   = $CodigoPNCP;
	$_SESSION['Lei']   		  = $Lei ;
	$_SESSION['Artigo']   	  = $Artigo;
	$_SESSION['Inciso']   	  = $IncisoConvertido;
	$Url = "TabRelLegisPNCPExcluir.php";
	// if(!in_array($Url,$_SESSION['GetUrl'])){
	// 	$_SESSION['GetUrl'][] = $Url;
	// }
	header("location: ".$Url);
	exit();

}elseif($Botao == "Voltar"){
	header("location: TabRelLegisPNCPSelecionar.php");
	exit();
}elseif($Botao == "Alterar"){
	$CodigoPNCPNovo = $_POST['CodigoPNCP'];
	$Mens     = 0;
    $Mensagem = "Informe: ";
	
    if ($CodigoPNCP == "") {
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<class=\"titulo2\">Código de Legislação de Compras</a>";
    }
    if ($Mens == 0) {
		if($CodigoPNCP != $CodigoPNCPNovo){
			$sqlValidaCod = "SELECT count(clcpnccodi) FROM SFPC.tblegislacaocompraspncp WHERE clcpnccodi = $CodigoPNCPNovo";
		
			$resultCod = $db->query($sqlValidaCod);
			$LinhaCod = $resultCod->fetchRow();
		
		if($LinhaCod[0]>0){
			
			$Mens = 1;
			$Tipo = 2;
			$Mensagem .= "<class=\"titulo2\">O código informado já esta sendo utilizado</a>";
    	
		}

		}else{
			$sql = "SELECT COUNT(clcpnccodi) FROM SFPC.tblegislacaocompraspncp WHERE cleiponume = $Lei 
				AND cartpoarti = $Artigo AND cincpainci = $Inciso ";
			$result = $db->query($sql);
			
			if (db::isError($result)) {
				ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
			
			}else{
				$Linha = $result->fetchRow();
				$Qtd = $Linha[0];
				
				if ($Qtd > 0) {
					$Mens = 1;
					$Tipo = 2;
					$Mensagem .= "<class=\"titulo2\">Os parametros informados já estão sendo utilizados</a>";
				} else {
					# Atualiza Código de Legislação de Compras #
					$Data = date("Y-m-d H:i:s");

					$db->query("BEGIN TRANSACTION");
						
					$sql  = "UPDATE SFPC.tblegislacaocompraspncp ";
					$sql .= "SET	clcpnccodi = '$CodigoPNCPNovo', cleiponume = '$Lei', cartpoarti = '$Artigo', cincpainci = '$Inciso', cusupocodi = '$CodigoUsuario', tlcpnculat = '$Data' ";
					$sql .= "WHERE 	clcpnccodi = ".$CodigoPNCP;
					$result = $db->query($sql);
					
					if (db::isError($result)) {
						$db->query("ROLLBACK");
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					} else {
						$db->query("COMMIT");
						$db->query("END TRANSACTION");
						$db->disconnect();

						# Envia mensagem para página selecionar #
						$Mensagem = urlencode("Código de Legislação de Compra alterado com sucesso");
						$Url = "TabRelLegisPNCPSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
						header("location: ".$Url);
						exit();
					}
				}
			}
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
	
	function enviar(valor) {
		document.TabRelLegisPNCPAlterar.Botao.value = valor;
		document.TabRelLegisPNCPAlterar.submit();
	}
	<?php MenuAcesso(); ?>
	//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="TabRelLegisPNCPAlterar.php" method="post" name="TabRelLegisPNCPAlterar">
		<br><br><br><br>
		<table cellpadding="3" border="0">
			<!-- Caminho -->
			<tr>
				<br>
				<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="2">
					<font class="titulo2">|</font>
					<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Planejamento > PNCP > Manter
				</td>
			</tr>
			<!-- Fim do Caminho-->
			<!-- Erro -->
			<?php
			if ($Mens == 1) {
				?>
  				<tr>
  					<td width="150"></td>
					<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
				</tr>
				<?php
			}
			?>
			<!-- Fim do Erro -->
			<!-- Corpo -->
			<tr>
				<td width="150"></td>
				<td class="textonormal">
					<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
        				<tr>
          					<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	           					MANTER - PNCP
          					</td>
        				</tr>
        				<tr>
          					<td class="textonormal" >
             					<p align="justify">
			 					Para atualizar um código de Legislação de Compras preencha os campos abaixo e clique no botão "Alterar".
			 					Para apagar clique no botão "Excluir".
             					</p>
          					</td>
        				</tr>
        				<tr>
          					<td>
            					<table>
              						<tr>
                						<td class="textonormal" bgcolor="#DCEDF7">Código de Legislação de Compras:</td>
               							<td class="textonormal">
               								<input type="text" class="textonormal" name="CodigoPNCP" id="CodigoPNCP" value="<?php echo empty($_GET['CodigoPNCP'])?$CodigoPNCP:$_GET['CodigoPNCP'];?>">
											
                						</td>
										<td class="textonormal" >Lei:</td>
										<td>
										<?php
											$db = Conexao();

											$sqlLei = 'SELECT CLEIPONUME FROM SFPC.TBLEIPORTAL';
											$resLei = executarTransacao($db, $sqlLei);
											?>
											<select class="textonormal" name="Lei" id="Lei" onChange="atualizar()">
											<option value="">Selecione uma Lei...</option>
											<?php 

											while ($LinhaLei = $resLei->fetchRow()) {
												$leiItem = $LinhaLei[0];
												
											?>
											<option value="<?php echo  $leiItem ?>" <?php if ($leiItem == $Lei) { echo 'selected'; } ?>><?php echo $leiItem; ?></option>
											<?php       
											}   
											
											?>
											</select>
											</td>
											<td class="textonormal">
												Artigo: 
                                                    <select class="textonormal" name="Artigo" id="Artigo" onChange="atualizar()">
                                                    	<option value="">Selecione um Artigo...</option>  
															<?php
																if(!is_null($Lei) and $Lei!=''){
																	$db = Conexao();
																	$sqlArtigo = 'SELECT CARTPOARTI FROM SFPC.TBARTIGOPORTAL WHERE CLEIPONUME = ' . $Lei . ' ';
																	var_dump($sqlArtigo);
																	$resArtigo = executarTransacao($db, $sqlArtigo);
																	?>
																	
																	<?php   
														
																	while ($LinhaArtigo = $resArtigo->fetchRow()) {
																		$ArtigoItem = $LinhaArtigo[0];
																	?>
																	<option value="<?php echo  $ArtigoItem ?>" <?php if ($ArtigoItem == $Artigo) { echo 'selected'; } ?>><?php echo $ArtigoItem; ?></option>
																	<?php       
																	}
																}
																?>
													</select>
											</td>
											<td class="textonormal">
												Inciso/Parágrafo:
												<select class="textonormal" name="Inciso" id="Inciso" onChange="atualizar()">
													<option value="">Selecione um Inciso/Parágrafo...</option>
														<?php
															if(!is_null($Lei) and $Lei!='' and !is_null($Artigo) and $Artigo!=''){
																$db = Conexao();
																$sqlInciso = 'SELECT CINCPAINCI, NINCPANUME FROM SFPC.TBINCISOPARAGRAFOPORTAL WHERE CLEIPONUME = '.$Lei.' AND CARTPOARTI = '.$Artigo.'';
																
																$resInciso = executarTransacao($db, $sqlInciso);
														?>
																
																<?php   
													
																while ($LinhaInciso = $resInciso->fetchRow()) {
																	$IncisoItem     = $LinhaInciso[0];
																	$IncisoNumero   = $LinhaInciso[1];
																?>
																	<option value="<?php echo  $IncisoItem ?>" <?php if ($IncisoItem == $Inciso) { echo 'selected'; } ?>><?php echo $IncisoNumero; ?></option>
																	<?php       
																}
															}
																	?>
												</select>
											</td>
									</tr>
            					</table>
          					</td>
        				</tr>
						<script>
							function atualizar(){
								$('form').submit();
							}                     
							</script>
        				<tr align="right">
          					<td>
          						<input type="button" value="Alterar" class="botao" onclick="javascript:enviar('Alterar');">
								<input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Excluir');">
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
