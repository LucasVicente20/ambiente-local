<?php
/**
 * Portal da DGCO
 *
 * PHP version 5.2.5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category  Pitang Registro Preço
 * @package   registropreco
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 * @version   Git: $Id:$
 */

 // 220038--
 
if (! @require_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}

/**
 * CadRegistroPrecoIntencaoIncluir
 *
 * Class application
 */
class CadAtaRegistroPrecoExternaSelecionarIncluir extends AbstractApplication
{
    /**
     * Proccess flow Principal
     *
     * @param [type] $variablesGlobals
     *            [description]
     */
    private function proccessPrincipal()
    {
        $itensAta = array();
        $this->plotarBlocoProcesso($processos);
        
        $this->collectorSessionItem();
        $itensSelecionado = $this->collectorListTableIntencaoItem(0);
        
        if (sizeof($itensSelecionado)) {
            $ata = $this->atualizaValoresAtaTela();
            $ata = $this->arrayParaObject($ata);
            $this->plotarBlocoProcesso($ata);
            
            $itens = $this->atualizaValoresItemTela();
            $itens = $this->recuperarValorItens();
            
            $valorMaior = 0;
            if ($itens != null) {
                $valorMaior = $itens[sizeof($itens) - 1]->aitarporde;
            }
            
            $itensSelecionado = $this->collectorListTableIntencaoItem($valorMaior);
            $itens = array_merge($itens, $itensSelecionado);
            $this->plotarBlocoItem($itens);
            
            $this->getTemplate()->block("BLOCO_FORMULARIO_MANTER");
            return;
        }
        
        $this->plotarBlocoItem($itensSelecionado);
        $this->getTemplate()->block("BLOCO_FORMULARIO_MANTER");
    }

    /**
     * Carregar Ano
     */
    private function carregarAno()
    {
        $anoAtual = (int) date('Y');
        $anos = array();
        for ($i = 0; $i < 3; $i ++) {
            array_push($anos, strval($anoAtual - $i));
        }
        return $anos;
    }

    private function plotarBlocoModalidade(array $modalidades, $valorAtual)
    {
        foreach ($modalidades as $modalidade) {
            $this->getTemplate()->MODALIDADE_VALUE = $modalidade->cmodlicodi;
            $this->getTemplate()->MODALIDADE_TEXT = $modalidade->emodlidesc;
            
            // Vendo se a opção atual deve ter o atributo "selected"

            if ($valorAtual != null) {
                if ($value == $valorAtual) {
                    $this->getTemplate()->MODALIDADE_SELECTED = "selected";
                }
            }
            
            if ($valorAtual == $modalidade->cmodlicodi) {
                $this->getTemplate()->MODALIDADE_SELECTED = "selected";
            }

            // Caso esta não seja a opção atual, limpamos o valor da variável SELECTED
            else {
                $this->getTemplate()->clear("MODALIDADE_SELECTED");
            }
            
            $this->getTemplate()->block("BLOCO_MODALIDADE");
        }
    }

    /**
     * Coleta dados do CadItemIncluir que foram setado em session['item']
     * e move para session['intencaoItem']
     */
    /**
     * Coleta dados do CadItemIncluir que foram setado em session['item']
     * e move para session['intencaoItem']
     */
    private function collectorSessionItem()
    {
        if (isset($this->variables['session']['item'])) {
            $countItem = count($this->variables['session']['item']);
            for ($i = 0; $i < $countItem; $i ++) {
                $newItem = $this->variables['session']['item'][$i];
                $this->variables['session']['intencaoItem'][] = $newItem;
            }
        }
        // cleaning for news itens
        unset($this->variables['session']['item']);
    }

    /**
     * Collector list table intencao item
     */
    private function collectorListTableIntencaoItem($ordemAtual)
    {
        $resultados = array();
        $countItem = count($this->variables['session']['intencaoItem']);
        
        if ($countItem > 0) {
            for ($i = 0; $i < $countItem; $i ++) {
                $ordemAtual ++;
                $this->initializeVariableItem();
                $dados = explode($this->variables['separatorArray'], $this->variables['session']['intencaoItem'][$i]);
                
                $descricao = explode($this->variables['separatorDesc'], $dados[0]);
                
                $item = array();
                
                $valorEstimado = $this->variables['post']['ValorUnitarioEstimado'][$i];
                
                $item['aitarporde'] = $ordemAtual;
                $item['cmatepsequ'] = $dados[3] == 'S' ? null : $dados[1];
                $item['cservpsequ'] = $dados[3] == 'S' ? $dados[1] : null;
                $item['aitarpqtor'] = "";
                $item['vitarpvori'] = converte_valor_estoques($valorEstimado / 10000);
                $item['citarpnuml'] = "";
                $item['aitarpqtat'] = "";
                $item['vitarpvatu'] = "";
                $item['eitarpdescse'] = $dados[3] == 'S' ? $descricao[0] : null;
                $item['eitarpdescmat'] = $dados[3] == 'S' ? null : $descricao[0];
                array_push($resultados, (object) $item);
            }
        }
        return $resultados;
    }

