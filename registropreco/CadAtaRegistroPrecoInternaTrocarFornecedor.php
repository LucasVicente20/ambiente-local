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
 * @category  Pitang_Registro_Preço
 * @package   Registropreco
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 * @version   GIT: 20150209_143500-93-g66fecca
 */
#----------------------------------------------
# Pitang Agile TI - Caio Coutinho
# Data: 04/07/2018
# Objetivo: CR Redmine #198150
#----------------------------------------------
# Autor: Lucas Vicente
# Data: 22/08/2022
# Objetivo: CR 240266
#----------------------------------------------

// 220038--

if (! @require_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}

class RegistroPreco_Dados_CadAtaRegistroPrecoInternaTrocarFornecedor extends Dados_Abstrata
{
    /**
     *
     * @return NULL|[]
     */
    public function consultarAtasInternas()
    {
        return ClaDatabasePostgresql::executarSQL("SELECT * FROM  sfpc.tbataregistroprecointerna");
    }

    /**
     *
     * @return NULL
     */
    public function consultarOrgaoComNumeracaoGerada()
    {
        return ClaDatabasePostgresql::executarSQL($this->sqlOrgaoAtaGerada());
    }

    /**
     *
     * @param integer $ano
     * @param integer $orgao
     * @return string
     */
    public function sqlProcessoRegistroPrecoEmHomologacao($ano, $orgao, $grupo)
    {
        $licHomologacao = 13;
        $licHomologacaoParcial = 26;

            $sql = "
            SELECT
            DISTINCT l.clicpoproc,
            l.alicpoanop,
            l.ccomlicodi,
            l.cgrempcodi,
            l.corglicodi,
            B.ecomlidesc,
            e.cfasescodi
        FROM
            sfpc.tblicitacaoportal l
            INNER JOIN
                sfpc.tbcomissaolicitacao B
                    ON l.ccomlicodi = B.ccomlicodi
            INNER JOIN sfpc.tbusuariocomis D
                ON l.cgrempcodi = D.cgrempcodi
                    AND D.ccomlicodi = l.ccomlicodi
            INNER JOIN sfpc.tbfaselicitacao E
                ON l.clicpoproc = e.clicpoproc
                    AND l.alicpoanop = e.alicpoanop
                    AND l.cgrempcodi = e.cgrempcodi
                    AND l.ccomlicodi = e.ccomlicodi
                    AND l.corglicodi = e.corglicodi
			INNER JOIN sfpc.tbataregistroprecointerna F
					ON l.clicpoproc = F.clicpoproc
						AND l.alicpoanop = F.alicpoanop
						AND l.cgrempcodi = F.cgrempcodi
						AND l.ccomlicodi = F.ccomlicodi
						AND l.corglicodi = F.corglicodi
        WHERE
            l.cgrempcodi = ".$grupo."
            AND l.alicpoanop = %d
            AND l.flicporegp LIKE 'S'
            AND l.corglicodi = %d
            AND e.cfasescodi = %d
        ORDER BY
            B.ecomlidesc ASC,
            l.alicpoanop DESC,
            l.clicpoproc DESC";
      
            return sprintf($sql, $ano, $orgao, $licHomologacao, $licHomologacaoParcial);
            //print_r($licHomologacaoParcial);die;
    }

    /**
     *
     * @return string
     */
    public function sqlOrgaoAtaGerada()
    {
        $sql = "
        SELECT DISTINCT o.corglicodi, o.eorglidesc, a.cgrempcodi
        FROM  sfpc.tborgaolicitante o
        INNER JOIN sfpc.tbataregistroprecointerna a ON o.corglicodi = a.corglicodi
        WHERE o.forglisitu LIKE 'A'
        ORDER BY o.eorglidesc
        ";

        return $sql;
    }

