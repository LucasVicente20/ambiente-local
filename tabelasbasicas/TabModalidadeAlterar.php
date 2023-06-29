<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabModalidadeAlterar.php
# Autor:    Rossana Lira
# Data:     03/04/03
# Objetivo: Programa de Alteração da Modalidade
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabModalidadeExcluir.php' );
AddMenuAcesso( '/tabelasbasicas/TabModalidadeSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao               = $_POST['Botao'];
		$Modalidade          = $_POST['Modalidade'];
		$ModalidadeDescricao = strtoupper2(trim($_POST['ModalidadeDescricao']));
		$Ordem               = trim($_POST['Ordem']);
}else{
		$Modalidade = $_GET['Modalidade'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Redireciona para a página de excluir #
if( $Botao == "Excluir" ){
		$Url = "TabModalidadeExcluir.php?Modalidade=$Modalidade";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	  header("location: ".$Url);
	  exit();
}elseif( $Botao == "Voltar" ){
		header("location: TabModalidadeSelecionar.php");
		exit();
}elseif( $Botao == "Alterar" ){
		# Critica dos Campos #
	  $Mens     = 0;
    $Mensagem = "Informe: ";
    if( $ModalidadeDescricao == "" ) {
        $LerTabela = 0;
		 	  $Mens      = 1;
		 	  $Tipo      = 2;
		    $Mensagem .= "<a href=\"javascript:document.Modalidade.ModalidadeDescricao.focus();\" class=\"titulo2\">Modalidade</a>";
    }
    if ($Ordem == "") {
        if ($Mens == 1){$Mensagem.=", ";}
        $LerTabela = 0;
    		$Mens      = 1;
    		$Tipo      = 2;
		    $Mensagem .= "<a href=\"javascript:document.Modalidade.Ordem.focus();\" class=\"titulo2\">Ordem</a>";
    }else{
				if( !SoNumeros($Ordem) ){
						$Mens     = 1;
						$Tipo     = 2;
						$Mensagem = "<a href=\"javascript:document.Modalidade.Ordem.focus();\" class=\"titulo2\"> Ordem de Exibição Inválida</a>";
				}
		}
    if( $Mens == 0 ){
				# Verifica a Duplicidade de Modalidade #
				$db  = Conexao();
		   	$sql = "SELECT COUNT(CMODLICODI) FROM SFPC.TBMODALIDADELICITACAO WHERE RTRIM(LTRIM(EMODLIDESC)) = '$ModalidadeDescricao' AND CMODLICODI <> $Modalidade";
		 		$res = $db->query($sql);
				if( PEAR::isError($res) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$Qtd = $res->fetchRow();
		    		if( $Qtd[0] > 0 ) {
					    	$Mens     = 1;
					    	$Tipo     = 2;
								$Mensagem = "<a href=\"javascript:document.Modalidade.ModalidadeDescricao.focus();\" class=\"titulo2\"> Modalidade Já Cadastrada</a>";
						}else{
						    # Verifica a Duplicidade da Ordem #
								$sql  = "SELECT COUNT(CMODLICODI) FROM SFPC.TBMODALIDADELICITACAO ";
								$sql .= " WHERE AMODLIORDE = $Ordem AND CMODLICODI <> $Modalidade";
						 		$res  = $db->query($sql);
								if( PEAR::isError($res) ){
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$Qtd = $res->fetchRow();
						    		if( $Qtd[0] > 0 ) {
								    	$Mens     = 1;
								    	$Tipo     = 2;
											$Mensagem = "<a href=\"javascript:document.Modalidade.Ordem.focus();\" class=\"titulo2\"> Ordem de Exibição Já Cadastrada</a>";
										}else{
								        # Atualiza Modalidade #
								        $db->query("BEGIN TRANSACTION");
												$sql  = "UPDATE SFPC.TBMODALIDADELICITACAO ";
												$sql .= "   SET AMODLIORDE = $Ordem, EMODLIDESC = '$ModalidadeDescricao', ";
								        $sql .= "       TMODLIULAT = '".date("Y-m-d H:i:s")."' ";
								        $sql .= " WHERE CMODLICODI = $Modalidade";
								        $res  = $db->query($sql);
												if( PEAR::isError($res) ){
														$db->query("ROLLBACK");
												    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$db->query("COMMIT");
														$db->query("END TRANSACTION");
										        $db->disconnect();

										        # Envia mensagem para página selecionar #
										        $Mensagem = urlencode("Modalidade Alterada com Sucesso");
										        $Url = "TabModalidadeSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
														if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
										        header("location: ".$Url);
										        exit();
										    }
								    }
								}
						}
				}
				$db->disconnect();
    }
}
if( $Botao == "" ){
		$db   = Conexao();
		$sql  = "SELECT EMODLIDESC, AMODLIORDE, CMODLICODI ";
		$sql .= "  FROM SFPC.TBMODALIDADELICITACAO ";
		$sql .= " WHERE CMODLICODI = $Modalidade";
		$res  = $db->query($sql);
		if (PEAR::isError($res)) {
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha = $res->fetchRow();
				$ModalidadeDescricao = $Linha[0];
				$Ordem               = $Linha[1];
				$Modalidade          = $Linha[2];
		}
		$db->disconnect();
}
?>
<html>
<?
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.Modalidade.Botao.value=valor;
	document.Modalidade.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabModalidadeAlterar.php" method="post" name="Modalidade">
<br><br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Modalidade > Manter
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
	           MANTER - MODALIDADE
          </td>
        </tr>
        <tr>
					<td class="textonormal">
						<p align="justify">
						Para atualizar a Modalidade, preencha os dados abaixo e clique no botão "Alterar". Para apagar a Modalidade clique no botão "Excluir".
						</p>
					</td>
        </tr>
        <tr>
          <td>
            <table border="0" width="100%" summary="">
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" width="30%">Modalidade*</td>
               	<td class="textonormal">
               		<input type="text" name="ModalidadeDescricao" size="40" maxlength="50" value="<?php echo $ModalidadeDescricao; ?>" class="textonormal">
                	<input type="hidden" name="Modalidade" value="<?php echo $Modalidade; ?>">
                </td>
              </tr>
              <tr>
              	<td class="textonormal" bgcolor="#DCEDF7">Ordem de Exibição*</td>
								<td class="textonormal">
									<input type="text" name="Ordem" size="2" value="<?php echo $Ordem; ?>" maxlength="2" class="textonormal">
								</td>
                </td>
	            </tr>
            </table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right">
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
<script language="javascript" type="">
<!--
document.Modalidade.ModalidadeDescricao.focus();
//-->
</script>
