<?php
#------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelEntradasSaidasAnaliticoPdf.php
# Objetivo: Programa de impressão do resumo de estoque e movimentações do mês anterior.
# Autor:    Carlos Abreu
# Data:     12/06/2007
# OBS.:     Tabulação 2 espaços
#           Quando o inventário for aberto e fechado no mesmo mês e não houverem movimentações neste mês, o total do mês 
#           baterá com o relatório de fechamento de inventário. Porém, se houver movimentações no mês do 
#           inventário terá que ser acrescido ao valor do fechamento de inventário, estas movimentações
#------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

session_cache_limiter('private_no_expire');

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
$Almoxarifado = $_SESSION['Almoxarifado'];
$DataAtual    = $_SESSION['DataAtual'];
$TipoMaterial = $_SESSION['TipoMaterial'];

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Função exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

# Informa o Título do Relatório #
$TituloRelatorio = "Relatório Analítico de Entradas e Saídas";

# Cria o objeto PDF, o Default é formato Retrato, A4  e a medida em milímetros #
$pdf = new PDF("L","mm","A4");

# Define um apelido para o número total de páginas #
$pdf->AliasNbPages();

# Define as cores do preenchimentos que serão usados #
$pdf->SetFillColor(220,220,220);

# Adiciona uma página no documento #
$pdf->AddPage();

# Seta as fontes que serão usadas na impressão de strings #
$pdf->SetFont("Arial","",9);

# DEFININDO A DATA DA PESQUISA #
# Descobre a data atual #
if(!$DataAtual) $DataAtual = date("d/m/Y");
$DataAtual   = explode("/",$DataAtual);
$DiaAtual    = $DataAtual[0];
$MesAtual    = $DataAtual[1];
$AnoAtual    = $DataAtual[2];
if($MesAtual == 1){      # Se o mês for janeiro, a ano inicial e final de pesquisa será o anterior
		$AnoPesquisaF = $AnoAtual - 1;
		$MesPesquisaF = 12;
		$AnoPesquisaI = $AnoAtual - 1;
		$MesPesquisaI = 11;
		$Mes          = "DEZEMBRO";
}elseif($MesAtual == 2){ # Se o mês for fevereiro, a ano inicial de pesquisa será o anterior
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
		$Meses = array("JANEIRO","FEVEREIRO","MARÇO","ABRIL","MAIO","JUNHO","JULHO","AGOSTO","SETEMBRO","OUTUBRO","NOVEMBRO","DEZEMBRO");
		$Mes          = $Meses[$MesPesquisaF-1];
}
# Descobre os útimos dias dos meses #
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

