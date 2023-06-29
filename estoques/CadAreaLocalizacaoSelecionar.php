<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadAreaLocalizacaoSelecionar.php
# Autor:    Franklin Alves
# Data:     14/07/05
# Objetivo: Programa de Manutenção de Área de Localização
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/CadAreaLocalizacaoAlterar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Area		= $_POST['Area'];
		$Botao  = $_POST['Botao'];
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
    if( $Area == "" ) {
	      $Mens      = 1;
	      $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript:document.CadAreaLocalizacaoSelecionar.Area.focus();\" class=\"titulo2\">Área</a>";
    }else{
    		$Url = "CadAreaLocalizacaoAlterar.php?Area=$Area";
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
	document.CadAreaLocalizacaoSelecionar.Botao.value=valor;
	document.CadAreaLocalizacaoSelecionar.submit();
}
function remeter(){
	document.CadAreaLocalizacaoSelecionar.submit();
}

<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadAreaLocalizacaoSelecionar.php" method="post" name="CadAreaLocalizacaoSelecionar">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> >  Estoques > Área > Manter
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
	           MANTER - ÁREA
          </td>
        </tr>
        <tr>
          <td class="textonormal" bgcolor="#FFFFFF">
             <p align="justify">
             Para atualizar/excluir uma Área já cadastrada, selecione a Área e clique no botão "Selecionar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" width="100%" summary="">
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" width="30%">Área</td>
                <td class="textonormal">
                  <select name="Area" class="textonormal">
	      	          <option value="">Selecione uma Área...</option>
                    <?php
										# Mostra as áreas cadastradas de acordo com o grupo logado #
										$db   = Conexao();
										# ALTERAR QUANDO OS USUÁRIOS FOREM CADASTRAR AS SUAS PRÓPRIAS ÁREAS
										$sql  = "SELECT E.CARLOCCODI, E.EARLOCDESC, A.CALMPOCODI, A.EALMPODESC";
										$sql .= "  FROM SFPC.TBALMOXARIFADOPORTAL A, SFPC.TBALMOXARIFADOORGAO B, ";
										$sql .= "  			SFPC.TBGRUPOORGAO C, SFPC.TBGRUPOEMPRESA D, SFPC.TBAREAALMOXARIFADO E ";
										$sql .= " WHERE A.FALMPOSITU = 'A' AND A.CALMPOCODI = B.CALMPOCODI ";
										$sql .= "   AND B.CORGLICODI = C.CORGLICODI AND C.CGREMPCODI = D.CGREMPCODI ";
										$sql .= "   AND A.CALMPOCODI = E.CALMPOCODI  ";
										if( $_SESSION['_cgrempcodi_'] != 0 ){
										    $sql .= "   AND C.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ";
										}
										$sql .= "   ORDER BY A.EALMPODESC, E.EARLOCDESC  ";
										$result = $db->query($sql);
										if( PEAR::isError($result) ){
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												while( $Linha = $result->fetchRow() ){
										    		if( $Linha[0] == $Almoxarifado ){
										    				echo"<option value=\"$Linha[0]\" selected>$Linha[3]_$Linha[1]</option>\n";
										        }else{
										         		echo"<option value=\"$Linha[0]\">$Linha[3]_$Linha[1]</option>\n";
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
