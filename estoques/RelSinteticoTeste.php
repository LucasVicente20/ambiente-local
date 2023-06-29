<?php
#------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelSinteticoEntradasSaidasPdf.php
# Autor:    Ávaro Faria
# Data:     12/09/2006
# Alterado: Ávaro Faria
# Data:     25/09/2006 - Pesquisa por Ano/Mê# Objetivo: Programa de impressãdo resumo de estoque e movimentaçs do mêanterior.
#           No caso das movimentaçs que foram criadas no mêanterior ao de referêia
#           e modificadas no mêde referêia, o sistema irápresentar compensando para
#           mais ou para menos no mêde referêia. Podendo para isso, gerar e
#           apresentar valores negativos. Isso tambépode gerar relatós com totais
#           diferentes entre este (sintéco) e o RelEntradaNotaFiscal e o
#           Relató de Movimentaç por Tipo (Saí por requisiç)

#           A coluna Entradas Aquisiçs exibe o total de movimentaçs de nota fiscal
#           influenciadas por cancelamentos e alteraçs de nota fiscal de meses anteriores
#           no mêde referêia, podendo exibir valores negativos

#           A coluna Saís Requisiçs mostra as saís no mêde referêia e sofre
#           influêia de cancelamentos ou alteraçs de requisiçs de meses anteriores.
#           Podendo inclusive mostrar valores negativos.
#           Desta forma, a informaç desta coluna, pode nãcoincidir com o
#           "TOTAL SAÍA POR REQUISIÇO" do relató de
#           "Relató de Movimentaç por Tipo", que mostra apenas o acontecido no mê
#           Para se chegar a diferençentre estes valores, rodar o seguinte select:
#           SELECT (Cancelamento + EntradaAcerto - SaidaAcerto) FROM 
#          (SELECT CASE WHEN SUM(vmovmavalo*amovmaqtdm) IS NULL THEN 0 ELSE SUM(vmovmavalo*amovmaqtdm) END AS Cancelamento FROM tbmovimentacaomaterial WHERE calmpocodi = $Almoxarifado AND ctipmvcodi = 18 AND dmovmamovi >= '$ANO-12-01' AND dmovmamovi < '$ANO-12-$ULTIMODIA') AS Cancelamento,
#          (SELECT CASE WHEN SUM(vmovmavalo*amovmaqtdm) IS NULL THEN 0 ELSE SUM(vmovmavalo*amovmaqtdm) END AS EntradaAcerto FROM tbmovimentacaomaterial WHERE calmpocodi = $Almoxarifado AND ctipmvcodi = 19 AND dmovmamovi >= '$ANO-12-01' AND dmovmamovi < '$ANO-12-$ULTIMODIA') AS EntradaAcerto,
#          (SELECT CASE WHEN SUM(vmovmavalo*amovmaqtdm) IS NULL THEN 0 ELSE SUM(vmovmavalo*amovmaqtdm) END AS SaidaAcerto FROM tbmovimentacaomaterial WHERE calmpocodi = $Almoxarifado AND ctipmvcodi = 20 AND dmovmamovi >= '$ANO-12-01' AND dmovmamovi < '$ANO-12-$ULTIMODIA') AS SaidaAcerto
#           OBS: Substituir as variáis $Almoxarifado, $ANO e $ULTIMODIA por valores

#           A coluna Outras (Entradas ou Saís) exibe movimentaçs extras, como
#           empréimos, doaçs, trocas, avarias e etc. Esta coluna nãénfluenciada
#           por movimentaçs de meses anteriores, apenas quando a próa movimentaç
#           élterada atravédo programa CadMovimentacaoManter.php

# Alterado: Carlos Abreu
# Data:     09/05/2007 - Ajuste no sql para padronizaç de relatós
# Alterado: Carlos Abreu
# Data:     01/10/2007 - Ajustes na query e programacao para evitar estouro de memoria na execuç