//$TotalEstoqueI       = 0;
$TotalResumoMesAqu   = 0;
$TotalEntradasMesOut = 0;
$TotalResumoMesReq   = 0;
$TotalSaidasMesOut   = 0;
//$TotalEstoqueF       = 0;
/*
# FUNÇÕES DE RESGATE DE INFORMAÇÕES #
# Resgata o estoque em uma data definida, com cálculos no PHP #
function EstoqueData($Almoxarifado, $TipoMaterial, $GrupoMaterial, $DataPesquisa, $db){
		# Converte a data para o formato de pesquisa no banco de dados
		if($DataPesquisa) $DataPesquisa = DataInvertida($DataPesquisa);
		# Resgata os itens do estoque #
		$sql  = " SELECT MAT.CMATEPSEQU, ARM.AARMATQTDE, ";
		$sql .= "        ARM.VARMATUMED, TIP.FTIPMVTIPO, MOV.AMOVMAQTDM ";
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
		$sql .= "    AND GRU.CGRUMSCODI = $GrupoMaterial ";
		$res  = $db->query($sql);
		if( PEAR::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sql");
		}else{
				# Linhas de Itens de Material #
				$rows = $res->numRows();
				if($rows == 0){
						$Totalizador = 0;
				}else{
						for($i=0; $i<= $rows; $i++){
								$Linha = $res->fetchRow();
								$CodigoReduzido[$i] = $Linha[0];
								$QtdEstoque[$i]     = $Linha[1];
								$ValorUnitario[$i]  = $Linha[2];
								$TipoMov[$i]        = $Linha[3];
								$QuantMov[$i]       = $Linha[4];
								if(!$MaterialTestado) $MaterialTestado = $CodigoReduzido[$i];
								if(!$j) $j=0;
								# Montando o array de Itens do Estoque #	
								if ($CodigoReduzido[$i] != $MaterialTestado) {
										$MaterialTestado = $CodigoReduzido[$i];
										# Descobre o valor unitário para o material na data especificada #
										$sqlvalor  = " SELECT VMOVMAUMED FROM SFPC.TBMOVIMENTACAOMATERIAL ";
										$sqlvalor .= "  WHERE CALMPOCODI = $Almoxarifado ";
										$sqlvalor .= "    AND CMATEPSEQU = ".$CodigoReduzido[$i-1]." ";
										$sqlvalor .= "    AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
										$sqlvalor .= "    AND TMOVMAULAT = ";
										$sqlvalor .= "        (SELECT MAX(TMOVMAULAT) FROM SFPC.TBMOVIMENTACAOMATERIAL ";
										$sqlvalor .= "          WHERE CALMPOCODI = $Almoxarifado ";
										$sqlvalor .= "            AND CMATEPSEQU = ".$CodigoReduzido[$i-1]." ";
										$sqlvalor .= "            AND TMOVMAULAT <= '$DataPesquisa 23:59:59' ";
										$sqlvalor .= "            AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') )";
										$resvalor    = $db->query($sqlvalor);
										if( PEAR::isError($resvalor) ){
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlvalor");
										}else{
												$LinhaValor              = $resvalor->fetchRow();
												$ValorUnitarioPesquisado = $LinhaValor[0];
										}

										$QtdTotal        = $QtdTotal + $QtdEstoqueCalc;
										$Totalizador     = $Totalizador + ($QtdEstoqueCalc * $ValorUnitarioPesquisado);
										$j = $j + 1;
										
										if($TipoMov[$i] == 'S'){
												$QtdEstoqueCalc = $QtdEstoque[$i] + $QuantMov[$i];
										}elseif($TipoMov[$i] == 'E'){
												$QtdEstoqueCalc = $QtdEstoque[$i] - $QuantMov[$i];
										}else{
												$QtdEstoqueCalc = $QtdEstoque[$i];
										}
								}else{
										if($TipoMov[$i] == 'S'){
												if(is_null($QtdEstoqueCalc)) $QtdEstoqueCalc = $QtdEstoque[$i];
												$QtdEstoqueCalc = $QtdEstoqueCalc + $QuantMov[$i];
										}elseif($TipoMov[$i] == 'E'){
												if(is_null($QtdEstoqueCalc)) $QtdEstoqueCalc = $QtdEstoque[$i];
												$QtdEstoqueCalc = $QtdEstoqueCalc - $QuantMov[$i];
										}else{
												if(is_null($QtdEstoqueCalc)) $QtdEstoqueCalc = $QtdEstoque[$i]; // Para entrada do primeiro material resultante do select quando neste momento não tem movimentação modificando. A variavel recebe a quantidade em estoque, se esta variável ainda estiver vazia.
										}
								}
						}
				}
		}
		return $Totalizador;
}
*/

