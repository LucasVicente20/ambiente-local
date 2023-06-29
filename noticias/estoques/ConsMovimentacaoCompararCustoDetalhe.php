<?php
# -----------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsMovimentacaoCompararCustoDetalhe.php
# Autor:    Álvaro Faria
# Data:     03/10/2006
# Objetivo: Programa de Informações de Centros de Custo
# OBS.:     Tabulação 2 espaços
# -----------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 73662
# -----------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao    = $_POST['Botao'];
}else{
		$Orgao    = $_GET['Orgao'];
		$Unidade  = $_GET['Unidade'];
		$RPA      = $_GET['RPA'];
		$Centro   = $_GET['Centro'];
		$Deta     = $_GET['Deta'];
		$Gasto    = $_GET['Gasto'];
		$TipoMaterial    = $_GET['TipoMaterial'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;
?>
<html>
<head>
<title>Portal de Compras - Incluir Classes</title>
<link rel="stylesheet" type="text/css" href="../estilo.css">
</head>
<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
<table cellpadding="0" border="0" summary="">
	<!-- Erro -->
	<tr>
		<td align="left" colspan="2">
			<?php if( $Mens != 0 ){ ExibeMens($Mensagem,$Tipo,1);	}?>
		</td>
	</tr>
	<!-- Fim do Erro -->
	<!-- Corpo -->
	<tr>
		<td class="textonormal">
			<table border="0" cellspacing="0" cellpadding="3" summary="" width="100%">
				<tr>
					<td class="textonormal">
						<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" width="100%" class="textonormal" bgcolor="#FFFFFF" summary="">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="3">
									INFORMAÇÕES DO CENTRO DE CUSTO
								</td>
							</tr>
							<tr>
								<td class="textonormal" colspan="3">
									<p align="justify">
										Para fechar a janela clique no no botão "Voltar".
										&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										&nbsp;&nbsp;
									</p>
								</td>
							</tr>
							<?php
							# Pega os dados do Centro de Custo de acordo com os parâmetros #
							$db   = Conexao();
							$sql  = "SELECT ECENPODESC, ECENPODETA ";
							$sql .= "  FROM SFPC.TBCENTROCUSTOPORTAL ";
							$sql .= " WHERE CCENPOCORG = $Orgao ";
							$sql .= "   AND CCENPOUNID = $Unidade ";
							$sql .= "   AND CCENPONRPA = $RPA ";
							$sql .= "   AND CCENPOCENT = $Centro ";
							$sql .= "   AND CCENPODETA = $Deta ";
							$res  = $db->query($sql);
							if( db::isError($res) ){
									ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
							}else{
									$Linha      = $res->fetchRow();
									$CentroDesc = $Linha[0];
									$DetaDesc   = $Linha[1];
							}
							$db->disconnect();
							?>
							<tr>
								<td colspan="3">
									<table border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" width="100%" summary="">
										<tr>
											<td colspan="2">
												<table class="textonormal" border="0" width="100%" summary="">
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20" width="20%">Órgão</td>
														<td class="textonormal"><?php echo $Orgao; ?></td>
													</tr>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Unidade</td>
														<td class="textonormal"><?php echo $Unidade; ?></td>
													</tr>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">RPA</td>
														<td class="textonormal"><?php echo $RPA; ?></td>
													</tr>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Centro de Custo</td>
														<td class="textonormal"><?php echo $Centro." - ".$CentroDesc; ?></td>
													</tr>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Função de Governo</td>
														<td class="textonormal"><?php echo $Deta." - ".$DetaDesc; ?></td>
													</tr>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Item de Gasto</td>
														<td class="textonormal"><?php
																if($TipoMaterial=='C'){
																	echo 'CONSUMO';
																}else if($TipoMaterial=='P'){
																	echo 'PERMANENTE';
																}
																$TipoMaterial
															?></td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td colspan="3" align="right">
									<input type="button" value="Voltar" class="botao" onclick="javascript:self.close();">
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
<script language="javascript" type="">
window.focus();
//-->
</script>
</body>
</html>
