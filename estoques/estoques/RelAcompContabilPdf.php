<?php
#------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelAcompContabilPdf.php
# Autor:    Rodrigo Melo
# Data:      03/06/2008
# Objetivo: Relatório criado para atender aos contadores da prefeitura tomando como base o relatório RelSinteticoEntradasSaidasPdf.php.
#               Este relatório preocupa-se em exibir as informações das movimentações confirmadas e pendentes dos materiais permanentes e de
#               consumo com base nas movimentações dos almoxarifados.
#----------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     13/06/2008 - Correção para exibir o total das notas fiscais e as requisições pendentes.
# Alterado: Rodrigo Melo
# Data:     03/07/2008 - Alteração para atender a redefinição do relatório.
# Alterado: Ariston Cordeiro
# Data:     06/04/2009 - Nova movimentação: "saída por processo administrativo" (37)
# Alterado: Rodrigo Melo
# Data:     11/06/2009 - Correção do relatório para não obter os dados através do ULAT, pois os mesmos podem ser modificados após a correção de materiais.
#----------------------------------------------------
# OBS.:     Tabulação 2 espaços
#------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

session_cache_limiter('private_no_expire');

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "GET"){
  $Almoxarifado = $_GET['Almoxarifado'];
  $DataIni      = $_GET['DataIni'];
  $DataFim      = $_GET['DataFim'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Função exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

# Informa o Título do Relatório #
$TituloRelatorio = "RELATÓRIO SINTÉTICO DE ACOMPANHAMENTO CONTÁBIL";

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

# FUNÇÕES DE RESGATE DE INFORMAÇÕES #

function VerificaSQL($Almoxarifado, $TipoMov, $TipoMaterial, $DataPesquisa1,
                     $DataPesquisa2, $Movimentacoes, $Operacao, $UtilizaFlagCorresp, $sql){
   echo "<BR>";
   echo "Almoxarifado: $Almoxarifado";
   echo "<BR>";
   echo "TipoMov: $TipoMov";
   echo "<BR>";
   echo "TipoMaterial: $TipoMaterial";
   echo "<BR>";
   echo "DataPesquisa1: $DataPesquisa1";
   echo "<BR>";
   echo "DataPesquisa2: $DataPesquisa2";
   echo "<BR>";
   echo "Movimentacoes: $Movimentacoes";
   echo "<BR>";
   echo "Operacao: $Operacao";
   echo "<BR>";
   echo "UtilizaFlagCorresp: $UtilizaFlagCorresp";
   echo "<BR>";
   echo "sql: $sql";
   echo "<BR>";
   echo "<BR>";
   echo "<BR>";
}


function Movimentacoes($Almoxarifado, $TipoMov, $TipoMaterial, $DataPesquisa1, $DataPesquisa2, $Movimentacoes, $Operacao, $UtilizaFlagCorresp, $db){
		# Converte a data para o formato de pesquisa no banco de dados
		if($DataPesquisa1) $DataPesquisa1 = DataInvertida($DataPesquisa1);
		if($DataPesquisa2) $DataPesquisa2 = DataInvertida($DataPesquisa2);

    $sql  = " SELECT SUM(MOV.AMOVMAQTDM";
		$sql .= "        * ";
		$sql .= "        CASE WHEN MOV.CTIPMVCODI IN (3,7,8) THEN (MOV.VMOVMAVALO)";
		$sql .= "        ELSE (MOV.VMOVMAUMED) END )";
		$sql .= "   FROM SFPC.TBTIPOMOVIMENTACAO TIP, SFPC.TBGRUPOMATERIALSERVICO GRU,  ";
		$sql .= "        SFPC.TBMATERIALPORTAL MAT, SFPC.TBSUBCLASSEMATERIAL SUB,  ";
		$sql .= "        SFPC.TBMOVIMENTACAOMATERIAL MOV  ";

    //PARA AS REQUISIÇÕES
    $sql .= "   LEFT OUTER JOIN SFPC.TBSITUACAOREQUISICAO SIT ";
    $sql .= "     ON MOV.CREQMASEQU = SIT.CREQMASEQU ";
    $sql .= "    AND TSITRESITU IN ";
    $sql .= "                   (SELECT MAX(TSITRESITU) FROM SFPC.TBSITUACAOREQUISICAO ";
    $sql .= "                     WHERE CREQMASEQU = SIT.CREQMASEQU ";
    $sql .= "                     AND TSITRESITU BETWEEN '$DataPesquisa1 00:00:00' AND '$DataPesquisa2 23:59:59' ) ";

    //PARA AS NOTAS FISCAIS
    $sql .= "   LEFT OUTER JOIN SFPC.TBENTRADANOTAFISCAL NFI   ";
    $sql .= "   ON MOV.CALMPOCODI = NFI.CALMPOCODI AND MOV.AENTNFANOE = NFI.AENTNFANOE ";
    $sql .= "   AND MOV.CENTNFCODI = NFI.CENTNFCODI "; //JOIN COM AS NOTAS FISCAIS

    $sql .= "  WHERE MOV.CTIPMVCODI = TIP.CTIPMVCODI ";
		$sql .= "    AND MOV.CMATEPSEQU = MAT.CMATEPSEQU ";
		$sql .= "    AND MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
		$sql .= "    AND SUB.CGRUMSCODI = GRU.CGRUMSCODI ";
    $sql .= "    AND TIP.CTIPMVCODI NOT IN (0,5) ";
    $sql .= "    AND TIP.FTIPMVTIPO = '$TipoMov' ";
		$sql .= "    AND GRU.FGRUMSTIPM = '$TipoMaterial' ";
		$sql .= "    AND MOV.CALMPOCODI = $Almoxarifado ";
    $sql .= "    AND (MOV.FMOVMASITU IS NULL OR MOV.FMOVMASITU = 'A') "; // Traz só as movimentações ativas
    $sql .= "    AND (NFI.FENTNFCANC IS NULL OR NFI.FENTNFCANC <> 'S') "; // Traz só as notas fiscais que não estão canceladas
    if($Operacao == 'I'){
      $sql .= "  AND MOV.CTIPMVCODI IN ($Movimentacoes) " ;
    }elseif($Operacao == 'N'){
      $sql .= "  AND MOV.CTIPMVCODI NOT IN ($Movimentacoes) " ;
    }

    //TESTE
    // $sql .= "  AND (  MOV.TMOVMAULAT BETWEEN '$DataPesquisa1 00:00:00' ";
    // $sql .= "          AND '$DataPesquisa2 23:59:59' )  ";

    $sql .= " AND (CASE  ";
    $sql .= "       WHEN (MOV.CTIPMVCODI IN (4,19,20) AND MOV.CREQMASEQU IS NOT NULL) THEN (  "; //Colocar as movimentação 4,19, 20 - Conforme o relatório de movimentação de materiais e Não colocar as movimentação 2, 21 e 22, pois não se aplicam a esta situação.
    if($UtilizaFlagCorresp == "S") { // Para as movimentações 4,18,19,20
      //$sql .= " se_requisicao_baixada(MOV.CREQMASEQU) = 1 "; //RETORNA 1 SE A REQUISIÇÃO ESTIVER BAIXADA
      $sql .= " SIT.CTIPSRCODI = 5 "; // BAIXADA (CTIPSRCODI = 5) OU CANCELADA (CTIPSRCODI = 5)
    } else {
      //$sql .= " se_requisicao_baixada(MOV.CREQMASEQU) = 0 "; //RETORNA 0 SE A REQUISIÇÃO NÃO ESTIVER BAIXADA
      $sql .= " SIT.CTIPSRCODI IN (3,4) "; //ATENDIDA TOTALMENTE(CTIPSRCODI = 3) OU ATENDIDA PARCIALMENTE (CTIPSRCODI = 4) - AINDA NÃO FORAM CONFIRMADAS, OU SEJA, BAIXADAS OU CANCELADAS
      $sql .= "          AND (DMOVMAMOVI BETWEEN '$DataPesquisa1 00:00:00' AND '$DataPesquisa2 23:59:59')  ";
      $sql .= "                AND MOV.CREQMASEQU NOT IN ( ";
      $sql .= "                SELECT CREQMASEQU FROM SFPC.TBSITUACAOREQUISICAO WHERE CREQMASEQU = MOV.CREQMASEQU AND CTIPSRCODI = 5 AND TSITRESITU BETWEEN '$DataPesquisa1 00:00:00' AND '$DataPesquisa2 23:59:59') ";
    }

    //$sql .= "  AND TO_DATE(TO_CHAR(SIT.TSITRESITU,'YYYY-MM-DD'),'YYYY-MM-DD')  ";
    //$sql .= "    BETWEEN '$DataPesquisa1 00:00:00' AND '$DataPesquisa2 23:59:59'  ";

    $sql .= "      )  "; // FIM DO WHEN MOV.CTIPMVCODI IN (4,19,20) THEN (

    $sql .= "  ELSE (CASE WHEN MOV.CTIPMVCODI IN (18) THEN (  ";
    $sql .= "    SIT.CTIPSRCODI = 6 )  "; //FIM DO WHEN MOV.CTIPMVCODI IN (18) THEN (
    //$sql .= "      END)  "; //FIM CASE WHEN MOV.CTIPMVCODI IN (18) THEN (

    $sql .= "  ELSE (CASE  ";

    $sql .= "      WHEN MOV.CTIPMVCODI IN (12,13,15,30) THEN (  ";

    if($UtilizaFlagCorresp != "S" && $Movimentacoes = "12,13,15,30") {
      $sql .= "      (  MOV.DMOVMAMOVI BETWEEN '$DataPesquisa1 00:00:00' ";
      $sql .= "          AND '$DataPesquisa2 23:59:59' )  AND  ";
    }

    $sql .= "        (  ";

    if($UtilizaFlagCorresp == "S") { // Para as movimentações 12,13,15,30
      $sql .= " MOV.FMOVMACORR = 'S' ";
      $sql .= " AND          ";
    } else {
      $sql .= " (MOV.FMOVMACORR = 'N' OR MOV.FMOVMACORR IS NULL) ";
      $sql .= " OR  NOT  ";
    }

    $sql .= "              (SELECT DATATRANSACAO.DMOVMAMOVI ";
    $sql .= "                 FROM SFPC.TBMOVIMENTACAOMATERIAL DATATRANSACAO ";
    $sql .= "                WHERE DATATRANSACAO.CALMPOCOD1 = MOV.CALMPOCODI ";
    $sql .= "                  AND DATATRANSACAO.AMOVMAANO1 = MOV.AMOVMAANOM ";
    $sql .= "                  AND DATATRANSACAO.CMOVMACOD1 = MOV.CMOVMACODI ";
    $sql .= "                  AND DATATRANSACAO.CTIPMVCODI IN (6,9,11,29) ";
    $sql .= "                  AND CASE WHEN MOV.CTIPMVCODI = 11 THEN ";
    $sql .= "                           DATATRANSACAO.CMATEPSEQ1 = MOV.CMATEPSEQU ";
    $sql .= "                      ELSE ";
    $sql .= "                           DATATRANSACAO.CMATEPSEQU = MOV.CMATEPSEQU ";
    $sql .= "                      END";
    $sql .= "              ) BETWEEN '$DataPesquisa1 00:00:00' AND '$DataPesquisa2 23:59:59' ) ";

    // if($UtilizaFlagCorresp != "S"){
      // $sql .= " OR (MOV.FMOVMACORR = 'S'  AND  (SELECT DATATRANSACAO.DMOVMAMOVI ";
      // $sql .= "                 FROM SFPC.TBMOVIMENTACAOMATERIAL DATATRANSACAO ";
      // $sql .= "                WHERE DATATRANSACAO.CALMPOCOD1 = MOV.CALMPOCODI ";
      // $sql .= "                  AND DATATRANSACAO.AMOVMAANO1 = MOV.AMOVMAANOM ";
      // $sql .= "                  AND DATATRANSACAO.CMOVMACOD1 = MOV.CMOVMACODI ";
      // $sql .= "                  AND DATATRANSACAO.CTIPMVCODI IN (6,9,11,29) ";
      // $sql .= "                  AND CASE WHEN MOV.CTIPMVCODI = 11 THEN ";
      // $sql .= "                           DATATRANSACAO.CMATEPSEQ1 = MOV.CMATEPSEQU ";
      // $sql .= "                      ELSE ";
      // $sql .= "                           DATATRANSACAO.CMATEPSEQU = MOV.CMATEPSEQU ";
      // $sql .= "                      END";
      // $sql .= "              ) > '$DataPesquisa2 23:59:59' ) ";

    // }

    $sql .= "      )  "; // FIM DO WHEN MOV.CTIPMVCODI IN (12,13,15,30) THEN (
    $sql .= "  ELSE (  MOV.DMOVMAMOVI BETWEEN '$DataPesquisa1 00:00:00' ";
    $sql .= "          AND '$DataPesquisa2 23:59:59' )  ";  //FIM ELSE INTERNO
    $sql .= "       END)  "; //FIM ELSE INTERNO
    $sql .= "     END)  "; //FIM CASE WHEN MOV.CTIPMVCODI IN (18) THEN (
    $sql .= "    END)  "; //FIM DO CASE

    //REMOVER
    // VerificaSQL($Almoxarifado, $TipoMov, $TipoMaterial, $DataPesquisa1,
                     // $DataPesquisa2, $Movimentacoes, $Operacao, $UtilizaFlagCorresp, $sql);
    //FIM REMOVER

    $res  = $db->query($sql);

		if( db::isError($res) ){
				$CodErroEmail  = $res->getCode(); //Obtém o código de erro do Banco de dados
        $DescErroEmail = $res->getMessage(); //Obtém a descrição do erro do Banco de dados

        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sql2\n\n$DescErroEmail ($CodErroEmail)");
        exit;
		}else{
				$Linha        = $res->fetchRow();
				$Movimentacao = $Linha[0];

				return $Movimentacao;
		}
}

# Conecta ao banco de dados #
$db = Conexao();

# Imprime as informações do Almoxarifado #
$sqlalmo = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL WHERE CALMPOCODI = $Almoxarifado";
$resalmo = $db->query($sqlalmo);
if( db::isError($resalmo) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sqlalmo");
    exit;
}else{
		$Almox     = $resalmo->fetchRow();
		$DescAlmox = $Almox[0];
		$pdf->Cell(40,5,"ALMOXARIFADO",1,0,"L",1);
		$pdf->Cell(240,5,$DescAlmox,1,1,"L",0);
}
# Imprime a data das informações #
$pdf->Cell(40,5,"PERÍODO",1,0,"L",1);
$pdf->Cell(240,5,$DataIni." À ".$DataFim,1,0,"L",0);
$pdf->ln(8);

# Cabeçalho da tabela #
//$pdf->SetX(25);
//$pdf->SetLeftMargin(10);
$pdf->Cell(261,5,"MOVIMENTAÇÕES CONFIRMADAS",1,1,"C",1);

$pdf->Cell(144,5,"ENTRADAS",1,0,"C",1);
$pdf->Cell(72,5,"SAÍDAS",1,0,"C",1);
$pdf->Cell(45,5,"","TLR",1,"C",1);


$pdf->Cell(36,5,"AQUISIÇÕES",1,0,"C",1);
$pdf->Cell(36,5,"AJUSTES",1,0,"C",1);
$pdf->Cell(36,5,"OUTRAS",1,0,"C",1);
$pdf->Cell(36,5,"CANCELAMENTO",1,0,"C",1);
$pdf->Cell(36,5,"REQUISIÇÕES",1,0,"C",1);
$pdf->Cell(36,5,"OUTRAS",1,0,"C",1);
$pdf->Cell(45,5,"SUB-TOTAL","LR",1,"C",1);


$pdf->Cell(36,5,"","BLR",0,"L",1);
$pdf->Cell(36,5,"AQUISIÇÕES","BLR",0,"C",1);
$pdf->Cell(36,5,"","BLR",0,"C",1);
$pdf->Cell(36,5,"REQUISIÇÕES","BLR",0,"C",1);
$pdf->Cell(36,5,"","BLR",0,"C",1);
$pdf->Cell(36,5,"","BLR",0,"L",1);
//$pdf->Cell(36,5,"","BLR",1,"L",1);
$pdf->SetFontSize(6);
$pdf->Cell(45,5,"(MOVIMENTAÇÕES CONFIRMADAS)","BLR",1,"C",1);
$pdf->SetFontSize(9);



# OBTEM AS INFORMAÇÕES NECESSÁRIAS PARA O RELATÓRIO ATRAVÉS DA INVOCAÇÃO ÀS FUNÇÕES #

# Calcula as movimentações (Aquisições) dentro do mês #

#Obtém o valor total das notas fiscais para exibir no PDF
$ResumoCMesAquConfirmada = Movimentacoes($Almoxarifado, "E", "C", $DataIni, $DataFim, "3", "I", "S", $db);
$ResumoPMesAquConfirmada = Movimentacoes($Almoxarifado, "E", "P", $DataIni, $DataFim, "3", "I", "S", $db);

#Obtém o valor total da entrada dos ajustes das notas fiscais (Mov 7)
$EntradasCMesAquAjustesConfirmada = Movimentacoes($Almoxarifado, "E", "C", $DataIni, $DataFim, "7", "I", "S", $db);
$EntradasPMesAquAjustesConfirmada = Movimentacoes($Almoxarifado, "E", "P", $DataIni, $DataFim, "7", "I", "S", $db);

#Obtém o valor total da saída dos ajustes das notas fiscais (Mov 8)
$SaidasCMesAquAjustesConfirmada = Movimentacoes($Almoxarifado, "S", "C", $DataIni, $DataFim, "8", "I", "S", $db);
$SaidasPMesAquAjustesConfirmada = Movimentacoes($Almoxarifado, "S", "P", $DataIni, $DataFim, "8", "I", "S", $db);

#Obtém o valor total das entradas válidas para as movimentações confirmadas (Movimentações: 2, 6, 9, 10, 11, 21, 26, 28, 29, 31, 32, 33) para exibir no PDF
$ResumoCMesAquOutrasConfirmada = Movimentacoes($Almoxarifado, "E", "C", $DataIni, $DataFim, "3,7,18,19", "N", "S", $db); //Obtem todas as movimentações de entrada para os materiais de consumo diferentes das movimentações: 3 (Entrada por nota fiscal), 7 (Entrada por alteração de nota fiscal), 18 (Entrada por cancelamento de requisição), 19 (Entrada para acerto da requisição)

$ResumoPMesAquOutrasConfirmada = Movimentacoes($Almoxarifado, "E", "P", $DataIni, $DataFim, "3,7,18,19", "N", "S", $db); //Obtem todas as movimentações de entrada para os materiais permanentes diferentes das movimentações: 3 (Entrada por nota fiscal), 7 (Entrada por alteração de nota fiscal), 18 (Entrada por cancelamento de requisição), 19 (Entrada para acerto da requisição)

#Calculo os ajustes das notas fiscais (Entrada - Saída: Mov 7 - Mov 8) para exibir no PDF. Este valor pode resultar num valor negativo.
$ResumoCMesAquAjustesConfirmada = $EntradasCMesAquAjustesConfirmada - $SaidasCMesAquAjustesConfirmada;
$ResumoPMesAquAjustesConfirmada = $EntradasPMesAquAjustesConfirmada - $SaidasPMesAquAjustesConfirmada;

#Obtém o valor total das entradas por cancelamento das requisições (Mov 18), essas movimentações são apenas para requisições que ainda não foram baixadas, pois os ajustes não podem ser feitas em requisições baixadas. Logo, o parâmetro $UtilizaFlagCorresp deve ser igual 'N'.
$ResumoCMesCancReqConfirmada = Movimentacoes($Almoxarifado, "E", "C", $DataIni, $DataFim, "18", "I", "N", $db);
$ResumoPMesCancReqConfirmada = Movimentacoes($Almoxarifado, "E", "P", $DataIni, $DataFim, "18", "I", "N", $db);

# Calcula as movimentações confirmadas (Requisição) dentro do mês anterior #

#Obtém o valor total das requisições dos materiais de consumo e peramente baixadas (Mov. 4) para exibir no PDF
$SaidasCMesReqConfirmada = Movimentacoes($Almoxarifado, "S", "C", $DataIni, $DataFim, "4", "I", "S", $db);
$SaidasPMesReqConfirmada = Movimentacoes($Almoxarifado, "S", "P", $DataIni, $DataFim, "4", "I", "S", $db);

#Obtém as modificações nas requisições confirmadas, para realizar o cálculo das requisições confimadas (Mov 4 + Mov 20 - Mov 19)

#Obtém o valor total da saída dos ajustes das requisições para os materiais de consumo e peramente (Mov 20).
$SaidasCMesReqModificacaoConfirmada = Movimentacoes($Almoxarifado, "S", "C", $DataIni, $DataFim, "20", "I", "S", $db);
$SaidasPMesReqModificacaoConfirmada = Movimentacoes($Almoxarifado, "S", "P", $DataIni, $DataFim, "20", "I", "S", $db);

#Obtém o valor total da entrada dos ajustes das requisições para os materiais de consumo e peramente (Mov 19).
$EntradasCMesReqModificacaoConfirmada = Movimentacoes($Almoxarifado, "E", "C", $DataIni, $DataFim, "19", "I", "S", $db);
$EntradasPMesReqModificacaoConfirmada = Movimentacoes($Almoxarifado, "E", "P", $DataIni, $DataFim, "19", "I", "S", $db);

#Obtém o valor total das saídas válidas para as movimentações confirmadas (Movimentações: 14, 16, 17, 22, 23, 24, 25, 27, 34).
$ResumoCMesReqOutrasConfirmada = Movimentacoes($Almoxarifado, "S", "C", $DataIni, $DataFim, "14, 16, 17, 22, 23, 24, 25, 27, 34, 37", "I", "S", $db);
$ResumoPMesReqOutrasConfirmada = Movimentacoes($Almoxarifado, "S", "P", $DataIni, $DataFim, "14, 16, 17, 22, 23, 24, 25, 27, 34, 37", "I", "S", $db);

#Obtém o valor total das saídas válidas para as movimentações confirmadas com suas movimentações correspondentes(Movimentações: 12,13,15,30, caso tenham sido anteriormente confirmadas com suas respectivas movimentações correspondentes)
$ResumoCMesReqOutrasConfirmada = $ResumoCMesReqOutrasConfirmada + Movimentacoes($Almoxarifado, "S", "C", $DataIni, $DataFim, "12,13,15,30", "I", "S", $db);
$ResumoPMesReqOutrasConfirmada = $ResumoPMesReqOutrasConfirmada + Movimentacoes($Almoxarifado, "S", "P", $DataIni, $DataFim, "12,13,15,30", "I", "S", $db);

$ResumoCMesReqConfirmada   = ($SaidasCMesReqConfirmada + $SaidasCMesReqModificacaoConfirmada) - $EntradasCMesReqModificacaoConfirmada;
$ResumoPMesReqConfirmada   = ($SaidasPMesReqConfirmada + $SaidasPMesReqModificacaoConfirmada) - $EntradasPMesReqModificacaoConfirmada;


# Calcula SUB-TOTAIS
$EstoqueCEntradas = $ResumoCMesAquConfirmada + $ResumoCMesAquAjustesConfirmada + $ResumoCMesAquOutrasConfirmada + $ResumoCMesCancReqConfirmada;
$EstoquePEntradas = $ResumoPMesAquConfirmada + $ResumoPMesAquAjustesConfirmada + $ResumoPMesAquOutrasConfirmada + $ResumoPMesCancReqConfirmada;

$EstoqueCSaidas = $ResumoCMesReqConfirmada + $ResumoCMesReqOutrasConfirmada;
$EstoquePSaidas = $ResumoPMesReqConfirmada + $ResumoPMesReqOutrasConfirmada;


//Soma das entradas com as saídas
$SubTotalCMes = $EstoqueCEntradas - $EstoqueCSaidas;
$SubTotalPMes = $EstoquePEntradas - $EstoquePSaidas;
$SubTotalMes  = $SubTotalCMes + $SubTotalPMes;

# Calcula Totais #
$ResumoMesAqu   = $ResumoCMesAquConfirmada + $ResumoPMesAquConfirmada;
$ResumoMesAquAjustes = $ResumoCMesAquAjustesConfirmada + $ResumoPMesAquAjustesConfirmada;
$ResumoMesAquOutrasConfirmada = $ResumoCMesAquOutrasConfirmada + $ResumoPMesAquOutrasConfirmada;
$ResumoMesCancReqConfirmada = $ResumoCMesCancReqConfirmada + $ResumoPMesCancReqConfirmada;
$ResumoMesReqConfirmada   = $ResumoCMesReqConfirmada   + $ResumoPMesReqConfirmada;
$ResumoMesReqOutrasConfirmadas   = $ResumoCMesReqOutrasConfirmada   + $ResumoPMesReqOutrasConfirmada;


# IMPRIME DADOS #

//Obter os dados para as movimentações confirmadas de consumo e imprimir aqui...
# Dados Consumo #
$ResumoCMesAquConfirmada   = converte_valor_estoques($ResumoCMesAquConfirmada);
$ResumoCMesAquOutrasConfirmada = converte_valor_estoques($ResumoCMesAquOutrasConfirmada);
$ResumoCMesAquAjustesConfirmada = converte_valor_estoques($ResumoCMesAquAjustesConfirmada);
$ResumoCMesCancReqConfirmada = converte_valor_estoques($ResumoCMesCancReqConfirmada);
$ResumoCMesReqConfirmada   = converte_valor_estoques($ResumoCMesReqConfirmada);
$ResumoCMesReqOutrasConfirmada = converte_valor_estoques($ResumoCMesReqOutrasConfirmada);
$SubTotalCMes = converte_valor_estoques($SubTotalCMes);

$pdf->SetFont("Arial","B",10);
$pdf->Cell(261,5,"CONSUMO",1,1,"C",0);
$pdf->SetFont("Arial","",9);
$pdf->Cell(36,5,"$ResumoCMesAquConfirmada",1,0,"R",0);
$pdf->Cell(36,5,"$ResumoCMesAquAjustesConfirmada",1,0,"R",0);
$pdf->Cell(36,5,"$ResumoCMesAquOutrasConfirmada",1,0,"R",0);
$pdf->Cell(36,5,"$ResumoCMesCancReqConfirmada",1,0,"R",0);
$pdf->Cell(36,5,"$ResumoCMesReqConfirmada",1,0,"R",0);
$pdf->Cell(36,5,"$ResumoCMesReqOutrasConfirmada",1,0,"R",0);
$pdf->Cell(45,5,"$SubTotalCMes",1,1,"R",0);

//Obter os dados para as movimentações confirmadas permanentes e imprimir aqui...
# Dados Permanente #


$ResumoPMesAquConfirmada   = converte_valor_estoques($ResumoPMesAquConfirmada);
$ResumoPMesAquAjustesConfirmada = converte_valor_estoques($ResumoPMesAquAjustesConfirmada);
$ResumoPMesAquOutrasConfirmada = converte_valor_estoques($ResumoPMesAquOutrasConfirmada);
$ResumoPMesCancReqConfirmada = converte_valor_estoques($ResumoPMesCancReqConfirmada);
$ResumoPMesReqConfirmada   = converte_valor_estoques($ResumoPMesReqConfirmada);
$ResumoPMesReqOutrasConfirmada = converte_valor_estoques($ResumoPMesReqOutrasConfirmada);
$SubTotalPMes = converte_valor_estoques($SubTotalPMes);

$pdf->SetFont("Arial","B",10);
$pdf->Cell(261,5,"PERMANENTE",1,1,"C",0);
$pdf->SetFont("Arial","",9);
$pdf->Cell(36,5,"$ResumoPMesAquConfirmada",1,0,"R",0);
$pdf->Cell(36,5,"$ResumoPMesAquAjustesConfirmada",1,0,"R",0);
$pdf->Cell(36,5,"$ResumoPMesAquOutrasConfirmada",1,0,"R",0);
$pdf->Cell(36,5,"$ResumoPMesCancReqConfirmada",1,0,"R",0);
$pdf->Cell(36,5,"$ResumoPMesReqConfirmada",1,0,"R",0);
$pdf->Cell(36,5,"$ResumoPMesReqOutrasConfirmada",1,0,"R",0);
$pdf->Cell(45,5,"$SubTotalPMes",1,1,"R",0);

//Obter os dados para o TOTAL das movimentações confirmadas de consumo e permanentes. E prepara os dados para imprimir.
# Dados Totais #
$EstoqueI        = converte_valor_estoques($EstoqueI);
$ResumoMesAqu    = converte_valor_estoques($ResumoMesAqu);
$ResumoMesAquAjustes = converte_valor_estoques($ResumoMesAquAjustes);
$ResumoMesAquOutrasConfirmada  = converte_valor_estoques($ResumoMesAquOutrasConfirmada);
$ResumoMesCancReqConfirmada   = converte_valor_estoques($ResumoMesCancReqConfirmada);
$ResumoMesReqConfirmada    = converte_valor_estoques($ResumoMesReqConfirmada);
$ResumoMesReqOutrasConfirmadas  = converte_valor_estoques($ResumoMesReqOutrasConfirmadas);
$SubTotalMes = converte_valor_estoques($SubTotalMes);

$pdf->SetFont("Arial","B",10);
$pdf->Cell(261,5,"TOTAL",1,1,"C",0);
$pdf->SetFont("Arial","",9);

$pdf->Cell(36,5,"$ResumoMesAqu",1,0,"R",0);
$pdf->Cell(36,5,"$ResumoMesAquAjustes",1,0,"R",0);
$pdf->Cell(36,5,"$ResumoMesAquOutrasConfirmada",1,0,"R",0);
$pdf->Cell(36,5,"$ResumoMesCancReqConfirmada",1,0,"R",0);
$pdf->Cell(36,5,"$ResumoMesReqConfirmada",1,0,"R",0);
$pdf->Cell(36,5,"$ResumoMesReqOutrasConfirmadas",1,0,"R",0);
$pdf->Cell(45,5,"$SubTotalMes",1,1,"R",0);

#################################
//Movimentações pendentes
#################################

# Calcula as movimentações pendentes (Requisição) dentro do mês anterior #

#Obtém o valor total das requisições dos materiais de consumo e peramente NÃO baixadas (Mov. 4) para exibir no PDF
$SaidasCMesReqPendente = Movimentacoes($Almoxarifado, "S", "C", $DataIni, $DataFim, "4", "I", "N", $db);
$SaidasPMesReqPendente = Movimentacoes($Almoxarifado, "S", "P", $DataIni, $DataFim, "4", "I", "N", $db);

#Obtém as modificações nas requisições confirmadas, para realizar o cálculo das requisições PENDENTES (Mov 4 + Mov 20 - Mov 19)

#Obtém o valor total da saída dos ajustes das requisições para os materiais de consumo e peramente (Mov 20) NÃO BAIXADAS.
$SaidasCMesReqModificacaoPendente = Movimentacoes($Almoxarifado, "S", "C", $DataIni, $DataFim, "20", "I", "N", $db);
$SaidasPMesReqModificacaoPendente = Movimentacoes($Almoxarifado, "S", "P", $DataIni, $DataFim, "20", "I", "N", $db);

#Obtém o valor total da entrada dos ajustes das requisições para os materiais de consumo e peramente (Mov 19) NÃO BAIXADAS.
$EntradasCMesReqModificacaoPendente = Movimentacoes($Almoxarifado, "E", "C", $DataIni, $DataFim, "19", "I", "N", $db);
$EntradasPMesReqModificacaoPendente = Movimentacoes($Almoxarifado, "E", "P", $DataIni, $DataFim, "19", "I", "N", $db);

#Obtém o valor total das saídas válidas para as movimentações PENDENTES, ou seja, que não possuem suas movimentações correspondentes(Movimentações: 12,13,15,30, caso NÃO tenham sido anteriormente confirmadas com suas respectivas movimentações correspondentes)
$ResumoCMesReqOutrasPendente = Movimentacoes($Almoxarifado, "S", "C", $DataIni, $DataFim, "12,13,15,30", "I", "N", $db);
$ResumoPMesReqOutrasPendente = Movimentacoes($Almoxarifado, "S", "P", $DataIni, $DataFim, "12,13,15,30", "I", "N", $db);

#Calculando o valor das requisições pendentes (Mov 4 + Mov 20 - Mov 19)  para exibir no PDF
$ResumoCMesReqPendente   = ($SaidasCMesReqPendente + $SaidasCMesReqModificacaoPendente) - $EntradasCMesReqModificacaoPendente;
$ResumoPMesReqPendente   = ($SaidasPMesReqPendente + $SaidasPMesReqModificacaoPendente) - $EntradasPMesReqModificacaoPendente;

#Calculando os subtotais para o materiais de consumo e permanente
//Soma as requisições, ajustes e outras requisições pendentes para os materiais de consumo. A soma é obtida da seguinte forma: (Req. Pendentes + Outras Mov. Pendentes) - Ajustes (Entrada por cancelamento de Requisição).
$ResumoCMesSubTotalPendente = ($ResumoCMesReqPendente + $ResumoCMesReqOutrasPendente);
//Soma as requisições, ajustes e outras requisições pendentes para os materiais permanentes
$ResumoPMesSubTotalPendente = ($ResumoPMesReqPendente + $ResumoPMesReqOutrasPendente);

#Calculando os TOTAIS das movimentações pendentes
$ResumoMesReqPendente = $ResumoCMesReqPendente + $ResumoPMesReqPendente;
$ResumoMesReqOutrasPendente = $ResumoCMesReqOutrasPendente + $ResumoPMesReqOutrasPendente;
$ResumoMesSubTotalPendente = $ResumoCMesSubTotalPendente + $ResumoPMesSubTotalPendente;

$db->disconnect(); //Fecha conexão com o oracle

$pdf->ln(10);
$pdf->Cell(261,5,"MOVIMENTAÇÕES PENDENTES",1,1,"C",1);

$pdf->Cell(202,5,"SAÍDAS","TLR",0,"C",1);
$pdf->Cell(59,5,"SUB-TOTAL","TLR",1,"C",1);

$pdf->Cell(101,5,"REQUISIÇÕES","TLR",0,"C",1);
$pdf->Cell(101,5,"OUTRAS","TLR",0,"C",1);
$pdf->SetFontSize(6);
$pdf->Cell(59,5,"(MOVIMENTAÇÕES PENDENTES)","BLR",1,"C",1);
$pdf->SetFontSize(9);


//Obter os dados para as movimentações pendentes de consumo e imprimir aqui...
$ResumoCMesReqPendente = converte_valor_estoques($ResumoCMesReqPendente);
$ResumoCMesReqOutrasPendente = converte_valor_estoques($ResumoCMesReqOutrasPendente);
$ResumoCMesSubTotalPendente = converte_valor_estoques($ResumoCMesSubTotalPendente);

$pdf->SetFont("Arial","B",10);
$pdf->Cell(261,5,"CONSUMO",1,1,"C",0);
$pdf->SetFont("Arial","",9);

$pdf->Cell(101,5,"$ResumoCMesReqPendente",1,0,"R",0);
$pdf->Cell(101,5,"$ResumoCMesReqOutrasPendente",1,0,"R",0);
$pdf->Cell(59,5,"$ResumoCMesSubTotalPendente",1,1,"R",0);

//Obter os dados para as movimentações pendentes permanentes e imprimir aqui...
$ResumoPMesReqPendente = converte_valor_estoques($ResumoPMesReqPendente);
$ResumoPMesReqOutrasPendente = converte_valor_estoques($ResumoPMesReqOutrasPendente);
$ResumoPMesSubTotalPendente = converte_valor_estoques($ResumoPMesSubTotalPendente);

$pdf->SetFont("Arial","B",10);
$pdf->Cell(261,5,"PERMANENTE",1,1,"C",0);
$pdf->SetFont("Arial","",9);

$pdf->Cell(101,5,"$ResumoPMesReqPendente",1,0,"R",0);
$pdf->Cell(101,5,"$ResumoPMesReqOutrasPendente",1,0,"R",0);
$pdf->Cell(59,5,"$ResumoPMesSubTotalPendente",1,1,"R",0);

//Obter os dados para o TOTAL das movimentações pendentes de consumo e permanentes. E prepara os dados para imprimir.
$ResumoMesReqPendente = converte_valor_estoques($ResumoMesReqPendente);
$ResumoMesReqOutrasPendente = converte_valor_estoques($ResumoMesReqOutrasPendente);
$ResumoMesSubTotalPendente = converte_valor_estoques($ResumoMesSubTotalPendente);


$pdf->SetFont("Arial","B",10);
$pdf->Cell(261,5,"TOTAL",1,1,"C",0);
$pdf->SetFont("Arial","",9);

$pdf->Cell(101,5,"$ResumoMesReqPendente",1,0,"R",0);
$pdf->Cell(101,5,"$ResumoMesReqOutrasPendente",1,0,"R",0);
$pdf->Cell(59,5,"$ResumoMesSubTotalPendente",1,1,"R",0);

//INFORMAÇÕES:
$pdf->ln(8);
$observacoes = "1) Este relatório integra ao valor das requisições as modificações por elas sofridas";
$pdf->SetFont("Arial","B",8);
$pdf->Cell(111,5,$observacoes,"",1,"L",0);
$observacoes = "2) O sub-total (Movimentações confirmadas) = entradas confirmdas – saídas confirmadas";
$pdf->Cell(111,5,$observacoes,"",1,"L",0);
$observacoes = "3) O sub-total (Movimentações pendentes)  = requisições pendentes + outras movimentações pendentes";
$pdf->Cell(111,5,$observacoes,"",1,"L",0);
$pdf->SetFont("Arial","",9);

$pdf->Output();

?>