# Resgata movimentações para exibição e cálculo de estoque em datas específicas #
function Movimentacoes($Almoxarifado, $TipoMov, $TipoMaterial, $GrupoMaterial, $DataPesquisa1, $DataPesquisa2, $Movimentacoes, $Operacao, $db){
		# Converte a data para o formato de pesquisa no banco de dados
		if($DataPesquisa1) $DataPesquisa1 = DataInvertida($DataPesquisa1);
		if($DataPesquisa2) $DataPesquisa2 = DataInvertida($DataPesquisa2);
		$sql  = " SELECT SUM(MOV.AMOVMAQTDM";
		$sql .= "        * ";
		$sql .= "        CASE WHEN MOV.CTIPMVCODI IN (4,19,20) THEN ";
		$sql .= "        ( ";
		$sql .= "        SELECT VMOVMAVALO FROM SFPC.TBMOVIMENTACAOMATERIAL ";
		$sql .= "        WHERE CMATEPSEQU = MOV.CMATEPSEQU ";
		$sql .= "          AND CREQMASEQU = MOV.CREQMASEQU ";
		$sql .= "          AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' ) ";
		$sql .= "          AND TMOVMAULAT = ( ";
		$sql .= "              SELECT MAX(TMOVMAULAT) FROM SFPC.TBMOVIMENTACAOMATERIAL ";
		$sql .= "               WHERE CTIPMVCODI IN (4,19,20) ";
		$sql .= "                 AND CMATEPSEQU = MOV.CMATEPSEQU ";
		$sql .= "                 AND CREQMASEQU = MOV.CREQMASEQU ";
		$sql .= "                 AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' ) ";
		$sql .= "              ) ";
		$sql .= "        ) ";
		$sql .= "        ELSE ";
		$sql .= "            MOV.VMOVMAVALO ";
		$sql .= "        END ";
		$sql .= "        ) ";
		$sql .= "   FROM SFPC.TBMOVIMENTACAOMATERIAL MOV, SFPC.TBTIPOMOVIMENTACAO TIP, ";
		$sql .= "        SFPC.TBMATERIALPORTAL MAT, ";
		$sql .= "        SFPC.TBSUBCLASSEMATERIAL SUB, ";
		$sql .= "        SFPC.TBGRUPOMATERIALSERVICO GRU ";
		$sql .= "  WHERE MOV.CTIPMVCODI = TIP.CTIPMVCODI ";
		$sql .= "    AND MOV.CMATEPSEQU = MAT.CMATEPSEQU ";
		$sql .= "    AND MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
		$sql .= "    AND SUB.CGRUMSCODI = GRU.CGRUMSCODI ";
		$sql .= "    AND GRU.CGRUMSCODI = $GrupoMaterial ";
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
		$sql .= "    AND (MOV.FMOVMASITU IS NULL OR MOV.FMOVMASITU = 'A') "; // Traz só as movimentações ativas
		
    
    //REMOVER
    //if($Movimentacoes == "4,20,22,8" && $Operacao == 'N'){
    // if($Movimentacoes == "2,18,19,21" && $Operacao == 'I'){
    // if($Movimentacoes == "4,20,22" && $Operacao == 'I'){
      // echo "SQL: $sql";
      // echo "<BR><BR><BR><BR>";
    // }
    //REMOVER
    
    $res  = $db->query($sql);
		if( PEAR::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sql");
		}else{
				$Linha        = $res->fetchRow();
				$Movimentacao = $Linha[0];
				return $Movimentacao;
		}
}

