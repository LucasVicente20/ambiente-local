<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabUsuarioComissaoManter.php
# Autor:    Rossana Lira
# Data:     03/05/03
# Objetivo: Programa de Manutenção de Usuário/Comisssão
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabUsuarioComissaoSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao          = $_POST['Botao'];
		$Critica        = $_POST['Critica'];
		$UsuarioCodigo  = $_POST['UsuarioCodigo'];
		$GrupoCodigo    = $_POST['GrupoCodigo'];
		$ComissaoCodigo = $_POST['ComissaoCodigo'];
}else{
		$UsuarioCodigo  = $_GET['UsuarioCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabUsuarioComissaoManter.php";

# Redireciona para a página de excluir #
if( $Botao == "Voltar" ){
	  header("location: TabUsuarioComissaoSelecionar.php");
	  exit();
}else{
		# Critica dos Campos #
		if( $Critica == 1 ) {
				$Mens = 0;

		    # Deleta as comissões cadastradas para o usuário #
				$db     = Conexao();
				$db->query("BEGIN TRANSACTION");
		    $sql    = "DELETE FROM SFPC.TBUSUARIOCOMIS WHERE CUSUPOCODI = $UsuarioCodigo";
				$result = $db->query($sql);
				if( PEAR::isError($result) ) {
						$db->query("ROLLBACK");
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$Seleciona = "S";

						# Verifica se foram selecionadas as comissões #
						for( $P = 0; $P < count($ComissaoCodigo); $P++ ){
								if ( $ComissaoCodigo[$P] == "" ) {
									$Seleciona = "N";
								}
						}
						if( $Seleciona == "S" ){
								for( $P = 0; $P < count($ComissaoCodigo); $P++ ) {
										$ComissoesSelecionadas[$P] = $ComissaoCodigo[$P];
								}
								for( $P = 0; $P < count($ComissoesSelecionadas); $P++ ) {
										$ComissaoCod = $ComissoesSelecionadas[$P];
										$Data   = date("Y-m-d H:i:s");
										$sql    = "INSERT INTO SFPC.TBUSUARIOCOMIS ( ";
										$sql   .= "CGREMPCODI, CUSUPOCODI, CCOMLICODI, TUSUCOULAT ";
										$sql   .= " ) VALUES ( ";
										$sql   .= "$GrupoCodigo, $UsuarioCodigo, $ComissaoCod, '$Data')";
										$result = $db->query($sql);
										if( PEAR::isError($result) ){
								        $db->query("ROLLBACK");
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}
								}
						}
		        $db->query("COMMIT");
		        $db->query("END TRANSACTION");
		        $db->disconnect();

		        # Envia mensagem para página selecionar #
		        $Mensagem = urlencode("A Manutenção de Usuário/Comissão foi Executada com Sucesso");
		        $Url = "TabUsuarioComissaoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1&Critica=0";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		        header("location: ".$Url);
		        exit();
		    }
		}
}

if( $Critica == 0 ){
		# Carrega os dados do usuário selecionado #
		$db     = Conexao();
		$sql    = "SELECT EUSUPOLOGI, EUSUPORESP, CGREMPCODI ";
		$sql   .= "  FROM SFPC.TBUSUARIOPORTAL WHERE CUSUPOCODI = $UsuarioCodigo";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$Login       = $Linha[0];
						$Nome        = $Linha[1];
						$GrupoCodigo = $Linha[2];
				}
		}

		# Carrega os dados do usuário/comissão #
		$sql    = "SELECT CCOMLICODI FROM SFPC.TBUSUARIOCOMIS WHERE CUSUPOCODI = $UsuarioCodigo";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$UsuarioComissaoCodigo[] .= $Linha[0];
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
<form action="TabUsuarioComissaoManter.php" method="post" name="Usuario">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Usuário > Usuário/Comissão
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
	        	MANTER - USUÁRIO/COMISSÃO
          </td>
        </tr>
        <tr>
          <td class="textonormal" >
             <p align="justify">
             Para atualizar o Usuário/Comissão, selecione uma ou mais Comissões.
             Use (CTRL) +  clique no botão esquerdo do mouse para selecionar mais de uma Comissão de Licitação.
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" summary="">
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Login</td>
               	<td class="textonormal">
               		<?php echo $Login?>
                	<input type="hidden" name="Critica" value="1">
                	<input type="hidden" name="UsuarioCodigo" value="<?php echo $UsuarioCodigo; ?>">
                	<input type="hidden" name="GrupoCodigo" value="<?php echo $GrupoCodigo; ?>">
                </td>
              </tr>
							<tr>
    	      		<td class="textonormal" bgcolor="#DCEDF7" height="20">Nome do Usuário</td>
      	    		<td class="textonormal"><?php echo $Nome; ?></td>
        	  	</tr>
             	<tr>
        	   		<td class="textonormal" bgcolor="#DCEDF7" valign="top">Comissões de Licitação </td>
        	   		<td class="textonormal">
									<select name="ComissaoCodigo[]" multiple size="8" class="textonormal">
										<option value="">Nenhuma Comissão</option>
										<?php
										$db     = Conexao();
										$sql    = "SELECT CCOMLICODI, ECOMLIDESC FROM SFPC.TBCOMISSAOLICITACAO WHERE CGREMPCODI = $GrupoCodigo ORDER BY ECOMLIDESC";
										$result = $db->query($sql);
										if( PEAR::isError($result) ) {
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
		                		while( $Linha = $result->fetchRow() ){
														if( FindArray($Linha[0],$UsuarioComissaoCodigo) ){
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
            </table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right">
          	<input type="button" value="Manter" class="botao" onclick="javascript:enviar('Manter');">
          	<input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
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
