<?php
/**
 * Portal da DGCO.
 *
 * PHP version 5.2.5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category  Pitang Novo Layout
 *
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version   GIT: v1.13.0-41-gf34a9d8
 *
 * -----------------------------------------------------------------------------
 * HISTORICO DE ALTERACOES NO PROGRAMA
 * -----------------------------------------------------------------------------
 * Alterado: Pitang Agile TI <contato@pitang.com>
 * Data:     21/07/2015
 * Objetivo: CR 80716 - Avisos de Licitação - problema ao acessar um link de um processo
 * -----------------------------------------------------------------------------
 * Alterado: Pitang Agile TI <contato@pitang.com>
 * Data:     17/09/2015
 * Objetivo: CR 100458 - Mensagem de erro recorrente
 * Versão:   20150916_1550-1-gf471375
 * -----------------------------------------------------------------------------
 * Alterado: Lucsa Baracho
 * Data:     03/12/2018
 * Objetivo: Tarefa Redmine 207615
 * -----------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     15/02/2019
 * Objetivo: Tarefa Redmine 211801
 * -----------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     27/05/2019
 * Objetivo: Tarefa Redmine 217521
 * -----------------------------------------------------------------------------
 */

if (! @require_once dirname(__FILE__) . '/TemplateAppPadrao.php') {
    throw new Exception('Error Processing Request - TemplateAppPadrao.php', 1);
}

if (! @require_once dirname(__FILE__) . '/../licitacoes/funcoesLicitacoes.php') {
    throw new Exception('Error Processing Request - funcoesLicitacoes.php', 1);
}

/**
 *
 * @param array $dados            
 */
function loteTemItemGenerico($dados)
{
    $dao = Conexao();
    $sql = "
        SELECT COUNT(b.fmatepgene)
        FROM sfpc.tbitemlicitacaoportal a
        INNER JOIN sfpc.tbmaterialportal b ON a.cmatepsequ = b.cmatepsequ
        INNER JOIN sfpc.tbunidadedemedida c ON b.cunidmcodi = c.cunidmcodi
        LEFT JOIN sfpc.tbfornecedorcredenciado d ON a.aforcrsequ = d.aforcrsequ
        WHERE a.clicpoproc= %d
            AND  a.alicpoanop= %d
            AND a.cgrempcodi= %d
            AND a.ccomlicodi= %d
            AND a.corglicodi= %d
            AND a.citelpnuml = %d
            AND b.fmatepgene LIKE 'S'
    ";
    
    return $dao->getOne(sprintf($sql, $dados[0], $dados[1], $dados[2], $dados[3], $dados[4], $dados[5]));
}