/*
# Conecta ao banco de dados #
$db = Conexao();

# Imprime as informações do Almoxarifado #
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
# Imprime a data das informações #
$pdf->Cell(40,5,"MÊS/ANO REFERÊNCIA",1,0,"L",1);
$pdf->Cell(240,5,"$Mes / $AnoPesquisaF",1,0,"L",0);
$pdf->ln(8);

# Cabeçalho da tabela #
$pdf->Cell(40,5,"TIPO DO MATERIAL","TLR",0,"C",1);
$pdf->Cell(40,5,"SALDO INICIAL","TLR",0,"C",1);
$pdf->Cell(80,5,"ENTRADAS",1,0,"C",1);
$pdf->Cell(80,5,"SAÍDAS",1,0,"C",1);
$pdf->Cell(40,5,"SALDO FINAL","TLR",1,"C",1);

$pdf->Cell(40,5,"","BLR",0,"L",1);
$pdf->Cell(40,5,"$DataPesquisaI","BLR",0,"C",1);
$pdf->Cell(40,5,"AQUISIÇÕES",1,0,"C",1);
$pdf->Cell(40,5,"OUTRAS",1,0,"C",1);
$pdf->Cell(40,5,"REQUISIÇÕES",1,0,"C",1);
$pdf->Cell(40,5,"OUTRAS",1,0,"C",1);
$pdf->Cell(40,5,"$DataPesquisaF","BLR",1,"C",1);

# OBTEM AS INFORMAÇÕES NECESSÁRIAS PARA O RELATÓRIO ATRAVÉS DA INVOCAÇÃO ÀS FUNÇÕES #
# Calcula o estoque do último dia do mês anterior #
$EstoquePF       = EstoqueData($Almoxarifado, $TipoMaterial, $DataPesquisaF, $db);
# Calcula as movimentações (Requisição) dentro do mês anterior #
$SaidasPMesReq   = Movimentacoes($Almoxarifado, "S", $TipoMaterial, $DataPesquisaI, $DataPesquisaF, "4,20,22", "I", $db);
$EntradasPMesReq = Movimentacoes($Almoxarifado, "E", $TipoMaterial, $DataPesquisaI, $DataPesquisaF, "2,18,19,21", "I", $db);
$ResumoPMesReq   = $SaidasPMesReq - $EntradasPMesReq;
# Calcula as movimentações (Aquisições) dentro do mês anterior #
$EntradasPMesAqu = Movimentacoes($Almoxarifado, "E", $TipoMaterial, $DataPesquisaI, $DataPesquisaF, "3,7", "I", $db);
$SaidasPMesAqu   = Movimentacoes($Almoxarifado, "S", $TipoMaterial, $DataPesquisaI, $DataPesquisaF, "8", "I", $db);
$ResumoPMesAqu   = $EntradasPMesAqu - $SaidasPMesAqu;
# Calcula as movimentações (Outras) dentro do mês anterior #
$EntradasPMesOut = Movimentacoes($Almoxarifado, "E", $TipoMaterial, $DataPesquisaI, $DataPesquisaF, "2,18,19,21,3,7", "N", $db);
$SaidasPMesOut   = Movimentacoes($Almoxarifado, "S", $TipoMaterial, $DataPesquisaI, $DataPesquisaF, "4,20,22,8", "N", $db);
# Calcula o estoque do último dia do mês anterior ao anterior#
$EstoquePI = EstoqueData($Almoxarifado, $TipoMaterial, $DataPesquisaI, $db);

$db->disconnect();

# Resume as movimentações #
$MovimentacaoP   = ($ResumoPMesAqu - $ResumoPMesReq) + ($EntradasPMesOut - $SaidasPMesOut);

# Dados Permanente #
$EstoquePI       = converte_valor_estoques($EstoquePI);
$ResumoPMesAqu   = converte_valor_estoques($ResumoPMesAqu);
$EntradasPMesOut = converte_valor_estoques($EntradasPMesOut);
$ResumoPMesReq   = converte_valor_estoques($ResumoPMesReq);
$SaidasPMesOut   = converte_valor_estoques($SaidasPMesOut);
$EstoquePF       = converte_valor_estoques($EstoquePF);

if ($TipoMaterial=='P'){
	$pdf->Cell(40,5,"PERMANENTE",1,0,"L",1);
} elseif($TipoMaterial=='C') {
	$pdf->Cell(40,5,"CONSUMO",1,0,"L",1);
}
$pdf->Cell(40,5,"$EstoquePI",1,0,"R",0);
$pdf->Cell(40,5,"$ResumoPMesAqu",1,0,"R",0);
$pdf->Cell(40,5,"$EntradasPMesOut",1,0,"R",0);
$pdf->Cell(40,5,"$ResumoPMesReq",1,0,"R",0);
$pdf->Cell(40,5,"$SaidasPMesOut",1,0,"R",0);
$pdf->Cell(40,5,"$EstoquePF",1,1,"R",0);

$pdf->Output("EntradaSaidaSintetico.pdf?".mktime(),"I");
*/

# Conecta ao banco de dados #
$db = Conexao();

# Imprime as informações do Almoxarifado #
$sqlalmo = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL WHERE CALMPOCODI = $Almoxarifado";
$resalmo = $db->query($sqlalmo);
if( PEAR::isError($resalmo) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sqlalmo");
}else{
		$Almox     = $resalmo->fetchRow();
		$DescAlmox = $Almox[0];
		$pdf->Cell(60,5,"ALMOXARIFADO",1,0,"L",1);
		$pdf->Cell(220,5,$DescAlmox,1,1,"L",0);
}

