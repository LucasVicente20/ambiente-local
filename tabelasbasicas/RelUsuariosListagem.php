<?php
#--------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelUsuariosListagem.php
# Autor:    Roberta Costa
# Data:     28/08/03
# Objetivo: Programa que Exibe uma Lista com Nome e E-mail dos Usuários de Produção
# OBS.:     Tabulação 2 espaços
#--------------------------------------------------------------------------------------
# Alterado: Everton Lino
# Data:     15/04/2010 	- Inclusão do campo CPF na listagem.
#--------------------------------------------------------------------------------------
# Alterado: Lucas Baracho 
# Data:     27/07/2018
# Objetivo: Tarefa Redmine 199944
#--------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     02/08/2018
# Objetivo: Tarefa Redmine 144527
#--------------------------------------------------------------------------------------
# Alterado: João Madson
# Data:     20/01/2021
# Objetivo: Tarefa Redmine 242965
#--------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/RelUsuariosImpressao.php' );
//Manter por questão de segurança -> header("location: RelUsuariosImpressao.php?Opcao=$Opcao");

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Critica = $_POST['Critica'];
		$Botao   = $_POST['Botao'];
		$Opcao   = $_POST['Opcao'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "RelUsuariosListagem.php";

$GrupoDescricao = "";
$PerfilDescricao = "";

if( $Botao == "Imprimir" ){
  	if( $Opcao == "" ){
  			$Opcao = "Alfabetica";
  	}
}

# Constói a Lista com Nome e Email da tabela de usuários de produção #
$db  = Conexao();
$sql = "SELECT	DISTINCT UP.CUSUPOCODI, UP.EUSUPORESP, OL.EORGLIDESC, AP.EALMPODESC, UP.EUSUPOMAIL, UP.CGREMPCODI, P.CPERFICODI, UP.AUSUPOFONE, UP.AUSUPOCCPF, GE.EGREMPDESC, P.EPERFIDESC, UP.EUSUPOLOGI
		FROM	SFPC.TBUSUARIOPORTAL UP
				INNER JOIN SFPC.TBUSUARIOPERFIL UPE ON UP.CUSUPOCODI = UPE.CUSUPOCODI
				INNER JOIN SFPC.TBPERFIL P ON UPE.CPERFICODI = P.CPERFICODI
				LEFT JOIN SFPC.TBUSUARIOCENTROCUSTO UCS ON UP.CUSUPOCODI = UCS.CUSUPOCODI
				LEFT JOIN SFPC.TBCENTROCUSTOPORTAL CCP ON UCS.CCENPOSEQU = CCP.CCENPOSEQU
				LEFT JOIN SFPC.TBORGAOLICITANTE OL ON CCP.CORGLICODI = OL.CORGLICODI
				LEFT JOIN SFPC.TBALMOXARIFADOPORTAL AP ON UCS.CALMPOCODI = AP.CALMPOCODI
				INNER JOIN SFPC.TBGRUPOEMPRESA GE ON UP.CGREMPCODI = GE.CGREMPCODI
		WHERE	UP.CGREMPCODI <> 0 ";

if($Critica == 1) {
	if($_SESSION['_cgrempcodi_'] >= 0) {
		if($Botao == "Grupo") {
			$sql .= " ORDER BY GE.EGREMPDESC ASC, UP.EUSUPORESP ASC";
			$Opcao = "Grupo";
		} elseif ($Botao == "Alfabetica") {
			$sql .= " ORDER BY UP.EUSUPORESP ASC";
			$Opcao = "Alfabetica";
		} elseif ($Botao == "Perfil") {
			$sql .= " ORDER BY P.EPERFIDESC ASC, UP.EUSUPORESP";
			$Opcao = "Perfil";
		}
	}
} else {
	$sql  .= "ORDER BY UP.EUSUPORESP ASC";
	$Botao = "Inicial";
}

//print_r($sql);
//die;

$result = $db->query($sql);

if(PEAR::isError($result)) {
	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}

if($Opcao == "") { 
	$Opcao = "Alfabetica";
}

$Opcao = urlencode($Opcao);
$url   = "RelUsuariosImpressao.php?Opcao=$Opcao";

if (!in_array($url,$_SESSION['GetUrl'])) {
	$_SESSION['GetUrl'][] = $url;
}
?>

<html>
<?php
# Carrega o layout padrão
layout();
?>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.Lista.Botao.value=valor;
	document.Lista.submit();
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
<form action="RelUsuariosListagem.php" method="post" name="Lista">
<br><br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Usuários > Lista
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
			<table  border="1" cellspacing="0" cellpadding="3" bgcolor="#ffffff" bordercolor="#75ADE6" class="textonormal" summary="">
				<tr>
					<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="8">
						LISTA DE USUÁRIOS DO PORTAL DE COMPRAS
					</td>
				</tr>
				<tr>
					<td class="textonormal" colspan="8">
						<p align="justify">
							Para lista os usuários por grupo clique no botão "Grupo", para listar os usuários alfabeticamente clique no botão
							"Ordem Alfabética" ou ainda por Perfil de Usuário clique em "Perfil".Só serão exibidos os usuários ativos.<br><br>
							Para imprimir a lista dos usuários clique no botão "Imprimir".
							Os usuários com perfil "Internet" só serão exibidos nas listagens por Perfil e por Grupo.
						</p>
					</td >
				</tr>
				<tr>
			        <td class="textonormal" align="right"  colspan="8">
	                	<input type="hidden" name="Critica" value="1">
	                	<input type="hidden" name="Opcao" value="<?php echo $Opcao; ?>">
	                	<input type="button" value="Grupo" class="botao" onclick="javascript:enviar('Grupo');">
						<input type="button" value="Ordem Alfabética" class="botao" onclick="javascript:enviar('Alfabetica');">
						<input type="button" value="Perfil" class="botao" onclick="javascript:enviar('Perfil');">
	                	<input type="button" value="Imprimir" class="botao" onclick="javascript:janela('<?php echo $url?>','Portal',700,300,1)">
	      	      		<input type="hidden" name="Botao" value="">
			        </td>
			    </tr>
			    <tr bgcolor="#DCEDF7">
					<td class="titulo3" align="center" widht="100px">Cód.</td>
					<td class="titulo3" align="center" widht="100px">Nome</td>
					<td class="titulo3" align="center" widht="100px">Órgão</td>
					<td class="titulo3" align="center" widht="100px">Almoxarifado (Atendimento)</td>
					<td class="titulo3" align="center" widht="100px">E-mail</td>
					<td class="titulo3" align="center" widht="100px">Telefone</td>
					<td class="titulo3" align="center" widht="100px">CPF</td>
					<td class="titulo3" align="center" widht="100px">LOGIN</td>
				</tr>
				<tr>
					<?php	while($cols = $result->fetchRow()) {
							$codUsuario = $cols[0];
							$nome       = $cols[1];
							$orgaoDesc  = $cols[2];
							$almoxDesc  = $cols[3];
							$email      = $cols[4];
							$codGrupo   = $cols[5];
							$codPerfil  = $cols[6];
							$fone       = $cols[7];
							$cpf        = $cols[8];
							$login        = $cols[11];
																			
							if($Opcao == "Grupo") {
								$sqlGrupo = "SELECT EGREMPDESC FROM SFPC.TBGRUPOEMPRESA WHERE CGREMPCODI = $codGrupo ORDER BY EGREMPDESC";
													
								$res2 = $db->query($sqlGrupo);
													
								if(PEAR::isError($res2)) {
									ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlGrupo");
								} else {
									while($reg = $res2->fetchRow()) {
										if($grupoDesc != $reg[0]) {
											$grupoDesc = $reg[0]; ?>
																
											<tr bgcolor="#DCEDF7">
												<td class="titulo3" colspan="8">
													<?php echo $grupoDesc; ?>
												</td>
											</tr> <?php
										}
									}
								}
							} elseif($Opcao == "Perfil") {
								$sqlPerfil = "SELECT EPERFIDESC FROM SFPC.TBPERFIL WHERE CPERFICODI = $codPerfil ORDER BY EPERFIDESC";
													
								$res3 = $db->query($sqlPerfil);
													
								if(PEAR::isError($res3)) {
									ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlPerfil");
								} else {
									while ($reg = $res3->fetchRow()) {
										if($perfilDesc != $reg[0]) {
											$perfilDesc = $reg[0]; ?>
												
											<tr bgcolor="#DCEDF7">
												<td class="titulo3" colspan="8">
													<?php echo $perfilDesc; ?>
												</td>
											</tr> <?php
										}
									}
								}
							} ?>
							
							<tr>
								<td class="textonormal" align="center" widht="100px"><?php echo $codUsuario; ?></td>
								<td class="textonormal" align="center" widht="100px"><?php echo $nome; ?></td>
								<td class="textonormal" align="center" widht="100px"><?php echo $orgaoDesc; ?></td>
								<td class="textonormal" align="center" widht="100px"><?php echo $almoxDesc; ?></td>
								<td class="textonormal" align="center" widht="100px"><?php echo $email; ?></td>
								<td class="textonormal" align="center" widht="100px"><?php echo $fone; ?></td>
								<td class="textonormal" align="center" widht="100px"><?php echo $cpf; ?></td>
								<td class="textonormal" align="center" widht="100px"><?php echo $login; ?></td>
							</tr>
					<?php  } ?>
			</table>
		</td>
	</tr>
	<!-- Fim do Corpo -->
</table>
</form>
</body>
</html>
