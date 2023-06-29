<?php
/**
 * Portal da DGCO
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category Application
 * @package Pitang
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * #-------------------------------------------------------------------------
 * # Portal da DGCO
 * # Programa: EmissaoCHF.php
 * # Autor:    Roberta Costa
 * # Data:     21/09/04
 * # Objetivo: Programa que Exibe os dados do CHF do Fornecedor Cadastrado
 * #---------------------------------
 * # Alterado: Rossana Lira
 * # Data:     16/05/07 - Troca do nome fornecedor para firma
 * # Data:     09/07/07 - Permitir emitir o CHF, mesmo estando com certidões fora do
 * #                      prazo de validade
 * #                    - Passar mensagem fornecedor c/certidões fora do prazo p/impressão
 * # Alterado: Everton Lino
 * # Data:     06/08/2010 - Verificação de data de balanço anual se está no prazo
 * # Alterado: Everton Lino
 * # Data:     14/10/2010- Correção
 * # Alterado: Ariston Cordeiro
 * # Data:     05/11/2010 - Alterando prazos de balanço anual e certidão negativa
 * # Alterado: Rodrigo Melo
 * # Data:     25/04/2011 - Retirando da mensagem de atenção a palavra "Inabilitado",
 * #                        devido a solicitação do usuário. Tarefa Redmine: 2205.
 * # Data:     28/11/2014 - Novo Layout
 * # Alterado: Pitang Agile TI
 */
# Alterado: Lucas Baracho
# Data:     29/10/2018
# Objetivo: Tarefa Redmine 199575
#-------------------------------------------------------------------------
require_once "TemplateAppPadrao.php";
$tpl = new TemplateAppPadrao("templates/ConsRegistroPrecoDetalhes2017.html");

// # Acesso ao arquivo de funções #
// include "../funcoes.php";

// # Executa o controle de segurança #
// session_start();
// Seguranca();

// Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Botao = $_POST['Botao'];
    $Critica = $_POST['Critica'];
} else {
    $GrupoCodigo = $_GET['GrupoCodigo'];
    $Processo = $_GET['Processo'];
    $ProcessoAno = $_GET['ProcessoAno'];
    $ComissaoCodigo = $_GET['ComissaoCodigo'];
    $OrgaoLicitanteCodigo = $_GET['OrgaoLicitanteCodigo'];
    $ObjetoPesquisa = $_GET['ItemObjeto'];
    $ComissaoPesquisa = $_GET['ItemComissao'];
    $OrgaoPesquisa = $_GET['ItemOrgao'];
    // $Fase = $_GET['Fase'];
}

$titulo = '';
$dados = '';
$tituloDocumentos = '';
$documentos = '';
// Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