    /**
     *
     * @param integer $orgao
     * @param integer $ano
     * @return unknown[]
     */
    public function consultarProcessoDoOrgaoNoAno($orgao, $ano, $grupo)
    {
        return ClaDatabasePostgresql::executarSQL($this->sqlProcessoRegistroPrecoEmHomologacao($ano, $orgao, $grupo));
    }
}

/**
 *
 * @author jfsi
 *
 */
class RegistroPreco_Negocio_CadAtaRegistroPrecoInternaTrocarFornecedor extends Negocio_Abstrata
{
    /**
     *
     * {@inheritDoc}
     *
     * @see Negocio_Abstrata::getDados()
     */
    public function getDados()
    {
        $this->setDados(new RegistroPreco_Dados_CadAtaRegistroPrecoInternaTrocarFornecedor());
        return parent::getDados();
    }
}

/**
 *
 * @author jfsi
 *
 */
class RegistroPreco_Adaptacao_CadAtaRegistroPrecoInternaTrocarFornecedor extends Adaptacao_Abstrata
{
    /**
     *
     * {@inheritDoc}
     *
     * @see Adaptacao_Abstrata::getNegocio()
     */
    public function getNegocio()
    {
        $this->setNegocio(new RegistroPreco_Negocio_CadAtaRegistroPrecoInternaTrocarFornecedor());
        return parent::getNegocio();
    }
}

/**
 *
 * @author jfsi
 *
 */
class RegistroPreco_UI_CadAtaRegistroPrecoInternaTrocarFornecedor extends UI_Abstrata
{
    /**
     *
     * {@inheritDoc}
     *
     * @see UI_Abstrata::getAdaptacao()
     *
     * @return RegistroPreco_Adaptacao_CadAtaRegistroPrecoInternaTrocarFornecedor
     */
    public function getAdaptacao()
    {
        $this->setAdaptacao(new RegistroPreco_Adaptacao_CadAtaRegistroPrecoInternaTrocarFornecedor());
        return parent::getAdaptacao();
    }

    /**
     */
    public function __construct()
    {
        $template = new TemplatePaginaPadrao("templates/CadAtaRegistroPrecoInternaTrocarFornecedor.html", "Registro de Preço > Ata Interna > Trocar Fornecedor");
        $this->setTemplate($template);
    }

    /**
     *
     * @param array $anos
     */
    public function plotarBlocoAno(array $anos)
    {
        $gerarNumeracao = filter_var($_POST['valorAno'], FILTER_SANITIZE_NUMBER_INT);

        $this->getTemplate()->ANO_VALUE = - 1;
        $this->getTemplate()->ANO_TEXT = "Selecione o ano";
        $this->getTemplate()->ANO_SELECTED = "selected";
        $this->getTemplate()->clear("ANO_SELECTED");
        $this->getTemplate()->block("BLOCO_ANO");

        foreach ($anos as $value => $text) {
            $this->getTemplate()->ANO_VALUE = $text;
            $this->getTemplate()->ANO_TEXT = $text;

            if ($gerarNumeracao == $text) {
                $this->getTemplate()->ANO_SELECTED = "selected";
            } else {
                // Caso esta não seja a opção atual, limpamos o valor da variável SELECTED
                $this->getTemplate()->clear("ANO_SELECTED");
            }

            $this->getTemplate()->block("BLOCO_ANO");
        }
    }

    /**
     *
     * @param array $orgaos
     */
    public function plotarBlocoOrgao(array $orgaos)
    {
        $orgaoNumeracao = filter_var($_POST['orgaoGestor'], FILTER_VALIDATE_INT);

        $this->getTemplate()->ORGAO_VALUE = - 1;
        $this->getTemplate()->ORGAO_TEXT = "Selecione o órgão";
        $this->getTemplate()->ORGAO_SELECTED = "selected";
        $this->getTemplate()->clear("ORGAO_SELECTED");
        $this->getTemplate()->block("BLOCO_ORGAO");

        foreach ($orgaos as $orgao) {
            $this->getTemplate()->ORGAO_VALUE = $orgao->corglicodi;

            $this->getTemplate()->ORGAO_TEXT = $orgao->eorglidesc;

            // Vendo se a opção atual deve ter o atributo "selected"
            if ($orgaoNumeracao == $orgao->corglicodi) {
                $this->getTemplate()->ORGAO_SELECTED = "selected";
            } else {
                $this->getTemplate()->clear("ORGAO_SELECTED");
            }

            $this->getTemplate()->block("BLOCO_ORGAO");
        }
    }

