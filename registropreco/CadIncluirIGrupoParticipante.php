<?php
# -----------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     01/11/2018
# Objetivo: Tarefa Redmine 73662
# -----------------------------------------------------------------------------

// 220038--

header("Content-Type: text/html; charset=UTF-8",true);

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/CadItemDetalhe.php' );

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$ProgramaOrigem            = $_POST['ProgramaOrigem'];
		$Botao                     = $_POST['Botao'];
		$Almoxarifado              = $_POST['Almoxarifado'];
		$TipoPesquisa              = $_POST['TipoPesquisa'];
		$TipoMaterial              = $_POST['TipoMaterial'];
		$TipoGrupo                 = $_POST['TipoGrupo'];
		$Grupo                     = $_POST['Grupo'];
		$Classe                    = $_POST['Classe'];
		$Subclasse                 = $_POST['Subclasse'];
		$PesqApenas                = $_POST['PesqApenas']; // E - Para disponibilizar apenas Itens em Estoque, C - Apenas para Cadastro de Materiais, Null - Para os dois
		$Zerados                   = $_POST['Zerados'];    // N - Apenas Itens em Estoque não zerados, Null - Todos os Itens
		$sqlpost                   = str_replace('\\','',$_POST['sqlgeral']); // Criado para resolver demora para cadastrar itens do cadastro de materiais. No "Insere" era executado um select que retornava todos os materiais da DLC, com esta variável, é executado na inclusão o mesmo select da pesquisa
}else{
		$ProgramaOrigem            = $_GET['ProgramaOrigem'];
		$Almoxarifado              = $_GET['Almoxarifado'];
		$Grupo                     = $_GET['Grupo'];
		$Classe                    = $_GET['Classe'];
		$Subclasse                 = $_GET['Subclasse'];  // Null - Considerar para Serviço
		$PesqApenas                = $_GET['PesqApenas']; // E - Para disponibilizar apenas Itens em Estoque, C - Apenas para Cadastro de Materiais, Null - Para os dois
		$Zerados                   = $_GET['Zerados'];    // N - Apenas Itens em Estoque não zerados, Null - Todos os Itens

		$Mens                = $_GET['Mens'];
		$Tipo                = $_GET['Tipo'];
		$Mensagem            = urldecode($_GET['Mensagem']);
}

$tituloLabel = "PARTICIPANTE";