    private function retirarItem()
    {
        $ultimo = count($this->variables['session']['intencaoItem']) - 1;
        $valores = $this->variables['session']['intencaoItem'];
        
        unset($this->variables['session']['intencaoItem'][$ultimo]);
        
        $ata = $this->atualizaValoresAtaTela();
        $ata = $this->arrayParaObject($ata);
        $this->plotarBlocoProcesso($ata);
        
        $this->collectorSessionItem();
        $itensSelecionado = $this->collectorListTableIntencaoItem(0);
        $this->plotarBlocoItem($itensSelecionado);
        
        $this->getTemplate()->block("BLOCO_FORMULARIO_MANTER");
    }

    private function reiniciarTela()
    {
        $itens = $this->atualizaValoresItemTela();
        $ata = $this->atualizaValoresAtaTela();
        $ata = $this->arrayParaObject($ata);
        
        $this->plotarBlocoProcesso($ata);
        $itens = $this->recuperarValorItens();
        $this->plotarBlocoItem($itens);
    }

    private function validarInsercao($ata)
    {
        $mensagem = "";
        $valido = true;
        
        $linhaFim = '<br>';
        
        if ($ata['numeroAta'] == null) {
            $mensagem .= "Campo Número da Ata é de Preencimento Obrigatório" . $linhaFim;
            $valido = false;
        }
        if ($ata['ano'] == null) {
            $mensagem .= "Campo Ano da Ata é de Preencimento Obrigatório" . $linhaFim;
            $valido = false;
        }
        if ($ata['processo'] == null) {
            $mensagem .= "Campo Processo da Ata é de Preencimento Obrigatório" . $linhaFim;
            $valido = false;
        }
        if ($ata['orgao'] == null) {
            $mensagem .= "Campo Orgão da Ata é de Preencimento Obrigatório" . $linhaFim;
            $valido = false;
        }
        if ($ata['objeto'] == null) {
            $mensagem .= "Campo Objeto é de Preencimento Obrigatório" . $linhaFim;
            $valido = false;
        }
        if ($ata['dataInicial'] == null) {
            $mensagem .= "Campo Data Inicial é de Preencimento Obrigatório" . $linhaFim;
            $valido = false;
        }
        if ($ata['vigencia'] == null) {
            $mensagem .= "Campo Vigência é de Preencimento Obrigatório" . $linhaFim;
            $valido = false;
        }
        if ($ata['codigoFornecedor'] == null) {
            $mensagem .= "Campo Fornecedor Original é de Preencimento Obrigatório" . $linhaFim;
            $valido = false;
        }
        /* falta verificar o documento */
        $msgFinal = ExibeMensStr($mensagem, 0, 1);
        $msgFinal = str_replace('.', '', $msgFinal);
        
        $this->getTemplate()->MENSAGEM_ERRO = $msgFinal;
        $this->getTemplate()->block('BLOCO_ERRO', true);
        return $valido;
    }

    private function arrayParaObject($d)
    {
        return (object) $d;
    }

    private function plotarBlocoProcesso($processo)
    {
        $this->getTemplate()->NUMERO_ATA_EXTERNA = $processo->carpexcodn;
        $this->getTemplate()->ANO_ATA_EXTERNA = $processo->aarpexanon;
        $this->getTemplate()->PROCESSO_ATA_EXTERNA = $processo->earpexproc;
        
        $modalidades = $this->consultarModalidade();
        $this->plotarBlocoModalidade($modalidades, $processo->cmodlicodi);
        
        $this->getTemplate()->ORGAO_ATA_EXTERNA = $processo->earpexorgg;
        $this->getTemplate()->OBJETO_ATA_EXTERNA = $processo->earpexobje;
        $this->getTemplate()->DOCUMENTO_ATA_EXTERNA = "";
        $this->getTemplate()->DATA_ATA_EXTERNA = $processo->tarpexdini;
        $this->getTemplate()->VIGENCIA_ATA_EXTERNA = $processo->aarpexpzvg;
        
        if ($processo->aforcrsequ) {
            $fornecedorOrigin = $this->consultarFornecedor($processo->aforcrsequ);
        }
        
        if ($processo->aforcrsequ1) {
            $fornecedorAtual = $this->consultarFornecedor($processo->aforcrsequ1);
        }
        
        $fornecedorOrgiDoc = $fornecedorOrigin->aforcrccgc != null ? $fornecedorOrigin->aforcrccgc : $fornecedorOrigin->aforcrccpf;
        $fornecedorAtualDoc = $fornecedorAtual->aforcrccgc != null ? $fornecedorAtual->aforcrccgc : $fornecedorAtual->aforcrccp;
        
        $this->getTemplate()->FORNECEDOR_ORIGINAL_ATA_EXTERNA = $fornecedorOrgiDoc;
        $this->getTemplate()->CODIGO_FORNECEDOR_ORIGINAL = $fornecedorOrigin->aforcrsequ;
        
        $this->getTemplate()->FORNECEDOR_ATUAL_ATA_EXTERNA = $fornecedorAtualDoc;
        $this->getTemplate()->CODIGO_FORNECEDOR_ATUAL = $fornecedorAtual->aforcrsequ;
        
        $this->getTemplate()->VALOR_DESCRITIVO_FORNECEDOR_ORIGINAL = FormataCpfCnpj($fornecedorOrgiDoc) . " - " . $fornecedorOrigin->nforcrrazs;
        $this->getTemplate()->VALOR_DESCRITIVO_FORNECEDOR_LOGRADOURO = $fornecedorOrigin->eforcrlogr;
        
        $this->getTemplate()->VALOR_DESCRITIVO_FORNECEDOR_ATUAL = FormataCpfCnpj($fornecedorAtualDoc) . " - " . $fornecedorAtualDoc->nforcrrazs;
        $this->getTemplate()->VALOR_DESCRITIVO_FORNECEDOR_ATUAL_LOGRADOURO = $fornecedorAtualDoc->eforcrlogr;
        $this->getTemplate()->SITUACAO_ATA_EXTERNA = $processo->farpexsitu;
    }