# Imprime a data das informações #
$pdf->Cell(60,5,"MÊS/ANO REFERÊNCIA",1,0,"L",1);
$pdf->Cell(220,5,"$Mes / $AnoPesquisaF",1,0,"L",0);
$pdf->ln(8);

# Cabeçalho da tabela #
$pdf->Cell(160,5,"TIPO DO MATERIAL","TLR",0,"C",1);
//$pdf->Cell(30,5,"SALDO INICIAL","TLR",0,"C",1);
$pdf->Cell(60,5,"ENTRADAS",1,0,"C",1);
$pdf->Cell(60,5,"SAÍDAS",1,1,"C",1);
//$pdf->Cell(30,5,"SALDO FINAL","TLR",1,"C",1);

if ($TipoMaterial=='P'){
	$pdf->Cell(160,5,"PERMANENTE","BLR",0,"C",1);
} elseif($TipoMaterial=='C') {
	$pdf->Cell(160,5,"CONSUMO","BLR",0,"C",1);
}

//$pdf->Cell(30,5,"$DataPesquisaI","BLR",0,"C",1);
$pdf->Cell(30,5,"AQUISIÇÕES",1,0,"C",1);
$pdf->Cell(30,5,"OUTRAS",1,0,"C",1);
$pdf->Cell(30,5,"REQUISIÇÕES",1,0,"C",1);
$pdf->Cell(30,5,"OUTRAS",1,1,"C",1);
//$pdf->Cell(30,5,"$DataPesquisaF","BLR",1,"C",1);