if($ProgramaOrigem == "CadManterEspecialCarona"){
	$tituloLabel = "CARONA";
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Ano da Requisição Ano Atual #
$AnoRequisicao = date("Y");

# Verifica se o botão Incluir foi clicado #
if($Botao == "Incluir"){
	# Limpa o array de orgaos #
	//unset($_SESSION['orgaos']);
	if($_POST){
		$sessionName = ($_POST['ProgramaOrigem'] == 'CadManterEspecialCarona') ? 'orgaos_c' : 'orgaos';
		
		if(!isset($_POST['OrgaoLicitanteCodigo'])){
			
			$Mensagem = "Selecione um Órgão";

			$Url = "CadIncluirIGrupoParticipante.php?ProgramaOrigem=".$ProgramaOrigem."&PesqApenas=".$PesqApenas."&ata=&Tipo=0&Mens=1&Mensagem=".urlencode($Mensagem);
			if (!in_array($Url, $_SESSION['GetUrl'])) {
				$_SESSION['GetUrl'][] = $Url;
			}
			header("location: ".$Url);
			exit();
		}

		$inGrupos = implode(",", $_POST['OrgaoLicitanteCodigo']);		
		$db     = Conexao();
		$sql    = "SELECT CORGLICODI, EORGLIDESC FROM SFPC.TBORGAOLICITANTE WHERE CORGLICODI IN (". $inGrupos .") ORDER BY EORGLIDESC";
		$result = $db->query($sql);
		if (PEAR::isError($result)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
			while( $linha = $result->fetchRow() ){
				if(isset($_SESSION[$sessionName]) && !empty($_SESSION[$sessionName])){
					foreach ($_SESSION[$sessionName] as $key => $orgao) {
						if($key != $linha[0]){
							$_SESSION[$sessionName][$linha[0]] = $linha[1];
						}
					}
				}else{
					$_SESSION[$sessionName][$linha[0]] = $linha[1];
				}
			}
		}
		$db->disconnect();

		echo "<script>opener.document.$ProgramaOrigem.InicioPrograma.value=1</script>";
		echo "<script>opener.document.$ProgramaOrigem.submit()</script>";
		echo "<script>self.close()</script>";	
	}
}

# Critica dos Campos #
if($Botao == "Validar"){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( $SubclasseDescricaoDireta != "" and $OpcaoPesquisaSubClasse == 0 and ! SoNumeros($SubclasseDescricaoDireta) ){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens = 1;
				$Tipo = 2;
				$Mensagem .= "<a href=\"javascript:document.CadIncluirIGrupoParticipante.SubclasseDescricaoDireta.focus();\" class=\"titulo2\">Código reduzido da Subclasse</a>";
		}elseif($SubclasseDescricaoDireta != "" and ($OpcaoPesquisaSubClasse == 1 or $OpcaoPesquisaSubClasse == 2) and strlen($SubclasseDescricaoDireta)< 2){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens = 1;
				$Tipo = 2;
				$Mensagem .= "<a href=\"javascript:document.CadIncluirIGrupoParticipante.SubclasseDescricaoDireta.focus();\" class=\"titulo2\">Descrição com no mínimo 2 caracteres</a>";
		}
		if( $MaterialDescricaoDireta != "" and $OpcaoPesquisaMaterial == 0 and ! SoNumeros($MaterialDescricaoDireta) ){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens = 1;
				$Tipo = 2;
				$Mensagem .= "<a href=\"javascript:document.CadIncluirIGrupoParticipante.MaterialDescricaoDireta.focus();\" class=\"titulo2\">Código reduzido do Material</a>";
		}elseif($MaterialDescricaoDireta != "" and ($OpcaoPesquisaMaterial == 1 or $OpcaoPesquisaMaterial == 2) and strlen($MaterialDescricaoDireta)< 2){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens = 1;
				$Tipo = 2;
				$Mensagem .= "<a href=\"javascript:document.CadIncluirIGrupoParticipante.MaterialDescricaoDireta.focus();\" class=\"titulo2\">Descrição com no mínimo 2 caracteres</a>";
		}
}
?>
<html>
<head>
<title>Portal Compras - Adicionar participantes</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<script language="javascript" type="">
<!--
function checktodos(){
	document.CadIncluirIGrupoParticipante.Subclasse.value = '';
	document.CadIncluirIGrupoParticipante.submit();
}
function enviar(valor){
	document.CadIncluirIGrupoParticipante.Botao.value = valor;
	document.CadIncluirIGrupoParticipante.submit();
}
function validapesquisa(){
	if( ( document.CadIncluirIGrupoParticipante.MaterialDescricaoDireta.value != '' ) || ( document.CadIncluirIGrupoParticipante.SubclasseDescricaoDireta.value != '') ) {
		if(document.CadIncluirIGrupoParticipante.Grupo){
			document.CadIncluirIGrupoParticipante.Grupo.value = '';
		}
		if(document.CadIncluirIGrupoParticipante.Classe){
			document.CadIncluirIGrupoParticipante.Classe.value = '';
		}
		document.CadIncluirIGrupoParticipante.Botao.value = 'Validar';
	}
	if(document.CadIncluirIGrupoParticipante.Subclasse){
		if(document.CadIncluirIGrupoParticipante.SubclasseDescricaoFamilia.value != "") {
			document.CadIncluirIGrupoParticipante.Subclasse.value = '';
		}
	}
	document.CadIncluirIGrupoParticipante.submit();
}
function AbreJanela(url,largura,altura) {
	window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=45,top=150,width='+largura+',height='+altura);
}
function voltar(){
	self.close();
}
function remeter(){
	document.CadIncluirIGrupoParticipante.submit();
}
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">

</head>
<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
<form action="CadIncluirIGrupoParticipante.php" method="post" name="CadIncluirIGrupoParticipante">
	<table cellpadding="0" border="0" summary="">
		<!-- Erro -->
		<tr>
			<td align="left" colspan="4">
				<?php if( $Mens != 0 ){ ExibeMens($Mensagem,$Tipo,1);	}?>
			</td>
		</tr>
		<!-- Fim do Erro -->

		<!-- Corpo -->
		<tr>
			
				
			<td class="textonormal">
				<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">								
					<input type="hidden" name="ProgramaOrigem" value="<?php echo $ProgramaOrigem; ?>">
					
					<tr>
						<td align="center" bgcolor="#DCEDF7" class="titulo3" colspan="4">ADICIONAR <?php echo $tituloLabel ?> - ATA INTERNA</td>
					</tr>
					<tr>
						<td class="textonormal" bgcolor="#DCEDF7">Órgãos</td>
						<td class="normal">
										<select name="OrgaoLicitanteCodigo[]" multiple size="8" class="textonormal">
											<?php
											$db     = Conexao();
											$sql    = "SELECT CORGLICODI, EORGLIDESC FROM SFPC.TBORGAOLICITANTE ORDER BY EORGLIDESC";
											$result = $db->query($sql);
											if (PEAR::isError($result)) {
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
											}else{
													while( $Linha = $result->fetchRow() ){
															if( FindArray($Linha[0],$OrgaoLicitanteCodigo) ){
																	echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
															}else{
																	echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
															}
													}
											}
											$db->disconnect();
											?>
										</select>
						</td>
					</tr>
					

					<tr>
						<td colspan="4" align="right">
							<input type="button" value="Incluir" class="botao" onclick="javascript:enviar('Incluir');">
							<input type="button" value="Voltar" class="botao" onclick="javascript:voltar();">
							<input type="hidden" name="Botao" value="">
							<input type="hidden" name="PesqApenas" value="<?php echo $PesqApenas; ?>">
							<input type="hidden" name="Zerados" value="<?php echo $Zerados; ?>">
							<input type="hidden" name="sqlgeral" value="<?php echo substr($sqlgeral,7); ?>">
						</td>
					</tr>
				</table>
			</td>
					
			
		</tr>
		<!-- Fim do Corpo -->
	</table>
</form>
<script language="javascript" type="">
	window.focus();
//-->
</script>
</body>
</html>