    /**
     *
     * @param array $processos
     */
    public function plotarBlocoProcesso(array $processos)
    {
        $ultCodComiss = 0;
        $this->getTemplate()->TEXTO_PROCESSO_SELECIONE = "Selecione o processo";
        

        foreach ($processos as $processo) {
            if ($processo != null) {
                if ($ultCodComiss == 0) {
                    $ultCodComiss = $processo->ccomlicodi;
                    $this->getTemplate()->OPTGROUP_INICIO = '<optgroup label="' . $processo->ecomlidesc . '">';
                } elseif ($ultCodComiss != $processo->ccomlicodi) {
                    $this->getTemplate()->block("BLOCK_PROCESSO");
                    $this->getTemplate()->OPTGROUP_FINAL = '</optgroup>';
                    $ultCodComiss = $processo->ccomlicodi;
                    $this->getTemplate()->OPTGROUP_INICIO = '<optgroup label="' . $processo->ecomlidesc . '">';
                }

                $this->getTemplate()->TEXTO_PROCESSO = str_pad($processo->clicpoproc, 4, '0', STR_PAD_LEFT) . '/' . $processo->alicpoanop;
                
                $formataId = implode('-', array(
                    $processo->clicpoproc,
                    $processo->alicpoanop,
                    $processo->cgrempcodi,
                    $processo->ccomlicodi,
                    $processo->corglicodi,
                ));
               $this->getTemplate()->PROCESSO_VALUE = $formataId;
                
                // $this->getTemplate()->PROCESSO_VALUE = $processo->clicpoproc;
            }

            // Vendo se a opção atual deve ter o atributo "selected"CadAtaRegistroPrecoInternaManter
            if ($processo == null) {
                $this->getTemplate()->PROCESSO_SELECTED = "selected";
            } else {
                // Caso esta não seja a opção atual, limpamos o valor da variável SELECTED
                $this->getTemplate()->clear("PROCESSO_SELECTED");
            }
            $this->getTemplate()->block("BLOCK_OPTION");
        }
        $this->getTemplate()->block("BLOCK_PROCESSO");
    }
}

/**
 */
class CadAtaRegistroPrecoInternaTrocarFornecedor extends ProgramaAbstrato
{
    /**
     */
    private function proccessPrincipal()
    {
        $atasInternas = $this->getUI()
            ->getAdaptacao()
            ->getNegocio()
            ->getDados()
            ->consultarAtasInternas();

        if ($GLOBALS['REQUEST_METHOD'] == 'POST' && empty($atasInternas)) {
            $mensagem = 'Não existe Ata Interna cadastrada';
            $this->getUI()->mensagemSistema($mensagem, 1, 1);
            return;
        }
        $orgaos = $this->getUI()
            ->getAdaptacao()
            ->getNegocio()
            ->getDados()
            ->consultarOrgaoComNumeracaoGerada();
        $this->getUI()->plotarBlocoOrgao($orgaos);

        $anos = array();
        $this->getUI()->plotarBlocoAno($anos);

        $processos = array();
        $this->getUI()->plotarBlocoProcesso($processos);
    }

