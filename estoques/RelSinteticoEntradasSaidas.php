<?php
#------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelSinteticoEntradasSaidas.php
# Objetivo: Relatório que mostra as entradas e saídas num mês, de forma sintética
# Autor:    Álvaro Faria
# Data:     05/09/2006
# Alterado: Álvaro Faria
# Data:     25/09/2006 - Pesquisa por Ano/Mês
# Alterado: Álvaro Faria
# Data:     03/10/2006 - Identação / Padronização do cabeçalho
# Alterado: Carlos Abreu
# Data:     04/06/2007 - Filtro no combo do almoxarifado para que quando usuario for do tipo 
#                        atendimento apareça apenas o almox. que ele esteja relacionado
# Alterado: Rodrigo Melo
# Data:     29/11/2007 - Alteração para exibir o mês de dezembro e o mês corrente (para testes)
# Alterado: Álvaro Faria
# Data:     21/12/2007 - Chama RelSinteticoEntradasSaidasAntPdf.php ou RelSinteticoEntradasSaidasPdf.php de acordo com o ano (2008)
# Alterado: Rodrigo Melo
# Data:     07/08/2008 - Alterado para remover o checkbox de acompanhamento contábil.
# OBS.:     Tabulação 2 espaços
#           Quando o inventário for aberto e fechado no mesmo mês e não houverem movimentações neste mês, o total do mês 
#           baterá com o relatório de fechamento de inventário. Porém, se houver movimentações no mês do 
#           inventário terá que ser acrescido ao valor do fechamento de inventário, estas movimentações
#------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/RelSinteticoEntradasSaidasPdf.php' );
AddMenuAcesso( '/estoques/RelSinteticoEntradasSaidasAntes2008Pdf.php' );
AddMenuAcesso( '/estoques/RelAcompContabilSinteticoEntradasSaidasPdf.php' );


# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao                   = $_POST['Botao'];
		$Almoxarifado            = $_POST['Almoxarifado'];
		$CarregaAlmoxarifado     = $_POST['CarregaAlmoxarifado'];
		$DataAtual               = $_POST['DataAtual'];    
}else{
		$Mensagem                = urldecode($_GET['Mensagem']);
		$Mens                    = $_GET['Mens'];
		$Tipo                    = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Monta a data Invertida(aaaa-mm-dd) qunado a Data vem dd/mm/aaaa - Roberta #
function DataInvertidaInt($DataInvertida){
	$DataInvertida = substr($DataInvertida,6,4).substr($DataInvertida,3,2).substr($DataInvertida,0,2);
	return $DataInvertida;
}


if($Botao == "Limpar"){
		header("location: RelSinteticoEntradasSaidas.php");
		exit;
}elseif($Botao == "Imprimir"){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( ($Almoxarifado == "") && ($CarregaAlmoxarifado == 'N') ){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Almoxarifado";
		}elseif($Almoxarifado == ""){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RelSinteticoEntradasSaidas.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		}
		if($Mens == 0 and $Botao == "Imprimir"){
        $Url = null;
        if(DataInvertidaInt($DataAtual) <= 20080101){
          $Url = "RelSinteticoEntradasSaidasAntes2008Pdf.php?Almoxarifado=$Almoxarifado&DataAtual=$DataAtual&".mktime();
        }else{
          $Url = "RelSinteticoEntradasSaidasPdf.php?Almoxarifado=$Almoxarifado&DataAtual=$DataAtual&".mktime();
        }
        
        if($Url != null){         
  				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
  				header("location: ".$Url);
  				exit;
        }
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
function enviar(valor){
	document.RelSinteticoEntradasSaidas.Botao.value=valor;
	document.RelSinteticoEntradasSaidas.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RelSinteticoEntradasSaidas.php" method="post" name="RelSinteticoEntradasSaidas">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Relatórios > Saldo Inicial Almoxarifado
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
		<td width="100"></td>
		<td align="left" colspan="2">
			<?php if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,1); } ?>
		</td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
			<table  border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
				<tr>
					<td class="textonormal">
						<table border="1" cellpadding="3" cellspacing="0"  bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" colspan="6" class="titulo3">
									RELATÓRIO ENTRADAS E SAÍDAS SINTÉTICO
								</td>
							</tr>
							<tr>
								<td class="textonormal" colspan="6">
									<p align="justify">
										Para imprimir os dados do Relatório de Entradas e Saídas Sintético, selecione um Almoxarifado, o Ano e o Mês e clique em "Imprimir".
										Para limpar os campos, clique no botão "Limpar".<br><br>
										<?php /* A data de referência para este relatório é o último dia do mês anterior ao atual.<BR><BR> */?>
										Se você não possui o Acrobat Reader, clique <a href="javascript:janela('../pdf.php','Relatorio',400,400,1,0)" class="titulo2">AQUI</a> para fazer o download.
									</p>
								</td>
							</tr>
							<tr>
								<td class="textonormal" colspan="6">
									<table class="textonormal" border="0" align="left" summary="" width="100%">
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Almoxarifado*</td>
											<td class="textonormal">
												<?php
												# Mostra o(s) Almoxarifado(s) de Acordo com o Usuário Logado #
												$db = Conexao();
												if($_SESSION['_cgrempcodi_'] == 0 or $_SESSION['_fperficorp_'] == 'S'){
														$sql    = "SELECT A.CALMPOCODI, A.EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL A ";
														if($Almoxarifado){
																$sql   .= " WHERE A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A'";
														}
												}else{
														$sql    = "SELECT A.CALMPOCODI, A.EALMPODESC ";
														$sql   .= "  FROM SFPC.TBALMOXARIFADOPORTAL A, SFPC.TBALMOXARIFADOORGAO B ";
														$sql   .= " WHERE A.CALMPOCODI = B.CALMPOCODI ";
														if ($Almoxarifado) {
																$sql   .= " AND A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A'";
														}
														$sql .= "   AND B.CORGLICODI = ";
														$sql .= "       ( SELECT DISTINCT CEN.CORGLICODI ";
														$sql .= "           FROM SFPC.TBCENTROCUSTOPORTAL CEN, SFPC.TBUSUARIOCENTROCUSTO USU ";
														$sql .= "          WHERE USU.CCENPOSEQU = CEN.CCENPOSEQU AND USU.CUSUPOCODI = ". $_SESSION['_cusupocodi_'] ." AND USU.FUSUCCTIPO IN ('T','R') ";
														
														# restringir almoxarifado quando requisitante
														$sql .= "            AND CASE WHEN USU.FUSUCCTIPO = 'T' THEN B.CALMPOCODI = USU.CALMPOCODI ELSE CEN.FCENPOSITU <> 'I' END";
														
														$sql .= "       ) ";
												}
												$sql .= " ORDER BY A.EALMPODESC ";
												$res  = $db->query($sql);
												if(PEAR::isError($res)){
														EnviaErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Rows = $res->numRows();
														if($Rows == 1){
																$Linha = $res->fetchRow();
																$Almoxarifado = $Linha[0];
																echo "$Linha[1]<br>";
																echo "<input type=\"hidden\" name=\"Almoxarifado\" value=\"$Almoxarifado\">";
																echo $DescAlmoxarifado;
														}elseif( $Rows > 1 ){
																echo "<select name=\"Almoxarifado\" class=\"textonormal\">\n";
																echo "	<option value=\"\">Selecione um Almoxarifado...</option>\n";
																for($i=0;$i< $Rows; $i++){
																		$Linha = $res->fetchRow();
																		$DescAlmoxarifado = $Linha[1];
																		if($Linha[0] == $Almoxarifado){
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
											<td class="textonormal"  bgcolor="#DCEDF7">Mês/Ano*</td>
											<td class="textonormal" >
												<select name="DataAtual" class="textonormal">
                        <!--</select>-->
													<?php
													$Meses = array("JANEIRO","FEVEREIRO","MARÇO","ABRIL","MAIO","JUNHO","JULHO","AGOSTO","SETEMBRO","OUTUBRO","NOVEMBRO","DEZEMBRO");
													$DataAtual    = "01/".date("m/Y");
													//$DataAtualMk  = mktime(0,0,0,date("m"),1,date("Y")); # Dia 1.º do mês atual                          ORIGINAL
                          $DataAtualMk  = mktime(0,0,0,date("m") + 1,1,date("Y")); # Dia 1.º do mês posterior ao atual - APENAS PARA TESTES REMOVER
													$DataMinima   = "01/05/2006"; # Mês/Ano Inicial
													$DataMinimaMk = mktime(0,0,0,5,1,2006);
													$DataLoopMk   = $DataAtualMk;
                          //echo "ANTES: ".date("d/m/Y",$DataLoopMk);
                          //echo "<BR>";
													while($DataLoopMk >= $DataMinimaMk){
															$DataValue = date("d/m/Y",$DataLoopMk);
                                                         
															//if(date("m",$DataAtualMk) == 1){
                              if(date("m",$DataLoopMk) == 1){
																	$DataView = (date("Y",$DataLoopMk) - 1)."/".$Meses[11];
															}else{                                
																	$DataView = date("Y",$DataLoopMk)."/".$Meses[date("m",$DataLoopMk)-2];
                                  //$DataView = date("Y",$DataLoopMk)."/".$Meses[date("m",$DataLoopMk)-1];                                
															}
															echo "<option value=\"".$DataValue."\">$DataView</option>\n";
															$DataLoopMk = mktime(0,0,0,date("m",$DataLoopMk)-1,1,date("Y",$DataLoopMk));
                              //echo "DataValue: ".date("d/m/Y",$DataValue)." - DataLoopMk: ".date("d/m/Y",$DataLoopMk);
                              //echo "<br>";   
													}
                          //exit;
													?>
												</select>
											</td>
										</tr>                    
									</table>
								</td>
							</tr>
							<tr>
								<td align="right" colspan="6">
									<input type="button" name="Imprimir" value="Imprimir" class="botao" onclick="javascript:enviar('Imprimir');">
									<input type="button" name="Limpar" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
									<input type="hidden" name="Botao" value="">
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
