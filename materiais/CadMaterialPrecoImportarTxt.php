<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadMaterialPrecoImportarTxt.php
# Autor:    Carlos Abreu
# Data:     11/06/2007
# Objetivo: Programa de Importação TXT com lista de Preços
# Autor:    Rossana Lira
# Data:     03/07/2007 - Troca da coluna [84] por [88] devido a mudança de 
#                        arquivo pelo usuário
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
		$Botao                    = $_POST['Botao'];
}else{
		$Mensagem                 = urldecode($_GET['Mensagem']);
		$Mens                     = $_GET['Mens'];
		$Tipo                     = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if($Botao == "Limpar"){
		header("location: CadMaterialPrecoImportarTxt.php");
		exit;
}elseif( $Botao == "Importar" ){
	# Critica dos Campos #
	$Mens     = 0;
	$Mensagem = "Informe: ";
	$_FILES['Arquivo']['name'] = RetiraAcentos($_FILES['Arquivo']['name']);
	if( !eregi("\.csv$", $_FILES['Arquivo']['name']) ){
			$Mens = 1; $Tipo = 2;
			$Mensagem .= "somente arquivos com a extensão (.csv)";
	} else {
		$Tamanho = 5242880; /* 5MB */
		if( ( $_FILES['Arquivo']['size'] > $Tamanho ) ||
		    ( $_FILES['Arquivo']['size'] == 0) ){
				$Mens = 1; $Tipo = 2;
				$Kbytes = $Tamanho/1024;
				$Kbytes = (int) $Kbytes;
				$Mensagem = "Este arquivo é muito grande ou está vazio. Tamanho Máximo: $Kbytes Kb";
		}
		if ($Mens == 0){
			$Conteudo = file_get_contents($_FILES['Arquivo']['tmp_name']);
			if( file_exists($_FILES['Arquivo']['tmp_name']) ){ unlink ($_FILES['Arquivo']['tmp_name']);}
			$Conteudo = explode("\r\n",$Conteudo);
			for ($i=0;$i<count($Conteudo);$i++){
				$Conteudo[$i] = explode("§",$Conteudo[$i]);
				$Conteudo[$i][88] = trim(str_replace("\"","",$Conteudo[$i][88]));
			}
			$db   = Conexao();
			$db->query("BEGIN TRANSACTION");
			$DataHora = date("Y-m-d H:i:s");
			$Data     = date("Y-m-d");
			for ($i=0;$i<count($Conteudo);$i++){
				if ( preg_match("/^\d+$/",$Conteudo[$i][1]) and preg_match("/^\d+|\d+[,]+\d+$/",$Conteudo[$i][88]) ){
					$Atualizado = 1;
					$Material = $Conteudo[$i][1];
					$NovoPreco = str_replace(",",".",$Conteudo[$i][88]);
					# Verifica se foi cadastrado o preço do material no dia corrente
					$sql  = "SELECT COUNT(*) FROM SFPC.TBPRECOMATERIAL WHERE CMATEPSEQU = $Material AND DPRECMCADA = '$Data'";
					$result = $db->query($sql);
					if (PEAR::isError($result)) {
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
							$db->query("ROLLBACK");
							$db->query("END");
					}else{
						$Linha    = $result->fetchRow();
						if ($Linha[0] > 0) {
							# Altere o preço na Tabela de Materiais #
							$sql  = "UPDATE SFPC.TBPRECOMATERIAL ";
							$sql .= "   SET VPRECMPREC = $NovoPreco, TPRECMULAT = '$DataHora'";
							$sql .= " WHERE CMATEPSEQU = $Material AND DPRECMCADA = '$Data' ";
							$res  = $db->query($sql);
							if( PEAR::isError($res) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								$db->query("ROLLBACK");
								$db->query("END");
								exit;
							}
						} else {
							# Inclui na Tabela de Materiais #
							$sql  = "INSERT INTO SFPC.TBPRECOMATERIAL (CMATEPSEQU, DPRECMCADA, VPRECMPREC, TPRECMULAT)";
							$sql .= "   VALUES ($Material, '$Data', $NovoPreco,'$DataHora')";
							$res  = $db->query($sql);
							if( PEAR::isError($res) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								$db->query("ROLLBACK");
								$db->query("END");
								exit;
							}
						}		
					}
				}
			}
			if ($Atualizado == 1){
				$db->query("COMMIT");
				$db->query("END");
				$Tipo = 1;
				$Mens = 1;
				$Mensagem = "Infomações importadas com sucesso";
			} else {
				$db->query("ROLLBACK");
				$db->query("END");
				$Tipo = 2;
				$Mens = 1;
				$Mensagem = "Nenhuma informação importada";
			}
			$db->disconnect();
		}
	}
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
	document.CadMaterialPrecoImportarTxt.Botao.value = valor;
	document.CadMaterialPrecoImportarTxt.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form enctype="multipart/form-data" action="CadMaterialPrecoImportarTxt.php" method="post" name="CadMaterialPrecoImportarTxt">
<br><br><br><br><br>
<table cellpadding="3" border="0" width="100%" summary="">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais > Preço > Importar TXT
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
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" class="textonormal"  bgcolor="#FFFFFF" summary="">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="2">
	           PREÇOS DE MATERIAIS - IMPORTAR TXT
          </td>
        </tr>
        <tr>
          <td class="textonormal" colspan="2">
             <p align="justify">
             Para importar o arquivo precisa ter a extensão (.csv) e ter o delimitador de campos o símbolo (§). Selecione o arquivo e clique no botão importar.
             </p>
          </td>
        </tr>
        <tr>
          <td colspan="2">
            <table border="0" width="100%" summary="">
	            <tr>
	              <td class="textonormal" bgcolor="#DCEDF7" >Arquivo</td>
	              <td class="textonormal">
	              	<input type="file" name="Arquivo" >
	              </td>
	            </tr>
            </table>
          </td>
        </tr>
     		<tr>
        	<td colspan="2" align="right">
        		<input type="button" value="Enviar" class="botao" onclick="javascript:enviar('Importar');">
	       		<input type="button" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
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
