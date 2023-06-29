<?php
/**
 * Portal de Compras
 * 
 * Programa: calendario.php
 * Autor:    Roberta Costa
 * Data:     15/06/2004
 * ---------------------------------------------------------------------------------------------
 * Alterado: Álvaro Faria
 * Data:     12/09/2006 - Indentação
 * Objetivo: Programa de inclusão de projeto
 * ---------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     25/07/2018
 * Objetivo: Tarefa Redmine 194740
 * ---------------------------------------------------------------------------------------------
 */

if ($_SERVER['REQUEST_METHOD'] == "GET") {
	$Formulario = $_GET['Formulario'];
	$Campo      = $_GET['Campo'];
	$Data       = $_GET['Data'];
	$Acao       = $_GET['Acao'];
}
?>
<html>
<link rel="stylesheet" type="text/css" href="estilo.css">
<body>
	<?php
	if ($Data == "") {
		$Data = date("d/m/Y");
	}

	$Data = explode("/",$Data);
	$Dia = $Data[0];
	$Mes = $Data[1];
	$Ano = $Data[2];

	$MesVetor = array("JANEIRO","FEVEREIRO","MAR&CcedilO","ABRIL","MAIO","JUNHO","JULHO","AGOSTO","SETEMBRO","OUTUBRO","NOVEMBRO","DEZEMBRO");

	// Pega dia da semana
	$DiaSemanaIni = date("w",strtotime("$Mes/01/$Ano"));

	// Dias dos Meses
	$Dias1 = array("31","28","31","30","31","30","31","31","30","31","30","31");
	$Dias2 = array("31","29","31","30","31","30","31","31","30","31","30","31");

	$I = $Mes - 1;
	
	if ($Ano%4 == 0) { 
		$FimMes = $Dias2[$I];
	} else {
		$FimMes = $Dias1[$I];
	}

	// Gera a data do mês anterior
	$MesAnt = $Mes - 1;
	
	if ($MesAnt == 0) {
		$MesAnt = 12;
		$AnoAnt = $Ano - 1;
	} else {
		$Tam = strlen($MesAnt);
		
		if ($Tam == 1) {
			$MesAnt = "0$MesAnt";
		}

		$AnoAnt = $Ano;
	}

	$DataAnterior  = "01/".$MesAnt."/".$AnoAnt;

	// Gera a data do mês posterior
	$MesPos = $Mes + 1;
	
	if ($MesPos == 13) {
		$MesPos = "01";
		$AnoPos = $Ano + 1;
	} else {
		$Tam = strlen($MesPos);

		if ($Tam == 1) {
			$MesPos = "0$MesPos";
		}

		$AnoPos = $Ano;
	}

	$DataPosterior = "01/".$MesPos."/".$AnoPos;
	?>
	<table>
		<tr>
			<td class="titulotabela" colspan="7" height="24" valign="center" align="center" background="midia/bg_cabecalho_tabela.gif">
				<a href=calendario.php?Formulario=<?php echo $Formulario;?>&Campo=<?php echo $Campo;?>&Data=<?php echo $DataAnterior ?>&Acao=<?php echo $Acao;?>><img src="midia/setacal_esq.gif" border="0"></a>
				&nbsp;&nbsp;&nbsp;<?php echo $MesVetor[$Mes-1]." - ".$Ano;?>&nbsp;&nbsp;&nbsp;
				<a href=calendario.php?Formulario=<?php echo $Formulario;?>&Campo=<?php echo $Campo;?>&Data=<?php echo $DataPosterior?>&Acao=<?php echo $Acao;?>><img src="midia/setacal_dir.gif" border="0"></a>
			</td>
		</tr>
		<tr>
			<td bgcolor="e7e7e7" class="subtitulotabela" align="right" width="23">dom</td>
			<td bgcolor="e7e7e7" class="subtitulotabela" align="right" width="23">seg</td>
			<td bgcolor="e7e7e7" class="subtitulotabela" align="right" width="23">ter</td>
			<td bgcolor="e7e7e7" class="subtitulotabela" align="right" width="23">qua</td>
			<td bgcolor="e7e7e7" class="subtitulotabela" align="right" width="23">qui</td>
			<td bgcolor="e7e7e7" class="subtitulotabela" align="right" width="23">sex</td>
			<td bgcolor="e7e7e7" class="subtitulotabela" align="right" width="23">sab</td>
		</tr>
		<tr>
			<?php
			$DiaPos = 1;

			while ($DiaPos <= $FimMes+$DiaSemanaIni) {
				if ($DiaPos <= $DiaSemanaIni) {
					echo "<td></td>\n";
				} else {
					$Soma = $DiaPos - $DiaSemanaIni;

					if (strlen($Soma) == 1) {
						echo "<td align=\"center\" bgcolor=\"DCEDF7\" class=\"textonormal\"><a href=\"javascript:retorna('0$Soma/$Mes/$Ano');\" class=\"textonormal\">0$Soma</a></td>\n";
					} else {
						echo "<td align=\"center\" bgcolor=\"DCEDF7\" class=\"textonormal\"><a href=\"javascript:retorna('$Soma/$Mes/$Ano');\" class=\"textonormal\">$Soma</a></td>\n";
					}
				}

				if (($DiaPos) % 7 == 0) {
					echo "</tr><tr>\n";
				}

				$DiaPos = $DiaPos + 1;
			}
			?>
		</tr>
	</table>
</body>
</html>
<script language="JavaScript">
	<!--
	window.focus();

	function retorna(Data) {
		opener.document.<?php echo $Formulario; ?>.<?php echo $Campo; ?>.value=Data;
		<?php
		if ($Acao == "S") {
			echo "opener.document.$Formulario.submit();\n";
		}
		?>
		window.close();
	}
	//-->
</script>