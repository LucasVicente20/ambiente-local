<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CorrecaoValorMaterial.php
# Objetivo: Programa para correção do valor unitário e médio de um material em um almoxarifado na tabela de armazenamento de material (tbamarzenamentomaterial).
# Autor:    Rodrigo Melo
# Data:     22/07/2008
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

$ProgramaOrigem = "CorrecaoValorMaterial";

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao          = $_POST['Botao'];
		$Almoxarifado   = $_POST['Almoxarifado'];
		$Material       = $_POST['Material'];    
    $ValorUnitario  = $_POST['ValorUnitario'];
    $ValorMedio     = $_POST['ValorMedio'];    
}else{
		$Mensagem       = urldecode($_GET['Mensagem']);
		$Mens           = $_GET['Mens'];
		$Tipo           = $_GET['Tipo'];
		$Troca          = $_GET['Troca'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CorrecaoValorMaterial.php";
$GrupoEmp        = $_SESSION['_cgrempcodi_'];
$Usuario         = $_SESSION['_cusupocodi_'];

# Descobre o ano atual #
$Ano = date("Y");

# Padrão que pode ser mudado durante o programa. Desta forma converte última vírgula da mensagem de erro por "e" #
if(!$Troca) $Troca = 1;

if($Botao == "Limpar"){		
		header("location: CorrecaoValorMaterial.php");
		exit;
}elseif($Botao == "Alterar"){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if(!$Almoxarifado){
      $Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.CorrecaoValorMaterial.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
    }
    if(!$Material){
        if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CorrecaoValorMaterial.Material.focus();\" class=\"titulo2\">Material</a>";
		} else {
      $ValorUnitarioCritica  = str_replace(",",".",str_replace(".","",$ValorUnitario));
      $ValorMedioCritica  = str_replace(",",".",str_replace(".","",$ValorMedio));
  		if(!$ValorUnitario || !is_numeric($ValorUnitarioCritica)){
  				if($Mens == 1){ $Mensagem .= ", "; }
  				$Mens      = 1;
  				$Tipo      = 2;
  				$Mensagem .= "<a href=\"javascript:document.CorrecaoValorMaterial.ValorUnitario.focus();\" class=\"titulo2\">Valor Unitário</a>";
  		}
      
  		if(!$ValorMedio || !is_numeric($ValorMedioCritica)){
  				if($Mens == 1){ $Mensagem .= ", "; }
  				$Mens      = 1;
  				$Tipo      = 2;
  				$Mensagem .= "<a href=\"javascript:document.CorrecaoValorMaterial.ValorMedio.focus();\" class=\"titulo2\">Valor Médio</a>";
  		}
    }
				
  
		if($Mens == 0){
       
      $db   = Conexao();
    
      $sql  = "SELECT LOC.CLOCMACODI FROM SFPC.TBLOCALIZACAOMATERIAL LOC WHERE LOC.CALMPOCODI = $Almoxarifado";

      $res  = $db->query($sql);
      if( PEAR::isError($res) ){
          ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
      }else{
        $Linha   = $res->fetchRow();
        $LocMat = $Linha[0];
        
        $DataGravacao = date("Y-m-d H:i:s");
      
        $ValorMedioBD  = str_replace(",",".",str_replace(".","",$ValorMedio));
        $ValorUnitarioBD  = str_replace(",",".",str_replace(".","",$ValorUnitario));
        
        $sqlUpdate  = "UPDATE SFPC.TBARMAZENAMENTOMATERIAL ";      
        $sqlUpdate .= "   SET VARMATUMED = $ValorMedioBD, ";
        $sqlUpdate .= "       VARMATULTC = $ValorUnitarioBD, ";
        $sqlUpdate .= "       CGREMPCODI = $GrupoEmp, ";
        $sqlUpdate .= "       CUSUPOCODI = $Usuario, TARMATULAT = '$DataGravacao' ";
        $sqlUpdate .= " WHERE CMATEPSEQU = $Material AND CLOCMACODI = $LocMat ";
        $sqlUpdate .= " AND (VARMATUMED <> $ValorMedioBD OR VARMATULTC <> $ValorUnitarioBD) "; //Realiza a atualização apenas se um dos valores forem distintos
                
        $res  = $db->query($sqlUpdate);
        if( PEAR::isError($res) ){
            $db->query("ROLLBACK");
            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlUpdate");
        }else{
                  
          $LinhasAlteradas = $db->affectedRows();
          
          if($LinhasAlteradas != 1){
            $db->query("ROLLBACK");
            if($Mens == 1){ $Mensagem .= ", "; }
  				  $Mens      = 1;
  				  $Tipo      = 2;
  				  $Mensagem  = "Os Valores não foram atualizados";
          } else {
            $Mens      = 1;
  				  $Tipo      = 1;
  				  $Mensagem  = "Os Valores foram atualizados com sucesso";
          }
          
        }        
      }
      
      $db->disconnect();
       
		}
}elseif($Botao == "Pesquisar"){
  $Mens     = 0;
	$Mensagem = "Informe: ";
  if(!$Almoxarifado){
    $Mens     = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CorrecaoValorMaterial.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
  }
  
  if(!$Material){
    if($Mens == 1){ $Mensagem .= ", "; }
	  $Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CorrecaoValorMaterial.Material.focus();\" class=\"titulo2\">Material</a>";
	}
  
  if($Mens == 0) {
    $db   = Conexao();  
    $sql  = "SELECT MAT.EMATEPDESC, ARM.VARMATUMED, ARM.VARMATULTC  ";
    $sql .= " FROM SFPC.TBLOCALIZACAOMATERIAL LOC, SFPC.TBARMAZENAMENTOMATERIAL ARM, SFPC.TBMATERIALPORTAL MAT ";
    $sql .= " WHERE LOC.CALMPOCODI = $Almoxarifado AND ARM.CMATEPSEQU = $Material AND LOC.CLOCMACODI = ARM.CLOCMACODI AND ARM.CMATEPSEQU = MAT.CMATEPSEQU ";
    
    $res  = $db->query($sql);
    if( PEAR::isError($res) ){
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    }else{
      $Linha   = $res->fetchRow();
      $DescMat = $Linha[0];
      $ValorMedioAtual = $Linha[1];
      $ValorUnitarioAtual = $Linha[2];   
      
      if($DescMat == null || $DescMat == ""){
        if($Mens == 1){ $Mensagem .= ", "; }
        $Mens      = 1;
        $Tipo      = 1;
        $Mensagem  = "Material Inexistente no Almoxarifado";
      }
      
    }
    
    $db->disconnect();  
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
	document.CorrecaoValorMaterial.Botao.value = valor;
	document.CorrecaoValorMaterial.submit();
}
function AbreJanelaItem(url,largura,altura){
	window.open(url,'paginaitem','status=no,scrollbars=yes,left=90,top=150,width='+largura+',height='+altura); 
}
<?php MenuAcesso(); ?>
//-->
</script>

<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CorrecaoValorMaterial.php" method="post" name="CorrecaoValorMaterial">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Correção > Valores Materiais
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if($Mens == 1){?>
	<tr>
		<td width="100"></td>
		<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,$Troca); ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
			<table border="0" cellspacing="0" cellpadding="3">
				<tr>
					<td class="textonormal">
						<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
									CORRIGIR - VALORES DOS MATERIAIS ARMAZENADOS
								</td>
							</tr>
							<tr>
								<td class="textonormal">
									<p align="justify">
										Para corrigir o valor unitário e médio de um material, informe os dados abaixo e clique no botão "Alterar". 
                    <br>Os itens obrigatórios estão com *. 
                    <br>E o separador de milhar é o ponto (.) e o de casa decimal a virgula (,). Ex: 1.000,50.
									</p>
								</td>
							</tr>
							<tr>
								<td>
									<table class="textonormal" border="0" align="left" class="caixa">
										
                    <tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Almoxarifado*</td>
											<td class="textonormal">
												<?php
												# Mostra o(s) Almoxarifado(s) de Acordo com o Usuário Logado e órgão #
												$db  = Conexao();
												if( $_SESSION['_cgrempcodi_'] == 0 ){
														$sql    = "SELECT A.CALMPOCODI, A.EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL A ";
														if($Almoxarifado){
																$sql   .= " WHERE A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A'";
														}
												}else{
														$sql    = "SELECT A.CALMPOCODI, A.EALMPODESC ";
														$sql   .= "  FROM SFPC.TBALMOXARIFADOPORTAL A, SFPC.TBALMOXARIFADOORGAO B , SFPC.TBLOCALIZACAOMATERIAL C ";
														#$sql   .= "  LEFT OUTER JOIN (SELECT * FROM SFPC.TBINVENTARIOCONTAGEM WHERE (CLOCMACODI, AINVCOANOB, AINVCOSEQU) IN ( SELECT A.CLOCMACODI, A.AINVCOANOB, MAX(A.AINVCOSEQU) AS AINVCOSEQU FROM SFPC.TBINVENTARIOCONTAGEM A WHERE (A.FINVCOFECH IS NULL OR A.FINVCOFECH = 'N') AND (A.CLOCMACODI, A.AINVCOANOB) IN ( SELECT CLOCMACODI, MAX(AINVCOANOB) FROM SFPC.TBINVENTARIOCONTAGEM GROUP BY CLOCMACODI) GROUP BY A.CLOCMACODI, A.AINVCOANOB ) ) AS D";
														$sql   .= "  LEFT OUTER JOIN (SELECT * FROM SFPC.TBINVENTARIOCONTAGEM WHERE (CLOCMACODI, AINVCOANOB, AINVCOSEQU) IN ( SELECT A.CLOCMACODI, A.AINVCOANOB, MAX(A.AINVCOSEQU) AS AINVCOSEQU FROM SFPC.TBINVENTARIOCONTAGEM A WHERE (A.FINVCOFECH = 'S') AND (A.CLOCMACODI, A.AINVCOANOB) IN ( SELECT CLOCMACODI, MAX(AINVCOANOB) FROM SFPC.TBINVENTARIOCONTAGEM GROUP BY CLOCMACODI) GROUP BY A.CLOCMACODI, A.AINVCOANOB ) ) AS D";
														$sql   .= "    ON C.CLOCMACODI = D.CLOCMACODI ";
														$sql   .= " WHERE A.CALMPOCODI = C.CALMPOCODI AND A.CALMPOCODI = B.CALMPOCODI ";
														if($Almoxarifado){
																$sql   .= " AND A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A'";
														}
														$sql .= "   AND B.CORGLICODI = ";
														$sql .= "       (SELECT DISTINCT CEN.CORGLICODI ";
														$sql .= "           FROM SFPC.TBCENTROCUSTOPORTAL CEN, SFPC.TBUSUARIOCENTROCUSTO USU ";
														$sql .= "          WHERE USU.CCENPOSEQU = CEN.CCENPOSEQU AND USU.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." AND CEN.FCENPOSITU <> 'I' AND USU.FUSUCCTIPO IN ('T','R') AND CASE WHEN USU.FUSUCCTIPO = 'T' THEN B.CALMPOCODI = USU.CALMPOCODI ELSE CEN.FCENPOSITU <> 'I' END) ";

														# Trecho com relação a data de fechamento #
														$sql .= "   AND CASE WHEN ('".date("Y-m-d")."'>='".$InventarioDataInicial."') THEN ";
														# Para que inventário seja feito no período determinado, sem passar da data final definida, descomentar a linha abaixo e comentar a posterior #
														# $sql .= "            (A.FALMPOINVE = 'N' OR A.FALMPOINVE IS NULL) AND D.TINVCOFECH >= '".$InventarioDataInicial."' AND D.TINVCOFECH <= '".$InventarioDataFinal."' ";
														$sql .= "            (A.FALMPOINVE = 'N' OR A.FALMPOINVE IS NULL) AND D.TINVCOFECH >= '".$InventarioDataInicial."' ";
														$sql .= "       ELSE ";
														$sql .= "            (A.FALMPOINVE = 'N' OR A.FALMPOINVE IS NULL) ";
														$sql .= "        END ";
														# Trecho com relação a data de hoje #
														$sql .= "   AND ( ";
														$sql .= "        TO_DATE('".date('Y-m-d')."','YYYY-MM-DD') < TO_DATE('".$InventarioDataInicial."','YYYY-MM-DD') ";
														$sql .= "        OR TO_DATE('".date('Y-m-d')."','YYYY-MM-DD') > TO_DATE('".$InventarioDataFinal."','YYYY-MM-DD') ";
														$sql .= "   ) ";
												}
												$sql .= " ORDER BY A.EALMPODESC ";                        
												$res  = $db->query($sql);
												if( PEAR::isError($res) ){
														$CodErroEmail  = $res->getCode();
														$DescErroEmail = $res->getMessage();
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
												}else{
														$Rows = $res->numRows();
														if($Rows == 1){
																$Linha = $res->fetchRow();
																$Almoxarifado = $Linha[0];
																echo "$Linha[1]<br>";
																echo "<input type=\"hidden\" name=\"Almoxarifado\" value=\"$Almoxarifado\">";
																echo $DescAlmoxarifado;
														}elseif( $Rows > 1 ){
																//echo "<select name=\"Almoxarifado\" class=\"textonormal\" onChange=\"submit();\">\n";
																echo "<select name=\"Almoxarifado\" class=\"textonormal\" >\n";
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
																echo "ALMOXARIFADO NÃO CADASTRADO, INATIVO OU SOB INVENTÁRIO";
																echo "<input type=\"hidden\" name=\"CarregaAlmoxarifado\" value=\"N\">";
														}
												}
												$db->disconnect();
												?>
											</td>
										</tr>
                    
                    <tr>
                      <td class="textonormal"  bgcolor="#DCEDF7">Material*</td>
                      <td>
                        <input type="text" name="Material" size="7" class="textonormal" value="<?php if($Material != null) {echo $Material;} else {echo "";} ?>" >
                        <a href="javascript:enviar('Pesquisar');"> <img src="../midia/lupa.gif" border="0"></a>                                        
                      </td>
                    </tr>                      
                    
                    <?php if($DescMat != null && $ValorMedioAtual != null && $ValorUnitarioAtual != null){ ?>
                      <tr>
                          <td class="textonormal"  bgcolor="#DCEDF7">Descrição do Material</td>
                          <td class="textonormal"><?php echo $DescMat;?></td>                      
                      </tr>
                      
                      <tr>
                          <td class="textonormal"  bgcolor="#DCEDF7">Valor Médio Atual</td>
                          <td class="textonormal"><?php echo converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorMedioAtual))); ?></td>
                      </tr>
                      
                      <tr>
                          <td class="textonormal"  bgcolor="#DCEDF7">Valor Unitário Atual</td>
                          <td class="textonormal"><?php echo converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorUnitarioAtual))); ?></td>
                      </tr>
                      
                      <tr>
                          <td class="textonormal"  bgcolor="#DCEDF7">NOVO Valor Médio*</td>
                          <td class="textonormal"><input type="text" name="ValorMedio" class="textonormal"></td>
                      </tr>
                      
                      <tr>
                          <td class="textonormal"  bgcolor="#DCEDF7">NOVO Valor Unitário*</td>
                          <td class="textonormal"><input type="text" name="ValorUnitario" class="textonormal"></td>
                      </tr>
                      
                    <?php } ?>
                    
                    
									</table>
								</td>
							</tr>
							<tr>
								<td class="textonormal" align="right">
									<input type="hidden" name="Botao">
                  <input type="hidden" name="DescMat" value="<?php echo $DescMat; ?>">                                    
									<input type="button" name="Incluir" value="Alterar" class="botao" onClick="javascript:enviar('Alterar');">
									<input type="button" name="Limpar" value="Limpar" class="botao" onClick="javascript:enviar('Limpar');">
								</td>
							</tr>
						</table>
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