$sql  = "SELECT DISTINCT GRU.CGRUMSCODI, GRU.EGRUMSDESC ";
$sql .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL MOV ";
$sql .= " INNER JOIN SFPC.TBMATERIALPORTAL MAT ";
$sql .= "    ON MOV.CMATEPSEQU = MAT.CMATEPSEQU ";
$sql .= " INNER JOIN SFPC.TBSUBCLASSEMATERIAL SUB ";
$sql .= "    ON MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
$sql .= " INNER JOIN SFPC.TBGRUPOMATERIALSERVICO GRU ";
$sql .= "    ON SUB.CGRUMSCODI = GRU.CGRUMSCODI ";
$sql .= " WHERE GRU.FGRUMSTIPM = '$TipoMaterial' ";
$sql .= "   AND MOV.CALMPOCODI = $Almoxarifado ";
$sql .= "   AND MOV.TMOVMAULAT > '".DataInvertida($DataPesquisaI)." 23:59:59' AND MOV.TMOVMAULAT <= '".DataInvertida($DataPesquisaF)." 23:59:59' ";
$sql .= "   AND (MOV.FMOVMASITU IS NULL OR MOV.FMOVMASITU = 'A') ";
$res = $db->query($sql);
if( PEAR::isError($res) ){
	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sql");
}else{
	while( $Linha = $res->fetchRow() ){
		
		$GrupoMaterial = $Linha[0];
		$GrupoMaterialDescricao = $Linha[1];
		
		# OBTEM AS INFORMAÇÕES NECESSÁRIAS PARA O RELATÓRIO ATRAVÉS DA INVOCAÇÃO ÀS FUNÇÕES #
		//# Calcula o estoque do último dia do mês anterior #
		//$EstoqueF       = EstoqueData($Almoxarifado, $TipoMaterial, $GrupoMaterial, $DataPesquisaF, $db);
		# Calcula as movimentações (Requisição) dentro do mês anterior #
		$SaidasMesReq   = Movimentacoes($Almoxarifado, "S", $TipoMaterial, $GrupoMaterial, $DataPesquisaI, $DataPesquisaF, "4,20,22", "I", $db);
		$EntradasMesReq = Movimentacoes($Almoxarifado, "E", $TipoMaterial, $GrupoMaterial, $DataPesquisaI, $DataPesquisaF, "2,18,19,21", "I", $db);
		$ResumoMesReq   = $SaidasMesReq - $EntradasMesReq;
		# Calcula as movimentações (Aquisições) dentro do mês anterior #
		$EntradasMesAqu = Movimentacoes($Almoxarifado, "E", $TipoMaterial, $GrupoMaterial, $DataPesquisaI, $DataPesquisaF, "3,7", "I", $db);
		$SaidasMesAqu   = Movimentacoes($Almoxarifado, "S", $TipoMaterial, $GrupoMaterial, $DataPesquisaI, $DataPesquisaF, "8", "I", $db);
		$ResumoMesAqu   = $EntradasMesAqu - $SaidasMesAqu;
		# Calcula as movimentações (Outras) dentro do mês anterior #
		$EntradasMesOut = Movimentacoes($Almoxarifado, "E", $TipoMaterial, $GrupoMaterial, $DataPesquisaI, $DataPesquisaF, "2,18,19,21,3,7", "N", $db);
		$SaidasMesOut   = Movimentacoes($Almoxarifado, "S", $TipoMaterial, $GrupoMaterial, $DataPesquisaI, $DataPesquisaF, "4,20,22,8", "N", $db);
		//# Calcula o estoque do último dia do mês anterior ao anterior#
		//$EstoqueI = EstoqueData($Almoxarifado, $TipoMaterial, $GrupoMaterial, $DataPesquisaI, $db);
		# Resume as movimentações #
		$Movimentacao   = ($ResumoMesAqu - $ResumoMesReq) + ($EntradasMesOut - $SaidasMesOut);
		
		//echo $GrupoMaterial ." - ".$EstoqueI." - ".$EntradasMesAqu ." - ".$EntradasMesReq ." - ".$SaidasMesAqu ." - ".$SaidasMesReq ." - ".$EstoqueF ." - ".$Movimentacao."<br>";
		
		//$TotalEstoqueI       += $EstoqueI;
		$TotalResumoMesAqu   += $ResumoMesAqu;
		$TotalEntradasMesOut += $EntradasMesOut;
		$TotalResumoMesReq   += $ResumoMesReq;
		$TotalSaidasMesOut   += $SaidasMesOut;
		//$TotalEstoqueF       += $EstoqueF;
		
		# Dados #
		//$EstoqueI       = converte_valor_estoques($EstoqueI);
		$ResumoMesAqu   = converte_valor_estoques($ResumoMesAqu);
		$EntradasMesOut = converte_valor_estoques($EntradasMesOut);
		$ResumoMesReq   = converte_valor_estoques($ResumoMesReq);
		$SaidasMesOut   = converte_valor_estoques($SaidasMesOut);
		//$EstoqueF       = converte_valor_estoques($EstoqueF);
				
		$pdf->Cell(160,5,$GrupoMaterialDescricao,1,0,"L",0);
		//$pdf->Cell(30,5,"$EstoqueI",1,0,"R",0);
		$pdf->Cell(30,5,"$ResumoMesAqu",1,0,"R",0);
		$pdf->Cell(30,5,"$EntradasMesOut",1,0,"R",0);
		$pdf->Cell(30,5,"$ResumoMesReq",1,0,"R",0);
		$pdf->Cell(30,5,"$SaidasMesOut",1,1,"R",0);
		//$pdf->Cell(30,5,"$EstoqueF",1,1,"R",0);
		
	}
  
  //exit; //REMOVER
  
}
$pdf->Cell(160,5,"TOTAL",1,0,"R",1);
//$pdf->Cell(30,5,converte_valor_estoques($TotalEstoqueI),1,0,"R",0);
$pdf->Cell(30,5,converte_valor_estoques($TotalResumoMesAqu),1,0,"R",0);
$pdf->Cell(30,5,converte_valor_estoques($TotalEntradasMesOut),1,0,"R",0);
$pdf->Cell(30,5,converte_valor_estoques($TotalResumoMesReq),1,0,"R",0);
$pdf->Cell(30,5,converte_valor_estoques($TotalSaidasMesOut),1,1,"R",0);
//$pdf->Cell(30,5,converte_valor_estoques($TotalEstoqueF),1,1,"R",0);

$db->disconnect();
$pdf->Output("EntradaSaidaSintetico.pdf?".mktime(),"I");
?>