    private function plotarBlocoUnicoItem($item)
    {
        $this->getTemplate()->ITESEQ = $item->citarpsequ;
        $this->getTemplate()->ORD_ITEM = $item->aitarporde;
        $this->getTemplate()->CADUS_ITEM = $item->cmatepsequ == null ? $item->cservpsequ : $item->cmatepsequ;
        $this->getTemplate()->DESCRICAO_ITEM = $item->cmatepsequ != null ? $item->eitarpdescmat : $item->eitarpdescse;
        $this->getTemplate()->UND_ITEM = "UN";
        $this->getTemplate()->ORIGINAL_ITEM = $item->aitarpqtor;
        $this->getTemplate()->VALOR_ORGINAL_ITEM = $item->vitarpvori;
        $this->getTemplate()->TOTAL_ITEM = $item->aitarpqtor * $item->vitarpvori;
        $this->getTemplate()->LOTE_ITEM = $item->citarpnuml;
        $this->getTemplate()->QTD_ATUAL_ITEM = $item->aitarpqtat;
        $this->getTemplate()->VALOR_UNITARIO_ITEM = $item->vitarpvatu;
        $this->getTemplate()->VALOR_TOTAL_ITEM = $item->aitarpqtat * $item->vitarpvatu;
        $this->getTemplate()->VALOR_TIPO = $item->cmatepsequ == null ? "S" : "M";
    }

    private function processImprimir()
    {
        $pdf = new PdfAtaRegistroPrecoExternaDetalhamento();
        $pdf->setAno($_SESSION['anoProcesso']);
        $pdf->setProcesso($_SESSION['processoExterno']);
        $pdf->gerarRelatorio();
    }

    private function plotarBlocoItem(array $itens)
    {
        $valorTotal = 0;
        
        if ($itens == null) {
            return;
        }
        
        foreach ($itens as $item) {
            $this->plotarBlocoUnicoItem($item);
            $valorTotal += $item->aitarpqtat * $item->vitarpvatu;
            ;
            $this->getTemplate()->block("BLOCO_ITEM");
        }
        $this->getTemplate()->TOTAL_ATA = converte_valor_estoques($valorTotal / 10000);
    }

    private function salvarDados()
    {
        $database = & Conexao();
        $database->autoCommit(false);
        
        $proximaAta = $this->consultarProximaAtaNova($database);
        
        $this->inserirAtaRegistroPrecoNova($proximaAta, $database);
        
        $this->atualizarAtaRegistroPrecoExterna($database, $proximaAta);
        
        $this->atualizarItemAtaRegistroPrecoExterna($database, $proximaAta);
        
        $commited = $database->commit();
        
        if ($commited instanceof DB_error) {
            $database->rollback();
            return;
        }
        
        unset($this->variables['session']['intencaoItem']);
        $this->plotarBlocoProcesso($processos);
    }

