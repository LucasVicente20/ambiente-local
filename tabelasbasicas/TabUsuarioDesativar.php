<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabUsuarioDesativar.php
# Autor:    Carlos Abreu
# Data:     13/06/2007
# Objetivo: Programa de Desativação de Usuário
# Alterado: Carlos Abreu
# Data:     26/06/2007 - Alterado para Grupo do Usuario Desativado Ficar INTERNET
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabUsuarioAlterar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']  == "POST"){
		$Botao         = $_POST['Botao'];
		$Critica       = $_POST['Critica'];
		$UsuarioCodigo = $_POST['UsuarioCodigo'];
}else{
		$UsuarioCodigo = $_GET['UsuarioCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabUsuarioDesativar.php";

# Critica dos Campos #
if( $Botao == "Voltar" ){
		$Url = "TabUsuarioAlterar.php?UsuarioCodigo=$UsuarioCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit();
}else{
		if( $Critica == 1 ){
				$Mens     = 0;

				# Verifica se o usuário está excluindo a si próprio #
				if( $UsuarioCodigo == $_SESSION['_cusupocodi_'] ){
						$Mens     = 1;
						$Tipo     = 2;
						$Mensagem = urlencode("Desativação Cancelada!<br>O Usuário Está Logado Atualmente");
						$Url = "TabUsuarioAlterar.php?UsuarioCodigo=$UsuarioCodigo&Mens=$Mens&Tipo=$Tipo&Mensagem=$Mensagem";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						header("location: ".$Url);
						exit();
				}else{
						$db     = Conexao();
						# Exclui Usuario/Perfil #
						$db->query("BEGIN TRANSACTION");
						$sql    = "UPDATE SFPC.TBUSUARIOPERFIL SET CPERFICODI = 0 WHERE CUSUPOCODI = $UsuarioCodigo"; // PERFIL INTERNET
						$result = $db->query($sql);
						if( PEAR::isError($result) ){
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								# Exclui relacionamento de Usuario com as Comissoes de Licitacao #
								$sql = "DELETE FROM SFPC.TBUSUARIOCOMIS WHERE CUSUPOCODI = $UsuarioCodigo ";
								$result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
										$db->query("END TRANSACTION");
										$db->disconnect();
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										# Exclui Relacionamento de Usuario com Centros de Custo #
										$sql = "DELETE FROM SFPC.TBUSUARIOCENTROCUSTO WHERE CUSUPOCODI = $UsuarioCodigo ";
										$result = $db->query($sql);
										if( PEAR::isError($result) ){
												$db->query("ROLLBACK");
												$db->query("END TRANSACTION");
												$db->disconnect();
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$db->query("COMMIT");
												$db->query("END TRANSACTION");
												$db->disconnect();
												# Envia mensagem para página selecionar #
												$Mensagem = urlencode("Usuário Desativado com Sucesso");
												$Url = "TabUsuarioSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
												if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
												header("location: ".$Url);
												exit;
										}
								}
						}
				}
		}
}

if( $Critica == 0 ){
		# Carrega os dados do usuário selecionado #
		$db     = Conexao();
		$sql    = "SELECT EUSUPOLOGI, EUSUPORESP, EUSUPOMAIL, AUSUPOFONE, CGREMPCODI ";
		$sql   .= "  FROM SFPC.TBUSUARIOPORTAL WHERE CUSUPOCODI = $UsuarioCodigo";
		$result = $db->query($sql);
		if (PEAR::isError($result)) {
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$Login       = $Linha[0];
						$Nome        = $Linha[1];
						$Email       = $Linha[2];
						$Fone        = $Linha[3];
						$GrupoCodigo = $Linha[4];
				}
		}

		# Carrega o perfil do usuário selecionado #
		$sql    = "SELECT CPERFICODI FROM SFPC.TBUSUARIOPERFIL WHERE CUSUPOCODI = $UsuarioCodigo";
		$result = $db->query($sql);
		if (PEAR::isError($result)) {
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$PerfilCodigo = $Linha[0];
				}
		}
		$db->disconnect();
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
	document.Usuario.Botao.value=valor;
	document.Usuario.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabUsuarioDesativar.php" method="post" name="Usuario">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Usuário > Desativar
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
		<td class="textonormal">
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
				<tr>
					<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
						DESATIVAR - USUÁRIO
					</td>
				</tr>
				<tr>
					<td class="textonormal">
						<p align="justify">
							Para confirmar a desativação do Usuário clique no botão "Desativar", caso contrário clique no botão "Voltar".
						</p>
					</td>
				</tr>
				<tr>
					<td>
						<table border="0" summary="">
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" height="20">Login </td>
								<td class="textonormal">
									<?php echo $Login; ?>
									<input type="hidden" name="Critica" value="1">
									<input type="hidden" name="UsuarioCodigo" value="<?php echo $UsuarioCodigo; ?>">
								</td>
							</tr>
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" height="20">Nome do Usuário </td>
								<td class="textonormal"><?php echo $Nome; ?></td>
							</tr>
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" height="20">E-mail </td>
									<td class="textonormal"><?php echo $Email; ?></td>
							</tr>
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" height="20">Telefone </td>
								<td class="textonormal"><?php echo $Fone; ?></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td class="textonormal" align="right">
						<input type="submit" value="Desativar" class="botao" onclick="javascript:enviar('Desativar')">
						<input type="button" value="Voltar"  class="botao" onclick="javascript:enviar('Voltar')">
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
