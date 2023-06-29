<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelAcompContabil.php
# Objetivo: Imprimir o Relatório de Acompanhamento Contábil
# Autor:    Ariston Cordeiro
# Data:     01/08/08
#----------------------
# Alterado:	Ariston
# Data:			11/08/08	-	Esta arquivo estava informando o valor de 'Formulario' errado para calendário.php. Foi consertado agora
#											- Mensagens de erros em data inicial e data final agora são mais específicos.
#---------------------
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/RelAcompContabil.php' );
AddMenuAcesso( '/estoques/RelAcompContabilPdf.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
		$Botao        = $_POST['Botao'];
		$Situacao     = $_POST['Situacao'];
		$Todas        = $_POST['Todas'];
		$DataIni      = $_POST['DataIni'];
		if( $DataIni != "" ){ $DataIni = FormataData($DataIni); }
		$DataFim      = $_POST['DataFim'];
		if( $DataFim != "" ){ $DataFim = FormataData($DataFim); }
		$NumNota      = $_POST['NumNota'];
		$SerNota      = $_POST['SerNota'];
		$Almoxarifado = $_POST['Almoxarifado'];
		$CarregaAlmoxarifado = $_POST['CarregaAlmoxarifado'];
}else{
		$Mensagem = urldecode($_GET['Mensagem']);
		$Mens     = $_GET['Mens'];
		$Tipo     = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Limpar" ){
 		header("location: RelAcompContabil.php");
	  exit;
}elseif( $Botao == "Imprimir" ){
		# Critica dos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( ($Almoxarifado == "") && ($CarregaAlmoxarifado == 'N') ){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Almoxarifado";
		} elseif ($Almoxarifado == "") {
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RelAcompContabil.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		}
		
		# Validar Datas
		if( $DataIni != "" ){
				$MensErro = ValidaData($DataIni);
				if( $MensErro != "" ){
						if( $Mens == 1 ){ $Mensagem .= ", "; }
						$Mens      = 1;
				  	$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.RelAcompContabil.DataIni.focus();\" class=\"titulo2\">Data inicial válida (".$MensErro.")</a>";
				}
		}
		if( $DataFim != "" ){
				$MensErro = ValidaData($DataFim);
				if( $MensErro != "" ){
						if( $Mens == 1 ){ $Mensagem .= ", "; }
						$Mens      = 1;
				  	$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.RelAcompContabil.DataFim.focus();\" class=\"titulo2\">Data final válida (".$MensErro.")</a>";
				}
		}
		if($Mens == 0){
			$DataIniDB = DataInvertida($DataIni);
			$DataFimDB = DataInvertida($DataFim);
			if( $DataIniDB > $DataFimDB ){
						if( $Mens == 1 ){ $Mensagem .= ", "; }
						$Mens      = 1;
				  	$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.RelAcompContabil.DataIni.focus();\" class=\"titulo2\">Data Inicial deve ser menor que a data final</a>";
			} else if( $DataIniDB < "2008-01-01" ){
						if( $Mens == 1 ){ $Mensagem .= ", "; }
						$Mens      = 1;
				  	$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.RelAcompContabil.DataIni.focus();\" class=\"titulo2\">Ano da data inicial deve ser maior ou igual a 2008</a>";
			}
		}
		if($Mens == 0){
				$Url = "RelAcompContabilPdf.php?Almoxarifado=$Almoxarifado&DataIni=$DataIni&DataFim=$DataFim&".mktime();
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
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
<!--
function enviar(valor){
		document.RelAcompContabil.Botao.value = valor;
		document.RelAcompContabil.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RelAcompContabil.php" method="post" name="RelAcompContabil">
<br><br><br><br><br>
<table cellpadding="3" border="0" width="100%" summary="">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Relatórios > Acompanhamento Contábil
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
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
        		 RELATÓRIO DE ACOMPANHAMENTO CONTÁBIL
          </td>
        </tr>
        <tr>
          <td class="textonormal" colspan="4">
             <p align="justify">
             	Para Imprimir o relatório, informe os campos abaixo, clique no botão "Imprimir".
             </p>
          </td>
        </tr>
        <tr>
          <td colspan="4">
            <table border="0" width="100%" summary="">
	            <tr>
	              <td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Almoxarifado*</td>
	              <td class="textonormal">
                	<?php
              		# Mostra o(s) Almoxarifado(s) de Acordo com o Usuário Logado #
									$db  = Conexao();
                  if($_SESSION['_cgrempcodi_'] == 0 or $_SESSION['_fperficorp_'] == 'S'){
	              			$sql    = "SELECT A.CALMPOCODI, A.EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL A ";
											if ($Almoxarifado) {
													$sql   .= " WHERE A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A'";
											}
									} else {
	              			$sql    = "SELECT A.CALMPOCODI, A.EALMPODESC ";
											$sql   .= "  FROM SFPC.TBALMOXARIFADOPORTAL A, SFPC.TBALMOXARIFADOORGAO B ";
											$sql   .= " WHERE A.CALMPOCODI = B.CALMPOCODI ";
											if ($Almoxarifado) {
													$sql   .= " AND A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A'";
											}
											$sql .= "   AND B.CORGLICODI = ";
								    	$sql .= "       ( SELECT DISTINCT CEN.CORGLICODI ";
								    	$sql .= "           FROM SFPC.TBCENTROCUSTOPORTAL CEN, SFPC.TBUSUARIOCENTROCUSTO USU ";
									    $sql .= "          WHERE USU.CCENPOSEQU = CEN.CCENPOSEQU AND USU.CUSUPOCODI = ". $_SESSION['_cusupocodi_'] ." AND CEN.FCENPOSITU <> 'I' AND USU.FUSUCCTIPO IN ('T','R') ";
									    
									    # restringir almoxarifado quando requisitante
									    $sql .= "            AND CASE WHEN USU.FUSUCCTIPO = 'T' THEN B.CALMPOCODI = USU.CALMPOCODI ELSE CEN.FCENPOSITU <> 'I' END";
									    
									    $sql .= "       ) ";
                  }
									$sql .= " ORDER BY A.EALMPODESC ";
              		$res  = $db->query($sql);
									if( PEAR::isError($res) ){
									    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
									}else{
											$Rows = $res->numRows();
											if( $Rows == 1 ){
													$Linha = $res->fetchRow();
	          	      			$Almoxarifado = $Linha[0];
        	   	      			echo "$Linha[1]<br>";
        	   	      			echo "<input type=\"hidden\" name=\"Almoxarifado\" value=\"$Almoxarifado\">";
        	   	      			echo $DescAlmoxarifado;
				            	}elseif( $Rows > 1 ){
													echo "<select name=\"Almoxarifado\" class=\"textonormal\">\n";
				                  echo "	<option value=\"\">Selecione um Almoxarifado...</option>\n";
													for( $i=0;$i< $Rows; $i++ ){
															$Linha = $res->fetchRow();
															$DescAlmoxarifado = $Linha[1];
	          	   	      			if( $Linha[0] == $Almoxarifado ){
	          	   	      					echo"<option value=\"$Linha[0]\" selected>$DescAlmoxarifado</option>\n";
			          	      			}else{
			          	      					echo"<option value=\"$Linha[0]\">$DescAlmoxarifado</option>\n";
			          	      			}
				                	}
				                	echo "</select>\n";
				                	$CarregaAlmoxarifado = "";
				              }else{
				            			echo "ALMOXARIFADO NÃO CADASTRADO OU INATIVO";
		  	          	   	  echo "<input type=\"hidden\" name=\"CarregaAlmoxarifado\" value=\"N\">";
				            	}
		              }
           			 	$db->disconnect();
    	            ?>
	              </td>
	            </tr>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Período de Entrada*</td>
                <td class="textonormal">
									<?php
    	      			$DataMes = DataMes();
    	      			if( $DataIni == "" ){ $DataIni = $DataMes[0]; }
									if( $DataFim == "" ){ $DataFim = $DataMes[1]; }
									$URLIni = "../calendario.php?Formulario=RelAcompContabil&Campo=DataIni";
									$URLFim = "../calendario.php?Formulario=RelAcompContabil&Campo=DataFim";
									?>
									<input type="text" name="DataIni" size="10" maxlength="10" value="<?php echo $DataIni;?>" class="textonormal">
									<a href="javascript:janela('<?php echo $URLIni ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
									&nbsp;a&nbsp;
									<input type="text" name="DataFim" size="10" maxlength="10" value="<?php echo $DataFim;?>" class="textonormal">
									<a href="javascript:janela('<?php echo $URLFim ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right" colspan="4">
   	      	<input type="button" name="Imprimir" value="Imprimir" class="botao" onClick="javascript:enviar('Imprimir')">
   	      	<input type="button" name="Limpar" value="Limpar" class="botao" onClick="javascript:enviar('Limpar')">
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
