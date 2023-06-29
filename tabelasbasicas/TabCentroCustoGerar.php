<?php
#------------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabCentroCustoGerar.php
# Objetivo: Programa que ativa a atualização da tabela de Centro de Custo
# Autor:    Roberta Costa
# Data:     03/08/2005
# Alterado: Álvaro Faria
# Data:     15/07/2006 - Correção da formatação do retorno de linha do arquivo
#                        Indentação
# Alterado: Álvaro Faria
# Data:     02/01/2007 - Correções para mudança de ano
# Alterado: Carlos Abreu
# Data:     30/05/2007 - Inclusao da funcao set_time_limit para evitar que para a execucao por estouro de tempo
# OBS.:     Tabulação 2 espaços
#------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

set_time_limit(55555555555);

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/oracle/tabelasbasicas/RotCentroCustoGerar.php' );

$Ano = date("Y");

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao       = $_POST['Botao'];
		$UltimaData  = $_POST['UltimaData'];
		$Qtd         = $_POST['Qtd'];
		$CentroCusto = $_POST['CentroCusto'];
}else{
		$Mens        = $_GET['Mens'];
		$Tipo        = $_GET['Tipo'];
		$Mensagem    = urldecode($_GET['Mensagem']);
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if($Botao == "Gerar"){
		if($CentroCusto){
				foreach($CentroCusto as $Centro){
						if($Centros){
								$Centros .= "æ".$Centro;
						}else{
								$Centros = $Centro;
						}
				}
		}
		if($Centros){
				$Url = "tabelasbasicas/RotCentroCustoGerar.php?Centros=".urlencode($Centros);
		}else{
				$Url = "tabelasbasicas/RotCentroCustoGerar.php";
		}
		if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		//Redireciona($Url);
		//exit;
}elseif($Botao == ""){
		$db     = Conexao();
		$sql    = "SELECT MAX(TCENPOULAT) FROM SFPC.TBCENTROCUSTOPORTAL";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha = $result->fetchRow();
				if($Linha[0] == ""){
						$UltimaData = "-";
				}else{
						$UltimaData = substr($Linha[0],8,2)."/".substr($Linha[0],5,2)."/".substr($Linha[0],0,4)." ".substr($Linha[0],11,8);
				}
		}

		# Pega a quantidade de linhas da tabela #
		$sql    = "SELECT COUNT(*) FROM SFPC.TBCENTROCUSTOPORTAL WHERE ACENPOANOE = $Ano";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha = $result->fetchRow();
				$Qtd   = $Linha[0];
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
	document.TabCentroCustoGerar.Botao.value=valor;
	document.TabCentroCustoGerar.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabCentroCustoGerar.php" method="post" name="TabCentroCustoGerar">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Estoques > Centro de Custo > Gerar
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if($Mens == 1){?>
	<tr>
		<td width="150"></td>
		<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
			<table border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff">
				<tr>
					<td class="textonormal">
						<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" class="textonormal" summary="">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="2">
									GERAÇÃO DA TABELA DE CENTRO DE CUSTO
								</td>
							</tr>
							<tr>
								<td class="textonormal" colspan="2">
									<p align="justify">
										Para fazer a atualização da tabela de Centro de Custo do Portal de Compras a partir do SOFIN, clique no botão "Gerar".
									</p>
								</td>
							</tr>
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" height="20">Unidade Orçamentária*</td>
								<td class="textonormal">
									<select name="CentroCusto[]" class="textonormal" multiple size="8">
										<option value="">Selecione as Unidade Orçamentárias - Centro de Custo...</option>
										<?php 
										$db     = Conexao();
										# Mostra as Unidades Orçamentárias #
										$sql    = "SELECT CUNIDOORGA, CUNIDOCODI, EUNIDODESC ";
										$sql   .= "  FROM SFPC.TBUNIDADEORCAMENTPORTAL WHERE TUNIDOEXER = $Ano";
										$sql   .= " ORDER BY EUNIDODESC";
										$result = $db->query($sql);
										if( PEAR::isError($result) ){
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												while( $Linha = $result->fetchRow() ){
														$Orgao       = $Linha[0];
														$Unidade     = $Linha[1];
														$DescUnidade = $Linha[2];
														echo "<option value=\"".$Orgao."_".$Unidade."\">$DescUnidade</option>\n";
												}
										}
										$db->disconnect();
										?>
									</select>
								</td>
							</tr>

							<tr>
								<td colspan="2">
									<table class="textonormal" border="0" align="left" class="caixa">
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" >Última Atualização</td>
											<td class="textonormal"><?php echo $UltimaData; ?></td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" >Total de Linhas</td>
											<td class="textonormal"><?php echo $Qtd; ?></td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td class="textonormal" align="right" colspan="2">
									<input type="hidden" name="UltimaData" value="<?php echo $UltimaData; ?>">
									<input type="hidden" name="Qtd" value="<?php echo $Qtd; ?>">
									<input type="button" value="Gerar" class="botao" onclick="javascript:enviar('Gerar');">
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