    private function atualizarAtaRegistroPrecoExterna($db, $sequencialAta)
    {
        $valoresAta = array();
        
        $valoresAta['numeroAta'] = $this->variables['post']['NumeroAta'];
        $valoresAta['ano'] = $this->variables['post']['anoAta'];
        $valoresAta['processo'] = $this->variables['post']['processoAta'];
        $valoresAta['modalidade'] = $this->variables['post']['modalidadeAta'];
        $valoresAta['orgao'] = $this->variables['post']['orgaoAta'];
        $valoresAta['objeto'] = $this->variables['post']['objetoAta'];
        $valoresAta['dataInicial'] = $this->variables['post']['dataInicialAta'];
        $valoresAta['vigencia'] = $this->variables['post']['vigenciaAta'];
        $valoresAta['codigoFornecedor'] = $this->variables['post']['codigoFornecedor'];
        $valoresAta['codigoFornecedorAtual'] = $this->variables['post']['codigoFornecedorAtual'];
        
        if (! $this->validarInsercao($valoresAta)) {
            $this->reiniciarTela();
            $this->getTemplate()->block("BLOCO_FORMULARIO_MANTER");
            return;
        }
        $this->alterarAtaExterna($valoresAta, $sequencialAta, $db);
    }

    private function atualizaValoresAtaTela()
    {
        $valoresAta = array();
        
        $valoresAta['carpexcodn'] = $this->variables['post']['NumeroAta'];
        $valoresAta['aarpexanon'] = $this->variables['post']['anoAta'];
        $valoresAta['earpexproc'] = $this->variables['post']['processoAta'];
        $valoresAta['cmodlicodi'] = $this->variables['post']['modalidadeAta'];
        $valoresAta['earpexorgg'] = $this->variables['post']['orgaoAta'];
        $valoresAta['earpexobje'] = $this->variables['post']['objetoAta'];
        $valoresAta['tarpexdini'] = $this->variables['post']['dataInicialAta'];
        $valoresAta['aarpexpzvg'] = $this->variables['post']['vigenciaAta'];
        $valoresAta['aforcrsequ'] = $this->variables['post']['codigoFornecedor'];
        $valoresAta['aforcrseq1'] = $this->variables['post']['codigoFornecedorAtual'];
        $valoresAta['farpexsitu'] = $this->variables['post']['SituacaoAta'];
        
        return $valoresAta;
    }

    private function atualizaValoresItemTela()
    {
        $valoresAta = array();
        
        $valoresAta['qtdOriginal'] = $this->variables['post']['NumeroAta'];
        $valoresAta['qtdAtual'] = $this->variables['post']['anoAta'];
        $valoresAta['valorOriginal'] = $this->variables['post']['processoAta'];
        $valoresAta['valorAtual'] = $this->variables['post']['modalidadeAta'];
        $valoresAta['lote'] = $this->variables['post']['orgaoAta'];
        $valoresAta['situacao'] = $this->variables['post']['objetoAta'];
        $valoresAta['dataInicial'] = $this->variables['post']['dataInicialAta'];
        
        return $valoresAta;
    }

    private function atualizarItemAtaRegistroPrecoExterna($database, $numeroAta)
    {
        $valoresAta = $this->recuperarValorItens();
        $seqItem = 1;
        foreach ($valoresAta as $item) {
            $this->alterarItemAtaExterna($item, $numeroAta, $seqItem, $database);
            $seqItem += $seqItem;
        }
    }

    private function ativarAtaExterna()
    {
        $numeroAta = $this->variables['post']['NumeroAta'];
        $this->alterarSituacaoAtaExterna($numeroAta, 'A');
        $this->reiniciarTela();
        
        $this->getTemplate()->SITUACAO_ATA_EXTERNA = 'A';
        $this->getTemplate()->block("BLOCO_FORMULARIO_MANTER");
    }

    private function inativarAtaExterna()
    {
        $numeroAta = $this->variables['post']['NumeroAta'];
        $this->alterarSituacaoAtaExterna($numeroAta, 'I');
        $this->reiniciarTela();
        
        $this->getTemplate()->SITUACAO_ATA_EXTERNA = 'I';
        $this->getTemplate()->block("BLOCO_FORMULARIO_MANTER");
    }

    private function consultarProcessoExterno($ano, $processo)
    {
        $db = Conexao();
        $sql = $this->sqlProcessoExterno($ano, $processo);
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($processos, DB_FETCHMODE_OBJECT);
        return $processos;
    }

    private function consultarModalidade()
    {
        $resultados = array();
        
        $db = Conexao();
        $sql = $this->sqlModalidade();
        $resultado = executarSQL($db, $sql);
        while ($resultado->fetchInto($modalidade, DB_FETCHMODE_OBJECT)) {
            array_push($resultados, $modalidade);
        }
        return $resultados;
    }

    private function consultarFornecedor($codigo)
    {
        $db = Conexao();
        $sql = $this->sqlFornecedor($codigo);
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($fornecedor, DB_FETCHMODE_OBJECT);
        return $fornecedor;
    }

    private function consultarFornecedorPorDocumento($documentoFornecedor)
    {
        $db = Conexao();
        $sql = $this->sqlSelectFornecedorPorDocumento($documentoFornecedor);
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($fornecedor, DB_FETCHMODE_OBJECT);
        
        return $fornecedor;
    }