# OBS.:     Tabulaç 2 espaç
#           Quando o inventáo for aberto e fechado no mesmo mêe nãhouverem movimentaçs neste mê o total do mê
#           bateráom o relató de fechamento de inventáo. Poré se houver movimentaçs no mêdo 
#           inventáo teráue ser acrescido ao valor do fechamento de inventáo, estas movimentaçs
#------------------------------------------------------------------------------------

# Acesso ao arquivo de funçs #
include "../funcoes.php";

session_cache_limiter('private_no_expire');

# Executa o controle de seguranç#
session_start();
//Seguranca();

# Variáis com o global off #
if($_SERVER['REQUEST_METHOD'] == "GET"){
		$Almoxarifado = $_GET['Almoxarifado'];
		$DataAtual    = $_GET['DataAtual'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Funç exibe o Cabeçho e o Rodapé
CabecalhoRodapePaisagem();

# Informa o Tílo do Relató #
$TituloRelatorio = "Relató Sintéco de Entradas e Saís";

# Cria o objeto PDF, o Default éormato Retrato, A4  e a medida em milítros #
$pdf = new PDF("L","mm","A4");

# Define um apelido para o nú total de pánas #
$pdf->AliasNbPages();

# Define as cores do preenchimentos que serãusados #
$pdf->SetFillColor(220,220,220);

# Adiciona uma pána no documento #
$pdf->AddPage();

# Seta as fontes que serãusadas na impressãde strings #
$pdf->SetFont("Arial","",9);

# DEFININDO A DATA DA PESQUISA #
# Descobre a data atual #
if(!$DataAtual) $DataAtual = date("d/m/Y");
$DataAtual   = explode("/",$DataAtual);
$DiaAtual    = $DataAtual[0];
$MesAtual    = $DataAtual[1];
$AnoAtual    = $DataAtual[2];
if($MesAtual == 1){      # Se o mêfor janeiro, a ano inicial e final de pesquisa será anterior
		$AnoPesquisaF = $AnoAtual - 1;
		$MesPesquisaF = 12;
		$AnoPesquisaI = $AnoAtual - 1;
		$MesPesquisaI = 11;
		$Mes          = "DEZEMBRO";
}elseif($MesAtual == 2){ # Se o mêfor fevereiro, a ano inicial de pesquisa será anterior
		$AnoPesquisaF = $AnoAtual;
		$MesPesquisaF = 1;
		$AnoPesquisaI = $AnoAtual - 1;
		$MesPesquisaI = 12;
		$Mes          = "JANEIRO";
}else{
		$AnoPesquisaF = $AnoAtual;
		$MesPesquisaF = $MesAtual - 1;
		$AnoPesquisaI = $AnoAtual;
		$MesPesquisaI = $MesAtual - 2;
		$Meses = array("JANEIRO","FEVEREIRO","MARÇ","ABRIL","MAIO","JUNHO","JULHO","AGOSTO","SETEMBRO","OUTUBRO","NOVEMBRO","DEZEMBRO");
		$Mes          = $Meses[$MesPesquisaF-1];
}
# Descobre os ús dias dos meses #
$Dias1       = array("31","28","31","30","31","30","31","31","30","31","30","31");
$Dias2       = array("31","29","31","30","31","30","31","31","30","31","30","31");
if($AnoPesquisaI%4 == 0 ){
		$DiaPesquisaI = $Dias2[$MesPesquisaI-1];
}else{
		$DiaPesquisaI = $Dias1[$MesPesquisaI-1];
}
if($AnoPesquisaF%4 == 0 ){
		$DiaPesquisaF = $Dias2[$MesPesquisaF-1];
}else{
		$DiaPesquisaF = $Dias1[$MesPesquisaF-1];
}
$DataPesquisaI = sprintf("%02d",$DiaPesquisaI)."/".sprintf("%02d",$MesPesquisaI)."/".sprintf("%04d",$AnoPesquisaI);
$DataPesquisaF = sprintf("%02d",$DiaPesquisaF)."/".sprintf("%02d",$MesPesquisaF)."/".sprintf("%04d",$AnoPesquisaF);

# FUNÇES DE RESGATE DE INFORMAÇES #
# Resgata o estoque em uma data definida, com cáulos no PHP #
function EstoqueData($Almoxarifado, $TipoMaterial, $DataPesquisa, $db){
		# Converte a data para o formato de pesquisa no banco de dados
		if($DataPesquisa) $DataPesquisa = DataInvertida($DataPesquisa);
		# Resgata os itens do estoque #
		$sql  = " SELECT MAT.CMATEPSEQU, ARM.AARMATQTDE, ";  # Alterar
		$sql .= "        AARMATQTDE + SUM(CASE WHEN FTIPMVTIPO = 'S' THEN AMOVMAQTDM ELSE CASE WHEN FTIPMVTIPO = 'E' THEN -AMOVMAQTDM ELSE 0 END END ) AS QTD ";
		$sql .= "   FROM SFPC.TBMATERIALPORTAL MAT, ";
		$sql .= "        SFPC.TBSUBCLASSEMATERIAL SUB, SFPC.TBGRUPOMATERIALSERVICO GRU, ";
		$sql .= "        SFPC.TBLOCALIZACAOMATERIAL LOC, SFPC.TBARMAZENAMENTOMATERIAL ARM ";
		$sql .= "   LEFT OUTER JOIN SFPC.TBMOVIMENTACAOMATERIAL MOV ";
		$sql .= "     ON ARM.CMATEPSEQU = MOV.CMATEPSEQU ";
		$sql .= "    AND MOV.CALMPOCODI = $Almoxarifado ";
		$sql .= "    AND MOV.TMOVMAULAT > '$DataPesquisa 23:59:59' ";
		$sql .= "    AND (MOV.FMOVMASITU IS NULL OR MOV.FMOVMASITU = 'A') ";
		$sql .= "   LEFT OUTER JOIN SFPC.TBTIPOMOVIMENTACAO TIP ";
		$sql .= "     ON MOV.CTIPMVCODI = TIP.CTIPMVCODI ";
		$sql .= "  WHERE MAT.CMATEPSEQU = ARM.CMATEPSEQU ";
		$sql .= "    AND MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
		$sql .= "    AND SUB.CGRUMSCODI = GRU.CGRUMSCODI ";
		$sql .= "    AND LOC.CLOCMACODI = ARM.CLOCMACODI ";
		$sql .= "    AND LOC.CALMPOCODI = $Almoxarifado ";
		$sql .= "    AND GRU.FGRUMSTIPM = '$TipoMaterial' ";
		$sql .= " GROUP BY MAT.CMATEPSEQU, ARM.AARMATQTDE, ARM.VARMATUMED ";
		$sql .= " ORDER BY MAT.CMATEPSEQU ";
		$res  = $db->query($sql);
		if( PEAR::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sql");
		}else{
				# Linhas de Itens de Material #
				$rows = $res->numRows();
				if($rows == 0){
						$Totalizador = 0;
				}else{
						for($i=0; $i< $rows; $i++){
								$Linha = $res->fetchRow();
								$CodigoReduzido[$i] = $Linha[0];
								$QtdEstoque[$i]     = $Linha[1]; # Alterar
								$QuantMov[$i]       = $Linha[2];
								# Montando o array de Itens do Estoque #

								# Descobre o valor unitáo para o material na data especificada #
								$sqlvalor  = " SELECT VMOVMAUMED FROM SFPC.TBMOVIMENTACAOMATERIAL ";
								$sqlvalor .= "  WHERE CALMPOCODI = $Almoxarifado ";
								$sqlvalor .= "    AND CMATEPSEQU = ".$CodigoReduzido[$i]." ";
								$sqlvalor .= "    AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
								$sqlvalor .= "    AND TMOVMAULAT = ";
								$sqlvalor .= "        (SELECT MAX(TMOVMAULAT) FROM SFPC.TBMOVIMENTACAOMATERIAL ";
								$sqlvalor .= "          WHERE CALMPOCODI = $Almoxarifado ";
								$sqlvalor .= "            AND CMATEPSEQU = ".$CodigoReduzido[$i]." ";
								$sqlvalor .= "            AND TMOVMAULAT <= '$DataPesquisa 23:59:59' ";
								$sqlvalor .= "            AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') )";
								//$sqlvalor .= " ORDER BY AMOVMAANOM DESC, CMOVMACODI DESC"; # Alterar
								$resvalor    = $db->query($sqlvalor);
								if( PEAR::isError($resvalor) ){
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlvalor");
								}else{
										$LinhaValor              = $resvalor->fetchRow();
										$ValorUnitarioPesquisado = $LinhaValor[0];
								}

								$QtdTotal        = $QuantMov[$i];
								$Totalizador     = $Totalizador + ($QtdTotal * $ValorUnitarioPesquisado);
						}
				}
		}
		return $Totalizador;
}


# Resgata movimentaçs para exibiç e cáulo de estoque em datas especícas #
function Movimentacoes($Almoxarifado, $TipoMov, $TipoMaterial, $DataPesquisa1, $DataPesquisa2, $Movimentacoes, $Operacao, $db){
		# Converte a data para o formato de pesquisa no banco de dados
		if($DataPesquisa1) $DataPesquisa1 = DataInvertida($DataPesquisa1);
		if($DataPesquisa2) $DataPesquisa2 = DataInvertida($DataPesquisa2);
		$sql  = " SELECT SUM(MOV.AMOVMAQTDM";
		$sql .= "        * ";
		$sql .= "        CASE WHEN MOV.CTIPMVCODI IN (3,7,8) THEN (MOV.VMOVMAVALO)";
		$sql .= "        ELSE (MOV.VMOVMAUMED) END )";

		/*$sql .= "        CASE WHEN MOV.CTIPMVCODI IN (3,7,8) THEN (MOV.VMOVMAVALO)";
		$sql .= "        ELSE ";
		$sql .= "        (SELECT VMOVMAVALO FROM SFPC.TBMOVIMENTACAOMATERIAL ";
		$sql .= "         WHERE CTIPMVCODI IN (4,19,20) ";
		$sql .= "           AND CMATEPSEQU = MOV.CMATEPSEQU ";
		$sql .= "           AND CREQMASEQU = MOV.CREQMASEQU ";
		$sql .= "           AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' ) ";
		$sql .= "           AND TMOVMAULAT = ( ";
		$sql .= "               SELECT MAX(TMOVMAULAT) FROM SFPC.TBMOVIMENTACAOMATERIAL ";
		$sql .= "               WHERE CTIPMVCODI IN (4,19,20) ";
		$sql .= "                 AND CMATEPSEQU = MOV.CMATEPSEQU ";
		$sql .= "                 AND CREQMASEQU = MOV.CREQMASEQU ";
		$sql .= "                 AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' ) ";
		$sql .= "              ) ";
		$sql .= "         ORDER BY AMOVMAANOM DESC, CMOVMACODI DESC";  # Alterar
		$sql .= "        )END ";
		$sql .= "        ) ";*/

		/*$sql .= "        CASE WHEN MOV.CTIPMVCODI IN (4,19,20) THEN ";
		$sql .= "        ( ";
		$sql .= "        SELECT VMOVMAVALO FROM SFPC.TBMOVIMENTACAOMATERIAL ";
		$sql .= "         WHERE CTIPMVCODI IN (4,19,20) ";
		$sql .= "           AND CMATEPSEQU = MOV.CMATEPSEQU ";
		$sql .= "           AND CREQMASEQU = MOV.CREQMASEQU ";
		$sql .= "           AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' ) ";
		$sql .= "           AND TMOVMAULAT = ( ";
		$sql .= "               SELECT MAX(TMOVMAULAT) FROM SFPC.TBMOVIMENTACAOMATERIAL ";
		$sql .= "               WHERE CTIPMVCODI IN (4,19,20) ";
		$sql .= "                 AND CMATEPSEQU = MOV.CMATEPSEQU ";
		$sql .= "                 AND CREQMASEQU = MOV.CREQMASEQU ";
		$sql .= "                 AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' ) ";
		$sql .= "              ) ";
		$sql .= "         ORDER BY AMOVMAANOM DESC, CMOVMACODI DESC";  # Alterar
		$sql .= "        ) ";
		$sql .= "        ELSE ";
		$sql .= "            MOV.VMOVMAVALO ";
		$sql .= "        END ";
		$sql .= "        ) ";*/

		$sql .= "   FROM SFPC.TBMOVIMENTACAOMATERIAL MOV, SFPC.TBTIPOMOVIMENTACAO TIP, ";
		$sql .= "        SFPC.TBMATERIALPORTAL MAT, ";
		$sql .= "        SFPC.TBSUBCLASSEMATERIAL SUB, SFPC.TBGRUPOMATERIALSERVICO GRU ";
		$sql .= "  WHERE MOV.CTIPMVCODI = TIP.CTIPMVCODI ";
		$sql .= "    AND MOV.CMATEPSEQU = MAT.CMATEPSEQU ";
		$sql .= "    AND MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
		$sql .= "    AND SUB.CGRUMSCODI = GRU.CGRUMSCODI ";
		if($Movimentacoes){
				if($Operacao == 'I'){
						$sql .= "    AND MOV.CTIPMVCODI IN ($Movimentacoes)" ;
				}elseif($Operacao == 'N'){
						$sql .= "    AND MOV.CTIPMVCODI NOT IN ($Movimentacoes)" ;
				}
		}
		$sql .= "    AND TIP.FTIPMVTIPO = '$TipoMov' ";
		$sql .= "    AND GRU.FGRUMSTIPM = '$TipoMaterial' ";
		$sql .= "    AND MOV.CALMPOCODI = $Almoxarifado ";
		$sql .= "    AND MOV.TMOVMAULAT > '$DataPesquisa1 23:59:59' ";
		if ($DataPesquisa2){
				$sql .= "    AND MOV.TMOVMAULAT <= '$DataPesquisa2 23:59:59' ";
		}
		$sql .= "    AND (MOV.FMOVMASITU IS NULL OR MOV.FMOVMASITU = 'A') "; // Traz só movimentaçs ativas
		$res  = $db->query($sql);
		if( PEAR::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sql");
		}else{
				$Linha        = $res->fetchRow();
				$Movimentacao = $Linha[0];
				return $Movimentacao;
		}
}

# Conecta ao banco de dados #
$db = Conexao();

# Imprime as informaçs do Almoxarifado #
$sqlalmo = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL WHERE CALMPOCODI = $Almoxarifado";
$resalmo = $db->query($sqlalmo);
if( PEAR::isError($resalmo) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sqlalmo");
}else{
		$Almox     = $resalmo->fetchRow();
		$DescAlmox = $Almox[0];
		$pdf->Cell(40,5,"ALMOXARIFADO",1,0,"L",1);
		$pdf->Cell(240,5,$DescAlmox,1,1,"L",0);
}
# Imprime a data das informaçs #
$pdf->Cell(40,5,"MÊ/ANO REFERÊCIA",1,0,"L",1);
$pdf->Cell(240,5,"$Mes / $AnoPesquisaF",1,0,"L",0);
$pdf->ln(8);

# Cabeçho da tabela #
$pdf->Cell(40,5,"TIPO DO MATERIAL","TLR",0,"C",1);
$pdf->Cell(40,5,"SALDO INICIAL","TLR",0,"C",1);
$pdf->Cell(80,5,"ENTRADAS",1,0,"C",1);
$pdf->Cell(80,5,"SAÍAS",1,0,"C",1);
$pdf->Cell(40,5,"SALDO FINAL","TLR",1,"C",1);

$pdf->Cell(40,5,"","BLR",0,"L",1);
$pdf->Cell(40,5,"$DataPesquisaI","BLR",0,"C",1);
$pdf->Cell(40,5,"AQUISIÇES",1,0,"C",1);
$pdf->Cell(40,5,"OUTRAS",1,0,"C",1);
$pdf->Cell(40,5,"REQUISIÇES",1,0,"C",1);
$pdf->Cell(40,5,"OUTRAS",1,0,"C",1);
$pdf->Cell(40,5,"$DataPesquisaF","BLR",1,"C",1);

# OBTEM AS INFORMAÇES NECESSÁIAS PARA O RELATÓIO ATRAVÉ DA INVOCAÇO À FUNÇES #
# Calcula o estoque do úo dia do mêanterior #
$EstoquePF       = EstoqueData($Almoxarifado, "P", $DataPesquisaF, $db);
$EstoqueCF       = EstoqueData($Almoxarifado, "C", $DataPesquisaF, $db);
# Calcula as movimentaçs (Requisiç) dentro do mêanterior #
$SaidasPMesReq   = Movimentacoes($Almoxarifado, "S", "P", $DataPesquisaI, $DataPesquisaF, "4,20,22", "I", $db);
$SaidasCMesReq   = Movimentacoes($Almoxarifado, "S", "C", $DataPesquisaI, $DataPesquisaF, "4,20,22", "I", $db);
$EntradasPMesReq = Movimentacoes($Almoxarifado, "E", "P", $DataPesquisaI, $DataPesquisaF, "2,18,19,21", "I", $db);
$EntradasCMesReq = Movimentacoes($Almoxarifado, "E", "C", $DataPesquisaI, $DataPesquisaF, "2,18,19,21", "I", $db);
$ResumoPMesReq   = $SaidasPMesReq - $EntradasPMesReq;
$ResumoCMesReq   = $SaidasCMesReq - $EntradasCMesReq;
# Calcula as movimentaçs (Aquisiçs) dentro do mêanterior #
$EntradasPMesAqu = Movimentacoes($Almoxarifado, "E", "P", $DataPesquisaI, $DataPesquisaF, "3,7", "I", $db);
$EntradasCMesAqu = Movimentacoes($Almoxarifado, "E", "C", $DataPesquisaI, $DataPesquisaF, "3,7", "I", $db);
$SaidasPMesAqu   = Movimentacoes($Almoxarifado, "S", "P", $DataPesquisaI, $DataPesquisaF, "8", "I", $db);
$SaidasCMesAqu   = Movimentacoes($Almoxarifado, "S", "C", $DataPesquisaI, $DataPesquisaF, "8", "I", $db);
$ResumoPMesAqu   = $EntradasPMesAqu - $SaidasPMesAqu;
$ResumoCMesAqu   = $EntradasCMesAqu - $SaidasCMesAqu;
# Calcula as movimentaçs (Outras) dentro do mêanterior #
$EntradasPMesOut = Movimentacoes($Almoxarifado, "E", "P", $DataPesquisaI, $DataPesquisaF, "2,18,19,21,3,7", "N", $db);
$EntradasCMesOut = Movimentacoes($Almoxarifado, "E", "C", $DataPesquisaI, $DataPesquisaF, "2,18,19,21,3,7", "N", $db);
$SaidasPMesOut   = Movimentacoes($Almoxarifado, "S", "P", $DataPesquisaI, $DataPesquisaF, "4,20,22,8", "N", $db);
$SaidasCMesOut   = Movimentacoes($Almoxarifado, "S", "C", $DataPesquisaI, $DataPesquisaF, "4,20,22,8", "N", $db);
# Calcula o estoque do úo dia do mêanterior ao anterior#
$EstoquePI = EstoqueData($Almoxarifado, "P", $DataPesquisaI, $db);
$EstoqueCI = EstoqueData($Almoxarifado, "C", $DataPesquisaI, $db);

$db->disconnect();

# Resume as movimentaçs #
$MovimentacaoP   = ($ResumoPMesAqu - $ResumoPMesReq) + ($EntradasPMesOut - $SaidasPMesOut);
$MovimentacaoC   = ($ResumoCMesAqu - $ResumoCMesReq) + ($EntradasCMesOut - $SaidasCMesOut);

# Calcula Totais #
$EstoqueI       = $EstoqueCI       + $EstoquePI;
$ResumoMesAqu   = $ResumoCMesAqu   + $ResumoPMesAqu;
$EntradasMesOut = $EntradasCMesOut + $EntradasPMesOut;
$ResumoMesReq   = $ResumoCMesReq   + $ResumoPMesReq;
$SaidasMesOut   = $SaidasCMesOut   + $SaidasPMesOut;
$EstoqueF       = $EstoqueCF       + $EstoquePF;

# IMPRIME DADOS #
# Dados Consumo #
$EstoqueCI       = converte_valor_estoques($EstoqueCI);
$ResumoCMesAqu   = converte_valor_estoques($ResumoCMesAqu);
$EntradasCMesOut = converte_valor_estoques($EntradasCMesOut);
$ResumoCMesReq   = converte_valor_estoques($ResumoCMesReq);
$SaidasCMesOut   = converte_valor_estoques($SaidasCMesOut);
$EstoqueCF       = converte_valor_estoques($EstoqueCF);
$pdf->Cell(40,5,"CONSUMO",1,0,"L",1);
$pdf->Cell(40,5,"$EstoqueCI",1,0,"R",0);
$pdf->Cell(40,5,"$ResumoCMesAqu",1,0,"R",0);
$pdf->Cell(40,5,"$EntradasCMesOut",1,0,"R",0);
$pdf->Cell(40,5,"$ResumoCMesReq",1,0,"R",0);
$pdf->Cell(40,5,"$SaidasCMesOut",1,0,"R",0);
$pdf->Cell(40,5,"$EstoqueCF",1,1,"R",0);

# Dados Permanente #
$EstoquePI       = converte_valor_estoques($EstoquePI);
$ResumoPMesAqu   = converte_valor_estoques($ResumoPMesAqu);
$EntradasPMesOut = converte_valor_estoques($EntradasPMesOut);
$ResumoPMesReq   = converte_valor_estoques($ResumoPMesReq);
$SaidasPMesOut   = converte_valor_estoques($SaidasPMesOut);
$EstoquePF       = converte_valor_estoques($EstoquePF);
$pdf->Cell(40,5,"PERMANENTE",1,0,"L",1);
$pdf->Cell(40,5,"$EstoquePI",1,0,"R",0);
$pdf->Cell(40,5,"$ResumoPMesAqu",1,0,"R",0);
$pdf->Cell(40,5,"$EntradasPMesOut",1,0,"R",0);
$pdf->Cell(40,5,"$ResumoPMesReq",1,0,"R",0);
$pdf->Cell(40,5,"$SaidasPMesOut",1,0,"R",0);
$pdf->Cell(40,5,"$EstoquePF",1,1,"R",0);

# Dados Totais #
$EstoqueI        = converte_valor_estoques($EstoqueI);
$ResumoMesAqu    = converte_valor_estoques($ResumoMesAqu);
$EntradasMesOut  = converte_valor_estoques($EntradasMesOut);
$ResumoMesReq    = converte_valor_estoques($ResumoMesReq);
$SaidasMesOut    = converte_valor_estoques($SaidasMesOut);
$EstoqueF        = converte_valor_estoques($EstoqueF);
$pdf->Cell(40,5,"TOTAL",1,0,"L",1);
$pdf->Cell(40,5,"$EstoqueI",1,0,"R",0);
$pdf->Cell(40,5,"$ResumoMesAqu",1,0,"R",0);
$pdf->Cell(40,5,"$EntradasMesOut",1,0,"R",0);
$pdf->Cell(40,5,"$ResumoMesReq",1,0,"R",0);
$pdf->Cell(40,5,"$SaidasMesOut",1,0,"R",0);
$pdf->Cell(40,5,"$EstoqueF",1,1,"R",0);

$pdf->Output();
?>