    /**
     */
    private function atualizaProcessos()
    {
        if (! filter_var($_POST['orgaoGestor'], FILTER_VALIDATE_INT)) {
            $this->getUI()->mensagemSistema("Órgao não foi informado", 1, 1);
            return;
        }

        $orgao = filter_var($_POST['orgaoGestor'], FILTER_SANITIZE_NUMBER_INT);

        $orgaos = $this->getUI()
            ->getAdaptacao()
            ->getNegocio()
            ->getDados()
            ->consultarOrgaoComNumeracaoGerada();

        $this->getUI()->plotarBlocoOrgao($orgaos);

        $anos = HelperPitang::carregarAno();
        $this->getUI()->plotarBlocoAno($anos);

        $ano = empty($_POST['valorAno']) ? 0 : $_POST['valorAno'];
        $grupo = $this->buscarGrupo($orgao, $orgaos);
        $processos = $this->getUI()
            ->getAdaptacao()
            ->getNegocio()
            ->getDados()
            ->consultarProcessoDoOrgaoNoAno($orgao, $ano, $grupo);

        $this->getUI()->plotarBlocoProcesso($processos);
    }

    public function buscarGrupo($orgao, $orgaos) {
        $grupo = null;
        if(!empty($orgao) && !empty($orgaos)) {
            foreach ($orgaos as $value) {
                if ($value->corglicodi == $orgao) {
                    $grupo = $value->cgrempcodi;
                    break;
                }
            }
        }

        return $grupo;
    }

    /**
     */
    private function proccessSelecionar()
    {
        $orgao      = filter_var($_POST['orgaoGestor'], FILTER_SANITIZE_NUMBER_INT);
        $ano        = filter_var($_POST['valorAno'], FILTER_SANITIZE_NUMBER_INT);
        $processo   = filter_var($_POST['GerarNumeracaoProcesso'], FILTER_SANITIZE_NUMBER_INT);

        if (empty($orgao)) {
            $this->getUI()->mensagemSistema("Órgao não foi selecionado", 1, 1);
            $this->proccessPrincipal();
            return;
        } else if(empty($ano)) {
            $this->getUI()->mensagemSistema("Ano não foi selecionado", 1, 1);
            $this->proccessPrincipal();
            return;
        } else if (empty($processo)) {
            $this->getUI()->mensagemSistema("Processo não foi selecionado", 1, 1);
            $this->proccessPrincipal();
            return;
        } else {
            $uri = 'CadAtaRegistroPrecoInternaTrocarFornecedorAtas.php?ano=' . $ano . '&processo=' . $processo . '&orgao=' . $orgao;
            header('location: ' . $uri);
        }
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see ProgramaAbstrato::configuracao()
     */
    protected function configuracao()
    {
        $this->setUI(new RegistroPreco_UI_CadAtaRegistroPrecoInternaTrocarFornecedor());
        $this->getUI()->getTemplate()->NAME_PROGRAMA = 'CadAtaRegistroPrecoInternaTrocarFornecedor';
        $this->getUI()->getTemplate()->TITULO_SUPERIOR = 'TROCAR FORNECEDOR - ATA INTERNA';
        $this->getUI()->getTemplate()->DESCRICAO_REGRAS_PAG = 'Para trocar o fornecedor de uma Ata  Interna, selecione o ano e o Processo Licitatório que já possui a numeração das Atas Internas e clique no botão “Selecionar”.';
    }

    /**
     * [frontController description]
     *
     * @return [type] [description]
     */
    public function frontController()
    {
        $botao = isset($_REQUEST['Botao']) ? filter_var($_POST['Botao'], FILTER_SANITIZE_STRING) : 'Principal';

        switch ($botao) {
            case 'Voltar':
                $this->processVoltar();
                break;
            case 'Selecionar':
                $this->proccessSelecionar();
                break;
            case 'AtualizaAnos':
                $this->atualizaProcessos();
                break;
            case 'AtualizaProcessos':
                $this->atualizaProcessos();
                break;
            case 'ImprimirCompleto':
                $this->processImprimirCompleto();
                break;
            case 'Principal':
            default:
                $this->proccessPrincipal();
                break;
        }
    }
}

ProgramaAbstrato::iniciar(new CadAtaRegistroPrecoInternaTrocarFornecedor());
