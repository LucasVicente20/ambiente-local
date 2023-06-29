<?php
#--------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabAtualizaPerfilInternet.php
# Autor:    Lucas Baracho
# Data:     18/09/2018
# Objetivo: Tarefa Redmine 203480
#--------------------------------------------------------------------------------------

/* -------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     15/03/2019
 * Objetivo: Tarefa Redmine     212698
 * -------------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Critica = $_POST['Critica'];
	$Botao   = $_POST['Botao'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabAtualizaPerfilInternet.php";

# Constroi a lista com dados do usuário #
$db  = Conexao();

$sql = "SELECT	UP.CUSUPOCODI, UP.EUSUPOLOGI, UP.EUSUPORESP, UP.EUSUPOMAIL, UP.AUSUPOFONE, PE.EPERFIDESC, UPE.TUSUPEULAT
		FROM	SFPC.TBUSUARIOPORTAL UP
				LEFT JOIN SFPC.TBUSUARIOPERFIL UPE ON UP.CUSUPOCODI = UPE.CUSUPOCODI
				LEFT JOIN SFPC.TBPERFIL PE ON UPE.CPERFICODI = PE.CPERFICODI
		WHERE	UPE.CPERFICODI <> 0
		ORDER BY UP.EUSUPORESP ASC ";

$result = $db->query($sql);

if (PEAR::isError($result)) {
	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}

if ($Botao == 'Atualizar') {
    $usuarios = array();
    $emails = array();

    // Selecionar emails dos admins geral
    $sqlSelecAdm = "SELECT USPO.eusupomail FROM SFPC.TBUSUARIOPORTAL USPO LEFT JOIN SFPC.TBUSUARIOPERFIL USPE ON USPO.CUSUPOCODI = USPE.CUSUPOCODI WHERE USPE.CPERFICODI= 2";
    $resSelectAdm = $db->query($sqlSelecAdm);
    while ($resSelectAdm->fetchInto($email, DB_FETCHMODE_OBJECT)) {
        $emails[] = $email->eusupomail;
    }

    // Selecionar os usuários
    $sqlSelect = "SELECT USPO.CUSUPOCODI FROM SFPC.TBUSUARIOPORTAL USPO LEFT JOIN SFPC.TBUSUARIOPERFIL USPE ON USPO.CUSUPOCODI = USPE.CUSUPOCODI WHERE	USPE.CPERFICODI <> 2 AND USPE.CPERFICODI <> 0 ";
    $resSelect = $db->query($sqlSelect);
    while ($resSelect->fetchInto($usuario, DB_FETCHMODE_OBJECT)) {
        $usuarios[] = $usuario->cusupocodi;
    }

    $db->query("BEGIN TRANSACTION");

    // Atualizar os usuários
	$sqlupdt = "UPDATE	SFPC.TBUSUARIOPERFIL SET CPERFICODI = 0 WHERE	CPERFICODI <> 2 AND CPERFICODI <> 0 ";
    $sqlUpdtSenha = "UPDATE	SFPC.TBUSUARIOPORTAL set EUSUPOSEN2 = 'P$2Cvq7rvXUZo' WHERE	CUSUPOCODI in (".implode(',',$usuarios).")";

    $resupdt = $db->query($sqlupdt);
    $resupdtS = $db->query($sqlUpdtSenha);

    if (PEAR::isError($resupdt) || PEAR::isError($resupdtS)) {
        $db->query("ROLLBACK");
		ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sqlupdt");
	} else {
        $db->query("COMMIT");
        $db->query("END TRANSACTION");

        // Enviar e-mail
        $msgEmail = "Foram atualizados os perfis das contas de todos os usuários do Portal, exceto os que estão com perfil 'ADMINISTRADOR GERAL'.\n As contas estão com o perfil 'INTERNET' e a senha atual para as contas é: 123mudar";
        foreach ($emails as $email) {
            EnviaEmail(
                $email,
                "Atualização geral das contas dos usuários no Portal de Compras",
                $msgEmail,
                "from: portalcompras@recife.pe.gov.br"
            );
        }

        $Mens = 1;
        $Tipo = 1;
        $Mensagem = "Perfis alterados com sucesso";
	}
}
?>

<html>
	<?php	# Carrega o layout padrão
		layout(); ?>
	<script language="javascript" type="">
		<!--
		function enviar(valor){
			if(valor == 'Atualizar') {
            confirmar = confirm('Deseja alterar os perfis de todas as contas, exceto às dos administradores gerais?');
            if(!confirmar) {
                return false;
            }
        }
			document.Atualizar.Botao.value=valor;
			document.Atualizar.submit();
		}
		<?php MenuAcesso(); ?>
		//-->
	</script>
	<link rel="stylesheet" type="text/css" href="../estilo.css">
	<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
		<script language="JavaScript" src="../menu.js"></script>
		<script language="JavaScript">Init();</script>
		<form action="TabAtualizaPerfilInternet.php" method="post" name="Atualizar">
			<br><br><br><br><br>
			<table cellpadding="3" border="0">
				<!-- Caminho -->
				<tr>
					<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
					<td align="left" class="textonormal" colspan="2">
						<font class="titulo2">|</font>
						<a href="../index.php">
							<font color="#000000">Página Principal</font>
						</a> > Tabelas > Usuários > Atualização de Perfil
					</td>
				</tr>
				<!-- Fim do Caminho-->
				<!-- Erro -->
				<?php if ($Mens == 1) { ?>
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
						<table border="1" cellspacing="0" cellpadding="3" bgcolor="#ffffff" bordercolor="#75ADE6" class="textonormal" summary="">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="7">
									ATUALIZAÇÃO DO PERFIL DE USUÁRIO - ROTINA DE SEGURANÇA
								</td>
							</tr>
							<tr>
								<td class="textonormal" colspan="7">
									<p align="justify">
										Na lista abaixo estão todas as contas ativas do Portal de Compras. São consideradas como ativas as contas que não possuem o perfil "Internet".</br>
										Ao clicar em "Atualizar" serão alterados os perfis de todas as contas, exceto às com o perfil "Administrador Geral", para "Internet".
									</p>
								</td >
							</tr>
							<tr>
			        			<td class="textonormal" align="right"  colspan="7">
	                				<input type="hidden" name="Critica" value="1">
	                				<input type="button" value="Atualizar" class="botao" onclick="javascript:enviar('Atualizar');">
									<input type="hidden" name="Botao" value="">
			        			</td>
			    			</tr>
			    			<tr bgcolor="#DCEDF7">
								<td class="titulo3" align="center" widht="100px">Cód.</td>
								<td class="titulo3" align="center" widht="100px">Login</td>
								<td class="titulo3" align="center" widht="100px">Usuário</td>
								<td class="titulo3" align="center" widht="100px">E-Mail</td>
								<td class="titulo3" align="center" widht="100px">Telefone</td>
								<td class="titulo3" align="center" widht="100px">Perfil</td>
								<td class="titulo3" align="center" widht="100px">Data/hora</br> da última atualização</td>
							</tr>
							<tr>
								<?php while ($cols = $result->fetchRow()) {
										$codUsuario = $cols[0];
										$login      = $cols[1];
										$usuario    = $cols[2];
										$email      = $cols[3];
										$telefone   = $cols[4];
										$perfil     = $cols[5];
										$data       = ($cols[6] != "") ? substr($cols[6], 8, 2).'/'.substr($cols[6], 5, 2).'/'.substr($cols[6], 0, 4).' '.substr($cols[6], 11, 9) : ' - ';
								?>
								<tr>
									<td class="textonormal" align="center" widht="100px"><?php echo $codUsuario; ?></td>
									<td class="textonormal" align="center" widht="100px"><?php echo $login; ?></td>
									<td class="textonormal" align="center" widht="100px"><?php echo $usuario; ?></td>
									<td class="textonormal" align="center" widht="100px"><?php echo $email; ?></td>
									<td class="textonormal" align="center" widht="100px"><?php echo $telefone; ?></td>
									<td class="textonormal" align="center" widht="100px"><?php echo $perfil; ?></td>
									<td class="textonormal" align="center" widht="100px"><?php echo $data; ?></td>
								</tr>
								<?php } ?>
							</tr>
						</table>
					</td>
				</tr>
				<!-- Fim do Corpo -->
			</table>
		</form>
	</body>
</html>