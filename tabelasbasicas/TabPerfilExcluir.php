<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabPerfilExcluir.php
# Autor:    Rossana Lira
# Data:     04/04/03
# Objetivo: Programa de Exclusão da Perfil
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     24/07/2018
# Objetivo: Tarefa Redmine 79809
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabPerfilAlterar.php' );
AddMenuAcesso( '/tabelasbasicas/TabPerfilSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']  == "POST"){
		$Botao        = $_POST['Botao'];
		$Critica      = $_POST['Critica'];
		$PerfilCodigo = $_POST['PerfilCodigo'];
		$PerfCorp     = $_POST['Corporativo'];
}else{
		$PerfilCodigo = $_GET['PerfilCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabPerfilExcluir.php";

if( $Botao == "Voltar" ){
		$Url = "TabPerfilAlterar.php?PerfilCodigo=$PerfilCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit();
}else{
		# Critica dos Campos #
		if( $Critica == 1 ) {
				$Mens     = 0;
		    $Mensagem = "Informe: ";

			  # Verifica se o Perfil tem algum Usuário relacionado #
		    if ($PerfilCodigo == 1) {
		       	$Mensagem = urlencode("Exclusão Cancelada!<br>O Perfil Internet não pode ser excluído");
		       	$Url = "TabPerfilSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=2";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		        header("location: ".$Url);
		        exit();
		    }else{
				    # Verifica se o Perfil tem algum Usuário relacionado #
				    $db     = Conexao();
				    $sql    = "SELECT COUNT(*) FROM SFPC.TBUSUARIOPERFIL WHERE CPERFICODI = $PerfilCodigo";
				    $result = $db->query($sql);
						if (PEAR::isError($result)) {
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$Linha = $result->fetchRow();
						    $Qtd = $Linha[0];
						    if( $Qtd > 0 ) {
						    		$db->disconnect();

						        # Envia mensagem para página selecionar #
						        $Mensagem = urlencode("Exclusão Cancelada!<br>Perfil Relacionado com ($Qtd) Usuário(s)/Perfil(is)");
						        $Url = "TabPerfilSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=2";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						        header("location: ".$Url);
						        exit();
						    }else{
							     	# Verifica se o Perfil tem algum acesso relacionado #
								    $sql    = "SELECT COUNT(*) FROM SFPC.TBPERFILACESSO WHERE CPERFICODI = $PerfilCodigo";
								    $result = $db->query($sql);
								    if (PEAR::isError($result)) {
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$Linha = $result->fetchRow();
										    $Qtd = $Linha[0];
										    if( $Qtd > 0 ) {
										        if ($Mens == 1){ $Mensagem .= "<br>"; }else{ $Mensagem .= "Exclusão Cancelada!<br>"; }
														$db->disconnect();

										        # Envia mensagem para página selecionar #
										        $Mensagem .= urlencode("Perfil Relacionado com ($Qtd) Acesso(s)");
										        $Url = "TabPerfilSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=2";
														if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
										        header("location: ".$Url);
										        exit();
										    }
										}
								}
						}
						$db->disconnect();
			  }
				if( $Mens == 0 ){
		    	  # Exclui Perfil #
		    	  $db = Conexao();
		        $db->query("BEGIN TRANSACTION");
		        # Apaga todos os Acesso relacionados com o Perfil selecionado
		        $sql    = "DELETE FROM SFPC.TBPERFILACESSO WHERE CPERFICODI = $PerfilCodigo";
					  $result = $db->query($sql);
				    if( PEAR::isError($result) ){
				    		$db->query("ROLLBACK");
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								# Apaga o Perfil selecionado
				        $sql    = "DELETE FROM SFPC.TBPERFIL WHERE CPERFICODI = $PerfilCodigo";
							  $result = $db->query($sql);
						    if( PEAR::isError($result) ){
						    		$db->query("ROLLBACK");
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$db->query("COMMIT");
										$db->query("END TRANSACTION");
										$db->disconnect();
										# Envia mensagem para página selecionar #
										$Mensagem = urlencode("Perfil Excluído com Sucesso");
										$Url = "TabPerfilSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
										header("location: ".$Url);
										exit();
								}
						}
						$db->disconnect();
				}
		}
}
if( $Critica == 0){
		$db     = Conexao();
		$sql    = "SELECT EPERFIDESC, FPERFISITU, FPERFICORP FROM SFPC.TBPERFIL WHERE CPERFICODI = $PerfilCodigo";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$PerfilDescricao = $Linha[0];
						$Situacao        = $Linha[1];
						$PerfCorp        = $Linha[2];
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
	document.Perfil.Botao.value=valor;
	document.Perfil.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabPerfilExcluir.php" method="post" name="Perfil">
<br><br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Perfil > Manter
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
	        	EXCLUIR - PERFIL
          </td>
        </tr>
        <tr>
          <td class="textonormal" >
             <p align="justify">
               Para confirmar a exclusão do Perfil clique no botão "Excluir", caso contrário clique no botão "Voltar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Perfil </td>
               	<td class="textonormal">
               		<?php echo $PerfilDescricao;?>
                	<input type="hidden" name="Critica" value="1">
                	<input type="hidden" name="PerfilCodigo" value="<?php echo $PerfilCodigo; ?>">
                </td>
              </tr>
							<tr>
              	<td class="textonormal" bgcolor="#DCEDF7" height="20">Situação </td>
	              <td class="textonormal">
	                <?php if( $Situacao == "A" ){ echo "ATIVO"; }else{ echo "INATIVO"; } ?>
                </td>
	            </tr>
							<tr>
              	<td class="textonormal" bgcolor="#DCEDF7" height="20">Perfil corporativo</td>
	              <td class="textonormal">
	                <?php if( $PerfCorp == "S" ){ echo "SIM"; }else{ echo "NÃO"; } ?>
                </td>
	            </tr>
            </table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right">
	          <input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Excluir')">
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