    private function consultarProximaAtaNova($db)
    {
        $sql = $this->sqlSelectMaiorAtaNova();
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($ata, DB_FETCHMODE_OBJECT);
        
        return $ata->max + 1;
    }

    private function inserirAtaRegistroPrecoNova($numeroAta, $db)
    {
        $sql = $this->sqlInsertAtaRegistroPrecoNova($numeroAta);
        $resultado = executarTransacao($db, $sql);
    }

    private function consultarItemAtaInterna($codigoAta)
    {
        $resultados = array();
        
        $db = Conexao();
        $sql = $this->sqlItemAtaExterna($codigoAta);
        $resultado = executarSQL($db, $sql);
        while ($resultado->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            array_push($resultados, $item);
        }
        return $resultados;
    }

    private function alterarSituacaoAtaExterna($numeroAta, $situacao)
    {
        $db = Conexao();
        $sql = $this->sqlAlteraSituacao($numeroAta, $situacao);
        $result = $db->query($sql);
        if ($PEAR::isError($result)) {
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
        }
    }

    private function alterarAtaExterna($valoresAta, $numeroAta, $db)
    {
        $sql = $this->sqlAlterarAtaRegistroPrecoExterna($valoresAta, $numeroAta);
        $resultado = executarTransacao($db, $sql);
    }

    private function alterarItemAtaExterna($valoresAta, $numeroAta, $numeroItem, $database)
    {
        $sql = $this->sqlAlterarItemAta($valoresAta, $numeroAta, $numeroItem);
        $resultado = executarTransacao($database, $sql);
    }

    private function sqlAlteraSituacao($numeroAta, $situacao)
    {
        $sql = "UPDATE sfpc.tbataregistroprecoexterna";
        $sql .= " SET farpexsitu='%s', cusupocodi= %d, tarpinulat='now()'";
        $sql .= " WHERE carpnosequ=" . $numeroAta;
        
        $sql = sprintf($sql, $situacao, $_SESSION['_cusupocodi_']);
        
        return $sql;
    }

    private function sqlProcessoExterno($ano, $processo)
    {
        $sql = "SELECT a.carpnosequ, a.aarpexanon, a.carpexcodn, a.earpexproc, a.cmodlicodi,";
        $sql .= " a.earpexorgg, a.earpexobje, a.tarpexdini, a.aarpexpzvg, a.aforcrsequ, a.aforcrseq1, a.farpexsitu, a.cusupocodi, a.tarpinulat";
        $sql .= " FROM sfpc.tbataregistroprecoexterna a";
        $sql .= " where a.aarpexanon =" . $ano;
        $sql .= " and a.earpexproc = '$processo'";
        
        return $sql;
    }

    private function sqlModalidade()
    {
        $sql = "select m.cmodlicodi, m.emodlidesc from sfpc.tbmodalidadelicitacao m";
        return $sql;
    }

    private function sqlFornecedor($codigofornecedor)
    {
        $sql = "select f.aforcrccgc,aforcrsequ, f.aforcrccpf, f.nforcrrazs, f.eforcrlogr";
        $sql .= " from sfpc.tbfornecedorcredenciado f where f.aforcrsequ =" . $codigofornecedor;
        return $sql;
    }

    private function sqlSelectFornecedorPorDocumento($documentoFornecedor)
    {
        $sql = "select f.aforcrccgc,aforcrsequ, f.aforcrccpf, f.nforcrrazs, f.eforcrlogr";
        $sql .= " from sfpc.tbfornecedorcredenciado f where (f.aforcrccpf = '%s'";
        $sql .= " or f.aforcrccgc= '%s')";
        
        $sql = sprintf($sql, $documentoFornecedor, $documentoFornecedor);
        
        return $sql;
    }

    private function sqlItemAtaExterna($codigoAta)
    {
        $sql = "SELECT carpnosequ, citarpsequ, aitarporde, cmatepsequ, cservpsequ, aitarpqtor,";
        $sql .= "aitarpqtat, vitarpvori, vitarpvatu, citarpnuml, eitarpmarc, eitarpmode,";
        $sql .= "eitarpdescmat, eitarpdescse, fitarpsitu, fitarpincl, fitarpexcl,";
        $sql .= "titarpincl, cusupocodi, titarpulat";
        $sql .= " FROM sfpc.tbitemataregistropreconova i";
        $sql .= " where i.carpnosequ =" . $codigoAta;
        
        return $sql;
    }