function consultarHistorico($tpl, $LicitacaoProcesso, $LicitacaoAno, $ComissaoCodigo, $GrupoCodigo, $OrgaoLicitanteCodigo) {
    $db = Conexao();

    $sql = "SELECT A.EFASESDESC, A.AFASESORDE, B.CLICPOPROC, B.ALICPOANOP, ";
    $sql .= "       B.CFASESCODI, B.EFASELDETA, B.TFASELDATA, C.CATASFCODI, ";
    $sql .= "       C.EATASFNOME, C.eatasfobse, C.fatasfexcl, U.EUSUPORESP, C.TATASFULAT";
    $sql .= "  FROM SFPC.TBFASES A, SFPC.TBFASELICITACAO B LEFT OUTER JOIN SFPC.TBATASFASE C ";
    $sql .= "    ON B.CLICPOPROC = C.CLICPOPROC AND B.ALICPOANOP = C.ALICPOANOP ";
    $sql .= "   AND B.CCOMLICODI = C.CCOMLICODI AND B.CGREMPCODI = C.CGREMPCODI ";
    $sql .= "   AND B.CORGLICODI = C.CORGLICODI AND B.CFASESCODI = C.CFASESCODI ";
    $sql .= "       LEFT OUTER JOIN SFPC.TBUSUARIOPORTAL U ON C.CUSUPOCODI = U.CUSUPOCODI";
    $sql .= " WHERE B.CLICPOPROC = $LicitacaoProcesso AND B.ALICPOANOP = $LicitacaoAno ";
    $sql .= "   AND B.CCOMLICODI = $ComissaoCodigo AND B.CGREMPCODI = $GrupoCodigo ";
    $sql .= "   AND B.CFASESCODI = A.CFASESCODI AND A.CFASESCODI <> 1 "; // Menos a fase Interna
    $sql .= " ORDER BY B.TFASELDATA ASC";

    $result = $db->query($sql);

    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
    }

    $resultadoFases = $db->query($sql);
    $totalLinhas = $resultadoFases->numRows();
    $totalAtasNaHomologacao = 0; // Acumulador de total de atas na fase de homologação
    $codigoAta = "";
    $faseCod = "";

    if ($totalLinhas > 0) {
        while ($linhaFase = $resultadoFases->fetchRow()) {
            $descricaoFase = $linhaFase[0];
            $codigoAta = $linhaFase[7];
            $nomeAta = $linhaFase[8];
            $faseCod = $linhaFase[4];

            if ($descricaoFase == "HOMOLOGAÇÃO" && $codigoAta != "" && $nomeAta != "") {
                $totalAtasNaHomologacao ++;
            }
        }

        // Exibe link direto para o único arquivo
        if ($totalAtasNaHomologacao == 1) {
            $ArqUpload = "licitacoes/" . "ATASFASE" . $GrupoCodigo . "_" . $LicitacaoProcesso . "_" . $LicitacaoAno . "_" . $ComissaoCodigo . "_" . $OrgaoLicitanteCodigo . "_" . $faseCod . "_" . $codigoAta;
            $Arquivo = $GLOBALS["CAMINHO_UPLOADS"] . $ArqUpload;
            addArquivoAcesso($ArqUpload);
        
            $Url = "../licitacoes/ConsAcompDownloadAtas.php?GrupoCodigo=$GrupoCodigo&Processo=$LicitacaoProcesso&ProcessoAno=$LicitacaoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&FaseCodigo=$faseCod&AtaCodigo=$codigoAta";
        
            if (! in_array($Url, $_SESSION['GetUrl'])) {
                $_SESSION['GetUrl'][] = $Url;
            }
        
            $tpl->URL_DIRETA_ATA = $Url;
            $tpl->block("PROCESSO_LICITATORIO");
        }
    
        // Caso exista mais de uma ata na fase de homologação será exibido um link para um popup
        if ($totalAtasNaHomologacao > 1) {
            $paramentrosConsultaDocumentos = "processo=$LicitacaoProcesso&ano=$LicitacaoAno&comissao=$ComissaoCodigo&grupo=$GrupoCodigo&orgaoLicitante=$OrgaoLicitanteCodigo";

            $tpl->EVENTOMUITOSPROCESSOS = "onclick=javascript:AbreJanelaItem(\'../licitacoes/ConsAcompDetalhesDocumentosResultadoProcessoLicitatorio.php?' . $paramentrosConsultaDocumentos . '\', 900, 350);" > $tpl->block("PROCESSO_LICITATORIO");
        }
    }

    $Rows = $result->numRows();

    if ($Rows > 0) {
        while ($Linha = $result->fetchRow()) {
            $FaseCodigo = $Linha[4];
            $DataFase = substr($Linha[6], 8, 2) . "/" . substr($Linha[6], 5, 2) . "/" . substr($Linha[6], 0, 4);
            $FaseDetalhamento = $Linha[5];
            $nomeAta = $Linha[8];
            $itemObservacao = " - <b>Observação/ Justificativa:</b> \"" . $Linha[9] . "\"";
            $itemExcluido = $Linha[10];
            $itemAutor = " - <b>Responsável:</b> \"" . $Linha[11] . "\"";
            $itemDataAlteracao = $Linha[12];
        
            if ($itemDataAlteracao < "2011-03-23") {
                $itemObservacao = "";
                $itemAutor = "";
            }
        
            $valor = "";
        
            if ($Linha[4] == $CodFaseAnterior) {
                $ArqUpload = "licitacoes/" . "ATASFASE" . $GrupoCodigo . "_" . $LicitacaoProcesso . "_" . $LicitacaoAno . "_" . $ComissaoCodigo . "_" . $OrgaoLicitanteCodigo . "_" . $FaseCodigo . "_" . $Linha[7];
                $Arquivo = $GLOBALS["CAMINHO_UPLOADS"] . $ArqUpload;
                addArquivoAcesso($ArqUpload);
            
                if ($itemExcluido == "S") {
                    $valor .= "<s><br><img src='../midia/disqueteInexistente.gif' border='0'><font color=\"#000000\"> $nomeAta </font></s> $itemAutor $itemObservacao <b>(excluído)</b><br/>";
                } elseif (file_exists($Arquivo)) {
                    $Url = "../licitacoes/ConsAcompDownloadAtas.php?GrupoCodigo=$GrupoCodigo&Processo=$LicitacaoProcesso&ProcessoAno=$LicitacaoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&FaseCodigo=$FaseCodigo&AtaCodigo=$Linha[7]";
                
                    if (! in_array($Url, $_SESSION['GetUrl'])) {
                        $_SESSION['GetUrl'][] = $Url;
                    }
                
                    $valor .= "<br><a href='$Url'><img src=../midia/disquete.gif border=0> <font color='#000000'> $nomeAta </font></a> $itemAutor $itemObservacao<br/>";
                } else {
                    $valor .= "<br><img src='../midia/disqueteInexistente.gif' border='0'><font color=\"#000000\"> $nomeAta </font> $itemAutor $itemObservacao <b>(arquivo não armazenado 1)</b><br/>";
                }
            } else {
                $valor .= "<tr>\n";
                $DataFase = substr($Linha[6], 8, 2) . "/" . substr($Linha[6], 5, 2) . "/" . substr($Linha[6], 0, 4);
                $valor .= "<td colspan='3' style='text-align: left'>$Linha[0]</td>\n";
                $valor .= "<td colspan='3' style='text-align: left'>$DataFase</td>\n";
                $valor .= "<td colspan='3' style='text-align: left'>$Linha[5]&nbsp;</td>\n";
            
                if ($Linha[7] != 0) {
                    $ArqUpload = "licitacoes/" . "ATASFASE" . $GrupoCodigo . "_" . $LicitacaoProcesso . "_" . $LicitacaoAno . "_" . $ComissaoCodigo . "_" . $OrgaoLicitanteCodigo . "_" . $FaseCodigo . "_" . $Linha[7];
                    $Arquivo = $GLOBALS["CAMINHO_UPLOADS"] . $ArqUpload;
                    addArquivoAcesso($ArqUpload);
                
                    if ($itemExcluido == "S") {
                        $valor .= "<td style=\"text-align: left\" colspan=\"3\"><img src='../midia/disqueteInexistente.gif' border='0'><s><font color=\"#000000\"> $nomeAta</font></s> $itemAutor $itemObservacao <b>(excluído)</b><br/>";
                    } elseif (file_exists($Arquivo)) {
                        $Url = "../licitacoes/ConsAcompDownloadAtas.php?GrupoCodigo=$GrupoCodigo&Processo=$LicitacaoProcesso&ProcessoAno=$LicitacaoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&FaseCodigo=$FaseCodigo&AtaCodigo=$Linha[7]";

                        if (! in_array($Url, $_SESSION['GetUrl'])) {
                            $_SESSION['GetUrl'][] = $Url;
                        }

                        $valor .= "<td colspan='3' style='text-align: left'><a href='$Url'><img src=../midia/disquete.gif border=0> <font color='#000000'> $nomeAta </font></a> $itemAutor $itemObservacao<br/>";
                    } else {
                        $valor .= "<td colspan='3' style='text-align: left'><img src='../midia/disqueteInexistente.gif' border='0'><font color=\"#000000\"> $nomeAta</font> $itemAutor $itemObservacao <b>(arquivo não armazenado 2)</b><br/>";
                    }
                } else {
                    $valor .= "<td colspan='3' style='text-align: left'>&nbsp;</td>";
                }
            }

            $tpl->BLOCOTOTAL = $valor;
            $tpl->block("BLOCO_LISTA_FASES");
            $tpl->BLOCOTOTAL = "";
            $CodFaseAnterior = $Linha[4];
        }
    
        $tpl->block("BLOCO_TABELA_FINAL");
    }
}

