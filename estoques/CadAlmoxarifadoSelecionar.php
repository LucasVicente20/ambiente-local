<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadAlmoxarifadoSelecionar.php
# Autor:    Franklin Alves
# Data:     27/06/05
# Objetivo: Programa de Manutenção de Almoxarifado
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/CadAlmoxarifadoAlterar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Almoxarifado  = $_POST['Almoxarifado'];
		$Botao         = $_POST['Botao'];
}else{
		$Mensagem = urldecode($_GET['Mensagem']);
		$Mens     = $_GET['Mens'];
		$Tipo     = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Selecionar" ){
		# Critica dos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";
    if( $Almoxarifado == "" ) {
	      if( $Mens == 1 ){ $Mensagem .= ", "; }
	      $Mens      = 1;
	      $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript: document.CadAlmoxarifadoSelecionar.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
    }else{
    		$Url = "CadAlmoxarifadoAlterar.php?Almoxarifado=$Almoxarifado";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	      header("location: ".$Url);
	      exit;
    }
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript">
<!--
function enviar(valor){
	document.CadAlmoxarifadoSelecionar.Botao.value=valor;
	document.CadAlmoxarifadoSelecionar.submit();
}
function remeter(){
	document.CadAlmoxarifadoSelecionar.submit();
}

<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadAlmoxarifadoSelecionar.php" method="post" name="CadAlmoxarifadoSelecionar">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> >  Estoques > Almoxarifado > Manter
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
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	           MANTER - ALMOXARIFADO
          </td>
        </tr>
        <tr>
          <td class="textonormal" bgcolor="#FFFFFF">
             <p align="justify">
             Para atualizar/excluir um Almoxarifado já cadastrado, selecione o Almoxarifado e clique no botão "Selecionar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" width="100%" summary="">
             <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Almoxarifado</td>
                <td class="textonormal">
                  <select name="Almoxarifado" class="textonormal">
	      	          <option value="">Selecione um Almoxarifado...</option>
	                	<?php
	              		# Mostra o(s) Almoxarifado(s) de Acordo com o Usuário Logado #
										$db   = Conexao();
			         			if( $_SESSION['_cgrempcodi_'] == 0 ){
		              			$sql    = "SELECT A.CALMPOCODI, A.EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL A ";
												if ($Almoxarifado) {
														$sql   .= " WHERE A.CALMPOCODI = $Almoxarifado ";
												}
										} else {
		              			$sql    = "SELECT A.CALMPOCODI, A.EALMPODESC, B.CORGLICODI ";
												$sql   .= "  FROM SFPC.TBALMOXARIFADOPORTAL A, SFPC.TBALMOXARIFADOORGAO B ";
												$sql   .= " WHERE A.CALMPOCODI = B.CALMPOCODI ";
												if ($Almoxarifado) {
														$sql   .= " AND A.CALMPOCODI = $Almoxarifado ";
												}
												$sql .= "   AND B.CORGLICODI = ";
									    	$sql .= "       ( SELECT DISTINCT CEN.CORGLICODI ";
									    	$sql .= "           FROM SFPC.TBCENTROCUSTOPORTAL CEN, SFPC.TBUSUARIOCENTROCUSTO USU ";
										    $sql .= "          WHERE USU.CCENPOSEQU = CEN.CCENPOSEQU AND USU.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." AND USU.FUSUCCTIPO IN ('T','R') ";
										    $sql .= "		AND CEN.FCENPOSITU <> 'I') "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
											var_dump($sql);
	                  							}	
										$sql .= " ORDER BY A.EALMPODESC ";
										
	              		$res  = $db->query($sql);
										if( PEAR::isError($res) ){
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$Rows = $res->numRows();
												for( $i=0;$i< $Rows; $i++ ){
														$Linha = $res->fetchRow();
														$DescAlmoxarifado = $Linha[1];
          	   	      			if( $Linha[0] == $Almoxarifado ){
          	   	      					echo"<option value=\"$Linha[0]\" selected>$DescAlmoxarifado</option>\n";
		          	      			}else{
		          	      					echo"<option value=\"$Linha[0]\">$DescAlmoxarifado</option>\n";
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
	      	<td align="right">
	      		<input type="button" value="Selecionar" class="botao" onClick="javascript:enviar('Selecionar');">
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
<script language="javascript">
<!--
document.CadAlmoxarifadoSelecionar.Almoxarifado.focus();
//-->
</script>