    private function sqlAlterarAtaRegistroPrecoExterna($valoresAta, $sequencialAta)
    {
        $sql = "INSERT INTO sfpc.tbataregistroprecoexterna (carpnosequ,aarpexanon,carpexcodn,earpexproc,cmodlicodi,earpexorgg,";
        $sql .= "earpexobje,tarpexdini,aarpexpzvg,cusupocodi,tarpinulat,farpexsitu,aforcrsequ";
        $sql .= empty($valoresAta['codigoFornecedorAtual']) ? '' : ",aforcrseq1";
        $sql .= ")";
        $sql .= " VALUES (%d,%d,%d,'%s',%d,'%s','%s','%s',%d,%d,now(),'%s',%d";
        
        $sql .= empty($valoresAta['codigoFornecedorAtual']) ? '' : ",%d";
        $sql .= ")";
        
        if (! empty($valoresAta['codigoFornecedorAtual'])) {
            $sql = sprintf($sql, $sequencialAta, $valoresAta['ano'], $valoresAta['numeroAta'], $valoresAta['processo'], $valoresAta['modalidade'], $valoresAta['orgao'], $valoresAta['objeto'], $valoresAta['dataInicial'], $valoresAta['vigencia'], $_SESSION['_cusupocodi_'], "A", $valoresAta['codigoFornecedor'], $valoresAta['codigoFornecedorAtual']);
        } else {
            $sql = sprintf($sql, $sequencialAta, $valoresAta['ano'], $valoresAta['numeroAta'], $valoresAta['processo'], $valoresAta['modalidade'], $valoresAta['orgao'], $valoresAta['objeto'], $valoresAta['dataInicial'], $valoresAta['vigencia'], $_SESSION['_cusupocodi_'], "A", $valoresAta['codigoFornecedor']);
        }
        
        return $sql;
    }

    private function sqlInsertAtaRegistroPrecoNova($numeroAta)
    {
        $sql = "INSERT INTO sfpc.tbataregistropreconova";
        $sql .= "(carpnosequ, carpnotiat, tarpnoincl, cusupocodi, tarpnoulat)";
        $sql .= "VALUES(%d, 'E', now(), %d, now())";
        
        $sql = sprintf($sql, $numeroAta, $_SESSION['_cusupocodi_']);
        
        return $sql;
    }

    private function sqlSelectMaiorAtaNova()
    {
        $sql = "select max(a.carpnosequ) from sfpc.tbataregistropreconova a";
        return $sql;
    }

    private function sqlAlterarItemAta($valoresItens, $numeroAta, $numeroItem, $ordem)
    {
        $sql .= "INSERT INTO sfpc.tbitemataregistropreconova";
        $sql .= "(carpnosequ, citarpsequ, aitarporde, aitarpqtor, aitarpqtat, vitarpvori,";
        $sql .= "vitarpvatu, citarpnuml, eitarpdescmat, eitarpdescse,";
        $sql .= "fitarpsitu, fitarpincl, fitarpexcl, titarpincl, cusupocodi, titarpulat";
        $sql .= empty($valoresItens->cservpsequ) ? ",cmatepsequ" : ",cservpsequ";
        $sql .= ")";
        
        $sql .= "VALUES(%d, %d, %d, %d, %d, %f, %f, %d,'%s', '%s', 'A', 'S', 'N',now(), %d, now(),%d)";
        
        if (empty($valoresItens->cservpsequ)) {
            $sql = sprintf($sql, $numeroAta, $numeroItem, $valoresItens->aitarporde, $valoresItens->aitarpqtor, $valoresItens->aitarpqtat, $valoresItens->vitarpvori, $valoresItens->vitarpvatu, $valoresItens->citarpnuml, $valoresItens->eitarpdescmat, $valoresItens->eitarpdescse, $_SESSION['_cusupocodi_'], $valoresItens->cmatepsequ);
        } else {
            $sql = sprintf($sql, $numeroAta, $numeroItem, $valoresItens->aitarporde, $valoresItens->aitarpqtor, $valoresItens->aitarpqtat, $valoresItens->vitarpvori, $valoresItens->vitarpvatu, $valoresItens->citarpnuml, $valoresItens->eitarpdescmat, $valoresItens->eitarpdescse, $_SESSION['_cusupocodi_'], $valoresItens->cservpsequ);
        }
        return $sql;
    }

    private function sqlInsertDocumentoAtaExterna()
    {
        $sql = "INSERT INTO sfpc.tbataregistroprecoexternadoc";
        $sql .= " (carpetcodi, carpedcodi, darpeddata, earpednome, tarpedulat, cgrempcodi, cusupocodi, earpednoms)";
        $sql .= " VALUES(0, 0, '', '', '', 0, 0, '')";
        
        $sql = sprintf($sql, 1);
    }

    private function inserirDocumento()
    {
        $documento = $this->variables['post']['documentoAta'];
        $documentos = $_SESSION['documentos'] == null ? array() : $_SESSION['documentos'];
        array_push($documentos, $documento);
        
        $_SESSION['documentos'] = $documentos;
        
        $this->reiniciarTela();
        $this->getTemplate()->block("BLOCO_FORMULARIO_MANTER");
    }

    private function removerDocumento()
    {
        $documentos = $_SESSION['documentos'];
        
        if (sizeof($documentos)) {
            unset($documentos[0]);
            $_SESSION['documentos'] = $documentos;
        }
        
        $this->reiniciarTela();
        $this->getTemplate()->block("BLOCO_FORMULARIO_MANTER");
    }