/**
 */
function proccessPrincipal()
{
    $tpl = new TemplateAppPadrao('templates/ConsAvisosDocumentos.html', 'ConsAvisosDocumentos');
    $ErroPrograma = __FILE__;
    
    // Variáveis com o global off #
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $Mensagem             = $_POST['Mensagem'];
        $Tipo                 = $_POST['Tipo'];
        $Objeto               = $_POST['Objeto'];
        $OrgaoLicitanteCodigo = $_POST['OrgaoLicitanteCodigo'];
        $ComissaoCodigo       = $_POST['ComissaoCodigo'];
        $ModalidadeCodigo     = $_POST['ModalidadeCodigo'];
        $GrupoCodigo          = $_POST['GrupoCodigo'];
        $LicitacaoProcesso    = $_POST['LicitacaoProcesso'];
        $LicitacaoAno         = $_POST['LicitacaoAno'];
        $LicitacaoAnoAux      = $_POST['LicitacaoAno'];
    } else {
        $Acesso = $_GET['Acesso'];
        
        if ($Acesso == 'INTERNET') {
            TiraSeguranca();
        }
    }
    // redirecionar para Pesquisar, caso dados necessários para renderizar a página não forem especificados
    if ((empty($GrupoCodigo)) && (empty($ComissaoCodigo)) && (empty($LicitacaoProcesso)) && (empty($LicitacaoAno)) && (empty($OrgaoLicitanteCodigo))) {
        header('Location: ' . $GLOBALS['DNS_SISTEMA'] . getPathNovoLayout() . '/ConsAvisosPesquisar.php');
        exit();
    }
    
    $database = Conexao();
    
    $sql  = "SELECT A.EGREMPDESC, B.EMODLIDESC, C.ECOMLIDESC, D.CLICPOCODL, ";
    $sql .= "       D.ALICPOANOL, D.XLICPOOBJE, E.EORGLIDESC, D.TLICPODHAB, ";
    $sql .= "       C.NCOMLIPRES, C.ECOMLILOCA, C.ACOMLIFONE, C.ACOMLINFAX, ";
    $sql .= "       D.VLICPOVALE, E.FORGLIEXVE , C.CCOMLICODI ";
    $sql .= " FROM   SFPC.TBGRUPOEMPRESA A, SFPC.TBMODALIDADELICITACAO B, SFPC.TBCOMISSAOLICITACAO C, ";
    $sql .= "       SFPC.TBLICITACAOPORTAL D, SFPC.TBORGAOLICITANTE E ";
    $sql .= "WHERE  A.CGREMPCODI = D.CGREMPCODI ";
    $sql .= "       AND D.CGREMPCODI = $GrupoCodigo  ";
    $sql .= "       AND D.CMODLICODI = B.CMODLICODI ";
    $sql .= "       AND C.CCOMLICODI = D.CCOMLICODI ";
    $sql .= "       AND D.CCOMLICODI = $ComissaoCodigo ";
    $sql .= "       AND D.CLICPOPROC = $LicitacaoProcesso ";
    $sql .= "       AND D.ALICPOANOP = $LicitacaoAno ";
    $sql .= "       AND E.CORGLICODI = D.CORGLICODI ";
    $sql .= "       AND D.CORGLICODI = $OrgaoLicitanteCodigo";
    //46
    $result = $database->query($sql);
    
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\nIP: " . $_SERVER['REMOTE_ADDR']);
    } else {
        $Rows = $result->numRows();
        
        while ($Linha = $result->fetchRow()) {
            $ModalidadeDescricao     = $Linha[1];
            $ComissaoDescricao       = $Linha[2];
            $NLicitacao              = substr($Linha[3] + 10000, 1);
            $LicitacaoAno            = $Linha[4];
            $ObjetoLicitacao         = $Linha[5];
            $OrgaoLicitanteDescricao = $Linha[6];
            $LicitacaoDtAbertura     = substr($Linha[7], 8, 2) . '/' . substr($Linha[7], 5, 2) . '/' . substr($Linha[7], 0, 4);
            $LicitacaoHoraAbertura   = substr($Linha[7], 11, 5);
            $nomePresidente          = $Linha[8];
            $endereco                = $Linha[9];
            $telefone                = $Linha[10];
            $fax                     = $Linha[11];
            $valorEstimado           = converte_valor($Linha[12]);
            $exibicaoValor           = $Linha[13];
            $comissao   = $Linha[14];
        }
    }
    
    $LicitacaoProcesso = substr($LicitacaoProcesso + 10000, 1);
    $existeDadosExtras = false;
    
    if (! empty($nomePresidente)) {
        $tpl->VALOR_PRESIDENTE = "PRESIDENTE: $nomePresidente";
        $tpl->block('BLOCO_NOME_PRESIDENTE');
    }
    
    if (! empty($endereco)) {
        $tpl->VALOR_ENDERECO = "ENDEREÇO: $endereco";
        $existeDadosExtras = true;
    }
    
    if (! empty($telefone)) {
        $tpl->VALOR_TELEFONE = "- TEL: $telefone";
        $existeDadosExtras = true;
    }
    
    if (! empty($fax)) {
        $tpl->VALOR_FAX = "- FAX: $fax";
        $existeDadosExtras = true;
    }
    
    if ($existeDadosExtras) {
        $tpl->block('BLOCO_DADOS_EXTRAS');
    }
    
    $tpl->VALOR_COMISSAO        = $ComissaoDescricao;
    $tpl->VALOR_MODALIDADE      = $ModalidadeDescricao;
    $tpl->VALOR_PROCESSO        = $LicitacaoProcesso . '/' . $LicitacaoAno;
    $tpl->VALOR_LICITACAO       = $NLicitacao . '/' . $LicitacaoAno;
    $tpl->VALOR_OBJETO          = $ObjetoLicitacao;
    $tpl->VALOR_DATA            = $LicitacaoDtAbertura;
    $tpl->VALOR_HORA            = $LicitacaoHoraAbertura;
    $tpl->VALOR_ORGAO_LICITANTE = $OrgaoLicitanteDescricao;
    
    if ($exibicaoValor != "N" &&  $comissao != "46") {
        $tpl->NOME_CAMPO = 'VALOR ESTIMADO TOTAL';
        $tpl->VALOR_ESTIMADO_TOTAL = 'R$ ' . $valorEstimado;
        $tpl->block('BLOCO_VALOR_ESTIMADO');
    }
    
    
    $tpl->block('BLOCO_AVISO_DE_LICITACAO');
    
    // Verificar se Licitação tem resultado
    $sql  = ' SELECT flicporesu AS resultado ';
    $sql .= ' FROM sfpc.tblicitacaoportal ';
    $sql .= ' WHERE ';
    $sql .= " clicpoproc = $LicitacaoProcesso";
    $sql .= ' AND alicpoanop = ' . $LicitacaoAnoAux;
    $sql .= ' AND cgrempcodi = ' . $GrupoCodigo;
    $sql .= ' AND ccomlicodi = ' . $ComissaoCodigo;
    $sql .= ' AND corglicodi = ' . $OrgaoLicitanteCodigo;
    
    $result = executarTransacao($database, $sql);

    $row = $result->fetchRow(DB_FETCHMODE_OBJECT);
    
    $licitacaoComResultado = false;

    if ($row->resultado == 'S') {
        $licitacaoComResultado = true;
    }
    
    // Verificar ultim afase da licitação   
    $ultimaFase = ultimaFase($LicitacaoProcesso, $LicitacaoAno, $GrupoCodigo, $ComissaoCodigo, $OrgaoLicitanteCodigo, $database);
    
    // SQL para capturar os itens de material da licitação
    $sql = "SELECT  A.AITELPORDE, B.EMATEPDESC, A.CMATEPSEQU, C.EUNIDMDESC, A.AITELPQTSO, A.CITELPNUML,
                    D.AFORCRSEQU, D.NFORCRRAZS, D.NFORCRFANT, D.AFORCRCCGC, A.EITELPDESCMAT, A.EITELPDESCSE, B.FMATEPGENE
            FROM    SFPC.TBITEMLICITACAOPORTAL A
                    INNER JOIN SFPC.TBMATERIALPORTAL B ON A.CMATEPSEQU = B.CMATEPSEQU
                    INNER JOIN SFPC.TBUNIDADEDEMEDIDA C ON B.CUNIDMCODI = C.CUNIDMCODI
                    LEFT JOIN SFPC.TBFORNECEDORCREDENCIADO D ON A.AFORCRSEQU = D.AFORCRSEQU
            WHERE   A.CLICPOPROc=$LicitacaoProcesso
                    AND A.ALICPOANOP = $LicitacaoAnoAux
                    AND A.CGREMPCODI = $GrupoCodigo
                    AND A.CCOMLICODI = $ComissaoCodigo
                    AND A.CORGLICODI = $OrgaoLicitanteCodigo
            ORDER BY 6,1 ";
    
    $result = $database->query($sql);
    $Rows = $result->numRows();

    // Se encontrar pelo menos uma linha exibir grade com Itens
    if ($Rows > 0) {
        $numLoteMatAntes = '999';
        
        while ($Linha = $result->fetchRow()) {
            $ordMaterial    = $Linha[0];
            $descMaterial   = $Linha[1];
            $seqMaterial    = $Linha[2];
            $unidMaterial   = $Linha[3];
            $qtdMaterial    = $Linha[4];
            $numLoteMat     = $Linha[5];
            $razaoSocForMat = $Linha[7];
            $cgcForCredMat  = $Linha[9];
            $descDetMat     = $Linha[10];
            $descDetServ    = $Linha[11];
            $generico       = $Linha[12];
            
            $processoLicitatorioLote = array($LicitacaoProcesso, $LicitacaoAnoAux, $GrupoCodigo, $ComissaoCodigo, $OrgaoLicitanteCodigo, $numLoteMat);
            $temDescriaoDetalhada = 0;

            if ($numLoteMat != $numLoteMatAntes) {
                $numLoteMatAntes = $numLoteMat;
                
                if ($licitacaoComResultado and $ultimaFase == 13 and ! empty($razaoSocForMat)) {
                    $soma = getTotalValorLogrado($database, $LicitacaoProcesso, $LicitacaoAnoAux, $GrupoCodigo, $ComissaoCodigo, $OrgaoLicitanteCodigo, $numLoteMat);
                    $tpl->VALOR_LOTE_MATERIAIS = 'LOTE ' . ($numLoteMat) . ' FORNECEDOR VENCEDOR: ' . FormataCpfCnpj($cgcForCredMat) . ' - ' . ($razaoSocForMat) . ' - ' . 'R$ ' . (number_format((float) $soma, 2, ',', '.'));
                } else {
                    $tpl->VALOR_LOTE_MATERIAIS = 'LOTE ' . ($numLoteMat);
                }
                
                $temDescriaoDetalhada = loteTemItemGenerico($processoLicitatorioLote);
                $tpl->COLSPAN = 8;
                
                if ($temDescriaoDetalhada > 0) {
                    $tpl->COLSPAN = 4;
                    $tpl->block('BLOCO_DESCRICAO_DETALHADA_TITULO');
                }
                
                $tpl->block('BLOCO_LOTE_MATERIAL');
            }
            
            $tpl->VALOR_ORD_MATERIAIS = $ordMaterial;
            $tpl->VALOR_DESC_ITEM_MATERIAIS = $descMaterial;
            
            $temDescriaoDetalhada = loteTemItemGenerico($processoLicitatorioLote);
            
            if ($temDescriaoDetalhada > 0) {
                $tpl->VALOR_DESC_DETALHADA = $generico == 'S' ? strtoupper2($descDetMat) : '---';
                $tpl->block('BLOCO_DESCRICAO_DETALHADA_DADO');
            }
            
            $tpl->VALOR_COD_ITEM_MATERIAIS = $seqMaterial;
            $tpl->VALOR_UNIDADE_MATERIAIS = $unidMaterial;
            $tpl->VALOR_QUANTIDADE_MATERIAIS = number_format($qtdMaterial, '4', ',', '.');
            
            $tpl->block('BLOCO_DADOS_ITENS_MATERIAIS_LICITACAO');
        }
        $tpl->block('BLOCO_ITENS_MATERIAIS_LICITACAO');
    }
    
    // SQL para capturar os itens de serviço da licitação
    $sql = ' SELECT a.aitelporde, b.eservpdesc, a.cservpsequ, a.citelpnuml, c.aforcrsequ, ';
    $sql .= ' c.nforcrrazs, c.nforcrfant, c.aforcrccgc,lp.xlicpoobje, a.eitelpdescse ';
    $sql .= ' FROM sfpc.tbitemlicitacaoportal a LEFT JOIN sfpc.tbfornecedorcredenciado c ';
    $sql .= ' ON a.aforcrsequ = c.aforcrsequ';
    $sql .= ' JOIN sfpc.tbservicoportal b ';
    $sql .= ' on b.cservpsequ = a.cservpsequ ';
    $sql .= 'LEFT JOIN sfpc.tblicitacaoportal lp';
    $sql .= ' ON lp.clicpoproc = a.clicpoproc';
    $sql .= ' AND lp.alicpoanop = a.alicpoanop';
    $sql .= ' AND lp.cgrempcodi = a.cgrempcodi';
    $sql .= ' AND lp.ccomlicodi = a.ccomlicodi';
    $sql .= ' AND lp.corglicodi = a.corglicodi';
    $sql .= ' WHERE ';
    $sql .= ' a.clicpoproc=' . $LicitacaoProcesso;
    $sql .= ' AND  a.alicpoanop=' . $LicitacaoAnoAux;
    $sql .= ' AND a.cgrempcodi=' . $GrupoCodigo;
    $sql .= ' AND a.ccomlicodi=' . $ComissaoCodigo;
    $sql .= ' AND a.corglicodi=' . $OrgaoLicitanteCodigo;
    $sql .= ' ORDER BY 4,1 ';
    
    $result = $database->query($sql);
    
    $Rows = $result->numRows();
    
    // - Se encontrar pelo menos uma linha exibir grade com Itens    
    $Objeto = '';

    if ($Rows > 0) {
        $numLoteServAntes = '999';
        
        while ($Linha = $result->fetchRow()) {
            $ordServico      = $Linha[0];
            $descServico     = $Linha[1];
            $seqServico      = $Linha[2];
            $numLoteServico  = $Linha[3];
            $razaoSocForServ = $Linha[5];
            $cgcForCredServ  = $Linha[7];
            
            if (empty($Objeto)) {
                $Objeto = $Linha[8];
            }
            
            $descDetServ = $Linha[9];
            
            if ($numLoteServico != $numLoteServAntes) {
                $numLoteServAntes = $numLoteServico;
                
                if ($licitacaoComResultado and $ultimaFase == 13 and ! empty($razaoSocForServ)) {
                    $soma = getTotalValorServico($database, $LicitacaoProcesso, $LicitacaoAnoAux, $GrupoCodigo, $ComissaoCodigo, $OrgaoLicitanteCodigo, $numLoteServico);
                    $tpl->VALOR_LOTE_SERVICO = 'LOTE ' . ($numLoteServico) . ' FORNECEDOR VENCEDOR: ' . FormataCpfCnpj($cgcForCredServ) . ' - ' . ($razaoSocForServ) . ' - ' . ($razaoSocForServ) . ' - ' . 'R$ ' . (number_format((float) $soma, 2, ',', '.'));
                } else {
                    $tpl->VALOR_LOTE_SERVICO = 'LOTE ' . ($numLoteServico);
                }
                $tpl->block('BLOCO_LOTE_CABECALHO');
            }
            $tpl->VALOR_ORD_SERVICO = $ordServico;
            $tpl->VALOR_DESC_ITEM_SERVICO = $descServico;
            $tpl->VALOR_DESC_DETALHADA_SERVICO = strtoupper2($descDetServ);
            $tpl->VALOR_COD_SERVICO = $seqServico;
            
            $tpl->block('BLOCO_DADOS_ITENS_SERVICO_LICITACAO');
            $tpl->block('BLOCO_TABELA_RESULTADO');
        }
        $tpl->block('BLOCO_ITENS_SERVICO_LICITACAO');
    }
    
    // Final Trecho de código inserido por Heraldo  
    if ($Mens2 == 1) {
        ExibeMens($Mensagem, $Tipo);
    }

    $sql = 'SELECT CDOCLICODI, EDOCLINOME, EDOCLIOBSE, fdocliexcl, u.eusuporesp, tdocliulat  ';
    $sql .= '  FROM SFPC.TBDOCUMENTOLICITACAO d, sfpc.tbusuarioportal u ';
    $sql .= " WHERE CLICPOPROC = $LicitacaoProcesso AND ALICPOANOP = $LicitacaoAno ";
    $sql .= "   AND CCOMLICODI = $ComissaoCodigo    AND d.CGREMPCODI = $GrupoCodigo AND u.cusupocodi = d.cusupocodi";
      
    if ($_SESSION['_cperficodi_'] == null or ($_SESSION['_cperficodi_'] != 7 and $_SESSION['_cperficodi_'] != 18)) {
        $sql .= " AND ( NOT ( (edoclinome ~* '^RESULTADO_') OR (edoclinome ~* '^ORCAMENTO_') )  ) ";
    }
    
    $result = $database->query($sql);
    
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\nIP: " . $_SERVER['REMOTE_ADDR']);
    } else {
        $Rows = $result->numRows();
        
        if ($Rows > 0) {
            resetArquivoAcesso();
            
            while ($Linha = $result->fetchRow()) {
                $itemCodigo        = $Linha[0];
                $itemNome          = $Linha[1];
                $itemObservacao    = $Linha[2];
                $itemExcluido      = $Linha[3];
                $itemAutor         = $Linha[4];
                $itemDataAlteracao = $Linha[5];
                
                $ArqUpload = 'licitacoes/' . 'DOC' . $GrupoCodigo . '_' . $LicitacaoProcesso . '_' . $LicitacaoAno . '_' . $ComissaoCodigo . '_' . $OrgaoLicitanteCodigo . '_' . $itemCodigo;
                $Arq = $GLOBALS['CAMINHO_UPLOADS'] . $ArqUpload;
                addArquivoAcesso($ArqUpload);
                
                $itemArquivoExiste = true;
                $tamanho = '';
                
                if (! file_exists($Arq)) {
                    $itemArquivoExiste = false;
                } else {
                    $tamanho = filesize($Arq) / 1024;
                }
                
                if ($itemExcluido == 'S') {
                    $itemNome = "<s style='text-decoration:line-through;'>" . $itemNome . '</s> <b>(excluído)</b>';
                } elseif (!$itemArquivoExiste) {
                    $itemNome = '' . $itemNome . ' <b>(arquivo não armazenado 3)</b>';
                } else {
                    $itemNome = "<a href=\"#\" onclick=\"AbreDocumentos('$OrgaoLicitanteCodigo','$ComissaoCodigo','$ModalidadeCodigo','$GrupoCodigo','$LicitacaoProcesso','$LicitacaoAno','$itemCodigo');\" class=\"textonormal\">" . $itemNome . '</a>';
                }
                
                // Autor e observação de documentos de antes da melhoria não devem ser mostrados
                if ($itemDataAlteracao < '2011-03-23') {
                    $itemAutor = '---';
                    $itemObservacao = '---';
                }
                
                if ($itemExcluido != 'S' and $itemArquivoExiste) {
                    $tpl->VALOR_OBJETO_ARQUIVO = $itemCodigo;
                    $tpl->ABREDOCUMENTO = "AbreDocumentos('$OrgaoLicitanteCodigo','$ComissaoCodigo','$ModalidadeCodigo','$GrupoCodigo','$LicitacaoProcesso','$LicitacaoAno','$itemCodigo')";
                    
                    $tpl->block('BLOCO_LINK_ARQUIVO_EXISTENTE');
                } else {
                    $tpl->block('BLOCO_LINK_ARQUIVO_INEXISTENTE');
                }
                
                $tpl->VALOR_DOCUMENTO_DOCUMENTOS_RELACIONADOS = $itemNome;
                
                if ($itemArquivoExiste) {
                    $tpl->VALOR_TAMANHO_DOCUMENTOS_RELACIONADOS = intval($tamanho) . ' Kbytes';
                }
                
                $tpl->VALOR_RESPONSAVEL_DOCUMENTOS_RELACIONADOS = $itemAutor;
                $tpl->VALOR_OBSERVACAO_JUSTIFICATIVA_DOCUMENTOS_RELACIONADOS = $itemObservacao;
                
                $tpl->block('BLOCO_ARQUIVOS_DOCUMENTOS_RELACIONADOS');
            }
        } else {
            $tpl->block('BLOCO_ARQUIVOS_DOCUMENTOS_RELACIONADOS_INEXISTENTE');
        }
        $tpl->block('BLOCO_DOCUMENTOS_RELACIONADOS');
    }

    // Histórico
    consultarHistorico($tpl, $LicitacaoProcesso, $LicitacaoAno, $ComissaoCodigo, $GrupoCodigo, $OrgaoLicitanteCodigo);
    
    $tpl->VALOR_OBJETO             = $Objeto;
    $tpl->VALOR_ORGAO_LICITANTE    = $OrgaoLicitanteCodigo;
    $tpl->VALOR_COMISSAO_CODIGO    = $ComissaoCodigo;
    $tpl->VALOR_MODALIDADE_CODIGO  = $ModalidadeCodigo;
    $tpl->VALOR_GRUPO_CODIGO       = $GrupoCodigo;
    $tpl->VALOR_LICITACAO_PROCESSO = $LicitacaoProcesso;
    $tpl->VALOR_LICITACAO_ANO      = $LicitacaoAno;
    $tpl->VALOR_DOCUMENTO_CODIGO   = $DocumentoCodigo;
    
    $tpl->show();
}

/**
 * [frontController description].
 *
 * @return [type] [description]
 */
function frontController()
{
    $botao = isset($_REQUEST['BotaoAcao']) ? $_REQUEST['BotaoAcao'] : 'Principal';
    
    switch ($botao) {
        case 'Pesquisar':
            processPesquisar();
            break;
        case 'LimparTela':
            proccessPrincipal();
            break;
        case 'Principal':
        default:
            proccessPrincipal();
    }
}

frontController();
