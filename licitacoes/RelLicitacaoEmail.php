<?php
/**
 * Portal de Compras
 * 
 * Programa: RelLicitacaoEmail.php
 * Autor:    Ariston Cordeiro
 * Data:     12/05/2022
 * Objetivo: Programa de relatório de envio de email
 * OBS:      Tabulação 2 espaços
 * ----------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     28/12/2022
 * Tareca:   276782
 * ----------------------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "GET") {
	$Processo = $_GET['Processo'];
	$Ano 	  = $_GET['Ano'];
	$Comissao = $_GET['Comissao'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "RelLicitacaoEmail.php";

# Função que recebe os dados de uma tabela de log em String e retorna um array com os dados de cada campo
function explodirValores($strValores) {
	global $SimboloConcatenacaoArray;

	$Valores       = substr($strValores, 0, -1); //remove o fecha parenteses
	$Valores       = substr($Valores, 1); //remove o abre parenteses
	$tamanhoString = strlen($Valores);
	$disable       = FALSE;

	for ($itf=0;$itf<$tamanhoString;$itf++) {
		if ($Valores[$itf]=="\"") {
			if ($disable) {
				$disable = FALSE;
			} else {
				$disable = TRUE;
			}

		}elseif ($Valores[$itf]=="," and !$disable) {
			//marcar virgulas que definem separação de valores
			$Valores[$itf] = $SimboloConcatenacaoArray;
		}
	}

	$Valores = explode($SimboloConcatenacaoArray,$Valores); //remove o fecha parenteses

	return $Valores;
}

$db   = Conexao();

$sql = "SELECT CLICPOCODL, ALICPOANOL, CORGLICODI, CGREMPCODI
		FROM   SFPC.TBLICITACAOPORTAL
		WHERE  CLICPOPROC = $Processo
		       AND ALICPOANOP = $Ano
			   AND CCOMLICODI = $Comissao ";

$result = $db->query($sql);

if (db::isError($result)) {
	EmailErroSQL("Erro no SQL", __FILE__, __LINE__, "Erro no SQL", $sql, $result);
}

$Linha = $result->fetchRow();

$Licitacao    = $Linha[0];
$AnoLicitacao = $Linha[1];
$Orgao        = $Linha[2];
$Grupo        = $Linha[3];

$sql = "SELECT min(DLICEMULAT)
	    FROM SFPC.TBLICITACAOEMAIL ";

$result = $db->query($sql);

if (db::isError($result)) {
	EmailErroSQL("Erro no SQL", __FILE__, __LINE__, "Erro no SQL", $sql, $result);
}

$Linha = $result->fetchRow();

$dataInicioLog = $Linha[0];
?>

<html>
<body marginwidth="0" marginheight="0">
	<link rel="stylesheet" type="text/css" href="../estilo.css">
	<form action="RelLicitacaoEmail.php" method="post" name="Relatorio">
		<p class="titulo3" align="center">
  		Prefeitura da Cidade do Recife<br><br>
  		RELATÓRIO DE ENVIO DE EMAILS*<br><br>
  		<a href="javascript:Fecha()"><img src="../midia/brasao.jpg" width="50" height="40" border="0"></a>
		<p class="titulo3" align="right">
			Data: <?php echo date("d/m/Y H:i"); ?>
		</p>
     	<f class="textonormal">*Este relatório apenas exibe e-mails registrados desde o dia <?php echo DataBarra($dataInicioLog) ?>, e que foram enviados através do sistema Portal de Compras.</f>
		<hr/>
		<table border="0" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
			<tr>
  				<td class="textonormal" >
  					<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
    					<tr>
      						<td class="textonormal" bgcolor="#DCEDF7" width="100">Comissão</td>
      						<?php
          					$sql = "SELECT ECOMLIDESC FROM SFPC.TBCOMISSAOLICITACAO WHERE CCOMLICODI = $Comissao ";

							$result = $db->query($sql);

							if (db::isError($result)) {
						    	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
							}
							?>
          					<td class="textonormal"><?php while ($Linha = $result->fetchRow()) { echo $Linha[0]; } ?></td>
  						</tr>
    					<tr>
      						<td class="textonormal" bgcolor="#DCEDF7">Processo</td>
          					<td class="textonormal"><?php echo substr($Processo + 10000,1);?>/<?=$Ano?></td>
  						</tr>
    					<tr>
      						<td class="textonormal" bgcolor="#DCEDF7">Licitação</td>
          					<td class="textonormal"><?php echo substr($Licitacao + 10000,1);?>/<?=$AnoLicitacao?></td>
  						</tr>
  						<?php
						$sql = "SELECT	XLICEMTITL, XLICEMBODY, DLICEMULAT, CLICEMCODI
								FROM   	SFPC.TBLICITACAOEMAIL
								WHERE  	CLICPOPROC = $Processo
								       	AND ALICPOANOP = $Ano
										AND CGREMPCODI = $Grupo
										AND CCOMLICODI = $Comissao
										AND CORGLICODI = $Orgao
								ORDER BY DLICEMULAT ";

						$result = $db->query($sql);

						if (db::isError($result)) {
							EmailErroSQL("Erro no SQL", __FILE__, __LINE__, "Erro no SQL", $sql, $result);
						}

						$Rows = $result->numRows();
						$noEmails = 0;

						while ($Linha = $result->fetchRow()) {
							$Titulo = $Linha[0];
							$Corpo  = $Linha[1];
							$Data   = $Linha[2];
							$Email  = $Linha[3];

							$Participantes   = "";
							$noParticipantes = 0;

							$sql = "SELECT	DISTINCT T2.ELISOLMAIL, T2.ELISOLNOME, T2.CLISOLCNPJ, T2.CLISOLCCPF 
									FROM	SFPC.TBLICITACAOEMAILSOLICITANTE T 
											LEFT JOIN SFPC.TBLISTASOLICITAN T2 ON
												T.CLICPOPROC = T2.CLICPOPROC
												AND T.ALICPOANOP = T2.ALICPOANOP
												AND T.CGREMPCODI = T2.CGREMPCODI
												AND T.CCOMLICODI = T2.CCOMLICODI
												AND T.CORGLICODI = T2.CORGLICODI
												AND T.CLISOLCODI = T2.CLISOLCODI 
									WHERE	T.CLICPOPROC = $Processo
											AND T.ALICPOANOP = $Ano
											AND T.CGREMPCODI = $Grupo
											AND T.CCOMLICODI = $Comissao
											AND T.CORGLICODI = $Orgao ";

							$result2 = $db->query($sql);

							if (db::isError($result2)) {
								EmailErroSQL("Erro no SQL", __FILE__, __LINE__, "Erro no SQL", $sql, $result2);
							}

							$Rows2 = $result2->numRows();

							while ($Linha2 = $result2->fetchRow()) {
								$noParticipantes++;
							 	$Participantes .= "<tr><td>".$Linha2[0]."</td></tr>";
							}

							if ($noParticipantes > 0) {
								$noEmails++;
								?>
						  		<tr>
									<td class='titulo3' colspan='6' bgcolor='#DCEDF7' colspan='2'>Informações referentes a email enviado dia <?=DataBarra($Data)?> hora <?=Hora($Data)?></td>
								</tr>
			      				<tr>
			      					<td class="textonormal" bgcolor="#DCEDF7">Título</td>
			      	  				<td><pre><?=$Titulo?></pre></td>
			      				</tr>
			      				<tr>
			      					<td class="textonormal" bgcolor="#DCEDF7">Conteúdo</td>
			      	  				<td><pre><?=$Corpo?></pre></td>
			      				</tr>
			      				<tr>
			      					<td class="textonormal" bgcolor="#DCEDF7">Participantes</td>
			      	  				<td style="padding:0px; ">
			      	  					<table border="1" cellpadding="3" cellspacing="0" bordercolor="#fefefe" summary="" class="textonormal" width="100%">
			      	  						<tr>
			      	  							<?=$Participantes?>
			      	  						</tr>
			      	  					</table>
			      	  				</td>
			      				</tr>
								<?php
							}
						}

						$db->disconnect();

						if ($noEmails == 0) {
							?>
			      			<tr>
			      				<td class="textonormal" colspan="2">Nenhum e-mail foi registrado para esta licitação.</td>
			      			</tr>
							<?php
						}
  						?>
      				</table>
	  			</td>
			</tr>
		</table>
	</form>
</body>
</html>

<script language="javascript">
<!--
self.print();
function Fecha(){
	window.close();
}
//-->
</script>