    private function recuperarValorItens()
    {
        $itens = array();
        
        $arrayOriginal = $this->variables['post']['orginalItem'];
        $arrayvalorOrginalItem = $this->variables['post']['valororginalItem'];
        $arrayTotalOrginalItem = $this->variables['post']['totalorginalItem'];
        $arrayloteItem = $this->variables['post']['loteItem'];
        $arrayQuantidade = $this->variables['post']['quantidadeItem'];
        $arrayValorUnitario = $this->variables['post']['valorUnitarioItem'];
        $arrayTotal = $this->variables['post']['totalUnitarioItem'];
        $arraySituacao = $this->variables['post']['situacaoAta'];
        $arrayOrdem = $this->variables['post']['ordem'];
        $arrayTipo = $this->variables['post']['tipo'];
        $arrayDescricao = $this->variables['post']['descricao'];
        $arrayFlagTipo = $this->variables['post']['valorTipo'];
        $arraySeq = $this->variables['post']['seq'];
        
        for ($i = 0; $i < sizeof($arrayOriginal); $i ++) {
            $item = array();
            $item['aitarpqtor'] = $arrayOriginal[$i];
            $item['vitarpvori'] = $arrayvalorOrginalItem[$i];
            $item['citarpnuml'] = $arrayloteItem[$i];
            $item['aitarpqtat'] = $arrayQuantidade[$i];
            $item['vitarpvatu'] = $arrayValorUnitario[$i];
            $item['fitarpsitu'] = $arraySituacao[$i];
            $item['aitarpqtat'] = $arrayQuantidade[$i];
            $item['vitarpvatu'] = $arrayValorUnitario[$i];
            $item['fitarpsitu'] = $arraySituacao[$i];
            $item['aitarporde'] = $arrayOrdem[$i];
            $item['cmatepsequ'] = $arrayFlagTipo[$i] == "M" ? $arrayTipo[$i] : null;
            $item['cservpsequ'] = $arrayFlagTipo[$i] == "S" ? $arrayTipo[$i] : null;
            $item['eitarpdescse'] = $arrayFlagTipo[$i] == "S" ? $arrayDescricao[$i] : null;
            $item['eitarpdescmat'] = $arrayFlagTipo[$i] == "M" ? $arrayDescricao[$i] : null;
            $item['citarpsequ'] = $arraySeq[$i];
            array_push($itens, (object) $item);
        }
        return $itens;
    }

    private function pesquisarFornecedor($fornecedorTipo)
    {
        if ($fornecedorTipo == 'Original') {
            $documento = $this->variables['post']['fornecedorOriginalAta'];
        } else {
            $documento = $this->variables['post']['fornecedorAtualAta'];
        }
        
        $documento = $this->soNumero($documento);
        
        $fornecedor = $this->consultarFornecedorPorDocumento($documento);
        
        $itens = $this->atualizaValoresItemTela();
        $ata = $this->atualizaValoresAtaTela();
        
        if ($fornecedorTipo == 'Original') {
            $ata['aforcrsequ'] = $fornecedor->aforcrsequ;
        } else {
            $ata['aforcrsequ1'] = $fornecedor->aforcrsequ;
        }
        
        $ata = $this->arrayParaObject($ata);
        $this->plotarBlocoProcesso($ata);
        $itens = $this->recuperarValorItens();
        $this->plotarBlocoItem($itens);
        $this->getTemplate()->block("BLOCO_FORMULARIO_MANTER");
    }

    public function soNumero($str)
    {
        return preg_replace("/[^0-9]/", "", $str);
    }

    private function processVoltar()
    {
        $uri = 'CadAtaRegistroPrecoExternaSelecionarNovo.php';
        header('location: ' . $uri);
    }

    /**
     * Proccess Retirar
     */
    private function atualizaProcessos()
    {
        $variables = $this->getVariables();
        $valor = $variables['post']['anoProcesso'];
        
        $anos = $this->carregarAno();
        $this->plotarBlocoAno($anos);
        
        $processos = $this->consultarProcessoExterno($anos[$valor]);
        $this->plotarBlocoProcesso($processos);
        
        $this->getTemplate()->block("BLOCO_FORMULARIO_MANTER");
    }

    /**
     * Front Controller
     */
    protected function frontController()
    {
        $variables = $this->getVariables();
        $botao = isset($variables['post']['Botao']) ? $variables['post']['Botao'] : 'Principal';
        switch ($botao) {
            case 'Salvar':
                $this->salvarDados();
                break;
            case 'InserirDocumento':
                $this->inserirDocumento();
                break;
            case 'RemoverDocumento':
                $this->removerDocumento();
                break;
            case 'Voltar':
                $this->processVoltar();
                break;
            case 'original':
                $this->pesquisarFornecedor('Original');
                break;
            case 'atual':
                $this->pesquisarFornecedor('Atual');
                break;
            case 'retirarItem':
                $this->retirarItem();
                break;
            case 'Principal':
            default:
                $this->proccessPrincipal();
                break;
        }
    }