// Resgata as informações da licitação #
$db = Conexao();
$sql = "SELECT A.EGREMPDESC, B.EMODLIDESC, C.ECOMLIDESC, D.XLICPOOBJE, ";
$sql .= "       E.EORGLIDESC, D.TLICPODHAB, D.CLICPOCODL, D.ALICPOANOP, ";
$sql .= "       D.FLICPOREGP, B.CMODLICODI, D.VLICPOVALE, D.VLICPOVALH, ";
$sql .= "       D.VLICPOTGES ";
$sql .= "  FROM SFPC.TBGRUPOEMPRESA A, SFPC.TBMODALIDADELICITACAO B, SFPC.TBCOMISSAOLICITACAO C, ";
$sql .= "       SFPC.TBLICITACAOPORTAL D, SFPC.TBORGAOLICITANTE E ";
$sql .= " WHERE A.CGREMPCODI = D.CGREMPCODI AND D.CGREMPCODI = $GrupoCodigo ";
$sql .= "   AND D.CMODLICODI = B.CMODLICODI AND C.CCOMLICODI = D.CCOMLICODI ";
$sql .= "   AND D.CCOMLICODI = $ComissaoCodigo AND D.ALICPOANOP = $ProcessoAno ";
$sql .= "   AND D.CLICPOPROC = $Processo AND E.CORGLICODI = D.CORGLICODI ";
$sql .= "   AND D.CORGLICODI = $OrgaoLicitanteCodigo";
$result = $db->query($sql);
if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
} else {
    $Rows = $result->numRows();
    while ($Linha = $result->fetchRow()) {
        $GrupoDesc = $Linha[0];
        $ModalidadeDesc = $Linha[1];
        $ComissaoDesc = $Linha[2];
        $OrgaoLicitacao = $Linha[4];
        $ObjetoLicitacao = $Linha[3];
        $Licitacao = substr($Linha[6] + 10000, 1);
        $AnoLicitacao = $Linha[7];
        $LicitacaoDtAbertura = substr($Linha[5], 8, 2) . "/" . substr($Linha[5], 5, 2) . "/" . substr($Linha[5], 0, 4);
        $LicitacaoHoraAbertura = substr($Linha[5], 11, 5);
        if ($Linha[8] == "S") {
            $RegistroPreco = "SIM";
        } else {
            $RegistroPreco = "NÃO";
        }
        $ModalidadeCodigo = $Linha[9];
        $ValorEstimado = converte_valor($Linha[10]);
        $ValorHomologado = converte_valor($Linha[11]);
        $TotalGeralEstimado = converte_valor($Linha[12]);
        
        
    }
}
$titulo .= "          <td colspan=\"4\"><strong>\n";
$titulo .= "$GrupoDesc<br><br>$ModalidadeDesc<br><br>$ComissaoDesc<br>";
$titulo .= "          </strong></td>\n";
$tpl->TITULO = $titulo;
$Processo = substr($Processo + 10000, 1);
$dados .= "          <tr>\n";
$dados .= "              <td valign=\"top\" colspan=\"2\"><strong>PROCESSO</strong></td>\n";
$dados .= "              <td valign=\"top\" class=\"textonormal\" colspan=\"2\">$Processo/$ProcessoAno</td>\n";
$dados .= "          </tr>\n";
$dados .= "          <tr>\n";
$dados .= "              <td valign=\"top\" colspan=\"2\"><strong>LICITAÇÃO</strong></td>\n";
$dados .= "              <td valign=\"top\" class=\"textonormal\" colspan=\"2\">$Licitacao/$AnoLicitacao</td>\n";
$dados .= "          </tr>\n";
$dados .= "          <tr>\n";
$dados .= "              <td valign=\"top\" colspan=\"2\"><strong>REGISTRO DE PREÇO";
if ($ModalidadeCodigo == 3) {
    $dados .= "/PERMISSÃO REMUNERADA DE USO";
}
$dados .= "              </strong></td>\n";
$dados .= "              <td valign=\"top\" class=\"textonormal\" colspan=\"2\">$RegistroPreco</td>\n";
$dados .= "          </tr>\n";
$dados .= "          <tr>\n";
$dados .= "              <td valign=\"top\" colspan=\"2\"><strong>OBJETO</strong></td>\n";
$dados .= "              <td valign=\"top\" class=\"textonormal\" colspan=\"2\">$ObjetoLicitacao</td>\n";
$dados .= "          </tr>\n";
$dados .= "          <tr>\n";
$dados .= "              <td valign=\"top\" colspan=\"2\"><strong>DATA/HORA DE ABERTURA</strong></td>\n";
$dados .= "              <td valign=\"top\" class=\"textonormal\" colspan=\"2\">$LicitacaoDtAbertura $LicitacaoHoraAbertura h</b></td>\n";
$dados .= "          </tr>\n";
$dados .= "          <tr>\n";
$dados .= "              <td valign=\"top\" colspan=\"2\"><strong>ÓRGÃO LICITANTE</strong></td>\n";
$dados .= "              <td valign=\"top\" class=\"textonormal\" colspan=\"2\">$OrgaoLicitacao</td>\n";
$dados .= "          </tr>\n";
if ($ValorHomologado != "0,00") {
    if ($ValorEstimado == "") {
        $ValorEstimado = "NÃO INFORMADO";
    }
    $dados .= "          <tr>\n";
    $dados .= "              <td valign=\"top\" colspan=\"2\"><strong>VALOR ESTIMADO</strong></td>\n";
    $dados .= "              <td valign=\"top\" class=\"textonormal\" colspan=\"2\">$ValorEstimado</td>\n";
    $dados .= "          </tr>\n";
}
if ($TotalGeralEstimado != "0,00") {
    $dados .= "          <tr>\n";
    $dados .= "              <td valign=\"top\" colspan=\"2\"><strong>TOTAL GERAL ESTIMADO<br>(Itens que Lograram Êxito</strong></td>\n";
    $dados .= "              <td valign=\"top\" class=\"textonormal\" colspan=\"2\">$TotalGeralEstimado</td>\n";
    $dados .= "          </tr>\n";
}
if ($ValorHomologado != "0,00") {
    $dados .= "          <tr>\n";
    $dados .= "              <td valign=\"top\" colspan=\"2\"><strong>VALOR HOMOLOGADO<br>(Itens que Lograram Êxito)</strong></td>\n";
    $dados .= "              <td valign=\"top\" class=\"textonormal\" colspan=\"2\">$ValorHomologado</td>\n";
    $dados .= "          </tr>\n";
}
$tpl->DADOS = $dados;
// Pega as Fases da Licitação #
$sql = "SELECT A.EFASESDESC, A.AFASESORDE, B.CLICPOPROC, B.ALICPOANOP, ";
$sql .= "       B.CFASESCODI, B.EFASELDETA, B.TFASELDATA, C.CATASFCODI, ";
$sql .= "       C.EATASFNOME ";
$sql .= "  FROM SFPC.TBFASES A, SFPC.TBFASELICITACAO B LEFT OUTER JOIN SFPC.TBATASFASE C ";
$sql .= "    ON B.CLICPOPROC = C.CLICPOPROC AND B.ALICPOANOP = C.ALICPOANOP ";
$sql .= "   AND B.CCOMLICODI = C.CCOMLICODI AND B.CGREMPCODI = C.CGREMPCODI ";
$sql .= "   AND B.CORGLICODI = C.CORGLICODI AND B.CFASESCODI = C.CFASESCODI ";
$sql .= " WHERE B.CLICPOPROC = $Processo AND B.ALICPOANOP = $ProcessoAno ";
$sql .= "   AND B.CCOMLICODI = $ComissaoCodigo AND B.CGREMPCODI = $GrupoCodigo ";
$sql .= "   AND B.CFASESCODI = A.CFASESCODI AND A.CFASESCODI = 13 "; // Só a fase de Homologação
$sql .= " ORDER BY A.AFASESORDE";
$result = $db->query($sql);
if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
}
$Rows = $result->numRows();
if ($Rows > 0) {
    $tpl->TITULO_DOCUMENTOS = "DOCUMENTOS ANEXADOS";
    
    if ($Mens2 == 1) {
        ExibeMens($Mensagem, $Tipo);
    }
    // Pega a(s) ata(s) de registro de preços - documentos #
    $sql = "SELECT CATARPCODI, EATARPNOME ";
    $sql .= "  FROM SFPC.TBATAREGISTROPRECO";
    $sql .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
    $sql .= "   AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = $GrupoCodigo ";
    $result = $db->query($sql);
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
    } else {
        $Rows = $result->numRows();
        resetArquivoAcesso();
        while ($cols = $result->fetchRow()) {
			$cont ++;
            $dadosP[$cont - 1] = "$cols[0];$cols[1];$cols[2]";
		}
		// Mostra os Documentos relacionados com a Licitação #
        if ($Rows > 0) {
            for ($Row = 0; $Row < $Rows; $Row ++) {
				$Linha = explode(";", $dadosP[$Row]);
                $ArqUpload = "registropreco/ATAREGISTROPRECO" . $GrupoCodigo . "_" . $Processo . "_" . $ProcessoAno . "_" . $ComissaoCodigo . "_" . $OrgaoLicitanteCodigo . "_" . $Linha[0];
                $Arq = $GLOBALS["CAMINHO_UPLOADS"] . $ArqUpload;
                
                if (file_exists($Arq)) {
                    $tamanho = filesize($Arq) / 1024;
                    addArquivoAcesso($ArqUpload);
                    $Url = "ConsRegistroPrecoDownloadDoc.php?GrupoCodigo=$GrupoCodigo&Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&DocCodigo=$Linha[0]";
                    if (! in_array($Url, $_SESSION['GetUrl'])) {
                        $_SESSION['GetUrl'][] = $Url;
                    }
                    $documentos .= "<a href=\"$Url\" target=\"_blank\" class=\"textonormal\"><img src=\"../midia/disquete.gif\" border=\"0\"> $Linha[1]</a> - ";
                    $documentos .= sprintf("%01.1f", $tamanho);
                    $documentos .= " k <br>";
                } else {
                    $documentos .= "<img src=\"../midia/disqueteInexistente.gif\" border=\"0\"> $Linha[1] - <b>Arquivo não armazenado</b>";
                }
				
                if ($Linha[2] != "") {
                    $documentos .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Obs.: $Linha[2]";
					
                }
                $documentos .= "<br>\n";
            }
        } else {
            $documentos .= "<font class=\"textonegrito\">Nenhum Documento Relacionado!</font><br>&nbsp;\n";
        }
        $tpl->DOCUMENTOS = $documentos;
    }
}

$tpl->COMISSAO_CODIGO = ComissaoCodigo;
$tpl->ORGAO_LICITANTE_CODIGO = $OrgaoLicitanteCodigo;
$tpl->MODALIDADE_CODIGO = $ModalidadeCodigo;
$tpl->GRUPO_CODIGO = $GrupoCodigo;
$tpl->OBJETO = $Objeto;

$tpl->COMISSAO_PESQUISA = $ComissaoPesquisa;
$tpl->ORGAO_PESQUISA = $OrgaoPesquisa;
$tpl->OBJETO_PESQUISA = $ObjetoPesquisa;

echo $tpl->show();
