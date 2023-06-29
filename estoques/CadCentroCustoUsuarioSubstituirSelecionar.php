<?php
#-----------------------------------------------------------------------------
# Portal da DGCO teste
# Programa: CadCentroCustoUsuarioSubstituirSelecionar.php
# Autor:    Roberta Costa
# Data:     09/06/05
# Objetivo: Programa que seleciona o Usuário para Substituição do Aprovador
# OBS.:     Tabulação 2 espaços
#-----------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/CadCentroCustoUsuarioSubstituir.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
		$Botao   = $_POST['Botao'];
		$Usuario = $_POST['Usuario'];
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
    if( $Usuario == "" ) {
	      $Mens      = 1;
	      $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript: document.CentroCusto.Usuario.focus();\" class=\"titulo2\">Usuário</a>";
    }else{
    		$Url = "CadCentroCustoUsuarioSubstituir.php?Usuario=$Usuario";
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
<script language="javascript" type="">
<!--
function enviar(valor){
	document.CadCentroCustoUsuarioSubstituirSelecionar.Botao.value = valor;
	document.CadCentroCustoUsuarioSubstituirSelecionar.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadCentroCustoUsuarioSubstituirSelecionar.php" method="post" name="CadCentroCustoUsuarioSubstituirSelecionar">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Substituição de Aprovador
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
	        		SUBSTITUIÇÃO DE APROVADOR
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
             Para Substituir um Aprovador para Centro de Custo escolha o Usuário desejado e clique no botão "Selecionar".<br>
             Só aparecerão os usuários do tipo Aprovador.
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" width="100%" summary="">
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Usuário</td>
                <td class="textonormal">
                  <select name="Usuario" class="textonormal">
                  	<option value="">Selecione um Usuário...</option>
                  	<?php
                		# Mostra os usuários cadastrados #
                		$db   = Conexao();
            				$sql  = "SELECT A.CGREMPCODI, A.CUSUPOCODI, C.EGREMPDESC, D.EORGLIDESC, ";
            				$sql .= "       E.EUSUPORESP, B.ecenpodesc ";
            				$sql .= "  FROM SFPC.TBUSUARIOCENTROCUSTO A, SFPC.TBCENTROCUSTOPORTAL B, SFPC.TBGRUPOEMPRESA C, ";
                		$sql .= "       SFPC.TBORGAOLICITANTE D, SFPC.TBUSUARIOPORTAL E ";
                 		$sql .= " WHERE A.FUSUCCTIPO = 'A' AND A.CGREMPCODI <> 0  AND A.FUSUCCTIPO IN ('T','R')"; // Para retirar o grupo internet
                		$sql .= "   AND A.CCENPOSEQU = B.CCENPOSEQU AND A.CGREMPCODI = C.CGREMPCODI ";
                		$sql .= "   AND B.CORGLICODI = D.CORGLICODI ";
                 		$sql .= "   AND A.CUSUPOCODI = E.CUSUPOCODI ";
	              		if( $_SESSION['_cgrempcodi_'] != 0 ){
			                  $sql .= "   AND A.CGREMPCODI = ".$_SESSION['_cgrempcodi_']."";
	                  }
	                  $sql .= "   AND B.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
                		$sql .= " ORDER BY C.EGREMPDESC, D.EORGLIDESC, B.ecenpodesc, E.EUSUPORESP";
                		$res  = $db->query($sql);
										if( PEAR::isError($res) ){
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$GrupoDescAntes       = "";
												$OrgaoDescAntes       = "";
												$CentroCustoDescAntes = "";
												while( $Linha = $res->fetchRow() ){
		          	      			if( $_SESSION['_cgrempcodi_'] == 0 ){
				          	      			$GrupoDesc = $Linha[2];
		          	   	      			if( $GrupoDescAntes != $GrupoDesc ){
				          	   	      			echo"<option value=\"\">$GrupoDesc</option>\n";
				          	   	      	}
				          	   	      	$Edentecao = "&nbsp;&nbsp;&nbsp;&nbsp;";
				          	   	    }
				          	   	    $OrgaoDesc = $Linha[3];
          	   	      			if( $OrgaoDescAntes != $OrgaoDesc ){
		          	   	      			echo"<option value=\"\">$Edentecao $OrgaoDesc</option>\n";
		          	   	      	}
				          	   	    $CentroCustoDesc = $Linha[5];
          	   	      			if( $CentroCustoDescAntes != $CentroCustoDesc ){
		          	   	      			echo"<option value=\"\">$Edentecao&nbsp;&nbsp;&nbsp;&nbsp;$CentroCustoDesc</option>\n";
		          	   	      	}
          	      					echo"<option value=\"$Linha[1]\">$Edentecao&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$Linha[4]</option>\n";
		          	      			$GrupoDescAntes       = $GrupoDesc;
		          	      			$OrgaoDescAntes       = $OrgaoDesc;
		          	      			$CentroCustoDescAntes = $CentroCustoDesc;
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
   	      	<input type="button" name="Selecionar" value="Selecionar" class="botao" onClick="javascript:enviar('Selecionar')">
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
document.CadCentroCustoUsuarioSubstituirSelecionar.Usuario.focus();
//-->
</script>
