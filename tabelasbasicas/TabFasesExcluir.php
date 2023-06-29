<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabFasesExcluir.php
# Autor:    Rossana Lira
# Data:     23/04/03
# Objetivo: Programa de Exclusão da Fases
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabFasesAlterar.php' );
AddMenuAcesso( '/tabelasbasicas/TabFasesSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']  == "POST"){
		$Botao       = $_POST['Botao'];
		$Critica     = $_POST['Critica'];
		$FasesCodigo = $_POST['FasesCodigo'];
}else{
		$FasesCodigo = $_GET['FasesCodigo'];
}

# Critica dos Campos #
if( $Botao == "Voltar" ){
		$Url = "TabFasesAlterar.php?FasesCodigo=$FasesCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit();
}else{
		if( $Critica == 1 ) {
				$Mens     = 0;
		    $Mensagem = "Informe: ";

			  # Verifica se a Fase está relacionada com alguma licitação #
		    $db     = Conexao();
		    $sql    = "SELECT COUNT(*)FROM SFPC.TBFASELICITACAO WHERE CFASESCODI = $FasesCodigo";
		    $result = $db->query($sql);
				if( PEAR::isError($result) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$Linha = $result->fetchRow();
						$QtdLicitacao = $Linha[0];
				    if( $QtdLicitacao > 0 ) {
				        $Mensagem = urlencode("Exclusão Cancelada!<br>Fases Relacionada com ($QtdLicitacao) Licitação(ões)");
				        $Url = "TabFasesSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=2";
								if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				        header("location: ".$Url);
				        exit();
				    }else{
						    # Verifica se a Fase está relacionada com alguma Ata #
						    $sql   = "SELECT COUNT(*) AS Qtd FROM SFPC.TBATASFASE WHERE CFASESCODI = $FasesCodigo";
						    $result = $db->query($sql);
								if( PEAR::isError($result) ){
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$Linha = $result->fetchRow();
										$QtdAta = $Linha[0];
								    if( $QtdAta > 0 ){
								        $Mensagem = urlencode("Exclusão Cancelada!<br>Fases Relacionada com ($QtdAta) Ata(s)");
								        $Url = "TabFasesSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=2";
												if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
								        header("location: ".$Url);
								        exit();
								    }
								    if( $Mens == 0 ){
								    	  # Exclui Fases #
									      $db->query("BEGIN TRANSACTION");
									      $sql    = "DELETE FROM SFPC.TBFASES WHERE CFASESCODI = $FasesCodigo";
									 	    $result = $db->query($sql);
												if( PEAR::isError($result) ){
														$db->query("ROLLBACK");
												    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$db->query("COMMIT");
										        $db->query("END TRANSACTION");
										        $db->disconnect();

														# Envia mensagem para página selecionar #
														$Mensagem = urlencode("Fase Excluída com Sucesso");
														$Url = "TabFasesSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
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
if( $Critica == 0 ){
		$db     = Conexao();
		$sql    = "SELECT EFASESDESC,AFASESORDE FROM SFPC.TBFASES WHERE CFASESCODI = $FasesCodigo";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$FasesDescricao = $Linha[0];
						$Ordem          = $Linha[1];
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
	document.Fases.Botao.value=valor;
	document.Fases.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabFasesExcluir.php" method="post" name="Fases">
<br><br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Fases > Manter
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
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#ffffff">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	        	EXCLUIR - FASES
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
               Para confirmar a exclusão da Fase clique no botão "Excluir", caso contrário clique no botão "Voltar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0">
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Fase </td>
               	<td class="textonormal">
               		<?php echo $FasesDescricao; ?>
                	<input type="hidden" name="Critica" value="1">
                	<input type="hidden" name="FasesCodigo" value="<?php echo $FasesCodigo; ?>">
                </td>
              </tr>
              <tr>
								<td class="textonormal" bgcolor="#DCEDF7" height="20">Ordem de Exibição</td>
								<td class="textonormal"><?php echo $Ordem; ?></td>
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
