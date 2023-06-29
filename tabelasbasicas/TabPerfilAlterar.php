<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabPerfilAlterar.php
# Autor:    Rossana Lira
# Data:     04/04/03
# Objetivo: Programa de Alteração da Perfil
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
AddMenuAcesso( '/tabelasbasicas/TabPerfilExcluir.php' );
AddMenuAcesso( '/tabelasbasicas/TabPerfilSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao           = $_POST['Botao'];
		$Critica         = $_POST['Critica'];
		$PerfilCodigo    = $_POST['PerfilCodigo'];
		$PerfilDescricao = strtoupper2(trim($_POST['PerfilDescricao']));
		$Situacao        = $_POST['Situacao'];
		$PerfCorp        = $_POST['Corporativo'];
}else{
		$PerfilCodigo    = $_GET['PerfilCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabPerfilAlterar.php";

# Redireciona para a página de excluir #
if( $Botao == "Excluir" ){
		$Url = "TabPerfilExcluir.php?PerfilCodigo=$PerfilCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	  header("location: ".$Url);
	  exit();
}else if( $Botao == "Voltar" ){
		header("location: TabPerfilSelecionar.php");
		exit();
}else{
		# Critica dos Campos #
		if( $Critica == 1 ) {
			  $Mens     = 0;
		    $Mensagem = "Informe: ";
		    if( $PerfilDescricao == "" ) {
			      $Critica   = 1;
		        $LerTabela = 0;
				 	  $Mens      = 1;
				 	  $Tipo      = 2;
				    $Mensagem .= "<a href=\"javascript:document.Perfil.PerfilDescricao.focus();\" class=\"titulo2\">Perfil</a>";
		    }
		    if( $Mens == 0 ){
						# Verifica a Duplicidade de Perfil #
						$db     = Conexao();
				   	$sql    = "SELECT COUNT(CPERFICODI) FROM SFPC.TBPERFIL WHERE RTRIM(LTRIM(EPERFIDESC)) = '$PerfilDescricao' AND CPERFICODI <> $PerfilCodigo ";
				 		$result = $db->query($sql);
						if (PEAR::isError($result)) {
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
				    		$Linha = $result->fetchRow();
								$Qtd = $Linha[0];
				    		if( $Qtd > 0 ) {
						    	$Mens     = 1;
						    	$Tipo     = 2;
									$Mensagem = "<a href=\"javascript:document.Perfil.PerfilDescricao.focus();\" class=\"titulo2\"> Perfil Já Cadastrado</a>";
								}else{
						        # Atualiza Perfil #
						        $Data   = date("Y-m-d H:i:s");
						        $db->query("BEGIN TRANSACTION");
						        $sql    = "UPDATE SFPC.TBPERFIL ";
						        $sql   .= "   SET EPERFIDESC = '$PerfilDescricao', FPERFISITU = '$Situacao', ";
						        $sql   .= "       TPERFIULAT = '$Data', FPERFICORP = '$PerfCorp' ";
						        $sql   .= " WHERE CPERFICODI = $PerfilCodigo";
						        $result = $db->query($sql);
										if( PEAR::isError($result) ){
												$db->query("ROLLBACK");
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$db->query("COMMIT");
												$db->query("END TRANSACTION");
								        $db->disconnect();

								        # Envia mensagem para página selecionar #
								        $Mensagem = urlencode("Perfil Alterado com Sucesso");
								        $Url = "TabPerfilSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1&Critica=0";
												if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
								        header("location: ".$Url);
								        exit();
								     }
							  }
						}
						$db->disconnect();
		    }
		}
}
if( $Critica == 0 ){
		$db     = Conexao();
		$sql    = "SELECT EPERFIDESC, CPERFICODI, FPERFISITU, FPERFICORP FROM SFPC.TBPERFIL WHERE CPERFICODI = $PerfilCodigo";
		$result = $db->query($sql);
		if (PEAR::isError($result)) {
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$PerfilDescricao = $Linha[0];
						$PerfilCodigo    = $Linha[1];
						$Situacao        = $Linha[2];
						$PerfCorp        = $Linha[3];
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
<form action="TabPerfilAlterar.php" method="post" name="Perfil">
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
	        	MANTER - PERFIL
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
             Para atualizar o Perfil, preencha os dados abaixo e clique no botão "Alterar". Para apagar o Perfil clique no botão "Excluir".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Perfil* </td>
               	<td class="textonormal">
               		<input type="text" name="PerfilDescricao" size="40" maxlength="30" value="<?php echo $PerfilDescricao?>" class="textonormal">
                	<input type="hidden" name="Critica" value="1">
                	<input type="hidden" name="PerfilCodigo" value="<?php echo $PerfilCodigo?>">
                </td>
              </tr>
              <tr>
              	<td class="textonormal" bgcolor="#DCEDF7">Situação* </td>
	              <td class="textonormal">
	                <?php if( $Situacao == "A") { $DescSituacao = "ATIVO"; }else{ $DescSituacao = "INATIVO"; }	 ?>
	                <select name="Situacao" value="<?php echo $DescSituacao; ?>" class="textonormal">
	        	        <option value="A" <?php if ( $Situacao == "A" ) { echo "selected"; }?>>ATIVO</option>
                    <option value="I" <?php if ( $Situacao == "I" ) { echo "selected"; }?>>INATIVO</option>
                  </select>
                </td>
	            </tr>
							<tr>
              	<td class="textonormal" bgcolor="#DCEDF7">Perfil corporativo </td>
	              <td class="textonormal">
	                <?php if( $PerfCorp == "S") { $DescCorp = "SIM"; } else { $DescCorp = "NÃO"; }	 ?>
	                <select name="Corporativo" value="<?php echo $DescCorp; ?>" class="textonormal">
	        	        <option value="S" <?php if ( $PerfCorp == "S" ) { echo "selected"; }?>>SIM</option>
                    <option value="N" <?php if ( $PerfCorp == "N" || $PerfCorp == ""  ) { echo "selected"; }?>>NÃO</option>
                  </select>
                </td>
	            </tr>
            </table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right">
	          <input type="button" value="Alterar" class="botao" onclick="javascript:enviar('Alterar');">
						<input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Excluir');">
	          <input name="voltar" type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar')">
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
document.Perfil.PerfilDescricao.focus();
//-->
</script>