    /**
     * Construct
     *
     * @param array $session
     *            [description]
     */
    public function __construct(array $variablesGlobals)
    {
        /**
         * Settings
         */
        /**
         * Create instance of Template
         */
        $template = new TemplatePaginaPadrao("templates/CadAtaRegistroPrecoExternaSelecionarIncluir.html", "Registro de Preço > Ata Externa > Manter");
        
        $this->setTemplate($template);
        $this->setVariables($variablesGlobals);
        /**
         * Front Controller for action
         */
        $this->frontController();
    }

    /**
     * Filter Sanitize input data POST
     *
     * @return array [description]
     */
    public static function filterSanitizePOST()
    {
        return array(
            'Botao' => FILTER_SANITIZE_STRING,
            'NumeroAta' => FILTER_SANITIZE_NUMBER_INT,
            'anoAta' => FILTER_SANITIZE_NUMBER_INT,
            'processoAta' => FILTER_SANITIZE_STRING,
            'modalidadeAta' => FILTER_SANITIZE_STRING,
            'orgaoAta' => FILTER_SANITIZE_STRING,
            'objetoAta' => FILTER_SANITIZE_STRING,
            'documentoAta' => FILTER_SANITIZE_STRING,
            'dataInicialAta' => FILTER_SANITIZE_STRING,
            'vigenciaAta' => FILTER_SANITIZE_STRING,
            'fornecedorOriginalAta' => FILTER_SANITIZE_STRING,
            'fornecedorAtualAta' => FILTER_SANITIZE_STRING,
            'codigoFornecedorAtual' => FILTER_SANITIZE_NUMBER_INT,
            'codigoFornecedor' => FILTER_SANITIZE_NUMBER_INT,
            'documentos' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_REQUIRE_ARRAY
            ),
            
            'Orgaos' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_REQUIRE_ARRAY
            ),
            'ObservacaoIntencao' => FILTER_SANITIZE_STRING,
            'intencaoItem' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_REQUIRE_ARRAY
            ),
            'DataLimite' => FILTER_SANITIZE_STRING,
            'Objeto' => FILTER_SANITIZE_STRING,
            'ano' => FILTER_SANITIZE_NUMBER_INT,
            'Observacao' => FILTER_SANITIZE_STRING,
            'processo' => FILTER_SANITIZE_STRING,
            'SituacaoAta' => FILTER_SANITIZE_STRING,
            'processoExterno' => FILTER_SANITIZE_STRING,
            'anoProcesso' => FILTER_SANITIZE_NUMBER_INT,
            
            'orginalItem' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_REQUIRE_ARRAY
            ),
            'valororginalItem' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_REQUIRE_ARRAY
            ),
            'totalorginalItem' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_REQUIRE_ARRAY
            ),
            'loteItem' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_REQUIRE_ARRAY
            ),
            'quantidadeItem' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_REQUIRE_ARRAY
            ),
            'valorUnitarioItem' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_REQUIRE_ARRAY
            ),
            'totalUnitarioItem' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_REQUIRE_ARRAY
            ),
            'situacaoAta' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_REQUIRE_ARRAY
            ),
            'ordem' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_REQUIRE_ARRAY
            ),
            'tipo' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_REQUIRE_ARRAY
            ),
            'descricao' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_REQUIRE_ARRAY
            ),
            'valorTipo' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_REQUIRE_ARRAY
            ),
            'seq' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_REQUIRE_ARRAY
            )
        )
        ;
    }

    /**
     * Filter Sanitize input data GET
     *
     * @return array [description]
     */
    public static function filterSanitizeGET()
    {
        return array(
            'numero' => FILTER_SANITIZE_STRING,
            'processo' => FILTER_SANITIZE_STRING,
            'ano' => FILTER_SANITIZE_NUMBER_INT
        );
    }

    /**
     * Bootstrap application
     */
    public static function bootstrap()
    {
        $arrayGlobals = parent::setup();
        
        if ($arrayGlobals['server']['REQUEST_METHOD'] == "POST") {
            $arrayGlobals['post'] = filter_input_array(INPUT_POST, self::filterSanitizePOST());
        }
        
        if ($arrayGlobals['server']['REQUEST_METHOD'] == 'GET') {
            $arrayGlobals['get'] = filter_input_array(INPUT_GET, self::filterSanitizeGET());
        }
        
        $app = new CadAtaRegistroPrecoExternaSelecionarIncluir($arrayGlobals);
        echo $app->run();
    }
}
/**
 * DO REMOVE IT'S STATEMENT
 */
CadAtaRegistroPrecoExternaSelecionarIncluir::bootstrap();
